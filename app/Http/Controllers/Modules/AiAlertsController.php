<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MainMeterAlert;
use App\Models\SubmeterAlert;
use App\Services\EnergyRecommendationService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AiAlertsController extends Controller
{
    public function __construct(
        private readonly EnergyRecommendationService $energyRecommendationService
    ) {
    }

    public function index(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view AI alerts.');
        }

        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));
        [$periodStart, $periodEnd, $safeMonth] = $this->resolveMonthRange($selectedMonth);

        $selectedFacility = $request->query('facility_id');
        $selectedSource = strtolower(trim((string) $request->query('source', 'all')));
        if (! in_array($selectedSource, ['all', 'main', 'sub'], true)) {
            $selectedSource = 'all';
        }

        $selectedLevel = strtolower(trim((string) $request->query('alert_level', '')));
        if (! in_array($selectedLevel, ['', 'warning', 'critical'], true)) {
            $selectedLevel = '';
        }

        $facilityScope = $this->staffFacilityIds($request);

        $mainRows = collect();
        if (in_array($selectedSource, ['all', 'main'], true)) {
            $mainQuery = MainMeterAlert::query()
                ->with([
                    'facility:id,name',
                    'reading:id,facility_id,period_type,period_start_date,period_end_date,kwh_used,peak_demand_kw,approved_at',
                ])
                ->whereHas('facility')
                ->whereHas('reading', function (Builder $builder) use ($periodStart, $periodEnd) {
                    $builder->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
                });

            if ($selectedFacility) {
                $mainQuery->where('facility_id', $selectedFacility);
            }

            if ($selectedLevel !== '') {
                $mainQuery->whereRaw('LOWER(alert_level) = ?', [$selectedLevel]);
            }

            if ($facilityScope !== null) {
                $mainQuery->whereIn('facility_id', $facilityScope);
            }

            $mainRows = $mainQuery
                ->orderByDesc('created_at')
                ->get()
                ->map(function (MainMeterAlert $alert) use ($safeMonth) {
                    $createdAt = $alert->created_at;
                    $facility = $alert->facility;
                    return [
                        'source' => 'main',
                        'source_label' => 'Main Meter',
                        'facility_name' => (string) ($facility?->name ?? '-'),
                        'facility_type' => (string) ($facility?->type ?? ''),
                        'floor_area' => $this->resolveFloorArea($facility),
                        'meter_name' => 'Main Meter',
                        'period_label' => $alert->reading?->periodLabel() ?? '-',
                        'current_kwh' => is_numeric($alert->current_kwh) ? (float) $alert->current_kwh : null,
                        'baseline_kwh' => is_numeric($alert->baseline_kwh) ? (float) $alert->baseline_kwh : null,
                        'increase_percent' => is_numeric($alert->increase_percent) ? (float) $alert->increase_percent : null,
                        'alert_level' => strtolower((string) ($alert->alert_level ?? 'warning')),
                        'reason' => (string) ($alert->reason ?? ''),
                        'created_at' => $createdAt,
                        'created_at_ts' => $createdAt ? $createdAt->getTimestamp() : 0,
                        'detail_url' => route('modules.main-meter.monitoring', [
                            'month' => $safeMonth,
                            'facility_id' => (int) ($alert->facility_id ?? 0),
                        ]),
                    ];
                });
        }

        $subRows = collect();
        if (in_array($selectedSource, ['all', 'sub'], true)) {
            $subQuery = SubmeterAlert::query()
                ->with([
                    'submeter:id,facility_id,submeter_name,status',
                    'submeter.facility:id,name',
                    'reading:id,submeter_id,period_type,period_start_date,period_end_date,kwh_used,approved_at',
                ])
                ->whereHas('submeter.facility')
                ->whereHas('reading', function (Builder $builder) use ($periodStart, $periodEnd) {
                    $builder->whereBetween('period_end_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
                });

            if ($selectedFacility) {
                $subQuery->whereHas('submeter', function (Builder $builder) use ($selectedFacility) {
                    $builder->where('facility_id', $selectedFacility);
                });
            }

            if ($selectedLevel !== '') {
                $subQuery->whereRaw('LOWER(alert_level) = ?', [$selectedLevel]);
            }

            if ($facilityScope !== null) {
                $subQuery->whereHas('submeter', function (Builder $builder) use ($facilityScope) {
                    $builder->whereIn('facility_id', $facilityScope);
                });
            }

            $subRows = $subQuery
                ->orderByDesc('created_at')
                ->get()
                ->map(function (SubmeterAlert $alert) {
                    $createdAt = $alert->created_at;
                    $facility = $alert->submeter?->facility;
                    return [
                        'source' => 'sub',
                        'source_label' => 'Submeter',
                        'facility_name' => (string) ($facility?->name ?? '-'),
                        'facility_type' => (string) ($facility?->type ?? ''),
                        'floor_area' => $this->resolveFloorArea($facility),
                        'meter_name' => (string) ($alert->submeter?->submeter_name ?? '-'),
                        'period_label' => $alert->reading?->periodLabel() ?? '-',
                        'current_kwh' => is_numeric($alert->current_value_kwh) ? (float) $alert->current_value_kwh : null,
                        'baseline_kwh' => is_numeric($alert->baseline_value_kwh) ? (float) $alert->baseline_value_kwh : null,
                        'increase_percent' => is_numeric($alert->increase_percent) ? (float) $alert->increase_percent : null,
                        'alert_level' => strtolower((string) ($alert->alert_level ?? 'warning')),
                        'reason' => (string) ($alert->reason ?? ''),
                        'created_at' => $createdAt,
                        'created_at_ts' => $createdAt ? $createdAt->getTimestamp() : 0,
                        'detail_url' => route('modules.submeters.show', (int) ($alert->submeter_id ?? 0)),
                    ];
                });
        }

        $rows = $mainRows
            ->concat($subRows)
            ->sortByDesc('created_at_ts')
            ->values();

        $summary = [
            'total' => (int) $rows->count(),
            'critical' => (int) $rows->where('alert_level', 'critical')->count(),
            'warning' => (int) $rows->where('alert_level', 'warning')->count(),
            'main' => (int) $rows->where('source', 'main')->count(),
            'sub' => (int) $rows->where('source', 'sub')->count(),
        ];

        $perPage = 20;
        $page = (int) ($request->query('page', 1));
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $perPage;
        $pageItems = $rows
            ->slice($offset, $perPage)
            ->values()
            ->map(fn (array $row) => $this->attachRecommendation($row));

        $alerts = new LengthAwarePaginator(
            $pageItems,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($query) => $query->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.submeters.ai-alerts', [
            'alerts' => $alerts,
            'summary' => $summary,
            'facilities' => $facilities,
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'selectedSource' => $selectedSource,
            'selectedLevel' => $selectedLevel,
        ]);
    }

    private function canView(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
    }

    private function staffFacilityIds(Request $request): ?array
    {
        $user = $request->user();
        if (! $user || ! RoleAccess::is($user, 'staff')) {
            return null;
        }

        return $user->facilities->pluck('id')->map(fn ($id) => (int) $id)->all();
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

    private function attachRecommendation(array $row): array
    {
        $context = [
            'facility_name' => (string) ($row['facility_name'] ?? ''),
            'facility_type' => (string) ($row['facility_type'] ?? ''),
            'alert_level' => (string) ($row['alert_level'] ?? 'warning'),
            'trend_percent' => $row['increase_percent'] ?? null,
            'actual_kwh' => $row['current_kwh'] ?? null,
            'baseline_kwh' => $row['baseline_kwh'] ?? null,
            'floor_area' => $row['floor_area'] ?? null,
            'last_maintenance' => '',
            'next_maintenance' => '',
        ];

        // Use rule-based recommendation for table listing to keep response fast and stable.
        $insight = $this->energyRecommendationService->generateFacilityInsight($context, false);

        $row['recommendation'] = trim((string) ($insight['recommendation'] ?? ''));
        $row['recommendation_source'] = (string) ($insight['source'] ?? 'rules');

        return $row;
    }

    private function resolveFloorArea(?Facility $facility): ?float
    {
        if (! $facility) {
            return null;
        }

        if (is_numeric($facility->floor_area_sqm ?? null)) {
            return (float) $facility->floor_area_sqm;
        }

        return is_numeric($facility->floor_area ?? null)
            ? (float) $facility->floor_area
            : null;
    }
}
