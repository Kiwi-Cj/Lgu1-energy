<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\SubmeterEquipment;
use App\Support\EnergyCost;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EnergyConservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer'])) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view energy conservation.');
        }

        [$year, $month, $selectedMonth, $periodLabel] = $this->resolveMonth($request->query('month'));
        $facilityScope = $this->facilityScope($request);

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($query) => $query->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'baseline_kwh', 'status']);

        $facilityIds = $facilities->pluck('id')->map(fn ($id) => (int) $id)->all();
        $facilityMap = $facilities->keyBy('id');

        $records = EnergyRecord::query()
            ->with('meter:id,facility_id,meter_name,meter_type')
            ->whereIn('facility_id', $facilityIds)
            ->where('year', $year)
            ->where('month', $month)
            ->whereHas('meter', fn ($query) => $query->where('meter_type', 'main'))
            ->get(['id', 'facility_id', 'meter_id', 'year', 'month', 'actual_kwh', 'baseline_kwh', 'energy_cost', 'rate_per_kwh']);

        $rows = $records
            ->groupBy('facility_id')
            ->map(function (Collection $facilityRecords, int $facilityId) use ($facilityMap, $selectedMonth) {
                $facility = $facilityMap->get($facilityId);
                $actualKwh = (float) $facilityRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0));
                $baselineKwh = (float) $facilityRecords->sum(fn ($record) => is_numeric($record->baseline_kwh ?? null) ? (float) $record->baseline_kwh : 0);
                if ($baselineKwh <= 0 && is_numeric($facility?->baseline_kwh)) {
                    $baselineKwh = (float) $facility->baseline_kwh;
                }

                $energyCost = (float) $facilityRecords->sum(fn ($record) => EnergyCost::cost($record));
                $resolvedRate = $actualKwh > 0 ? ($energyCost / $actualKwh) : EnergyCost::DEFAULT_RATE_PER_KWH;
                $excessKwh = max(0, $actualKwh - $baselineKwh);
                $avoidableCost = $excessKwh * $resolvedRate;
                $deviation = EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);
                $alertLevel = $baselineKwh > 0 ? EnergyRecord::resolveAlertLevel($deviation, $baselineKwh) : 'No Data';

                return [
                    'facility_id' => $facilityId,
                    'facility_name' => (string) ($facility?->name ?? 'Facility #' . $facilityId),
                    'facility_type' => (string) ($facility?->type ?? 'Facility'),
                    'actual_kwh' => round($actualKwh, 2),
                    'baseline_kwh' => round($baselineKwh, 2),
                    'excess_kwh' => round($excessKwh, 2),
                    'avoidable_cost' => round($avoidableCost, 2),
                    'deviation' => $deviation,
                    'alert_level' => $alertLevel !== '' ? $alertLevel : 'Normal',
                    'recommendation' => $this->recommendationFor($alertLevel, $deviation),
                    'monthly_records_url' => route('facilities.monthly-records', ['facility' => $facilityId, 'year' => (int) substr($selectedMonth, 0, 4)]),
                ];
            })
            ->sortByDesc('avoidable_cost')
            ->values();

        $facilitiesWithoutCurrentRecord = $facilities
            ->reject(fn (Facility $facility) => $rows->contains('facility_id', (int) $facility->id))
            ->values();

        $topEquipment = $this->topEquipment($facilityScope);

        $totals = [
            'facilities' => $facilities->count(),
            'monitored_facilities' => $rows->count(),
            'actual_kwh' => round((float) $rows->sum('actual_kwh'), 2),
            'baseline_kwh' => round((float) $rows->sum('baseline_kwh'), 2),
            'excess_kwh' => round((float) $rows->sum('excess_kwh'), 2),
            'avoidable_cost' => round((float) $rows->sum('avoidable_cost'), 2),
            'priority_count' => $rows->filter(fn (array $row) => in_array($row['alert_level'], ['High', 'Very High', 'Critical'], true))->count(),
        ];

        return view('modules.energy-conservation.index', [
            'selectedMonth' => $selectedMonth,
            'periodLabel' => $periodLabel,
            'rows' => $rows,
            'totals' => $totals,
            'topEquipment' => $topEquipment,
            'facilitiesWithoutCurrentRecord' => $facilitiesWithoutCurrentRecord,
        ]);
    }

    private function resolveMonth(mixed $month): array
    {
        try {
            $anchor = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth();
        } catch (\Throwable $e) {
            $anchor = now()->startOfMonth();
        }

        return [(int) $anchor->year, (int) $anchor->month, $anchor->format('Y-m'), $anchor->format('F Y')];
    }

    private function facilityScope(Request $request): ?array
    {
        $user = $request->user();
        if (! $user || ! RoleAccess::is($user, 'staff')) {
            return null;
        }

        return $user->facilities->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function recommendationFor(string $alertLevel, ?float $deviation): string
    {
        return match ($alertLevel) {
            'Critical' => 'Immediate conservation action: validate meter data, inspect major loads, and reduce unnecessary after-hours operation.',
            'Very High' => 'Schedule an urgent load audit and adjust operating schedules for large equipment before the next billing cycle.',
            'High' => 'Review high-use equipment, lighting schedules, and air-conditioning runtime for quick kWh reduction.',
            'Warning' => 'Monitor daily use and confirm that current operations still match the approved facility schedule.',
            'Normal' => 'Consumption is within expected limits. Keep current controls and continue monthly conservation checks.',
            default => $deviation === null
                ? 'Add current main meter readings and baseline data to generate conservation guidance.'
                : 'Continue monitoring this facility for conservation opportunities.',
        };
    }

    private function topEquipment(?array $facilityScope): Collection
    {
        return SubmeterEquipment::query()
            ->with([
                'submeter:id,facility_id,submeter_name',
                'submeter.facility:id,name',
                'mainMeter:id,facility_id,meter_name',
                'mainMeter.facility:id,name',
            ])
            ->where(function ($query) use ($facilityScope) {
                $query->where(function ($subQuery) use ($facilityScope) {
                    $subQuery->where('meter_scope', 'sub')
                        ->whereHas('submeter', function ($meterQuery) use ($facilityScope) {
                            if ($facilityScope !== null) {
                                $meterQuery->whereIn('facility_id', $facilityScope);
                            }
                        });
                })->orWhere(function ($mainQuery) use ($facilityScope) {
                    $mainQuery->where('meter_scope', 'main')
                        ->whereHas('mainMeter', function ($meterQuery) use ($facilityScope) {
                            if ($facilityScope !== null) {
                                $meterQuery->whereIn('facility_id', $facilityScope);
                            }
                        });
                });
            })
            ->orderByDesc('estimated_kwh')
            ->limit(8)
            ->get()
            ->map(function (SubmeterEquipment $equipment) {
                $scope = strtolower((string) ($equipment->meter_scope ?? 'sub'));
                $facility = $scope === 'main'
                    ? $equipment->mainMeter?->facility
                    : $equipment->submeter?->facility;

                return [
                    'equipment_name' => (string) ($equipment->equipment_name ?? 'Equipment'),
                    'facility_name' => (string) ($facility?->name ?? 'Unassigned facility'),
                    'meter_name' => $equipment->meter_name,
                    'estimated_kwh' => round((float) ($equipment->estimated_kwh ?? 0), 2),
                    'scope_label' => $equipment->meter_scope_label,
                ];
            });
    }
}
