<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CprfFacilityReadingController extends Controller
{
    /**
     * Inbound manual meter readings pushed by CPRF (facilities reservation).
     *
     * Upserts the facility-level (meter_id NULL) monthly energy_records row so
     * CPRF-sourced data flows through the same baseline/deviation/alert
     * pipeline as records encoded on this app's own Energy Monitoring page.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'previous_reading_kwh' => ['required', 'numeric', 'min:0'],
            'current_reading_kwh' => ['required', 'numeric', 'gte:previous_reading_kwh'],
            'reading_date' => ['required', 'date'],
            'energy_cost' => ['nullable', 'numeric', 'min:0'],
            'rate_per_kwh' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'external_ref' => ['nullable', 'string', 'max:100'],
            'recorded_by_name' => ['nullable', 'string', 'max:255'],
            'recorded_by_email' => ['nullable', 'email', 'max:255'],
        ]);

        /** @var Facility $facility */
        $facility = Facility::query()->findOrFail((int) $validated['facility_id']);
        $recordedBy = $this->resolveRecordedByUser(
            $facility,
            $validated['recorded_by_email'] ?? null,
            $validated['recorded_by_name'] ?? null,
        );

        $actualKwh = round((float) $validated['current_reading_kwh'] - (float) $validated['previous_reading_kwh'], 2);
        $baseline = $facility->resolveBaselineKwh();
        $deviation = EnergyRecord::calculateDeviation($actualKwh, $baseline);
        $alert = EnergyRecord::resolveAlertLevel($deviation, $baseline);

        $attributes = [
            'facility_id' => $facility->id,
            'year' => (int) $validated['year'],
            'month' => (int) $validated['month'],
        ];
        $fill = [
            'day' => Carbon::parse($validated['reading_date'])->day,
            'actual_kwh' => $actualKwh,
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
            'review_status' => 'for_review',
            'reviewed_by' => null,
            'reviewed_at' => null,
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
            'external_ref' => $validated['external_ref'] ?? null,
            'recorded_by_name' => $validated['recorded_by_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => $wasExisting ? 'Facility reading updated.' : 'Facility reading received.',
            'record' => [
                'id' => $record->id,
                'facility_id' => $record->facility_id,
                'period' => ['year' => (int) $record->year, 'month' => (int) $record->month],
                'actual_kwh' => (float) (string) $record->actual_kwh,
                'baseline_kwh' => $record->baseline_kwh !== null ? (float) (string) $record->baseline_kwh : null,
                'deviation_percent' => $record->deviation,
                'alert' => $record->alert,
                'input_source' => $record->input_source,
                'recorded_by' => $record->recorded_by,
                'review_status' => $record->review_status ?? 'for_review',
            ],
        ], $wasExisting ? 200 : 201, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Find-or-create the facility-level (meter_id NULL) monthly energy_records
     * row for $attributes and fill/save it with $fill.
     *
     * @return array{0: EnergyRecord, 1: bool} the record and whether it already existed.
     */
    private function upsertFacilityPeriod(array $attributes, array $fill): array
    {
        $record = EnergyRecord::query()->firstOrNew(array_merge($attributes, ['meter_id' => null]));
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
