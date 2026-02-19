<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Modules\EnergyIncidentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/modules/energy-incidents', [EnergyIncidentController::class, 'index'])->name('energy-incidents.index');
    // Removed manual incident creation routes
    Route::get('/modules/energy-incidents/history', [EnergyIncidentController::class, 'history'])->name('energy-incidents.history');
    Route::get('/modules/energy-incidents/{energyIncident}', [EnergyIncidentController::class, 'show'])->name('energy-incidents.show');
    Route::get('/modules/energy-incidents/{energyIncident}/edit', [EnergyIncidentController::class, 'edit'])->name('energy-incidents.edit');
    Route::put('/modules/energy-incidents/{energyIncident}', [EnergyIncidentController::class, 'update'])->name('energy-incidents.update');
});
