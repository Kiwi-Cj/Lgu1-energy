<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\EnergyRecord;
use App\Services\EnergyRecommendationService;
use App\Services\EnergyTrendService;
use Illuminate\Http\Request;

class EnergyReportController extends Controller
{
    private ?array $trendPercentThresholdsBySize = null;

    public function __construct(
        private readonly EnergyRecommendationService $energyRecommendationService,
        private readonly EnergyTrendService $energyTrendService
    ) {
    }

    public function exportPdf(Request $request)
    {
        $query = EnergyRecord::with(['facility', 'meter']);
        $query->where(function ($mainScope) {
            $mainScope->whereNull('meter_id')
                ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
        });
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        $year = $request->has('year') ? $request->input('year') : date('Y');
        if ($year) {
            $query->where('year', $year);
        }
        $month = $request->has('month') ? $request->input('month') : date('n');
        if ($month) {
            $query->where('month', $month);
        }
        $records = $query->orderBy('year')->orderBy('month')->get();

        $energyData = [];
        $totalActualKwh = 0.0;
        $totalBaselineKwh = 0.0;
        $totalVarianceKwh = 0.0;
        $totalEnergyCost = 0.0;

        $trendByRecordId = $this->energyTrendService->labelsFor($records);

        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = $record->baseline_kwh !== null ? (float) $record->baseline_kwh : null;
            $actualKwh = $record->actual_kwh !== null ? (float) $record->actual_kwh : 0.0;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = $this->energyTrendService->displayLabel($trendByRecordId[$record->id] ?? 'insufficient');
            $variancePercent = ($variance !== null && $baseline > 0) ? (($variance / $baseline) * 100) : null;
            $energyCost = is_numeric($record->energy_cost ?? null)
                ? (float) $record->energy_cost
                : ($actualKwh * (float) ($record->rate_per_kwh ?? 0));
            $floorArea = is_numeric($facility?->floor_area ?? null) ? (float) $facility->floor_area : null;
            $eui = ($floorArea !== null && $floorArea > 0) ? ($actualKwh / $floorArea) : null;
            $assessment = $this->assessmentForVariance($variancePercent, $baseline);
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
            $totalEnergyCost += $energyCost;

            $recommendation = $this->energyRecommendationService->generateFacilityRecommendation([
                'facility_name' => (string) ($facility?->name ?? ''),
                'facility_type' => (string) ($facility?->type ?? ''),
                'alert_level' => $this->assessmentAlertLevel($assessment),
                'trend_percent' => $variancePercent,
                'actual_kwh' => $actualKwh,
                'baseline_kwh' => $baseline,
                'floor_area' => $floorArea,
            ], false);

            $energyData[] = [
                'facility' => $facility ? $facility->name : 'N/A',
                'meter' => trim((string) ($record->meter?->meter_name ?? '')) ?: 'Main Meter',
                'meter_number' => trim((string) ($record->meter?->meter_number ?? '')),
                'month' => $monthYear,
                'actual_kwh' => number_format($actualKwh, 2),
                'baseline_kwh' => $baseline !== null ? number_format($baseline, 2) : 'N/A',
                'variance' => $variance !== null ? number_format($variance, 2) : 'N/A',
                'variance_percent' => $variancePercent !== null ? number_format($variancePercent, 2) . '%' : 'N/A',
                'energy_cost' => number_format($energyCost, 2),
                'eui' => $eui !== null ? number_format($eui, 2) : 'N/A',
                'assessment' => $assessment,
                'trend' => $trend,
                'recommendation' => $recommendation,
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

        if ($year && $month) {
            $monthKey = (int) $month;
            $selectedPeriod = ($months[$monthKey] ?? ('Month ' . $monthKey)) . ' ' . $year;
        } elseif ($year) {
            $selectedPeriod = 'Year ' . $year;
        } else {
            $selectedPeriod = 'All Periods';
        }

        $generatedAt = now()->format('M d, Y h:i A');
        $preparedBy = auth()->user()?->full_name ?? auth()->user()?->name ?? auth()->user()?->username ?? 'System User';
        $overallVariancePercent = $totalBaselineKwh > 0
            ? (($totalVarianceKwh / $totalBaselineKwh) * 100)
            : null;
        $overallAssessment = $this->assessmentForVariance($overallVariancePercent, $totalBaselineKwh);
        $executiveSummary = $this->buildExecutiveSummary(
            $selectedFacilityName,
            $selectedPeriod,
            $totalActualKwh,
            $totalBaselineKwh,
            $totalVarianceKwh,
            $overallVariancePercent
        );
        $primaryRecommendation = count($energyData) === 1
            ? (string) ($energyData[0]['recommendation'] ?? '')
            : $this->buildPortfolioRecommendation($overallAssessment, $overallVariancePercent);
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
            'generatedAt',
            'preparedBy',
            'totalEnergyCost',
            'overallVariancePercent',
            'overallAssessment',
            'executiveSummary',
            'primaryRecommendation'
        ));
        return $pdf->download('energy_report.pdf');
    }

    private function loadTrendHistory($selectedRecords, $year, $month)
    {
        $facilityIds = $selectedRecords->pluck('facility_id')->filter()->unique()->values();
        if ($facilityIds->isEmpty()) {
            return collect();
        }

        return EnergyRecord::with('facility')
            ->whereIn('facility_id', $facilityIds)
            ->where(function ($mainScope) {
                $mainScope->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->when($year && $month, function ($query) use ($year, $month) {
                $query->whereRaw('(year * 100 + month) <= ?', [((int) $year * 100) + (int) $month]);
            })
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function assessmentForVariance(?float $variancePercent, mixed $baseline): string
    {
        if (! is_numeric($baseline) || (float) $baseline <= 0 || $variancePercent === null) {
            return 'Baseline Required';
        }
        if ($variancePercent <= 0) {
            return 'Within Target';
        }
        if ($variancePercent <= 5) {
            return 'Slightly Above Baseline';
        }
        if ($variancePercent <= 10) {
            return 'Monitor Closely';
        }
        if ($variancePercent <= 20) {
            return 'High Consumption';
        }

        return 'Critical Consumption';
    }

    private function assessmentAlertLevel(string $assessment): string
    {
        return match ($assessment) {
            'Critical Consumption' => 'Critical',
            'High Consumption' => 'High',
            'Monitor Closely' => 'Warning',
            'Baseline Required' => 'No Data',
            default => 'Normal',
        };
    }

    private function buildExecutiveSummary(string $facility, string $period, float $actual, float $baseline, float $variance, ?float $variancePercent): string
    {
        if ($baseline <= 0 || $variancePercent === null) {
            return sprintf('%s recorded %s kWh for %s. A valid baseline is required to evaluate performance.', $facility, number_format($actual, 2), $period);
        }

        $direction = $variance >= 0 ? 'above' : 'below';
        return sprintf(
            '%s consumed %s kWh for %s, which is %s kWh or %s%% %s the %s kWh baseline.',
            $facility,
            number_format($actual, 2),
            $period,
            number_format(abs($variance), 2),
            number_format(abs($variancePercent), 2),
            $direction,
            number_format($baseline, 2)
        );
    }

    private function buildPortfolioRecommendation(string $assessment, ?float $variancePercent): string
    {
        if ($variancePercent === null) {
            return 'Complete missing facility baselines before using this report for comparative performance decisions.';
        }
        if (in_array($assessment, ['High Consumption', 'Critical Consumption'], true)) {
            return 'Prioritize facilities with the largest positive variance, validate their meter readings, and assign corrective actions to the responsible facility teams.';
        }

        return 'Continue monthly monitoring and investigate facilities whose consumption moves above their approved baseline.';
    }

    public function index()
    {
        return redirect()->route('modules.reports.energy');
    }

    private function buildTrendLabelMap($records): array
    {
        $thresholds = $this->getTrendPercentThresholdsBySize();
        $trendByRecordId = [];

        $records
            ->groupBy(fn ($row) => (int) $row->facility_id . ':' . (int) ($row->meter_id ?? 0))
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

                        $trend = 'Insufficient Historical Data';
                        $actual = is_numeric($record->actual_kwh ?? null) ? (float) $record->actual_kwh : 0.0;
                        if ($reference !== null && $reference > 0) {
                            $trend = 'Stable';
                            $trendPercent = (($actual - $reference) / $reference) * 100;
                            if ($trendPercent > $threshold) {
                                $trend = 'Increasing';
                            } elseif ($trendPercent < -$threshold) {
                                $trend = 'Decreasing';
                            }
                        }

                        $trendLabel = $trend;

                        if ($actual > 0) {
                            $history[] = $actual;
                            if (count($history) >= 3) {
                                $lastThree = array_slice($history, -3);
                                if ($lastThree[2] > $lastThree[1] && $lastThree[1] > $lastThree[0]) {
                                    $trendLabel .= ' [3-Month Spike]';
                                }
                            }
                        }

                        $trendByRecordId[$record->id] = $trendLabel;
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
