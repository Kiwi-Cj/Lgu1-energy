<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submeter;
use App\Models\SubmeterReading;
use App\Services\SubmeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmeterSensorReadingController extends Controller
{
    public function __construct(private readonly SubmeterBaselineAlertService $baselineService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $configuredToken = (string) config('services.submeter_sensor.token', '');
        $requestToken = (string) $request->bearerToken();

        if ($configuredToken === '' || ! hash_equals($configuredToken, $requestToken)) {
            return response()->json([
                'message' => 'Invalid or missing submeter sensor token.',
            ], 401);
        }

        if (! $request->filled('period_start_date') && $request->filled('reading_month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', (string) $request->input('reading_month'))->startOfMonth();
                $request->merge([
                    'period_type' => $request->input('period_type', 'monthly'),
                    'period_start_date' => $month->toDateString(),
                    'period_end_date' => $month->copy()->endOfMonth()->toDateString(),
                ]);
            } catch (\Throwable $e) {
                // Validation will report the missing dates.
            }
        }

        $validated = $request->validate([
            'submeter_id' => 'required|integer|exists:submeters,id',
            'device_id' => 'required|string|max:255',
            'period_type' => 'nullable|in:daily,weekly,monthly',
            'reading_month' => 'nullable|date_format:Y-m',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after_or_equal:period_start_date',
            'reading_start_kwh' => 'required|numeric|min:0',
            'reading_end_kwh' => 'required|numeric|min:0|gte:reading_start_kwh',
            'operating_days' => 'nullable|integer|min:1|max:366',
        ]);

        $submeter = Submeter::with('facility')->findOrFail((int) $validated['submeter_id']);
        if ($submeter->status !== 'active') {
            return response()->json([
                'message' => 'Selected submeter is inactive.',
            ], 422);
        }

        if (! $submeter->facility) {
            return response()->json([
                'message' => 'Selected submeter belongs to an archived facility.',
            ], 422);
        }

        $periodType = $validated['period_type'] ?? 'monthly';
        $duplicate = SubmeterReading::query()
            ->where('submeter_id', $submeter->id)
            ->where('period_type', $periodType)
            ->whereDate('period_start_date', $validated['period_start_date'])
            ->whereDate('period_end_date', $validated['period_end_date'])
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'A submeter reading for the same period already exists.',
            ], 409);
        }

        $reading = SubmeterReading::create([
            'submeter_id' => $submeter->id,
            'period_type' => $periodType,
            'period_start_date' => $validated['period_start_date'],
            'period_end_date' => $validated['period_end_date'],
            'reading_start_kwh' => $validated['reading_start_kwh'],
            'reading_end_kwh' => $validated['reading_end_kwh'],
            'operating_days' => $validated['operating_days'] ?? null,
            'input_source' => 'iot',
            'device_id' => $validated['device_id'],
            'received_at' => now(),
            'approved_at' => now(),
        ]);

        $result = $this->baselineService->processReading($reading->fresh(['submeter.facility']));
        $alert = $result['alert'] ?? null;

        return response()->json([
            'message' => 'Submeter sensor reading received.',
            'reading' => [
                'id' => $reading->id,
                'submeter_id' => $reading->submeter_id,
                'device_id' => $reading->device_id,
                'input_source' => $reading->input_source,
                'period_type' => $reading->period_type,
                'period' => $reading->periodLabel(),
                'kwh_used' => (float) $reading->kwh_used,
                'received_at' => $reading->received_at?->toIso8601String(),
            ],
            'alert_level' => $alert?->alert_level,
        ], 201);
    }
}
