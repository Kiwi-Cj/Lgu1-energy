<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\MainMeterReading;
use App\Models\Submeter;
use App\Models\SubmeterEquipment;
use App\Models\SubmeterReading;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LoadTrackingService
{
    public const VARIANCE_WARNING_PERCENT = 10.0;
    public const VARIANCE_FLAG_PERCENT = 20.0;

    /**
     * @return array{
     *   rows: Collection<int, array<string, mixed>>,
     *   top_equipment: Collection<int, SubmeterEquipment>,
     *   pie_labels: array<int, string>,
     *   pie_values: array<int, float>,
     *   comparison_labels: array<int, string>,
     *   comparison_estimated: array<int, float>,
     *   comparison_actual: array<int, float>,
     *   totals: array{
     *     estimated_kwh: float,
     *     actual_kwh: float,
     *     variance_percent: float|null,
     *     flagged_submeters: int
     *   }
     * }
     */
    public function buildMonthlySnapshot(
        int $year,
        int $month,
        ?int $facilityId = null,
        ?int $submeterId = null,
        ?array $facilityScope = null,
        ?string $meterScope = null,
        ?int $mainMeterId = null
    ): array {
        $meterScope = in_array($meterScope, ['sub', 'main'], true) ? $meterScope : null;
        if ($meterScope === 'sub') {
            $mainMeterId = null;
        } elseif ($meterScope === 'main') {
            $submeterId = null;
        }

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $submeters = collect();
        if ($meterScope !== 'main') {
            $submeterQuery = Submeter::query()
                ->with([
                    'facility' => fn ($query) => $query->select('id', 'name'),
                ])
                ->whereHas('facility')
                ->where('status', 'active');

            if ($facilityId) {
                $submeterQuery->where('facility_id', $facilityId);
            }

            if ($submeterId) {
                $submeterQuery->where('id', $submeterId);
            }

            if ($facilityScope !== null) {
                $submeterQuery->whereIn('facility_id', $facilityScope);
            }

            $submeters = $submeterQuery
                ->orderBy('submeter_name')
                ->get(['id', 'facility_id', 'submeter_name', 'meter_type', 'status']);

            if ($mainMeterId && ! $submeterId) {
                $linkedSubmeterKeys = $this->linkedSubmeterKeysForMainMeter($mainMeterId);
                $submeters = $submeters
                    ->filter(function (Submeter $submeter) use ($linkedSubmeterKeys) {
                        return $linkedSubmeterKeys->contains($this->meterNameKey(
                            (int) $submeter->facility_id,
                            (string) $submeter->submeter_name
                        ));
                    })
                    ->values();
            }
        }

        $mainMeters = collect();
        if ($meterScope !== 'sub') {
            $mainMeterQuery = FacilityMeter::query()
                ->with([
                    'facility' => fn ($query) => $query->select('id', 'name'),
                ])
                ->whereHas('facility')
                ->where('meter_type', 'main')
                ->where('status', 'active')
                ->whereNotNull('approved_at');

            if ($facilityId) {
                $mainMeterQuery->where('facility_id', $facilityId);
            }

            if ($mainMeterId) {
                $mainMeterQuery->where('id', $mainMeterId);
            }

            if ($facilityScope !== null) {
                $mainMeterQuery->whereIn('facility_id', $facilityScope);
            }

            $mainMeters = $mainMeterQuery
                ->orderBy('meter_name')
                ->get(['id', 'facility_id', 'meter_name', 'meter_number', 'status']);
        }

        if ($submeters->isEmpty() && $mainMeters->isEmpty()) {
            return [
                'rows' => collect(),
                'top_equipment' => collect(),
                'pie_labels' => [],
                'pie_values' => [],
                'comparison_labels' => [],
                'comparison_estimated' => [],
                'comparison_actual' => [],
                'totals' => [
                    'estimated_kwh' => 0.0,
                    'actual_kwh' => 0.0,
                    'variance_percent' => null,
                    'flagged_submeters' => 0,
                ],
            ];
        }

        $submeterIds = $submeters->pluck('id')->all();
        $mainMeterIds = $mainMeters->pluck('id')->all();
        $submeterParentMap = $this->submeterParentMap($submeters);

        $equipmentStatsRows = SubmeterEquipment::query()
            ->select([
                'meter_scope',
                'submeter_id',
                'facility_meter_id',
                'quantity',
                'rated_watts',
                'estimated_kwh',
            ])
            ->where(function ($builder) use ($submeterIds, $mainMeterIds) {
                $hasCondition = false;

                if (! empty($submeterIds)) {
                    $builder->where(function ($subQuery) use ($submeterIds) {
                        $subQuery->where('meter_scope', 'sub')
                            ->whereIn('submeter_id', $submeterIds);
                    });
                    $hasCondition = true;
                }

                if (! empty($mainMeterIds)) {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $builder->{$method}(function ($mainQuery) use ($mainMeterIds) {
                        $mainQuery->where('meter_scope', 'main')
                            ->whereIn('facility_meter_id', $mainMeterIds);
                    });
                }
            })
            ->get();

        $estimatedByMeter = $equipmentStatsRows
            ->groupBy(function ($row) {
                $scope = strtolower((string) ($row->meter_scope ?? 'sub'));
                $meterId = $scope === 'main'
                    ? (int) ($row->facility_meter_id ?? 0)
                    : (int) ($row->submeter_id ?? 0);

                return $scope . ':' . $meterId;
            })
            ->map(function (Collection $group) {
                return (object) [
                    'total_estimated_kwh' => round((float) $group->sum(fn ($row) => (float) ($row->estimated_kwh ?? 0)), 2),
                    'total_watts' => round((float) $group->sum(function ($row) {
                        return (float) ($row->rated_watts ?? 0) * (int) ($row->quantity ?? 0);
                    }), 2),
                    'total_equipment' => $group->count(),
                ];
            });

        $actualBySubmeter = collect();
        if (! empty($submeterIds)) {
            $actualBySubmeter = SubmeterReading::query()
                ->approved()
                ->where('period_type', 'monthly')
                ->whereIn('submeter_id', $submeterIds)
                ->whereBetween('period_end_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->select([
                    'submeter_id',
                    DB::raw('SUM(kwh_used) as total_actual_kwh'),
                ])
                ->groupBy('submeter_id')
                ->get()
                ->keyBy('submeter_id');
        }

        $manualActualByMainMeter = collect();
        if (! empty($mainMeterIds)) {
            $manualActualByMainMeter = EnergyRecord::query()
                ->whereIn('meter_id', $mainMeterIds)
                ->where('year', $year)
                ->where('month', $month)
                ->select([
                    'meter_id',
                    DB::raw('SUM(actual_kwh) as total_actual_kwh'),
                ])
                ->groupBy('meter_id')
                ->get()
                ->keyBy('meter_id');
        }

        $mainMeterIdsCollection = collect($mainMeterIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();
        $fallbackSensorMainMeterId = (int) ($mainMeterIdsCollection->first() ?? 0);
        $resolveSensorMainMeterId = static function ($reading) use ($mainMeterIdsCollection, $fallbackSensorMainMeterId): int {
            $deviceId = (string) ($reading->device_id ?? '');
            if (preg_match('/FAKE-MAIN-(\d+)/', $deviceId, $matches)) {
                $meterId = (int) ltrim($matches[1], '0');
                if ($meterId > 0 && $mainMeterIdsCollection->contains($meterId)) {
                    return $meterId;
                }
            }

            return $fallbackSensorMainMeterId;
        };

        $sensorMainRows = collect();
        if ($mainMeterIdsCollection->isNotEmpty()) {
            $sensorMainRows = MainMeterReading::query()
                ->approved()
                ->where('period_type', 'monthly')
                ->where('input_source', 'iot')
                ->whereYear('period_end_date', $year)
                ->whereMonth('period_end_date', $month)
                ->get(['id', 'device_id', 'period_end_date', 'kwh_used']);
        }

        $sensorActualByMainMeter = $sensorMainRows
            ->groupBy(fn ($reading) => $resolveSensorMainMeterId($reading))
            ->map(fn ($group) => (object) [
                'total_actual_kwh' => round((float) $group->sum(fn ($reading) => (float) ($reading->kwh_used ?? 0)), 2),
                'reading_count' => $group->count(),
            ]);

        $subRows = $submeters->map(function (Submeter $submeter) use ($estimatedByMeter, $actualBySubmeter, $submeterParentMap) {
            $meterKey = 'sub:' . (int) $submeter->id;
            $meterStats = $estimatedByMeter->get($meterKey);
            $estimated = (float) ($meterStats->total_estimated_kwh ?? 0);
            $actual = (float) ($actualBySubmeter->get($submeter->id)->total_actual_kwh ?? 0);
            $parentMain = $submeterParentMap->get($this->meterNameKey(
                (int) $submeter->facility_id,
                (string) $submeter->submeter_name
            ));
            $variancePercent = $this->variancePercent($estimated, $actual);
            $consumptionLevel = $this->resolveConsumptionLevel($estimated, $actual, $variancePercent);
            $isFlagged = $consumptionLevel === 'high';
            $direction = $variancePercent === null ? 'n/a' : ($variancePercent >= 0 ? 'above' : 'below');

            return [
                'meter_scope' => 'sub',
                'meter_scope_label' => 'Sub Meter',
                'meter_id' => (int) $submeter->id,
                'meter_name' => $submeter->submeter_name,
                'parent_main_meter_id' => $parentMain['id'] ?? null,
                'parent_main_meter_name' => $parentMain['name'] ?? null,
                'facility_id' => $submeter->facility_id,
                'facility_name' => $this->resolveFacilityName(
                    $submeter->facility_id,
                    $submeter->facility?->name
                ),
                'equipment_count' => (int) ($meterStats->total_equipment ?? 0),
                'total_watts' => round((float) ($meterStats->total_watts ?? 0), 2),
                'estimated_kwh' => round($estimated, 2),
                'actual_kwh' => round($actual, 2),
                'variance_percent' => $variancePercent,
                'variance_direction' => $direction,
                'consumption_level' => $consumptionLevel,
                'consumption_label' => $this->consumptionLevelLabel($consumptionLevel),
                'is_flagged' => $isFlagged,
            ];
        });

        $mainRows = $mainMeters->map(function (FacilityMeter $mainMeter) use ($estimatedByMeter, $manualActualByMainMeter, $sensorActualByMainMeter) {
            $meterKey = 'main:' . (int) $mainMeter->id;
            $meterStats = $estimatedByMeter->get($meterKey);
            $estimated = (float) ($meterStats->total_estimated_kwh ?? 0);
            $sensorActual = $sensorActualByMainMeter->get((int) $mainMeter->id);
            $actual = $sensorActual && (int) ($sensorActual->reading_count ?? 0) > 0
                ? (float) ($sensorActual->total_actual_kwh ?? 0)
                : (float) ($manualActualByMainMeter->get($mainMeter->id)->total_actual_kwh ?? 0);
            $variancePercent = $this->variancePercent($estimated, $actual);
            $consumptionLevel = $this->resolveConsumptionLevel($estimated, $actual, $variancePercent);
            $isFlagged = $consumptionLevel === 'high';
            $direction = $variancePercent === null ? 'n/a' : ($variancePercent >= 0 ? 'above' : 'below');

            return [
                'meter_scope' => 'main',
                'meter_scope_label' => 'Main Meter',
                'meter_id' => (int) $mainMeter->id,
                'meter_name' => $mainMeter->meter_name,
                'parent_main_meter_id' => null,
                'parent_main_meter_name' => null,
                'facility_id' => $mainMeter->facility_id,
                'facility_name' => $this->resolveFacilityName(
                    $mainMeter->facility_id,
                    $mainMeter->facility?->name
                ),
                'equipment_count' => (int) ($meterStats->total_equipment ?? 0),
                'total_watts' => round((float) ($meterStats->total_watts ?? 0), 2),
                'estimated_kwh' => round($estimated, 2),
                'actual_kwh' => round($actual, 2),
                'variance_percent' => $variancePercent,
                'variance_direction' => $direction,
                'consumption_level' => $consumptionLevel,
                'consumption_label' => $this->consumptionLevelLabel($consumptionLevel),
                'is_flagged' => $isFlagged,
            ];
        });

        $rows = $subRows
            ->concat($mainRows)
            ->sortByDesc('estimated_kwh')
            ->values();

        $topEquipment = SubmeterEquipment::query()
            ->with([
                'submeter:id,facility_id,submeter_name',
                'submeter.facility:id,name',
                'mainMeter:id,facility_id,meter_name',
                'mainMeter.facility:id,name',
            ])
            ->where(function ($builder) use ($submeterIds, $mainMeterIds) {
                $hasCondition = false;

                if (! empty($submeterIds)) {
                    $builder->where(function ($subQuery) use ($submeterIds) {
                        $subQuery->where('meter_scope', 'sub')
                            ->whereIn('submeter_id', $submeterIds);
                    });
                    $hasCondition = true;
                }

                if (! empty($mainMeterIds)) {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $builder->{$method}(function ($mainQuery) use ($mainMeterIds) {
                        $mainQuery->where('meter_scope', 'main')
                            ->whereIn('facility_meter_id', $mainMeterIds);
                    });
                }
            })
            ->orderByDesc('estimated_kwh')
            ->limit(10)
            ->get();

        [$pieLabels, $pieValues] = $this->buildPieSeries($topEquipment);

        $comparisonLabels = $rows->map(function (array $row) {
            return $row['meter_name'] . ' [' . $row['meter_scope_label'] . '] (' . $row['facility_name'] . ')';
        })->all();

        $comparisonEstimated = $rows->pluck('estimated_kwh')->map(fn ($value) => (float) $value)->all();
        $comparisonActual = $rows->pluck('actual_kwh')->map(fn ($value) => (float) $value)->all();

        $totalEstimated = round((float) $rows->sum('estimated_kwh'), 2);
        $totalActual = round((float) $rows->sum('actual_kwh'), 2);
        $totalVariance = $this->variancePercent($totalEstimated, $totalActual);
        $flaggedCount = $rows->where('is_flagged', true)->count();

        return [
            'rows' => $rows,
            'top_equipment' => $topEquipment,
            'pie_labels' => $pieLabels,
            'pie_values' => $pieValues,
            'comparison_labels' => $comparisonLabels,
            'comparison_estimated' => $comparisonEstimated,
            'comparison_actual' => $comparisonActual,
            'totals' => [
                'estimated_kwh' => $totalEstimated,
                'actual_kwh' => $totalActual,
                'variance_percent' => $totalVariance,
                'flagged_submeters' => $flaggedCount,
            ],
        ];
    }

    public function variancePercent(float $estimatedKwh, float $actualKwh): ?float
    {
        if ($estimatedKwh <= 0) {
            return null;
        }

        return round((($actualKwh - $estimatedKwh) / $estimatedKwh) * 100, 2);
    }

    public function resolveConsumptionLevel(float $estimatedKwh, float $actualKwh, ?float $variancePercent): string
    {
        if ($variancePercent !== null) {
            if ($variancePercent > self::VARIANCE_FLAG_PERCENT) {
                return 'high';
            }
            if ($variancePercent > self::VARIANCE_WARNING_PERCENT) {
                return 'warning';
            }

            return 'normal';
        }

        // No estimate available yet: mark separately to avoid false high-consumption classification.
        if ($estimatedKwh <= 0 && $actualKwh > 0) {
            return 'no_estimate';
        }

        return 'normal';
    }

    public function consumptionLevelLabel(string $level): string
    {
        return match (strtolower($level)) {
            'high' => 'High Consumption',
            'warning' => 'Warning',
            'no_estimate' => 'No Estimate',
            default => 'Normal',
        };
    }

    private function resolveFacilityName(?int $facilityId, ?string $facilityName): string
    {
        $name = trim((string) ($facilityName ?? ''));
        if ($name !== '') {
            return $name;
        }

        if (! empty($facilityId)) {
            $archivedName = (string) (Facility::withTrashed()->where('id', (int) $facilityId)->value('name') ?? '');
            if (trim($archivedName) !== '') {
                return $archivedName . ' (Archived)';
            }

            return 'Deleted Facility #' . (int) $facilityId;
        }

        return 'Unknown Facility';
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    private function buildPieSeries(Collection $topEquipment): array
    {
        if ($topEquipment->isEmpty()) {
            return [[], []];
        }

        $labels = [];
        $values = [];
        foreach ($topEquipment as $item) {
            $name = (string) ($item->equipment_name ?? 'Equipment');
            $isMainScope = strtolower((string) ($item->meter_scope ?? 'sub')) === 'main';
            $meterName = $isMainScope
                ? (string) ($item->mainMeter?->meter_name ?? 'Main Meter')
                : (string) ($item->submeter?->submeter_name ?? 'Submeter');
            $scopeLabel = $isMainScope ? 'Main' : 'Sub';
            $labels[] = $name . ' - ' . $meterName . ' [' . $scopeLabel . ']';
            $values[] = round((float) ($item->estimated_kwh ?? 0), 2);
        }

        return [$labels, $values];
    }

    private function linkedSubmeterKeysForMainMeter(int $mainMeterId): Collection
    {
        if ($mainMeterId <= 0) {
            return collect();
        }

        return FacilityMeter::query()
            ->where('meter_type', 'sub')
            ->where('parent_meter_id', $mainMeterId)
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->get(['facility_id', 'meter_name'])
            ->map(fn (FacilityMeter $meter) => $this->meterNameKey(
                (int) $meter->facility_id,
                (string) $meter->meter_name
            ))
            ->values();
    }

    private function submeterParentMap(Collection $submeters): Collection
    {
        $facilityIds = $submeters->pluck('facility_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($facilityIds->isEmpty()) {
            return collect();
        }

        return FacilityMeter::query()
            ->with('parentMeter:id,meter_name')
            ->whereIn('facility_id', $facilityIds)
            ->where('meter_type', 'sub')
            ->whereNotNull('parent_meter_id')
            ->get(['id', 'facility_id', 'meter_name', 'parent_meter_id'])
            ->mapWithKeys(function (FacilityMeter $meter) {
                return [
                    $this->meterNameKey((int) $meter->facility_id, (string) $meter->meter_name) => [
                        'id' => (int) $meter->parent_meter_id,
                        'name' => (string) ($meter->parentMeter?->meter_name ?? 'Main Meter #' . (int) $meter->parent_meter_id),
                    ],
                ];
            });
    }

    private function meterNameKey(int $facilityId, string $name): string
    {
        $normalizedName = strtolower((string) preg_replace('/\s+/', ' ', trim($name)));

        return $facilityId . '|' . $normalizedName;
    }
}
