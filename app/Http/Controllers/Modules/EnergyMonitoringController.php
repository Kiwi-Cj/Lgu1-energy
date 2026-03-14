<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use App\Models\Setting;
use App\Services\EnergyRecommendationService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class EnergyMonitoringController extends Controller
{
    private ?array $alertThresholdsBySize = null;
    private ?array $trendPercentThresholdsBySize = null;

    public function __construct(
        private readonly EnergyRecommendationService $energyRecommendationService
    ) {
    }

    /**
     * Display the Energy Monitoring Dashboard with dynamic total facilities card and facility table.
     */
    public function index()
    {
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $search = trim((string) request('search', ''));

        if ($role === 'staff') {
            $facilityQuery = $user->facilities();
        } else {
            $facilityQuery = Facility::query();
        }

        if ($search !== '') {
            $facilityQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%");
            });
        }

        $facilities = $facilityQuery->get();
        $totalFacilities = $facilities->count();
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $facilityIds = $facilities->pluck('id')->all();

        $totalEnergyCost = EnergyRecord::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'main');
            })
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
            $lastMaintenance = $lastMaintenanceByFacility->get($facility->id);
            $nextMaintenance = $nextMaintenanceByFacility->get($facility->id);

            $facility->trend_percent = $trendPercent;
            $facility->trend_analysis = $trendDisplay;
            $facility->alert_level = $alertLevel;
            $recommendationContext = [
                'facility_name' => (string) ($facility->name ?? ''),
                'facility_type' => (string) ($facility->type ?? ''),
                'alert_level' => $alertLevel,
                'trend_percent' => $trendPercent,
                'actual_kwh' => $currentMonthRecord?->actual_kwh,
                'baseline_kwh' => $currentMonthRecord?->baseline_kwh,
                'floor_area' => $facility->floor_area,
                'last_maintenance' => $lastMaintenance?->completed_date,
                'next_maintenance' => $nextMaintenance?->scheduled_date,
            ];
            // Keep first render fast: use rules-based text on table load.
            $facility->trend_recommendation = $this->energyRecommendationService
                ->generateFacilityRecommendation($recommendationContext, false);

            if ($currentMonthRecord) {
                $currentMonthRecord->last_maintenance = $lastMaintenance?->completed_date;
                $currentMonthRecord->next_maintenance = $nextMaintenance?->scheduled_date;
            }

            if ($currentMonthRecord && in_array($alertLevel, ['High', 'Very High', 'Critical'], true)) {
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

    public function aiRecommendation(Facility $facility): JsonResponse
    {
        $user = auth()->user();
        $role = RoleAccess::normalize($user);

        if ($role === 'staff') {
            $hasAccess = $user
                && $user->facilities()
                    ->where('facilities.id', $facility->id)
                    ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'message' => 'You do not have access to this facility.',
                ], 403);
            }
        }

        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $facilityIds = [(int) $facility->id];

        $recordsByFacility = $this->loadRecentRecordsByFacility($facilityIds, $currentYear, $currentMonth);
        $lastMaintenanceByFacility = $this->loadLastMaintenanceByFacility($facilityIds);
        $nextMaintenanceByFacility = $this->loadNextMaintenanceByFacility($facilityIds);

        $facilityRecords = $recordsByFacility->get($facility->id, collect());
        $currentMonthRecord = $facilityRecords->first(function ($record) use ($currentYear, $currentMonth) {
            return (int) $record->year === $currentYear && (int) $record->month === $currentMonth;
        });

        [$trendPercent, $trendDisplay] = $this->calculateTrendPercent($facilityRecords, $currentYear, $currentMonth);
        $alertLevel = $this->resolveAlertLevel($facility, $currentMonthRecord, $trendPercent);
        $lastMaintenance = $lastMaintenanceByFacility->get($facility->id);
        $nextMaintenance = $nextMaintenanceByFacility->get($facility->id);

        $insight = $this->energyRecommendationService->generateFacilityInsight([
            'facility_name' => (string) ($facility->name ?? ''),
            'facility_type' => (string) ($facility->type ?? ''),
            'alert_level' => $alertLevel,
            'trend_percent' => $trendPercent,
            'actual_kwh' => $currentMonthRecord?->actual_kwh,
            'baseline_kwh' => $currentMonthRecord?->baseline_kwh,
            'floor_area' => $facility->floor_area,
            'last_maintenance' => $lastMaintenance?->completed_date,
            'next_maintenance' => $nextMaintenance?->scheduled_date,
        ], true);
        $resolvedAlertLevel = (string) ($insight['alert_level'] ?? $alertLevel);
        $resolvedRecommendation = (string) ($insight['recommendation'] ?? '');

        return response()->json([
            'facility_id' => (int) $facility->id,
            'facility_name' => (string) ($facility->name ?? ''),
            'alert_level' => $resolvedAlertLevel,
            'trend_analysis' => $trendDisplay,
            'recommendation' => $resolvedRecommendation,
            'recommendation_source' => (string) ($insight['source'] ?? 'rules'),
        ]);
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
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'main');
            })
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
        $currentKey = $anchor->format('Y-m');
        $currentKwh = (float) ($monthTotals->get($currentKey) ?? 0);

        $previousMonths = [];
        for ($i = 1; $i <= 3; $i++) {
            $key = $anchor->copy()->subMonths($i)->format('Y-m');
            if ($monthTotals->has($key)) {
                $value = (float) $monthTotals->get($key);
                if ($value > 0) {
                    $previousMonths[] = $value;
                }
            }
        }

        if (count($previousMonths) >= 3) {
            $referenceKwh = array_sum($previousMonths) / 3;
        } elseif (count($previousMonths) >= 1) {
            // Fallback while history is still building up.
            $referenceKwh = (float) $previousMonths[0];
        } else {
            return [null, '-'];
        }

        if ($referenceKwh <= 0) {
            return [null, '-'];
        }

        $trendPercent = (($currentKwh - $referenceKwh) / $referenceKwh) * 100;
        $trendDisplay = ($trendPercent >= 0 ? '+' : '') . number_format($trendPercent, 2) . '%';

        return [$trendPercent, $trendDisplay];
    }

    private function resolveAlertLevel(Facility $facility, $record, ?float $trendPercent): string
    {
        if (! $record || $trendPercent === null) {
            return 'No Data';
        }

        $size = strtolower((string) ($facility->size_label ?? $this->inferFacilitySize($facility, $record)));
        $thresholds = $this->resolveThresholdsForSize($size);
        $trendTrigger = $this->resolveTrendPercentTriggerForSize($size);

        if ($trendPercent > $thresholds['level5']) return 'Critical';
        if ($trendPercent > $thresholds['level4']) return 'Very High';
        if ($trendPercent > $thresholds['level3']) return 'High';
        if ($trendPercent > $trendTrigger) return 'Warning';

        return 'Normal';
    }

    private function inferFacilitySize(Facility $facility, $record): string
    {
        $baseline = (float) ($record->baseline_kwh ?? $facility->baseline_kwh ?? 0);
        return Facility::resolveSizeLabelFromBaseline($baseline) ?? 'Small';
    }

    private function resolveThresholdsForSize(string $sizeLabel): array
    {
        $sizeKey = match (strtolower(str_replace('_', '-', trim($sizeLabel)))) {
            'small' => 'small',
            'small-medium', 'small medium' => 'small', // legacy label fallback
            'medium' => 'medium',
            'large' => 'large',
            'extra-large', 'extra large', 'xlarge' => 'xlarge',
            default => 'small',
        };

        $all = $this->getAlertThresholdsBySize();

        return $all[$sizeKey] ?? $all['small'];
    }

    private function resolveTrendPercentTriggerForSize(string $sizeLabel): float
    {
        $sizeKey = match (strtolower(str_replace('_', '-', trim($sizeLabel)))) {
            'small' => 'small',
            'small-medium', 'small medium' => 'small', // legacy label fallback
            'medium' => 'medium',
            'large' => 'large',
            'extra-large', 'extra large', 'xlarge' => 'xlarge',
            default => 'small',
        };

        $all = $this->getTrendPercentThresholdsBySize();

        return (float) ($all[$sizeKey] ?? $all['small'] ?? 0);
    }

    private function getAlertThresholdsBySize(): array
    {
        if ($this->alertThresholdsBySize !== null) {
            return $this->alertThresholdsBySize;
        }

        $defaults = [
            'small' => ['level1' => 5, 'level2' => 10, 'level3' => 15, 'level4' => 25, 'level5' => 35],
            'medium' => ['level1' => 4, 'level2' => 8, 'level3' => 12, 'level4' => 20, 'level5' => 30],
            'large' => ['level1' => 3, 'level2' => 6, 'level3' => 10, 'level4' => 16, 'level5' => 24],
            'xlarge' => ['level1' => 2, 'level2' => 4, 'level3' => 7, 'level4' => 12, 'level5' => 18],
        ];

        $keys = [];
        foreach (array_keys($defaults) as $sizeKey) {
            for ($lvl = 1; $lvl <= 5; $lvl++) {
                $keys[] = "alert_level{$lvl}_{$sizeKey}";
            }
        }

        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');
        $resolved = [];

        foreach ($defaults as $sizeKey => $levels) {
            $resolved[$sizeKey] = [];
            foreach ($levels as $levelKey => $defaultValue) {
                $settingKey = "alert_{$levelKey}_{$sizeKey}";
                $raw = $settings[$settingKey] ?? $defaultValue;
                $resolved[$sizeKey][$levelKey] = is_numeric($raw) ? (float) $raw : (float) $defaultValue;
            }
        }

        return $this->alertThresholdsBySize = $resolved;
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
