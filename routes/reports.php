<?php
use App\Http\Controllers\Modules\EnergyController;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/modules/reports/index', '/modules/reports/energy')->name('reports.index');
    Route::get('/modules/reports/energy', [EnergyController::class, 'energyReport'])->name('reports.energy');
    Route::get('/modules/reports/efficiency-summary', [\App\Http\Controllers\Reports\EfficiencySummaryReportController::class, 'show'])->name('reports.efficiency-summary');
    Route::redirect('/modules/reports/facilities', '/modules/reports/energy')->name('reports.facilities');
    // Monthly report route for dashboard shortcut
    Route::redirect('/modules/reports/monthly', '/modules/reports/energy')->name('reports.monthly');
    // AJAX endpoint for dashboard summary cards
    Route::get('/modules/reports/dashboard-summary', [\App\Http\Controllers\Reports\DashboardSummaryController::class, 'summary'])->name('reports.dashboard-summary');
    Route::get('/modules/reports/efficiency-summary-export', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        if (RoleAccess::is($user, 'staff')) {
            return redirect()
                ->route('reports.efficiency-summary', array_filter($request->query()))
                ->with('error', 'Export download is not available for staff accounts.');
        }

        $role = strtolower((string) ($user?->role ?? ''));
        $facilities = ($role === 'staff') ? $user->facilities : \App\Models\Facility::all();
        $selectedFacility = $request->input('facility_id');
        $selectedRating = $request->input('rating');
        $exportFormat = strtolower(trim((string) $request->input('format', 'csv')));
        if (! in_array($exportFormat, ['csv', 'xlsx', 'pdf'], true)) {
            $exportFormat = 'csv';
        }

        if ($selectedFacility) {
            $facilities = $facilities->where('id', $selectedFacility);
        }

        $facilityIds = $facilities->pluck('id')->values();
        $recordsByFacility = \App\Models\EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->where(function ($mainScope) {
                $mainScope->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->whereNotNull('actual_kwh')
            ->where('actual_kwh', '>', 0)
            ->get(['facility_id', 'year', 'month', 'actual_kwh'])
            ->groupBy('facility_id');

        $hasOpenMaintenance = \App\Models\Maintenance::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('maintenance_type', '!=', 'Task')
            ->whereIn('maintenance_status', ['Pending', 'Ongoing'])
            ->select('facility_id')
            ->distinct()
            ->pluck('facility_id')
            ->flip();

        $lastAuditByFacility = \App\Models\MaintenanceHistory::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereNotNull('completed_date')
            ->orderByDesc('completed_date')
            ->get(['facility_id', 'completed_date'])
            ->groupBy('facility_id')
            ->map(fn ($rows) => $rows->first());

        $efficiencyRows = [];
        $highCount = 0;
        $mediumCount = 0;
        $flaggedCount = 0;
        foreach ($facilities as $facility) {
            $records = $recordsByFacility->get($facility->id, collect());
            $monthlyTotals = $records
                ->groupBy(function ($record) {
                    return sprintf('%04d-%02d', (int) $record->year, (int) $record->month);
                })
                ->map(function ($rows) {
                    return (float) $rows->sum('actual_kwh');
                });

            $avgMonthlyKwh = $monthlyTotals->count() > 0 ? (float) $monthlyTotals->avg() : null;
            $floorArea = (float) ($facility->floor_area ?? 0);
            $euiValue = ($avgMonthlyKwh !== null && $floorArea > 0)
                ? round($avgMonthlyKwh / $floorArea, 2)
                : null;
            $rating = '-';
            if ($euiValue !== null) {
                if ($euiValue < 5) {
                    $rating = 'High';
                } elseif ($euiValue < 10) {
                    $rating = 'Medium';
                } else {
                    $rating = 'Low';
                }
            }

            if ($selectedRating && $selectedRating !== 'all' && $selectedRating !== $rating) {
                continue;
            }

            if ($rating === 'High') {
                $highCount++;
            } elseif ($rating === 'Medium') {
                $mediumCount++;
            }

            $lastAudit = $lastAuditByFacility->get($facility->id);
            $lastAuditDate = $lastAudit
                ? \Carbon\Carbon::parse($lastAudit->completed_date)->format('M d, Y')
                : '-';

            $needsMaintenance = (($rating === 'Low') || $hasOpenMaintenance->has($facility->id));
            if ($needsMaintenance) {
                $flaggedCount++;
            }

            $ratingLabel = match ($rating) {
                'High' => 'Efficient',
                'Medium' => 'Moderate',
                'Low' => 'Needs Improvement',
                default => 'Not Evaluated',
            };
            $latestRecord = $records
                ->sortByDesc(fn ($record) => sprintf('%04d-%02d', (int) $record->year, (int) $record->month))
                ->first();

            $efficiencyRows[] = [
                'facility' => $facility->name,
                'avg_monthly_kwh' => $avgMonthlyKwh !== null ? number_format($avgMonthlyKwh, 2) : '-',
                'floor_area' => $floorArea > 0 ? number_format($floorArea, 0) : '-',
                'eui' => $euiValue !== null ? number_format($euiValue, 2) : '-',
                'rating' => $ratingLabel,
                'months_count' => $monthlyTotals->count(),
                'latest_period' => $latestRecord
                    ? \Carbon\Carbon::create((int) $latestRecord->year, (int) $latestRecord->month, 1)->format('M Y')
                    : 'No readings',
                'last_audit' => $lastAuditDate,
                'maintenance_status' => $needsMaintenance ? 'Requires Action' : 'No Immediate Action',
            ];
        }

        $selectedFacilityName = $selectedFacility
            ? optional(\App\Models\Facility::find($selectedFacility))->name ?? 'All Facilities'
            : 'All Facilities';
        $selectedRatingLabel = match ($selectedRating) {
            'High' => 'Efficient',
            'Medium' => 'Moderate',
            'Low' => 'Needs Improvement',
            default => 'All Efficiency Bands',
        };
        $generatedAt = now()->format('M d, Y h:i A');

        if ($exportFormat === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.efficiency-summary-pdf', compact(
                'efficiencyRows',
                'highCount',
                'mediumCount',
                'flaggedCount',
                'selectedFacilityName',
                'selectedRatingLabel',
                'generatedAt'
            ));
            return $pdf->download('efficiency_summary_report.pdf');
        }

        if ($exportFormat === 'xlsx') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\EfficiencySummaryReportExport($efficiencyRows),
                'efficiency_summary_report.xlsx'
            );
        }

        $filename = 'efficiency_summary_' . date('Ymd_His') . '.csv';
        return response()->streamDownload(function () use ($efficiencyRows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Facility Name', 'Average Monthly kWh', 'Floor Area (sqm)', 'EUI (kWh/sqm)', 'Efficiency Band', 'Months Included', 'Latest Period', 'Last Completed Maintenance', 'Action Status']);
            foreach ($efficiencyRows as $row) {
                fputcsv($handle, [
                    $row['facility'],
                    $row['avg_monthly_kwh'],
                    $row['floor_area'],
                    $row['eui'],
                    $row['rating'],
                    $row['months_count'],
                    $row['latest_period'],
                    $row['last_audit'],
                    $row['maintenance_status'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    })->middleware('download.confirmed')->name('reports.efficiency-summary-export');
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
        $exportFormat = strtolower(trim((string) $request->input('format', 'xlsx')));
        if (! in_array($exportFormat, ['xlsx', 'csv'], true)) {
            $exportFormat = 'xlsx';
        }
        $query = \App\Models\EnergyRecord::with('facility');
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
            $baseline = is_numeric($record->baseline_kwh ?? null)
                ? (float) $record->baseline_kwh
                : (is_numeric($facility?->baseline_kwh ?? null) ? (float) $facility->baseline_kwh : null);
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


