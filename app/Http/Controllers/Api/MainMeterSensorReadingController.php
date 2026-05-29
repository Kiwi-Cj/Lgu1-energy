<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MainMeterReading;
use App\Services\MainMeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MainMeterSensorReadingController extends Controller
{
    public function __construct(private readonly MainMeterBaselineAlertService $baselineService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $configuredToken = (string) config('services.main_meter_sensor.token', '');
        $requestToken = (string) $request->bearerToken();

        if ($configuredToken === '' || ! hash_equals($configuredToken, $requestToken)) {
            return response()->json([
                'message' => 'Invalid or missing sensor token.',
            ], 401);
        }

        if (! $request->filled('period_start_date') && $request->filled('reading_month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', (string) $request->input('reading_month'))->startOfMonth();
                $request->merge([
                    'period_start_date' => $month->toDateString(),
                    'period_end_date' => $month->copy()->endOfMonth()->toDateString(),
                ]);
            } catch (\Throwable $e) {
                // Validation will report the missing dates.
            }
        }

        $validated = $request->validate([
            'facility_id' => 'required|integer|exists:facilities,id',
            'device_id' => 'required|string|max:255',
            'reading_month' => 'nullable|date_format:Y-m',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after_or_equal:period_start_date',
            'reading_start_kwh' => 'required|numeric|min:0',
            'reading_end_kwh' => 'required|numeric|min:0|gte:reading_start_kwh',
            'operating_days' => 'nullable|integer|min:1|max:366',
            'peak_demand_kw' => 'nullable|numeric|min:0',
            'power_factor' => 'nullable|numeric|min:0|max:1',
        ]);

        $duplicate = MainMeterReading::query()
            ->where('facility_id', (int) $validated['facility_id'])
            ->where('period_type', 'monthly')
            ->whereDate('period_start_date', $validated['period_start_date'])
            ->whereDate('period_end_date', $validated['period_end_date'])
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'A main meter reading for the same period already exists.',
            ], 409);
        }

        $reading = MainMeterReading::create([
            'facility_id' => (int) $validated['facility_id'],
            'period_type' => 'monthly',
            'period_start_date' => $validated['period_start_date'],
            'period_end_date' => $validated['period_end_date'],
            'reading_start_kwh' => $validated['reading_start_kwh'],
            'reading_end_kwh' => $validated['reading_end_kwh'],
            'operating_days' => $validated['operating_days'] ?? null,
            'peak_demand_kw' => $validated['peak_demand_kw'] ?? null,
            'power_factor' => $validated['power_factor'] ?? null,
            'input_source' => 'iot',
            'device_id' => $validated['device_id'],
            'received_at' => now(),
            'approved_at' => now(),
        ]);

        $result = $this->baselineService->processReading($reading->fresh(['facility']));
        $alert = $result['alert'] ?? null;

        return response()->json([
            'message' => 'Main meter sensor reading received.',
            'reading' => [
                'id' => $reading->id,
                'facility_id' => $reading->facility_id,
                'device_id' => $reading->device_id,
                'input_source' => $reading->input_source,
                'period' => $reading->periodLabel(),
                'kwh_used' => (float) $reading->kwh_used,
                'received_at' => $reading->received_at?->toIso8601String(),
            ],
            'alert_level' => $alert?->alert_level,
        ], 201);
    }
}
