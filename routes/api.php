<?php

use App\Http\Controllers\Api\SubmeterSensorReadingController;
use App\Http\Controllers\Api\IntegrationDataController;
use App\Http\Controllers\Api\CprfFacilityReadingController;
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

Route::prefix('v1')->middleware(['integration.api', 'throttle:60,1'])->group(function () {
    Route::get('/summary', [IntegrationDataController::class, 'summary']);
    Route::get('/facilities', [IntegrationDataController::class, 'facilities']);
    Route::get('/meters', [IntegrationDataController::class, 'meters']);
    Route::get('/energy-records', [IntegrationDataController::class, 'energyRecords']);
    Route::get('/incidents', [IntegrationDataController::class, 'incidents']);
    Route::get('/maintenance', [IntegrationDataController::class, 'maintenance']);
});

// CIMM <-> Energy maintenance sync integration (Facilities Needing Maintenance
// page). Kept on its own prefix/token (services.cimm_maintenance_sync)
// instead of reusing 'integration.api' above: that token already gates
// several unrelated read endpoints and may have a real secret configured
// elsewhere, while this token is scoped to just this integration and
// defaults to a shared dev key so the sync works out of the box. Read
// endpoints reuse the same maintenance()/maintenanceHistory() controller
// methods as their /api/v1/... counterparts -- only the auth differs.
Route::prefix('v1/cimm-maintenance-sync')->middleware(['cimm.maintenance.sync', 'throttle:60,1'])->group(function () {
    Route::get('/maintenance', [IntegrationDataController::class, 'maintenance']);
    Route::get('/maintenance-history', [IntegrationDataController::class, 'maintenanceHistory']);
    Route::post('/maintenance/{id}/sync', [IntegrationDataController::class, 'updateMaintenance']);
});

// CPRF (facilities reservation) <-> Energy integration. CPRF pushes manual
// facility meter readings in and pulls facilities/recommendations out. Same
// per-partner token pattern as the CIMM group above (services.cprf_integration).
// GET endpoints reuse IntegrationDataController methods -- only the auth differs.
Route::prefix('v1/cprf')->middleware(['cprf.integration', 'throttle:60,1'])->group(function () {
    Route::get('/facilities', [IntegrationDataController::class, 'facilities']);
    Route::get('/recommendations', [IntegrationDataController::class, 'recommendations']);
    Route::post('/facility-readings', [CprfFacilityReadingController::class, 'store']);
});
