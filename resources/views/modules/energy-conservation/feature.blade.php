@extends('layouts.qc-admin')
@section('title', $feature['title'] ?? 'Energy Conservation Feature')

@section('content')
@php
    $feature = $feature ?? [];
    $overview = $overview ?? [];
    $facilities = $overview['facilities'] ?? collect();
    $rows = $overview['rows'] ?? collect();
    $totals = $overview['totals'] ?? [];
    $topFacility = $overview['topFacility'] ?? null;
    $averageDeviation = $overview['averageDeviation'] ?? null;
    $latestContactSuggestions = $overview['latestContactSuggestions'] ?? collect();
    $selectedFacility = $selectedFacility ?? null;
    $selectedFacilityId = (int) ($selectedFacilityId ?? 0);
    $featureStatus = $feature['status'] ?? 'enabled';
    $featureBadge = $feature['badge'] ?? 'Enabled';
    $featureData = $featureData ?? [];
    $taskSummary = $featureData['taskSummary'] ?? [];
    $dailyChecklistTasks = collect($dailyChecklistTasks ?? []);
    $dailyChecklistReviewTasks = collect($dailyChecklistReviewTasks ?? []);
    $dailyChecklistCompletedTasks = collect($dailyChecklistCompletedTasks ?? []);
    $selectedFacilityLabel = $selectedFacility ? ($selectedFacility->name . ($selectedFacility->type ? ' - ' . $selectedFacility->type : '')) : 'All Facilities';
    $newTaskId = session('new_task_id');
    $canApproveTasks = \App\Support\RoleAccess::in(auth()->user(), ['super_admin', 'admin']);
    $canCreateTasks = \App\Support\RoleAccess::in(auth()->user(), ['super_admin', 'admin', 'energy_officer']);
    $canDeleteTasks = \App\Support\RoleAccess::in(auth()->user(), ['super_admin', 'admin', 'energy_officer']);
    $selectedTab = (string) ($selectedTab ?? 'tasks');
@endphp

<style>
    .feature-shell {
        width: 100%;
        margin: 0;
        padding: 28px 34px 36px;
        border-radius: 24px;
        background: linear-gradient(135deg, #ffffff, #eff6ff);
        border: 1px solid #dbe4f0;
        box-shadow: 0 12px 40px rgba(37, 99, 235, .12);
        display: grid;
        gap: 18px;
    }
    .feature-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .feature-kicker {
        color: #2563eb;
        font-size: .78rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .feature-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.5rem, 2.2vw, 2.25rem);
        font-weight: 900;
    }
    .feature-desc {
        margin-top: 8px;
        max-width: 900px;
        color: #475569;
        line-height: 1.5;
    }
    .feature-status {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .feature-status.enabled { background: #dcfce7; color: #166534; }
    .feature-status.coming-soon { background: #fef3c7; color: #92400e; }
    .feature-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e40af;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .02em;
    }
    .feature-chip i { font-size: .8rem; }
    .task-toast {
        position: sticky;
        top: 12px;
        z-index: 20;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px solid #bbf7d0;
        background: #dcfce7;
        color: #166534;
        font-weight: 800;
    }
    .task-toast.show { display: flex; }
    .task-toast button {
        border: 0;
        background: transparent;
        color: inherit;
        cursor: pointer;
        font-size: .95rem;
    }
    .recent-task {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px rgba(37, 99, 235, .18), 0 14px 30px rgba(37, 99, 235, .12);
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        animation: highlightPulse 1.3s ease-in-out 1;
    }
    body.dark-mode .recent-task {
        border-color: #60a5fa;
        background: linear-gradient(135deg, #111827, #0f172a);
    }
    @keyframes highlightPulse {
        0% { transform: scale(0.995); }
        50% { transform: scale(1.01); }
        100% { transform: scale(1); }
    }
    .task-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px 16px;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        background: #f8fbff;
    }
    .task-toolbar .help-text {
        margin: 0;
    }
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        z-index: 1000;
    }
    .modal-backdrop.open {
        display: flex;
    }
    .modal-card {
        width: min(720px, 100%);
        max-height: 90vh;
        overflow: auto;
        border-radius: 22px;
        background: #fff;
        border: 1px solid #dbe4f0;
        box-shadow: 0 24px 80px rgba(15, 23, 42, .28);
    }
    .modal-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .modal-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 900;
        color: #0f172a;
    }
    .modal-close {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 1.1rem;
        cursor: pointer;
    }
    .modal-body {
        padding: 20px;
    }
    body.dark-mode .modal-card {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .modal-head {
        border-color: #334155;
    }
    body.dark-mode .modal-title {
        color: #f8fafc;
    }
    body.dark-mode .modal-close {
        color: #cbd5e1;
    }
    body.dark-mode .task-toolbar {
        background: #0f172a;
        border-color: #334155;
    }
    .feature-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
        align-items: start;
    }
    .panel {
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        width: 100%;
    }
    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e2e8f0;
    }
    .panel-title {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
    }
    .panel-note {
        color: #64748b;
        font-size: .8rem;
        font-weight: 600;
    }
    .panel-body {
        padding: 18px;
        display: grid;
        gap: 14px;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1e40af;
        font-weight: 900;
        text-decoration: none;
    }
    .back-link:hover { text-decoration: underline; }
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 10px;
    }
    .stat-card {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid #dbe4f0;
        background: #f8fbff;
    }
    .stat-label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .stat-value {
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
        margin-top: 5px;
        line-height: 1.25;
        word-break: break-word;
    }
    .stat-sub {
        color: #64748b;
        font-size: .82rem;
        margin-top: 3px;
        line-height: 1.35;
    }
    .form-grid {
        display: grid;
        gap: 12px;
    }
    .field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: .78rem;
        font-weight: 800;
    }
    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fff;
        color: #0f172a;
    }
    .field textarea {
        min-height: 120px;
        resize: vertical;
    }
    .help-text {
        margin-top: 6px;
        color: #64748b;
        font-size: .76rem;
        line-height: 1.35;
    }
    .action-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-main {
        border: 0;
        border-radius: 12px;
        background: #2563eb;
        color: #fff;
        padding: 10px 14px;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .btn-secondary {
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #1e40af;
    }
    .feature-list {
        display: grid;
        gap: 10px;
    }
    .feature-point {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fbff;
        color: #334155;
        line-height: 1.45;
    }
    .feature-point i {
        color: #2563eb;
        margin-top: 2px;
    }
    .table-wrap {
        overflow-x: auto;
    }
    .simple-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 680px;
    }
    .simple-table th,
    .simple-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        vertical-align: top;
    }
    .simple-table th {
        color: #475569;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #f8fafc;
    }
    .pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eef2ff;
        color: #1e40af;
        font-size: .7rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .suggestion-list {
        display: grid;
        gap: 10px;
    }
    .suggestion-item {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        background: #f8fbff;
    }
    .suggestion-name {
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 3px;
    }
    .suggestion-meta {
        color: #64748b;
        font-size: .78rem;
        margin-bottom: 6px;
    }
    .suggestion-body {
        color: #334155;
        font-size: .9rem;
        line-height: 1.45;
    }
    .checklist-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }
    .checklist-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        background: #f8fbff;
    }
    .checklist-item.task-collapsed {
        cursor: pointer;
    }
    .checklist-item.task-collapsed:hover {
        border-color: #bfd7ff;
        background: #f3f8ff;
    }
    .checklist-check {
        width: 26px;
        height: 26px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 26px;
        margin-top: 2px;
        color: #fff;
        font-size: .75rem;
    }
    .checklist-check.done { background: #2563eb; }
    .checklist-check.pending { background: #f59e0b; }
    .checklist-check.for-review { background: #64748b; }
    .checklist-check.overdue { background: #ef4444; }
    .checklist-main {
        flex: 1;
        min-width: 0;
    }
    .task-card-body {
        display: grid;
        gap: 10px;
    }
    .task-extra {
        display: none;
        grid-template-columns: 1fr;
        gap: 10px;
        padding-top: 4px;
    }
    .checklist-item.task-expanded .task-extra {
        display: grid;
    }
    .checklist-title-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }
    .checklist-title {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.35;
    }
    .checklist-meta {
        margin-top: 4px;
        color: #64748b;
        font-size: .8rem;
    }
    .task-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
    .task-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e40af;
        font-size: .72rem;
        font-weight: 800;
        line-height: 1;
    }
    .task-tag.muted {
        background: #f1f5f9;
        color: #475569;
    }
    .task-tag.required-photo {
        background: #fee2e2;
        color: #991b1b;
    }
    .task-tag.required-photo i {
        color: #dc2626;
    }
    .task-progress-form {
        margin-top: 4px;
        padding: 12px;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        background: #fff;
    }
    .task-progress-form .field {
        margin-bottom: 10px;
    }
    .task-progress-form .field textarea {
        min-height: 92px;
    }
    .task-action-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .task-action-row .btn-main {
        padding: 9px 13px;
        border-radius: 11px;
    }
    .task-expand-hint {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
        color: #2563eb;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .checklist-item.task-expanded .task-expand-hint i {
        transform: rotate(180deg);
    }
    .task-expand-hint i {
        transition: transform .18s ease;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .status-chip.done { background: #dcfce7; color: #166534; }
    .status-chip.pending { background: #fef3c7; color: #92400e; }
    .status-chip.for-review { background: #e2e8f0; color: #334155; }
    .status-chip.overdue { background: #fee2e2; color: #991b1b; }
    body.dark-mode .feature-shell,
    body.dark-mode .panel,
    body.dark-mode .stat-card,
    body.dark-mode .field input,
    body.dark-mode .field select,
    body.dark-mode .field textarea,
    body.dark-mode .feature-point,
    body.dark-mode .suggestion-item {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .feature-title,
    body.dark-mode .panel-title,
    body.dark-mode .stat-value,
    body.dark-mode .suggestion-name {
        color: #f8fafc;
    }
    body.dark-mode .feature-desc,
    body.dark-mode .panel-note,
    body.dark-mode .stat-label,
    body.dark-mode .stat-sub,
    body.dark-mode .help-text,
    body.dark-mode .suggestion-meta,
    body.dark-mode .suggestion-body,
    body.dark-mode .feature-point,
    body.dark-mode .field label {
        color: #cbd5e1;
    }
    body.dark-mode .checklist-item {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .checklist-item.task-collapsed:hover {
        background: #111827;
        border-color: #475569;
    }
    body.dark-mode .checklist-title {
        color: #f8fafc;
    }
    body.dark-mode .checklist-meta {
        color: #94a3b8;
    }
    body.dark-mode .task-progress-form {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .task-tag {
        background: #1e293b;
        color: #bfdbfe;
    }
    body.dark-mode .task-tag.muted {
        background: #0f172a;
        color: #cbd5e1;
    }
    body.dark-mode .task-tag.required-photo {
        background: #3f1d1d;
        color: #fecaca;
    }
    body.dark-mode .task-tag.required-photo i {
        color: #f87171;
    }
    body.dark-mode .task-expand-hint {
        color: #93c5fd;
    }
    body.dark-mode .feature-chip {
        background: #1e293b;
        color: #bfdbfe;
    }
    body.dark-mode .simple-table th {
        background: #111827;
        color: #cbd5e1;
    }
    @media (max-width: 960px) {
        .feature-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 560px) {
        .feature-shell {
            padding: 18px;
        }
    }
</style>

<div class="feature-shell">
    <div class="feature-head">
            <div>
                <div class="feature-kicker">Energy Conservation Feature</div>
                <h1 class="feature-title">{{ $feature['title'] ?? 'Feature' }}</h1>
                <div class="feature-desc">{{ $feature['description'] ?? '' }}</div>
                @if($featureSlug === 'daily-checklist')
                    <div class="feature-chip">
                        <i class="fa-solid fa-building"></i>
                        <span>Selected Facility: {{ $selectedFacilityLabel }}</span>
                    </div>
                @endif
            </div>
        <span class="feature-status {{ $featureStatus }}">{{ $featureBadge }}</span>
    </div>

    <div class="feature-grid">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Main Content</h2>
                    <div class="panel-note">Real content and forms tied to current app data.</div>
                </div>
                <a class="back-link" href="{{ route('modules.energy-conservation.index') }}">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="panel-body">
                @if($featureSlug === 'daily-checklist')
                    @if(session('success'))
                        <div class="task-toast show" id="taskToast">
                            <span>{{ session('success') }}</span>
                            <button type="button" onclick="dismissTaskToast()" aria-label="Close toast">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    @endif
                    <form class="form-grid" method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}">
                        <div class="field">
                            <label>Select Facility</label>
                            <select name="facility_id" onchange="this.form.submit()">
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ (int) $selectedFacilityId === (int) $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }}{{ $facility->type ? ' - ' . $facility->type : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="help-text">Choose the facility you want to update before saving the checklist.</div>
                        </div>
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    </form>
                    <div class="stat-grid">
                        @foreach($taskSummary as $summary)
                            <div class="stat-card">
                                <div class="stat-label">{{ $summary['label'] }}</div>
                                <div class="stat-value">{{ $summary['value'] }}</div>
                                <div class="stat-sub">Based on saved tasks in the maintenance table.</div>
                            </div>
                        @endforeach
                    </div>

                    @if($canCreateTasks)
                        <div class="task-toolbar">
                            <div>
                                <strong style="color:#0f172a;">Need a new task?</strong>
                                <div class="help-text">Admins and energy officers can create follow-up tasks.</div>
                            </div>
                            <button type="button" class="btn-main" onclick="openDailyTaskModal()">
                                <i class="fa-solid fa-plus"></i> Create Item
                            </button>
                        </div>
                    @endif

                    @if($canApproveTasks)
                        <div class="action-row" style="margin-top:4px;">
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <a class="back-link" href="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug, 'month' => $selectedMonth, 'facility_id' => $selectedFacilityId, 'tab' => 'tasks']) }}" style="{{ $selectedTab === 'tasks' ? 'text-decoration:underline;' : '' }}">Checklist</a>
                                <a class="back-link" href="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug, 'month' => $selectedMonth, 'facility_id' => $selectedFacilityId, 'tab' => 'review']) }}" style="{{ $selectedTab === 'review' ? 'text-decoration:underline;' : '' }}">For Review ({{ $dailyChecklistReviewTasks->count() }})</a>
                                <a class="back-link" href="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug, 'month' => $selectedMonth, 'facility_id' => $selectedFacilityId, 'tab' => 'completed']) }}" style="{{ $selectedTab === 'completed' ? 'text-decoration:underline;' : '' }}">Completed ({{ $dailyChecklistCompletedTasks->count() }})</a>
                            </div>
                        </div>
                    @endif

                    <div class="panel" style="box-shadow:none;">
                        <div class="panel-head">
                            <div>
                                <h2 class="panel-title">
                                    {{ $selectedTab === 'review' ? 'For Review' : ($selectedTab === 'completed' ? 'Completed Checklist Items' : 'Saved Checklist') }}
                                </h2>
                                <div class="panel-note">
                                    {{ $selectedTab === 'review' ? 'Submitted progress waiting for admin approval.' : ($selectedTab === 'completed' ? 'Checklist items already approved and marked complete.' : 'Active checklist items for this facility and month.') }}
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            @php
                                $tasksToShow = $selectedTab === 'review'
                                    ? $dailyChecklistReviewTasks
                                    : ($selectedTab === 'completed'
                                        ? $dailyChecklistCompletedTasks
                                        : $dailyChecklistTasks->where('maintenance_status', '!=', 'Completed')->values());
                            @endphp
                            @forelse($tasksToShow as $task)
                                @php
                                    $taskStatus = \Illuminate\Support\Str::of((string) $task->maintenance_status)->lower();
                                    $taskStatusClass = \Illuminate\Support\Str::slug((string) $task->maintenance_status, '-');
                                    $taskIcon = match ((string) $task->maintenance_status) {
                                        'Completed' => 'fa-check',
                                        'Overdue' => 'fa-triangle-exclamation',
                                        'For Review' => 'fa-hourglass-half',
                                        default => 'fa-clipboard-list',
                                    };
                                @endphp
                                <div
                                    class="checklist-item task-collapsed {{ $newTaskId && (int) $newTaskId === (int) $task->id ? 'recent-task' : '' }}"
                                    id="task-{{ $task->id }}"
                                    role="button"
                                    tabindex="0"
                                    aria-expanded="false"
                                    onclick="toggleTaskDetails(this)"
                                    onkeydown="handleTaskKey(event, this)"
                                >
                                    <span class="checklist-check {{ $taskStatusClass }}">
                                        <i class="fa-solid {{ $taskIcon }}"></i>
                                    </span>
                                    <div class="checklist-main task-card-body">
                                        <div class="checklist-title-row">
                                            <div>
                                                <div class="checklist-title">{{ $task->issue_type ?: 'Energy task' }}</div>
                                                <div class="checklist-meta">
                                                    {{ $task->assigned_to ? 'Assigned to ' . $task->assigned_to . ' | ' : '' }}
                                                    {{ $task->scheduled_date ? \Illuminate\Support\Carbon::parse($task->scheduled_date)->format('M d, Y') : 'No due date' }}
                                                </div>
                                                <div class="task-details">
                                                    <span class="task-tag"><i class="fa-regular fa-user"></i> {{ $task->assigned_to ?: 'Unassigned' }}</span>
                                                    <span class="task-tag muted"><i class="fa-regular fa-calendar"></i> {{ $task->scheduled_date ? \Illuminate\Support\Carbon::parse($task->scheduled_date)->format('M d, Y') : 'No due date' }}</span>
                                                    @if(($task->photo_requirement ?? 'Optional') === 'Required')
                                                        <span class="task-tag required-photo"><i class="fa-solid fa-camera"></i> Photo required</span>
                                                    @else
                                                        <span class="task-tag muted"><i class="fa-regular fa-camera"></i> Photo optional</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="status-chip {{ $taskStatusClass }}">{{ $task->maintenance_status }}</span>
                                        </div>
                                        <div class="task-expand-hint">
                                    <span>View details</span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                        <div class="task-extra" onclick="event.stopPropagation();">
                                            @if($task->remarks)
                                                <div class="help-text" style="margin-top:0; white-space:pre-line;">{{ $task->remarks }}</div>
                                            @endif
                                            @if($task->maintenance_status !== 'Completed' && ! $canApproveTasks)
                                                <form class="task-progress-form" method="POST" enctype="multipart/form-data" action="{{ route('modules.energy-conservation.daily-checklist.tasks.progress', ['task' => $task->id, 'month' => $selectedMonth, 'facility_id' => $selectedFacilityId]) }}">
                                                    @csrf
                                                    <div class="field" style="margin-bottom:10px;">
                                                        <label>Progress Report</label>
                                                        <textarea name="progress" placeholder="Describe what was done, what is pending, or what needs admin review..." required></textarea>
                                                    </div>
                                                    <div class="field" style="margin-bottom:10px;">
                                                        <label>Optional Evidence Photo</label>
                                                        <input type="file" name="progress_photo" accept="image/*">
                                                        <div class="help-text">Photo recommended, but not required.</div>
                                                    </div>
                                                    <div class="task-action-row">
                                                        <button type="submit" class="btn-main btn-secondary"><i class="fa-solid fa-paper-plane"></i> Submit Progress</button>
                                                    </div>
                                                </form>
                                            @endif
                                            @if($task->maintenance_status !== 'Completed' && $canApproveTasks)
                                                <form class="task-progress-form" method="POST" enctype="multipart/form-data" action="{{ route('modules.energy-conservation.daily-checklist.tasks.complete', ['task' => $task->id, 'month' => $selectedMonth, 'facility_id' => $selectedFacilityId]) }}">
                                                    @csrf
                                                    <div class="field" style="margin-bottom:10px;">
                                                        <label>Admin Remarks</label>
                                                        <textarea name="approval_remarks" placeholder="Optional approval notes before marking the task complete..."></textarea>
                                                    </div>
                                                    <div class="field" style="margin-bottom:10px;">
                                                        <label>Optional Verification Photo</label>
                                                        <input type="file" name="approval_photo" accept="image/*">
                                                        <div class="help-text">Photo recommended, but not required.</div>
                                                    </div>
                                                    <div class="task-action-row">
                                                        <button type="submit" class="btn-main btn-secondary"><i class="fa-solid fa-check"></i> Approve Completion</button>
                                                    </div>
                                                </form>
                                            @endif
                                            @if($canDeleteTasks)
                                                <form method="POST" action="{{ route('modules.energy-conservation.daily-checklist.tasks.delete', ['task' => $task->id, 'month' => $selectedMonth, 'facility_id' => $selectedFacilityId]) }}" style="margin-top:10px;">
                                                    @csrf
                                                    <div class="task-action-row">
                                                        <button type="button" class="btn-main" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;" onclick="openDeleteTaskModal(this.form, '{{ $task->issue_type ?: 'Energy task' }}')">
                                                            <i class="fa-solid fa-trash"></i> Delete Item
                                                        </button>
                                                    </div>
                                                </form>
                                            @endif
                                            @if(!empty($task->proof_photo_path))
                                                <div style="margin-top:4px;">
                                                    <span class="task-tag muted"><i class="fa-regular fa-image"></i> Photo attached</span>
                                                </div>
                                                <div style="margin-top:6px;">
                                                    <a class="back-link" href="{{ asset('storage/' . $task->proof_photo_path) }}" target="_blank" rel="noopener">
                                                        <i class="fa-regular fa-image"></i> View uploaded photo
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="feature-point">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>No saved tasks yet for this facility and month.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                @elseif($featureSlug === 'suggestions-box')
                    <form class="form-grid" method="POST" action="{{ route('landing.contact.store') }}">
                        @csrf
                        <div class="stat-grid">
                            <div class="stat-card">
                                <div class="stat-label">Inbox Count</div>
                                <div class="stat-value">{{ number_format((int) ($overview['contactInboxCount'] ?? 0)) }}</div>
                                <div class="stat-sub">Messages already stored in `contact_messages`.</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Selected Month</div>
                                <div class="stat-value">{{ $selectedMonth }}</div>
                                <div class="stat-sub">Used for related energy summaries.</div>
                            </div>
                        </div>
                        <div class="field">
                            <label>Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->full_name ?? auth()->user()?->name ?? auth()->user()?->username ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label>Subject</label>
                            <input type="text" name="subject" value="{{ old('subject', 'Energy conservation suggestion') }}" placeholder="Short subject">
                        </div>
                        <div class="field">
                            <label>Suggestion</label>
                            <textarea name="message" required placeholder="Write your energy-saving suggestion here...">{{ old('message') }}</textarea>
                            <div class="help-text">This is saved in the existing contact inbox workflow and can be reviewed by admins.</div>
                        </div>
                        <div class="action-row">
                            <span class="help-text">Uses the current contact message database and inbox notifications.</span>
                            <button type="submit" class="btn-main"><i class="fa-solid fa-paper-plane"></i> Submit Suggestion</button>
                        </div>
                    </form>
                @elseif(in_array($featureSlug, ['estimated-savings', 'conservation-goals', 'ai-recommendations'], true))
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Monitored Facilities</div>
                            <div class="stat-value">{{ number_format((int) ($totals['monitored_facilities'] ?? 0)) }}</div>
                            <div class="stat-sub">Current monthly records in `energy_records`.</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Actual kWh</div>
                            <div class="stat-value">{{ number_format((float) ($totals['actual_kwh'] ?? 0), 2) }}</div>
                            <div class="stat-sub">Pulled from existing energy data.</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Avoidable Cost</div>
                            <div class="stat-value">PHP {{ number_format((float) ($totals['avoidable_cost'] ?? 0), 2) }}</div>
                            <div class="stat-sub">Baseline vs actual monthly comparison.</div>
                        </div>
                    </div>

                    <form class="form-grid" method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}">
                        <div class="field">
                            <label>Select Facility</label>
                            <select name="facility_id">
                                <option value="0">All facilities</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ (int) $selectedFacilityId === (int) $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }}{{ $facility->type ? ' - ' . $facility->type : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="help-text">Use this to preview data-driven goals, savings, and AI suggestions.</div>
                        </div>
                        <div class="action-row">
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            <button class="btn-main" type="submit"><i class="fa-solid fa-filter"></i> Apply Filter</button>
                            <a class="btn-main btn-secondary" href="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                        </div>
                    </form>

                    @if($featureSlug === 'conservation-goals')
                        <form class="form-grid" method="POST" action="{{ route('landing.contact.store') }}">
                            @csrf
                            <input type="hidden" name="subject" value="New conservation goal proposal">
                            <input type="hidden" name="name" value="{{ auth()->user()?->full_name ?? auth()->user()?->name ?? auth()->user()?->username ?? 'System User' }}">
                            <input type="hidden" name="email" value="{{ auth()->user()?->email ?? 'support@example.com' }}">
                            <div class="field">
                                <label>Goal Summary</label>
                                <textarea name="message" required>Suggested goal for {{ $selectedMonth }}: reduce consumption by 5% from the current baseline across monitored facilities.</textarea>
                                <div class="help-text">This is a working proposal form. Later we can save this into a dedicated goals table.</div>
                            </div>
                            <div class="action-row">
                                <span class="help-text">Current top facility: {{ $topFacility['facility_name'] ?? 'No current data' }}</span>
                                <button type="submit" class="btn-main"><i class="fa-solid fa-bullseye"></i> Send Goal Proposal</button>
                            </div>
                        </form>
                    @endif

                    @if($featureSlug === 'ai-recommendations')
                        <div class="feature-list">
                            @forelse($rows->take(5) as $row)
                                <div class="feature-point">
                                    <i class="fa-solid fa-robot"></i>
                                    <span>
                                        <strong>{{ $row['facility_name'] }}</strong>:
                                        {{ $row['recommendation'] }}
                                    </span>
                                </div>
                            @empty
                                <div class="feature-point">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>No monthly data is available yet for AI-style recommendations.</span>
                                </div>
                            @endforelse
                        </div>
                    @elseif($featureSlug === 'estimated-savings')
                        <div class="feature-list">
                            <div class="feature-point">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Estimated kWh savings are computed from baseline vs actual records already stored in `energy_records`.</span>
                            </div>
                            <div class="feature-point">
                                <i class="fa-solid fa-peso-sign"></i>
                                <span>Avoidable cost uses the same monthly data and current rate logic used by the app.</span>
                            </div>
                            <div class="feature-point">
                                <i class="fa-solid fa-leaf"></i>
                                <span>CO2 reduction can be added later as a computed field once you confirm the preferred emission factor.</span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="feature-list">
                        @foreach(($feature['details'] ?? []) as $detail)
                            <div class="feature-point">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>{{ $detail }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    </div>
</div>

<div class="modal-backdrop" id="dailyTaskModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="dailyTaskModalTitle">
        <div class="modal-head">
            <div>
                <h3 class="modal-title" id="dailyTaskModalTitle">Create Checklist Item</h3>
                <div class="panel-note">Create a follow-up task for {{ $selectedFacilityLabel }}.</div>
            </div>
            <button type="button" class="modal-close" aria-label="Close" onclick="closeDailyTaskModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form class="form-grid" method="POST" action="{{ route('modules.energy-conservation.daily-checklist.tasks.store') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
                <div class="field">
                    <label>Checklist Title</label>
                    <input type="text" name="title" placeholder="Example: Turn off unused lighting after office hours" required>
                </div>
                <div class="field">
                    <label>Assigned To</label>
                    <input type="text" name="assigned_to" placeholder="Optional assignee">
                </div>
                <div class="field">
                    <label>Due Date</label>
                    <input type="date" name="due_date">
                </div>
                <div class="field">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="field">
                    <label>Photo Requirement</label>
                    <select name="photo_requirement">
                        <option value="Optional" selected>Optional</option>
                        <option value="Required">Required</option>
                    </select>
                    <div class="help-text">Choose <strong>Required</strong> if staff must upload a photo before submitting progress. Staff will see this as a required photo badge on the task row.</div>
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <textarea name="remarks" placeholder="Optional notes about the task..."></textarea>
                </div>
                <div class="action-row">
                    <span class="help-text">You can mark the photo as optional or required for this task.</span>
                    <button type="submit" class="btn-main"><i class="fa-solid fa-plus"></i> Create Item</button>
                </div>
            </form>
        </div>
</div>
</div>

<div class="modal-backdrop" id="deleteTaskModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="deleteTaskModalTitle" style="width:min(520px, 100%);">
        <div class="modal-head" style="border-bottom:1px solid #fecaca;background:linear-gradient(135deg, #fff5f5, #ffffff);">
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <div style="width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:#fee2e2;color:#b91c1c;flex:none;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="modal-title" id="deleteTaskModalTitle" style="color:#991b1b;">Delete Checklist Item</h3>
                    <div class="panel-note" id="deleteTaskModalNote" style="color:#b91c1c;font-weight:700;">This action cannot be undone.</div>
                </div>
            </div>
            <button type="button" class="modal-close" aria-label="Close" onclick="closeDeleteTaskModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <p class="help-text" style="margin-top:0;color:#334155;">Are you sure you want to delete <strong id="deleteTaskModalName">this item</strong>?</p>
            <div class="feature-point" style="margin-top:14px;border-color:#fecaca;background:#fff5f5;">
                <i class="fa-solid fa-circle-info" style="color:#b91c1c;"></i>
                <span>This will remove the task from the list for staff and admins.</span>
            </div>
            <div class="action-row" style="margin-top:18px;">
                <button type="button" class="btn-main btn-secondary" onclick="closeDeleteTaskModal()">Cancel</button>
                <button type="button" class="btn-main" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;" onclick="submitDeleteTaskForm()">
                    <i class="fa-solid fa-trash"></i> Delete Item
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function dismissTaskToast() {
    var toast = document.getElementById('taskToast');
    if (toast) {
        toast.classList.remove('show');
    }
}

function openDailyTaskModal() {
    var modal = document.getElementById('dailyTaskModal');
    if (modal) {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }
}

function closeDailyTaskModal() {
    var modal = document.getElementById('dailyTaskModal');
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }
}

document.addEventListener('click', function (event) {
    var modal = document.getElementById('dailyTaskModal');
    if (modal && event.target === modal) {
        closeDailyTaskModal();
    }
});

var deleteTaskForm = null;

function openDeleteTaskModal(form, taskName) {
    deleteTaskForm = form || null;
    var modal = document.getElementById('deleteTaskModal');
    var label = document.getElementById('deleteTaskModalName');
    if (label) {
        label.textContent = taskName || 'this task';
    }
    if (modal) {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }
}

function closeDeleteTaskModal() {
    var modal = document.getElementById('deleteTaskModal');
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }
    deleteTaskForm = null;
}

function submitDeleteTaskForm() {
    if (deleteTaskForm) {
        deleteTaskForm.submit();
    }
    closeDeleteTaskModal();
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeDailyTaskModal();
        closeDeleteTaskModal();
    }
});

function toggleTaskDetails(card) {
    if (!card) {
        return;
    }

    var isExpanded = card.classList.contains('task-expanded');
    card.classList.toggle('task-expanded', !isExpanded);
    card.classList.toggle('task-collapsed', isExpanded);
    card.setAttribute('aria-expanded', String(!isExpanded));
}

function handleTaskKey(event, card) {
    if (!event || !card) {
        return;
    }

    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleTaskDetails(card);
    }
}

window.addEventListener('load', function () {
    var target = document.querySelector('.recent-task');
    if (target) {
        setTimeout(function () {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 150);
    }
});
</script>
@endsection

