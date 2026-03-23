<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use App\Models\Setting;
use App\Services\EnergyRecommendationService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

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
        $mainMetersByFacility = $this->loadMainMetersByFacility($facilityIds);
        $mainMeterSnapshotsByFacility = $this->loadCurrentMonthMainMeterSnapshots($facilityIds, $currentYear, $currentMonth);
        $lastMaintenanceByFacility = $this->loadLastMaintenanceByFacility($facilityIds);
        $nextMaintenanceByFacility = $this->loadNextMaintenanceByFacility($facilityIds);

        $highAlertCount = 0;
        foreach ($facilities as $facility) {
            $facilityRecords = $recordsByFacility->get($facility->id, collect());
            $mainMeters = $this->attachCurrentMonthSnapshotsToMainMeters(
                $mainMetersByFacility->get($facility->id, collect()),
                $mainMeterSnapshotsByFacility->get($facility->id, collect())
            );
            $currentMonthRecord = $facilityRecords->first(function ($record) use ($currentYear, $currentMonth) {
                return (int) $record->year === $currentYear && (int) $record->month === $currentMonth;
            });

            $facility->main_meters = $mainMeters;
            $facility->main_meter_name = $this->resolveMainMeterSummaryLabel($mainMeters);
            $facility->main_meter_status_label = $this->resolveMainMeterStatusLabel($mainMeters);
            $facility->main_meter_meta_label = $this->resolveMainMeterMetaLabel($mainMeters);
            $facility->main_meter_alert_summary_label = $this->resolveMainMeterAlertSummaryLabel($mainMeters);
            $facility->currentMonthRecord = $currentMonthRecord;
            [$trendPercent, $trendDisplay] = $this->calculateTrendPercent($facilityRecords, $currentYear, $currentMonth);
            $trendAlertLevel = $this->resolveAlertLevel($facility, $currentMonthRecord, $trendPercent);
            $alertLevel = $this->resolveFacilityAlertLevel($mainMeters, $trendAlertLevel);
            $lastMaintenance = $lastMaintenanceByFacility->get($facility->id);
            $nextMaintenance = $nextMaintenanceByFacility->get($facility->id);

            $facility->trend_percent = $trendPercent;
            $facility->trend_analysis = $trendDisplay;
            $facility->trend_alert_level = $trendAlertLevel;
            $facility->alert_level = $alertLevel;
            $facility->facility_status_label = $this->resolveFacilityOperationalStatusLabel($facility);
            $facility->maintenance_status_label = $this->resolveMaintenanceStatusLabel($nextMaintenance, $lastMaintenance);
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

        return view('modules.energy-monitoring.index', compact(
            'totalFacilities',
            'facilities',
            'highAlertCount',
            'totalEnergyCost'
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
        $mainMetersByFacility = $this->loadMainMetersByFacility($facilityIds);
        $mainMeterSnapshotsByFacility = $this->loadCurrentMonthMainMeterSnapshots($facilityIds, $currentYear, $currentMonth);
        $lastMaintenanceByFacility = $this->loadLastMaintenanceByFacility($facilityIds);
        $nextMaintenanceByFacility = $this->loadNextMaintenanceByFacility($facilityIds);

        $facilityRecords = $recordsByFacility->get($facility->id, collect());
        $mainMeters = $this->attachCurrentMonthSnapshotsToMainMeters(
            $mainMetersByFacility->get($facility->id, collect()),
            $mainMeterSnapshotsByFacility->get($facility->id, collect())
        );
        $currentMonthRecord = $facilityRecords->first(function ($record) use ($currentYear, $currentMonth) {
            return (int) $record->year === $currentYear && (int) $record->month === $currentMonth;
        });

        [$trendPercent, $trendDisplay] = $this->calculateTrendPercent($facilityRecords, $currentYear, $currentMonth);
        $trendAlertLevel = $this->resolveAlertLevel($facility, $currentMonthRecord, $trendPercent);
        $alertLevel = $this->resolveFacilityAlertLevel($mainMeters, $trendAlertLevel);
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

    private function loadMainMetersByFacility(array $facilityIds): Collection
    {
        if (empty($facilityIds)) {
            return collect();
        }

        return FacilityMeter::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('meter_type', 'main')
            ->orderByRaw("CASE WHEN approved_at IS NOT NULL THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) = 'active' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->get(['id', 'facility_id', 'meter_name', 'meter_number', 'status', 'approved_at'])
            ->groupBy('facility_id')
            ->map(fn (Collection $rows) => $rows->values());
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
            ->map(fn (Collection $rows) => $this->aggregateFacilityRecordsByMonth($rows));
    }

    private function loadCurrentMonthMainMeterSnapshots(array $facilityIds, int $currentYear, int $currentMonth): Collection
    {
        if (empty($facilityIds)) {
            return collect();
        }

        return EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->whereNotNull('meter_id')
            ->whereHas('meter', function ($meterQuery) {
                $meterQuery->where('meter_type', 'main');
            })
            ->get(['facility_id', 'meter_id', 'actual_kwh', 'baseline_kwh', 'energy_cost'])
            ->groupBy('facility_id')
            ->map(function (Collection $rows) {
                return $rows
                    ->groupBy('meter_id')
                    ->map(function (Collection $meterRows) {
                        $baselineKwh = (float) $meterRows->sum(function ($row) {
                            return is_numeric($row->baseline_kwh ?? null) ? (float) $row->baseline_kwh : 0.0;
                        });
                        $energyCost = (float) $meterRows->sum(function ($row) {
                            return is_numeric($row->energy_cost ?? null) ? (float) $row->energy_cost : 0.0;
                        });

                        return collect([
                            'actual_kwh' => (float) $meterRows->sum(function ($row) {
                                return is_numeric($row->actual_kwh ?? null) ? (float) $row->actual_kwh : 0.0;
                            }),
                            'baseline_kwh' => $baselineKwh > 0 ? $baselineKwh : null,
                            'energy_cost' => $energyCost > 0 ? $energyCost : null,
                        ]);
                    });
            });
    }

    private function attachCurrentMonthSnapshotsToMainMeters(Collection $mainMeters, Collection $meterSnapshots): Collection
    {
        return $mainMeters
            ->map(function (FacilityMeter $meter) use ($meterSnapshots) {
                $snapshot = $meterSnapshots->get($meter->id, collect());
                $meter->current_month_kwh = is_numeric($snapshot->get('actual_kwh')) ? (float) $snapshot->get('actual_kwh') : null;
                $meter->current_month_baseline_kwh = is_numeric($snapshot->get('baseline_kwh')) ? (float) $snapshot->get('baseline_kwh') : null;
                $meter->current_month_energy_cost = is_numeric($snapshot->get('energy_cost')) ? (float) $snapshot->get('energy_cost') : null;
                $meter->current_month_alert_level = $this->resolveCurrentMonthMainMeterAlertLevel(
                    $meter->current_month_kwh,
                    $meter->current_month_baseline_kwh
                );

                return $meter;
            })
            ->values();
    }

    private function resolveCurrentMonthMainMeterAlertLevel(?float $actualKwh, ?float $baselineKwh): string
    {
        if (! is_numeric($actualKwh) || ! is_numeric($baselineKwh) || (float) $baselineKwh <= 0) {
            return 'No Data';
        }

        $deviation = EnergyRecord::calculateDeviation((float) $actualKwh, (float) $baselineKwh);
        if ($deviation === null) {
            return 'No Data';
        }

        $resolved = EnergyRecord::resolveAlertLevel($deviation, (float) $baselineKwh);

        return $resolved !== '' ? $resolved : 'No Data';
    }

    private function resolveFacilityAlertLevel(Collection $mainMeters, string $fallbackAlertLevel): string
    {
        $highestLabel = null;
        $highestRank = -1;

        foreach ($mainMeters as $meter) {
            $label = (string) ($meter->current_month_alert_level ?? 'No Data');
            $rank = $this->resolveAlertLevelRank($label);

            if ($rank > $highestRank) {
                $highestRank = $rank;
                $highestLabel = $label;
            }
        }

        if ($highestLabel !== null && $highestRank > 0) {
            return $highestLabel;
        }

        return $fallbackAlertLevel;
    }

    private function resolveMainMeterAlertSummaryLabel(Collection $mainMeters): string
    {
        if ($mainMeters->count() <= 1) {
            return '';
        }

        $counts = $mainMeters
            ->map(function (FacilityMeter $meter) {
                $label = trim((string) ($meter->current_month_alert_level ?? 'No Data'));

                return $label !== '' ? $label : 'No Data';
            })
            ->countBy();

        if ($counts->isEmpty()) {
            return '';
        }

        $orderedLabels = collect(array_keys($this->alertLevelRanks()))
            ->filter(fn (string $label) => $counts->has($label))
            ->values();

        if ($orderedLabels->count() === 1) {
            $label = (string) $orderedLabels->first();

            return 'Alert Summary: All ' . $label;
        }

        $parts = $orderedLabels
            ->map(fn (string $label) => $counts->get($label) . ' ' . $label)
            ->implode(', ');

        return 'Mixed Alert: ' . $parts;
    }

    private function loadLastMaintenanceByFacility(array $facilityIds): Collection
    {
        if (empty($facilityIds)) {
            return collect();
        }

        if (! Schema::hasTable('maintenance_history')) {
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

        if (! Schema::hasTable('maintenance')) {
            return collect();
        }

        $orderColumn = Schema::hasColumn('maintenance', 'scheduled_date')
            ? 'scheduled_date'
            : (Schema::hasColumn('maintenance', 'created_at') ? 'created_at' : 'id');

        return Maintenance::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('maintenance_status', 'Ongoing')
            ->orderBy($orderColumn)
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

    private function aggregateFacilityRecordsByMonth(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn ($row) => sprintf('%04d-%02d', (int) $row->year, (int) $row->month))
            ->map(function (Collection $group) {
                $first = $group->first();
                $actualKwh = (float) $group->sum(fn ($row) => (float) ($row->actual_kwh ?? 0));
                $baselineKwh = (float) $group->sum(function ($row) {
                    return is_numeric($row->baseline_kwh ?? null) ? (float) $row->baseline_kwh : 0.0;
                });
                $energyCost = (float) $group->sum(function ($row) {
                    return is_numeric($row->energy_cost ?? null) ? (float) $row->energy_cost : 0.0;
                });

                return (object) [
                    'year' => (int) ($first->year ?? 0),
                    'month' => (int) ($first->month ?? 0),
                    'actual_kwh' => $actualKwh,
                    'baseline_kwh' => $baselineKwh > 0 ? $baselineKwh : null,
                    'energy_cost' => $energyCost > 0 ? $energyCost : null,
                    'meter_count' => (int) $group->count(),
                ];
            })
            ->sortBy(fn ($row) => ((int) $row->year * 100) + (int) $row->month)
            ->values();
    }

    private function resolveMainMeterSummaryLabel(Collection $mainMeters): string
    {
        $count = $mainMeters->count();

        if ($count === 0) {
            return 'No Main Meter';
        }

        if ($count === 1) {
            return (string) ($mainMeters->first()->meter_name ?? 'Main Meter');
        }

        return $count . ' Main Meters';
    }

    private function resolveMainMeterMetaLabel(Collection $mainMeters): string
    {
        $count = $mainMeters->count();

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return trim((string) ($mainMeters->first()->meter_number ?? ''));
        }

        $activeCount = $mainMeters->filter(function (FacilityMeter $meter) {
            return strtolower(trim((string) ($meter->status ?? ''))) === 'active';
        })->count();
        $approvedCount = $mainMeters->filter(fn (FacilityMeter $meter) => ! is_null($meter->approved_at))->count();

        return sprintf('%d active, %d approved', $activeCount, $approvedCount);
    }

    private function resolveMainMeterStatusLabel(Collection $mainMeters): string
    {
        if ($mainMeters->isEmpty()) {
            return 'No Main Meter';
        }

        $approvedCount = $mainMeters->filter(fn (FacilityMeter $meter) => ! is_null($meter->approved_at))->count();
        if ($approvedCount === 0) {
            return 'Pending Approval';
        }

        $statusCounts = $mainMeters
            ->map(function (FacilityMeter $meter) {
                return strtolower(trim((string) ($meter->status ?? 'unknown')));
            })
            ->countBy();

        if ($mainMeters->count() === 1) {
            $status = (string) $statusCounts->keys()->first();

            return match ($status) {
                'active' => 'Active',
                'inactive' => 'Inactive',
                'maintenance' => 'Maintenance',
                default => $status !== '' ? ucfirst($status) : 'Unknown',
            };
        }

        if ($statusCounts->count() > 1) {
            return 'Mixed Status';
        }

        $status = (string) $statusCounts->keys()->first();

        return match ($status) {
            'active' => 'All Active',
            'inactive' => 'All Inactive',
            'maintenance' => 'All Maintenance',
            default => 'Mixed Status',
        };
    }

    private function resolveFacilityOperationalStatusLabel(Facility $facility): string
    {
        $status = strtolower(trim((string) ($facility->status ?? '')));

        return match ($status) {
            'active' => 'Facility Active',
            'inactive' => 'Facility Inactive',
            'maintenance' => 'Facility Maintenance',
            default => $status !== '' ? 'Facility ' . ucfirst($status) : 'Facility Unknown',
        };
    }

    private function resolveMaintenanceStatusLabel($nextMaintenance, $lastMaintenance): string
    {
        if ($nextMaintenance) {
            return 'Maintenance Ongoing';
        }

        if ($lastMaintenance) {
            return 'Maintenance Logged';
        }

        return 'No Maintenance Log';
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

    private function alertLevelRanks(): array
    {
        return [
            'Critical' => 5,
            'Very High' => 4,
            'High' => 3,
            'Warning' => 2,
            'Normal' => 1,
            'No Data' => 0,
        ];
    }

    private function resolveAlertLevelRank(string $label): int
    {
        return $this->alertLevelRanks()[$label] ?? 0;
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

        $settings = Setting::getMany($keys);
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
