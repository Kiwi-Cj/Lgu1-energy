<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EnergyMonitoringController extends Controller
{
    /**
     * Display the Energy Monitoring Dashboard with dynamic total facilities card and facility table.
     */
    public function index()
    {
        $user = auth()->user();
        $role = strtolower((string) ($user?->role ?? ''));

        if ($role === 'staff') {
            $facilities = $user->facilities()->get();
        } else {
            $facilities = Facility::query()->get();
        }

        $totalFacilities = $facilities->count();
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $facilityIds = $facilities->pluck('id')->all();

        $totalEnergyCost = EnergyRecord::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->when(!empty($facilityIds), fn ($q) => $q->whereIn('facility_id', $facilityIds))
            ->sum('energy_cost');

        $recordsByFacility = $this->loadRecentRecordsByFacility($facilityIds, $currentYear, $currentMonth);
        $lastMaintenanceByFacility = $this->loadLastMaintenanceByFacility($facilityIds);
        $nextMaintenanceByFacility = $this->loadNextMaintenanceByFacility($facilityIds);

        $highAlertCount = 0;
        foreach ($facilities as $facility) {
            $facilityRecords = $recordsByFacility->get($facility->id, collect());
            $currentMonthRecord = $facilityRecords->first(function ($record) use ($currentYear, $currentMonth) {
                return (int) $record->year === $currentYear && (int) $record->month === $currentMonth;
            });

            $facility->currentMonthRecord = $currentMonthRecord;
            [$trendPercent, $trendDisplay] = $this->calculateTrendPercent($facilityRecords, $currentYear, $currentMonth);
            $alertLevel = $this->resolveAlertLevel($facility, $currentMonthRecord, $trendPercent);

            $facility->trend_percent = $trendPercent;
            $facility->trend_analysis = $trendDisplay;
            $facility->alert_level = $alertLevel;
            $facility->trend_recommendation = $this->resolveTrendRecommendation($alertLevel);

            if ($currentMonthRecord) {
                $lastMaintenance = $lastMaintenanceByFacility->get($facility->id);
                $nextMaintenance = $nextMaintenanceByFacility->get($facility->id);
                $currentMonthRecord->last_maintenance = $lastMaintenance?->completed_date;
                $currentMonthRecord->next_maintenance = $nextMaintenance?->scheduled_date;
            }

            if ($currentMonthRecord && in_array($alertLevel, ['High', 'Critical'], true)) {
                $highAlertCount++;
            }
        }

        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;

        return view('modules.energy-monitoring.index', compact(
            'totalFacilities',
            'facilities',
            'highAlertCount',
            'totalEnergyCost',
            'notifications',
            'unreadNotifCount'
        ) + ['role' => $role, 'user' => $user]);
    }

    private function loadRecentRecordsByFacility(array $facilityIds, int $currentYear, int $currentMonth): Collection
    {
        if (empty($facilityIds)) {
            return collect();
        }

        $currentYm = $currentYear * 100 + $currentMonth;
        $startYm = (int) Carbon::create($currentYear, $currentMonth, 1)->subMonths(5)->format('Ym');

        return EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$startYm, $currentYm])
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->groupBy('facility_id')
            ->map(fn (Collection $rows) => $rows->values());
    }

    private function loadLastMaintenanceByFacility(array $facilityIds): Collection
    {
        if (empty($facilityIds)) {
            return collect();
        }

        return MaintenanceHistory::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereNotNull('completed_date')
            ->orderByDesc('completed_date')
            ->get()
            ->groupBy('facility_id')
            ->map(fn (Collection $rows) => $rows->first());
    }

    private function loadNextMaintenanceByFacility(array $facilityIds): Collection
    {
        if (empty($facilityIds)) {
            return collect();
        }

        return Maintenance::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('maintenance_status', 'Ongoing')
            ->orderBy('scheduled_date')
            ->get()
            ->groupBy('facility_id')
            ->map(fn (Collection $rows) => $rows->first());
    }

    private function calculateTrendPercent(Collection $records, int $currentYear, int $currentMonth): array
    {
        if ($records->isEmpty()) {
            return [null, '-'];
        }

        $monthTotals = $records
            ->groupBy(fn ($row) => sprintf('%04d-%02d', (int) $row->year, (int) $row->month))
            ->map(fn (Collection $group) => (float) $group->sum('actual_kwh'));

        $anchor = Carbon::create($currentYear, $currentMonth, 1);
        $currentKwh = 0.0;
        $previousKwh = 0.0;

        for ($i = 2; $i >= 0; $i--) {
            $key = $anchor->copy()->subMonths($i)->format('Y-m');
            $currentKwh += (float) ($monthTotals->get($key) ?? 0);
        }

        for ($i = 5; $i >= 3; $i--) {
            $key = $anchor->copy()->subMonths($i)->format('Y-m');
            $previousKwh += (float) ($monthTotals->get($key) ?? 0);
        }

        if ($previousKwh <= 0) {
            return [null, '-'];
        }

        $trendPercent = (($currentKwh - $previousKwh) / $previousKwh) * 100;
        $trendDisplay = ($trendPercent >= 0 ? '+' : '') . number_format($trendPercent, 2) . '%';

        return [$trendPercent, $trendDisplay];
    }

    private function resolveAlertLevel(Facility $facility, $record, ?float $trendPercent): string
    {
        if ($trendPercent === null) {
            return 'Normal';
        }

        $size = strtolower((string) ($facility->size_label ?? $this->inferFacilitySize($facility, $record)));

        if ($size === 'small') {
            if ($trendPercent > 40) return 'Critical';
            if ($trendPercent > 30) return 'High';
            if ($trendPercent > 20) return 'Moderate';
            if ($trendPercent > 10) return 'Low';
            return 'Normal';
        }

        if ($size === 'medium') {
            if ($trendPercent > 30) return 'Critical';
            if ($trendPercent > 20) return 'High';
            if ($trendPercent > 15) return 'Moderate';
            if ($trendPercent > 7) return 'Low';
            return 'Normal';
        }

        if (in_array($size, ['extra large', 'extra_large'], true)) {
            if ($trendPercent > 15) return 'Critical';
            if ($trendPercent > 10) return 'High';
            if ($trendPercent > 6) return 'Moderate';
            if ($trendPercent > 2) return 'Low';
            return 'Normal';
        }

        if ($trendPercent > 20) return 'Critical';
        if ($trendPercent > 12) return 'High';
        if ($trendPercent > 8) return 'Moderate';
        if ($trendPercent > 4) return 'Low';

        return 'Normal';
    }

    private function inferFacilitySize(Facility $facility, $record): string
    {
        $baseline = (float) ($record->baseline_kwh ?? $facility->baseline_kwh ?? 0);

        if ($baseline <= 1000) return 'Small';
        if ($baseline <= 3000) return 'Medium';
        if ($baseline <= 10000) return 'Large';

        return 'Extra Large';
    }

    private function resolveTrendRecommendation(string $alertLevel): string
    {
        $recommendations = [
            'Critical' => 'Immediate action required! Trend shows a significant increase in energy use. Investigate and resolve excessive consumption.',
            'High' => 'High upward trend detected. Review operations and address high energy consumption.',
            'Moderate' => 'Moderate increase in trend. Monitor closely and plan for efficiency improvements.',
            'Low' => 'Slight upward trend. Consider energy efficiency improvements.',
            'Normal' => 'Stable trend. No immediate action required.',
        ];

        return $recommendations[$alertLevel] ?? 'No recommendation';
    }
}
