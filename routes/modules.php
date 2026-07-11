<?php

use App\Http\Controllers\Modules\AuditLogController;
use App\Http\Controllers\Modules\ContactInboxController;
use App\Http\Controllers\Modules\EnergyController;
use App\Http\Controllers\Modules\EnergyConservationController;
use App\Http\Controllers\Modules\FacilityController;
use App\Http\Controllers\Modules\FacilityMeterController;
use App\Http\Controllers\Modules\MaintenanceController;
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
    Route::get('/modules/facilities/archive', [FacilityController::class, 'archive'])->name('modules.facilities.archive');
    Route::post('/modules/facilities/{id}/restore', [FacilityController::class, 'restore'])->name('modules.facilities.restore');
    Route::delete('/modules/facilities/{id}/force-delete', [FacilityController::class, 'forceDelete'])->name('modules.facilities.force-delete');
    Route::get('/modules/facilities/{id}/show', function ($id) {
        $facility = \App\Models\Facility::findOrFail($id);
        $showAvg = false;
        $avgKwh = $facility->baseline_kwh ?? 0;
        return view('modules.facilities.show', compact('facility', 'showAvg', 'avgKwh'));
    })->name('modules.facilities.show');
    Route::get('/modules/facilities/{id}/edit', fn($id) => redirect()->route('modules.facilities.show', ['id' => $id]))->name('modules.facilities.edit');
    Route::get('/modules/facilities/{facility}/equipment-inventory', [FacilityController::class, 'equipmentInventory'])->name('modules.facilities.equipment-inventory');

    // Facility Meters (Main/Sub-meter master data)
    Route::get('/modules/facilities/{facility}/meters', [FacilityMeterController::class, 'index'])->name('modules.facilities.meters.index');
    Route::post('/modules/facilities/{facility}/meters', [FacilityMeterController::class, 'store'])->name('modules.facilities.meters.store');
    Route::put('/modules/facilities/{facility}/meters/{meter}', [FacilityMeterController::class, 'update'])->name('modules.facilities.meters.update');
    Route::delete('/modules/facilities/{facility}/meters/{meter}', [FacilityMeterController::class, 'destroy'])->name('modules.facilities.meters.destroy');
    Route::post('/modules/facilities/{facility}/meters/{meter}/toggle-approval', [FacilityMeterController::class, 'toggleApproval'])->name('modules.facilities.meters.toggle-approval');
    Route::get('/modules/facilities/{facility}/meters/unapproved', [FacilityMeterController::class, 'unapproved'])->name('modules.facilities.meters.unapproved');
    Route::get('/modules/facilities/{facility}/meters/archive', [FacilityMeterController::class, 'archive'])->name('modules.facilities.meters.archive');
    Route::get('/modules/facilities/{facility}/meters/{meter}/submeters', [FacilityMeterController::class, 'mainSubmeters'])->name('modules.facilities.meters.main-submeters');
    Route::get('/modules/facilities/{facility}/meters/{meter}/equipment', [FacilityMeterController::class, 'submeterEquipment'])->name('modules.facilities.meters.submeter-equipment');
    Route::post('/modules/facilities/{facility}/meters/{meter}/equipment', [FacilityMeterController::class, 'storeSubmeterEquipment'])->name('modules.facilities.meters.submeter-equipment.store');
    Route::post('/modules/facilities/{facility}/meters/{meter}/restore', [FacilityMeterController::class, 'restore'])->name('modules.facilities.meters.restore');
    Route::delete('/modules/facilities/{facility}/meters/{meter}/force-delete', [FacilityMeterController::class, 'forceDelete'])->name('modules.facilities.meters.force-delete');

    // Submeter Monitoring and Alerts
    Route::get('/modules/submeters/monitoring', [SubmeterMonitoringController::class, 'index'])->name('modules.submeters.monitoring');
    Route::post('/modules/submeters/readings', [SubmeterMonitoringController::class, 'store'])->name('modules.submeters.readings.store');
    Route::post('/modules/submeters/readings/{reading}/approve', [SubmeterMonitoringController::class, 'approve'])->name('modules.submeters.readings.approve');
    Route::get('/modules/submeters/alerts', [SubmeterMonitoringController::class, 'alerts'])->name('modules.submeters.alerts');
    Route::get('/modules/submeters/{submeter}/ai-insight', [SubmeterMonitoringController::class, 'aiInsight'])->name('modules.submeters.ai-insight');
    Route::get('/modules/submeters/{submeter}', [SubmeterMonitoringController::class, 'show'])->name('modules.submeters.show');
    Route::get('/modules/energy-conservation', [EnergyConservationController::class, 'index'])->name('modules.energy-conservation.index');

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
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'main');
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

        $archivedCount = \App\Models\EnergyRecord::onlyTrashed()->where('facility_id', $facilityId)->count();

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
            'archivedCount'
        ));
    })->name('facilities.monthly-records');

    Route::get('/modules/facilities/{facility}/monthly-records/submeters', function (\Illuminate\Http\Request $request, $facilityId) {
        $facility = \App\Models\Facility::find($facilityId);
        if (! $facility) {
            $fallbackFacility = \App\Models\Facility::query()
                ->whereIn('name', ['LGU City Hall Main Building', 'LGU Health Office'])
                ->orderBy('id')
                ->first();

            if ($fallbackFacility) {
                return redirect()
                    ->route('facilities.monthly-records.submeters', ['facility' => $fallbackFacility->id])
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

        $mainMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'main')
            ->whereNotNull('approved_at')
            ->orderBy('meter_name')
            ->get();

        if ($mainMeterOptions->isEmpty()) {
            return redirect()
                ->route('facilities.monthly-records', ['facility' => $facilityId])
                ->with('error', 'Add and approve a Main Meter first before viewing Sub-meter monthly records.');
        }

        $selectedMainMeterId = (int) ($request->query('main_meter_id') ?: 0);
        if ($selectedMainMeterId > 0 && ! $mainMeterOptions->contains(fn ($meter) => (int) $meter->id === $selectedMainMeterId)) {
            $selectedMainMeterId = 0;
        }

        $allSubMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'sub')
            ->whereNotNull('approved_at')
            ->orderBy('meter_name')
            ->get();

        $subMeterOptions = $allSubMeterOptions
            ->when($selectedMainMeterId > 0, fn ($collection) => $collection->filter(fn ($meter) => (int) ($meter->parent_meter_id ?? 0) === $selectedMainMeterId))
            ->values();

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

        $selectedYear = (int) ($request->query('year') ?: date('Y'));
        $selectedMonth = (int) ($request->query('month') ?: 0);
        if ($selectedMonth < 0 || $selectedMonth > 12) {
            $selectedMonth = 0;
        }
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

        $manualRows = $submeterRecords->map(function ($record) use ($resolveCost) {
            $actualKwh = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
            $baselineKwh = is_numeric($record->baseline_kwh)
                ? (float) $record->baseline_kwh
                : (is_numeric($record->meter?->baseline_kwh) ? (float) $record->meter->baseline_kwh : null);
            $deviation = is_numeric($record->deviation)
                ? (float) $record->deviation
                : \App\Models\EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);

            return [
                'id' => (int) ($record->id ?? 0),
                'meter_id' => (int) ($record->meter_id ?? 0),
                'year' => (int) ($record->year ?? 0),
                'month' => (int) ($record->month ?? 0),
                'day' => $record->day ?: '-',
                'meter_name' => (string) ($record->meter?->meter_name ?? '-'),
                'actual_kwh' => $actualKwh,
                'baseline_kwh' => $baselineKwh,
                'deviation' => $deviation,
                'cost' => round((float) $resolveCost($record), 2),
                'source_label' => 'Manual',
            ];
        });

        $sensorPreferredRows = $sensorRows->map(function ($reading) use ($submeterToFacilityMeterIdMap, $subMeterOptions) {
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
            ->merge($manualRows->reject(fn ($row) => $sensorKeys->has((int) $row['meter_id'] . '-' . (int) $row['year'] . '-' . (int) $row['month'])))
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

        $submeterGroups = $preferredRows
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

        $totalKwh = round((float) $preferredRows->sum(fn ($row) => (float) ($row['actual_kwh'] ?? 0)), 2);
        $totalCost = round((float) $preferredRows->sum(fn ($row) => (float) ($row['cost'] ?? 0)), 2);
        $totalRecords = (int) $preferredRows->count();

        return view('modules.facilities.monthly-record.submeter-records', compact(
            'facility',
            'mainMeterOptions',
            'subMeterOptions',
            'submeterGroups',
            'selectedYear',
            'selectedMonth',
            'selectedMainMeterId',
            'selectedMeterId',
            'yearOptions',
            'monthLabels',
            'totalKwh',
            'totalCost',
            'totalRecords'
        ));
    })->name('facilities.monthly-records.submeters');

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
    Route::get('/modules/reports/facilities', fn() => redirect()->route('modules.reports.energy'))->name('modules.reports.facilities');

    // Users - Admin/Energy Officer only (Staff blocked via controller)
    Route::get('/modules/users/roles', [\App\Http\Controllers\Modules\UsersController::class, 'roles'])->name('modules.users.roles');
    Route::get('/modules/audit/index', [AuditLogController::class, 'index'])->name('modules.audit.index');
    Route::get('/modules/contact-messages', [ContactInboxController::class, 'index'])->name('modules.contact-messages.index');
    Route::post('/modules/contact-messages/{contactMessage}/mark-read', [ContactInboxController::class, 'markRead'])->name('modules.contact-messages.mark-read');
    Route::post('/modules/contact-messages/{contactMessage}/mark-unread', [ContactInboxController::class, 'markUnread'])->name('modules.contact-messages.mark-unread');
    Route::post('/modules/contact-messages/{contactMessage}/reply', [ContactInboxController::class, 'reply'])->name('modules.contact-messages.reply');

    Route::get('/modules/energy/annual', function () {
        $years = range(date('Y'), date('Y') - 10);
        $selectedYear = request('year', date('Y'));
        $facilities = \App\Models\Facility::all();
        $selectedFacility = request('facility_id', '');

        $query = \App\Models\EnergyRecord::with('facility')
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'main');
            });
        if ($selectedFacility) {
            $query->where('facility_id', $selectedFacility);
        }
        $query->where('year', $selectedYear);
        $records = $query->get();

        $getAlertBySize = function ($deviation, $baselineKwh) {
            if ($deviation === null || $baselineKwh === null || $baselineKwh <= 0) {
                return '-';
            }

            if ($baselineKwh <= 1000) {
                $size = 'Small';
            } elseif ($baselineKwh <= 3000) {
                $size = 'Medium';
            } elseif ($baselineKwh <= 10000) {
                $size = 'Large';
            } else {
                $size = 'Extra Large';
            }

            $thresholds = [
                'Small' => ['level5' => 80, 'level4' => 50, 'level3' => 30, 'level2' => 15],
                'Medium' => ['level5' => 60, 'level4' => 40, 'level3' => 20, 'level2' => 10],
                'Large' => ['level5' => 30, 'level4' => 20, 'level3' => 12, 'level2' => 5],
                'Extra Large' => ['level5' => 20, 'level4' => 12, 'level3' => 7, 'level2' => 3],
            ];
            $t = $thresholds[$size];

            if ($deviation > $t['level5']) return 'Critical';
            if ($deviation > $t['level4']) return 'Very High';
            if ($deviation > $t['level3']) return 'High';
            if ($deviation > $t['level2']) return 'Warning';
            return 'Normal';
        };

        $getHighestAlert = function ($alerts) {
            $priority = [
                'Critical' => 5,
                'Very High' => 4,
                'High' => 3,
                'Warning' => 2,
                'Normal' => 1,
                '-' => 0,
            ];
            $best = '-';
            $bestScore = 0;
            foreach ($alerts as $alert) {
                $score = $priority[$alert] ?? 0;
                if ($score > $bestScore) {
                    $best = $alert;
                    $bestScore = $score;
                }
            }
            return $best;
        };

        $monthlyBreakdown = [];
        $totalActualKwh = 0;
        $annualBaseline = 0;
        foreach (range(1, 12) as $m) {
            $monthRecords = $records->where('month', str_pad($m, 2, '0', STR_PAD_LEFT));
            $actual = $monthRecords->sum('actual_kwh');
            $baseline = 0;
            $monthAlerts = [];
            foreach ($monthRecords as $record) {
                $recordBaseline = $record->baseline_kwh;
                if ($recordBaseline === null || $recordBaseline <= 0) {
                    $profile = $record->facility ? $record->facility->energyProfiles()->latest()->first() : null;
                    $recordBaseline = $profile ? (float) $profile->baseline_kwh : 0;
                }
                $baseline += (float) $recordBaseline;
                $deviation = $recordBaseline > 0
                    ? ((float)$record->actual_kwh - (float)$recordBaseline) / (float)$recordBaseline * 100
                    : null;
                $monthAlerts[] = $getAlertBySize($deviation, $recordBaseline);
            }
            $diff = $actual - $baseline;
            $status = $getHighestAlert($monthAlerts);
            $monthlyBreakdown[] = [
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'actual' => $actual,
                'baseline' => $baseline,
                'diff' => $diff,
                'status' => $status,
            ];
            $totalActualKwh += $actual;
            $annualBaseline += $baseline;
        }
        $annualDifference = $totalActualKwh - $annualBaseline;
        $annualStatus = $getHighestAlert(array_column($monthlyBreakdown, 'status'));
        $user = auth()->user();
        $role = strtolower($user->role ?? '');

        return view('modules.energy-monitoring.annual', compact('years', 'selectedYear', 'facilities', 'selectedFacility', 'totalActualKwh', 'annualBaseline', 'annualDifference', 'annualStatus', 'monthlyBreakdown', 'role', 'user'));
    })->name('modules.energy.annual');
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
            ->get(['id', 'meter_name', 'meter_number', 'meter_type', 'parent_meter_id', 'location', 'status', 'multiplier', 'baseline_kwh', 'notes', 'approved_by_user_id', 'approved_at']);
        $subMeterOptions = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'sub')
            ->whereNotNull('approved_at')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->get(['id', 'meter_name', 'meter_number', 'meter_type', 'parent_meter_id', 'location', 'status', 'multiplier', 'baseline_kwh', 'notes', 'approved_by_user_id', 'approved_at']);
        $subMetersByParentMainId = $subMeterOptions
            ->filter(fn ($meter) => ! empty($meter->parent_meter_id))
            ->groupBy(fn ($meter) => (int) $meter->parent_meter_id);
        $normalizeName = function (string $name): string {
            return strtolower((string) preg_replace('/\s+/', ' ', trim($name)));
        };
        $submeterNameToIdMap = \App\Models\Submeter::where('facility_id', $facilityModel->id)
            ->where('status', 'active')
            ->get(['id', 'submeter_name'])
            ->mapWithKeys(function ($submeter) use ($normalizeName) {
                return [$normalizeName((string) $submeter->submeter_name) => (int) $submeter->id];
            });
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
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->count();
        $activeMainMeterCount = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'main')
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->count();
        $subMeterCount = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->where('meter_type', 'sub')
            ->whereNotNull('approved_at')
            ->count();
        $unapprovedMeterCount = \App\Models\FacilityMeter::where('facility_id', $facilityModel->id)
            ->whereNull('approved_at')
            ->count();
        $archivedMeterCount = \App\Models\FacilityMeter::onlyTrashed()->where('facility_id', $facilityModel->id)->count();
        $canManageMeters = \App\Support\RoleAccess::can($user, 'manage_facility_master');
        $canApproveMeters = \App\Support\RoleAccess::can($user, 'approve_facility_meters');
        // 3-Month average update logic removed
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
            'canApproveMeters'
        ));
    })->name('modules.facilities.energy-profile.index');

    // Store new energy profile (controller-based)
    Route::post('/modules/facilities/{facility}/energy-profile', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'store'])->name('modules.facilities.energy-profile.store');

    // Update energy profile
    Route::match(['put', 'patch'], '/modules/facilities/{facility}/energy-profile/{profile}', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'update'])
        ->name('modules.facilities.energy-profile.update');

    // Toggle engineer approval for energy profile
    Route::post('/modules/facilities/{facility}/energy-profile/{profile}/toggle-approval', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'toggleEngineerApproval'])->name('energy-profile.toggle-approval');

    // Delete energy profile (controller, like monthly record)
    Route::delete('/modules/facilities/{facility}/energy-profile/{profile}', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'destroy'])
        ->name('modules.facilities.energy-profile.destroy');

    // Fallback for DELETE without profile id (returns 405)
    Route::delete('/modules/facilities/{facility}/energy-profile', function () {
        abort(405, 'Profile ID required for delete.');
    });
});
