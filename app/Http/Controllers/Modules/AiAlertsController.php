<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Support\EnergyAlertRouting;
use App\Support\EnergyCost;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AiAlertsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! RoleAccess::can($user, 'view_energy_monitoring')) {
            return redirect()->route('dashboard.index')
                ->with('error', 'You do not have permission to view AI Alerts.');
        }

        $facilities = RoleAccess::normalize($user) === 'staff'
            ? $user->facilities()->orderBy('name')->get()
            : Facility::query()->orderBy('name')->get();

        $facilityIds = $facilities->pluck('id');
        $period = $this->resolvePeriod($request, $facilityIds->all());
        $previousPeriod = $period->copy()->subMonth();

        $currentRecords = $this->recordsForPeriod($facilityIds->all(), $period->year, $period->month)
            ->get()->groupBy('facility_id');
        $previousRecords = $this->recordsForPeriod($facilityIds->all(), $previousPeriod->year, $previousPeriod->month)
            ->get()->groupBy('facility_id');

        $alerts = $facilities->map(function (Facility $facility) use ($currentRecords, $previousRecords, $period) {
            $records = $currentRecords->get($facility->id, collect());
            $previous = $previousRecords->get($facility->id, collect());
            $actualKwh = (float) $records->sum(fn ($record) => (float) $record->actual_kwh);
            $baselineKwh = (float) $records->sum(fn ($record) => (float) ($record->baseline_kwh ?: 0));
            if ($baselineKwh <= 0 && is_numeric($facility->baseline_kwh)) {
                $baselineKwh = (float) $facility->baseline_kwh;
            }

            $deviation = EnergyRecord::calculateDeviation($actualKwh, $baselineKwh > 0 ? $baselineKwh : null);
            $usageLevel = EnergyRecord::resolveAlertLevel($deviation, $baselineKwh > 0 ? $baselineKwh : null) ?: 'No Data';
            $currentCost = (float) $records->sum(fn ($record) => EnergyCost::cost($record));
            $previousCost = (float) $previous->sum(fn ($record) => EnergyCost::cost($record));

            $latestDay = (int) $records->max('day');
            $isDailyProgress = $latestDay > 0 && $period->isSameMonth(now());
            $projectedCost = $isDailyProgress
                ? ($currentCost / max(1, $latestDay)) * $period->daysInMonth
                : $currentCost;
            $costVariance = $previousCost > 0
                ? (($projectedCost - $previousCost) / $previousCost) * 100
                : null;
            $costExceeded = $previousCost > 0 && $projectedCost > $previousCost;
            $actionOwner = EnergyAlertRouting::owner($usageLevel, $costExceeded);

            return [
                'facility' => $facility,
                'has_data' => $records->isNotEmpty(),
                'actual_kwh' => round($actualKwh, 2),
                'baseline_kwh' => $baselineKwh > 0 ? round($baselineKwh, 2) : null,
                'deviation' => $deviation,
                'usage_level' => $usageLevel,
                'usage_alert' => in_array($usageLevel, ['High', 'Very High', 'Critical'], true),
                'current_cost' => round($currentCost, 2),
                'projected_cost' => round($projectedCost, 2),
                'previous_cost' => round($previousCost, 2),
                'cost_variance' => $costVariance !== null ? round($costVariance, 1) : null,
                'cost_alert' => $costExceeded,
                'action_owner' => $actionOwner,
                'tip' => $this->energyTip($usageLevel, $deviation, $costExceeded, $costVariance),
            ];
        })->sortByDesc(fn (array $alert) => ($alert['usage_alert'] ? 2 : 0) + ($alert['cost_alert'] ? 1 : 0))->values();

        return view('modules.ai-alerts.index', [
            'alerts' => $alerts,
            'period' => $period,
            'periodInput' => $period->format('Y-m'),
            'summary' => [
                'facilities' => $alerts->where('has_data', true)->count(),
                'high_usage' => $alerts->where('usage_alert', true)->count(),
                'cost' => $alerts->where('cost_alert', true)->count(),
                'normal' => $alerts->where('has_data', true)->where('usage_alert', false)->where('cost_alert', false)->count(),
            ],
        ]);
    }

    private function recordsForPeriod(array $facilityIds, int $year, int $month)
    {
        return EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('year', $year)
            ->where('month', $month)
            ->where(function ($query) {
                $query->whereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'))
                    ->orWhere(function ($fallback) {
                        $fallback->whereNull('meter_id');
                    });
            });
    }

    private function resolvePeriod(Request $request, array $facilityIds): Carbon
    {
        $input = trim((string) $request->query('month'));
        if (preg_match('/^\d{4}-\d{2}$/', $input)) {
            return Carbon::createFromFormat('Y-m', $input)->startOfMonth();
        }

        $latest = EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->orderByDesc('year')->orderByDesc('month')
            ->first(['year', 'month']);

        return $latest
            ? Carbon::create((int) $latest->year, (int) $latest->month, 1)
            : now()->startOfMonth();
    }

    private function energyTip(string $level, ?float $deviation, bool $costExceeded, ?float $costVariance): string
    {
        if (in_array($level, ['Very High', 'Critical'], true)) {
            return 'Prioritize an equipment audit and check air-conditioning schedules, lighting, and devices left on after operating hours.';
        }
        if ($level === 'High') {
            return 'Review peak-hour loads and stagger high-consumption equipment. A short daily shutdown checklist can reduce avoidable usage.';
        }
        if ($costExceeded) {
            $increase = $costVariance !== null ? number_format(abs($costVariance), 1).'%' : 'the previous month';
            return "The projected bill is up by {$increase}. Verify the electricity rate and reduce non-essential loads during peak operating hours.";
        }
        if ($deviation !== null && $deviation < 0) {
            return 'Usage is below baseline. Keep the current operating schedule and document the practices that produced the reduction.';
        }

        return 'Maintain efficient settings: use natural light where possible, keep cooling at 24–26°C, and switch off idle equipment.';
    }
}
