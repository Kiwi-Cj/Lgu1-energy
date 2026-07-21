<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\EnergyRecord;
use App\Models\EnergySavingRecommendation;
use App\Models\Facility;
use App\Support\EnergyCost;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EnergyConservationController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderOverview($request);
    }

    public function feature(Request $request, string $feature)
    {
        $features = $this->featureCatalog();
        $selected = $features[$feature] ?? null;
        if (! $selected) {
            return redirect()->route('modules.energy-conservation.index')->with('error', 'Feature not found.');
        }

        $overview = $this->buildOverviewData($request);
        $selectedFacilityId = (int) $request->query('facility_id', 0);
        $selectedFacility = $overview['facilities']->firstWhere('id', $selectedFacilityId);
        $canReviewTips = RoleAccess::in($request->user(), ['super_admin', 'admin', 'energy_officer', 'engineer']);
        $energyTips = collect();

        if ($feature === 'energy-saving-tips') {
            $energyTips = $this->energySavingTips($overview['rows'], $selectedFacilityId, $overview['periodLabel']);
            [$tipYear, $tipMonth] = array_map('intval', explode('-', $overview['selectedMonth']));
            $reviews = EnergySavingRecommendation::query()
                ->with('reviewer:id,username')
                ->where('year', $tipYear)
                ->where('month', $tipMonth)
                ->whereIn('facility_id', $energyTips->pluck('facility_id')->filter())
                ->get()
                ->keyBy('facility_id');

            $energyTips = $energyTips
                ->map(function (array $tip) use ($reviews) {
                    $tip['review'] = $reviews->get($tip['facility_id'] ?? 0);
                    return $tip;
                })
                ->when(! $canReviewTips, fn (Collection $tips) => $tips->filter(
                    fn (array $tip) => ($tip['review']?->status ?? null) === 'approved' || empty($tip['facility_id'])
                ))
                ->values();
        }

        return view('modules.energy-conservation.feature', [
            'selectedMonth' => $overview['selectedMonth'],
            'featureSlug' => $feature,
            'feature' => $selected,
            'featureCatalog' => $features,
            'overview' => $overview,
            'selectedFacility' => $selectedFacility,
            'selectedFacilityId' => $selectedFacilityId,
            'energyTips' => $energyTips,
            'canReviewTips' => $canReviewTips,
        ]);
    }

    public function reviewEnergyTip(Request $request)
    {
        if (! RoleAccess::in($request->user(), ['super_admin', 'admin', 'energy_officer', 'engineer'])) {
            abort(403, 'Only Engineering, Energy Officers, or administrators can review energy tips.');
        }

        $validated = $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'period' => ['required', 'date_format:Y-m'],
            'status' => ['required', 'in:for_review,approved,dismissed'],
            'engineer_recommendation' => ['nullable', 'string', 'max:3000', 'required_if:status,approved'],
            'expected_savings_kwh' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
        ]);

        $request->merge(['month' => $validated['period'], 'facility_id' => $validated['facility_id']]);
        $overview = $this->buildOverviewData($request);
        $tip = $this->energySavingTips($overview['rows'], (int) $validated['facility_id'], $overview['periodLabel'])->first();
        abort_unless($tip && ! empty($tip['facility_id']), 422, 'No monthly energy data is available for this facility.');
        [$year, $month] = array_map('intval', explode('-', $validated['period']));

        EnergySavingRecommendation::updateOrCreate(
            ['facility_id' => $validated['facility_id'], 'year' => $year, 'month' => $month],
            [
                'generated_message' => $tip['message'],
                'engineer_recommendation' => $validated['engineer_recommendation'],
                'status' => $validated['status'],
                'expected_savings_kwh' => $validated['expected_savings_kwh'] ?? null,
                'target_date' => $validated['target_date'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]
        );

        return redirect()->route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'month' => $validated['period'],
            'facility_id' => $validated['facility_id'],
        ])->with('success', 'Energy-saving recommendation updated.');
    }

    private function renderOverview(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer'])) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view energy conservation.');
        }

        $catalog = $this->featureCatalog();

        return view('modules.energy-conservation.index', [
            'selectedMonth' => (string) $request->query('month', now()->format('Y-m')),
            'featureCatalog' => $catalog,
        ]);
    }

    private function buildOverviewData(Request $request): array
    {
        $user = $request->user();
        if (! RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer'])) {
            abort(403);
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

        $totals = [
            'facilities' => $facilities->count(),
            'monitored_facilities' => $rows->count(),
            'actual_kwh' => round((float) $rows->sum('actual_kwh'), 2),
            'baseline_kwh' => round((float) $rows->sum('baseline_kwh'), 2),
            'excess_kwh' => round((float) $rows->sum('excess_kwh'), 2),
            'avoidable_cost' => round((float) $rows->sum('avoidable_cost'), 2),
            'priority_count' => $rows->filter(fn (array $row) => in_array($row['alert_level'], ['High', 'Very High', 'Critical'], true))->count(),
        ];

        return [
            'selectedMonth' => $selectedMonth,
            'periodLabel' => $periodLabel,
            'facilities' => $facilities,
            'rows' => $rows,
            'totals' => $totals,
            'facilitiesWithoutCurrentRecord' => $facilitiesWithoutCurrentRecord,
            'topFacility' => $rows->first(),
            'averageDeviation' => $rows->isNotEmpty() ? round((float) $rows->filter(fn (array $row) => $row['deviation'] !== null)->avg('deviation'), 2) : null,
            'contactInboxCount' => ContactMessage::count(),
            'latestContactSuggestions' => ContactMessage::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'subject', 'message', 'created_at']),
        ];
    }

    private function featureCatalog(): array
    {
        return [
            'energy-saving-tips' => [
                'title' => 'Energy Saving Tips',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-sun',
                'description' => 'Data-driven recommendations for reducing electricity use across LGU facilities.',
                'details' => [
                    'Post practical tips for AC, lighting, and equipment use.',
                    'Highlight weekly or monthly saving reminders.',
                    'Use this area for quick staff education.',
                ],
            ],
            'conservation-goals' => [
                'title' => 'Conservation Goals',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-bullseye',
                'description' => 'Nagtatakda ng energy reduction targets.',
                'details' => [
                    'Set reduction targets per month or quarter.',
                    'Track progress against baseline usage.',
                    'Show goal progress in percent and kWh.',
                ],
            ],
            'department-ranking' => [
                'title' => 'Department Ranking',
                'badge' => 'Coming Soon',
                'status' => 'coming-soon',
                'icon' => 'fa-solid fa-ranking-star',
                'description' => 'Nagra-rank ng departments base sa energy efficiency.',
                'details' => [
                    'Compare department usage against targets.',
                    'Rank by savings percentage and consistency.',
                    'Allow filters by month, quarter, and year.',
                ],
            ],
            'rewards-system' => [
                'title' => 'Rewards System',
                'badge' => 'Coming Soon',
                'status' => 'coming-soon',
                'icon' => 'fa-solid fa-medal',
                'description' => 'Nagbibigay ng badges o incentives sa mga nakakatipid.',
                'details' => [
                    'Issue badges for consistent low-consumption teams.',
                    'Highlight top performers in the dashboard.',
                    'Optionally connect to incentive approvals.',
                ],
            ],
            'ai-recommendations' => [
                'title' => 'AI Recommendations',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-robot',
                'description' => 'Nagbibigay ng AI-based energy-saving suggestions.',
                'details' => [
                    'Suggest actions based on monthly trends.',
                    'Summarize inefficiencies in plain language.',
                    'Combine manual rules with AI output.',
                ],
            ],
            'campaign-management' => [
                'title' => 'Campaign Management',
                'badge' => 'Coming Soon',
                'status' => 'coming-soon',
                'icon' => 'fa-solid fa-bullhorn',
                'description' => 'Nagpo-post ng energy conservation campaigns.',
                'details' => [
                    'Publish campaigns and reminders.',
                    'Attach target dates and campaign owners.',
                    'Track which departments acknowledged the campaign.',
                ],
            ],
            'daily-checklist' => [
                'title' => 'Daily Checklist',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-clipboard-check',
                'description' => 'Checklist ng energy-saving practices.',
                'details' => [
                    'Use a simple checklist for opening and closing routines.',
                    'Mark completed conservation tasks each day.',
                    'Show overdue checklist items clearly.',
                ],
            ],
            'estimated-savings' => [
                'title' => 'Estimated Savings',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-chart-line',
                'description' => 'Nagpapakita ng natipid na kWh, gastos, at CO2 reduction.',
                'details' => [
                    'Display kWh savings, peso savings, and CO2 impact.',
                    'Break down savings by month and department.',
                    'Use baseline comparisons to compute estimates.',
                ],
            ],
            'suggestions-box' => [
                'title' => 'Suggestions Box',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-inbox',
                'description' => 'Tumatanggap ng energy-saving suggestions mula sa users.',
                'details' => [
                    'Let users submit ideas and observations.',
                    'Show admin review status and follow-up notes.',
                    'Keep the suggestions visible for everyone to see progress.',
                ],
            ],
            'conservation-reports' => [
                'title' => 'Conservation Reports',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-file-lines',
                'description' => 'Gumagawa ng reports tungkol sa conservation efforts.',
                'details' => [
                    'Generate printable and exportable reports.',
                    'Summarize goals, tips, ranking, and savings in one place.',
                    'Use reports for management review and compliance.',
                ],
            ],
        ];
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

    private function energySavingTips(Collection $rows, int $facilityId, string $periodLabel): Collection
    {
        $targetRows = $facilityId > 0
            ? $rows->where('facility_id', $facilityId)
            : $rows;

        if ($targetRows->isEmpty()) {
            return collect([[
                'priority' => 'Data needed',
                'tone' => 'info',
                'icon' => 'fa-solid fa-circle-info',
                'title' => 'Add a monthly main-meter record',
                'message' => "No usable energy record was found for {$periodLabel}. Add actual kWh and a baseline so the system can calculate targeted saving tips.",
                'facility_name' => null,
                'facility_id' => null,
                'metric' => null,
            ]]);
        }

        return $targetRows
            ->sortByDesc(fn (array $row) => (float) ($row['deviation'] ?? -999))
            ->take(6)
            ->map(function (array $row) use ($periodLabel) {
                $actual = (float) ($row['actual_kwh'] ?? 0);
                $baseline = (float) ($row['baseline_kwh'] ?? 0);
                $excess = (float) ($row['excess_kwh'] ?? 0);
                $avoidableCost = (float) ($row['avoidable_cost'] ?? 0);
                $deviation = $row['deviation'];
                $facilityName = (string) ($row['facility_name'] ?? 'Facility');
                $facilityType = (string) ($row['facility_type'] ?? 'Facility');
                $operationalAction = $this->operationalTipFor($facilityType);

                if ($baseline <= 0 || $deviation === null) {
                    return [
                        'priority' => 'Set baseline',
                        'tone' => 'info',
                        'icon' => 'fa-solid fa-gauge-high',
                        'title' => 'Establish a reliable consumption baseline',
                        'message' => "{$facilityName} used " . number_format($actual, 2) . " kWh in {$periodLabel}, but has no valid baseline. Add a baseline before setting a reduction target. {$operationalAction}",
                        'facility_name' => $facilityName,
                        'facility_id' => $row['facility_id'],
                        'metric' => number_format($actual, 2) . ' kWh actual',
                    ];
                }

                if ($deviation >= 20) {
                    return [
                        'priority' => 'Urgent',
                        'tone' => 'critical',
                        'icon' => 'fa-solid fa-triangle-exclamation',
                        'title' => 'Cut the current excess load first',
                        'message' => "{$facilityName} is " . number_format((float) $deviation, 2) . "% above baseline. Target at least " . number_format($excess, 2) . " kWh reduction and review the largest loads immediately. {$operationalAction}",
                        'facility_name' => $facilityName,
                        'facility_id' => $row['facility_id'],
                        'metric' => 'Potential PHP ' . number_format($avoidableCost, 2) . ' avoidable cost',
                    ];
                }

                if ($deviation >= 10) {
                    return [
                        'priority' => 'High priority',
                        'tone' => 'warning',
                        'icon' => 'fa-solid fa-bolt',
                        'title' => 'Reduce operating-hour consumption',
                        'message' => "{$facilityName} is " . number_format((float) $deviation, 2) . "% above baseline for {$periodLabel}. {$operationalAction} Track the meter weekly until usage returns to baseline.",
                        'facility_name' => $facilityName,
                        'facility_id' => $row['facility_id'],
                        'metric' => number_format($excess, 2) . ' excess kWh',
                    ];
                }

                if ($deviation > 0) {
                    return [
                        'priority' => 'Monitor',
                        'tone' => 'watch',
                        'icon' => 'fa-solid fa-chart-line',
                        'title' => 'Prevent the small increase from growing',
                        'message' => "{$facilityName} is slightly above baseline by " . number_format((float) $deviation, 2) . "%. {$operationalAction} Compare weekly readings to confirm the adjustment works.",
                        'facility_name' => $facilityName,
                        'facility_id' => $row['facility_id'],
                        'metric' => number_format($excess, 2) . ' excess kWh',
                    ];
                }

                $savedKwh = max(0, $baseline - $actual);

                return [
                    'priority' => 'Maintain',
                    'tone' => 'success',
                    'icon' => 'fa-solid fa-leaf',
                    'title' => 'Maintain the current energy controls',
                    'message' => "{$facilityName} is within or below baseline for {$periodLabel}. Keep the current operating schedule and check for rebound consumption next month.",
                    'facility_name' => $facilityName,
                    'facility_id' => $row['facility_id'],
                    'metric' => number_format($savedKwh, 2) . ' kWh below baseline',
                ];
            })
            ->values();
    }

    private function operationalTipFor(string $facilityType): string
    {
        $type = strtolower($facilityType);

        return match (true) {
            str_contains($type, 'health'), str_contains($type, 'hospital'), str_contains($type, 'clinic')
                => 'Keep critical medical loads on, but optimize AC zoning, lighting, and non-clinical equipment schedules.',
            str_contains($type, 'market')
                => 'Check refrigeration seals and setpoints, stagger heavy equipment startup, and switch off unused stall lighting.',
            str_contains($type, 'office'), str_contains($type, 'administrative')
                => 'Set AC to 24–25°C, use natural light where practical, and enforce shutdown of idle computers after office hours.',
            str_contains($type, 'engineering'), str_contains($type, 'workshop')
                => 'Stagger high-load tools, inspect motors, and avoid leaving workshop equipment energized while idle.',
            default
                => 'Review AC runtime, lighting schedules, and equipment left energized outside operating hours.',
        };
    }

}
