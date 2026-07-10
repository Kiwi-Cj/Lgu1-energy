<?php

use App\Http\Controllers\Api\SubmeterSensorReadingController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'update']);

Route::get('/submeter/sensor-readings', function () {
    return response()->json([
        'message' => 'This endpoint is for submeter sensor submissions. Use POST with JSON data.',
        'method' => 'POST',
        'url' => url('/api/submeter/sensor-readings'),
        'headers' => [
            'Authorization' => 'Bearer demo-submeter-sensor-token',
            'Content-Type' => 'application/json',
        ],
        'sample_body' => [
            'submeter_id' => 1,
            'device_id' => 'SIMULATED-SUBMETER-001',
            'period_type' => 'monthly',
            'reading_month' => now()->format('Y-m'),
            'reading_start_kwh' => 500,
            'reading_end_kwh' => 620,
        ],
    ]);
});

Route::post('/submeter/sensor-readings', [SubmeterSensorReadingController::class, 'store'])
    ->name('api.submeter.sensor-readings.store');
