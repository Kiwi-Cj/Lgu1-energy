<?php

use App\Http\Controllers\Modules\EnergyController;
use App\Http\Controllers\Modules\FacilityController;
use App\Http\Controllers\Modules\MaintenanceController;
use Illuminate\Support\Facades\Route;

// =====================
// FACILITIES CONTROLLER ROUTES (for named routes)
// =====================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [FacilityController::class, 'create'])->name('facilities.create');
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
    Route::get('/modules/facilities/create', fn() => view('modules.facilities.create'))->name('modules.facilities.create');
    Route::get('/modules/facilities/{id}/show', function ($id) {
        $facility = \App\Models\Facility::findOrFail($id);
        // first3months_data table removed; fallback to baseline_kwh
        $showAvg = false;
        $avgKwh = $facility->baseline_kwh ?? 0;
        return view('modules.facilities.show', compact('facility', 'showAvg', 'avgKwh'));
    })->name('modules.facilities.show');
    Route::get('/modules/facilities/{id}/edit', fn($id) => view('modules.facilities.edit', ['id' => $id]))->name('modules.facilities.edit');

    // Monthly Records per Facility
    Route::get('/modules/facilities/{facility}/monthly-records', function ($facilityId) {
        $facility = \App\Models\Facility::findOrFail($facilityId);
        $records = \App\Models\EnergyRecord::where('facility_id', $facilityId)->orderByDesc('year')->orderByDesc('month')->get();
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.facilities.monthly-record.records', compact('facility', 'records', 'notifications', 'unreadNotifCount'));
    })->name('facilities.monthly-records');

    // Maintenance
    Route::get('/modules/maintenance/index', [MaintenanceController::class, 'index'])->name('modules.maintenance.index');
    Route::get('/modules/maintenance/create', [MaintenanceController::class, 'create'])->name('modules.maintenance.create');
    Route::get('/modules/maintenance/schedule', fn() => view('modules.maintenance.schedule'))->name('modules.maintenance.schedule');
    Route::post('/modules/maintenance/schedule', [MaintenanceController::class, 'store'])->name('modules.maintenance.schedule');

    // Reports
    Route::get('/modules/reports/energy', [EnergyController::class, 'energyReport'])->name('modules.reports.energy');
    Route::get('/modules/reports/facilities', fn() => view('modules.reports.facilities'))->name('modules.reports.facilities');

    // Users - Admin/Energy Officer only (Staff blocked via controller)
    Route::get('/modules/users/roles', [\App\Http\Controllers\Modules\UsersController::class, 'roles'])->name('modules.users.roles');

    // Energy
    Route::get('/modules/energy/index', function (\Illuminate\Http\Request $request) {
        $facilities = \App\Models\Facility::all();
        $query = \App\Models\EnergyRecord::with('facility');
        if ($request->has('facility_id') && $request->facility_id) {
            $query->where('facility_id', $request->facility_id);
        }
        $monthlyRecords = $query->get();
        return view('modules.energy-monitoring.records', compact('facilities', 'monthlyRecords'));
    })->name('modules.energy.index');
    Route::get('/modules/energy/create', [EnergyController::class, 'create'])->name('modules.energy.create');
    Route::post('/modules/energy/store', [EnergyController::class, 'store'])->name('modules.energy.store');
    Route::get('/modules/energy/{id}/show', [EnergyController::class, 'show'])->name('modules.energy.show');
    Route::get('/modules/energy/{id}/edit', [EnergyController::class, 'edit'])->name('modules.energy.edit');
    Route::put('/modules/energy/{id}', [EnergyController::class, 'update'])->name('modules.energy.update');
    Route::delete('/modules/energy/{id}', [EnergyController::class, 'destroy'])->name('modules.energy.destroy');
    Route::get('/modules/energy/trends', fn() => view('modules.energy.trends'))->name('modules.energy.trends');
    Route::get('/modules/energy/annual', function () {
        $years = range(date('Y'), date('Y') - 10);
        $selectedYear = request('year', date('Y'));
        $facilities = \App\Models\Facility::all();
        $selectedFacility = request('facility_id', '');

        $query = \App\Models\EnergyRecord::with('facility');
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
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;

        return view('modules.energy-monitoring.annual', compact('years', 'selectedYear', 'facilities', 'selectedFacility', 'totalActualKwh', 'annualBaseline', 'annualDifference', 'annualStatus', 'monthlyBreakdown', 'role', 'user', 'notifications', 'unreadNotifCount'));
    })->name('modules.energy.annual');
});

// =====================
// FACILITIES ENERGY PROFILE ROUTES
// =====================
Route::middleware(['auth', 'verified'])->group(function () {
    // Energy Profile per Facility
    Route::get('/modules/facilities/{facility}/energy-profile', function ($facility) {
        $facilityModel = \App\Models\Facility::findOrFail($facility);
        $energyProfiles = $facilityModel->energyProfiles;
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        // 3-Month average update logic removed
        return view('modules.facilities.energy-profile.index', compact('facilityModel', 'energyProfiles', 'notifications', 'unreadNotifCount'));
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


