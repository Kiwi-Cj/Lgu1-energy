<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;
use App\Support\BaselineResolver;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CprfFacilityReadingController extends Controller
{
    /**
     * Inbound manual meter readings pushed directly by CPRF (facilities
     * reservation), independent of the UMAN-mediated monthly-record path.
     *
     * Attaches the facility's approved main meter (auto-creating one, same
     * as UmanMonthlyRecordSyncService::importRow(), if the facility genuinely
     * has none yet) and auto-approves the record, matching the UMAN path's
     * trust level - CPRF is treated as a reviewed upstream source for both
     * routes, not as unreviewed manual entry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'previous_reading_kwh' => ['required', 'numeric', 'min:0'],
            'current_reading_kwh' => ['required', 'numeric', 'min:0'],
            'reading_date' => ['required', 'date'],
            'is_rollover' => ['nullable', 'boolean'],
            'is_meter_reset' => ['nullable', 'boolean'],
            'dial_capacity' => ['nullable', 'numeric', 'min:1'],
            'energy_cost' => ['nullable', 'numeric', 'min:0'],
            'rate_per_kwh' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'external_ref' => ['nullable', 'string', 'max:100'],
            'recorded_by_name' => ['nullable', 'string', 'max:255'],
            'recorded_by_email' => ['nullable', 'email', 'max:255'],
        ]);

        $prev = (float) $validated['previous_reading_kwh'];
        $curr = (float) $validated['current_reading_kwh'];
        $isRollover = ! empty($validated['is_rollover']);
        $isReset = ! empty($validated['is_meter_reset']);

        if ($curr < $prev && ! $isRollover && ! $isReset) {
            return response()->json([
                'message' => "Current dial reading ({$curr}) is lower than previous dial reading ({$prev}). If the meter rolled over its dial or was replaced, pass 'is_rollover': true or 'is_meter_reset': true.",
                'errors' => [
                    'current_reading_kwh' => [
                        "Current reading must be greater than or equal to previous reading unless 'is_rollover' or 'is_meter_reset' is specified.",
                    ],
                ],
            ], 422);
        }

        if ($isReset) {
            // New meter installed or dial reset to 0
            $actualKwh = round($curr, 2);
        } elseif ($isRollover) {
            // Dial rollover past maximum capacity
            $capacity = ! empty($validated['dial_capacity'])
                ? (float) $validated['dial_capacity']
                : pow(10, max(4, (int) ceil(log10(max(10, $prev + 1)))));
            $actualKwh = round(($capacity - $prev) + $curr, 2);
        } else {
            $actualKwh = round($curr - $prev, 2);
        }

        // Negative consumption guard
        $actualKwh = max(0.0, $actualKwh);

        /** @var Facility $facility */
        $facility = Facility::query()->findOrFail((int) $validated['facility_id']);
        $recordedBy = $this->resolveRecordedByUser(
            $facility,
            $validated['recorded_by_email'] ?? null,
            $validated['recorded_by_name'] ?? null,
        );
        $meter = $this->resolveOrCreateMainMeter($facility);

        $baseline = BaselineResolver::forFacility($facility, $meter);
        $deviation = EnergyRecord::calculateDeviation($actualKwh, $baseline);
        $alert = EnergyRecord::resolveAlertLevel($deviation, $baseline);

        // Anomaly / abnormal spike guard
        $isAnomalousSpike = false;
        $isZeroConsumption = ($actualKwh == 0.0);
        $spikeWarnings = [];

        if ($baseline !== null && $baseline > 0 && $actualKwh >= ($baseline * 3)) {
            $isAnomalousSpike = true;
            $alert = 'Critical';
            $pct = round(($actualKwh / $baseline) * 100);
            $spikeWarnings[] = "Abnormal consumption spike: {$actualKwh} kWh is {$pct}% of monthly baseline ({$baseline} kWh).";
        }

        if ($isZeroConsumption) {
            $spikeWarnings[] = 'Zero consumption reported. Verify facility operation or meter status.';
        }

        $attributes = [
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => (int) $validated['year'],
            'month' => (int) $validated['month'],
        ];
        $fill = [
            'day' => Carbon::parse($validated['reading_date'])->day,
            'actual_kwh' => $actualKwh,
            // Raw dial values, kept verbatim alongside the derived
            // consumption above - CPRF is the only source that ever sends
            // these (manual entry and UMAN both submit consumption
            // directly), so they stay null for every other input_source.
            'previous_reading_kwh' => $validated['previous_reading_kwh'],
            'current_reading_kwh' => $validated['current_reading_kwh'],
            'baseline_kwh' => $baseline,
            'deviation' => $deviation,
            'alert' => $alert,
            // 0, not null: both columns are declared nullable() in the
            // migrations, but production's actual columns enforce NOT NULL
            // (same drift class as recorded_by below) — energy_cost already
            // confirmed live, rate_per_kwh presumed guilty by association
            // (added in the same migration wave, same manual-entry form).
            // CPRF often has no cost/rate to report; 0 reads as "no cost
            // data" and satisfies either schema, so this doesn't depend on
            // production's column nullability matching the migrations.
            'energy_cost' => $validated['energy_cost'] ?? 0,
            'rate_per_kwh' => $validated['rate_per_kwh'] ?? 0,
            'input_source' => 'cprf',
            // Auto-approved like the UMAN path (UmanMonthlyRecordSyncService)
            // - CPRF's own data-entry flow is the review, not a second
            // manual pass in Energy. Without this + a real meter_id, the
            // record would sit invisible to /api/v1/cprf/recommendations
            // (whereNotNull('meter_id') + review_status='approved').
            'review_status' => 'approved',
            'reviewed_by' => null,
            'reviewed_at' => now(),
            'review_remarks' => null,
            // Resolve the CPRF recorder to the matching active Energy staff
            // account assigned to this facility. This lets downstream
            // recommendations default their implementation owner correctly.
            'recorded_by' => $recordedBy?->id,
            // Keep the source display name even when no matching local Energy
            // account exists, so attribution is never discarded.
            'recorded_by_name' => $validated['recorded_by_name'] ?? null,
        ];

        try {
            [$record, $wasExisting] = $this->upsertFacilityPeriod($attributes, $fill);
        } catch (QueryException $e) {
            if (! $this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            // A concurrent/retried request won the race and inserted the row
            // first. Retry once: firstOrNew will now find that row and update
            // it, keeping this endpoint idempotent under the DB-level unique
            // constraint on (facility_id, active_period_key, year, month).
            [$record, $wasExisting] = $this->upsertFacilityPeriod($attributes, $fill);
        }

        // notes and external_ref have no energy_records columns; include them
        // with the source recorder metadata in the integration log.
        Log::info('CPRF facility reading received', [
            'energy_record_id' => $record->id,
            'meter_id' => $meter->id,
            'external_ref' => $validated['external_ref'] ?? null,
            'recorded_by_name' => $validated['recorded_by_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => $wasExisting ? 'Facility reading updated.' : 'Facility reading received.',
            'record' => [
                'id' => $record->id,
                'facility_id' => $record->facility_id,
                'meter_id' => $record->meter_id,
                'period' => ['year' => (int) $record->year, 'month' => (int) $record->month],
                'actual_kwh' => (float) (string) $record->actual_kwh,
                'previous_reading_kwh' => $record->previous_reading_kwh !== null ? (float) (string) $record->previous_reading_kwh : null,
                'current_reading_kwh' => $record->current_reading_kwh !== null ? (float) (string) $record->current_reading_kwh : null,
                'baseline_kwh' => $record->baseline_kwh !== null ? (float) (string) $record->baseline_kwh : null,
                'deviation_percent' => $record->deviation,
                'alert' => $record->alert,
                'input_source' => $record->input_source,
                'recorded_by' => $record->recorded_by,
                'review_status' => $record->review_status,
            ],
            'guards' => [
                'is_rollover' => $isRollover,
                'is_meter_reset' => $isReset,
                'is_anomalous_spike' => $isAnomalousSpike,
                'is_zero_consumption' => $isZeroConsumption,
                'warnings' => $spikeWarnings,
            ],
        ], $wasExisting ? 200 : 201, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * The facility's approved main meter, or an integration-managed one
     * created on first use. Mirrors UmanMonthlyRecordSyncService::importRow()
     * exactly, so a facility fed by both paths converges on the same meter
     * instead of each path creating its own.
     */
    private function resolveOrCreateMainMeter(Facility $facility): FacilityMeter
    {
        $primaryMeterId = (int) ($facility->energyProfiles()->value('primary_meter_id') ?? 0);
        $meter = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->when(
                $primaryMeterId > 0,
                fn ($query) => $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$primaryMeterId])
            )
            ->orderBy('id')
            ->first();

        if ($meter) {
            return $meter;
        }

        $meter = FacilityMeter::withTrashed()->firstOrNew([
            'facility_id' => $facility->id,
            'meter_number' => 'CPRF-'.$facility->id,
        ]);
        $meter->fill([
            'meter_name' => 'CPRF Integrated Main Meter',
            'meter_type' => 'main',
            'location' => (string) ($facility->address ?? $facility->name),
            'status' => 'active',
            'multiplier' => 1,
            'notes' => 'Automatically managed by the CPRF facility-reading integration.',
            'approved_at' => $meter->approved_at ?? now(),
        ]);
        $meter->save();
        if ($meter->trashed()) {
            $meter->restore();
        }

        return $meter;
    }

    /**
     * Find-or-create the monthly energy_records row for $attributes (now
     * keyed on the resolved meter_id, not the facility-level NULL slot) and
     * fill/save it with $fill.
     *
     * @return array{0: EnergyRecord, 1: bool} the record and whether it already existed.
     */
    private function upsertFacilityPeriod(array $attributes, array $fill): array
    {
        $record = EnergyRecord::query()->firstOrNew($attributes);
        $wasExisting = $record->exists;

        $record->fill($fill);
        $record->save();

        return [$record, $wasExisting];
    }

    private function resolveRecordedByUser(Facility $facility, ?string $email, ?string $name): ?User
    {
        $eligibleStaff = User::query()
            ->where('status', 'active')
            ->whereRaw("REPLACE(REPLACE(LOWER(role), ' ', '_'), '-', '_') = ?", ['staff'])
            ->whereHas('facilities', fn ($query) => $query->whereKey($facility->id));

        $normalizedEmail = mb_strtolower(trim((string) $email));
        if ($normalizedEmail !== '') {
            $matchedByEmail = (clone $eligibleStaff)
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();
            if ($matchedByEmail) {
                return $matchedByEmail;
            }
        }

        $normalizedName = $this->normalizePersonName($name);
        if ($normalizedName === '') {
            return null;
        }

        return $eligibleStaff
            ->get(['id', 'full_name', 'name', 'username'])
            ->first(function (User $user) use ($normalizedName): bool {
                foreach ([$user->full_name, $user->name, $user->username] as $candidate) {
                    if ($this->normalizePersonName($candidate) === $normalizedName) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function normalizePersonName(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $value))) ?? '';
    }

    /**
     * Whether the given QueryException was caused by the DB-level unique
     * constraint on (facility_id, active_period_key, year, month), as opposed
     * to some other unrelated query failure that should be rethrown.
     */
    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        // SQLSTATE 23000 = integrity constraint violation, covering unique
        // index violations on both sqlite and MySQL/MariaDB.
        if ($e->getCode() === '23000') {
            return true;
        }

        $message = $e->getMessage();

        return str_contains($message, 'energy_records_active_period_unique')
            || str_contains($message, 'UNIQUE constraint failed');
    }
}
