<?php

use App\Http\Controllers\Modules\AuditLogController;
use App\Http\Controllers\Modules\AiAlertsController;
use App\Http\Controllers\Modules\ContactInboxController;
use App\Http\Controllers\Modules\EnergyController;
use App\Http\Controllers\Modules\EnergyConservationController;
use App\Http\Controllers\Modules\FacilityController;
use App\Http\Controllers\Modules\FacilityMeterController;
use App\Http\Controllers\Modules\MaintenanceController;
use App\Http\Controllers\Modules\LoadTrackingController;
use App\Http\Controllers\Modules\SubmeterMonitoringController;
use App\Support\EnergyCost;
use Illuminate\Support\Facades\Route;

// =====================
// FACILITIES CONTROLLER ROUTES (for named routes)
// =====================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
    Route::redirect('/facilities/create', '/facilities')->name('facilities.create');
    Route::post('/facilities', [FacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/inactive', [FacilityController::class, 'inactive'])->name('facilities.inactive');
    Route::post('/facilities/sync-cprf', [FacilityController::class, 'syncCprf'])->name('facilities.sync-cprf');
    Route::post('/facilities/{id}/reactivate', [FacilityController::class, 'reactivate'])->name('facilities.reactivate');
    Route::post('/facilities/{id}/deactivate', [FacilityController::class, 'deactivate'])->name('facilities.deactivate');
    Route::get('/facilities/{id}', [FacilityController::class, 'show'])->name('facilities.show');
    Route::get('/facilities/{id}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{id}', [FacilityController::class, 'update'])->name('facilities.update');
    Route::delete('/facilities/{id}', [FacilityController::class, 'destroy'])->name('facilities.destroy');
});

// =====================
// MODULE ROUTES (auto-mapped to Blade views)
// =====================
Route::middleware(['auth', 'verified'])->group(function () {
    // Facilities
    Route::get('/modules/facilities/index', [FacilityController::class, 'index'])->name('modules.facilities.index');
    Route::redirect('/modules/facilities/create', '/modules/facilities/index')->name('modules.facilities.create');
    Route::get('/modules/facilities/inactive', [FacilityController::class, 'inactive'])->name('modules.facilities.inactive');
    Route::post('/modules/facilities/{id}/reactivate', [FacilityController::class, 'reactivate'])->name('modules.facilities.reactivate');
    Route::post('/modules/facilities/{id}/deactivate', [FacilityController::class, 'deactivate'])->name('modules.facilities.deactivate');
    Route::get('/modules/facilities/archive', [FacilityController::class, 'archive'])->name('modules.facilities.archive');
    Route::post('/modules/facilities/{id}/restore', [FacilityController::class, 'restore'])->name('modules.facilities.restore');
    Route::delete('/modules/facilities/{id}/force-delete', [FacilityController::class, 'forceDelete'])->name('modules.facilities.force-delete');
    Route::delete('/modules/facilities/{id}', [FacilityController::class, 'destroy'])->name('modules.facilities.destroy');
    Route::get('/modules/facilities/{id}/show', function ($id) {
        $facility = \App\Models\Facility::findOrFail($id);
        $showAvg = false;
        $avgKwh = $facility->baseline_kwh ?? 0;
        return view('modules.facilities.show', compact('facility', 'showAvg', 'avgKwh'));
    })->name('modules.facilities.show');
    Route::get('/modules/facilities/{id}/edit', fn($id) => redirect()->route('modules.facilities.show', ['id' => $id]))->name('modules.facilities.edit');
    // Facility Meters (Main/Sub-meter master data)
    Route::get('/modules/facilities/{facility}/meters', [FacilityMeterController::class, 'index'])->name('modules.facilities.meters.index');
    Route::post('/modules/facilities/{facility}/meters', [FacilityMeterController::class, 'store'])->name('modules.facilities.meters.store');
    Route::put('/modules/facilities/{facility}/meters/{meter}', [FacilityMeterController::class, 'update'])->name('modules.facilities.meters.update');
    Route::delete('/modules/facilities/{facility}/meters/{meter}', [FacilityMeterController::class, 'destroy'])->name('modules.facilities.meters.destroy');
    Route::post('/modules/facilities/{facility}/meters/{meter}/toggle-approval', [FacilityMeterController::class, 'toggleApproval'])->name('modules.facilities.meters.toggle-approval');
    Route::get('/modules/facilities/{facility}/meters/unapproved', [FacilityMeterController::class, 'unapproved'])->name('modules.facilities.meters.unapproved');
    Route::get('/modules/facilities/{facility}/meters/archive', [FacilityMeterController::class, 'archive'])->name('modules.facilities.meters.archive');
    Route::get('/modules/facilities/{facility}/meters/{meter}/submeters', [FacilityMeterController::class, 'mainSubmeters'])->middleware('feature:submeters')->name('modules.facilities.meters.main-submeters');
    Route::post('/modules/facilities/{facility}/meters/{meter}/restore', [FacilityMeterController::class, 'restore'])->name('modules.facilities.meters.restore');
    Route::delete('/modules/facilities/{facility}/meters/{meter}/force-delete', [FacilityMeterController::class, 'forceDelete'])->name('modules.facilities.meters.force-delete');

    // Submeter Monitoring and Alerts
    Route::middleware('feature:submeters')->group(function () {
        Route::get('/modules/submeters/monitoring', [SubmeterMonitoringController::class, 'index'])->name('modules.submeters.monitoring');
        Route::post('/modules/submeters/readings', [SubmeterMonitoringController::class, 'store'])->name('modules.submeters.readings.store');
        Route::post('/modules/submeters/readings/{reading}/approve', [SubmeterMonitoringController::class, 'approve'])->name('modules.submeters.readings.approve');
        Route::get('/modules/submeters/alerts', [SubmeterMonitoringController::class, 'alerts'])->name('modules.submeters.alerts');
        Route::get('/modules/submeters/{submeter}/ai-insight', [SubmeterMonitoringController::class, 'aiInsight'])->name('modules.submeters.ai-insight');
        Route::get('/modules/submeters/{submeter}', [SubmeterMonitoringController::class, 'show'])->name('modules.submeters.show');
    });
    Route::get('/modules/ai-alerts', [AiAlertsController::class, 'index'])->name('modules.ai-alerts.index');
    Route::get('/modules/energy-conservation', [EnergyConservationController::class, 'index'])->name('modules.energy-conservation.index');
    Route::post('/modules/energy-conservation/daily-checklist', [EnergyConservationController::class, 'updateDailyChecklist'])->name('modules.energy-conservation.daily-checklist.update');
    Route::post('/modules/energy-conservation/daily-checklist/tasks', [EnergyConservationController::class, 'storeDailyChecklistTask'])->name('modules.energy-conservation.daily-checklist.tasks.store');
    Route::post('/modules/energy-conservation/daily-checklist/populate-default', [EnergyConservationController::class, 'populateDefaultChecklistTasks'])->name('modules.energy-conservation.daily-checklist.populate-default');
    Route::delete('/modules/energy-conservation/daily-checklist/tasks/{task}', [EnergyConservationController::class, 'destroyDailyChecklistTask'])->name('modules.energy-conservation.daily-checklist.tasks.destroy');
    Route::post('/modules/energy-conservation/goals', [EnergyConservationController::class, 'storeConservationGoal'])->name('modules.energy-conservation.goals.store');
    Route::delete('/modules/energy-conservation/goals/{goal}', [EnergyConservationController::class, 'destroyConservationGoal'])->name('modules.energy-conservation.goals.destroy');
    Route::get('/modules/energy-conservation/{feature}', [EnergyConservationController::class, 'feature'])->name('modules.energy-conservation.feature');
    Route::post('/modules/energy-conservation/energy-saving-tips/review', [EnergyConservationController::class, 'reviewEnergyTip'])->name('modules.energy-conservation.tips.review');
    Route::put('/modules/energy-conservation/energy-saving-tips/{recommendation}', [EnergyConservationController::class, 'updateEnergyTip'])->name('modules.energy-conservation.tips.update');
    Route::patch('/modules/energy-conservation/energy-saving-tips/{recommendation}/progress', [EnergyConservationController::class, 'updateEnergyTipProgress'])->name('modules.energy-conservation.tips.progress');
    Route::delete('/modules/energy-conservation/energy-saving-tips/{recommendation}', [EnergyConservationController::class, 'destroyEnergyTip'])->name('modules.energy-conservation.tips.destroy');

    // Load Tracking & Equipment Energy Computation per Facility
    Route::get('/modules/load-tracking', [LoadTrackingController::class, 'index'])->name('modules.load-tracking.index');
    Route::post('/modules/load-tracking/equipment', [LoadTrackingController::class, 'store'])->name('modules.load-tracking.equipment.store');
    Route::put('/modules/load-tracking/equipment/{id}', [LoadTrackingController::class, 'update'])->name('modules.load-tracking.equipment.update');
    Route::post('/modules/load-tracking/equipment/{id}/toggle-status', [LoadTrackingController::class, 'toggleStatus'])->name('modules.load-tracking.equipment.toggle-status');
    Route::delete('/modules/load-tracking/equipment/{id}', [LoadTrackingController::class, 'destroy'])->name('modules.load-tracking.equipment.destroy');
    Route::get('/modules/load-tracking/export/{facility}', [LoadTrackingController::class, 'export'])
        ->middleware('download.confirmed')
        ->name('modules.load-tracking.export');

    // Monthly Records per Facility
    Route::get('/modules/facilities/{facility}/monthly-records', function (\Illuminate\Http\Request $request, $facilityId) {
        $facility = \App\Models\Facility::find($facilityId);
        if (! $facility) {
            $fallbackFacility = \App\Models\Facility::query()
                ->whereIn('name', ['LGU City Hall Main Building', 'LGU Health Office'])
                ->orderBy('id')
                ->first();

            if ($fallbackFacility) {
                return redirect()
                    ->route('facilities.monthly-records', ['facility' => $fallbackFacility->id])
                    ->with('error', 'Facility ID not found after reseeding. Redirected to the current demo facility.');
            }

            return redirect()
                ->route('modules.facilities.index')
                ->with('error', 'Facility not found.');
        }
        $facilityId = $facility->id;

        $monthLabels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        $resolveCost = static fn ($record): float => EnergyCost::cost($record);

        $meterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->with(['childMeters' => function ($query) {
                $query
                    ->where('meter_type', 'sub')
                    ->whereNotNull('approved_at')
                    ->orderBy('meter_name');
            }])
            ->orderBy('meter_name')
            ->get();

        $mainMeterApprovalStates = \App\Models\FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'main')
            ->get(['id', 'approved_at']);
        $totalMainMeterCount = (int) $mainMeterApprovalStates->count();
        $approvedMainMeterCount = (int) $meterOptions->count();
        $pendingMainMeterCount = (int) $mainMeterApprovalStates
            ->filter(fn ($meter) => empty($meter->approved_at))
            ->count();

        $allRecords = \App\Models\EnergyRecord::with('meter')
            ->where('facility_id', $facilityId)
            ->where(function ($q) {
                $q->whereHas('meter', function ($meterQuery) {
                    $meterQuery->where('meter_type', 'main');
                })->orWhere(function ($q2) {
                    $q2->whereNull('meter_id')->where('input_source', 'cprf');
                });
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('day')
            ->orderByDesc('id')
            ->get();

        $currentYear = (int) date('Y');
        $years = $allRecords->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values();
        if (! $years->contains($currentYear)) {
            $years = $years->push($currentYear)->sortDesc()->values();
        }
        if ($years->isEmpty()) {
            $years = collect([$currentYear]);
        }

        $selectedYear = (int) $request->query('year', $currentYear);
        if (! $years->contains($selectedYear)) {
            $selectedYear = (int) $years->first();
        }

        $summaryMode = strtolower(trim((string) $request->query('summary_mode', 'year')));
        if (! in_array($summaryMode, ['year', 'current', 'month'], true)) {
            $summaryMode = 'year';
        }
        $summaryMonth = (int) $request->query('summary_month', date('n'));
        if ($summaryMonth < 1 || $summaryMonth > 12) {
            $summaryMonth = (int) date('n');
        }

        $effectiveSummaryMonth = null;
        if ($summaryMode === 'current') {
            $effectiveSummaryMonth = (int) date('n');
        } elseif ($summaryMode === 'month') {
            $effectiveSummaryMonth = $summaryMonth;
        }

        $summaryMonthResolved = $effectiveSummaryMonth !== null ? $effectiveSummaryMonth : $summaryMonth;
        $summaryMonthLabel = $monthLabels[$summaryMonthResolved] ?? ('Month ' . $summaryMonthResolved);
        $summaryContextLabel = match ($summaryMode) {
            'current' => 'Current Month (' . $summaryMonthLabel . ' ' . $selectedYear . ')',
            'month' => 'Selected Month (' . $summaryMonthLabel . ' ' . $selectedYear . ')',
            default => 'Year Total (' . $selectedYear . ')',
        };

        $selectedRecordScope = trim((string) $request->query('record_scope', 'main'));
        $scopeLabel = 'Main Meter Records';
        $selectedMeterId = null;
        if (str_starts_with($selectedRecordScope, 'meter:')) {
            $meterId = (int) substr($selectedRecordScope, strlen('meter:'));
            $selectedMeter = $meterOptions->first(fn ($meter) => (int) $meter->id === $meterId);
            if ($selectedMeter && $meterId > 0) {
                $selectedMeterId = $meterId;
                $scopeLabel = strtoupper((string) ($selectedMeter->meter_type ?? 'meter')) . ' - ' . (string) $selectedMeter->meter_name;
                $selectedRecordScope = 'meter:' . $meterId;
            } else {
                $selectedRecordScope = 'main';
            }
        } else {
            $selectedRecordScope = 'main';
        }

        $mainSubScope = trim((string) $request->query('main_sub_scope', 'all'));
        $selectedMainSubMeterId = null;
        $selectedMainMeterForMainSub = null;
        $mainSubScopeLabel = 'All Main Meters';
        if (str_starts_with($mainSubScope, 'main:')) {
            $mainMeterId = (int) substr($mainSubScope, strlen('main:'));
            $selectedMainMeter = $meterOptions->first(fn ($meter) => (int) $meter->id === $mainMeterId);
            if ($selectedMainMeter && $mainMeterId > 0) {
                $selectedMainSubMeterId = $mainMeterId;
                $selectedMainMeterForMainSub = $selectedMainMeter;
                $mainSubScope = 'main:' . $mainMeterId;
                $mainSubScopeLabel = (string) ($selectedMainMeter->meter_name ?? ('Main Meter #' . $mainMeterId));
            } else {
                $mainSubScope = 'all';
            }
        } else {
            $mainSubScope = 'all';
        }

        $recordsForYear = $allRecords
            ->filter(function ($record) use ($selectedYear, $selectedMeterId) {
                if ((int) ($record->year ?? 0) !== $selectedYear) {
                    return false;
                }
                if ($selectedMeterId !== null) {
                    return (int) ($record->meter_id ?? 0) === $selectedMeterId;
                }
                return true;
            })
            ->values();

        $allRecordsForYear = $allRecords
            ->filter(fn ($record) => (int) ($record->year ?? 0) === $selectedYear)
            ->values();

        $recommendationNotificationsByRecordId = collect();
        if (\App\Support\RoleAccess::is(auth()->user(), 'staff')) {
            $recommendationNotificationService = app(\App\Services\RecommendationNotificationService::class);
            $recommendationNotificationsByRecordId = $recordsForYear
                ->mapWithKeys(function ($record) use ($recommendationNotificationService) {
                    $notification = $recommendationNotificationService->ensureForUser(auth()->user(), $record);

                    return $notification ? [(int) $record->id => $notification] : [];
                });
        }

        $allMainRecordsForSummary = $allRecordsForYear->filter(function ($record) use ($effectiveSummaryMonth) {
            if ($effectiveSummaryMonth === null) {
                return true;
            }
            return (int) ($record->month ?? 0) === $effectiveSummaryMonth;
        })->values();

        $mainMeterManualPeriodTotals = $allMainRecordsForSummary
            ->groupBy(fn ($record) => (int) ($record->meter_id ?? 0))
            ->map(fn ($group) => round((float) $group->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2));

        $mainMeterIdsForSensor = $meterOptions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();
        $fallbackSensorMainMeterId = (int) ($mainMeterIdsForSensor->first() ?? 0);
        $resolveSensorMainMeterId = static function ($reading) use ($mainMeterIdsForSensor, $fallbackSensorMainMeterId): int {
            $deviceId = (string) ($reading->device_id ?? '');
            if (preg_match('/FAKE-MAIN-(\d+)/', $deviceId, $matches)) {
                $meterId = (int) ltrim($matches[1], '0');
                if ($meterId > 0 && $mainMeterIdsForSensor->contains($meterId)) {
                    return $meterId;
                }
            }

            return $fallbackSensorMainMeterId;
        };

        $sensorMainRowsForSummary = \App\Models\MainMeterReading::query()
            ->where('facility_id', $facilityId)
            ->where('input_source', 'iot')
            ->whereYear('period_end_date', $selectedYear)
            ->when($effectiveSummaryMonth !== null, fn ($query) => $query->whereMonth('period_end_date', $effectiveSummaryMonth))
            ->get(['id', 'facility_id', 'period_end_date', 'kwh_used', 'device_id']);

        $sensorMainPeriodTotals = $sensorMainRowsForSummary
            ->groupBy(fn ($reading) => $resolveSensorMainMeterId($reading))
            ->map(fn ($group) => round((float) $group->sum(fn ($reading) => (float) ($reading->kwh_used ?? 0)), 2));

        $sensorMainPeriodReadingCounts = $sensorMainRowsForSummary
            ->groupBy(fn ($reading) => $resolveSensorMainMeterId($reading))
            ->map(fn ($group) => $group->count());

        $mainMeterPeriodTotals = $mainMeterIdsForSensor
            ->mapWithKeys(function ($meterId) use ($mainMeterManualPeriodTotals, $sensorMainPeriodTotals, $sensorMainPeriodReadingCounts) {
                $meterId = (int) $meterId;
                $hasSensorReading = (int) ($sensorMainPeriodReadingCounts->get($meterId, 0)) > 0;
                $preferredKwh = $hasSensorReading
                    ? (float) $sensorMainPeriodTotals->get($meterId, 0)
                    : (float) $mainMeterManualPeriodTotals->get($meterId, 0);

                return [$meterId => round($preferredKwh, 2)];
            });

        $mainMeterPreferredSources = $mainMeterIdsForSensor
            ->mapWithKeys(function ($meterId) use ($mainMeterManualPeriodTotals, $sensorMainPeriodReadingCounts) {
                $meterId = (int) $meterId;
                if ((int) ($sensorMainPeriodReadingCounts->get($meterId, 0)) > 0) {
                    return [$meterId => 'Sensor'];
                }
                if ((float) ($mainMeterManualPeriodTotals->get($meterId, 0)) > 0) {
                    return [$meterId => 'Manual'];
                }

                return [$meterId => 'No Data'];
            });

        $submeterPeriodTotalsQuery = \App\Models\EnergyRecord::query()
            ->where('facility_id', $facilityId)
            ->where('year', $selectedYear)
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'sub');
            });
        if ($effectiveSummaryMonth !== null) {
            $submeterPeriodTotalsQuery->where('month', $effectiveSummaryMonth);
        }
        $submeterPeriodTotals = $submeterPeriodTotalsQuery
            ->selectRaw('meter_id, SUM(actual_kwh) as total_kwh')
            ->groupBy('meter_id')
            ->pluck('total_kwh', 'meter_id')
            ->map(fn ($value) => round((float) $value, 2));

        $submeterMonthlyTotals = \App\Models\EnergyRecord::query()
            ->where('facility_id', $facilityId)
            ->where('year', $selectedYear)
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'sub');
            })
            ->selectRaw('month, SUM(actual_kwh) as total_kwh')
            ->groupBy('month')
            ->pluck('total_kwh', 'month')
            ->map(fn ($value) => round((float) $value, 2));

        $mainMeterRecordCount = $allRecordsForYear->count();
        $selectedRecordCount = $recordsForYear->count();
        $selectedActualKwhTotal = round((float) $recordsForYear->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2);
        $selectedCostTotal = round((float) $recordsForYear->sum(fn ($record) => $resolveCost($record)), 2);
        $facilityActualKwhTotal = round((float) $allRecordsForYear->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2);
        $facilityCostTotal = round((float) $allRecordsForYear->sum(fn ($record) => $resolveCost($record)), 2);

        $mainRecordIndex = $allRecords
            ->filter(fn ($record) => ! empty($record->meter_id))
            ->keyBy(fn ($record) => (int) ($record->meter_id ?? 0) . '-' . (int) ($record->year ?? 0) . '-' . (int) ($record->month ?? 0));

        $meterSummaryCards = $recordsForYear
            ->groupBy(fn ($record) => (int) ($record->meter_id ?? 0))
            ->map(function ($group, $meterId) use ($resolveCost) {
                $first = $group->first();

                return [
                    'meter_id' => (int) $meterId,
                    'meter_name' => (string) ($first->meter->meter_name ?? ('Main Meter #' . (int) $meterId)),
                    'meter_number' => (string) ($first->meter->meter_number ?? ''),
                    'record_count' => $group->count(),
                    'total_kwh' => round((float) $group->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2),
                    'total_cost' => round((float) $group->sum(fn ($record) => $resolveCost($record)), 2),
                ];
            })
            ->sortBy('meter_name')
            ->values();

        $monthMeterBreakdown = $recordsForYear
            ->groupBy(fn ($record) => (int) ($record->month ?? 0))
            ->sortKeysDesc()
            ->map(function ($monthGroup, $monthNum) use ($monthLabels, $resolveCost) {
                $meterRows = $monthGroup
                    ->groupBy(fn ($record) => (int) ($record->meter_id ?? 0))
                    ->map(function ($group, $meterId) use ($resolveCost) {
                        $first = $group->first();

                        return [
                            'meter_id' => (int) $meterId,
                            'meter_name' => (string) ($first->meter->meter_name ?? ('Main Meter #' . (int) $meterId)),
                            'meter_number' => (string) ($first->meter->meter_number ?? ''),
                            'record_count' => $group->count(),
                            'total_kwh' => round((float) $group->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2),
                            'total_cost' => round((float) $group->sum(fn ($record) => $resolveCost($record)), 2),
                        ];
                    })
                    ->sortBy('meter_name')
                    ->values();

                return [
                    'month' => (int) $monthNum,
                    'month_label' => $monthLabels[(int) $monthNum] ?? ('Month ' . (int) $monthNum),
                    'record_count' => $monthGroup->count(),
                    'total_kwh' => round((float) $monthGroup->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2),
                    'total_cost' => round((float) $monthGroup->sum(fn ($record) => $resolveCost($record)), 2),
                    'meter_rows' => $meterRows,
                ];
            })
            ->values();

        $mainMeterOrganization = $meterOptions
            ->map(function ($mainMeter) use ($submeterPeriodTotals, $mainMeterPeriodTotals, $mainMeterManualPeriodTotals, $sensorMainPeriodTotals, $mainMeterPreferredSources) {
                $mainMeterId = (int) ($mainMeter->id ?? 0);
                $submeters = collect($mainMeter->childMeters ?? [])
                    ->filter(fn ($sub) => (string) ($sub->meter_type ?? '') === 'sub')
                    ->map(function ($sub) use ($submeterPeriodTotals) {
                        $submeterId = (int) ($sub->id ?? 0);

                        return [
                            'id' => $submeterId,
                            'meter_name' => (string) ($sub->meter_name ?? 'Sub-meter'),
                            'meter_number' => (string) ($sub->meter_number ?? ''),
                            'total_kwh' => round((float) ($submeterPeriodTotals->get($submeterId, 0)), 2),
                        ];
                    })
                    ->sortBy('meter_name')
                    ->values();

                $mainTotalKwh = round((float) ($mainMeterPeriodTotals->get($mainMeterId, 0)), 2);
                $manualTotalKwh = round((float) ($mainMeterManualPeriodTotals->get($mainMeterId, 0)), 2);
                $sensorTotalKwh = round((float) ($sensorMainPeriodTotals->get($mainMeterId, 0)), 2);
                $linkedSubTotalKwh = round((float) $submeters->sum(fn ($item) => (float) ($item['total_kwh'] ?? 0)), 2);

                return [
                    'main_id' => $mainMeterId,
                    'main_name' => (string) ($mainMeter->meter_name ?? 'Main Meter'),
                    'main_number' => (string) ($mainMeter->meter_number ?? ''),
                    'submeters' => $submeters,
                    'submeter_count' => (int) $submeters->count(),
                    'main_total_kwh' => $mainTotalKwh,
                    'manual_total_kwh' => $manualTotalKwh,
                    'sensor_total_kwh' => $sensorTotalKwh,
                    'source_label' => (string) ($mainMeterPreferredSources->get($mainMeterId, 'No Data')),
                    'linked_sub_total_kwh' => $linkedSubTotalKwh,
                    'main_minus_sub_kwh' => round($mainTotalKwh - $linkedSubTotalKwh, 2),
                ];
            })
            ->values();

        if ($selectedMainSubMeterId !== null) {
            $mainMeterOrganization = $mainMeterOrganization
                ->filter(fn ($row) => (int) ($row['main_id'] ?? 0) === $selectedMainSubMeterId)
                ->values();
        }

        $overallMainKwh = round((float) $mainMeterOrganization->sum('main_total_kwh'), 2);
        $overallLinkedSubKwh = round((float) $mainMeterOrganization->sum('linked_sub_total_kwh'), 2);
        $overallMainMinusSubKwh = round($overallMainKwh - $overallLinkedSubKwh, 2);

        $mainMonthlyTotalsSource = $allRecordsForYear->filter(function ($record) use ($selectedMainSubMeterId) {
            if ($selectedMainSubMeterId === null) {
                return true;
            }
            return (int) ($record->meter_id ?? 0) === $selectedMainSubMeterId;
        })->values();

        $mainMonthlyTotals = $mainMonthlyTotalsSource
            ->groupBy(fn ($record) => (int) ($record->month ?? 0))
            ->map(fn ($group) => round((float) $group->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2));

        $sensorMainMonthlyRows = \App\Models\MainMeterReading::query()
            ->where('facility_id', $facilityId)
            ->where('input_source', 'iot')
            ->whereYear('period_end_date', $selectedYear)
            ->get(['period_end_date', 'kwh_used', 'device_id']);

        if ($selectedMainSubMeterId !== null) {
            $sensorMainMonthlyRows = $sensorMainMonthlyRows
                ->filter(fn ($reading) => $resolveSensorMainMeterId($reading) === (int) $selectedMainSubMeterId)
                ->values();
        }

        $sensorMainMonthlyTotals = $sensorMainMonthlyRows
            ->groupBy(fn ($reading) => (int) \Carbon\Carbon::parse($reading->period_end_date)->month)
            ->map(fn ($group) => round((float) $group->sum(fn ($reading) => (float) ($reading->kwh_used ?? 0)), 2));

        foreach ($sensorMainMonthlyTotals as $monthNum => $sensorKwh) {
            $monthNum = (int) $monthNum;
            $mainMonthlyTotals[$monthNum] = round((float) ($mainMonthlyTotals->get($monthNum, 0)) + (float) $sensorKwh, 2);
        }

        $comparisonSubmeterMonthlyTotals = $submeterMonthlyTotals;
        if ($selectedMainSubMeterId !== null) {
            $selectedSubmeterIds = collect($selectedMainMeterForMainSub?->childMeters ?? [])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->values();

            if ($selectedSubmeterIds->isEmpty()) {
                $comparisonSubmeterMonthlyTotals = collect();
            } else {
                $comparisonSubmeterMonthlyTotals = \App\Models\EnergyRecord::query()
                    ->where('facility_id', $facilityId)
                    ->where('year', $selectedYear)
                    ->whereIn('meter_id', $selectedSubmeterIds->all())
                    ->selectRaw('month, SUM(actual_kwh) as total_kwh')
                    ->groupBy('month')
                    ->pluck('total_kwh', 'month')
                    ->map(fn ($value) => round((float) $value, 2));
            }
        }

        $mainSubMonthlyComparison = collect(range(1, 12))
            ->map(function ($monthNum) use ($monthLabels, $mainMonthlyTotals, $comparisonSubmeterMonthlyTotals) {
                $mainKwh = round((float) ($mainMonthlyTotals->get($monthNum, 0)), 2);
                $subKwh = round((float) ($comparisonSubmeterMonthlyTotals->get($monthNum, 0)), 2);

                return [
                    'month' => (int) $monthNum,
                    'month_label' => $monthLabels[(int) $monthNum] ?? ('Month ' . (int) $monthNum),
                    'main_kwh' => $mainKwh,
                    'sub_kwh' => $subKwh,
                    'diff_kwh' => round($mainKwh - $subKwh, 2),
                ];
            })
            ->filter(fn ($row) => (float) $row['main_kwh'] > 0 || (float) $row['sub_kwh'] > 0)
            ->values();

        $latestEnergyProfile = $facility->energyProfiles()->with('primaryMeter')->latest()->first();
        $billingSourceLabel = trim((string) ($latestEnergyProfile?->utility_provider ?? '')) ?: 'Main Meter';
        $primaryBillingMeter = $latestEnergyProfile?->primaryMeter;
        $primaryBillingMeterId = (int) ($latestEnergyProfile?->primary_meter_id ?? 0);
        if ($primaryBillingMeter && empty($primaryBillingMeter->approved_at)) {
            $primaryBillingMeter = null;
            $primaryBillingMeterId = 0;
        }
        $oldMeterId = (string) old('meter_id', $primaryBillingMeterId > 0 ? $primaryBillingMeterId : '');

        $latestMeterDials = [];
        $meterDialTimeline = [];

        // 1. Gather all weekly readings for this facility
        $allWeeklyReadings = \App\Models\FacilityMeterWeeklyReading::query()
            ->where('facility_id', $facilityId)
            ->where(function ($query) {
                $query->whereNotNull('current_reading_kwh')
                    ->orWhereNotNull('previous_reading_kwh')
                    ->orWhere('actual_kwh', '>', 0);
            })
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('week_number')
            ->get(['meter_id', 'year', 'month', 'week_number', 'previous_reading_kwh', 'current_reading_kwh', 'actual_kwh']);

        foreach ($allWeeklyReadings as $w) {
            $mid = (int) ($w->meter_id ?? 0);
            $currDial = $w->current_reading_kwh !== null && is_numeric($w->current_reading_kwh)
                ? (float) $w->current_reading_kwh
                : ($w->previous_reading_kwh !== null && is_numeric($w->previous_reading_kwh) ? (float) $w->previous_reading_kwh + (float) ($w->actual_kwh ?? 0) : null);
            $prevDial = $w->previous_reading_kwh !== null && is_numeric($w->previous_reading_kwh) ? (float) $w->previous_reading_kwh : null;
            $monthName = \Carbon\Carbon::create(2000, (int) $w->month, 1)->format('M');

            $meterDialTimeline[] = [
                'meter_id' => $mid,
                'year' => (int) $w->year,
                'month' => (int) $w->month,
                'week_number' => (int) $w->week_number,
                'order_key' => ((int) $w->year) * 10000 + ((int) $w->month) * 100 + ((int) $w->week_number),
                'period_label' => "Week {$w->week_number}, {$monthName} {$w->year}",
                'current_reading_kwh' => $currDial,
                'previous_reading_kwh' => $prevDial,
                'actual_kwh' => (float) ($w->actual_kwh ?? 0),
            ];
        }

        // 2. Gather monthly energy records for this facility
        $facilityRecords = \App\Models\EnergyRecord::query()
            ->where('facility_id', $facilityId)
            ->where(function ($query) {
                $query->whereNotNull('current_reading_kwh')
                    ->orWhereNotNull('previous_reading_kwh')
                    ->orWhere('actual_kwh', '>', 0);
            })
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('id')
            ->get(['meter_id', 'year', 'month', 'current_reading_kwh', 'previous_reading_kwh', 'actual_kwh', 'input_source']);

        foreach ($facilityRecords as $record) {
            $mid = (int) ($record->meter_id ?? 0);
            $currDial = $record->current_reading_kwh !== null && is_numeric($record->current_reading_kwh)
                ? (float) $record->current_reading_kwh
                : ($record->previous_reading_kwh !== null && is_numeric($record->previous_reading_kwh) ? (float) $record->previous_reading_kwh + (float) ($record->actual_kwh ?? 0) : null);
            $prevDial = $record->previous_reading_kwh !== null && is_numeric($record->previous_reading_kwh) ? (float) $record->previous_reading_kwh : null;
            $monthName = \Carbon\Carbon::create(2000, (int) $record->month, 1)->format('M');

            $meterDialTimeline[] = [
                'meter_id' => $mid,
                'year' => (int) $record->year,
                'month' => (int) $record->month,
                'week_number' => 4, // month end
                'order_key' => ((int) $record->year) * 10000 + ((int) $record->month) * 100 + 4,
                'period_label' => "{$monthName} {$record->year}",
                'current_reading_kwh' => $currDial,
                'previous_reading_kwh' => $prevDial,
                'actual_kwh' => (float) ($record->actual_kwh ?? 0),
            ];
        }

        // 3. Sort timeline descending by order_key to find latest dial per meter
        usort($meterDialTimeline, fn ($a, $b) => $b['order_key'] <=> $a['order_key']);

        foreach ($meterDialTimeline as $item) {
            $mid = $item['meter_id'];
            if (! isset($latestMeterDials[$mid]) && $item['current_reading_kwh'] !== null) {
                $latestMeterDials[$mid] = $item['current_reading_kwh'];
            }
        }
        if (! isset($latestMeterDials[0]) && ! empty($latestMeterDials)) {
            $latestMeterDials[0] = reset($latestMeterDials);
        }

        $timeframe = strtolower(trim((string) $request->query('timeframe', 'monthly')));
        if (! in_array($timeframe, ['monthly', 'weekly'], true)) {
            $timeframe = 'monthly';
        }
        $selectedWeek = (int) $request->query('week', 0);
        if ($selectedWeek < 0 || $selectedWeek > 4) {
            $selectedWeek = 0;
        }

        $daysInMonthForSummary = \Carbon\Carbon::createFromDate($selectedYear, $summaryMonth, 1)->daysInMonth;
        $week4DaysForSummary = max(1, $daysInMonthForSummary - 21);
        $weekDefinitions = [
            1 => ['weight' => 7 / $daysInMonthForSummary, 'label' => 'Week 1 (Days 1–7)', 'short' => 'Week 1', 'days' => '1–7'],
            2 => ['weight' => 7 / $daysInMonthForSummary, 'label' => 'Week 2 (Days 8–14)', 'short' => 'Week 2', 'days' => '8–14'],
            3 => ['weight' => 7 / $daysInMonthForSummary, 'label' => 'Week 3 (Days 15–21)', 'short' => 'Week 3', 'days' => '15–21'],
            4 => ['weight' => $week4DaysForSummary / $daysInMonthForSummary, 'label' => "Week 4 (Days 22–{$daysInMonthForSummary})", 'short' => 'Week 4', 'days' => "22–{$daysInMonthForSummary}"],
        ];

        $weeklyMainMeterRows = collect();
        if ($timeframe === 'weekly') {
            $manualReadings = \App\Models\FacilityMeterWeeklyReading::with(['meter', 'encodedBy'])
                ->where('facility_id', $facilityId)
                ->where('year', $selectedYear)
                ->get()
                ->groupBy(fn ($r) => ((int)$r->meter_id) . '-' . ((int)$r->month));

            // Gather all month/meter combinations from monthly records and manual weekly readings
            $monthMeterKeys = collect();
            foreach ($recordsForYear as $record) {
                $monthMeterKeys->put(((int)$record->meter_id) . '-' . ((int)$record->month), [
                    'meter_id' => (int) $record->meter_id,
                    'month' => (int) $record->month,
                    'year' => (int) $record->year,
                    'monthly_record' => $record,
                ]);
            }
            foreach ($manualReadings as $key => $readingsGroup) {
                if (! $monthMeterKeys->has($key)) {
                    $first = $readingsGroup->first();
                    $monthMeterKeys->put($key, [
                        'meter_id' => (int) $first->meter_id,
                        'month' => (int) $first->month,
                        'year' => (int) $first->year,
                        'monthly_record' => null,
                    ]);
                }
            }

            // Sort by month desc, meter_id asc
            $sortedKeys = $monthMeterKeys->sortByDesc(fn ($item) => sprintf('%04d-%02d-%05d', $item['year'], $item['month'], $item['meter_id']));

            foreach ($sortedKeys as $item) {
                $recMonth = (int) $item['month'];
                $recYear = (int) $item['year'];
                $meterId = (int) $item['meter_id'];
                $record = $item['monthly_record'];
                $key = "{$meterId}-{$recMonth}";
                $manualGroup = $manualReadings->get($key, collect());

                $meter = $record?->meter ?? \App\Models\FacilityMeter::find($meterId);
                $meterName = $meter?->meter_name ?? ($record?->input_source === 'cprf' ? 'Facility-Level (CPRF)' : 'Main Meter');

                $daysInMonth = \Carbon\Carbon::createFromDate($recYear, $recMonth, 1)->daysInMonth;
                $week4Days = max(1, $daysInMonth - 21);

                $recWeekDefs = [
                    1 => ['weight' => 7 / $daysInMonth, 'label' => 'Week 1 (Days 1–7)', 'short' => 'Week 1', 'days' => '1–7'],
                    2 => ['weight' => 7 / $daysInMonth, 'label' => 'Week 2 (Days 8–14)', 'short' => 'Week 2', 'days' => '8–14'],
                    3 => ['weight' => 7 / $daysInMonth, 'label' => 'Week 3 (Days 15–21)', 'short' => 'Week 3', 'days' => '15–21'],
                    4 => ['weight' => $week4Days / $daysInMonth, 'label' => "Week 4 (Days 22–{$daysInMonth})", 'short' => 'Week 4', 'days' => "22–{$daysInMonth}"],
                ];

                $weeksToProcess = ($selectedWeek > 0 && isset($recWeekDefs[$selectedWeek]))
                    ? [$selectedWeek => $recWeekDefs[$selectedWeek]]
                    : $recWeekDefs;

                $actualMonthKwh = $record && is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
                $baselineMonthKwh = ($meter && is_numeric($meter->baseline_kwh))
                    ? (float) $meter->baseline_kwh
                    : ($record && is_numeric($record->baseline_kwh) ? (float) $record->baseline_kwh : null);

                if ($baselineMonthKwh === null) {
                    $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
                    if ($profile && is_numeric($profile->baseline_kwh)) {
                        $baselineMonthKwh = (float) $profile->baseline_kwh;
                    } elseif ($facility && is_numeric($facility->baseline_kwh)) {
                        $baselineMonthKwh = (float) $facility->baseline_kwh;
                    }
                }
                $rate = $record ? \App\Support\EnergyCost::ratePerKwh($record) : 12.00;
                $monthCost = $record ? \App\Support\EnergyCost::cost($record, $rate) : 0;

                foreach ($weeksToProcess as $wNum => $wInfo) {
                    $weight = (float) $wInfo['weight'];
                    $baselineWeekKwh = $baselineMonthKwh !== null ? round($baselineMonthKwh * $weight, 2) : null;

                    $manualWeek = $manualGroup->firstWhere('week_number', $wNum);

                    if ($manualWeek) {
                        $actualWeekKwh = (float) $manualWeek->actual_kwh;
                        $weekRate = (float) $manualWeek->rate_per_kwh;
                        $weekCost = (float) $manualWeek->cost;
                        $isManual = true;
                        $manualReadingId = $manualWeek->id;
                        $manualDate = $manualWeek->reading_date ? $manualWeek->reading_date->format('M d, Y') : null;
                        $manualNotes = $manualWeek->notes;
                        $prevReading = $manualWeek->previous_reading_kwh;
                        $currReading = $manualWeek->current_reading_kwh;
                    } else {
                        // When no actual weekly reading has been logged for this specific week,
                        // do not synthesize or fake-divide the monthly bill into weekly numbers.
                        $actualWeekKwh = null;
                        $weekRate = $rate;
                        $weekCost = 0;
                        $isManual = false;
                        $manualReadingId = null;
                        $manualDate = null;
                        $manualNotes = null;
                        $prevReading = null;
                        $currReading = null;
                    }

                    $deviation = null;
                    if ($actualWeekKwh !== null && $baselineWeekKwh !== null && $baselineWeekKwh > 0) {
                        $deviation = round((($actualWeekKwh - $baselineWeekKwh) / $baselineWeekKwh) * 100, 2);
                    }

                    $alertLevel = 'No Data';
                    if ($deviation !== null && $baselineWeekKwh !== null && $baselineWeekKwh > 0) {
                        $alertLevel = \App\Models\EnergyRecord::resolveAlertLevel($deviation, $baselineWeekKwh);
                    }

                    $weeklyMainMeterRows->push([
                        'week_number' => $wNum,
                        'week_label' => $wInfo['label'],
                        'week_short' => $wInfo['short'],
                        'month' => $recMonth,
                        'month_name' => $monthLabels[$recMonth] ?? ('Month ' . $recMonth),
                        'year' => $recYear,
                        'meter_id' => $meterId,
                        'meter_name' => $meterName,
                        'actual_kwh' => $actualWeekKwh,
                        'baseline_kwh' => $baselineWeekKwh,
                        'deviation' => $deviation,
                        'alert_level' => $alertLevel,
                        'rate' => $weekRate,
                        'cost' => $weekCost,
                        'is_manual' => $isManual,
                        'manual_reading_id' => $manualReadingId,
                        'manual_date' => $manualDate,
                        'manual_notes' => $manualNotes,
                        'previous_reading_kwh' => $prevReading,
                        'current_reading_kwh' => $currReading,
                        'manual_weeks_count' => $manualGroup->count(),
                        'parent_record' => $record,
                    ]);
                }
            }
        }

        $archivedCount = \App\Models\EnergyRecord::onlyTrashed()->where('facility_id', $facilityId)->count();
        $umanConfigured = filled(config('services.uman_monthly_records.url'))
            && filled(config('services.uman_monthly_records.key'));
        $umanSync = \Illuminate\Support\Facades\Cache::get('integrations.uman_monthly_records', []);

        return view('modules.facilities.monthly-record.records', compact(
            'facility',
            'meterOptions',
            'totalMainMeterCount',
            'approvedMainMeterCount',
            'pendingMainMeterCount',
            'selectedRecordScope',
            'scopeLabel',
            'mainSubScope',
            'mainSubScopeLabel',
            'recordsForYear',
            'recommendationNotificationsByRecordId',
            'mainRecordIndex',
            'years',
            'selectedYear',
            'summaryMode',
            'summaryMonth',
            'summaryContextLabel',
            'monthLabels',
            'mainMeterRecordCount',
            'selectedRecordCount',
            'selectedActualKwhTotal',
            'selectedCostTotal',
            'facilityActualKwhTotal',
            'facilityCostTotal',
            'meterSummaryCards',
            'monthMeterBreakdown',
            'mainMeterOrganization',
            'overallMainKwh',
            'overallLinkedSubKwh',
            'overallMainMinusSubKwh',
            'mainSubMonthlyComparison',
            'billingSourceLabel',
            'primaryBillingMeter',
            'oldMeterId',
            'archivedCount',
            'umanConfigured',
            'umanSync',
            'latestMeterDials',
            'meterDialTimeline',
            'timeframe',
            'selectedWeek',
            'weekDefinitions',
            'weeklyMainMeterRows'
        ));
    })->name('facilities.monthly-records');

    $handleSubmeterRecords = function (\Illuminate\Http\Request $request, $facilityId) {
        $facility = \App\Models\Facility::find($facilityId);
        if (! $facility) {
            $fallbackFacility = \App\Models\Facility::query()
                ->whereIn('name', ['LGU City Hall Main Building', 'LGU Health Office'])
                ->orderBy('id')
                ->first();

            if ($fallbackFacility) {
                $targetRoute = $request->routeIs('facilities.weekly-records.submeters')
                    ? 'facilities.weekly-records.submeters'
                    : 'facilities.monthly-records.submeters';

                return redirect()
                    ->route($targetRoute, ['facility' => $fallbackFacility->id])
                    ->with('error', 'Facility ID not found after reseeding. Redirected to the current demo facility.');
            }

            return redirect()
                ->route('modules.facilities.index')
                ->with('error', 'Facility not found.');
        }
        $facilityId = $facility->id;
        $monthLabels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];
        $months = $monthLabels;
        $resolveCost = static fn ($record): float => EnergyCost::cost($record);

        $mainMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->orderBy('meter_name')
            ->get();

        if ($mainMeterOptions->isEmpty()) {
            return redirect()
                ->route('facilities.monthly-records', ['facility' => $facilityId])
                ->with('error', 'Add and approve a Main Meter first before viewing Sub-meter records.');
        }

        $selectedMainMeterId = (int) ($request->query('main_meter_id') ?: 0);
        if ($mainMeterOptions->count() === 1) {
            $selectedMainMeterId = (int) $mainMeterOptions->first()->id;
        } elseif ($selectedMainMeterId <= 0
            || ! $mainMeterOptions->contains(fn ($meter) => (int) $meter->id === $selectedMainMeterId)) {
            $selectedMainMeterId = 0;
        }

        $allSubMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'sub')
            ->whereNotNull('approved_at')
            ->orderBy('meter_name')
            ->get();

        $subMeterOptions = $selectedMainMeterId > 0
            ? $allSubMeterOptions
                ->filter(fn ($meter) => (int) ($meter->parent_meter_id ?? 0) === $selectedMainMeterId)
                ->values()
            : collect();

        $normalizeSubmeterName = static fn (string $name): string => preg_replace('/\s+/', ' ', strtolower(trim($name))) ?? '';
        $submeterNameToIdMap = \App\Models\Submeter::where('facility_id', $facilityId)
            ->get(['id', 'submeter_name'])
            ->mapWithKeys(fn ($submeter) => [$normalizeSubmeterName((string) $submeter->submeter_name) => (int) $submeter->id]);
        $facilityMeterToSubmeterIdMap = $subMeterOptions
            ->mapWithKeys(function ($meter) use ($submeterNameToIdMap, $normalizeSubmeterName) {
                $nameKey = $normalizeSubmeterName((string) ($meter->meter_name ?? ''));

                return [(int) ($meter->id ?? 0) => (int) ($submeterNameToIdMap->get($nameKey) ?? 0)];
            })
            ->filter(fn ($submeterId, $facilityMeterId) => (int) $facilityMeterId > 0 && (int) $submeterId > 0);
        $submeterToFacilityMeterIdMap = $facilityMeterToSubmeterIdMap
            ->mapWithKeys(fn ($submeterId, $facilityMeterId) => [(int) $submeterId => (int) $facilityMeterId]);

        $timeframe = strtolower(trim((string) $request->query('timeframe', $request->routeIs('facilities.weekly-records.submeters') ? 'weekly' : 'monthly')));
        if (! in_array($timeframe, ['monthly', 'weekly'], true)) {
            $timeframe = 'monthly';
        }

        $selectedWeek = (int) ($request->query('week') ?: 0);
        if ($selectedWeek < 0 || $selectedWeek > 4) {
            $selectedWeek = 0;
        }

        $selectedYear = (int) ($request->query('year') ?: date('Y'));
        $selectedMonth = (int) ($request->query('month') ?: 0);
        if ($selectedMonth < 0 || $selectedMonth > 12) {
            $selectedMonth = 0;
        }

        $daysInFilterMonth = $selectedMonth > 0
            ? \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth
            : 31;

        $weekLabels = [
            1 => 'Week 1 (Days 1–7)',
            2 => 'Week 2 (Days 8–14)',
            3 => 'Week 3 (Days 15–21)',
            4 => $selectedMonth > 0 ? "Week 4 (Days 22–{$daysInFilterMonth})" : 'Week 4 (Days 22–End of Month)',
        ];
        $meterIdQuery = $request->query('meter_id');
        $selectedMeterId = ($meterIdQuery === null || $meterIdQuery === '')
            ? (int) ($subMeterOptions->first()->id ?? 0)
            : (int) $meterIdQuery;

        $yearOptions = \App\Models\EnergyRecord::query()
            ->where('facility_id', $facilityId)
            ->whereHas('meter', fn ($q) => $q->where('meter_type', 'sub'))
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->values();

        $sensorYearOptions = collect();
        if ($facilityMeterToSubmeterIdMap->isNotEmpty()) {
            $sensorYearOptions = \App\Models\SubmeterReading::query()
                ->where('period_type', 'monthly')
                ->where('input_source', 'iot')
                ->whereIn('submeter_id', $facilityMeterToSubmeterIdMap->values()->all())
                ->selectRaw('YEAR(period_end_date) as year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->values();
        }

        $yearOptions = $yearOptions
            ->merge($sensorYearOptions)
            ->map(fn ($year) => (int) $year)
            ->filter(fn ($year) => $year > 0)
            ->unique()
            ->sortDesc()
            ->values();

        if ($yearOptions->isEmpty()) {
            $yearOptions = collect([$selectedYear]);
        }

        if (! $yearOptions->contains($selectedYear)) {
            $selectedYear = (int) $yearOptions->first();
        }

        $recordsQuery = \App\Models\EnergyRecord::with(['meter.parentMeter'])
            ->where('facility_id', $facilityId)
            ->where('year', $selectedYear)
            ->whereHas('meter', function ($q) use ($selectedMainMeterId) {
                $q->where('meter_type', 'sub');
                if ($selectedMainMeterId > 0) {
                    $q->where('parent_meter_id', $selectedMainMeterId);
                } else {
                    $q->where('parent_meter_id', -1);
                }
            });

        if ($selectedMonth >= 1 && $selectedMonth <= 12) {
            $recordsQuery->where('month', $selectedMonth);
        }

        if ($selectedMeterId > 0) {
            if ($subMeterOptions->contains(fn ($meter) => (int) $meter->id === $selectedMeterId)) {
                $recordsQuery->where('meter_id', $selectedMeterId);
            } else {
                $selectedMeterId = 0;
            }
        }

        $submeterRecords = $recordsQuery
            ->orderByDesc('month')
            ->orderByDesc('day')
            ->get();

        $selectedFacilityMeterIds = $subMeterOptions->pluck('id')->map(fn ($id) => (int) $id)->values();
        if ($selectedMeterId > 0) {
            $selectedFacilityMeterIds = $selectedFacilityMeterIds->filter(fn ($id) => (int) $id === $selectedMeterId)->values();
        }

        $sensorSubmeterIds = $selectedFacilityMeterIds
            ->map(fn ($facilityMeterId) => (int) ($facilityMeterToSubmeterIdMap->get((int) $facilityMeterId) ?? 0))
            ->filter(fn ($submeterId) => $submeterId > 0)
            ->unique()
            ->values();

        $sensorRows = collect();
        if ($sensorSubmeterIds->isNotEmpty()) {
            $sensorRows = \App\Models\SubmeterReading::query()
                ->with('submeter:id,submeter_name')
                ->where('period_type', 'monthly')
                ->where('input_source', 'iot')
                ->whereIn('submeter_id', $sensorSubmeterIds->all())
                ->whereYear('period_end_date', $selectedYear)
                ->when($selectedMonth >= 1 && $selectedMonth <= 12, fn ($query) => $query->whereMonth('period_end_date', $selectedMonth))
                ->orderByDesc('period_end_date')
                ->get();
        }

        $manualRows = $submeterRecords->toBase()->map(function ($record) use ($resolveCost) {
            $actualKwh = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
            $baselineKwh = is_numeric($record->baseline_kwh)
                ? (float) $record->baseline_kwh
                : (is_numeric($record->meter?->baseline_kwh) ? (float) $record->meter->baseline_kwh : null);
            $deviation = is_numeric($record->deviation)
                ? (float) $record->deviation
                : \App\Models\EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);
            $previousReading = is_numeric($record->previous_reading_kwh) ? (float) $record->previous_reading_kwh : null;
            $currentReading = is_numeric($record->current_reading_kwh) ? (float) $record->current_reading_kwh : null;

            return [
                'id' => (int) ($record->id ?? 0),
                'meter_id' => (int) ($record->meter_id ?? 0),
                'year' => (int) ($record->year ?? 0),
                'month' => (int) ($record->month ?? 0),
                'day' => $record->day ?: '-',
                'meter_name' => (string) ($record->meter?->meter_name ?? '-'),
                'previous_reading_kwh' => $previousReading,
                'current_reading_kwh' => $currentReading,
                'actual_kwh' => $actualKwh,
                'baseline_kwh' => $baselineKwh,
                'deviation' => $deviation,
                'cost' => round((float) $resolveCost($record), 2),
                'source_label' => 'Manual',
            ];
        });

        $sensorPreferredRows = $sensorRows->toBase()->map(function ($reading) use ($submeterToFacilityMeterIdMap, $subMeterOptions) {
            $meterId = (int) ($submeterToFacilityMeterIdMap->get((int) ($reading->submeter_id ?? 0)) ?? 0);
            $meter = $subMeterOptions->first(fn ($option) => (int) ($option->id ?? 0) === $meterId);
            $endDate = $reading->period_end_date ? \Carbon\Carbon::parse($reading->period_end_date) : null;
            $actualKwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : null;
            $baselineKwh = is_numeric($meter?->baseline_kwh) ? (float) $meter->baseline_kwh : null;
            $deviation = \App\Models\EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);

            return [
                'id' => (int) ($reading->id ?? 0),
                'meter_id' => $meterId,
                'year' => $endDate ? (int) $endDate->format('Y') : 0,
                'month' => $endDate ? (int) $endDate->format('n') : 0,
                'day' => $endDate ? (int) $endDate->format('j') : '-',
                'meter_name' => (string) ($meter?->meter_name ?? $reading->submeter?->submeter_name ?? '-'),
                'previous_reading_kwh' => is_numeric($reading->previous_reading_kwh ?? null) ? (float) $reading->previous_reading_kwh : null,
                'current_reading_kwh' => is_numeric($reading->current_reading_kwh ?? null) ? (float) $reading->current_reading_kwh : null,
                'actual_kwh' => $actualKwh,
                'baseline_kwh' => $baselineKwh,
                'deviation' => $deviation,
                'cost' => round(\App\Support\EnergyCost::cost(['actual_kwh' => $actualKwh]), 2),
                'source_label' => 'Sensor',
            ];
        })->filter(fn ($row) => (int) ($row['meter_id'] ?? 0) > 0);

        $sensorKeys = $sensorPreferredRows
            ->mapWithKeys(fn ($row) => [(int) $row['meter_id'] . '-' . (int) $row['year'] . '-' . (int) $row['month'] => true]);

        $preferredRows = $sensorPreferredRows
            ->concat($manualRows->reject(fn ($row) => $sensorKeys->has((int) $row['meter_id'] . '-' . (int) $row['year'] . '-' . (int) $row['month'])))
            ->map(function ($row) {
                $alertLabel = ($row['deviation'] !== null && $row['baseline_kwh'] !== null && $row['baseline_kwh'] > 0)
                    ? \App\Models\EnergyRecord::resolveAlertLevel((float) $row['deviation'], (float) $row['baseline_kwh'])
                    : 'No baseline';
                $alertValue = strtolower($alertLabel);
                $alertColor = '#475569';
                $alertBg = '#f1f5f9';
                if ($alertValue === 'warning') {
                    $alertColor = '#92400e';
                    $alertBg = '#fef3c7';
                } elseif ($alertValue === 'high') {
                    $alertColor = '#9a3412';
                    $alertBg = '#ffedd5';
                } elseif ($alertValue === 'very high') {
                    $alertColor = '#be123c';
                    $alertBg = '#fff1f2';
                } elseif ($alertValue === 'critical') {
                    $alertColor = '#991b1b';
                    $alertBg = '#fee2e2';
                } elseif ($alertValue === 'normal') {
                    $alertColor = '#166534';
                    $alertBg = '#dcfce7';
                }

                $row['alert_label'] = $alertLabel;
                $row['alert_color'] = $alertColor;
                $row['alert_bg'] = $alertBg;

                return $row;
            })
            ->sortByDesc(fn ($row) => sprintf('%04d-%02d-%02d', (int) ($row['year'] ?? 0), (int) ($row['month'] ?? 0), (int) (is_numeric($row['day'] ?? null) ? $row['day'] : 0)))
            ->values();

        if ($timeframe === 'weekly') {
            $weeklyRows = collect();
            foreach ($preferredRows as $row) {
                $prevRunning = $row['previous_reading_kwh'] ?? null;
                $monthKwh = (float) ($row['actual_kwh'] ?? 0);
                $monthBaseline = (float) ($row['baseline_kwh'] ?? 0);
                $monthName = $monthLabels[$row['month']] ?? ('Month ' . $row['month']);
                $currentRunningPrev = $prevRunning !== null ? (float) $prevRunning : null;

                $daysInRowMonth = \Carbon\Carbon::createFromDate((int) ($row['year'] ?? $selectedYear), (int) ($row['month'] ?? 1), 1)->daysInMonth;
                $week4Days = max(1, $daysInRowMonth - 21);

                $weekMultipliers = [
                    1 => ['weight' => 7 / $daysInRowMonth, 'label' => 'Week 1 (Days 1–7)'],
                    2 => ['weight' => 7 / $daysInRowMonth, 'label' => 'Week 2 (Days 8–14)'],
                    3 => ['weight' => 7 / $daysInRowMonth, 'label' => 'Week 3 (Days 15–21)'],
                    4 => ['weight' => $week4Days / $daysInRowMonth, 'label' => "Week 4 (Days 22–{$daysInRowMonth})"],
                ];

                foreach ($weekMultipliers as $wNum => $wData) {
                    $wKwh = round($monthKwh * $wData['weight'], 2);
                    $wBaseline = $monthBaseline > 0 ? round($monthBaseline * $wData['weight'], 2) : null;
                    $wCost = round((float) ($row['cost'] ?? 0) * $wData['weight'], 2);

                    $wPrev = $currentRunningPrev !== null ? round($currentRunningPrev, 2) : null;
                    $wCurr = $wPrev !== null ? round($wPrev + $wKwh, 2) : null;
                    if ($wCurr !== null) {
                        $currentRunningPrev = $wCurr;
                    }

                    $wDeviation = \App\Models\EnergyRecord::calculateDeviation($wKwh, $wBaseline);
                    $alertLabel = ($wDeviation !== null && $wBaseline !== null && $wBaseline > 0)
                        ? \App\Models\EnergyRecord::resolveAlertLevel((float) $wDeviation, (float) $wBaseline)
                        : ($row['alert_label'] ?? 'No baseline');
                    $alertValue = strtolower($alertLabel);
                    $alertColor = '#475569';
                    $alertBg = '#f1f5f9';
                    if ($alertValue === 'warning') {
                        $alertColor = '#92400e';
                        $alertBg = '#fef3c7';
                    } elseif ($alertValue === 'high') {
                        $alertColor = '#9a3412';
                        $alertBg = '#ffedd5';
                    } elseif ($alertValue === 'very high') {
                        $alertColor = '#be123c';
                        $alertBg = '#fff1f2';
                    } elseif ($alertValue === 'critical') {
                        $alertColor = '#991b1b';
                        $alertBg = '#fee2e2';
                    } elseif ($alertValue === 'normal') {
                        $alertColor = '#166534';
                        $alertBg = '#dcfce7';
                    }

                    $wRow = [
                        'id' => $row['id'] . '-w' . $wNum,
                        'meter_id' => $row['meter_id'],
                        'meter_name' => $row['meter_name'],
                        'year' => $row['year'],
                        'month' => $row['month'],
                        'week_num' => $wNum,
                        'week_label' => $wData['label'],
                        'period_display' => $row['year'] . ' ' . $monthName . ' • ' . $wData['label'],
                        'previous_reading_kwh' => $wPrev,
                        'current_reading_kwh' => $wCurr,
                        'actual_kwh' => $wKwh,
                        'baseline_kwh' => $wBaseline,
                        'deviation' => $wDeviation,
                        'cost' => $wCost,
                        'alert_label' => $alertLabel,
                        'alert_color' => $alertColor,
                        'alert_bg' => $alertBg,
                        'source_label' => $row['source_label'] ?? 'Manual',
                    ];

                    if ($selectedWeek === 0 || $selectedWeek === $wNum) {
                        $weeklyRows->push($wRow);
                    }
                }
            }

            $displayRows = $weeklyRows
                ->sortByDesc(fn ($r) => sprintf('%04d-%02d-%02d', (int) ($r['year'] ?? 0), (int) ($r['month'] ?? 0), (int) ($r['week_num'] ?? 0)))
                ->values();
        } else {
            $displayRows = $preferredRows;
        }

        $submeterGroups = $displayRows
            ->groupBy(fn ($row) => (int) ($row['meter_id'] ?? 0))
            ->map(function ($groupRows, $meterId) {
                $firstRow = $groupRows->first();

                return [
                    'meter_id' => (int) $meterId,
                    'meter_name' => (string) ($firstRow['meter_name'] ?? 'Unknown Sub-meter'),
                    'record_count' => (int) $groupRows->count(),
                    'total_kwh' => round((float) $groupRows->sum(fn ($row) => (float) ($row['actual_kwh'] ?? 0)), 2),
                    'total_cost' => round((float) $groupRows->sum(fn ($row) => (float) ($row['cost'] ?? 0)), 2),
                    'records' => $groupRows->values(),
                ];
            })
            ->sortBy(fn ($group) => strtolower((string) ($group['meter_name'] ?? '')))
            ->values();

        $totalKwh = round((float) $displayRows->sum(fn ($row) => (float) ($row['actual_kwh'] ?? 0)), 2);
        $totalCost = round((float) $displayRows->sum(fn ($row) => (float) ($row['cost'] ?? 0)), 2);
        $totalRecords = (int) $displayRows->count();
        $latestSubmeterDials = [];
        foreach ($subMeterOptions as $subMeter) {
            $latestRecord = \App\Models\EnergyRecord::where('facility_id', $facilityId)
                ->where('meter_id', $subMeter->id)
                ->whereNotNull('current_reading_kwh')
                ->where('current_reading_kwh', '>', 0)
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->first();
            $latestSubmeterDials[$subMeter->id] = $latestRecord ? (float) $latestRecord->current_reading_kwh : null;
        }

        return view('modules.facilities.monthly-record.submeter-records', compact(
            'facility',
            'mainMeterOptions',
            'subMeterOptions',
            'submeterGroups',
            'selectedYear',
            'selectedMonth',
            'selectedWeek',
            'selectedMainMeterId',
            'selectedMeterId',
            'yearOptions',
            'monthLabels',
            'months',
            'weekLabels',
            'timeframe',
            'totalKwh',
            'totalCost',
            'totalRecords',
            'latestSubmeterDials'
        ));
    };

    Route::get('/modules/facilities/{facility}/monthly-records/submeters', $handleSubmeterRecords)
        ->middleware('feature:submeters')
        ->name('facilities.monthly-records.submeters');

    Route::get('/modules/facilities/{facility}/weekly-records/submeters', $handleSubmeterRecords)
        ->middleware('feature:submeters')
        ->name('facilities.weekly-records.submeters');

    Route::get('/modules/facilities/{facility}/monthly-records/archive', function ($facilityId) {
        $facility = \App\Models\Facility::find($facilityId);
        if (! $facility) {
            $fallbackFacility = \App\Models\Facility::query()
                ->whereIn('name', ['LGU City Hall Main Building', 'LGU Health Office'])
                ->orderBy('id')
                ->first();

            if ($fallbackFacility) {
                return redirect()
                    ->route('facilities.monthly-records.archive', ['facility' => $fallbackFacility->id])
                    ->with('error', 'Facility ID not found after reseeding. Redirected to the current demo facility.');
            }

            return redirect()
                ->route('modules.facilities.index')
                ->with('error', 'Facility not found.');
        }
        $facilityId = $facility->id;
        $archivedRecords = \App\Models\EnergyRecord::onlyTrashed()
            ->with('meter')
            ->where('facility_id', $facilityId)
            ->orderByDesc('deleted_at')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('modules.facilities.monthly-record.archive', compact('facility', 'archivedRecords'));
    })->name('facilities.monthly-records.archive');

    // Maintenance
    Route::get('/modules/maintenance/index', [MaintenanceController::class, 'index'])->name('modules.maintenance.index');
    Route::get('/modules/maintenance/create', [MaintenanceController::class, 'create'])->name('modules.maintenance.create');
    Route::get('/modules/maintenance/schedule', fn() => redirect()->route('modules.maintenance.index'))->name('modules.maintenance.schedule');
    Route::post('/modules/maintenance/schedule', [MaintenanceController::class, 'store'])->name('modules.maintenance.schedule');

    // Reports
    Route::get('/modules/reports/energy', [EnergyController::class, 'energyReport'])->name('modules.reports.energy');

    // Users - Admin/Energy Officer only (Staff blocked via controller)
    Route::get('/modules/users/roles', [\App\Http\Controllers\Modules\UsersController::class, 'roles'])->name('modules.users.roles');
    Route::get('/modules/audit/index', [AuditLogController::class, 'index'])->name('modules.audit.index');
    Route::get('/modules/contact-messages', [ContactInboxController::class, 'index'])->name('modules.contact-messages.index');
    Route::post('/modules/contact-messages/{contactMessage}/mark-read', [ContactInboxController::class, 'markRead'])->name('modules.contact-messages.mark-read');
    Route::post('/modules/contact-messages/{contactMessage}/mark-unread', [ContactInboxController::class, 'markUnread'])->name('modules.contact-messages.mark-unread');
    Route::post('/modules/contact-messages/{contactMessage}/archive', [ContactInboxController::class, 'archive'])->name('modules.contact-messages.archive');
    Route::post('/modules/contact-messages/{contactMessage}/restore', [ContactInboxController::class, 'restore'])->name('modules.contact-messages.restore');
    Route::delete('/modules/contact-messages/{contactMessage}', [ContactInboxController::class, 'destroy'])->name('modules.contact-messages.destroy');
    Route::post('/modules/contact-messages/{contactMessage}/reply', [ContactInboxController::class, 'reply'])->name('modules.contact-messages.reply');

});

// =====================
// FACILITIES ENERGY PROFILE ROUTES
// =====================
Route::middleware(['auth', 'verified'])->group(function () {
    // Energy Profile per Facility
    Route::get('/modules/facilities/{facility}/energy-profile', function ($facility) {
        $facilityModel = \App\Models\Facility::find($facility);
        if (! $facilityModel) {
            $fallbackFacility = \App\Models\Facility::query()
                ->whereIn('name', ['LGU City Hall Main Building', 'LGU Health Office'])
                ->orderBy('id')
                ->first();

            if ($fallbackFacility) {
                return redirect()
                    ->route('modules.facilities.energy-profile.index', ['facility' => $fallbackFacility->id])
                    ->with('error', 'Facility ID not found after reseeding. Redirected to the current demo facility energy profile.');
            }

            return redirect()
                ->route('modules.facilities.index')
                ->with('error', 'Facility not found.');
        }
        $user = auth()->user();
        $submetersEnabled = (bool) config('features.submeters_enabled', false);
        $energyProfiles = $facilityModel->energyProfiles()->with('primaryMeter')->get();
        $mainMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->orderBy('meter_name')
            ->get(['id', 'meter_name', 'meter_number', 'baseline_kwh']);
        $mainMeters = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->get(['id', 'facility_id', 'meter_name', 'meter_number', 'meter_type', 'parent_meter_id', 'location', 'status', 'multiplier', 'baseline_kwh', 'notes', 'approved_by_user_id', 'approved_at', 'created_at']);
        $subMeterOptions = $submetersEnabled
            ? \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
                ->where('meter_type', 'sub')
                ->whereNotNull('approved_at')
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('meter_name')
                ->get(['id', 'meter_name', 'meter_number', 'meter_type', 'parent_meter_id', 'location', 'status', 'multiplier', 'baseline_kwh', 'notes', 'approved_by_user_id', 'approved_at', 'created_at'])
            : collect();
        $subMetersByParentMainId = $subMeterOptions
            ->filter(fn ($meter) => ! empty($meter->parent_meter_id))
            ->groupBy(fn ($meter) => (int) $meter->parent_meter_id);
        $normalizeName = function (string $name): string {
            return strtolower((string) preg_replace('/\s+/', ' ', trim($name)));
        };
        $submeterNameToIdMap = $submetersEnabled
            ? \App\Models\Submeter::where('facility_id', $facilityModel->id)
                ->where('status', 'active')
                ->get(['id', 'submeter_name'])
                ->mapWithKeys(function ($submeter) use ($normalizeName) {
                    return [$normalizeName((string) $submeter->submeter_name) => (int) $submeter->id];
                })
            : collect();
        $subMeterEntityIdMap = $subMeterOptions->mapWithKeys(function ($meter) use ($submeterNameToIdMap, $normalizeName) {
            $nameKey = $normalizeName((string) $meter->meter_name);
            $linkedSubmeterId = $submeterNameToIdMap->get($nameKey);

            return [(int) $meter->id => $linkedSubmeterId ? (int) $linkedSubmeterId : null];
        });
        $submeterToFacilityMeterIdMap = $subMeterEntityIdMap
            ->filter(fn ($submeterId) => ! empty($submeterId))
            ->mapWithKeys(fn ($submeterId, $facilityMeterId) => [(int) $submeterId => (int) $facilityMeterId]);
        $facilityMainMeterIds = $mainMeters->pluck('id')->map(fn ($id) => (int) $id)->all();
        $facilitySubmeterIds = $subMeterEntityIdMap
            ->filter(fn ($id) => ! empty($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $equipmentByMeterKey = collect();
        if (! empty($facilityMainMeterIds) || ! empty($facilitySubmeterIds)) {
            $equipmentByMeterKey = \App\Models\SubmeterEquipment::query()
                ->where(function ($query) use ($facilityMainMeterIds, $facilitySubmeterIds) {
                    $hasCondition = false;
                    if (! empty($facilityMainMeterIds)) {
                        $query->where(function ($mainQuery) use ($facilityMainMeterIds) {
                            $mainQuery->where('meter_scope', 'main')
                                ->whereIn('facility_meter_id', $facilityMainMeterIds);
                        });
                        $hasCondition = true;
                    }
                    if (! empty($facilitySubmeterIds)) {
                        $method = $hasCondition ? 'orWhere' : 'where';
                        $query->{$method}(function ($subQuery) use ($facilitySubmeterIds) {
                            $subQuery->where('meter_scope', 'sub')
                                ->whereIn('submeter_id', $facilitySubmeterIds);
                        });
                    }
                })
                ->orderByDesc('estimated_kwh')
                ->get([
                    'id',
                    'meter_scope',
                    'submeter_id',
                    'facility_meter_id',
                    'equipment_name',
                    'quantity',
                    'rated_watts',
                    'operating_hours_per_day',
                    'operating_days_per_month',
                    'estimated_kwh',
                ])
                ->groupBy(function ($equipment) use ($submeterToFacilityMeterIdMap) {
                    $scope = strtolower((string) ($equipment->meter_scope ?? 'sub'));
                    if ($scope === 'main') {
                        return 'main:' . (int) ($equipment->facility_meter_id ?? 0);
                    }

                    $facilityMeterId = (int) ($submeterToFacilityMeterIdMap->get((int) ($equipment->submeter_id ?? 0)) ?? 0);
                    return $facilityMeterId > 0 ? 'sub:' . $facilityMeterId : 'unmapped';
                })
                ->filter(fn ($group, $key) => $key !== 'unmapped')
                ->map(function ($group) {
                    return $group->values()->map(function ($equipment) {
                        $quantity = (int) ($equipment->quantity ?? 0);
                        $ratedWatts = (float) ($equipment->rated_watts ?? 0);

                        return [
                            'id' => (int) $equipment->id,
                            'name' => (string) ($equipment->equipment_name ?? 'Equipment'),
                            'quantity' => $quantity,
                            'rated_watts' => round($ratedWatts, 2),
                            'operating_hours_per_day' => round((float) ($equipment->operating_hours_per_day ?? 0), 2),
                            'operating_days_per_month' => (int) ($equipment->operating_days_per_month ?? 0),
                            'total_watts' => round($ratedWatts * max(0, $quantity), 2),
                            'estimated_kwh' => round((float) ($equipment->estimated_kwh ?? 0), 2),
                        ];
                    })->all();
                });
        }
        $parentMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->orderByRaw("CASE WHEN meter_type = 'main' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->get(['id', 'meter_name', 'meter_type']);
        $activeMeterCount = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->when(! $submetersEnabled, fn ($query) => $query->where('meter_type', 'main'))
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->count();
        $activeMainMeterCount = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'main')
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->count();
        $subMeterCount = $submetersEnabled
            ? \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
                ->where('meter_type', 'sub')
                ->whereNotNull('approved_at')
                ->count()
            : 0;
        $unapprovedMeterCount = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->when(! $submetersEnabled, fn ($query) => $query->where('meter_type', 'main'))
            ->whereNull('approved_at')
            ->count();
        $archivedMeterCount = \App\Models\FacilityMeter::onlyTrashed()
            ->where('facility_id', $facilityModel->id)
            ->when(! $submetersEnabled, fn ($query) => $query->where('meter_type', 'main'))
            ->count();
        $canManageMeters = \App\Support\RoleAccess::can($user, 'manage_facility_master');
        $canManageEnergyProfile = \App\Support\RoleAccess::can($user, 'manage_energy_profile');
        $canApproveMeters = \App\Support\RoleAccess::can($user, 'approve_facility_meters');
        $canEncodeMainReadings = \App\Support\RoleAccess::can($user, 'encode_main_meter_readings');
        $latestEnergyRecord = \App\Models\EnergyRecord::query()
            ->where('facility_id', $facilityModel->id)
            ->where(function ($mainScope) {
                $mainScope->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first(['id', 'facility_id', 'year', 'month', 'actual_kwh', 'baseline_kwh', 'input_source']);
        $baselineEstablishmentService = app(\App\Services\MainMeterBaselineEstablishmentService::class);
        $baselinePlans = $mainMeters->mapWithKeys(
            fn ($meter) => [(int) $meter->id => $baselineEstablishmentService->summary($meter)]
        );
        $historicalMonthlyReadings = \App\Models\EnergyRecord::query()
            ->where('facility_id', $facilityModel->id)
            ->where('review_status', 'approved')
            ->whereNotNull('actual_kwh')
            ->where('actual_kwh', '>', 0)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get(['year', 'month', 'actual_kwh'])
            ->unique(fn ($r) => sprintf('%04d-%02d', $r->year, $r->month))
            ->take(6)
            ->sortBy(fn ($r) => sprintf('%04d-%02d', $r->year, $r->month))
            ->values();

        $facilityEquipments = \App\Models\SubmeterEquipment::query()
            ->where(function ($q) use ($facilityModel) {
                $q->where('facility_id', $facilityModel->id)
                    ->orWhereHas('mainMeter', fn ($m) => $m->where('facility_id', $facilityModel->id))
                    ->orWhereHas('submeter', fn ($s) => $s->where('facility_id', $facilityModel->id));
            })
            ->orderByDesc('rated_watts')
            ->get();

        return view('modules.facilities.energy-profile.index', compact(
            'facilityModel',
            'energyProfiles',
            'mainMeterOptions',
            'mainMeters',
            'subMeterOptions',
            'subMeterEntityIdMap',
            'subMetersByParentMainId',
            'equipmentByMeterKey',
            'parentMeterOptions',
            'activeMeterCount',
            'activeMainMeterCount',
            'subMeterCount',
            'unapprovedMeterCount',
            'archivedMeterCount',
            'canManageMeters',
            'canManageEnergyProfile',
            'canApproveMeters',
            'canEncodeMainReadings',
            'latestEnergyRecord',
            'baselinePlans',
            'historicalMonthlyReadings',
            'facilityEquipments'
        ));
    })->name('modules.facilities.energy-profile.index');

    // Store new energy profile (controller-based)
    Route::post('/modules/facilities/{facility}/energy-profile', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'store'])->name('modules.facilities.energy-profile.store');

    // Update energy profile
    Route::match(['put', 'patch'], '/modules/facilities/{facility}/energy-profile/{profile}', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'update'])
        ->name('modules.facilities.energy-profile.update');

    // Toggle engineer approval for energy profile
    Route::post('/modules/facilities/{facility}/energy-profile/{profile}/toggle-approval', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'toggleEngineerApproval'])->name('energy-profile.toggle-approval');

    Route::post('/modules/facilities/{facility}/meters/{meter}/baseline/establish', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'establishBaseline'])
        ->name('modules.facilities.meters.baseline.establish');

    // Delete energy profile (controller, like monthly record)
    Route::delete('/modules/facilities/{facility}/energy-profile/{profile}', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'destroy'])
        ->name('modules.facilities.energy-profile.destroy');

    // Fallback for DELETE without profile id (returns 405)
    Route::delete('/modules/facilities/{facility}/energy-profile', function () {
        abort(405, 'Profile ID required for delete.');
    });

    // ============================================================
    // UTILITY CASH FLOW & BUDGET TRACKING
    // ============================================================
    Route::get('/modules/cashflow', [\App\Http\Controllers\Modules\CashFlowController::class, 'index'])->name('modules.cashflow.index');
    Route::post('/modules/cashflow/budget', [\App\Http\Controllers\Modules\CashFlowController::class, 'updateBudget'])->name('modules.cashflow.budget.update');
    Route::get('/modules/cashflow/export', [\App\Http\Controllers\Modules\CashFlowController::class, 'export'])
        ->middleware('download.confirmed')
        ->name('modules.cashflow.export');
});
