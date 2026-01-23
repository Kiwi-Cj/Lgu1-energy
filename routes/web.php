<?php
// Users & Roles Management - Admin/Energy Officer only
use Illuminate\Http\Request as HttpRequest;
use App\Http\Controllers\Modules\FacilityController;
use App\Http\Controllers\Modules\EnergyController;
use App\Http\Controllers\Modules\MaintenanceController;


use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('modules/users', [\App\Http\Controllers\Modules\UsersController::class, 'index'])->name('users.index');
    Route::get('modules/users/index', [\App\Http\Controllers\Modules\UsersController::class, 'index']);
    Route::post('modules/users', [\App\Http\Controllers\Modules\UsersController::class, 'store'])->name('users.store');
    Route::get('modules/users/{id}/edit', [\App\Http\Controllers\Modules\UsersController::class, 'edit'])->name('users.edit');
    Route::put('modules/users/{id}', [\App\Http\Controllers\Modules\UsersController::class, 'update'])->name('users.update');
    Route::get('/users/roles', function () {
        // Block Staff from accessing Roles page
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Roles.');
        }
        return view('modules.users.roles');
    })->name('users.roles');
});


Route::get('/', function () {
    return view('welcome');
});


// AJAX route to check for duplicate energy record
Route::get('/modules/energy/check-duplicate', function(HttpRequest $request) {
	$exists = \App\Models\EnergyRecord::where('facility_id', $request->facility_id)
		->where('month', $request->month)
		->where('year', $request->year)
		->exists();
	return response()->json(['exists' => $exists]);
})->name('modules.energy.check-duplicate');

// AJAX route to get kWh Consumed for a facility and month-year
Route::get('/modules/energy/get-kwh-consumed', function(HttpRequest $request) {
	$facilityId = $request->facility_id;
	$monthYear = $request->month; // format: YYYY-MM
	if (!$facilityId || !$monthYear) {
		return response()->json(['kwh_consumed' => null]);
	}
	[$year, $month] = explode('-', $monthYear);
	$record = \App\Models\EnergyRecord::where('facility_id', $facilityId)
		->where('year', $year)
		->where('month', str_pad($month, 2, '0', STR_PAD_LEFT))
		->first();
	return response()->json(['kwh_consumed' => $record ? $record->kwh_consumed : null]);
})->name('modules.energy.get-kwh-consumed');
// Maintenance History Route
Route::get('/modules/maintenance/history', [MaintenanceController::class, 'history'])->name('maintenance.history');
Route::delete('/modules/maintenance/history/{id}', [MaintenanceController::class, 'destroyHistory'])->name('modules.maintenance.history.destroy');

// Include authentication routes (login, register, etc.)
require __DIR__.'/auth.php';
// Include reports routes
require __DIR__.'/reports.php';

// Public welcome page route
Route::get('/modules/dashboard/index', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');

// Energy Monitoring Excel Export (CSV fallback)
Route::get('/modules/energy/export-excel', function(\Illuminate\Http\Request $request) {
	$query = \App\Models\EnergyRecord::with('facility');
	if ($request->filled('facility_id')) {
		$query->where('facility_id', $request->facility_id);
	}
	if ($request->filled('month')) {
		$query->where('month', $request->month);
	}
	if ($request->filled('year')) {
		$query->where('year', $request->year);
	}
	$records = $query->orderByDesc('year')->orderByDesc('month')->get();
	$filename = 'energy_monitoring_' . date('Ymd_His') . '.csv';
	$headers = [
		'Content-Type' => 'text/csv',
		'Content-Disposition' => "attachment; filename=\"$filename\"",
	];
	$columns = ['Year', 'Month', 'Facility', 'kWh Consumed'];
	$callback = function() use ($records, $columns) {
		$file = fopen('php://output', 'w');
		fputcsv($file, $columns);
		foreach ($records as $r) {
			$monthName = '';
			if ($r->month) {
				$monthNum = (int) ltrim($r->month, '0');
				$monthName = $monthNum >= 1 && $monthNum <= 12 ? date('M', mktime(0,0,0,$monthNum,1)) : $r->month;
			}
			fputcsv($file, [
				$r->year,
				$monthName,
				$r->facility ? $r->facility->name : '',
				$r->kwh_consumed,
			]);
		}
		fclose($file);
	};
	return response()->stream($callback, 200, $headers);
})->name('modules.energy.export-excel');

// Energy Profile per Facility

Route::middleware(['auth', 'verified'])->group(function () {
	// System Settings route for dashboard shortcut - Admin only
	Route::get('/modules/settings', function() {
		// Block Staff from accessing Settings page
		if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
			return redirect()->route('modules.energy.index')
				->with('error', 'You do not have permission to access System Settings.');
		}
		return view('modules.settings.index');
	})->name('settings.index');
	// Facility modal detail for AJAX
	Route::get('/modules/facilities/{facility}/modal-detail', [\App\Http\Controllers\Modules\FacilityController::class, 'modalDetail'])->name('modules.facilities.modal-detail');
	Route::get('/modules/energy-efficiency-analysis', [\App\Http\Controllers\Modules\EnergyEfficiencyAnalysisController::class, 'index'])->name('modules.energy-efficiency-analysis.index');
	// Annual Energy Summary Excel Export (CSV fallback)
	Route::get('/modules/energy/annual/export-excel', function(\Illuminate\Http\Request $request) {
		$years = range(date('Y'), date('Y')-10);
		$selectedYear = $request->query('year', date('Y'));
		$facilities = \App\Models\Facility::all();
		$selectedFacility = $request->query('facility_id', '');

			$query = \App\Models\EnergyRecord::query();
			if ($selectedFacility) {
				$query->where('facility_id', $selectedFacility);
			}
			$query->where('year', $selectedYear);
			$records = $query->get();

			// Calculate monthly breakdown
			$monthlyBreakdown = [];
			foreach (range(1, 12) as $m) {
				$monthRecords = $records->where('month', str_pad($m, 2, '0', STR_PAD_LEFT));
				$actual = $monthRecords->sum('kwh_consumed');
				$baseline = $monthRecords->map(function($r){
					$profile = $r->facility ? $r->facility->energyProfiles()->latest()->first() : null;
					return $profile ? $profile->average_monthly_kwh : 0;
				})->sum();
				$diff = $actual - $baseline;
				$status = ($baseline !== 0) ? ($diff > 0 ? 'High' : 'Efficient') : '-';
				$monthlyBreakdown[] = [
					'Month' => date('M', mktime(0,0,0,$m,1)),
					'Actual kWh' => $actual,
					'Baseline kWh' => $baseline,
					'Difference' => $diff,
					'Status' => $status,
				];
			}

			$filename = 'annual_energy_summary_' . date('Ymd_His') . '.csv';
			$headers = [
				'Content-Type' => 'text/csv',
				'Content-Disposition' => "attachment; filename=\"$filename\"",
			];
			$columns = ['Month', 'Actual kWh', 'Baseline kWh', 'Difference', 'Status'];
			$callback = function() use ($monthlyBreakdown, $columns) {
				$file = fopen('php://output', 'w');
				fputcsv($file, $columns);
				foreach ($monthlyBreakdown as $row) {
					fputcsv($file, [
						$row['Month'],
						$row['Actual kWh'],
						$row['Baseline kWh'],
						$row['Difference'],
						$row['Status'],
					]);
				}
				fclose($file);
			};
			return response()->stream($callback, 200, $headers);
		})->name('modules.energy.annual.export-excel');
	Route::get('/modules/facilities/{facility}/energy-profile', function($facility) {
		$facilityModel = \App\Models\Facility::findOrFail($facility);
		$energyProfiles = $facilityModel->energyProfiles;
		return view('modules.facilities.energy-profile.index', compact('facilityModel', 'energyProfiles'));
	})->name('modules.facilities.energy-profile.index');

	// Show create form for energy profile
	Route::get('/modules/facilities/{facility}/energy-profile/create', function($facility) {
		$facilityModel = \App\Models\Facility::findOrFail($facility);
		return view('modules.facilities.energy-profile.create', compact('facilityModel'));
	})->name('modules.facilities.energy-profile.create');

	// Store new energy profile
	Route::post('/modules/facilities/{facility}/energy-profile', function($facility, \Illuminate\Http\Request $request) {
		$facilityModel = \App\Models\Facility::findOrFail($facility);
		$validated = $request->validate([
			'electric_meter_no' => 'required|string|max:255',
			'utility_provider' => 'required|string|max:255',
			'contract_account_no' => 'required|string|max:255',
			'average_monthly_kwh' => 'required|numeric',
			'main_energy_source' => 'required|string|max:255',
			'backup_power' => 'required|string|max:255',
			'transformer_capacity' => 'nullable|string|max:255',
			'number_of_meters' => 'required|integer',
			'bill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
		]);
		$validated['facility_id'] = $facilityModel->id;
		if ($request->hasFile('bill_image')) {
			$validated['bill_image'] = $request->file('bill_image')->store('energy_bills', 'public');
		}
		\App\Models\EnergyProfile::create($validated);
		return redirect()->route('modules.facilities.energy-profile.index', $facilityModel->id)
			->with('success', 'Energy profile added successfully!');
	})->name('modules.facilities.energy-profile.store');
});
// =====================
// LOGOUT ROUTE (for Auth::logout)
// =====================
use Illuminate\Support\Facades\Auth;
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');
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
	// Dashboard (dynamic, via controller)

	// Facilities
	Route::get('/modules/facilities/index', [FacilityController::class, 'index'])->name('modules.facilities.index');
	Route::get('/modules/facilities/create', fn() => view('modules.facilities.create'))->name('modules.facilities.create');
	Route::get('/modules/facilities/{id}/show', fn($id) => view('modules.facilities.show', ['id' => $id]))->name('modules.facilities.show');
	Route::get('/modules/facilities/{id}/edit', fn($id) => view('modules.facilities.edit', ['id' => $id]))->name('modules.facilities.edit');

	// Billing
	Route::get('/modules/billing/index', function() {
		$facilities = \App\Models\Facility::all();
		$query = \App\Models\Bill::with('facility');
		$filterFacilityId = request('facility_id', '');
		$filterMonth = request('month', '');
		$filterStatus = request('status', '');

		if ($filterFacilityId) {
			$query->where('facility_id', $filterFacilityId);
		}
		if ($filterMonth) {
			$query->where('month', $filterMonth);
		}
		if ($filterStatus) {
			$query->where('status', $filterStatus);
		}

		$bills = $query->orderByDesc('month')->orderBy('facility_id')->get();

		// Summary cards
		$totalFacilitiesBilled = $bills->pluck('facility_id')->unique()->count();
		$totalAmountBilled = $bills->sum(function($bill) {
			return $bill->total_bill;
		});
		$pendingFacilities = $bills->where('status', 'Pending')->pluck('facility_id')->unique()->count();

		return view('modules.billing.index', compact('facilities','bills','totalFacilitiesBilled','totalAmountBilled','pendingFacilities','filterFacilityId','filterMonth','filterStatus'));
	})->name('modules.billing.index');
	Route::get('/modules/billing/create', function() {
		$facilities = \App\Models\Facility::all();
		return view('modules.billing.create', compact('facilities'));
	})->name('modules.billing.create');
	Route::get('/modules/billing/{id}/show', function($id) {
		$bill = \App\Models\Bill::with('facility')->findOrFail($id);
		return view('modules.billing.show', compact('bill'));
	})->name('modules.billing.show');
	Route::get('/modules/billing/{id}/edit', function($id) {
		$bill = \App\Models\Bill::findOrFail($id);
		$facilities = \App\Models\Facility::all();
		return view('modules.billing.edit', compact('bill', 'facilities', 'id'));
	})->name('modules.billing.edit');
	Route::post('/modules/billing/store', function(\Illuminate\Http\Request $request) {
		$validated = $request->validate([
			'facility_id' => 'required|exists:facilities,id',
			'month' => 'required|date_format:Y-m',
			'kwh_consumed' => 'required|numeric',
			'unit_cost' => 'required|numeric',
			'status' => 'required|in:Paid,Unpaid,Pending',
			'meralco_bill_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
		]);
		$validated['unit_cost'] = 12.50;
		$validated['total_bill'] = $validated['kwh_consumed'] * $validated['unit_cost'];

		if ($request->hasFile('meralco_bill_picture')) {
			$path = $request->file('meralco_bill_picture')->store('bills', 'public');
			$validated['meralco_bill_picture'] = $path;
		}

		\App\Models\Bill::create($validated);
		return redirect()->route('modules.billing.index')->with('success', 'Bill added successfully!');
	})->name('modules.billing.store');
	Route::put('/modules/billing/{id}', function($id, \Illuminate\Http\Request $request) {
		$bill = \App\Models\Bill::findOrFail($id);
		$validated = $request->validate([
			'facility_id' => 'required|exists:facilities,id',
			'month' => 'required|date_format:Y-m',
			'kwh_consumed' => 'required|numeric',
			'unit_cost' => 'required|numeric',
			'status' => 'required|in:Paid,Unpaid,Pending',
		]);
		$validated['unit_cost'] = 12.50;
		$validated['total_bill'] = $validated['kwh_consumed'] * $validated['unit_cost'];
		$bill->update($validated);
		return redirect()->route('modules.billing.index')->with('success', 'Bill updated successfully!');
	})->name('modules.billing.update');
	Route::delete('/modules/billing/{id}', fn($id) => redirect()->route('modules.billing.index'))->name('modules.billing.destroy');
	Route::get('/modules/billing/analysis', fn() => view('modules.billing.analysis'))->name('modules.billing.analysis');

	// Maintenance
	Route::get('/modules/maintenance/index', [MaintenanceController::class, 'index'])->name('modules.maintenance.index');
	Route::get('/modules/maintenance/create', [MaintenanceController::class, 'create'])->name('modules.maintenance.create');
	Route::get('/modules/maintenance/schedule', fn() => view('modules.maintenance.schedule'))->name('modules.maintenance.schedule');
	Route::post('/modules/maintenance/schedule', [MaintenanceController::class, 'store'])->name('modules.maintenance.schedule');

	// Reports
	Route::get('/modules/reports/energy', [EnergyController::class, 'energyReport'])->name('modules.reports.energy');
	Route::get('/modules/reports/facilities', fn() => view('modules.reports.facilities'))->name('modules.reports.facilities');

	// Settings - Admin only
	Route::get('/modules/settings/index', function() {
		// Block Staff from accessing Settings page
		if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
			return redirect()->route('modules.energy.index')
				->with('error', 'You do not have permission to access System Settings.');
		}
		return view('modules.settings.index');
	})->name('modules.settings.index');

	// Users - Admin/Energy Officer only (Staff blocked via controller)
	// Route::get('/modules/users/index', fn() => view('modules.users.index'))->name('modules.users.index');
	Route::get('/modules/users/roles', function() {
		// Block Staff from accessing Roles page
		if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
			return redirect()->route('modules.energy.index')
				->with('error', 'You do not have permission to access User Roles.');
		}
		return view('modules.users.roles');
	})->name('modules.users.roles');

	// Energy
	Route::get('/modules/energy/index', [EnergyController::class, 'index'])->name('modules.energy.index');
	Route::get('/modules/energy/create', [EnergyController::class, 'create'])->name('modules.energy.create');
	Route::post('/modules/energy/store', [EnergyController::class, 'store'])->name('modules.energy.store');
	Route::get('/modules/energy/{id}/show', [EnergyController::class, 'show'])->name('modules.energy.show');
	Route::get('/modules/energy/{id}/edit', [EnergyController::class, 'edit'])->name('modules.energy.edit');
	Route::put('/modules/energy/{id}', [EnergyController::class, 'update'])->name('modules.energy.update');
	Route::delete('/modules/energy/{id}', [EnergyController::class, 'destroy'])->name('modules.energy.destroy');
	Route::get('/modules/energy/trends', fn() => view('modules.energy.trends'))->name('modules.energy.trends');
	Route::get('/modules/energy/annual', function() {
		$years = range(date('Y'), date('Y')-10);
		$selectedYear = request('year', date('Y'));
		$facilities = \App\Models\Facility::all();
		$selectedFacility = request('facility_id', '');

		$query = \App\Models\EnergyRecord::query();
		if ($selectedFacility) {
			$query->where('facility_id', $selectedFacility);
		}
		$query->where('year', $selectedYear);
		$records = $query->get();

		// Calculate monthly breakdown
		$monthlyBreakdown = [];
		$totalActualKwh = 0;
		$annualBaseline = 0;
		foreach (range(1, 12) as $m) {
			$monthRecords = $records->where('month', str_pad($m, 2, '0', STR_PAD_LEFT));
			$actual = $monthRecords->sum('kwh_consumed');
			$baseline = $monthRecords->map(function($r){
				$profile = $r->facility ? $r->facility->energyProfiles()->latest()->first() : null;
				return $profile ? $profile->average_monthly_kwh : 0;
			})->sum();
			$diff = $actual - $baseline;
			$status = ($baseline !== 0) ? ($diff > 0 ? 'High' : 'Efficient') : '-';
			$monthlyBreakdown[] = [
				'label' => date('M', mktime(0,0,0,$m,1)),
				'actual' => $actual,
				'baseline' => $baseline,
				'diff' => $diff,
				'status' => $status,
			];
			$totalActualKwh += $actual;
			$annualBaseline += $baseline;
		}
		$annualDifference = $totalActualKwh - $annualBaseline;
		$annualStatus = ($annualBaseline !== 0) ? ($annualDifference > 0 ? 'High' : 'Efficient') : '-';

		return view('modules.energy.annual.annual', compact('years','selectedYear','facilities','selectedFacility','totalActualKwh','annualBaseline','annualDifference','annualStatus','monthlyBreakdown'));
	})->name('modules.energy.annual');
});
