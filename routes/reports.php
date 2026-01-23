<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/modules/reports/index', 'modules.reports.index')->name('reports.index');
    Route::view('/modules/reports/energy', 'modules.reports.energy')->name('reports.energy');
    Route::get('/modules/reports/billing', [\App\Http\Controllers\Reports\BillingReportController::class, 'show'])->name('reports.billing');
    Route::get('/modules/reports/efficiency-summary', [\App\Http\Controllers\Reports\EfficiencySummaryReportController::class, 'show'])->name('reports.efficiency-summary');
    Route::view('/modules/reports/facilities', 'modules.reports.facilities')->name('reports.facilities');
    // Monthly report route for dashboard shortcut
    Route::view('/modules/reports/monthly', 'modules.reports.index')->name('reports.monthly');
    // AJAX endpoint for dashboard summary cards
    Route::get('/modules/reports/dashboard-summary', [\App\Http\Controllers\Reports\DashboardSummaryController::class, 'summary'])->name('reports.dashboard-summary');
});
