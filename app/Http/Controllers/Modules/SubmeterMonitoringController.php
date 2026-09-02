<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\EnergyRecord;
use App\Models\Submeter;
use App\Models\SubmeterAlert;
use App\Models\SubmeterBaseline;
use App\Models\SubmeterReading;
use App\Services\EnergyRecommendationService;
use App\Services\SubmeterBaselineAlertService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubmeterMonitoringController extends Controller
{
    public function __construct(
        private readonly SubmeterBaselineAlertService $baselineService,
        private readonly EnergyRecommendationService $energyRecommendationService
    )
    {
    }

    public function index(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view submeter monitoring.');
        }

        $facilityScope = $this->staffFacilityIds($request);

        // 1. Available facilities with submeters
        $facilitiesWithSubmeters = Facility::query()
            ->whereHas('meters', fn ($q) => $q->where('meter_type', 'sub'))
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedFacilityId = $request->query('facility_id');
        if ($selectedFacilityId === null || $selectedFacilityId === '') {
            $selectedFacilityId = $facilitiesWithSubmeters->isNotEmpty() ? (int) $facilitiesWithSubmeters->first()->id : 0;
        } else {
            $selectedFacilityId = (int) $selectedFacilityId;
        }

        $selectedYear = (int) ($request->query('year') ?: date('Y'));
        $selectedMonth = (int) ($request->query('month') ?: 0);
        if ($selectedMonth < 0 || $selectedMonth > 12) {
            $selectedMonth = 0;
        }

        // Available years with records
        $yearOptions = EnergyRecord::query()
            ->whereHas('meter', fn ($q) => $q->where('meter_type', 'sub'))
            ->when($selectedFacilityId > 0, fn ($q) => $q->where('facility_id', $selectedFacilityId))
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->values();

        if ($yearOptions->isEmpty()) {
            $yearOptions = collect([(int) date('Y')]);
        }

        // 2. Fetch all submeters in scope
        $submeterQuery = FacilityMeter::with(['facility:id,name', 'parentMeter:id,meter_name'])
            ->where('meter_type', 'sub')
            ->whereNotNull('approved_at')
            ->when($selectedFacilityId > 0, fn ($q) => $q->where('facility_id', $selectedFacilityId))
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('facility_id', $facilityScope))
            ->orderBy('meter_name');

        $submeters = $submeterQuery->get();
        $submeterIds = $submeters->pluck('id')->all();

        // 3. Fetch Energy Records for these submeters
        $recordsQuery = EnergyRecord::with(['meter.facility', 'meter.parentMeter'])
            ->whereIn('meter_id', $submeterIds)
            ->where('year', $selectedYear)
            ->when($selectedMonth > 0, fn ($q) => $q->where('month', $selectedMonth))
            ->orderByDesc('year')
            ->orderByDesc('month');

        $energyRecords = $recordsQuery->get();

        // Map records with baseline, deviation, dials, and costs
        $mappedRows = $energyRecords->map(function ($rec) {
            $actualKwh = is_numeric($rec->actual_kwh) ? (float) $rec->actual_kwh : null;
            $baselineKwh = is_numeric($rec->baseline_kwh)
                ? (float) $rec->baseline_kwh
                : (is_numeric($rec->meter?->baseline_kwh) ? (float) $rec->meter->baseline_kwh : null);

            $deviation = is_numeric($rec->deviation)
                ? (float) $rec->deviation
                : \App\Models\EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);

            $alertLabel = ($deviation !== null && $baselineKwh !== null && $baselineKwh > 0)
                ? \App\Models\EnergyRecord::resolveAlertLevel((float) $deviation, (float) $baselineKwh)
                : 'Normal';

            $alertBg = '#dcfce7';
            $alertColor = '#166534';
            if (in_array(strtolower($alertLabel), ['critical', 'drop critical'], true)) {
                $alertBg = '#fee2e2';
                $alertColor = '#991b1b';
            } elseif (in_array(strtolower($alertLabel), ['high', 'very high', 'drop high'], true)) {
                $alertBg = '#ffedd5';
                $alertColor = '#9a3412';
            } elseif (in_array(strtolower($alertLabel), ['warning', 'drop warning'], true)) {
                $alertBg = '#fef3c7';
                $alertColor = '#92400e';
            }

            $rate = is_numeric($rec->rate_per_kwh) && $rec->rate_per_kwh > 0 ? (float) $rec->rate_per_kwh : 12.00;
            $cost = is_numeric($rec->energy_cost) && $rec->energy_cost > 0 ? (float) $rec->energy_cost : round(($actualKwh ?? 0) * $rate, 2);

            return [
                'id' => (int) $rec->id,
                'facility_id' => (int) $rec->facility_id,
                'facility_name' => (string) ($rec->meter?->facility?->name ?? 'Facility'),
                'meter_id' => (int) $rec->meter_id,
                'meter_name' => (string) ($rec->meter?->meter_name ?? 'Submeter'),
                'meter_number' => (string) ($rec->meter?->meter_number ?? ''),
                'location' => (string) ($rec->meter?->location ?? 'General'),
                'parent_meter_name' => (string) ($rec->meter?->parentMeter?->meter_name ?? 'Main Meter'),
                'year' => (int) $rec->year,
                'month' => (int) $rec->month,
                'previous_reading_kwh' => is_numeric($rec->previous_reading_kwh) ? (float) $rec->previous_reading_kwh : null,
                'current_reading_kwh' => is_numeric($rec->current_reading_kwh) ? (float) $rec->current_reading_kwh : null,
                'actual_kwh' => $actualKwh,
                'baseline_kwh' => $baselineKwh,
                'deviation' => $deviation,
                'alert_label' => $alertLabel,
                'alert_bg' => $alertBg,
                'alert_color' => $alertColor,
                'rate_per_kwh' => $rate,
                'cost' => $cost,
            ];
        });

        $totalSubmeterKwh = round((float) $mappedRows->sum(fn ($r) => (float) ($r['actual_kwh'] ?? 0)), 2);
        $totalSubmeterCost = round((float) $mappedRows->sum(fn ($r) => (float) ($r['cost'] ?? 0)), 2);
        $submeterCount = $submeters->count();
        $criticalAlertCount = $mappedRows->filter(fn ($r) => in_array(strtolower((string) $r['alert_label']), ['critical', 'drop critical', 'high', 'very high'], true))->count();

        $timeframe = strtolower(trim((string) $request->query('timeframe', 'monthly')));
        if (! in_array($timeframe, ['monthly', 'weekly'], true)) {
            $timeframe = 'monthly';
        }

        $selectedWeek = (int) ($request->query('week') ?: 0);
        if ($selectedWeek < 0 || $selectedWeek > 4) {
            $selectedWeek = 0;
        }

        // If weekly mode is chosen and month is 0, default to active monitoring month (e.g. 8)
        if ($timeframe === 'weekly' && $selectedMonth === 0) {
            $selectedMonth = 8;
        }

        // 4. Submeter Load Distribution & Share Breakdown
        $submeterLoadShares = $mappedRows
            ->groupBy('meter_id')
            ->map(function ($group) use ($totalSubmeterKwh) {
                $subKwh = round((float) $group->sum(fn ($r) => (float) ($r['actual_kwh'] ?? 0)), 2);
                $first = $group->first();
                $pct = $totalSubmeterKwh > 0 ? round(($subKwh / $totalSubmeterKwh) * 100, 1) : 0;
                return [
                    'meter_id' => $first['meter_id'],
                    'meter_name' => $first['meter_name'],
                    'facility_name' => $first['facility_name'],
                    'total_kwh' => $subKwh,
                    'total_cost' => round((float) $group->sum(fn ($r) => (float) ($r['cost'] ?? 0)), 2),
                    'share_percent' => $pct,
                ];
            })
            ->sortByDesc('total_kwh')
            ->values();

        // 5. Main vs. Submeter Reconciliation
        $mainRecordsQuery = EnergyRecord::query()
            ->whereHas('meter', fn ($q) => $q->where('meter_type', 'main'))
            ->where('year', $selectedYear)
            ->when($selectedMonth > 0, fn ($q) => $q->where('month', $selectedMonth));

        if ($selectedFacilityId > 0) {
            $mainRecordsQuery->where('facility_id', $selectedFacilityId);
        } else {
            $mainRecordsQuery->whereIn('facility_id', $facilitiesWithSubmeters->pluck('id'));
        }

        $mainRecords = $mainRecordsQuery->get();
        $mainMeterKwh = round((float) $mainRecords->sum('actual_kwh'), 2);
        $mainMeterCost = round((float) $mainRecords->sum('energy_cost'), 2);

        $accountedPercent = $mainMeterKwh > 0 ? min(100.0, round(($totalSubmeterKwh / $mainMeterKwh) * 100, 1)) : 0;
        $unaccountedKwh = max(0.0, round($mainMeterKwh - $totalSubmeterKwh, 2));
        $unaccountedCost = max(0.0, round($mainMeterCost - $totalSubmeterCost, 2));

        // 6. Weekly Breakdown Generator (Dynamically computed from actual days in month)
        $daysInSelectedMonth = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth > 0 ? $selectedMonth : 1, 1)->daysInMonth;
        $week4SelectedLabel = "Week 4 (Days 22–{$daysInSelectedMonth})";
        $weekLabels = [
            1 => 'Week 1 (Days 1–7)',
            2 => 'Week 2 (Days 8–14)',
            3 => 'Week 3 (Days 15–21)',
            4 => $selectedMonth > 0 ? $week4SelectedLabel : 'Week 4 (Days 22–End)',
        ];

        $weeklyRows = collect();
        foreach ($mappedRows as $row) {
            $prevRunning = $row['previous_reading_kwh'] ?? 50000.00;
            $monthKwh = (float) ($row['actual_kwh'] ?? 0);
            $monthBaseline = (float) ($row['baseline_kwh'] ?? 0);
            $daysInRowMonth = \Carbon\Carbon::createFromDate((int) ($row['year'] ?? $selectedYear), (int) ($row['month'] ?? 1), 1)->daysInMonth;
            $week4Days = max(1, $daysInRowMonth - 21);

            $weekMultipliers = [
                1 => ['weight' => 7 / $daysInRowMonth, 'label' => 'Week 1 (Days 1–7)'],
                2 => ['weight' => 7 / $daysInRowMonth, 'label' => 'Week 2 (Days 8–14)'],
                3 => ['weight' => 7 / $daysInRowMonth, 'label' => 'Week 3 (Days 15–21)'],
                4 => ['weight' => $week4Days / $daysInRowMonth, 'label' => "Week 4 (Days 22–{$daysInRowMonth})"],
            ];

            foreach ($weekMultipliers as $wNum => $wData) {
                $wKwh = round($monthKwh * $wData['weight'], 2);
                $wBaseline = round($monthBaseline * $wData['weight'], 2);
                $wPrev = $prevRunning;
                $wCurr = round($wPrev + $wKwh, 2);
                $prevRunning = $wCurr;
                $wCost = round($wKwh * ($row['rate_per_kwh'] ?? 13.50), 2);

                $wDev = $wBaseline > 0 ? round((($wKwh - $wBaseline) / $wBaseline) * 100, 2) : 0.0;

                $wRow = [
                    'id' => $row['id'] . '-w' . $wNum,
                    'facility_id' => $row['facility_id'],
                    'facility_name' => $row['facility_name'],
                    'meter_id' => $row['meter_id'],
                    'meter_name' => $row['meter_name'],
                    'parent_meter_name' => $row['parent_meter_name'],
                    'year' => $row['year'],
                    'month' => $row['month'],
                    'week_num' => $wNum,
                    'week_label' => $wData['label'],
                    'period_display' => $row['year'] . ' ' . ($monthLabels[$row['month']] ?? 'Month ' . $row['month']) . ' • ' . $wData['label'],
                    'previous_reading_kwh' => $wPrev,
                    'current_reading_kwh' => $wCurr,
                    'actual_kwh' => $wKwh,
                    'baseline_kwh' => $wBaseline,
                    'deviation' => $wDev,
                    'alert_label' => $row['alert_label'],
                    'alert_bg' => $row['alert_bg'],
                    'alert_color' => $row['alert_color'],
                    'cost' => $wCost,
                ];

                if ($selectedWeek === 0 || $selectedWeek === $wNum) {
                    $weeklyRows->push($wRow);
                }
            }
        }

        // Weekly Trend Chart Data (for Chart.js Bar Chart)
        $weeklyChartLabels = ['Week 1 (Days 1–7)', 'Week 2 (Days 8–14)', 'Week 3 (Days 15–21)', $week4SelectedLabel];
        $weeklyChartDatasets = [];
        $palette = ['#2563eb', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#14b8a6'];

        $submetersGrouped = $mappedRows->groupBy('meter_id');
        $colorIndex = 0;
        $w4WeightSelected = max(1, $daysInSelectedMonth - 21) / $daysInSelectedMonth;
        foreach ($submetersGrouped as $meterId => $mRows) {
            $first = $mRows->first();
            $meterMonthKwh = (float) $mRows->sum('actual_kwh');
            $weekVals = [
                round($meterMonthKwh * (7 / $daysInSelectedMonth), 2),
                round($meterMonthKwh * (7 / $daysInSelectedMonth), 2),
                round($meterMonthKwh * (7 / $daysInSelectedMonth), 2),
                round($meterMonthKwh * $w4WeightSelected, 2),
            ];

            $weeklyChartDatasets[] = [
                'label' => $first['meter_name'],
                'data' => $weekVals,
                'backgroundColor' => $palette[$colorIndex % count($palette)],
                'borderRadius' => 6,
            ];
            $colorIndex++;
        }

        $monthLabels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        return view('modules.submeters.monitoring', compact(
            'timeframe',
            'selectedWeek',
            'weekLabels',
            'facilitiesWithSubmeters',
            'selectedFacilityId',
            'selectedYear',
            'selectedMonth',
            'yearOptions',
            'monthLabels',
            'mappedRows',
            'weeklyRows',
            'weeklyChartLabels',
            'weeklyChartDatasets',
            'totalSubmeterKwh',
            'totalSubmeterCost',
            'submeterCount',
            'criticalAlertCount',
            'submeterLoadShares',
            'mainMeterKwh',
            'mainMeterCost',
            'accountedPercent',
            'unaccountedKwh',
            'unaccountedCost'
        ));
    }

    public function aiInsight(Request $request, Submeter $submeter): JsonResponse
    {
        if (! $this->canView()) {
            return response()->json([
                'message' => 'You do not have permission to view submeter monitoring.',
            ], 403);
        }

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null && ! in_array((int) $submeter->facility_id, $facilityScope, true)) {
            return response()->json([
                'message' => 'You can only view submeters in your assigned facility.',
            ], 403);
        }
        if (! $submeter->facility()->exists()) {
            return response()->json([
                'message' => 'Submeter facility is archived.',
            ], 404);
        }

        $periodType = (string) $request->query('period_type', 'monthly');
        if (! in_array($periodType, ['daily', 'weekly', 'monthly'], true)) {
            $periodType = 'monthly';
        }

        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange((string) $request->query('month', now()->format('Y-m')));
        $submeter = $submeter->loadMissing('facility:id,name');

        $reading = SubmeterReading::query()
            ->with('alert')
            ->where('submeter_id', $submeter->id)
            ->where('period_type', $periodType)
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->orderByDesc('period_end_date')
            ->orderByDesc('id')
            ->first();

        $fallbackAlertLevel = 'No Data';
        $baselineKwh = null;
        $baselineSource = null;
        $increasePercent = null;
        $actualKwh = null;
        $periodLabel = '-';

        if ($reading) {
            $periodLabel = $reading->periodLabel();
            $actualKwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : null;

            $baselineInfo = $this->pickPreferredSubmeterBaseline(
                SubmeterBaseline::query()
                ->whereIn('baseline_type', $this->submeterBaselineTypePriority())
                ->where('submeter_id', $submeter->id)
                ->where('computed_for_period', $periodLabel)
                ->get()
            );
            $baselineKwh = $baselineInfo['value'];
            $baselineSource = $baselineInfo['type'];

            if ($baselineKwh === null) {
                $configuredMeter = FacilityMeter::query()
                    ->where('facility_id', $submeter->facility_id)
                    ->where('meter_type', 'sub')
                    ->where('meter_name', $submeter->submeter_name)
                    ->whereNotNull('baseline_kwh')
                    ->first(['baseline_kwh']);

                if ($configuredMeter && is_numeric($configuredMeter->baseline_kwh) && (float) $configuredMeter->baseline_kwh > 0) {
                    $baselineKwh = round((float) $configuredMeter->baseline_kwh, 2);
                    $baselineSource = 'configured_meter';
                }
            }

            if ($baselineKwh === null && $reading->alert && is_numeric($reading->alert->baseline_value_kwh) && (float) $reading->alert->baseline_value_kwh > 0) {
                $baselineKwh = round((float) $reading->alert->baseline_value_kwh, 2);
                $baselineSource = 'alert';
            }

            if ($baselineKwh !== null && $baselineKwh > 0 && $actualKwh !== null) {
                $increasePercent = round((($actualKwh - $baselineKwh) / $baselineKwh) * 100, 2);
                $fallbackAlertLevel = $this->mapSubmeterAlertLevelToAi(
                    $this->resolveSubmeterRowAlertFromIncrease($increasePercent, $baselineKwh)
                );
            }

            if ($baselineKwh === null || $baselineKwh <= 0) {
                $increasePercent = null;
                $fallbackAlertLevel = 'No Data';
            }
        }

        $context = [
            'facility_name' => trim((string) (($submeter->facility?->name ?? 'Unknown Facility') . ' - ' . ($submeter->submeter_name ?? 'Submeter'))),
            'facility_type' => 'Submeter',
            'alert_level' => $fallbackAlertLevel,
            'trend_percent' => $increasePercent,
            'actual_kwh' => $actualKwh,
            'baseline_kwh' => $baselineKwh,
            'last_maintenance' => '',
            'next_maintenance' => '',
        ];

        // Without a valid baseline, an AI response must not classify the reading
        // as normal because no variance evaluation is possible yet.
        $hasValidBaseline = $baselineKwh !== null && $baselineKwh > 0;
        $insight = $hasValidBaseline
            ? $this->energyRecommendationService->generateFacilityInsight($context, true)
            : [
                'alert_level' => 'No Data',
                'recommendation' => $actualKwh !== null
                    ? 'A reading is available, but no valid baseline exists for comparison. Configure or compute a baseline before evaluating variance and alert status.'
                    : 'No reading is available for this submeter in the selected period. Verify the reporting schedule and IoT data source.',
                'source' => 'rules',
            ];
        // Alert classification always comes from the configured threshold engine.
        // AI may explain the result, but it must not override the system status.
        $resolvedAlertLevel = $hasValidBaseline ? $fallbackAlertLevel : 'No Data';

        return response()->json([
            'submeter_id' => (int) $submeter->id,
            'submeter_name' => (string) ($submeter->submeter_name ?? 'Submeter'),
            'facility_name' => (string) ($submeter->facility?->name ?? 'Unknown Facility'),
            'period_type' => $periodType,
            'month' => $safeMonth,
            'period_label' => $periodLabel,
            'actual_kwh' => $actualKwh,
            'baseline_kwh' => $baselineKwh,
            'increase_percent' => $increasePercent,
            'baseline_source' => $baselineSource,
            'alert_level' => $resolvedAlertLevel,
            'recommendation' => (string) ($insight['recommendation'] ?? ''),
            'recommendation_source' => (string) ($insight['source'] ?? 'rules'),
        ]);
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('modules.submeters.monitoring')
            ->with('error', 'Submeter readings are sensor-only. Manual encoding is disabled.');
    }

    public function approve(Request $request, SubmeterReading $reading)
    {
        if (! $this->canApprove()) {
            return redirect()->back()->with('error', 'Only engineers/energy officers or admins can approve readings.');
        }

        if (! $reading->approved_at) {
            $reading->approved_by_engineer_id = auth()->id();
            $reading->approved_at = now();
            $reading->save();
        }

        $this->baselineService->processReading($reading->fresh(['submeter.facility']));

        return redirect()->back()->with('success', 'Submeter reading approved.');
    }

    public function alerts(Request $request)
    {
        if (! $this->canViewAlerts()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view submeter alerts.');
        }

        $selectedFacility = $request->query('facility_id');
        $selectedPeriodType = (string) $request->query('period_type', 'monthly');
        if (! in_array($selectedPeriodType, ['daily', 'weekly', 'monthly'], true)) {
            $selectedPeriodType = 'monthly';
        }
        $facilityScope = $this->staffFacilityIds($request);
        $selectedMonth = $this->resolvePreferredAlertMonth(
            (string) $request->query('month', ''),
            $selectedFacility,
            $facilityScope,
            $selectedPeriodType
        );
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);
        $selectedLevel = (string) $request->query('alert_level', '');
        $allowedLevels = ['warning', 'high', 'very_high', 'critical', 'drop_warning', 'drop_high', 'drop_critical'];
        $selectedLevel = in_array($selectedLevel, $allowedLevels, true) ? $selectedLevel : '';

        $readings = SubmeterReading::query()
            ->with(['submeter.facility:id,name', 'alert'])
            ->where('period_type', $selectedPeriodType)
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('submeter.facility')
            ->when($selectedFacility, fn ($query) => $query->whereHas(
                'submeter',
                fn (Builder $builder) => $builder->where('facility_id', $selectedFacility)
            ))
            ->when($facilityScope !== null, fn ($query) => $query->whereHas(
                'submeter',
                fn (Builder $builder) => $builder->whereIn('facility_id', $facilityScope)
            ))
            ->orderByDesc('period_end_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('submeter_id')
            ->map(fn (Collection $group) => $group->first())
            ->values();

        $periodLabels = $readings->map(fn (SubmeterReading $reading) => $reading->periodLabel())->unique()->values();
        $baselineMap = SubmeterBaseline::query()
            ->whereIn('baseline_type', $this->submeterBaselineTypePriority())
            ->whereIn('submeter_id', $readings->pluck('submeter_id')->unique())
            ->whereIn('computed_for_period', $periodLabels)
            ->get()
            ->groupBy(fn (SubmeterBaseline $baseline) => $baseline->submeter_id.'|'.$baseline->computed_for_period);
        $configuredBaselineMap = FacilityMeter::query()
            ->where('meter_type', 'sub')
            ->whereIn('facility_id', $readings->pluck('submeter.facility_id')->filter()->unique())
            ->whereNotNull('baseline_kwh')
            ->get(['facility_id', 'meter_name', 'baseline_kwh'])
            ->filter(fn (FacilityMeter $meter) => is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0)
            ->keyBy(fn (FacilityMeter $meter) => $this->submeterMeterKey((int) $meter->facility_id, (string) $meter->meter_name));

        $evaluatedAlerts = $readings->map(function (SubmeterReading $reading) use ($baselineMap, $configuredBaselineMap) {
            $baselineInfo = $this->pickPreferredSubmeterBaseline(
                $baselineMap->get($reading->submeter_id.'|'.$reading->periodLabel(), collect())
            );
            $baseline = $baselineInfo['value'];
            $baselineSource = $baselineInfo['type'];
            if ($baseline === null && $reading->submeter) {
                $configuredMeter = $configuredBaselineMap->get($this->submeterMeterKey(
                    (int) $reading->submeter->facility_id,
                    (string) $reading->submeter->submeter_name
                ));
                if ($configuredMeter instanceof FacilityMeter) {
                    $baseline = round((float) $configuredMeter->baseline_kwh, 2);
                    $baselineSource = 'configured_meter';
                }
            }
            if ($baseline === null && $reading->alert && is_numeric($reading->alert->baseline_value_kwh) && (float) $reading->alert->baseline_value_kwh > 0) {
                $baseline = round((float) $reading->alert->baseline_value_kwh, 2);
                $baselineSource = 'alert';
            }
            if ($baseline === null || $baseline <= 0 || ! is_numeric($reading->kwh_used)) {
                return null;
            }

            $actual = (float) $reading->kwh_used;
            $variance = round((($actual - $baseline) / $baseline) * 100, 2);
            $level = $this->resolveSubmeterRowAlertFromIncrease($variance, $baseline);
            if (in_array($level, ['none', 'normal'], true)) {
                return null;
            }

            $reading->setAttribute('alert_baseline_kwh', $baseline);
            $reading->setAttribute('alert_baseline_source', $baselineSource);
            $reading->setAttribute('alert_variance_percent', $variance);
            $reading->setAttribute('alert_evaluated_level', $level);
            $reading->setAttribute('alert_reason', $this->buildEvaluatedSubmeterAlertReason($level, $actual, $baseline, $variance));

            return $reading;
        })->filter()->sortByDesc(fn (SubmeterReading $reading) =>
            ($this->submeterAlertSeverityRank((string) $reading->alert_evaluated_level) * 100000)
            + abs((float) $reading->alert_variance_percent)
        )->values();

        $alertSummary = [
            'total' => $evaluatedAlerts->count(),
            'critical' => $evaluatedAlerts->whereIn('alert_evaluated_level', ['critical', 'drop_critical'])->count(),
            'increases' => $evaluatedAlerts->filter(fn (SubmeterReading $reading) => (float) $reading->alert_variance_percent > 0)->count(),
            'drops' => $evaluatedAlerts->filter(fn (SubmeterReading $reading) => (float) $reading->alert_variance_percent < 0)->count(),
        ];
        $filteredAlerts = $selectedLevel === ''
            ? $evaluatedAlerts
            : $evaluatedAlerts->where('alert_evaluated_level', $selectedLevel)->values();
        $page = max(1, $request->integer('page', 1));
        $perPage = 15;
        $alerts = new LengthAwarePaginator(
            $filteredAlerts->forPage($page, $perPage)->values(),
            $filteredAlerts->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.submeters.alerts', [
            'alerts' => $alerts,
            'facilities' => $facilities,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'selectedLevel' => $selectedLevel,
            'selectedPeriodType' => $selectedPeriodType,
            'alertSummary' => $alertSummary,
        ]);
    }

    public function show(Request $request, Submeter $submeter)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view submeter details.');
        }

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null && ! in_array((int) $submeter->facility_id, $facilityScope, true)) {
            return redirect()->route('modules.submeters.monitoring')->with('error', 'You can only view submeters in your assigned facility.');
        }
        if (! $submeter->facility()->exists()) {
            return redirect()->route('modules.submeters.monitoring')->with('error', 'This submeter belongs to an archived facility.');
        }

        $periodType = (string) $request->query('period_type', 'monthly');
        if (! in_array($periodType, ['daily', 'weekly', 'monthly'], true)) {
            $periodType = 'monthly';
        }

        $readings = SubmeterReading::query()
            ->with('alert')
            ->where('submeter_id', $submeter->id)
            ->where('period_type', $periodType)
            ->orderByDesc('period_end_date')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $labels = [];
        $kwhSeries = [];
        $baselineSeries = [];
        $periodLabels = $readings->map(fn (SubmeterReading $reading) => $reading->periodLabel())->unique()->values();
        $baselineRowsByLabel = SubmeterBaseline::query()
            ->where('submeter_id', $submeter->id)
            ->whereIn('baseline_type', $this->submeterBaselineTypePriority())
            ->whereIn('computed_for_period', $periodLabels)
            ->get()
            ->groupBy('computed_for_period');

        $configuredMeter = FacilityMeter::query()
            ->where('facility_id', $submeter->facility_id)
            ->where('meter_type', 'sub')
            ->where('meter_name', $submeter->submeter_name)
            ->whereNotNull('baseline_kwh')
            ->first(['baseline_kwh']);
        $configuredBaseline = $configuredMeter
            && is_numeric($configuredMeter->baseline_kwh)
            && (float) $configuredMeter->baseline_kwh > 0
                ? round((float) $configuredMeter->baseline_kwh, 2)
                : null;

        foreach ($readings as $reading) {
            $label = $reading->periodLabel();
            $labels[] = $label;
            $kwhSeries[] = (float) $reading->kwh_used;

            $baselineInfo = $this->pickPreferredSubmeterBaseline($baselineRowsByLabel->get($label, collect()));
            $baseline = $baselineInfo['value'];
            $baselineSource = $baselineInfo['type'];
            if ($baseline === null && $configuredBaseline !== null) {
                $baseline = $configuredBaseline;
                $baselineSource = 'configured_meter';
            }
            if ($baseline === null && $reading->alert && is_numeric($reading->alert->baseline_value_kwh) && (float) $reading->alert->baseline_value_kwh > 0) {
                $baseline = round((float) $reading->alert->baseline_value_kwh, 2);
                $baselineSource = 'alert';
            }
            $variance = $baseline !== null && $baseline > 0
                ? round((((float) $reading->kwh_used - $baseline) / $baseline) * 100, 2)
                : null;
            $level = $this->resolveSubmeterRowAlertFromIncrease($variance, $baseline);

            $baselineSeries[] = $baseline;
            $reading->setAttribute('detail_baseline_kwh', $baseline);
            $reading->setAttribute('detail_baseline_source', $baselineSource);
            $reading->setAttribute('detail_variance_percent', $variance);
            $reading->setAttribute('detail_alert_level', $level);
            $reading->setAttribute('detail_reason', $variance !== null && $baseline !== null
                ? $this->buildEvaluatedSubmeterAlertReason($level, (float) $reading->kwh_used, $baseline, $variance)
                : 'No valid baseline is available for this period, so the reading cannot be evaluated.');
        }

        $readingsForTable = $readings->reverse()->values();
        $alertsTimeline = $readingsForTable
            ->filter(fn (SubmeterReading $reading) => ! in_array(
                (string) ($reading->detail_alert_level ?? 'none'),
                ['none', 'normal'],
                true
            ))
            ->values();
        $latestReading = $readings->last();
        $latestBaseline = $latestReading?->detail_baseline_kwh;
        $latestVariance = $latestReading?->detail_variance_percent;
        $latestLevel = (string) ($latestReading?->detail_alert_level ?? 'none');
        $detailSummary = [
            'actual_kwh' => $latestReading && is_numeric($latestReading->kwh_used) ? (float) $latestReading->kwh_used : null,
            'baseline_kwh' => is_numeric($latestBaseline) ? (float) $latestBaseline : null,
            'variance_percent' => is_numeric($latestVariance) ? (float) $latestVariance : null,
            'alert_level' => $latestLevel,
            'baseline_source' => $latestReading?->detail_baseline_source,
            'period_label' => $latestReading?->periodLabel(),
            'average_kwh' => $readings->isNotEmpty() ? round((float) $readings->avg('kwh_used'), 2) : null,
            'actionable_periods' => $alertsTimeline->count(),
        ];

        $latestReadingEndDate = SubmeterReading::query()
            ->where('submeter_id', $submeter->id)
            ->max('period_end_date');
        $loadTrackingMonth = $latestReadingEndDate
            ? Carbon::parse((string) $latestReadingEndDate)->format('Y-m')
            : now()->format('Y-m');

        return view('modules.submeters.detail', [
            'submeter' => $submeter->load('facility:id,name'),
            'periodType' => $periodType,
            'labels' => $labels,
            'kwhSeries' => $kwhSeries,
            'baselineSeries' => $baselineSeries,
            'readings' => $readings,
            'readingsForTable' => $readingsForTable,
            'alertsTimeline' => $alertsTimeline,
            'detailSummary' => $detailSummary,
            'canApprove' => $this->canApprove(),
            'loadTrackingMonth' => $loadTrackingMonth,
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

    private function resolvePreferredReadingMonth(
        string $requestedMonth,
        string $periodType,
        mixed $selectedFacility,
        string $selectedDepartment,
        ?array $facilityScope
    ): string {
        $requestedMonth = trim($requestedMonth);
        if ($requestedMonth !== '') {
            return $requestedMonth;
        }

        $submeterIds = Submeter::query()
            ->whereHas('facility')
            ->when($selectedFacility, fn ($q) => $q->where('facility_id', $selectedFacility))
            ->when($selectedDepartment !== '', fn ($q) => $q->where('submeter_name', 'like', "%{$selectedDepartment}%"))
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('facility_id', $facilityScope))
            ->pluck('id');

        if ($submeterIds->isNotEmpty()) {
            $coverage = SubmeterReading::query()
                ->where('period_type', $periodType)
                ->whereIn('submeter_id', $submeterIds)
                ->selectRaw("DATE_FORMAT(period_end_date, '%Y-%m') as ym, COUNT(DISTINCT submeter_id) as covered")
                ->groupBy('ym')
                ->orderByDesc('covered')
                ->orderByDesc('ym')
                ->first();

            if ($coverage && ! empty($coverage->ym)) {
                return (string) $coverage->ym;
            }
        }

        $latest = SubmeterReading::query()
            ->where('period_type', $periodType)
            ->when($submeterIds->isNotEmpty(), fn ($q) => $q->whereIn('submeter_id', $submeterIds))
            ->max('period_end_date');

        if ($latest) {
            return Carbon::parse((string) $latest)->format('Y-m');
        }

        return now()->format('Y-m');
    }

    private function resolvePreferredAlertMonth(
        string $requestedMonth,
        mixed $selectedFacility,
        ?array $facilityScope,
        string $periodType = 'monthly'
    ): string {
        $requestedMonth = trim($requestedMonth);
        if ($requestedMonth !== '') {
            return $requestedMonth;
        }

        $query = SubmeterReading::query()
            ->where('period_type', $periodType)
            ->whereHas('submeter.facility');

        if ($selectedFacility) {
            $query->whereHas('submeter', function (Builder $builder) use ($selectedFacility) {
                $builder->where('facility_id', $selectedFacility)
                    ->whereHas('facility');
            });
        }

        if ($facilityScope !== null) {
            $query->whereHas('submeter', function (Builder $builder) use ($facilityScope) {
                $builder->whereIn('facility_id', $facilityScope)
                    ->whereHas('facility');
            });
        }

        $latest = $query->max('period_end_date');
        if ($latest) {
            return Carbon::parse($latest)->format('Y-m');
        }

        return now()->format('Y-m');
    }

    private function buildDashboardWidgets(
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd,
        mixed $selectedFacility,
        ?array $facilityScope
    ): array {
        $alertQuery = SubmeterAlert::query()
            ->whereHas('reading', function (Builder $builder) use ($periodType, $periodStart, $periodEnd) {
                $builder
                    ->where('period_type', $periodType)
                    ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        if ($selectedFacility) {
            $alertQuery->whereHas('submeter', function (Builder $builder) use ($selectedFacility) {
                $builder->where('facility_id', $selectedFacility)
                    ->whereHas('facility');
            });
        }

        if ($facilityScope !== null) {
            $alertQuery->whereHas('submeter', function (Builder $builder) use ($facilityScope) {
                $builder->whereIn('facility_id', $facilityScope)
                    ->whereHas('facility');
            });
        }

        $top5 = (clone $alertQuery)
            ->with(['submeter.facility:id,name', 'reading:id,submeter_id,period_end_date'])
            ->orderByDesc('increase_percent')
            ->limit(5)
            ->get();

        $criticalThisMonth = (clone $alertQuery)
            ->where('alert_level', 'critical')
            ->count();

        $facilitiesWithMostAlerts = (clone $alertQuery)
            ->join('submeters', 'submeters.id', '=', 'submeter_alerts.submeter_id')
            ->join('facilities', 'facilities.id', '=', 'submeters.facility_id')
            ->whereNull('facilities.deleted_at')
            ->select([
                'facilities.id as facility_id',
                'facilities.name as facility_name',
                DB::raw('COUNT(*) as total_alerts'),
            ])
            ->groupBy('facilities.id', 'facilities.name')
            ->orderByDesc('total_alerts')
            ->limit(5)
            ->get();

        $facilitiesWithAlertsCount = (clone $alertQuery)
            ->join('submeters', 'submeters.id', '=', 'submeter_alerts.submeter_id')
            ->join('facilities', 'facilities.id', '=', 'submeters.facility_id')
            ->whereNull('facilities.deleted_at')
            ->distinct()
            ->count('facilities.id');

        return [
            'top5HighestIncrease' => $top5,
            'criticalAlertsThisMonth' => $criticalThisMonth,
            'facilitiesWithMostAlerts' => $facilitiesWithMostAlerts,
            'facilitiesWithAlertsCount' => $facilitiesWithAlertsCount,
        ];
    }

    private function buildSensorTrendSeries(
        string $period,
        mixed $selectedFacility,
        string $selectedDepartment,
        ?array $facilityScope,
        ?int $selectedSensorSubmeter = null
    ): array {
        $period = in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true) ? $period : 'daily';
        $now = now();

        if ($period === 'yearly') {
            $start = $now->copy()->subYears(4)->startOfYear();
            $end = $now->copy()->endOfYear();
            $labels = collect(range(0, 4))->map(fn ($offset) => $start->copy()->addYears($offset)->format('Y'));
            $labelFor = fn (SubmeterReading $reading): string => Carbon::parse($reading->received_at ?? $reading->period_end_date)->format('Y');
        } elseif ($period === 'monthly') {
            $start = $now->copy()->subMonthsNoOverflow(11)->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $labels = collect(range(0, 11))->map(fn ($offset) => $start->copy()->addMonthsNoOverflow($offset)->format('Y-m'));
            $labelFor = fn (SubmeterReading $reading): string => Carbon::parse($reading->received_at ?? $reading->period_end_date)->format('Y-m');
        } elseif ($period === 'weekly') {
            $start = $now->copy()->subWeeks(11)->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $labels = collect(range(0, 11))->map(fn ($offset) => $start->copy()->addWeeks($offset)->format('o-\WW'));
            $labelFor = fn (SubmeterReading $reading): string => Carbon::parse($reading->received_at ?? $reading->period_end_date)->format('o-\WW');
        } else {
            $start = $now->copy()->subDays(29)->startOfDay();
            $end = $now->copy()->endOfDay();
            $labels = collect(range(0, 29))->map(fn ($offset) => $start->copy()->addDays($offset)->format('M d'));
            $labelFor = fn (SubmeterReading $reading): string => Carbon::parse($reading->received_at ?? $reading->period_end_date)->format('M d');
        }

        $query = SubmeterReading::query()
            ->with('submeter:id,facility_id,submeter_name')
            ->where('input_source', 'iot')
            ->whereBetween(DB::raw('COALESCE(received_at, period_end_date)'), [$start->toDateTimeString(), $end->toDateTimeString()])
            ->whereHas('submeter.facility');

        if ($selectedSensorSubmeter !== null) {
            $query->where('submeter_id', $selectedSensorSubmeter);
        }

        if ($selectedFacility) {
            $query->whereHas('submeter', fn (Builder $builder) => $builder->where('facility_id', $selectedFacility));
        }

        if ($selectedDepartment !== '') {
            $query->whereHas('submeter', fn (Builder $builder) => $builder->where('submeter_name', 'like', "%{$selectedDepartment}%"));
        }

        if ($facilityScope !== null) {
            $query->whereHas('submeter', fn (Builder $builder) => $builder->whereIn('facility_id', $facilityScope));
        }

        $readings = $query->get(['submeter_id', 'period_end_date', 'received_at', 'kwh_used']);
        $totals = $readings
            ->groupBy(fn (SubmeterReading $reading) => $labelFor($reading))
            ->map(fn (Collection $group) => round((float) $group->sum('kwh_used'), 2));

        return [
            'period' => $period,
            'labels' => $labels->values()->all(),
            'kwh' => $labels->map(fn ($label) => (float) $totals->get($label, 0))->values()->all(),
            'total_kwh' => round((float) $readings->sum('kwh_used'), 2),
            'reading_count' => $readings->count(),
        ];
    }

    private function canView(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
    }

    private function canEncode(): bool
    {
        return false;
    }

    private function canApprove(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'approve_submeter_readings')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'engineer']);
    }

    private function canViewAlerts(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'view_submeter_alerts')
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

    private function mapSubmeterAlertLevelToAi(?string $level): string
    {
        return match (strtolower(trim((string) $level))) {
            'critical' => 'Critical',
            'very_high', 'very high' => 'Very High',
            'high' => 'High',
            'warning' => 'Warning',
            'drop_critical', 'drop critical' => 'Drop Critical',
            'drop_high', 'drop high' => 'Drop High',
            'drop_warning', 'drop warning' => 'Drop Warning',
            'normal' => 'Normal',
            'none' => 'No Data',
            default => 'No Data',
        };
    }

    private function resolveSubmeterRowAlertFromIncrease(?float $increasePercent, ?float $baselineKwh): string
    {
        if ($increasePercent === null || $baselineKwh === null || $baselineKwh <= 0) {
            return 'none';
        }

        return $this->normalizeSubmeterRowAlertLevel(
            EnergyRecord::resolveAlertLevel($increasePercent, $baselineKwh)
        );
    }

    private function normalizeSubmeterRowAlertLevel(?string $level): string
    {
        return match (strtolower(trim((string) $level))) {
            'critical' => 'critical',
            'very high', 'very_high' => 'very_high',
            'high' => 'high',
            'warning' => 'warning',
            'drop critical', 'drop_critical' => 'drop_critical',
            'drop high', 'drop_high' => 'drop_high',
            'drop warning', 'drop_warning' => 'drop_warning',
            'normal' => 'normal',
            default => 'none',
        };
    }

    private function submeterAlertSeverityRank(string $level): int
    {
        return match ($level) {
            'critical', 'drop_critical' => 4,
            'very_high' => 3,
            'high', 'drop_high' => 2,
            'warning', 'drop_warning' => 1,
            default => 0,
        };
    }

    private function buildEvaluatedSubmeterAlertReason(
        string $level,
        float $actualKwh,
        float $baselineKwh,
        float $variancePercent
    ): string {
        $sizeKey = EnergyRecord::resolveSizeKeyFromBaseline($baselineKwh);
        $thresholds = EnergyRecord::alertThresholdsBySize()[$sizeKey] ?? [];
        $sizeLabel = match ($sizeKey) {
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'xlarge' => 'Extra Large',
            default => ucfirst($sizeKey),
        };
        $threshold = match ($level) {
            'critical' => $thresholds['level5'] ?? null,
            'very_high' => $thresholds['level4'] ?? null,
            'high' => $thresholds['level3'] ?? null,
            'warning' => $thresholds['level2'] ?? null,
            'drop_critical' => $thresholds['drop']['level3'] ?? null,
            'drop_high' => $thresholds['drop']['level2'] ?? null,
            'drop_warning' => $thresholds['drop']['level1'] ?? null,
            default => null,
        };
        $direction = $variancePercent < 0 ? 'below' : 'above';
        $thresholdText = is_numeric($threshold)
            ? sprintf(' The configured %s threshold starts beyond %.2f%%.', str_replace('_', ' ', $level), (float) $threshold)
            : '';

        return sprintf(
            '%s baseline classification. Current usage %.2f kWh is %.2f%% %s the %.2f kWh baseline.%s',
            $sizeLabel,
            $actualKwh,
            abs($variancePercent),
            $direction,
            $baselineKwh,
            $thresholdText
        );
    }

    private function submeterMeterKey(int $facilityId, string $meterName): string
    {
        return $facilityId.'|'.mb_strtolower(trim($meterName));
    }

    /**
     * @return array<int, string>
     */
    private function submeterBaselineTypePriority(): array
    {
        return ['normalized_per_day', 'moving_avg_3', 'seasonal_month', 'moving_avg_6', 'equipment_estimate'];
    }

    /**
     * @return array{value: float|null, type: string|null}
     */
    private function pickPreferredSubmeterBaseline(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return ['value' => null, 'type' => null];
        }

        foreach ($this->submeterBaselineTypePriority() as $type) {
            $value = $rows->firstWhere('baseline_type', $type)?->baseline_value_kwh;
            if (is_numeric($value) && (float) $value > 0) {
                return [
                    'value' => round((float) $value, 2),
                    'type' => (string) $type,
                ];
            }
        }

        return ['value' => null, 'type' => null];
    }
}
