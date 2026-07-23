<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use Carbon\Carbon;
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

        $record = EnergyRecord::query()->firstOrNew([
            'facility_id' => $facility->id,
            'meter_id' => null,
            'year' => (int) $validated['year'],
            'month' => (int) $validated['month'],
        ]);
        $wasExisting = $record->exists;

        $record->fill([
            'day' => Carbon::parse($validated['reading_date'])->day,
            'actual_kwh' => $actualKwh,
            'baseline_kwh' => $baseline,
            'deviation' => $deviation,
            'alert' => $alert,
            'energy_cost' => $validated['energy_cost'] ?? null,
            'rate_per_kwh' => $validated['rate_per_kwh'] ?? null,
            'input_source' => 'cprf',
        ]);
        $record->save();

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
}
