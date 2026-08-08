<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ConservationGoal;
use App\Models\DailyEnergyChecklist;
use App\Models\DailyEnergyChecklistTask;
use App\Models\EnergyRecord;
use App\Models\EnergySavingRecommendation;
use App\Models\Facility;
use App\Services\RecommendationNotificationService;
use App\Support\BaselineResolver;
use App\Support\EnergyAlertRouting;
use App\Support\EnergyCost;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EnergyConservationController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderOverview($request);
    }

    public function feature(Request $request, string $feature)
    {
        // Legacy feature URLs now point to the single workspace that owns the
        // workflow, instead of maintaining duplicate conservation screens.
        if ($feature === 'ai-recommendations') {
            return redirect()->route('modules.ai-alerts.index', ['month' => $request->query('month')]);
        }
        if (in_array($feature, ['estimated-savings', 'conservation-reports'], true)) {
            return redirect()->route('reports.efficiency-summary');
        }
        if ($feature === 'suggestions-box') {
            return redirect()->route('landing.contact');
        }

        $features = $this->featureCatalog();
        $selected = $features[$feature] ?? null;
        if (! $selected) {
            return redirect()->route('modules.energy-conservation.index')->with('error', 'Feature not found.');
        }
        if (($selected['status'] ?? null) !== 'enabled') {
            return redirect()->route('modules.energy-conservation.index')
                ->with('error', $selected['title'].' is coming soon.');
        }

        $recommendationNotificationId = (int) $request->query('recommendation_notification_id', 0);
        if ($feature === 'energy-saving-tips' && $recommendationNotificationId > 0) {
            $request->user()
                ->notifications()
                ->whereKey($recommendationNotificationId)
                ->where('type', RecommendationNotificationService::TYPE)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $overview = $this->buildOverviewData($request);
        $selectedFacilityId = (int) $request->query('facility_id', 0);
        $selectedFacility = $overview['facilities']->firstWhere('id', $selectedFacilityId);
        $canReviewTips = RoleAccess::in($request->user(), ['super_admin', 'admin', 'energy_officer', 'engineer']);
        $energyTips = collect();
        $dailyChecklist = collect();
        $conservationGoals = collect();
        $checklistDate = (string) $request->query('date', now()->toDateString());
        $selectedRecordContext = null;
        $selectedRecordForAssignment = null;
        $isCprfIntegrationPeriod = false;
        $manualRecommendations = collect();
        $recommendationTotals = $overview['totals'];

        if ($feature === 'conservation-goals') {
            $allowedFacilityIds = $overview['facilities']->pluck('id');
            $conservationGoals = ConservationGoal::query()
                ->with('facility:id,name')
                ->where(fn ($query) => $query->whereNull('facility_id')->orWhereIn('facility_id', $allowedFacilityIds))
                ->latest('start_date')
                ->get()
                ->map(fn (ConservationGoal $goal) => $this->withGoalProgress($goal, $overview));
        }

        if ($feature === 'energy-saving-tips') {
            if (! $selectedFacility) {
                $selectedFacility = $overview['facilities']->first();
                $selectedFacilityId = (int) ($selectedFacility?->id ?? 0);
            }

            $recommendationRows = $selectedFacilityId > 0
                ? $overview['rows']->where('facility_id', $selectedFacilityId)->values()
                : $overview['rows'];
            $recommendationTotals = $this->summarizeEnergyRows($recommendationRows);

            $selectedRecordId = (int) $request->query('record_id', 0);
            if ($selectedRecordId > 0 && $selectedFacilityId > 0) {
                [$selectedYear, $selectedMonthNumber] = array_map('intval', explode('-', $overview['selectedMonth']));
                $selectedRecord = EnergyRecord::query()
                    ->with('meter:id,facility_id,meter_name,meter_type')
                    ->whereKey($selectedRecordId)
                    ->where('facility_id', $selectedFacilityId)
                    ->where('year', $selectedYear)
                    ->where('month', $selectedMonthNumber)
                    ->first();

                if ($selectedRecord) {
                    $selectedRecordForAssignment = $selectedRecord;
                    $isCprfFacilityLevel = $selectedRecord->meter_id === null
                        && strtolower((string) ($selectedRecord->input_source ?? '')) === 'cprf';
                    $recordDay = is_numeric($selectedRecord->day) ? (int) $selectedRecord->day : null;
                    $periodDate = Carbon::create($selectedYear, $selectedMonthNumber, 1);
                    $recordDateLabel = 'Day not specified';
                    if ($recordDay !== null && $recordDay >= 1 && $recordDay <= $periodDate->daysInMonth) {
                        $recordDateLabel = $periodDate->copy()->day($recordDay)->format('F j, Y');
                    }

                    $selectedRecordContext = [
                        'record_id' => (int) $selectedRecord->id,
                        'facility_name' => (string) ($selectedFacility?->name ?? 'Facility #'.$selectedFacilityId),
                        'facility_type' => (string) ($selectedFacility?->type ?? ''),
                        'period_label' => $periodDate->format('F Y'),
                        'record_date_label' => $recordDateLabel,
                        'meter_name' => $isCprfFacilityLevel
                            ? 'Facility-Level (CPRF)'
                            : (string) ($selectedRecord->meter?->meter_name ?? 'Main Meter'),
                        'source_label' => strtolower((string) ($selectedRecord->input_source ?? '')) === 'cprf'
                            ? 'CPRF via UMAN'
                            : 'Energy manual entry',
                        'review_status' => ucwords(str_replace('_', ' ', (string) ($selectedRecord->review_status ?? 'for_review'))),
                        'monthly_records_url' => route('facilities.monthly-records', [
                            'facility' => $selectedFacilityId,
                            'year' => $selectedYear,
                            'table_month' => $selectedMonthNumber,
                        ]),
                    ];
                }
            }

            $energyTips = $this->energySavingTips($overview['rows'], $selectedFacilityId, $overview['periodLabel']);
            [$tipYear, $tipMonth] = array_map('intval', explode('-', $overview['selectedMonth']));
            $isCprfIntegrationPeriod = $this->isCprfIntegrationPeriod(
                $selectedFacilityId,
                $tipYear,
                $tipMonth,
                $selectedRecordForAssignment?->id,
            );
            $manualRecommendations = EnergySavingRecommendation::query()
                ->with('reviewer:id,username')
                ->where('facility_id', $selectedFacilityId)
                ->where('year', $tipYear)
                ->where('month', $tipMonth)
                ->when(! $canReviewTips, fn ($query) => $query->where('status', 'approved'))
                ->latest('id')
                ->get();
            $reviews = EnergySavingRecommendation::query()
                ->with('reviewer:id,username')
                ->where('year', $tipYear)
                ->where('month', $tipMonth)
                ->whereIn('facility_id', $energyTips->pluck('facility_id')->filter())
                ->when(! $canReviewTips, fn ($query) => $query->where('status', 'approved'))
                ->latest('id')
                ->get()
                ->unique('facility_id')
                ->keyBy('facility_id');

            $energyTips = $energyTips
                ->map(function (array $tip) use ($reviews) {
                    $tip['review'] = $reviews->get($tip['facility_id'] ?? 0);
                    return $tip;
                })
                ->values();
        }

        if ($feature === 'daily-checklist') {
            if (! $selectedFacility) {
                $selectedFacility = $overview['facilities']->first();
                $selectedFacilityId = (int) ($selectedFacility?->id ?? 0);
            }
            try {
                $checklistDate = Carbon::parse($checklistDate)->toDateString();
            } catch (\Throwable) {
                $checklistDate = now()->toDateString();
            }
            $savedItems = DailyEnergyChecklist::query()
                ->with('completedBy:id,username,full_name')
                ->where('facility_id', $selectedFacilityId)
                ->whereDate('checklist_date', $checklistDate)
                ->get()
                ->keyBy('task_key');
            $dailyChecklist = $this->dailyChecklistTasks($selectedFacilityId)->map(function (array $task) use ($savedItems) {
                $task['record'] = $savedItems->get($task['key']);
                return $task;
            });
        }

        return view('modules.energy-conservation.feature', [
            'selectedMonth' => $overview['selectedMonth'],
            'featureSlug' => $feature,
            'feature' => $selected,
            'featureCatalog' => $features,
            'overview' => $overview,
            'selectedFacility' => $selectedFacility,
            'selectedFacilityId' => $selectedFacilityId,
            'selectedRecordContext' => $selectedRecordContext,
            'energyTips' => $energyTips,
            'canReviewTips' => $canReviewTips,
            'isCprfIntegrationPeriod' => $isCprfIntegrationPeriod,
            'recommendationTotals' => $recommendationTotals,
            'manualRecommendations' => $manualRecommendations,
            'dailyChecklist' => $dailyChecklist,
            'conservationGoals' => $conservationGoals,
            'canManageGoals' => $this->canManageChecklistTasks($request),
            'checklistDate' => $checklistDate,
            'canManageChecklistTasks' => $this->canManageChecklistTasks($request),
            'canCompleteChecklist' => RoleAccess::is($request->user(), 'staff'),
        ]);
    }

    public function storeConservationGoal(Request $request)
    {
        abort_unless($this->canManageChecklistTasks($request), 403);
        $validated = $request->validate([
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'goal_type' => ['required', 'in:daily,weekly,monthly,yearly'],
            'target_metric' => ['required', 'in:maximum_kwh,reduction_percent,cost_savings'],
            'target_value' => ['required', 'numeric', 'gt:0'],
            'baseline_start_date' => ['required', 'date'],
            'baseline_end_date' => ['required', 'date', 'after_or_equal:baseline_start_date'],
            'responsible_department' => ['required', 'string', 'max:255'],
            'action_plan' => ['required', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $allowedFacilityIds = $this->buildOverviewData($request)['facilities']->pluck('id')->map(fn ($id) => (int) $id);
        if (! empty($validated['facility_id'])) {
            abort_unless($allowedFacilityIds->contains((int) $validated['facility_id']), 403);
        }
        $validated['baseline_value'] = $this->calculateGoalBaseline(
            $allowedFacilityIds,
            isset($validated['facility_id']) ? (int) $validated['facility_id'] : null,
            $validated['baseline_start_date'],
            $validated['baseline_end_date'],
            $validated['goal_type'],
        );
        if ($validated['baseline_value'] <= 0) {
            return back()->withInput()->withErrors([
                'baseline_start_date' => 'No approved main-meter energy records were found for the selected facility and baseline period.',
            ]);
        }
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'active';
        ConservationGoal::create($validated);

        return redirect()->route('modules.energy-conservation.feature', ['feature' => 'conservation-goals'])
            ->with('success', 'Conservation goal created.');
    }

    public function destroyConservationGoal(Request $request, ConservationGoal $goal)
    {
        abort_unless($this->canManageChecklistTasks($request), 403);
        $goal->delete();

        return back()->with('success', 'Conservation goal removed.');
    }

    private function withGoalProgress(ConservationGoal $goal, array $overview): ConservationGoal
    {
        $row = $goal->facility_id
            ? $overview['rows']->firstWhere('facility_id', (int) $goal->facility_id)
            : null;
        $monthlyActual = (float) ($row['actual_kwh'] ?? $overview['totals']['actual_kwh'] ?? 0);
        $actual = round(match ($goal->goal_type) {
            'daily' => $monthlyActual / 30.4375,
            'weekly' => ($monthlyActual / 30.4375) * 7,
            'yearly' => $monthlyActual * 12,
            default => $monthlyActual,
        }, 2);
        $baseline = (float) ($goal->baseline_value ?: ($row['baseline_kwh'] ?? $overview['totals']['baseline_kwh'] ?? 0));
        $savedKwh = max(0, $baseline - $actual);
        $currentValue = match ($goal->target_metric) {
            'reduction_percent' => $baseline > 0 ? ($savedKwh / $baseline) * 100 : 0,
            'cost_savings' => $savedKwh * EnergyCost::DEFAULT_RATE_PER_KWH,
            default => $actual,
        };
        $progress = match ($goal->target_metric) {
            'maximum_kwh' => $baseline > (float) $goal->target_value
                ? (($baseline - $actual) / ($baseline - (float) $goal->target_value)) * 100
                : 0,
            default => (float) $goal->target_value > 0 ? ($currentValue / (float) $goal->target_value) * 100 : 0,
        };
        $progress = max(0, min(100, $progress));
        $met = $goal->target_metric === 'maximum_kwh'
            ? ($actual > 0 && $actual <= (float) $goal->target_value)
            : $currentValue >= (float) $goal->target_value;
        $effectiveStatus = $goal->status;
        if ($goal->status === 'active' && $goal->start_date->isFuture()) {
            $effectiveStatus = 'upcoming';
        } elseif ($goal->status === 'active' && $goal->end_date->isPast() && ! $goal->end_date->isToday()) {
            $effectiveStatus = $actual <= 0 ? 'expired' : ($met ? 'achieved' : 'failed');
        } elseif ($goal->status === 'active' && $actual > 0) {
            $totalDays = max(1, $goal->start_date->diffInDays($goal->end_date));
            $elapsedPercent = min(100, ($goal->start_date->diffInDays(now()) / $totalDays) * 100);
            if ($progress + 10 < $elapsedPercent) {
                $effectiveStatus = 'at risk';
            }
        }
        $tips = collect($goal->facility_id && $row ? [$row['recommendation'] ?? null] : $overview['rows']->pluck('recommendation'))
            ->filter()
            ->unique()
            ->take(3)
            ->values();
        if ($tips->isEmpty()) {
            $tips = collect([
                'Use LED lighting and maximize available natural light.',
                'Shut down computers and unplug unused devices after work.',
                'Keep air-conditioning between 24°C and 26°C.',
            ]);
        }

        $goal->setAttribute('current_value', round($currentValue, 2));
        $goal->setAttribute('current_kwh', round($actual, 2));
        $goal->setAttribute('energy_saved_kwh', round($savedKwh, 2));
        $goal->setAttribute('estimated_cost_saved', round($savedKwh * EnergyCost::DEFAULT_RATE_PER_KWH, 2));
        $goal->setAttribute('progress_percent', round($progress));
        $goal->setAttribute('effective_status', $effectiveStatus);
        $goal->setAttribute('energy_tips', $tips);
        $goal->setAttribute('data_source', 'Approved main-meter energy records for '.$overview['periodLabel']);
        $goal->setAttribute('last_updated_label', now()->format('M j, Y g:i A'));
        return $goal;
    }

    private function calculateGoalBaseline(Collection $allowedFacilityIds, ?int $facilityId, string $startDate, string $endDate, string $goalType): float
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();
        $records = EnergyRecord::query()
            ->whereIn('facility_id', $facilityId ? [$facilityId] : $allowedFacilityIds->all())
            ->whereHas('meter', fn ($query) => $query->where('meter_type', 'main')->whereNotNull('approved_at'))
            ->whereRaw('(year * 100 + month) between ? and ?', [
                ((int) $start->year * 100) + (int) $start->month,
                ((int) $end->year * 100) + (int) $end->month,
            ])
            ->get(['year', 'month', 'actual_kwh']);

        if ($records->isEmpty()) {
            return 0;
        }

        $averageMonthlyKwh = (float) $records
            ->groupBy(fn (EnergyRecord $record) => $record->year.'-'.str_pad((string) $record->month, 2, '0', STR_PAD_LEFT))
            ->map(fn (Collection $monthRecords) => (float) $monthRecords->sum('actual_kwh'))
            ->avg();

        return round(match ($goalType) {
            'daily' => $averageMonthlyKwh / 30.4375,
            'weekly' => ($averageMonthlyKwh / 30.4375) * 7,
            'yearly' => $averageMonthlyKwh * 12,
            default => $averageMonthlyKwh,
        }, 2);
    }

    public function updateDailyChecklist(Request $request)
    {
        abort_unless(RoleAccess::is($request->user(), 'staff'), 403, 'Only staff can complete the daily checklist.');
        $validated = $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'checklist_date' => ['required', 'date_format:Y-m-d'],
            'tasks' => ['nullable', 'array'],
            'tasks.*' => ['in:1'],
        ]);
        $allowedFacilityIds = $this->buildOverviewData($request)['facilities']->pluck('id')->map(fn ($id) => (int) $id);
        abort_unless($allowedFacilityIds->contains((int) $validated['facility_id']), 403);
        $completedKeys = array_keys($validated['tasks'] ?? []);

        DB::transaction(function () use ($validated, $completedKeys, $request) {
            foreach ($this->dailyChecklistTasks((int) $validated['facility_id']) as $task) {
                $completed = in_array($task['key'], $completedKeys, true);
                $record = DailyEnergyChecklist::firstOrNew([
                    'facility_id' => $validated['facility_id'],
                    'checklist_date' => $validated['checklist_date'],
                    'task_key' => $task['key'],
                ]);
                $record->fill(['task_label' => $task['label'], 'period' => $task['period'], 'is_completed' => $completed]);
                if ($completed && ! $record->completed_at) {
                    $record->completed_by = $request->user()->id;
                    $record->completed_at = now();
                } elseif (! $completed) {
                    $record->completed_by = null;
                    $record->completed_at = null;
                }
                $record->save();
            }
        });

        return redirect()->route('modules.energy-conservation.feature', [
            'feature' => 'daily-checklist',
            'facility_id' => $validated['facility_id'],
            'date' => $validated['checklist_date'],
        ])->with('success', 'Daily energy checklist saved.');
    }

    public function storeDailyChecklistTask(Request $request)
    {
        abort_unless($this->canManageChecklistTasks($request), 403);
        $validated = $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'task_label' => ['required', 'string', 'max:255'],
            'period' => ['required', 'in:opening,closing'],
            'return_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        DailyEnergyChecklistTask::create([
            'facility_id' => $validated['facility_id'],
            'task_key' => 'custom_'.str()->uuid(),
            'task_label' => $validated['task_label'],
            'period' => $validated['period'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('modules.energy-conservation.feature', [
            'feature' => 'daily-checklist', 'facility_id' => $validated['facility_id'],
            'date' => $validated['return_date'] ?? now()->toDateString(),
        ])->with('success', 'Checklist task added.');
    }

    public function destroyDailyChecklistTask(Request $request, DailyEnergyChecklistTask $task)
    {
        abort_unless($this->canManageChecklistTasks($request), 403);
        $task->update(['is_active' => false]);

        return back()->with('success', 'Checklist task removed.');
    }

    private function dailyChecklistTasks(int $facilityId): Collection
    {
        return DailyEnergyChecklistTask::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('facility_id')->orWhere('facility_id', $facilityId))
            ->orderByRaw("CASE WHEN period = 'opening' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get()
            ->map(fn (DailyEnergyChecklistTask $task) => [
                'id' => $task->id, 'key' => $task->task_key, 'period' => $task->period,
                'label' => $task->task_label, 'is_custom' => $task->facility_id !== null,
            ]);
    }

    private function isCprfIntegrationPeriod(int $facilityId, int $year, int $month, ?int $recordId = null): bool
    {
        $query = EnergyRecord::query()
            ->where('facility_id', $facilityId)
            ->where('year', $year)
            ->where('month', $month);

        if ($recordId !== null && $recordId > 0) {
            $query->whereKey($recordId);
        }

        return $query
            ->whereRaw('LOWER(input_source) = ?', ['cprf'])
            ->exists();
    }

    private function canManageChecklistTasks(Request $request): bool
    {
        return RoleAccess::in($request->user(), ['super_admin', 'admin', 'energy_officer', 'engineer']);
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
            'record_id' => ['nullable', 'integer'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'implementation_status' => ['nullable', 'in:pending,in_progress,implemented,verified'],
            'actual_savings_kwh' => ['nullable', 'numeric', 'min:0'],
            'implementation_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        [$year, $month] = array_map('intval', explode('-', $validated['period']));
        // Creating this record publishes advice only. Assignment and
        // implementation tracking are separate follow-up actions.
        $validated['assigned_to'] = null;

        $request->merge(['month' => $validated['period'], 'facility_id' => $validated['facility_id']]);
        $overview = $this->buildOverviewData($request);
        $tip = $this->energySavingTips($overview['rows'], (int) $validated['facility_id'], $overview['periodLabel'])->first();
        abort_unless($tip && ! empty($tip['facility_id']), 422, 'No monthly energy data is available for this facility.');

        $recommendation = EnergySavingRecommendation::create([
            'facility_id' => $validated['facility_id'],
            'year' => $year,
            'month' => $month,
            // Preserve the exact monthly assessment reviewed at publish time.
            // It remains attached to the recommendation even if a later month
            // changes the facility baseline.
            'generated_message' => $tip['assessment_message'] ?? $tip['message'],
            'engineer_recommendation' => $validated['engineer_recommendation'],
            'status' => $validated['status'],
            'expected_savings_kwh' => null,
            'target_date' => null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'implementation_status' => 'pending',
            'actual_savings_kwh' => null,
            'implementation_notes' => null,
            'implemented_at' => null,
            'verified_by' => null,
            'verified_at' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        try {
            app(RecommendationNotificationService::class)->notifyManualRecommendation($recommendation);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('modules.energy-conservation.feature', array_filter([
            'feature' => 'energy-saving-tips',
            'month' => $validated['period'],
            'facility_id' => $validated['facility_id'],
            'record_id' => $validated['record_id'] ?? null,
        ], fn ($value) => $value !== null))->with('success', 'Recommendation added successfully.');
    }

    public function updateEnergyTip(Request $request, EnergySavingRecommendation $recommendation)
    {
        if (! RoleAccess::in($request->user(), ['super_admin', 'admin', 'energy_officer', 'engineer'])) {
            abort(403, 'Only Engineering, Energy Officers, or administrators can update recommendations.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:for_review,approved,dismissed'],
            'engineer_recommendation' => ['required', 'string', 'max:3000'],
        ]);

        $recommendation->update([
            'engineer_recommendation' => $validated['engineer_recommendation'],
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Recommendation changes saved successfully.');
    }

    public function updateEnergyTipProgress(Request $request, EnergySavingRecommendation $recommendation)
    {
        abort_unless(
            RoleAccess::is($request->user(), 'staff'),
            403,
            'Only the assigned staff member can update implementation progress.'
        );
        abort_unless(
            (int) $recommendation->assigned_to === (int) $request->user()->id,
            403,
            'This recommendation is assigned to another staff member.'
        );
        abort_unless(
            $recommendation->status === 'approved',
            403,
            'Only approved recommendations can be implemented.'
        );
        abort_if(
            $recommendation->implementation_status === 'verified',
            403,
            'Verified recommendations are locked.'
        );

        $hasFacilityAccess = $request->user()
            ->facilities()
            ->whereKey((int) $recommendation->facility_id)
            ->exists();
        abort_unless($hasFacilityAccess, 403, 'You do not have access to this facility.');

        $validated = $request->validate([
            'implementation_status' => ['required', 'in:pending,in_progress,implemented'],
            'actual_savings_kwh' => ['nullable', 'numeric', 'min:0'],
            'implementation_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $isImplemented = $validated['implementation_status'] === 'implemented';
        $recommendation->update([
            'implementation_status' => $validated['implementation_status'],
            'actual_savings_kwh' => $validated['actual_savings_kwh'] ?? null,
            'implementation_notes' => $validated['implementation_notes'] ?? null,
            'implemented_at' => $isImplemented ? ($recommendation->implemented_at ?? now()) : null,
        ]);

        return back()->with('success', 'Implementation progress updated successfully.');
    }

    public function destroyEnergyTip(Request $request, EnergySavingRecommendation $recommendation)
    {
        if (! RoleAccess::in($request->user(), ['super_admin', 'admin', 'energy_officer', 'engineer'])) {
            abort(403, 'Only Engineering, Energy Officers, or administrators can delete recommendations.');
        }

        $recommendation->delete();

        return back()->with('success', 'Recommendation deleted successfully.');
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

        [$year, $month, $selectedMonth, $periodLabel] = $this->resolveMonth($request->input('month'));
        $facilityScope = $this->facilityScope($request);

        $facilities = Facility::query()
            ->with('energyProfiles:id,facility_id,baseline_kwh')
            ->when($facilityScope !== null, fn ($query) => $query->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'baseline_kwh', 'status']);

        $facilityIds = $facilities->pluck('id')->map(fn ($id) => (int) $id)->all();
        $facilityMap = $facilities->keyBy('id');

        $records = EnergyRecord::query()
            ->with('meter:id,facility_id,meter_name,meter_type,baseline_kwh')
            ->whereIn('facility_id', $facilityIds)
            ->where('year', $year)
            ->where('month', $month)
            ->where(function ($query) {
                $query
                    ->whereHas('meter', fn ($meterQuery) => $meterQuery->where('meter_type', 'main'))
                    ->orWhere(function ($cprfQuery) {
                        $cprfQuery
                            ->whereNull('meter_id')
                            ->where('input_source', 'cprf');
                    });
            })
            ->get(['id', 'facility_id', 'meter_id', 'input_source', 'year', 'month', 'actual_kwh', 'baseline_kwh', 'energy_cost', 'rate_per_kwh']);

        $rows = $records
            ->groupBy('facility_id')
            ->map(function (Collection $facilityRecords, int $facilityId) use ($facilityMap, $selectedMonth) {
                $facility = $facilityMap->get($facilityId);
                $actualKwh = (float) $facilityRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0));
                $baselineKwh = (float) $facilityRecords->sum(
                    fn (EnergyRecord $record) => BaselineResolver::forRecord($record, $facility) ?? 0
                );

                $energyCost = (float) $facilityRecords->sum(fn ($record) => EnergyCost::cost($record));
                $resolvedRate = $actualKwh > 0 ? ($energyCost / $actualKwh) : EnergyCost::DEFAULT_RATE_PER_KWH;
                $hasBaseline = $baselineKwh > 0;
                $excessKwh = $hasBaseline ? max(0, $actualKwh - $baselineKwh) : null;
                $avoidableCost = $hasBaseline ? $excessKwh * $resolvedRate : null;
                $deviation = EnergyRecord::calculateDeviation($actualKwh, $baselineKwh);
                $alertLevel = $hasBaseline ? EnergyRecord::resolveAlertLevel($deviation, $baselineKwh) : 'No Data';

                return [
                    'facility_id' => $facilityId,
                    'facility_name' => (string) ($facility?->name ?? 'Facility #' . $facilityId),
                    'facility_type' => (string) ($facility?->type ?? 'Facility'),
                    'actual_kwh' => round($actualKwh, 2),
                    'baseline_kwh' => round($baselineKwh, 2),
                    'has_baseline' => $hasBaseline,
                    'excess_kwh' => $excessKwh !== null ? round($excessKwh, 2) : null,
                    'avoidable_cost' => $avoidableCost !== null ? round($avoidableCost, 2) : null,
                    'energy_cost' => round($energyCost, 2),
                    'rate_per_kwh' => round($resolvedRate, 2),
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

        $totals = array_merge(
            ['facilities' => $facilities->count()],
            $this->summarizeEnergyRows($rows),
        );

        return [
            'selectedMonth' => $selectedMonth,
            'periodLabel' => $periodLabel,
            'facilities' => $facilities,
            'rows' => $rows,
            'totals' => $totals,
            'facilitiesWithoutCurrentRecord' => $facilitiesWithoutCurrentRecord,
        ];
    }

    private function summarizeEnergyRows(Collection $rows): array
    {
        $rowsWithBaseline = $rows->filter(fn (array $row) => (bool) ($row['has_baseline'] ?? false));

        return [
            'monitored_facilities' => $rows->count(),
            'actual_kwh' => round((float) $rows->sum('actual_kwh'), 2),
            'baseline_kwh' => round((float) $rowsWithBaseline->sum('baseline_kwh'), 2),
            'baseline_ready_facilities' => $rowsWithBaseline->count(),
            'excess_kwh' => $rowsWithBaseline->isNotEmpty()
                ? round((float) $rowsWithBaseline->sum('excess_kwh'), 2)
                : null,
            'avoidable_cost' => $rowsWithBaseline->isNotEmpty()
                ? round((float) $rowsWithBaseline->sum('avoidable_cost'), 2)
                : null,
            'priority_count' => $rows->filter(
                fn (array $row) => in_array($row['alert_level'], ['High', 'Very High', 'Critical'], true)
            )->count(),
        ];
    }

    private function featureCatalog(): array
    {
        return [
            'energy-saving-tips' => [
                'title' => 'Energy Recommendations',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-sun',
                'description' => 'Review monthly energy context and publish practical recommendations for each facility.',
            ],
            'conservation-goals' => [
                'title' => 'Conservation Goals',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-bullseye',
                'description' => 'Set and monitor energy reduction targets.',
            ],
            'daily-checklist' => [
                'title' => 'Daily Checklist',
                'badge' => 'Enabled',
                'status' => 'enabled',
                'icon' => 'fa-solid fa-clipboard-check',
                'description' => 'Track daily energy-saving practices and routines.',
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
                'message' => "No usable energy record was found for {$periodLabel}. Add a monthly main-meter reading first; new facilities can build an approved baseline from 3–6 months of data.",
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
                $details = [
                    'actual_kwh' => round($actual, 2),
                    'baseline_kwh' => $baseline > 0 ? round($baseline, 2) : null,
                    'difference_kwh' => $baseline > 0 ? round($actual - $baseline, 2) : null,
                    'deviation_percent' => $deviation !== null ? round((float) $deviation, 2) : null,
                    'avoidable_cost' => $baseline > 0 ? round($avoidableCost, 2) : null,
                    'energy_cost' => round((float) ($row['energy_cost'] ?? 0), 2),
                    'rate_per_kwh' => round((float) ($row['rate_per_kwh'] ?? 0), 2),
                    'status' => $this->recommendationAssessmentStatus($row['alert_level'] ?? null, $deviation, $baseline),
                    'alert_level' => (string) ($row['alert_level'] ?? 'No Data'),
                    'action_owner' => EnergyAlertRouting::owner((string) ($row['alert_level'] ?? 'No Data')),
                ];
                $assessmentMessage = $this->recommendationAssessmentMessage(
                    $facilityName,
                    $periodLabel,
                    $details,
                );

                if ($baseline <= 0 || $deviation === null) {
                    return [
                        'priority' => 'Set baseline',
                        'tone' => 'info',
                        'icon' => 'fa-solid fa-gauge-high',
                        'title' => 'Establish a reliable consumption baseline',
                        'assessment_message' => $assessmentMessage,
                        'details' => $details,
                        'message' => "{$facilityName} used " . number_format($actual, 2) . " kWh in {$periodLabel}. Its baseline is still being established from 3–6 approved monthly readings, so no reduction target or savings estimate is calculated yet. {$operationalAction}",
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
                        'assessment_message' => $assessmentMessage,
                        'details' => $details,
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
                        'assessment_message' => $assessmentMessage,
                        'details' => $details,
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
                        'assessment_message' => $assessmentMessage,
                        'details' => $details,
                        'message' => "{$facilityName} is slightly above baseline by " . number_format((float) $deviation, 2) . "%. {$operationalAction} Compare weekly readings to confirm the adjustment works.",
                        'facility_name' => $facilityName,
                        'facility_id' => $row['facility_id'],
                        'metric' => number_format($excess, 2) . ' excess kWh',
                    ];
                }

                $savedKwh = max(0, $baseline - $actual);

                if ($deviation <= -20 || in_array($row['alert_level'] ?? null, ['Drop High', 'Drop Critical'], true)) {
                    return [
                        'priority' => 'Verify reading',
                        'tone' => 'watch',
                        'icon' => 'fa-solid fa-magnifying-glass-chart',
                        'title' => 'Validate the unusually low consumption',
                        'assessment_message' => $assessmentMessage,
                        'details' => $details,
                        'message' => "{$facilityName} is substantially below baseline for {$periodLabel}. Confirm the meter reading and check for outages, reduced operating hours, or equipment downtime before treating the reduction as sustained savings. If the reading is valid, document the operating changes that caused it.",
                        'facility_name' => $facilityName,
                        'facility_id' => $row['facility_id'],
                        'metric' => number_format($savedKwh, 2) . ' kWh below baseline',
                    ];
                }

                return [
                    'priority' => 'Maintain',
                    'tone' => 'success',
                    'icon' => 'fa-solid fa-leaf',
                    'title' => 'Maintain the current energy controls',
                    'assessment_message' => $assessmentMessage,
                    'details' => $details,
                    'message' => "{$facilityName} is within or below baseline for {$periodLabel}. Keep the current operating schedule and check for rebound consumption next month.",
                    'facility_name' => $facilityName,
                    'facility_id' => $row['facility_id'],
                    'metric' => number_format($savedKwh, 2) . ' kWh below baseline',
                ];
            })
            ->values();
    }

    private function recommendationAssessmentStatus(?string $alertLevel, ?float $deviation, float $baseline): string
    {
        if ($baseline <= 0 || $deviation === null) {
            return 'No baseline yet';
        }

        if (in_array($alertLevel, ['Critical', 'Very High'], true) || $deviation >= 20) {
            return 'Very high consumption';
        }

        if ($alertLevel === 'High' || $deviation >= 10) {
            return 'High consumption';
        }

        if (in_array($alertLevel, ['Drop High', 'Drop Critical'], true) || $deviation <= -20) {
            return 'Very low consumption';
        }

        if ($deviation > 0) {
            return 'Slightly above baseline';
        }

        if ($deviation < 0) {
            return 'Below baseline';
        }

        return 'Within baseline';
    }

    private function recommendationAssessmentMessage(
        string $facilityName,
        string $periodLabel,
        array $details,
    ): string {
        $message = "Monthly record assessment for {$facilityName}, {$periodLabel}: Actual usage "
            . number_format((float) $details['actual_kwh'], 2)
            . " kWh; monthly energy cost PHP "
            . number_format((float) $details['energy_cost'], 2)
            . " at PHP "
            . number_format((float) $details['rate_per_kwh'], 2)
            . "/kWh. Status: {$details['status']}.";

        if ($details['baseline_kwh'] === null) {
            return $message
                . ' No approved baseline is available yet, so the difference, percentage deviation, and avoidable cost cannot be estimated.';
        }

        $difference = (float) $details['difference_kwh'];
        $direction = $difference > 0 ? 'above' : ($difference < 0 ? 'below' : 'equal to');

        $message .= ' Approved baseline '
            . number_format((float) $details['baseline_kwh'], 2)
            . ' kWh; usage is '
            . number_format(abs($difference), 2)
            . ' kWh ('
            . number_format(abs((float) $details['deviation_percent']), 2)
            . "%) {$direction} baseline.";

        if ((float) $details['avoidable_cost'] > 0) {
            $message .= ' Estimated avoidable cost: PHP '
                . number_format((float) $details['avoidable_cost'], 2)
                . '.';
        } else {
            $message .= ' No excess-cost estimate because usage did not exceed the baseline.';
        }

        return $message;
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
