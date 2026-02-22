<?php

use App\Http\Controllers\EnergyActionController;
use App\Http\Controllers\Reports\EnergyReportController;
use App\Models\EnergyRecord;
use App\Models\Facility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Route;

// Energy Actions
Route::get('/energy-actions', [EnergyActionController::class, 'index']);
Route::get('/energy-actions/create', [EnergyActionController::class, 'create']);
Route::post('/energy-actions/store', [EnergyActionController::class, 'store']);

Route::get('/modules/energy/trend', function (Request $request) {
    $user = auth()->user();
    $isStaff = strtolower((string) ($user?->role ?? '')) === 'staff';
    $facilityQuery = Facility::query()->orderBy('name');
    if ($isStaff) {
        $staffFacilityIds = $user->facilities()->pluck('facilities.id')->toArray();
        $facilityQuery->whereIn('id', $staffFacilityIds);
    }
    $facilities = $facilityQuery->get();
    $scopeFacilityIds = $facilities->pluck('id')->toArray();

    $years = EnergyRecord::query()
        ->when(!empty($scopeFacilityIds), fn($q) => $q->whereIn('facility_id', $scopeFacilityIds))
        ->select('year')
        ->distinct()
        ->orderByDesc('year')
        ->pluck('year')
        ->toArray();

    $selectedYear = (int) ($request->year ?? ($years[0] ?? 0));
    $months = collect();
    if ($selectedYear) {
        for ($m = 1; $m <= 12; $m++) {
            $months->push([
                'value' => sprintf('%04d-%02d', $selectedYear, $m),
                'label' => date('F', strtotime($selectedYear . '-' . $m . '-01')),
            ]);
        }
    }

    $trendData = [];
    $records = collect();
    $selectedFacilityId = (int) $request->get('facility_id', 0);
    $selectedMonth = $request->get('month');

    if ($selectedFacilityId > 0 && $selectedYear > 0) {
        $query = EnergyRecord::query()->where('facility_id', $selectedFacilityId);

        if (!empty($selectedMonth)) {
            [$monthYear, $monthNumber] = array_map('intval', explode('-', $selectedMonth));
            $anchor = Carbon::create($monthYear, $monthNumber, 1);
            $start = $anchor->copy()->subMonths(5);
            $query->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [(int) $start->format('Ym'), (int) $anchor->format('Ym')]);
        } else {
            $query->where('year', $selectedYear);
        }

        $records = $query
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    if ($records->count() > 0) {
        $trendData['labels'] = $records->map(fn($r) => date('M Y', strtotime($r->year . '-' . $r->month . '-01')))->toArray();
        $trendData['values'] = $records->map(fn($r) => $r->actual_kwh)->toArray();
        $trendData['period'] = $records->first() && $records->last()
            ? date('F Y', strtotime($records->first()->year . '-' . $records->first()->month . '-01')) . ' - ' . date('F Y', strtotime($records->last()->year . '-' . $records->last()->month . '-01'))
            : '';
    }

    $values = collect($trendData['values'] ?? []);
    $totalConsumption = (float) $values->sum();
    $peakUsage = (float) ($values->max() ?? 0);
    $lowestUsage = (float) ($values->min() ?? 0);

    $trendDirection = 'Stable';
    $trendChangePercent = null;
    $trendInsight = 'Select a facility and period to generate trend analysis.';
    if ($values->count() >= 2) {
        $first = (float) $values->first();
        $last = (float) $values->last();
        if ($first > 0) {
            $trendChangePercent = (($last - $first) / $first) * 100;
        }

        if ($last > $first) {
            $trendDirection = 'Increasing';
        } elseif ($last < $first) {
            $trendDirection = 'Decreasing';
        }

        $changeText = $trendChangePercent !== null
            ? number_format(abs($trendChangePercent), 2) . '%'
            : number_format(abs($last - $first), 2) . ' kWh';

        $trendInsight = match ($trendDirection) {
            'Increasing' => "Energy use is trending upward by {$changeText}. Validate operating schedules and inspect major loads.",
            'Decreasing' => "Energy use is trending downward by {$changeText}. Current controls appear effective; sustain the same operating discipline.",
            default => 'Energy use is relatively stable. Continue routine monitoring and preventive checks.',
        };
    } elseif ($values->count() === 1) {
        $trendInsight = 'Only one record is available for the selected period. Add more monthly records for a reliable trend.';
    }

    return view('modules.energy-monitoring.trend', compact(
        'trendData',
        'facilities',
        'months',
        'years',
        'selectedYear',
        'totalConsumption',
        'peakUsage',
        'lowestUsage',
        'trendDirection',
        'trendChangePercent',
        'trendInsight'
    ));
})->name('energy.trend');

Route::get('/modules/energy/export-report', function () {
    $facilities = Facility::orderBy('name')->get();
    return view('modules.energy.export-report', compact('facilities'));
})->name('energy.exportReport');

Route::get('/modules/energy/export-pdf', [EnergyReportController::class, 'exportPdf'])->name('modules.energy.export-pdf');

// AJAX route to check for duplicate energy record
Route::get('/modules/energy/check-duplicate', function (HttpRequest $request) {
    $exists = EnergyRecord::where('facility_id', $request->facility_id)
        ->where('month', $request->month)
        ->where('year', $request->year)
        ->exists();
    return response()->json(['exists' => $exists]);
})->name('modules.energy.check-duplicate');

// AJAX route to get kWh Consumed for a facility and month-year
Route::get('/modules/energy/get-kwh-consumed', function (HttpRequest $request) {
    $facilityId = $request->facility_id;
    $monthYear = $request->month; // format: YYYY-MM
    if (!$facilityId || !$monthYear) {
        return response()->json(['actual_kwh' => null]);
    }
    [$year, $month] = explode('-', $monthYear);
    $record = EnergyRecord::where('facility_id', $facilityId)
        ->where('year', $year)
        ->where('month', str_pad($month, 2, '0', STR_PAD_LEFT))
        ->first();
    return response()->json(['actual_kwh' => $record ? $record->actual_kwh : null]);
})->name('modules.energy.get-kwh-consumed');

// Energy Monitoring Excel Export (CSV fallback)
Route::get('/modules/energy/export-excel', function (Request $request) {
    $query = EnergyRecord::with('facility');
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
    $callback = function () use ($records, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
        foreach ($records as $r) {
            $monthName = '';
            if ($r->month) {
                $monthNum = (int) ltrim($r->month, '0');
                $monthName = $monthNum >= 1 && $monthNum <= 12 ? date('M', mktime(0, 0, 0, $monthNum, 1)) : $r->month;
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

Route::middleware(['auth', 'verified'])->group(function () {
    // Annual Energy Summary Excel Export (CSV fallback)
    Route::get('/modules/energy/annual/export-excel', function (Request $request) {
        $years = range(date('Y'), date('Y') - 10);
        $selectedYear = $request->query('year', date('Y'));
        $facilities = Facility::all();
        $selectedFacility = $request->query('facility_id', '');

        $query = EnergyRecord::with('facility');
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
                    ? ((float) $record->actual_kwh - (float) $recordBaseline) / (float) $recordBaseline * 100
                    : null;
                $monthAlerts[] = $getAlertBySize($deviation, $recordBaseline);
            }
            $diff = $actual - $baseline;
            $status = $getHighestAlert($monthAlerts);
            $monthlyBreakdown[] = [
                'Month' => date('M', mktime(0, 0, 0, $m, 1)),
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
        $callback = function () use ($monthlyBreakdown, $columns) {
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

    // Annual Energy Summary PDF Export
    Route::get('/modules/energy/annual/export-pdf', function (Request $request) {
        $selectedYear = $request->query('year', date('Y'));
        $selectedFacility = $request->query('facility_id', '');

        $facilities = Facility::orderBy('name')->get();
        $selectedFacilityName = 'All Facilities';
        if ($selectedFacility) {
            $selectedFacilityModel = $facilities->firstWhere('id', (int) $selectedFacility);
            if ($selectedFacilityModel) {
                $selectedFacilityName = $selectedFacilityModel->name;
            }
        }

        $query = EnergyRecord::with('facility');
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
                    ? ((float) $record->actual_kwh - (float) $recordBaseline) / (float) $recordBaseline * 100
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
        $generatedAt = now()->format('F d, Y h:i A');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'modules.energy-monitoring.annual-pdf',
            compact(
                'selectedYear',
                'selectedFacilityName',
                'monthlyBreakdown',
                'totalActualKwh',
                'annualBaseline',
                'annualDifference',
                'annualStatus',
                'generatedAt'
            )
        )->setPaper('a4', 'portrait');

        return $pdf->download('annual_energy_monitoring_' . $selectedYear . '.pdf');
    })->name('modules.energy.annual.export-pdf');
});
