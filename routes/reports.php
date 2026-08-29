<?php
use App\Http\Controllers\Modules\EnergyController;
use App\Support\RoleAccess;
use App\Support\SystemSettings;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/modules/reports/index', '/modules/reports/energy')->name('reports.index');
    Route::get('/modules/reports/energy/{facility}/{year}/annual-export', [EnergyController::class, 'exportAnnualSummary'])
        ->whereNumber('facility')
        ->whereNumber('year')
        ->middleware('download.confirmed')
        ->name('reports.energy-annual-export');
    Route::get('/modules/reports/performance-summary', [\App\Http\Controllers\Reports\EnergyPerformanceSummaryController::class, 'show'])
        ->name('reports.performance-summary');
    Route::redirect('/modules/reports/facilities', '/modules/reports/energy')->name('reports.facilities');
    // Monthly report route for dashboard shortcut
    Route::redirect('/modules/reports/monthly', '/modules/reports/energy')->name('reports.monthly');
    // AJAX endpoint for dashboard summary cards
    Route::get('/modules/reports/dashboard-summary', [\App\Http\Controllers\Reports\DashboardSummaryController::class, 'summary'])->name('reports.dashboard-summary');
    // Energy Report Excel / CSV Export
    Route::get('/modules/reports/energy-export', function (\Illuminate\Http\Request $request) {
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $energyReportRoute = Route::has('reports.energy') ? 'reports.energy' : 'modules.reports.energy';
            return redirect()
                ->route($energyReportRoute, array_filter($request->query()))
                ->with('error', 'Export download is not available for staff accounts.');
        }

        $facilityId = $request->input('facility_id');
        $year = $request->has('year') ? $request->input('year') : date('Y');
        $month = $request->has('month') ? $request->input('month') : date('n');
        $exportFormat = strtolower(trim((string) $request->input('format', SystemSettings::defaultExportFormat(['xlsx', 'csv']))));
        if (! in_array($exportFormat, ['xlsx', 'csv'], true)) {
            $exportFormat = 'xlsx';
        }
        $query = \App\Models\EnergyRecord::with(['facility.energyProfiles', 'meter']);
        $query->where(function ($mainScope) {
            $mainScope->whereNull('meter_id')
                ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
        });
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
        $trendService = app(\App\Services\EnergyTrendService::class);
        $trendByRecordId = $trendService->labelsFor($records);
        $energyRows = [];
        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = \App\Support\BaselineResolver::forRecord($record, $facility);
            $actualKwh = $record->actual_kwh;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = $trendService->displayLabel($trendByRecordId[$record->id] ?? 'insufficient');
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
        $filename = 'energy_report.' . $exportFormat;
        $writerType = $exportFormat === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\EnergyReportExport($energyRows),
            $filename,
            $writerType
        );
    })->middleware('download.confirmed')->name('reports.energy-export');
});


