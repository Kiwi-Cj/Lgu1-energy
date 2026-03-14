<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
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

        $periodType = (string) $request->query('period_type', 'monthly');
        if (! in_array($periodType, ['daily', 'weekly', 'monthly'], true)) {
            $periodType = 'monthly';
        }

        $selectedFacility = $request->query('facility_id');
        $selectedDepartment = trim((string) $request->query('department', ''));
        $facilityScope = $this->staffFacilityIds($request);
        $selectedMonth = $this->resolvePreferredReadingMonth(
            (string) $request->query('month', ''),
            $periodType,
            $selectedFacility,
            $selectedDepartment,
            $facilityScope
        );
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);

        $submetersForTable = Submeter::query()
            ->with('facility:id,name')
            ->whereHas('facility')
            ->when($selectedFacility, fn ($q) => $q->where('facility_id', $selectedFacility))
            ->when($selectedDepartment !== '', fn ($q) => $q->where('submeter_name', 'like', "%{$selectedDepartment}%"))
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('facility_id', $facilityScope))
            ->orderBy('submeter_name')
            ->get(['id', 'facility_id', 'submeter_name', 'status']);

        $submeterIds = $submetersForTable->pluck('id')->filter()->unique()->values();

        $rawRows = SubmeterReading::query()
            ->with('alert')
            ->where('period_type', $periodType)
            ->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->when($submeterIds->isNotEmpty(), fn ($q) => $q->whereIn('submeter_id', $submeterIds))
            ->orderByDesc('period_end_date')
            ->orderByDesc('id')
            ->get();

        $rowsBySubmeter = $rawRows
            ->groupBy('submeter_id')
            ->map(fn ($group) => $group->first());

        $periodLabels = $rowsBySubmeter->map(fn (SubmeterReading $r) => $r->periodLabel())->unique()->values();

        $baselineMap = SubmeterBaseline::query()
            ->whereIn('baseline_type', $this->submeterBaselineTypePriority())
            ->whereIn('submeter_id', $submeterIds)
            ->whereIn('computed_for_period', $periodLabels)
            ->get()
            ->groupBy(fn ($item) => $item->submeter_id . '|' . $item->computed_for_period);

        $rows = $submetersForTable->map(function (Submeter $submeter) use ($rowsBySubmeter, $baselineMap, $periodType) {
            $reading = $rowsBySubmeter->get($submeter->id);
            $hasReading = $reading instanceof SubmeterReading;

            if (! $hasReading) {
                return null;
            }

            $reading->setRelation('submeter', $submeter);
            $reading->setAttribute('monitor_has_reading', $hasReading);

            $baselineInfo = $this->pickPreferredSubmeterBaseline(
                $baselineMap->get($reading->submeter_id . '|' . $reading->periodLabel(), collect())
            );
            $baseline = $baselineInfo['value'];
            $alert = $reading->alert;
            if ($alert) {
                if ($baseline === null && is_numeric($alert->baseline_value_kwh)) {
                    $baseline = (float) $alert->baseline_value_kwh;
                }
                $reading->setAttribute('monitor_baseline_kwh', $baseline);
                $reading->setAttribute('monitor_baseline_source', $baselineInfo['type'] ?? 'alert');
                $increaseFromAlert = is_numeric($alert->increase_percent) ? (float) $alert->increase_percent : null;
                $reading->setAttribute('monitor_alert_level', $this->normalizeSubmeterRowAlertLevel((string) $alert->alert_level));
                $reading->setAttribute('monitor_increase_percent', $increaseFromAlert);
                return $reading;
            }

            $reading->setAttribute('monitor_baseline_kwh', $baseline);
            $reading->setAttribute('monitor_baseline_source', $baselineInfo['type']);
            $increasePercent = null;
            if ($baseline && $baseline > 0) {
                $kwh = is_numeric($reading->kwh_used) ? (float) $reading->kwh_used : 0.0;
                $increasePercent = round((($kwh - $baseline) / $baseline) * 100, 2);
            } else {
                $increasePercent = null;
            }
            $reading->setAttribute('monitor_increase_percent', $increasePercent);
            $reading->setAttribute('monitor_alert_level', $this->resolveSubmeterRowAlertFromIncrease($increasePercent));
            return $reading;
        })->filter()->values();

        $widgets = $this->buildDashboardWidgets($periodType, $periodStart, $periodEnd, $selectedFacility, $facilityScope);
        $widgets['top5HighestIncrease'] = $rows
            ->filter(fn (SubmeterReading $row) => (bool) ($row->monitor_has_reading ?? false))
            ->filter(fn (SubmeterReading $row) => is_numeric($row->monitor_increase_percent ?? null))
            ->sortByDesc(fn (SubmeterReading $row) => (float) $row->monitor_increase_percent)
            ->take(5)
            ->values();

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        $submeters = Submeter::query()
            ->with('facility:id,name')
            ->whereHas('facility')
            ->when($selectedFacility, fn ($q) => $q->where('facility_id', $selectedFacility))
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('facility_id', $facilityScope))
            ->orderBy('submeter_name')
            ->get(['id', 'facility_id', 'submeter_name', 'status']);
        $submetersForEncode = $submeters->where('status', 'active')->values();

        return view('modules.submeters.monitoring', [
            'rows' => $rows,
            'periodType' => $periodType,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'selectedDepartment' => $selectedDepartment,
            'facilities' => $facilities,
            'submeters' => $submeters,
            'submetersForEncode' => $submetersForEncode,
            'widgets' => $widgets,
            'canEncode' => $this->canEncode(),
            'canApprove' => $this->canApprove(),
            'canViewAlerts' => $this->canViewAlerts(),
        ]);
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

            if ($reading->alert) {
                if ($baselineKwh === null && is_numeric($reading->alert->baseline_value_kwh)) {
                    $baselineKwh = (float) $reading->alert->baseline_value_kwh;
                }
                $increasePercent = is_numeric($reading->alert->increase_percent)
                    ? (float) $reading->alert->increase_percent
                    : null;
                $fallbackAlertLevel = $this->mapSubmeterAlertLevelToAi((string) $reading->alert->alert_level);
            } else {
                if ($baselineKwh !== null && $baselineKwh > 0 && $actualKwh !== null) {
                    $increasePercent = round((($actualKwh - $baselineKwh) / $baselineKwh) * 100, 2);
                }
                $fallbackAlertLevel = $this->mapSubmeterAlertLevelToAi(
                    $this->resolveSubmeterRowAlertFromIncrease($increasePercent)
                );
            }
        }

        $context = [
            'facility_name' => trim((string) (($submeter->facility?->name ?? 'Unknown Facility') . ' - ' . ($submeter->submeter_name ?? 'Submeter'))),
            'facility_type' => 'Submeter',
            'alert_level' => $fallbackAlertLevel,
            'trend_percent' => $increasePercent,
            'actual_kwh' => $actualKwh,
            'baseline_kwh' => $baselineKwh,
            'floor_area' => null,
            'last_maintenance' => '',
            'next_maintenance' => '',
        ];

        $insight = $this->energyRecommendationService->generateFacilityInsight($context, true);

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
            'alert_level' => (string) ($insight['alert_level'] ?? $fallbackAlertLevel),
            'recommendation' => (string) ($insight['recommendation'] ?? ''),
            'recommendation_source' => (string) ($insight['source'] ?? 'rules'),
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->canEncode()) {
            return redirect()->back()->with('error', 'You do not have permission to encode submeter readings.');
        }

        $validated = $request->validate([
            'submeter_id' => 'required|integer|exists:submeters,id',
            'period_type' => 'required|in:daily,weekly,monthly',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after_or_equal:period_start_date',
            'reading_start_kwh' => 'required|numeric|min:0',
            'reading_end_kwh' => 'required|numeric|min:0|gte:reading_start_kwh',
            'operating_days' => 'nullable|integer|min:1|max:366',
        ]);

        $submeter = Submeter::with('facility')->findOrFail((int) $validated['submeter_id']);
        if ($submeter->status !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Selected submeter is inactive.');
        }
        if (! $submeter->facility) {
            return redirect()->back()->withInput()->with('error', 'Selected submeter belongs to an archived facility.');
        }

        $facilityScope = $this->staffFacilityIds($request);
        if ($facilityScope !== null && ! in_array((int) $submeter->facility_id, $facilityScope, true)) {
            return redirect()->back()->withInput()->with('error', 'You can only encode readings for your assigned facility.');
        }

        $duplicate = SubmeterReading::query()
            ->where('submeter_id', $submeter->id)
            ->where('period_type', $validated['period_type'])
            ->whereDate('period_start_date', $validated['period_start_date'])
            ->whereDate('period_end_date', $validated['period_end_date'])
            ->exists();

        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', 'A reading for the same submeter and period already exists.');
        }

        $reading = SubmeterReading::create([
            'submeter_id' => $submeter->id,
            'period_type' => $validated['period_type'],
            'period_start_date' => $validated['period_start_date'],
            'period_end_date' => $validated['period_end_date'],
            'reading_start_kwh' => $validated['reading_start_kwh'],
            'reading_end_kwh' => $validated['reading_end_kwh'],
            'operating_days' => $validated['operating_days'] ?? null,
            'encoded_by_user_id' => auth()->id(),
            'approved_by_engineer_id' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->baselineService->processReading($reading->fresh(['submeter.facility']));

        return redirect()->route('modules.submeters.monitoring', [
            'period_type' => $validated['period_type'],
            'month' => Carbon::parse($validated['period_end_date'])->format('Y-m'),
            'facility_id' => $submeter->facility_id,
        ])->with('success', 'Submeter reading encoded successfully.');
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
        $facilityScope = $this->staffFacilityIds($request);
        $selectedMonth = $this->resolvePreferredAlertMonth(
            (string) $request->query('month', ''),
            $selectedFacility,
            $facilityScope
        );
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);
        $selectedLevel = (string) $request->query('alert_level', '');
        $selectedLevel = in_array($selectedLevel, ['warning', 'critical'], true) ? $selectedLevel : '';

        $query = SubmeterAlert::query()
            ->with([
                'submeter.facility:id,name',
                'reading:id,submeter_id,period_type,period_start_date,period_end_date,kwh_used,approved_at',
            ])
            ->whereHas('submeter.facility')
            ->whereHas('reading', function (Builder $builder) use ($periodStart, $periodEnd) {
                $builder->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        if ($selectedFacility) {
            $query->whereHas('submeter', function (Builder $builder) use ($selectedFacility) {
                $builder->where('facility_id', $selectedFacility);
            });
        }

        if ($selectedLevel !== '') {
            $query->where('alert_level', $selectedLevel);
        }

        if ($facilityScope !== null) {
            $query->whereHas('submeter', function (Builder $builder) use ($facilityScope) {
                $builder->whereIn('facility_id', $facilityScope);
            });
        }

        $alerts = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
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

        foreach ($readings as $reading) {
            $label = $reading->periodLabel();
            $labels[] = $label;
            $kwhSeries[] = (float) $reading->kwh_used;

            $baselineInfo = $this->pickPreferredSubmeterBaseline($baselineRowsByLabel->get($label, collect()));
            $baselineSeries[] = $baselineInfo['value'];
        }

        $alertsTimeline = SubmeterAlert::query()
            ->with('reading:id,submeter_id,period_type,period_start_date,period_end_date,kwh_used')
            ->where('submeter_id', $submeter->id)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

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
            'alertsTimeline' => $alertsTimeline,
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
        ?array $facilityScope
    ): string {
        $requestedMonth = trim($requestedMonth);
        if ($requestedMonth !== '') {
            return $requestedMonth;
        }

        $query = SubmeterReading::query()
            ->where('period_type', 'monthly')
            ->whereHas('alert');

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

        return [
            'top5HighestIncrease' => $top5,
            'criticalAlertsThisMonth' => $criticalThisMonth,
            'facilitiesWithMostAlerts' => $facilitiesWithMostAlerts,
        ];
    }

    private function canView(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
    }

    private function canEncode(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'encode_submeter_readings')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff']);
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
            'warning' => 'Warning',
            'normal', 'none' => 'Normal',
            default => 'No Data',
        };
    }

    private function resolveSubmeterRowAlertFromIncrease(?float $increasePercent): string
    {
        if ($increasePercent === null) {
            return 'none';
        }

        if ($increasePercent >= 10.0) {
            return 'critical';
        }

        if ($increasePercent >= 3.0) {
            return 'warning';
        }

        return 'normal';
    }

    private function normalizeSubmeterRowAlertLevel(?string $level): string
    {
        return match (strtolower(trim((string) $level))) {
            'critical' => 'critical',
            'warning' => 'warning',
            'normal' => 'normal',
            default => 'none',
        };
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
