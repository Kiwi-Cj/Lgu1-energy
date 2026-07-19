<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\DailyChecklistItem;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use App\Support\EnergyCost;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

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
        $selectedFacility = $overview['facilities']->firstWhere('id', $selectedFacilityId)
            ?? $overview['facilities']->first();
        $selectedFacilityId = (int) ($selectedFacility?->id ?? $selectedFacilityId);
        $featureData = $this->featureData($feature, $overview, $selectedFacilityId);
        $selectedTab = (string) $request->query('tab', 'tasks');

        return view('modules.energy-conservation.feature', [
            'selectedMonth' => $overview['selectedMonth'],
            'featureSlug' => $feature,
            'feature' => $selected,
            'featureCatalog' => $features,
            'overview' => $overview,
            'selectedFacility' => $selectedFacility,
            'selectedFacilityId' => $selectedFacilityId,
            'selectedTab' => $selectedTab,
            'featureData' => $featureData,
            'dailyChecklistTasks' => $this->dailyChecklistTasks($overview['selectedMonth'], $selectedFacilityId),
            'dailyChecklistReviewTasks' => $this->dailyChecklistReviewTasks($overview['selectedMonth'], $selectedFacilityId),
            'dailyChecklistCompletedTasks' => $this->dailyChecklistCompletedTasks($overview['selectedMonth'], $selectedFacilityId),
            'newTaskId' => (int) $request->session()->pull('new_task_id', 0),
        ]);
    }

    public function saveDailyChecklist(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'submit_conservation_progress')) {
            abort(403);
        }

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'facility_id' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['nullable', 'in:Done,Pending,Overdue'],
        ]);

        $state = [
            'remarks' => trim((string) ($validated['remarks'] ?? '')),
            'statuses' => array_filter((array) ($validated['statuses'] ?? []), fn ($status) => in_array($status, ['Done', 'Pending', 'Overdue'], true)),
            'saved_at' => now()->toDateTimeString(),
            'saved_by' => $user?->id,
        ];

        Setting::setValue($this->dailyChecklistStateKey($validated['month'], (int) $validated['facility_id']), json_encode($state));

        return redirect()
            ->route('modules.energy-conservation.feature', ['feature' => 'daily-checklist', 'month' => $validated['month'], 'facility_id' => (int) $validated['facility_id']])
            ->with('success', 'Daily checklist saved successfully.');
    }

    public function storeDailyTask(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'manage_conservation_tasks')) {
            abort(403);
        }

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'facility_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:Low,Medium,High'],
            'photo_requirement' => ['required', 'in:Optional,Required'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $task = DailyChecklistItem::create([
            'facility_id' => (int) $validated['facility_id'],
            'issue_type' => $validated['title'],
            'trigger_month' => $validated['month'],
            'maintenance_status' => 'Pending',
            'scheduled_date' => $validated['due_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'remarks' => trim((string) ($validated['remarks'] ?? '')) . ($validated['remarks'] ? "\n" : '') . 'Priority: ' . $validated['priority'],
        ]);

        $task->photo_requirement = $validated['photo_requirement'];
        $task->save();

        return redirect()
            ->route('modules.energy-conservation.feature', ['feature' => 'daily-checklist', 'month' => $validated['month'], 'facility_id' => (int) $validated['facility_id']])
            ->with('success', 'Checklist item added successfully.')
            ->with('new_task_id', $task->id);
    }

    public function completeDailyTask(Request $request, DailyChecklistItem $task)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'approve_conservation_tasks')) {
            abort(403);
        }

        $this->authorizeDailyTask($task, $request);

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'facility_id' => ['required', 'integer', 'min:1'],
            'approval_remarks' => ['nullable', 'string', 'max:2000'],
            'approval_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $approvalRemarks = trim((string) ($validated['approval_remarks'] ?? ''));
        if ($request->hasFile('approval_photo')) {
            $path = $request->file('approval_photo')->store('checklist-proofs', 'public');
            $task->proof_photo_path = $path;
        }
        if ($approvalRemarks !== '') {
            $existingRemarks = trim((string) ($task->remarks ?? ''));
            $approvalNote = 'Admin approval remarks by ' . ($user?->full_name ?? $user?->name ?? 'admin') . ' on ' . now()->format('M d, Y h:i A');
            $task->remarks = $existingRemarks !== ''
                ? $existingRemarks . "\n\n" . $approvalNote . "\n" . $approvalRemarks
                : $approvalNote . "\n" . $approvalRemarks;
        }

        $task->maintenance_status = 'Completed';
        $task->completed_date = now()->toDateString();
        $task->save();

        return back()->with('success', 'Checklist item approved and marked as completed.');
    }

    public function submitDailyTaskProgress(Request $request, DailyChecklistItem $task)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'submit_conservation_progress')) {
            abort(403);
        }

        $this->authorizeDailyTask($task, $request);

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'facility_id' => ['required', 'integer', 'min:1'],
            'progress' => ['required', 'string', 'max:2000'],
            'progress_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $photoRequirement = (string) ($task->photo_requirement ?? 'Optional');
        if ($photoRequirement === 'Required' && ! $request->hasFile('progress_photo')) {
            return back()
                ->withErrors(['progress_photo' => 'This checklist item requires a photo before submitting progress.'])
                ->withInput();
        }

        $progressNote = trim((string) $validated['progress']);
        $prefix = 'Progress update by ' . ($user?->full_name ?? $user?->name ?? 'staff') . ' on ' . now()->format('M d, Y h:i A');

        if ($request->hasFile('progress_photo')) {
            $path = $request->file('progress_photo')->store('checklist-proofs', 'public');
            $task->proof_photo_path = $path;
        }

        $existingRemarks = trim((string) ($task->remarks ?? ''));
        $task->remarks = $existingRemarks !== ''
            ? $existingRemarks . "\n\n" . $prefix . "\n" . $progressNote
            : $prefix . "\n" . $progressNote;
        $task->maintenance_status = 'For Review';
        $task->save();

        $this->notifyAdminReviewQueue($task, $user, $progressNote);

        return redirect()
            ->route('modules.energy-conservation.feature', [
                'feature' => 'daily-checklist',
                'month' => $validated['month'],
                'facility_id' => (int) $validated['facility_id'],
            ])
            ->with('success', 'Progress submitted for admin review.');
    }

    public function deleteDailyTask(Request $request, DailyChecklistItem $task)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'manage_conservation_tasks')) {
            abort(403);
        }

        $this->authorizeDailyTask($task, $request);

        $task->delete();

        return back()->with('success', 'Checklist item deleted successfully.');
    }

    private function authorizeDailyTask(DailyChecklistItem $task, Request $request): void
    {
        $month = (string) $request->query('month', $task->trigger_month ?? now()->format('Y-m'));
        $facilityId = (int) $request->query('facility_id', $task->facility_id ?? 0);

        if ((string) $task->trigger_month !== $month || (int) $task->facility_id !== $facilityId) {
            abort(403);
        }
    }

    private function renderOverview(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'access_energy_conservation')) {
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
        if (! RoleAccess::can($user, 'access_energy_conservation')) {
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
                'description' => 'Nagpapakita ng mga tip para makatipid sa kuryente.',
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

    private function featureData(string $feature, array $overview, int $facilityId = 0): array
    {
        if ($feature !== 'daily-checklist') {
            return [];
        }

        $tasks = $this->dailyChecklistTasks($overview['selectedMonth'], $facilityId);
        $activeTasks = $tasks->where('maintenance_status', '!=', 'Completed');
        $taskCount = $activeTasks->count();
        $completedCount = $tasks->where('maintenance_status', 'Completed')->count();
        $dueThisMonthCount = $tasks->filter(fn (DailyChecklistItem $task) => ! empty($task->scheduled_date))->count();
        $latestTask = $activeTasks->first() ?? $tasks->first();
        $latestTaskLabel = $latestTask
            ? trim((string) ($latestTask->issue_type ?: 'Checklist item') . ' - ' . $latestTask->created_at?->format('M d, Y'))
            : 'No checklist items yet';

        return [
            'taskSummary' => [
                ['label' => 'Active Checklist Items', 'value' => (string) $taskCount],
                ['label' => 'Completed', 'value' => (string) $completedCount],
                ['label' => 'Due This Month', 'value' => (string) $dueThisMonthCount],
                ['label' => 'Latest Added', 'value' => $latestTaskLabel],
            ],
        ];
    }

    private function dailyChecklistTasks(string $month, int $facilityId): Collection
    {
        return DailyChecklistItem::query()
            ->where('facility_id', $facilityId)
            ->where('trigger_month', $month)
            ->latest()
            ->get(['id', 'facility_id', 'maintenance_status', 'scheduled_date', 'assigned_to', 'remarks', 'created_at']);
    }

    private function dailyChecklistReviewTasks(string $month, int $facilityId): Collection
    {
        return DailyChecklistItem::query()
            ->where('facility_id', $facilityId)
            ->where('trigger_month', $month)
            ->where('maintenance_status', 'For Review')
            ->latest()
            ->get(['id', 'facility_id', 'issue_type', 'maintenance_status', 'scheduled_date', 'assigned_to', 'remarks', 'created_at']);
    }

    private function dailyChecklistCompletedTasks(string $month, int $facilityId): Collection
    {
        return DailyChecklistItem::query()
            ->where('facility_id', $facilityId)
            ->where('trigger_month', $month)
            ->where('maintenance_status', 'Completed')
            ->latest()
            ->get(['id', 'facility_id', 'issue_type', 'maintenance_status', 'scheduled_date', 'assigned_to', 'remarks', 'created_at']);
    }

    private function notifyAdminReviewQueue(DailyChecklistItem $task, ?User $actor, string $progressNote): void
    {
        try {
            if (! class_exists(Notification::class) || ! class_exists(User::class)) {
                return;
            }

            $facility = $task->facility()->first(['id', 'name']);
            $facilityName = (string) ($facility?->name ?? 'Unknown Facility');
            $title = 'Checklist item submitted for review';
            $message = 'Checklist item "' . (string) ($task->issue_type ?: 'Energy task') . '" for ' . $facilityName .
                ' was submitted by ' . (string) ($actor?->full_name ?? $actor?->name ?? 'staff') .
                '. ' . $progressNote;

            User::query()
                ->get()
                ->filter(fn (User $user) => RoleAccess::in($user, ['super_admin', 'admin']))
                ->each(function (User $user) use ($message, $title, $task) {
                    $exists = $user->notifications()
                        ->where('type', 'maintenance')
                        ->where('message', $message)
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();

                    if ($exists) {
                        return;
                    }

                    $user->notifications()->create([
                        'title' => $title,
                        'message' => $message,
                        'type' => 'maintenance',
                        'target_url' => route('modules.energy-conservation.feature', [
                            'feature' => 'daily-checklist',
                            'month' => (string) ($task->trigger_month ?? now()->format('Y-m')),
                            'facility_id' => (int) ($task->facility_id ?? 0),
                        ]),
                    ]);
                });
        } catch (\Throwable) {
            // Keep the progress submission flow working even if notification creation fails.
        }
    }

    private function dailyChecklistState(string $month, int $facilityId = 0): array
    {
        $raw = $facilityId > 0 ? Setting::getValue($this->dailyChecklistStateKey($month, $facilityId), '') : '';
        if (! is_string($raw) || trim($raw) === '') {
            return ['remarks' => '', 'statuses' => []];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['remarks' => '', 'statuses' => []];
        }

        $decoded['remarks'] = (string) ($decoded['remarks'] ?? '');
        $decoded['statuses'] = array_values(array_filter((array) ($decoded['statuses'] ?? []), fn ($status) => in_array($status, ['Done', 'Pending', 'Overdue'], true)));

        return $decoded;
    }

    private function dailyChecklistStateKey(string $month, int $facilityId): string
    {
        return 'energy_conservation_daily_checklist_state_' . $month . '_facility_' . $facilityId;
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

}
