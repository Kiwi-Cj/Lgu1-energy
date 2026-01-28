
<?php

use App\Http\Controllers\EnergyActionController;

use App\Http\Controllers\First3MonthsController;
use App\Http\Controllers\Modules\EnergyMonitoringController;

// OTP routes (should NOT be inside auth middleware)
Route::get('/otp/request', [\App\Http\Controllers\OtpController::class, 'showRequestForm'])->name('otp.request');
Route::post('/otp/send', [\App\Http\Controllers\OtpController::class, 'sendOtp'])->name('otp.send');
Route::get('/otp/verify', [\App\Http\Controllers\OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/otp/verify', [\App\Http\Controllers\OtpController::class, 'verifyOtp'])->name('otp.verify.submit');
Route::post('/otp/resend', [\App\Http\Controllers\OtpController::class, 'resendOtp'])->name('otp.resend');
	// Energy Actions
	Route::get('/energy-actions', [EnergyActionController::class, 'index']);
	Route::get('/energy-actions/create', [EnergyActionController::class, 'create']);
	Route::post('/energy-actions/store', [EnergyActionController::class, 'store']);
		   // Delete a monthly energy record for a facility
		   Route::delete('/modules/facilities/{facility}/monthly-records/{record}', function($facilityId, $recordId) {
			   $record = \App\Models\EnergyRecord::where('facility_id', $facilityId)->where('id', $recordId)->firstOrFail();
			   $record->delete();
			   return redirect()->back()->with('success', 'Monthly record deleted!');
		   })->name('energy-records.delete');
	// Store new monthly energy record for a facility (for modal form)
		   Route::post('/modules/facilities/{facility}/monthly-records', function($facilityId, \Illuminate\Http\Request $request) {
			   $validated = $request->validate([
				   'year' => 'required|integer',
				   'month' => 'required|integer|min:1|max:12',
				   'actual_kwh' => 'required|numeric',
				   'energy_cost' => 'nullable|numeric',
				   'meralco_bill_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
			   ]);
			   $validated['facility_id'] = $facilityId;
			   $validated['recorded_by'] = auth()->id();
			   if ($request->hasFile('meralco_bill_picture')) {
				   $path = $request->file('meralco_bill_picture')->store('meralco_bills', 'public');
				   $validated['meralco_bill_picture'] = $path;
			   }
			   \App\Models\EnergyRecord::create($validated);
			   return redirect()->back()->with('success', 'Monthly record added!');
		   })->name('energy-records.store');

	// ...existing code...
	// First 3 Months Data Entry for Facilities
	Route::get('/facilities/first3months', [First3MonthsController::class, 'create'])->name('facilities.first3months.create');
	Route::post('/facilities/first3months', [First3MonthsController::class, 'store'])->name('facilities.first3months.store');
	Route::delete('/facilities/first3months/{facility_id}/{month_no}', [First3MonthsController::class, 'delete'])->name('facilities.first3months.delete');
				// Energy Monitoring - Energy Profile page
			// Placeholder route for energy-profiles.store to resolve RouteNotFoundException
			Route::post('/modules/energy-profiles', function() {
				// Implement storing logic here
				return redirect()->back()->with('success', 'Energy profile stored (placeholder).');
			})->name('energy-profiles.store');
		// Placeholder route for facilities.exportReport to resolve RouteNotFoundException
		Route::get('/modules/facilities/export-report', function() {
			return 'Export report for facilities (placeholder).';
		})->name('facilities.exportReport');
	// Redirect /modules/energy/dashboard to /modules/energy-monitoring for compatibility
	Route::get('/modules/energy/dashboard', function() {
		return redirect()->route('modules.energy-monitoring.index');
	})->name('energy.dashboard');

	Route::get('/modules/energy/records', function(\Illuminate\Http\Request $request) {
		$facilities = \App\Models\Facility::all();
		$query = \App\Models\EnergyRecord::with('facility');
		if ($request->has('facility_id') && $request->facility_id) {
			$query->where('facility_id', $request->facility_id);
		}
		$monthlyRecords = $query->get();
		return view('modules.energy-monitoring.records', compact('facilities', 'monthlyRecords'));
	})->name('energy.records');

	// Monthly Records per Facility (CORRECT ROUTE)
	Route::get('/modules/energy/records/{facility}', function($facilityId) {
		$facility = \App\Models\Facility::findOrFail($facilityId);
		$records = \App\Models\EnergyRecord::where('facility_id', $facilityId)->orderByDesc('year')->orderByDesc('month')->get();
		return view('modules.energy-monitoring.records', compact('facility', 'records'));
	})->name('energy.records');

	// Handle Add Monthly Record POST
	Route::post('/modules/energy/records', function(\Illuminate\Http\Request $request) {
		   $validated = $request->validate([
			   'facility_id' => 'required|exists:facilities,id',
			   'month' => 'required|integer|min:1|max:12',
			   'year' => 'required|integer',
			   'actual_kwh' => 'required|numeric',
			'average_monthly_kwh' => 'nullable|numeric',
			   'rate_per_kwh' => 'required|numeric',
			   'energy_cost' => 'nullable|numeric',
			   'meralco_bill_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
		   ]);
		// Prevent duplicate entry for the same facility, month, and year
		$exists = \App\Models\EnergyRecord::where('facility_id', $validated['facility_id'])
			->where('month', $validated['month'])
			->where('year', $validated['year'])
			->exists();
		if ($exists) {
			return redirect()->back()->withInput()->withErrors(['duplicate' => 'An energy record for this facility and month/year already exists.']);
		}
		   $data = $validated;
		   // Set baseline_kwh to average_monthly_kwh (they should always match)
		   if (!isset($data['average_monthly_kwh']) || $data['average_monthly_kwh'] === null) {
			   $data['average_monthly_kwh'] = 0;
		   }
		   // Calculate deviation_percent
		$data['deviation_percent'] = (isset($data['average_monthly_kwh']) && $data['average_monthly_kwh'] != 0)
			? round((($data['actual_kwh'] - $data['average_monthly_kwh']) / $data['average_monthly_kwh']) * 100, 2)
			   : 0;
		   // Calculate energy_cost
		   $data['energy_cost'] = $data['actual_kwh'] * $data['rate_per_kwh'];
		   if ($request->hasFile('meralco_bill_picture')) {
			   $path = $request->file('meralco_bill_picture')->store('meralco_bills', 'public');
			   $data['meralco_bill_picture'] = $path;
		   }
		   \App\Models\EnergyRecord::create($data);
		   return redirect()->route('modules.energy.index')->with('success', 'Energy record added!');
	});

	Route::get('/modules/energy/trend', function() {
		$trendData = [];
		// You can populate $trendData with your actual trend analytics data here
		return view('modules.energy-monitoring.trend', compact('trendData'));
	})->name('energy.trend');

	Route::get('/modules/energy/export-report', function() {
		return view('modules.energy.export-report');
	})->name('energy.exportReport');
// Removed unmatched closing brace here
// Users & Roles Management - Admin/Energy Officer only
use Illuminate\Http\Request as HttpRequest;
use App\Http\Controllers\Modules\FacilityController;
use App\Http\Controllers\Modules\EnergyController;
use App\Http\Controllers\Modules\MaintenanceController;


use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
		// Engineer Approval Toggle (AJAX)
		Route::post('/modules/facilities/{id}/toggle-engineer-approval', [FacilityController::class, 'toggleEngineerApproval'])->name('modules.facilities.toggle-engineer-approval');
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
		return response()->json(['actual_kwh' => null]);
	}
	[$year, $month] = explode('-', $monthYear);
	$record = \App\Models\EnergyRecord::where('facility_id', $facilityId)
		->where('year', $year)
		->where('month', str_pad($month, 2, '0', STR_PAD_LEFT))
		->first();
	return response()->json(['actual_kwh' => $record ? $record->actual_kwh : null]);
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

// Energy Monitoring Dashboard (Controller-based, for dynamic cards)
Route::get('/modules/energy-monitoring', [EnergyMonitoringController::class, 'index'])->name('modules.energy-monitoring.index');

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
				$r->actual_kwh,
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
				$actual = $monthRecords->sum('actual_kwh');
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

	   // (Modal-based creation: create route/view removed)

});
// =====================
// LOGOUT ROUTE (for Auth::logout)
// =====================
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
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
	Route::get('/modules/facilities/{id}/show', function($id) {
		$facility = \App\Models\Facility::findOrFail($id);
		// Compute 3-month average from first3months_data
		$first3mo = \DB::table('first3months_data')->where('facility_id', $id)->first();
		$months = [$first3mo?->month1, $first3mo?->month2, $first3mo?->month3];
		$validMonths = array_filter($months, fn($v) => $v !== null && $v !== '' && $v !== 0);
		$showAvg = count($validMonths) === 3;
		$avgKwh = $showAvg ? (array_sum($validMonths) / 3) : 0;
		return view('modules.facilities.show', compact('facility', 'showAvg', 'avgKwh'));
	})->name('modules.facilities.show');
	Route::get('/modules/facilities/{id}/edit', fn($id) => view('modules.facilities.edit', ['id' => $id]))->name('modules.facilities.edit');

	// Monthly Records per Facility
	Route::get('/modules/facilities/{facility}/monthly-records', function($facilityId) {
    $facility = \App\Models\Facility::findOrFail($facilityId);
    $records = \App\Models\EnergyRecord::where('facility_id', $facilityId)->orderByDesc('year')->orderByDesc('month')->get();
    return view('modules.facilities.monthly-record.records', compact('facility', 'records'));
})->name('facilities.monthly-records');

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
			'actual_kwh' => 'required|numeric',
			'unit_cost' => 'required|numeric',
			'status' => 'required|in:Paid,Unpaid,Pending',
			'meralco_bill_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
		]);
		$validated['unit_cost'] = 12.50;
		$validated['total_bill'] = $validated['actual_kwh'] * $validated['unit_cost'];

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
			'actual_kwh' => 'required|numeric',
			'unit_cost' => 'required|numeric',
			'status' => 'required|in:Paid,Unpaid,Pending',
		]);
		$validated['unit_cost'] = 12.50;
		$validated['total_bill'] = $validated['actual_kwh'] * $validated['unit_cost'];
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
	Route::get('/modules/energy/index', function(\Illuminate\Http\Request $request) {
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
			$actual = $monthRecords->sum('actual_kwh');
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
// =====================
// FACILITIES ENERGY PROFILE ROUTES
// =====================
Route::middleware(['auth', 'verified'])->group(function () {
	// Energy Profile per Facility
	Route::get('/modules/facilities/{facility}/energy-profile', function($facility) {
		$facilityModel = \App\Models\Facility::findOrFail($facility);
		$energyProfiles = $facilityModel->energyProfiles;
		// 3-Month average update logic removed
		return view('modules.facilities.energy-profile.index', compact('facilityModel', 'energyProfiles'));
	})->name('modules.facilities.energy-profile.index');

	// Store new energy profile (controller-based)
	Route::post('/modules/facilities/{facility}/energy-profile', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'store'])->name('modules.facilities.energy-profile.store');

	// Delete energy profile (controller, like monthly record)
	Route::delete('/modules/facilities/{facility}/energy-profile/{profile}', [\App\Http\Controllers\Modules\EnergyProfileController::class, 'destroy'])
		->name('modules.facilities.energy-profile.destroy');

	// Fallback for DELETE without profile id (returns 405)
	Route::delete('/modules/facilities/{facility}/energy-profile', function() {
    abort(405, 'Profile ID required for delete.');
});
});
// =====================
// LOGOUT ROUTE (for Auth::logout)
// =====================
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');