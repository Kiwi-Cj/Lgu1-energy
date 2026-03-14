<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MainMeterAlert;
use App\Models\MainMeterBaseline;
use App\Models\MainMeterReading;
use App\Services\MainMeterBaselineAlertService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MainMeterMonitoringController extends Controller
{
    public function __construct(private readonly MainMeterBaselineAlertService $baselineService)
    {
    }

    public function index(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view main meter monitoring.');
        }

        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));
        $selectedFacility = $request->query('facility_id');
        $selectedOverloadOnly = filter_var($request->query('overload_only', false), FILTER_VALIDATE_BOOLEAN);
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);

        $query = MainMeterReading::query()
            ->with([
                'facility:id,name',
                'alert',
            ])
            ->where('period_type', 'monthly')
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        if ($selectedFacility) {
            $query->where('facility_id', $selectedFacility);
        }

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null) {
            $query->whereIn('facility_id', $facilityScope);
        }

        $rawRows = $query
            ->orderByDesc('period_end_date')
            ->orderByDesc('id')
            ->get();

        $rows = $rawRows
            ->groupBy('facility_id')
            ->map(fn ($group) => $group->first())
            ->values();

        $facilityIds = $rows->pluck('facility_id')->filter()->unique()->values();
        $periodLabels = $rows->map(fn (MainMeterReading $r) => $r->periodLabel())->unique()->values();
        $fallbackBaselineMap = $this->resolveMainMeterFallbackBaselineMap($facilityIds);

        $baselineMap = MainMeterBaseline::query()
            ->whereIn('baseline_type', ['normalized_per_day', 'moving_avg_3', 'seasonal', 'moving_avg_6'])
            ->whereIn('facility_id', $facilityIds)
            ->whereIn('computed_for_period', $periodLabels)
            ->get()
            ->groupBy(fn ($item) => $item->facility_id . '|' . $item->computed_for_period);

        $rows->each(function (MainMeterReading $reading) use ($baselineMap, $fallbackBaselineMap) {
            $baselineRows = $baselineMap->get($reading->facility_id . '|' . $reading->periodLabel(), collect());
            $baseline = $this->pickPreferredBaseline($baselineRows);
            $baselinePeak = $this->pickPreferredBaselinePeak($baselineRows);
            $alert = $reading->alert;
            $peak = is_numeric($reading->peak_demand_kw) ? (float) $reading->peak_demand_kw : null;
            $overloadPercent = null;
            $isOverload = false;

            if ($baseline === null) {
                $fallback = $fallbackBaselineMap->get((int) $reading->facility_id);
                if (is_numeric($fallback) && (float) $fallback > 0) {
                    $baseline = round((float) $fallback, 2);
                }
            }

            if ($peak !== null && $baselinePeak !== null && $baselinePeak > 0) {
                $overloadPercent = round((($peak - $baselinePeak) / $baselinePeak) * 100, 2);
                $isOverload = $peak > ($baselinePeak * 1.15);
            }

            if (! $isOverload && $alert && str_contains(strtolower((string) ($alert->reason ?? '')), 'demand spike')) {
                $isOverload = true;
            }

            $reading->setAttribute('monitor_baseline_peak_kw', $baselinePeak);
            $reading->setAttribute('monitor_overload_percent', $overloadPercent);
            $reading->setAttribute('monitor_is_overload', $isOverload);

            if ($alert) {
                if ($baseline === null && is_numeric($alert->baseline_kwh)) {
                    $baseline = (float) $alert->baseline_kwh;
                }
                $reading->setAttribute('monitor_baseline_kwh', $baseline);
                $reading->setAttribute('monitor_alert_level', $alert->alert_level);
                $reading->setAttribute('monitor_increase_percent', (float) $alert->increase_percent);
                return;
            }

            $reading->setAttribute('monitor_baseline_kwh', $baseline);
            if ($baseline && $baseline > 0) {
                $kwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : 0.0;
                $reading->setAttribute('monitor_increase_percent', round((($kwh - $baseline) / $baseline) * 100, 2));
            } else {
                $reading->setAttribute('monitor_increase_percent', null);
            }
            $reading->setAttribute('monitor_alert_level', 'none');
        });

        $tableRows = $selectedOverloadOnly
            ? $rows->filter(fn (MainMeterReading $row) => (bool) ($row->monitor_is_overload ?? false))->values()
            : $rows;

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        $widgets = $this->buildDashboardWidgets($periodStart, $periodEnd, $selectedFacility, $facilityScope);
        $dashboard = $this->buildDashboardSummary($rows);
        $trend = $this->buildTrendSeries($safeMonth, $selectedFacility, $facilityScope);

        return view('modules.main-meter.monitoring', [
            'rows' => $tableRows,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'selectedOverloadOnly' => $selectedOverloadOnly,
            'facilities' => $facilities,
            'widgets' => $widgets,
            'dashboard' => $dashboard,
            'trend' => $trend,
            'canApprove' => $this->canApprove(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->canEncode()) {
            return redirect()->back()->with('error', 'You do not have permission to encode main meter readings.');
        }

        $validated = $request->validate([
            'facility_id' => 'required|integer|exists:facilities,id',
            'period_type' => 'required|in:monthly',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after_or_equal:period_start_date',
            'reading_start_kwh' => 'required|numeric|min:0',
            'reading_end_kwh' => 'required|numeric|min:0|gte:reading_start_kwh',
            'operating_days' => 'nullable|integer|min:1|max:366',
            'peak_demand_kw' => 'nullable|numeric|min:0',
            'power_factor' => 'nullable|numeric|min:0|max:1',
        ]);

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null && ! in_array((int) $validated['facility_id'], $facilityScope, true)) {
            return redirect()->back()->withInput()->with('error', 'You can only encode readings for your assigned facility.');
        }

        $duplicate = MainMeterReading::query()
            ->where('facility_id', (int) $validated['facility_id'])
            ->where('period_type', 'monthly')
            ->whereDate('period_start_date', $validated['period_start_date'])
            ->whereDate('period_end_date', $validated['period_end_date'])
            ->exists();

        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', 'A main meter reading for the same period already exists.');
        }

        $manualPeak = $validated['peak_demand_kw'] ?? null;
        $peakDemandKw = is_numeric($manualPeak) ? (float) $manualPeak : null;
        $peakWasEstimated = false;
        $powerFactorInput = $validated['power_factor'] ?? null;
        $powerFactor = is_numeric($powerFactorInput) ? (float) $powerFactorInput : 0.95;
        $powerFactorWasDefaulted = ! is_numeric($powerFactorInput);

        if ($peakDemandKw === null) {
            $peakDemandKw = $this->estimatePeakDemandKw(
                (float) $validated['reading_start_kwh'],
                (float) $validated['reading_end_kwh'],
                (string) $validated['period_start_date'],
                (string) $validated['period_end_date'],
                isset($validated['operating_days']) ? (int) $validated['operating_days'] : null
            );
            $peakWasEstimated = $peakDemandKw !== null;
        }

        $reading = MainMeterReading::create([
            'facility_id' => (int) $validated['facility_id'],
            'period_type' => 'monthly',
            'period_start_date' => $validated['period_start_date'],
            'period_end_date' => $validated['period_end_date'],
            'reading_start_kwh' => $validated['reading_start_kwh'],
            'reading_end_kwh' => $validated['reading_end_kwh'],
            'operating_days' => $validated['operating_days'] ?? null,
            'peak_demand_kw' => $peakDemandKw,
            'power_factor' => $powerFactor,
            'encoded_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $result = $this->baselineService->processReading($reading->fresh(['facility']));
        $alert = $result['alert'] ?? null;
        $peakSuffix = $peakWasEstimated && $peakDemandKw !== null
            ? (' Peak demand auto-estimated at ' . number_format($peakDemandKw, 2) . ' kW.')
            : '';
        $powerFactorSuffix = $powerFactorWasDefaulted
            ? (' Power factor defaulted to ' . number_format($powerFactor, 3) . '.')
            : '';
        $alertSuffix = $alert ? (' Alert: ' . strtoupper((string) $alert->alert_level) . '.') : '';

        return redirect()->route('modules.main-meter.monitoring', [
            'month' => Carbon::parse($validated['period_end_date'])->format('Y-m'),
            'facility_id' => $validated['facility_id'],
        ])->with('success', 'Main meter reading encoded successfully.' . $peakSuffix . $powerFactorSuffix . $alertSuffix);
    }

    public function approve(Request $request, MainMeterReading $reading)
    {
        if (! $this->canApprove()) {
            return redirect()->back()->with('error', 'Only engineers/energy officers or admins can approve readings.');
        }

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null && ! in_array((int) $reading->facility_id, $facilityScope, true)) {
            return redirect()->back()->with('error', 'You can only approve readings for your assigned facility.');
        }

        if (! $reading->approved_at) {
            $reading->approved_by = auth()->id();
            $reading->approved_at = now();
            $reading->save();
        }

        $this->baselineService->processReading($reading->fresh(['facility']));

        return redirect()->back()->with('success', 'Main meter reading approved.');
    }

    public function alerts(Request $request)
    {
        if (! $this->canViewAlerts()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view main meter alerts.');
        }

        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);

        $selectedFacility = $request->query('facility_id');
        $selectedLevel = (string) $request->query('alert_level', '');
        $selectedLevel = in_array($selectedLevel, ['warning', 'critical'], true) ? $selectedLevel : '';
        $selectedOverloadOnly = filter_var($request->query('overload_only', false), FILTER_VALIDATE_BOOLEAN);

        $query = MainMeterAlert::query()
            ->with([
                'facility:id,name',
                'reading:id,facility_id,period_type,period_start_date,period_end_date,kwh_used,peak_demand_kw,approved_at',
            ])
            ->whereHas('facility')
            ->whereHas('reading', function (Builder $builder) use ($periodStart, $periodEnd) {
                $builder->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        if ($selectedFacility) {
            $query->where('facility_id', $selectedFacility);
        }

        if ($selectedLevel !== '') {
            $query->where('alert_level', $selectedLevel);
        }

        if ($selectedOverloadOnly) {
            $query->whereRaw('LOWER(reason) LIKE ?', ['%demand spike%']);
        }

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null) {
            $query->whereIn('facility_id', $facilityScope);
        }

        $alerts = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.main-meter.alerts', [
            'alerts' => $alerts,
            'facilities' => $facilities,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'selectedLevel' => $selectedLevel,
            'selectedOverloadOnly' => $selectedOverloadOnly,
        ]);
    }

    public function monthlyReport(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view main meter reports.');
        }

        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);
        $selectedFacility = $request->query('facility_id');
        $facilityScope = $this->staffFacilityIds($request);

        $query = MainMeterReading::query()
            ->with(['facility:id,name', 'alert'])
            ->where('period_type', 'monthly')
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        if ($selectedFacility) {
            $query->where('facility_id', $selectedFacility);
        }

        if ($facilityScope !== null) {
            $query->whereIn('facility_id', $facilityScope);
        }

        $rows = $query
            ->orderBy('facility_id')
            ->orderByDesc('period_end_date')
            ->get();

        $totalKwh = round((float) $rows->sum('kwh_used'), 2);
        $avgPf = round((float) $rows->filter(fn ($row) => is_numeric($row->power_factor))->avg('power_factor'), 3);
        $maxPeak = round((float) $rows->max(fn ($row) => (float) ($row->peak_demand_kw ?? 0)), 2);

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.main-meter.reports.monthly', [
            'rows' => $rows,
            'facilities' => $facilities,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'totalKwh' => $totalKwh,
            'avgPf' => $avgPf,
            'maxPeak' => $maxPeak,
        ]);
    }

    public function baselineComparisonReport(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view main meter reports.');
        }

        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);
        $selectedFacility = $request->query('facility_id');
        $facilityScope = $this->staffFacilityIds($request);

        $query = MainMeterReading::query()
            ->with(['facility:id,name'])
            ->where('period_type', 'monthly')
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        if ($selectedFacility) {
            $query->where('facility_id', $selectedFacility);
        }

        if ($facilityScope !== null) {
            $query->whereIn('facility_id', $facilityScope);
        }

        $rows = $query->orderBy('facility_id')->orderByDesc('period_end_date')->get();
        $facilityIds = $rows->pluck('facility_id')->unique()->values();
        $periodLabels = $rows->map(fn (MainMeterReading $row) => $row->periodLabel())->unique()->values();
        $fallbackBaselineMap = $this->resolveMainMeterFallbackBaselineMap($facilityIds);

        $baselineRows = MainMeterBaseline::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereIn('computed_for_period', $periodLabels)
            ->whereIn('baseline_type', ['normalized_per_day', 'moving_avg_3', 'seasonal', 'moving_avg_6'])
            ->get()
            ->groupBy(fn ($item) => $item->facility_id . '|' . $item->computed_for_period);

        $comparisonRows = $rows->map(function (MainMeterReading $row) use ($baselineRows, $fallbackBaselineMap) {
            $baseline = $this->pickPreferredBaseline($baselineRows->get($row->facility_id . '|' . $row->periodLabel(), collect()));
            if ($baseline === null) {
                $fallback = $fallbackBaselineMap->get((int) $row->facility_id);
                if (is_numeric($fallback) && (float) $fallback > 0) {
                    $baseline = round((float) $fallback, 2);
                }
            }
            $actual = (float) $row->kwh_used;
            $deviation = null;
            if ($baseline && $baseline > 0) {
                $deviation = round((($actual - $baseline) / $baseline) * 100, 2);
            }

            return [
                'facility_name' => $row->facility?->name ?? 'Facility',
                'period_label' => $row->periodLabel(),
                'actual_kwh' => $actual,
                'baseline_kwh' => $baseline,
                'deviation_percent' => $deviation,
            ];
        });

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.main-meter.reports.baseline-comparison', [
            'rows' => $comparisonRows,
            'facilities' => $facilities,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
        ]);
    }

    public function demandSpikeReport(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view main meter reports.');
        }

        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);
        $selectedFacility = $request->query('facility_id');
        $facilityScope = $this->staffFacilityIds($request);

        $query = MainMeterReading::query()
            ->with(['facility:id,name', 'alert'])
            ->where('period_type', 'monthly')
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereNotNull('peak_demand_kw');

        if ($selectedFacility) {
            $query->where('facility_id', $selectedFacility);
        }

        if ($facilityScope !== null) {
            $query->whereIn('facility_id', $facilityScope);
        }

        $rows = $query->orderBy('facility_id')->orderByDesc('period_end_date')->get();
        $facilityIds = $rows->pluck('facility_id')->unique()->values();
        $periodLabels = $rows->map(fn (MainMeterReading $row) => $row->periodLabel())->unique()->values();

        $baselinePeakMap = MainMeterBaseline::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereIn('computed_for_period', $periodLabels)
            ->get()
            ->groupBy(fn ($item) => $item->facility_id . '|' . $item->computed_for_period);

        $spikeRows = $rows->map(function (MainMeterReading $row) use ($baselinePeakMap) {
            $baselineRows = $baselinePeakMap->get($row->facility_id . '|' . $row->periodLabel(), collect());
            $baselinePeak = $baselineRows
                ->filter(fn ($item) => is_numeric($item->baseline_peak_kw) && (float) $item->baseline_peak_kw > 0)
                ->max(fn ($item) => (float) $item->baseline_peak_kw);

            $peak = (float) ($row->peak_demand_kw ?? 0);
            $spikePercent = null;
            $isSpike = false;

            if ($baselinePeak && $baselinePeak > 0 && $peak > 0) {
                $spikePercent = round((($peak - $baselinePeak) / $baselinePeak) * 100, 2);
                $isSpike = $peak > ($baselinePeak * 1.15);
            }

            return [
                'facility_name' => $row->facility?->name ?? 'Facility',
                'period_label' => $row->periodLabel(),
                'peak_demand_kw' => $peak,
                'baseline_peak_kw' => $baselinePeak,
                'spike_percent' => $spikePercent,
                'is_spike' => $isSpike,
                'alert_reason' => $row->alert?->reason,
            ];
        })->filter(fn ($row) => $row['is_spike'])->values();

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.main-meter.reports.demand-spikes', [
            'rows' => $spikeRows,
            'facilities' => $facilities,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
        ]);
    }

    private function resolveMonthRange(string $month): array
    {
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
        }

        return [$start->copy(), $start->copy()->endOfMonth(), $start->format('Y-m')];
    }

    private function buildDashboardWidgets(
        Carbon $periodStart,
        Carbon $periodEnd,
        mixed $selectedFacility,
        ?array $facilityScope
    ): array {
        $alertQuery = MainMeterAlert::query()
            ->whereHas('reading', function (Builder $builder) use ($periodStart, $periodEnd) {
                $builder
                    ->where('period_type', 'monthly')
                    ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        if ($selectedFacility) {
            $alertQuery->where('facility_id', $selectedFacility);
        }

        if ($facilityScope !== null) {
            $alertQuery->whereIn('facility_id', $facilityScope);
        }

        $top5 = (clone $alertQuery)
            ->with(['facility:id,name', 'reading:id,facility_id,period_end_date'])
            ->orderByDesc('increase_percent')
            ->limit(5)
            ->get();

        $criticalThisMonth = (clone $alertQuery)
            ->where('alert_level', 'critical')
            ->count();

        $facilitiesWithMostAlerts = (clone $alertQuery)
            ->join('facilities', 'facilities.id', '=', 'main_meter_alerts.facility_id')
            ->select([
                'facilities.id as facility_id',
                'facilities.name as facility_name',
                DB::raw('COUNT(*) as total_alerts'),
            ])
            ->groupBy('facilities.id', 'facilities.name')
            ->orderByDesc('total_alerts')
            ->limit(5)
            ->get();

        return [
            'top5HighestIncrease' => $top5,
            'criticalAlertsThisMonth' => $criticalThisMonth,
            'facilitiesWithMostAlerts' => $facilitiesWithMostAlerts,
        ];
    }

    private function buildDashboardSummary(Collection $rows): array
    {
        $currentKwh = round((float) $rows->sum(fn ($row) => (float) ($row->kwh_used ?? 0)), 2);
        $baselineKwh = round((float) $rows->sum(function ($row) {
            return is_numeric($row->monitor_baseline_kwh ?? null) ? (float) $row->monitor_baseline_kwh : 0.0;
        }), 2);

        $increasePercent = null;
        if ($baselineKwh > 0) {
            $increasePercent = round((($currentKwh - $baselineKwh) / $baselineKwh) * 100, 2);
        }

        $peakDemand = round((float) $rows->max(function ($row) {
            return is_numeric($row->peak_demand_kw ?? null) ? (float) $row->peak_demand_kw : 0.0;
        }), 2);

        $levels = $rows->pluck('monitor_alert_level')->map(fn ($level) => strtolower((string) $level));
        $badge = 'none';
        if ($levels->contains('critical')) {
            $badge = 'critical';
        } elseif ($levels->contains('warning')) {
            $badge = 'warning';
        }

        return [
            'current_kwh' => $currentKwh,
            'baseline_kwh' => $baselineKwh,
            'increase_percent' => $increasePercent,
            'peak_demand_kw' => $peakDemand,
            'alert_badge' => $badge,
        ];
    }

    private function buildTrendSeries(string $month, mixed $selectedFacility, ?array $facilityScope): array
    {
        $endMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        $startMonth = $endMonth->copy()->subMonthsNoOverflow(11)->startOfMonth();
        $labels = collect();
        for ($i = 0; $i < 12; $i++) {
            $labels->push($startMonth->copy()->addMonthsNoOverflow($i)->format('Y-m'));
        }

        $readingsQuery = MainMeterReading::query()
            ->approved()
            ->whereBetween('period_end_date', [$startMonth->toDateString(), $endMonth->toDateString()]);

        $baselinesQuery = MainMeterBaseline::query()
            ->whereBetween('computed_for_period', [$startMonth->format('Y-m'), $endMonth->format('Y-m')])
            ->whereIn('baseline_type', ['normalized_per_day', 'moving_avg_3', 'seasonal', 'moving_avg_6']);

        if ($selectedFacility) {
            $readingsQuery->where('facility_id', $selectedFacility);
            $baselinesQuery->where('facility_id', $selectedFacility);
        }

        if ($facilityScope !== null) {
            $readingsQuery->whereIn('facility_id', $facilityScope);
            $baselinesQuery->whereIn('facility_id', $facilityScope);
        }

        $readings = $readingsQuery->get(['facility_id', 'period_end_date', 'kwh_used']);
        $baselineRows = $baselinesQuery->get(['facility_id', 'baseline_type', 'baseline_kwh', 'computed_for_period']);
        $trendFacilityIds = $readings->pluck('facility_id')
            ->merge($baselineRows->pluck('facility_id'))
            ->when($selectedFacility, fn ($items) => $items->push((int) $selectedFacility))
            ->filter()
            ->unique()
            ->values();
        $fallbackBaselineMap = $this->resolveMainMeterFallbackBaselineMap($trendFacilityIds);

        $readingsByPeriod = $readings->groupBy(function ($reading) {
            return Carbon::parse($reading->period_end_date)->format('Y-m');
        });

        $baselinesByPeriodFacility = $baselineRows->groupBy(function ($item) {
            return $item->facility_id . '|' . $item->computed_for_period;
        });

        $kwhSeries = [];
        $baselineSeries = [];
        foreach ($labels as $label) {
            $monthReadings = $readingsByPeriod->get($label, collect());
            $kwhSeries[] = round((float) $monthReadings->sum('kwh_used'), 2);

            $monthBaselineRows = $baselineRows
                ->where('computed_for_period', $label)
                ->groupBy('facility_id');
            $monthFacilityIds = $monthReadings->pluck('facility_id')
                ->merge($monthBaselineRows->keys())
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $baselineTotal = 0.0;
            foreach ($monthFacilityIds as $facilityId) {
                $preferred = $this->pickPreferredBaseline($baselinesByPeriodFacility->get($facilityId . '|' . $label, collect()));
                if ($preferred === null) {
                    $fallback = $fallbackBaselineMap->get((int) $facilityId);
                    if (is_numeric($fallback) && (float) $fallback > 0) {
                        $preferred = round((float) $fallback, 2);
                    }
                }
                if ($preferred !== null) {
                    $baselineTotal += $preferred;
                }
            }

            $baselineSeries[] = round($baselineTotal, 2);
        }

        return [
            'labels' => $labels->values()->all(),
            'kwh' => $kwhSeries,
            'baseline' => $baselineSeries,
        ];
    }

    private function pickPreferredBaseline(Collection $rows): ?float
    {
        if ($rows->isEmpty()) {
            return null;
        }

        $ordered = ['normalized_per_day', 'moving_avg_3', 'seasonal', 'moving_avg_6'];
        foreach ($ordered as $type) {
            $value = $rows->firstWhere('baseline_type', $type)?->baseline_kwh;
            if (is_numeric($value)) {
                return round((float) $value, 2);
            }
        }

        return null;
    }

    private function pickPreferredBaselinePeak(Collection $rows): ?float
    {
        if ($rows->isEmpty()) {
            return null;
        }

        $ordered = ['normalized_per_day', 'moving_avg_3', 'seasonal', 'moving_avg_6'];
        foreach ($ordered as $type) {
            $value = $rows->firstWhere('baseline_type', $type)?->baseline_peak_kw;
            if (is_numeric($value) && (float) $value > 0) {
                return round((float) $value, 2);
            }
        }

        return null;
    }

    private function estimatePeakDemandKw(
        float $readingStartKwh,
        float $readingEndKwh,
        string $periodStartDate,
        string $periodEndDate,
        ?int $operatingDays
    ): ?float {
        $kwhUsed = $readingEndKwh - $readingStartKwh;
        if ($kwhUsed <= 0) {
            return null;
        }

        $start = Carbon::parse($periodStartDate)->startOfDay();
        $end = Carbon::parse($periodEndDate)->startOfDay();
        $calendarDays = max(1, $start->diffInDays($end) + 1);
        $days = is_numeric($operatingDays) && (int) $operatingDays > 0
            ? (int) $operatingDays
            : $calendarDays;

        $averageKw = $kwhUsed / max(1, $days * 24);

        // Estimate peak from average load using a conservative load factor.
        $loadFactor = 0.60;
        $estimatedPeak = $averageKw / $loadFactor;

        return $estimatedPeak > 0 ? round($estimatedPeak, 2) : null;
    }

    private function resolveMainMeterFallbackBaselineMap(Collection $facilityIds): Collection
    {
        $ids = $facilityIds->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $facilities = Facility::query()
            ->whereIn('id', $ids)
            ->with([
                'energyProfiles' => fn ($query) => $query->orderByDesc('id'),
                'meters' => fn ($query) => $query
                    ->where('meter_type', 'main')
                    ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                    ->orderBy('id'),
            ])
            ->get(['id', 'baseline_kwh']);

        return $facilities->mapWithKeys(function (Facility $facility) {
            $latestProfile = $facility->energyProfiles->first();
            $baselineKwh = null;

            if ($latestProfile && ! empty($latestProfile->primary_meter_id)) {
                $primaryMainMeter = $facility->meters->firstWhere('id', (int) $latestProfile->primary_meter_id);
                if ($primaryMainMeter && is_numeric($primaryMainMeter->baseline_kwh) && (float) $primaryMainMeter->baseline_kwh > 0) {
                    $baselineKwh = (float) $primaryMainMeter->baseline_kwh;
                }
            }

            if ($baselineKwh === null) {
                $fallbackMainMeter = $facility->meters->first(function ($meter) {
                    return is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0;
                });
                if ($fallbackMainMeter) {
                    $baselineKwh = (float) $fallbackMainMeter->baseline_kwh;
                }
            }

            if ($baselineKwh === null && $latestProfile && is_numeric($latestProfile->baseline_kwh) && (float) $latestProfile->baseline_kwh > 0) {
                $baselineKwh = (float) $latestProfile->baseline_kwh;
            } elseif ($baselineKwh === null && is_numeric($facility->baseline_kwh) && (float) $facility->baseline_kwh > 0) {
                $baselineKwh = (float) $facility->baseline_kwh;
            }

            return [(int) $facility->id => $baselineKwh !== null ? round($baselineKwh, 2) : null];
        });
    }

    private function canView(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
    }

    private function canEncode(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'encode_main_meter_readings')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff']);
    }

    private function canApprove(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'approve_main_meter_readings')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'engineer']);
    }

    private function canViewAlerts(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'view_main_meter_alerts')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
    }

    private function staffFacilityIds(Request $request): ?array
    {
        $user = $request->user();
        if (! $user || ! RoleAccess::is($user, 'staff')) {
            return null;
        }

        return $user->facilities->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
