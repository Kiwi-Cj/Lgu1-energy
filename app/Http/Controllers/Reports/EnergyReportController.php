<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class EnergyReportController extends Controller
{
    private ?array $trendPercentThresholdsBySize = null;

    public function exportPdf(Request $request)
    {
        $query = \App\Models\EnergyRecord::with('facility');
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        $records = $query->orderBy('year')->orderBy('month')->get();

        $energyData = [];
        $totalActualKwh = 0.0;
        $totalBaselineKwh = 0.0;
        $totalVarianceKwh = 0.0;

        $trendByRecordId = $this->buildTrendLabelMap($records);

        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = $record->baseline_kwh !== null ? (float) $record->baseline_kwh : null;
            $actualKwh = $record->actual_kwh !== null ? (float) $record->actual_kwh : 0.0;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = $trendByRecordId[$record->id] ?? 'Stable';
            $monthNum = (int)ltrim($record->month, '0');
            $monthName = date('M', mktime(0, 0, 0, $monthNum, 1));
            $monthYear = $monthName . ' ' . $record->year;

            $totalActualKwh += $actualKwh;
            if ($baseline !== null) {
                $totalBaselineKwh += $baseline;
            }
            if ($variance !== null) {
                $totalVarianceKwh += $variance;
            }

            $energyData[] = [
                'facility' => $facility ? $facility->name : 'N/A',
                'month' => $monthYear,
                'actual_kwh' => number_format($actualKwh, 2),
                'baseline_kwh' => $baseline !== null ? number_format($baseline, 2) : 'N/A',
                'variance' => $variance !== null ? number_format($variance, 2) : 'N/A',
                'trend' => $trend,
            ];
        }

        $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
        $selectedFacilityName = 'All Facilities';
        if ($request->filled('facility_id')) {
            $facility = \App\Models\Facility::find($request->facility_id);
            if ($facility) {
                $selectedFacilityName = $facility->name;
            }
        }

        if ($request->filled('year') && $request->filled('month')) {
            $monthKey = (int) $request->month;
            $selectedPeriod = ($months[$monthKey] ?? ('Month ' . $monthKey)) . ' ' . $request->year;
        } elseif ($request->filled('year')) {
            $selectedPeriod = 'Year ' . $request->year;
        } else {
            $selectedPeriod = 'All Periods';
        }

        $generatedAt = now()->format('M d, Y h:i A');
        $columns = ['facility', 'month', 'actual_kwh', 'baseline_kwh', 'variance', 'trend'];
        $totalUsage = $totalActualKwh;
        $pdf = \PDF::loadView('admin.reports.energy-pdf', compact(
            'energyData',
            'totalUsage',
            'columns',
            'totalActualKwh',
            'totalBaselineKwh',
            'totalVarianceKwh',
            'selectedFacilityName',
            'selectedPeriod',
            'generatedAt'
        ));
        return $pdf->download('energy_report.pdf');
    }

    public function index()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $facilityIds = ($role === 'staff') ? $user->facilities->pluck('id')->toArray() : null;
        $query = \DB::table('energy_usages')
            ->join('facilities', 'energy_usages.facility_id', '=', 'facilities.id')
            ->select('facilities.name as facility', 'energy_usages.usage', 'energy_usages.date');
        if ($facilityIds) {
            $query->whereIn('energy_usages.facility_id', $facilityIds);
        }
        $energyData = $query
            ->orderBy('energy_usages.date', 'desc')
            ->get()
            ->map(function($row) {
                return [
                    'facility' => $row->facility,
                    'usage' => $row->usage,
                    'date' => $row->date,
                ];
            })->toArray();

        $totalUsage = collect($energyData)->sum('usage');

        return view('admin.reports.energy', compact('energyData', 'totalUsage'));
    }

    private function buildTrendLabelMap($records): array
    {
        $thresholds = $this->getTrendPercentThresholdsBySize();
        $trendByRecordId = [];

        $records
            ->groupBy('facility_id')
            ->each(function ($facilityRecords) use (&$trendByRecordId, $thresholds) {
                $history = [];

                $facilityRecords
                    ->sortBy(fn ($row) => sprintf('%04d-%02d-%06d', (int) $row->year, (int) $row->month, (int) $row->id))
                    ->each(function ($record) use (&$history, &$trendByRecordId, $thresholds) {
                        $baseline = is_numeric($record->baseline_kwh ?? null) ? (float) $record->baseline_kwh : null;
                        $facilityBaseline = is_numeric(optional($record->facility)->baseline_kwh ?? null) ? (float) optional($record->facility)->baseline_kwh : null;
                        $sizeLabel = Facility::resolveSizeLabelFromBaseline($baseline ?? $facilityBaseline) ?? 'Small';
                        $threshold = $this->resolveTrendPercentTriggerForSize($sizeLabel, $thresholds);

                        $reference = null;
                        $historyCount = count($history);
                        if ($historyCount >= 3) {
                            $reference = array_sum(array_slice($history, -3)) / 3;
                        } elseif ($historyCount >= 1) {
                            $reference = end($history);
                        }

                        $trend = 'Stable';
                        $actual = is_numeric($record->actual_kwh ?? null) ? (float) $record->actual_kwh : 0.0;
                        if ($reference !== null && $reference > 0) {
                            $trendPercent = (($actual - $reference) / $reference) * 100;
                            if ($trendPercent > $threshold) {
                                $trend = 'Increasing';
                            } elseif ($trendPercent < -$threshold) {
                                $trend = 'Decreasing';
                            }
                        }

                        $trendByRecordId[$record->id] = $trend;

                        if ($actual > 0) {
                            $history[] = $actual;
                        }
                    });
            });

        return $trendByRecordId;
    }

    private function resolveTrendPercentTriggerForSize(string $sizeLabel, ?array $thresholds = null): float
    {
        $sizeKey = match (strtolower(str_replace('_', '-', trim($sizeLabel)))) {
            'small' => 'small',
            'small-medium', 'small medium' => 'small', // legacy fallback
            'medium' => 'medium',
            'large' => 'large',
            'extra-large', 'extra large', 'xlarge' => 'xlarge',
            default => 'small',
        };

        $all = $thresholds ?? $this->getTrendPercentThresholdsBySize();

        return (float) ($all[$sizeKey] ?? $all['small'] ?? 0);
    }

    private function getTrendPercentThresholdsBySize(): array
    {
        if ($this->trendPercentThresholdsBySize !== null) {
            return $this->trendPercentThresholdsBySize;
        }

        return $this->trendPercentThresholdsBySize = [
            'small' => 10,
            'medium' => 7,
            'large' => 4,
            'xlarge' => 2,
        ];
    }
}
