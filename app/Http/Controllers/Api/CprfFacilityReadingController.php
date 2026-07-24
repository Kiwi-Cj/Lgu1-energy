<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
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
        ]);

        /** @var Facility $facility */
        $facility = Facility::query()->findOrFail((int) $validated['facility_id']);

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
            'energy_cost' => $validated['energy_cost'] ?? null,
            'rate_per_kwh' => $validated['rate_per_kwh'] ?? null,
            'input_source' => 'cprf',
            // Explicit, not omitted: a CPRF-pushed reading has no energy-app
            // user to attribute it to. Leaving this out of the insert made
            // MySQL strict mode reject the row ("doesn't have a default
            // value") whenever recorded_by has no column default.
            'recorded_by' => null,
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

        // notes / external_ref / recorded_by_name have no energy_records
        // columns; keep them in the log for traceability.
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
