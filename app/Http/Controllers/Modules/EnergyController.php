<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Support\RoleAccess;
use App\Services\EnergyTrendService;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    private ?array $trendPercentThresholdsBySize = null;

    public function __construct(private readonly EnergyTrendService $energyTrendService)
    {
    }

    public function destroy($id)
    {
        $usage = EnergyRecord::findOrFail($id);
        
        if ($response = $this->denyStaffCrossFacilityAccess($usage->facility_id, 'delete')) {
            return $response;
        }
        
        $usage->delete();

        $params = $this->buildIndexParams(request());

        return redirect()->route('modules.energy-monitoring.index', $params)->with('success', 'Energy record deleted successfully!');
    }

    public function edit($id)
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function update(Request $request, $id)
    {
        $usage = EnergyRecord::findOrFail($id);
        
        if ($response = $this->denyStaffCrossFacilityAccess($usage->facility_id, 'update')) {
            return $response;
        }
        
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
            'actual_kwh' => 'nullable|numeric|required_without:kwh_consumed',
            'kwh_consumed' => 'nullable|numeric|required_without:actual_kwh',
            'energy_cost' => 'nullable|numeric',
            'rate_per_kwh' => 'nullable|numeric',
            'baseline_kwh' => 'nullable|numeric',
            'alert' => 'nullable|string',
        ]);

        if ($response = $this->denyStaffCrossFacilityAccess((int) $validated['facility_id'], 'update', true)) {
            return $response;
        }

        $facility = Facility::find($validated['facility_id']);
        $usage->update($this->buildEnergyRecordPayload($request, $validated, $facility, false));

        $params = $this->buildIndexParams($request);

        return redirect()->route('modules.energy-monitoring.index', $params)
            ->with('success', 'Energy record updated successfully!');
    }

    public function create()
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
            'actual_kwh' => 'nullable|numeric|required_without:kwh_consumed',
            'kwh_consumed' => 'nullable|numeric|required_without:actual_kwh',
            'energy_cost' => 'nullable|numeric',
            'rate_per_kwh' => 'nullable|numeric',
            'baseline_kwh' => 'nullable|numeric',
            'alert' => 'nullable|string',
            'bill_image' => 'nullable|image|max:4096',
            'meralco_bill' => 'nullable|image|max:4096',
        ]);

        if ($response = $this->denyStaffCrossFacilityAccess((int) $validated['facility_id'], 'create', true)) {
            return $response;
        }

        $exists = EnergyRecord::where('facility_id', $validated['facility_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withInput()->withErrors(['duplicate' => 'An energy record for this facility and month/year already exists.']);
        }

        $facility = Facility::find($validated['facility_id']);
        EnergyRecord::create($this->buildEnergyRecordPayload($request, $validated, $facility, true));

        $params = $this->buildIndexParams($request);

        return redirect()->route('modules.energy-monitoring.index', $params)->with('success', 'Energy record added successfully!');
    }

    public function index(Request $request)
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function show($id)
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function energyReport(Request $request)
    {
        // Get all energy records with facility relationships
        $facilityId = $request->input('facility_id');
        $year = $request->input('year');
        $month = $request->has('month') ? $request->input('month') : date('n');
        $query = EnergyRecord::with('facility');
        $query->where(function ($mainScope) {
            $mainScope->whereNull('meter_id')
                ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
        });
        if ($facilityId) {
            $query->where('facility_id', $facilityId);
        }
        if ($year) {
            $query->where('year', $year);
        } else {
            $query->where('year', date('Y'));
        }
        if ($month) {
            $query->where('month', $month);
        }
        $records = $query
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $trendByRecordId = $this->energyTrendService->labelsFor($records);
        $energyRows = [];

        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = $record->baseline_kwh;
            $actualKwh = $record->actual_kwh;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = $trendByRecordId[$record->id] ?? 'insufficient';

            // Format month display
            $monthNum = (int)ltrim($record->month, '0');
            $monthName = date('M', mktime(0, 0, 0, $monthNum, 1));
            $monthYear = $monthName . ' ' . $record->year;

            $energyRows[] = [
                'facility' => $facility ? $facility->name : 'N/A',
                'month' => $monthYear,
                'actual_kwh' => number_format($actualKwh, 2),
                'baseline_kwh' => $baseline !== null ? number_format($baseline, 2) : '',
                'variance' => $variance !== null ? number_format($variance, 2) : '',
                'trend' => $trend,
                'summary_key' => (int) $record->facility_id . ':' . (int) $record->year,
            ];
        }

        $annualSummaries = $this->buildAnnualReportSummaries($records);
        
        $facilities = Facility::all();
        $years = EnergyRecord::select('year')->distinct()->orderByDesc('year')->pluck('year');
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $selectedMonth = (string) $month;

        return view('modules.reports.energy', compact('energyRows', 'annualSummaries', 'facilities', 'years', 'role', 'user', 'selectedMonth'));
    }

    public function exportAnnualSummary(Request $request, Facility $facility, int $year)
    {
        abort_unless(RoleAccess::can($request->user(), 'export_reports'), 403, 'You do not have permission to export reports.');
        abort_unless($year >= 2000 && $year <= 2100, 404);

        if (RoleAccess::is($request->user(), 'staff')) {
            $hasFacilityAccess = (string) ($request->user()?->facility_id ?? '') === (string) $facility->id
                || $request->user()?->facilities()->whereKey($facility->id)->exists();
            abort_unless($hasFacilityAccess, 403, 'You do not have permission to export this facility report.');
        }

        $seedRecord = EnergyRecord::query()
            ->where('facility_id', $facility->id)
            ->where('year', $year)
            ->where(function ($mainScope) {
                $mainScope->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->firstOrFail();

        $summaryKey = (int) $facility->id . ':' . $year;
        $summary = $this->buildAnnualReportSummaries(collect([$seedRecord]))[$summaryKey] ?? null;
        abort_unless($summary, 404);

        $format = strtolower((string) $request->query('format', 'csv'));
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $baseFilename = \Illuminate\Support\Str::slug($facility->name)
            . '-annual-energy-summary-' . $year;

        if ($format === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'modules.reports.annual-summary-pdf',
                [
                    'summary' => $summary,
                    'generatedAt' => now()->format('F d, Y h:i A'),
                ]
            )
                ->setPaper('a4', 'landscape')
                ->download($baseFilename . '.pdf');
        }

        return response()->streamDownload(function () use ($summary) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Annual Energy Summary']);
            fputcsv($stream, ['Facility', $summary['facility']]);
            fputcsv($stream, ['Year', $summary['year']]);
            fputcsv($stream, ['Months Recorded', $summary['months_recorded'] . ' of 12']);
            fputcsv($stream, []);
            fputcsv($stream, ['Month', 'Actual kWh', 'Baseline kWh', 'Variance kWh', 'Energy Cost PHP', 'Change %', 'Direction']);

            foreach ($summary['months'] as $month) {
                fputcsv($stream, [
                    $month['label'],
                    $month['actual'],
                    $month['baseline'],
                    $month['variance'],
                    $month['cost'],
                    $month['change_percent'],
                    ucfirst($month['direction']),
                ]);
            }

            fputcsv($stream, []);
            fputcsv($stream, ['Essential Annual Metrics', 'Value']);
            fputcsv($stream, ['Total Actual kWh', $summary['total_actual']]);
            fputcsv($stream, ['Total Baseline kWh', $summary['total_baseline']]);
            fputcsv($stream, ['Total Variance kWh', $summary['total_variance']]);
            fputcsv($stream, ['Variance %', $summary['variance_percent']]);
            fputcsv($stream, ['Average per Recorded Month kWh', $summary['average_actual']]);
            fputcsv($stream, ['Total Energy Cost PHP', $summary['total_cost']]);
            fputcsv($stream, ['Annual Performance', $summary['annual_status']]);
            fputcsv($stream, ['Peak Month', $summary['peak_month']['label'] ?? 'N/A']);
            fputcsv($stream, ['Peak Month Actual kWh', $summary['peak_month']['actual'] ?? null]);
            fputcsv($stream, ['Lowest Month', $summary['lowest_month']['label'] ?? 'N/A']);
            fputcsv($stream, ['Lowest Month Actual kWh', $summary['lowest_month']['actual'] ?? null]);
            fputcsv($stream, ['Months Above Baseline', $summary['months_above_baseline']]);
            fputcsv($stream, ['Months Below Baseline', $summary['months_below_baseline']]);
            fputcsv($stream, ['Largest Increase', $summary['peak_increase']['change_percent'] ?? null]);
            fputcsv($stream, ['Largest Increase Month', $summary['peak_increase']['label'] ?? 'N/A']);
            fputcsv($stream, ['Largest Decrease', $summary['peak_drop']['change_percent'] ?? null]);
            fputcsv($stream, ['Largest Decrease Month', $summary['peak_drop']['label'] ?? 'N/A']);
            fclose($stream);
        }, $baseFilename . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildAnnualReportSummaries($selectedRecords): array
    {
        $summaryKeys = $selectedRecords
            ->map(fn ($record) => (int) $record->facility_id . ':' . (int) $record->year)
            ->unique()
            ->values();

        if ($summaryKeys->isEmpty()) {
            return [];
        }

        $facilityIds = $selectedRecords->pluck('facility_id')->map(fn ($id) => (int) $id)->unique()->values();
        $years = $selectedRecords->pluck('year')->map(fn ($year) => (int) $year)->unique()->values();

        $annualRecords = EnergyRecord::with('facility:id,name')
            ->whereIn('facility_id', $facilityIds)
            ->whereIn('year', $years)
            ->where(function ($mainScope) {
                $mainScope->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
            })
            ->orderBy('month')
            ->orderBy('id')
            ->get(['id', 'facility_id', 'meter_id', 'year', 'month', 'actual_kwh', 'baseline_kwh', 'energy_cost']);

        $recordsByKey = $annualRecords->groupBy(
            fn ($record) => (int) $record->facility_id . ':' . (int) $record->year
        );

        return $summaryKeys->mapWithKeys(function (string $key) use ($recordsByKey) {
            $records = $recordsByKey->get($key, collect());
            $facility = $records->first()?->facility;
            [$facilityId, $year] = array_map('intval', explode(':', $key, 2));
            $recordsByMonth = $records->groupBy(fn ($record) => (int) $record->month);
            $months = [];
            $previousActual = null;

            for ($month = 1; $month <= 12; $month++) {
                $monthRecords = $recordsByMonth->get($month, collect());
                $actualValues = $monthRecords
                    ->pluck('actual_kwh')
                    ->filter(fn ($value) => is_numeric($value));
                $baselineValues = $monthRecords
                    ->pluck('baseline_kwh')
                    ->filter(fn ($value) => is_numeric($value));
                $costValues = $monthRecords
                    ->pluck('energy_cost')
                    ->filter(fn ($value) => is_numeric($value));
                $actual = $actualValues->isNotEmpty() ? (float) $actualValues->sum() : null;
                $baseline = $baselineValues->isNotEmpty() ? (float) $baselineValues->sum() : null;
                $cost = $costValues->isNotEmpty() ? (float) $costValues->sum() : null;
                $changePercent = null;

                if ($actual !== null && $previousActual !== null && $previousActual > 0) {
                    $changePercent = round((($actual - $previousActual) / $previousActual) * 100, 2);
                }

                $months[] = [
                    'month' => $month,
                    'label' => date('M', mktime(0, 0, 0, $month, 1)),
                    'actual' => $actual,
                    'baseline' => $baseline,
                    'variance' => ($actual !== null && $baseline !== null) ? round($actual - $baseline, 2) : null,
                    'cost' => $cost,
                    'change_percent' => $changePercent,
                    'direction' => $changePercent === null
                        ? 'none'
                        : ($changePercent > 0 ? 'up' : ($changePercent < 0 ? 'down' : 'stable')),
                ];

                if ($actual !== null) {
                    $previousActual = $actual;
                }
            }

            $recordedMonths = collect($months)->whereNotNull('actual');
            $baselineMonths = collect($months)->whereNotNull('baseline');
            $costMonths = collect($months)->whereNotNull('cost');
            $comparableMonths = collect($months)->whereNotNull('variance');
            $totalActual = $recordedMonths->isNotEmpty() ? (float) $recordedMonths->sum('actual') : null;
            $totalBaseline = $baselineMonths->isNotEmpty() ? (float) $baselineMonths->sum('baseline') : null;
            $latestMonth = $recordedMonths->last();
            $comparableBaseline = (float) $comparableMonths->sum('baseline');
            $totalVariance = $comparableMonths->isNotEmpty()
                ? round((float) $comparableMonths->sum('variance'), 2)
                : null;
            $variancePercent = $totalVariance !== null && $comparableBaseline > 0
                ? round(($totalVariance / $comparableBaseline) * 100, 2)
                : null;
            $peakMonth = $recordedMonths->sortByDesc('actual')->first();
            $lowestMonth = $recordedMonths->sortBy('actual')->first();
            $changedMonths = collect($months)->whereNotNull('change_percent');
            $peakIncrease = $changedMonths->where('change_percent', '>', 0)->sortByDesc('change_percent')->first();
            $peakDrop = $changedMonths->where('change_percent', '<', 0)->sortBy('change_percent')->first();
            $annualStatus = $variancePercent === null
                ? 'No Baseline'
                : ($variancePercent > 0.5
                    ? 'Above Baseline'
                    : ($variancePercent < -0.5 ? 'Below Baseline' : 'On Baseline'));

            return [$key => [
                'facility_id' => $facilityId,
                'facility' => $facility?->name ?? 'Facility',
                'year' => $year,
                'months_recorded' => $recordedMonths->count(),
                'total_actual' => $totalActual !== null ? round($totalActual, 2) : null,
                'total_baseline' => $totalBaseline !== null ? round($totalBaseline, 2) : null,
                'total_variance' => $totalVariance,
                'variance_percent' => $variancePercent,
                'average_actual' => $recordedMonths->isNotEmpty()
                    ? round($totalActual / $recordedMonths->count(), 2)
                    : null,
                'total_cost' => $costMonths->isNotEmpty() ? round((float) $costMonths->sum('cost'), 2) : null,
                'latest_change_percent' => $latestMonth['change_percent'] ?? null,
                'latest_direction' => $latestMonth['direction'] ?? 'none',
                'annual_status' => $annualStatus,
                'peak_month' => $peakMonth,
                'lowest_month' => $lowestMonth,
                'months_above_baseline' => $comparableMonths->where('variance', '>', 0)->count(),
                'months_below_baseline' => $comparableMonths->where('variance', '<', 0)->count(),
                'comparable_months' => $comparableMonths->count(),
                'peak_increase' => $peakIncrease,
                'peak_drop' => $peakDrop,
                'csv_url' => route('reports.energy-annual-export', [
                    'facility' => $facilityId,
                    'year' => $year,
                    'format' => 'csv',
                ]),
                'pdf_url' => route('reports.energy-annual-export', [
                    'facility' => $facilityId,
                    'year' => $year,
                    'format' => 'pdf',
                ]),
                'months' => $months,
            ]];
        })->all();
    }

    private function buildTrendDirectionMap($records): array
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

                        $trend = 'stable';
                        $actual = is_numeric($record->actual_kwh ?? null) ? (float) $record->actual_kwh : 0.0;
                        if ($reference !== null && $reference > 0) {
                            $trendPercent = (($actual - $reference) / $reference) * 100;
                            if ($trendPercent > $threshold) {
                                $trend = 'up';
                            } elseif ($trendPercent < -$threshold) {
                                $trend = 'down';
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

    private function buildEnergyRecordPayload(Request $request, array $validated, ?Facility $facility, bool $creating): array
    {
        $actualKwh = $this->resolveActualKwh($validated);
        $baseline = $this->resolveBaselineKwh($request, $facility, $validated);
        $deviation = ($baseline && $baseline != 0 && $actualKwh !== null)
            ? round((($actualKwh - $baseline) / $baseline) * 100, 2)
            : null;

        $payload = [
            'facility_id' => $validated['facility_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'actual_kwh' => $actualKwh,
            'baseline_kwh' => $baseline,
            'deviation' => $deviation,
            'energy_cost' => $validated['energy_cost'] ?? null,
            'rate_per_kwh' => $validated['rate_per_kwh'] ?? null,
            'alert' => $validated['alert'] ?? $this->resolveAlertLevel($baseline, $deviation),
            'review_status' => 'for_review',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_remarks' => null,
        ];

        if ($creating) {
            $payload['recorded_by'] = auth()->id();
        }

        if ($request->hasFile('bill_image')) {
            $payload['bill_image'] = $request->file('bill_image')->store('meralco_bills', 'public');
        } elseif ($request->hasFile('meralco_bill')) {
            $payload['bill_image'] = $request->file('meralco_bill')->store('meralco_bills', 'public');
        }

        return $payload;
    }

    private function resolveActualKwh(array $validated): ?float
    {
        $value = $validated['actual_kwh'] ?? $validated['kwh_consumed'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    private function resolveBaselineKwh(Request $request, ?Facility $facility, array $validated): ?float
    {
        if (array_key_exists('baseline_kwh', $validated) && $validated['baseline_kwh'] !== null && $validated['baseline_kwh'] !== '') {
            return (float) $validated['baseline_kwh'];
        }

        $baselineInput = $request->input('baseline_kwh');
        if ($baselineInput !== null && $baselineInput !== '') {
            return (float) $baselineInput;
        }

        return $facility?->resolveBaselineKwh();
    }

    private function resolveAlertLevel(?float $baseline, ?float $deviation): string
    {
        if ($deviation === null) {
            return '';
        }

        return \App\Models\EnergyRecord::resolveAlertLevel($deviation, $baseline);
    }

    private function buildIndexParams(Request $request): array
    {
        $params = [];

        foreach ([
            'facility_id_filter' => 'facility_id',
            'facility_id' => 'facility_id',
            'month_filter' => 'month',
            'month' => 'month',
            'year_filter' => 'year',
            'year' => 'year',
        ] as $source => $target) {
            if ($request->filled($source) && ! isset($params[$target])) {
                $params[$target] = $request->input($source);
            }
        }

        return $params;
    }

    private function denyStaffCrossFacilityAccess(int|string|null $facilityId, string $action, bool $redirectBack = false)
    {
        if (! RoleAccess::is(auth()->user(), 'staff')) {
            return null;
        }

        $userFacilityId = auth()->user()?->facility_id;
        if (! $userFacilityId || (string) $facilityId === (string) $userFacilityId) {
            return null;
        }

        $message = "You do not have permission to {$action} records for another facility.";

        return $redirectBack
            ? redirect()->back()->withInput()->withErrors(['facility_id' => $message])
            : redirect()->route('modules.energy-monitoring.index')->with('error', $message);
    }
}
