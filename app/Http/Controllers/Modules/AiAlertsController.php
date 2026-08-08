<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Support\BaselineResolver;
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
        $facilities->load('energyProfiles:id,facility_id,baseline_kwh');

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
            $baselineKwh = (float) $records->sum(function (EnergyRecord $record) {
                if (is_numeric($record->baseline_kwh) && (float) $record->baseline_kwh > 0) {
                    return (float) $record->baseline_kwh;
                }

                return is_numeric($record->meter?->baseline_kwh)
                    ? (float) $record->meter->baseline_kwh
                    : 0;
            });
            if ($baselineKwh <= 0) {
                $baselineKwh = (float) (BaselineResolver::forFacility($facility) ?? 0);
            }

            $deviation = EnergyRecord::calculateDeviation($actualKwh, $baselineKwh > 0 ? $baselineKwh : null);
            $usageLevel = EnergyRecord::resolveAlertLevel($deviation, $baselineKwh > 0 ? $baselineKwh : null) ?: 'No Data';
            $currentCost = (float) $records->sum(fn ($record) => EnergyCost::cost($record));
            $previousCost = (float) $previous->sum(fn ($record) => EnergyCost::cost($record));

            $costVariance = $previousCost > 0
                ? (($currentCost - $previousCost) / $previousCost) * 100
                : null;
            $costExceeded = $previousCost > 0 && $currentCost > $previousCost;
            $actionOwner = EnergyAlertRouting::owner($usageLevel, $costExceeded);
            $usageException = ! in_array($usageLevel, ['Normal', 'No Data'], true);
            $primaryRecord = $records->sortByDesc('id')->first();
            $reviewReady = $records->isNotEmpty()
                && $records->every(fn (EnergyRecord $record) => ($record->review_status ?? 'for_review') === 'approved');

            return [
                'facility' => $facility,
                'has_data' => $records->isNotEmpty(),
                'actual_kwh' => round($actualKwh, 2),
                'baseline_kwh' => $baselineKwh > 0 ? round($baselineKwh, 2) : null,
                'deviation' => $deviation,
                'usage_level' => $usageLevel,
                'usage_alert' => $usageException,
                'current_cost' => round($currentCost, 2),
                'previous_cost' => round($previousCost, 2),
                'cost_variance' => $costVariance !== null ? round($costVariance, 1) : null,
                'cost_alert' => $costExceeded,
                'action_owner' => $actionOwner,
                'record_id' => $primaryRecord?->id,
                'review_ready' => $reviewReady,
                'review_status' => $reviewReady ? 'Approved' : ($records->isNotEmpty() ? 'For Review' : 'No Record'),
                'source_label' => strtolower((string) ($primaryRecord?->input_source ?? '')) === 'cprf'
                    ? 'CPRF via UMAN'
                    : 'Energy',
                'tip' => $this->energyTip($usageLevel, $deviation, $costExceeded, $costVariance),
            ];
        })->sortByDesc(fn (array $alert) => $this->alertPriority($alert))->values();

        return view('modules.ai-alerts.index', [
            'alerts' => $alerts,
            'period' => $period,
            'periodInput' => $period->format('Y-m'),
            'summary' => [
                'facilities' => $alerts->where('has_data', true)->count(),
                'usage_exceptions' => $alerts->where('usage_alert', true)->count(),
                'cost' => $alerts->where('cost_alert', true)->count(),
                'normal' => $alerts->where('usage_level', 'Normal')->where('cost_alert', false)->count(),
                'baseline_pending' => $alerts->where('has_data', true)->where('usage_level', 'No Data')->count(),
            ],
        ]);
    }

    private function recordsForPeriod(array $facilityIds, int $year, int $month)
    {
        return EnergyRecord::query()
            ->with('meter:id,facility_id,meter_name,meter_type,baseline_kwh')
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

    private function alertPriority(array $alert): int
    {
        $severity = match ($alert['usage_level'] ?? 'No Data') {
            'Critical', 'Drop Critical' => 60,
            'Very High', 'Drop High' => 50,
            'High', 'Drop Warning' => 40,
            'Warning' => 30,
            'No Data' => 10,
            default => 0,
        };

        return $severity + (! empty($alert['cost_alert']) ? 5 : 0);
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
        if ($level === 'No Data') {
            return 'Keep collecting approved monthly readings. Establish a preliminary baseline after 3 months and use 6 months for the recommended baseline before estimating excess use.';
        }
        if (in_array($level, ['Drop Critical', 'Drop High'], true)) {
            return 'Validate the meter reading and check for outages, reduced operating hours, closed areas, or equipment downtime before treating this large drop as sustained savings.';
        }
        if ($level === 'Drop Warning') {
            return 'Confirm whether the lower reading is intentional and document the operating changes that produced it.';
        }
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
