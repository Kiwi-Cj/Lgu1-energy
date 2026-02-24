<?php
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/modules/reports/index', 'modules.reports.index')->name('reports.index');
    Route::view('/modules/reports/energy', 'modules.reports.energy')->name('reports.energy');
    Route::get('/modules/reports/efficiency-summary', [\App\Http\Controllers\Reports\EfficiencySummaryReportController::class, 'show'])->name('reports.efficiency-summary');
    Route::view('/modules/reports/facilities', 'modules.reports.facilities')->name('reports.facilities');
    // Monthly report route for dashboard shortcut
    Route::view('/modules/reports/monthly', 'modules.reports.index')->name('reports.monthly');
    // AJAX endpoint for dashboard summary cards
    Route::get('/modules/reports/dashboard-summary', [\App\Http\Controllers\Reports\DashboardSummaryController::class, 'summary'])->name('reports.dashboard-summary');
    // Energy Report Excel Export
    Route::get('/modules/reports/energy-export', function (\Illuminate\Http\Request $request) {
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $energyReportRoute = Route::has('reports.energy') ? 'reports.energy' : 'modules.reports.energy';
            return redirect()
                ->route($energyReportRoute, array_filter($request->query()))
                ->with('error', 'Excel export is not available for staff accounts.');
        }

        $facilityId = $request->input('facility_id');
        $year = $request->input('year');
        $month = $request->input('month');
        $query = \App\Models\EnergyRecord::with('facility');
        if ($facilityId) {
            $query->where('facility_id', $facilityId);
        }
        if ($year) {
            $query->where('year', $year);
        }
        if ($month) {
            $query->where('month', $month);
        }
        $records = $query->orderByDesc('year')->orderByDesc('month')->get();
        $energyRows = [];
        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = $facility ? $facility->baseline_kwh : null;
            $actualKwh = $record->actual_kwh;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = 'stable';
            if ($variance !== null && $baseline !== null && $baseline != 0) {
                if ($variance > ($baseline * 0.05)) {
                    $trend = 'up';
                } elseif ($variance < -($baseline * 0.05)) {
                    $trend = 'down';
                }
            }
            $monthNum = (int)ltrim($record->month, '0');
            $monthName = date('M', mktime(0, 0, 0, $monthNum, 1));
            $monthYear = $monthName . ' ' . $record->year;
            $energyRows[] = [
                'facility' => $facility ? $facility->name : 'N/A',
                'month' => $monthYear,
                'actual_kwh' => number_format($actualKwh, 2),
                'baseline_kwh' => $baseline !== null ? number_format($baseline, 2) : 'N/A',
                'variance' => $variance !== null ? number_format($variance, 2) : 'N/A',
                'trend' => $trend,
            ];
        }
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EnergyReportExport($energyRows), 'energy_report.xlsx');
    })->name('reports.energy-export');
});


