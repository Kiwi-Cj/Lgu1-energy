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
    $energyTips = $energyTips ?? collect();
    $canReviewTips = (bool) ($canReviewTips ?? false);
    $selectedFacility = $selectedFacility ?? null;
    $selectedFacilityId = (int) ($selectedFacilityId ?? 0);
    $featureStatus = $feature['status'] ?? 'enabled';
    $featureBadge = $feature['badge'] ?? 'Enabled';
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
    .feature-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(300px, .85fr);
        gap: 14px;
        align-items: start;
    }
    .feature-grid.single-panel { grid-template-columns: minmax(0, 1fr); }
    .panel {
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
    }
    .panel.tips-panel {
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .panel.tips-panel > .panel-head {
        padding: 4px 4px 18px;
        border-bottom: 1px solid #dbe4f0;
    }
    .panel.tips-panel > .panel-body {
        padding: 20px 4px 4px;
        gap: 16px;
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
    .tip-filter-card {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) auto;
        align-items: end;
        gap: 12px;
        padding: 16px 18px;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        background: #f8fbff;
    }
    .tip-filter-card .action-row { align-self: end; justify-content: flex-end; }
    .tip-filter-card .action-row .btn-main { min-height: 44px; justify-content: center; }
    .tip-filter-card .help-text { margin-bottom: 0; }
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
    .energy-tip-list { display: grid; gap: 12px; }
    .energy-tip-card {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid #dbe4f0;
        border-left: 4px solid #3b82f6;
        border-radius: 14px;
        background: #f8fbff;
    }
    .energy-tip-card.critical { border-left-color: #dc2626; background: #fff7f7; }
    .energy-tip-card.warning { border-left-color: #f97316; background: #fffaf5; }
    .energy-tip-card.watch { border-left-color: #eab308; background: #fffdf2; }
    .energy-tip-card.success { border-left-color: #16a34a; background: #f6fff8; }
    .energy-tip-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eaf2ff;
        color: #2563eb;
    }
    .energy-tip-card.critical .energy-tip-icon { background: #fee2e2; color: #dc2626; }
    .energy-tip-card.warning .energy-tip-icon { background: #ffedd5; color: #ea580c; }
    .energy-tip-card.watch .energy-tip-icon { background: #fef9c3; color: #a16207; }
    .energy-tip-card.success .energy-tip-icon { background: #dcfce7; color: #15803d; }
    .energy-tip-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .energy-tip-title { color: #0f172a; font-weight: 900; line-height: 1.3; }
    .energy-tip-priority {
        flex: 0 0 auto;
        padding: 4px 8px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-size: .66rem;
        font-weight: 900;
        text-transform: uppercase;
    }
    .energy-tip-message { margin-top: 4px; color: #475569; font-size: .84rem; line-height: 1.45; }
    .energy-tip-metric { margin-top: 6px; color: #1e40af; font-size: .75rem; font-weight: 800; }
    .tip-review-status { margin-top: 10px; font-size: .75rem; font-weight: 800; color: #64748b; }
    .tip-approved-text { margin-top: 10px; padding: 11px 12px; border-radius: 10px; background: #ecfdf5; color: #166534; line-height: 1.45; }
    .tip-review-form { display: grid; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #dbe4f0; }
    .tip-review-form textarea,
    .tip-review-form input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 9px 10px;
        background: #fff;
        color: #0f172a;
    }
    .tip-review-form textarea { min-height: 68px; resize: vertical; }
    .tip-review-disclosure { margin-top: 9px; border-top: 1px solid #dbe4f0; }
    .tip-review-disclosure summary {
        width: fit-content;
        margin-top: 9px;
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
        list-style: none;
        user-select: none;
    }
    .tip-review-disclosure summary::-webkit-details-marker { display: none; }
    .tip-review-disclosure summary::before { content: '\f044'; margin-right: 7px; font-family: 'Font Awesome 6 Free'; font-weight: 900; }
    .tip-review-disclosure[open] summary::before { content: '\f077'; }
    .tip-review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .tip-review-field label { display: block; margin-bottom: 5px; color: #64748b; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
    .tip-review-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .tip-action { border: 0; border-radius: 9px; padding: 8px 11px; font-size: .76rem; font-weight: 800; cursor: pointer; }
    .tip-action.save { background: #e2e8f0; color: #334155; }
    .tip-action.approve { background: #16a34a; color: #fff; }
    .tip-action.dismiss { background: #fee2e2; color: #b91c1c; }
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
    body.dark-mode .panel.tips-panel { background: transparent; border-color: transparent; box-shadow: none; }
    body.dark-mode .panel.tips-panel > .panel-head { border-color: #334155; }
    body.dark-mode .tip-filter-card { background: #111827; border-color: #334155; }
    body.dark-mode .feature-title,
    body.dark-mode .panel-title,
    body.dark-mode .stat-value,
    body.dark-mode .suggestion-name {
        color: #f8fafc;
    }
    body.dark-mode .energy-tip-card { background: #111827; border-color: #334155; }
    body.dark-mode .energy-tip-card.critical,
    body.dark-mode .energy-tip-card.warning,
    body.dark-mode .energy-tip-card.watch,
    body.dark-mode .energy-tip-card.success { background: #111827; }
    body.dark-mode .energy-tip-title { color: #f8fafc; }
    body.dark-mode .energy-tip-message { color: #cbd5e1; }
    body.dark-mode .energy-tip-priority { background: #1f2937; color: #cbd5e1; }
    body.dark-mode .energy-tip-metric { color: #93c5fd; }
    body.dark-mode .tip-approved-text { background: #052e1a; color: #bbf7d0; }
    body.dark-mode .tip-review-form { border-color: #334155; }
    body.dark-mode .tip-review-disclosure { border-color: #334155; }
    body.dark-mode .tip-review-disclosure summary { color: #93c5fd; }
    body.dark-mode .tip-review-form textarea,
    body.dark-mode .tip-review-form input { background: #0b1220; color: #e2e8f0; border-color: #334155; }
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
        .energy-tip-card { grid-template-columns: 32px minmax(0, 1fr); padding: 11px; gap: 9px; }
        .energy-tip-icon { width: 32px; height: 32px; }
        .energy-tip-top { display: grid; gap: 7px; }
        .energy-tip-priority { justify-self: start; }
        .tip-review-grid { grid-template-columns: 1fr; }
        .tip-review-actions .tip-action { flex: 1; }
        .tip-filter-card { grid-template-columns: 1fr; padding: 12px; }
        .tip-filter-card .action-row { display: grid; grid-template-columns: 1fr 1fr; }
        .tip-filter-card .btn-main { justify-content: center; }
        .panel.tips-panel > .panel-head { padding-inline: 0; }
        .panel.tips-panel > .panel-body { padding-inline: 0; }
    }
</style>

<div class="feature-shell">
    <div class="feature-head">
        <div>
            <div class="feature-kicker">Energy Conservation Feature</div>
            <h1 class="feature-title">{{ $feature['title'] ?? 'Feature' }}</h1>
            <div class="feature-desc">{{ $feature['description'] ?? '' }}</div>
        </div>
        <span class="feature-status {{ $featureStatus }}">{{ $featureBadge }}</span>
    </div>

    <div class="feature-grid{{ $featureSlug === 'energy-saving-tips' ? ' single-panel' : '' }}">
        <section class="panel{{ $featureSlug === 'energy-saving-tips' ? ' tips-panel' : '' }}">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">{{ $featureSlug === 'energy-saving-tips' ? 'Facility Recommendations' : 'Main Content' }}</h2>
                    <div class="panel-note">{{ $featureSlug === 'energy-saving-tips' ? 'Generated from monthly consumption, baseline, and deviation data.' : 'Actual content and forms tied to current app data.' }}</div>
                </div>
                <a class="back-link" href="{{ route('modules.energy-conservation.index') }}">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
            <div class="panel-body">
                @if($featureSlug === 'suggestions-box')
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
                            <div class="help-text">This is saved in the existing contact inbox workflow and can be reviewed by admin.</div>
                        </div>
                        <div class="action-row">
                            <span class="help-text">Uses the current contact message database and inbox notifications.</span>
                            <button type="submit" class="btn-main"><i class="fa-solid fa-paper-plane"></i> Submit Suggestion</button>
                        </div>
                    </form>
                @elseif(in_array($featureSlug, ['energy-saving-tips', 'estimated-savings', 'conservation-goals', 'ai-recommendations'], true))
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Monitored Facilities</div>
                            <div class="stat-value">{{ number_format((int) ($totals['monitored_facilities'] ?? 0)) }}</div>
                            <div class="stat-sub">Facilities with monthly energy records.</div>
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

                    <form class="form-grid{{ $featureSlug === 'energy-saving-tips' ? ' tip-filter-card' : '' }}" method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}">
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
                            <div class="help-text">Use this to generate results from the selected facility's monthly energy record.</div>
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
                                <div class="help-text">This is a working proposal form. Later pwede natin itong i-save into a dedicated goals table.</div>
                            </div>
                            <div class="action-row">
                                <span class="help-text">Current top facility: {{ $topFacility['facility_name'] ?? 'No current data' }}</span>
                                <button type="submit" class="btn-main"><i class="fa-solid fa-bullseye"></i> Send Goal Proposal</button>
                            </div>
                        </form>
                    @endif

                    @if($featureSlug === 'energy-saving-tips')
                        <div class="energy-tip-list">
                            @forelse($energyTips as $tip)
                                @php
                                    $review = $tip['review'] ?? null;
                                    $reviewStatus = $review?->status ?? 'for_review';
                                @endphp
                                <article class="energy-tip-card {{ $tip['tone'] ?? 'info' }}">
                                    <div class="energy-tip-icon"><i class="{{ $tip['icon'] ?? 'fa-solid fa-lightbulb' }}"></i></div>
                                    <div>
                                        <div class="energy-tip-top">
                                            <div class="energy-tip-title">{{ $tip['title'] }}</div>
                                            <span class="energy-tip-priority">{{ $tip['priority'] }}</span>
                                        </div>
                                        <div class="energy-tip-message">{{ $tip['message'] }}</div>
                                        @if(!empty($tip['metric']))
                                            <div class="energy-tip-metric"><i class="fa-solid fa-chart-simple"></i> {{ $tip['metric'] }}</div>
                                        @endif
                                        @if($reviewStatus === 'approved' && $review?->engineer_recommendation)
                                            <div class="tip-approved-text">
                                                <strong>Engineering-approved action:</strong><br>
                                                {{ $review->engineer_recommendation }}
                                            </div>
                                        @endif
                                        @if($review)
                                            <div class="tip-review-status">
                                                Status: {{ strtoupper(str_replace('_', ' ', $reviewStatus)) }}
                                                @if($review->reviewer) · Reviewed by {{ $review->reviewer->username }} @endif
                                            </div>
                                        @endif
                                        @if($canReviewTips && !empty($tip['facility_id']))
                                            <details class="tip-review-disclosure">
                                                <summary>Review recommendation</summary>
                                            <form class="tip-review-form" method="POST" action="{{ route('modules.energy-conservation.tips.review') }}">
                                                @csrf
                                                <input type="hidden" name="facility_id" value="{{ $tip['facility_id'] }}">
                                                <input type="hidden" name="period" value="{{ $selectedMonth }}">
                                                <div class="tip-review-field">
                                                    <label>Engineering Recommendation</label>
                                                    <textarea name="engineer_recommendation" placeholder="Review or replace the generated action...">{{ old('engineer_recommendation', $review?->engineer_recommendation ?? $tip['message']) }}</textarea>
                                                </div>
                                                <div class="tip-review-grid">
                                                    <div class="tip-review-field">
                                                        <label>Expected Savings (kWh)</label>
                                                        <input type="number" min="0" step="0.01" name="expected_savings_kwh" value="{{ old('expected_savings_kwh', $review?->expected_savings_kwh) }}">
                                                    </div>
                                                    <div class="tip-review-field">
                                                        <label>Target Date</label>
                                                        <input type="date" name="target_date" value="{{ old('target_date', $review?->target_date?->format('Y-m-d')) }}">
                                                    </div>
                                                </div>
                                                <div class="tip-review-actions">
                                                    <button class="tip-action save" type="submit" name="status" value="for_review">Save Draft</button>
                                                    <button class="tip-action approve" type="submit" name="status" value="approved">Approve</button>
                                                    <button class="tip-action dismiss" type="submit" name="status" value="dismissed">Dismiss</button>
                                                </div>
                                            </form>
                                            </details>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="feature-point">
                                    <i class="fa-solid fa-clock"></i>
                                    <span>No Engineering-approved energy tip is available for the selected facility and month yet.</span>
                                </div>
                            @endforelse
                        </div>
                    @elseif($featureSlug === 'ai-recommendations')
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
                                    <span>No monthly data yet for AI-style recommendations.</span>
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

        @if($featureSlug !== 'energy-saving-tips')
        <aside class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Live Data</h2>
                    <div class="panel-note">From current app tables and workflows.</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-label">Top Facility</div>
                        <div class="stat-value">{{ $topFacility['facility_name'] ?? 'No current data' }}</div>
                        <div class="stat-sub">{{ $topFacility ? number_format((float) ($topFacility['actual_kwh'] ?? 0), 2) . ' kWh actual' : 'Add monthly records first.' }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Average Deviation</div>
                        <div class="stat-value">{{ $averageDeviation !== null ? number_format((float) $averageDeviation, 2) . '%' : 'No data' }}</div>
                        <div class="stat-sub">Based on active facility records for {{ $selectedMonth }}.</div>
                    </div>
                </div>

                <div class="feature-list">
                    <div class="feature-point">
                        <i class="fa-solid fa-folder-open"></i>
                        <span>{{ number_format((int) ($overview['contactInboxCount'] ?? 0)) }} suggestions already stored in the system inbox.</span>
                    </div>
                    <div class="feature-point">
                        <i class="fa-solid fa-building"></i>
                        <span>{{ number_format((int) ($totals['facilities'] ?? 0)) }} facilities are available for filtering and goal previews.</span>
                    </div>
                    <div class="feature-point">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Reports can link directly to the existing energy report routes in the app.</span>
                    </div>
                </div>

                @if($featureSlug === 'suggestions-box' && $latestContactSuggestions->isNotEmpty())
                    <div>
                        <div class="panel-title" style="margin-bottom:10px;">Latest Suggestions</div>
                        <div class="suggestion-list">
                            @foreach($latestContactSuggestions as $suggestion)
                                <div class="suggestion-item">
                                    <div class="suggestion-name">{{ $suggestion->subject ?: 'Energy suggestion' }}</div>
                                    <div class="suggestion-meta">By {{ $suggestion->name }} | {{ $suggestion->created_at?->timezone('Asia/Manila')?->format('M d, Y') }}</div>
                                    <div class="suggestion-body">{{ \Illuminate\Support\Str::limit($suggestion->message, 120) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="action-row">
                    <a class="back-link" href="{{ route('modules.energy-conservation.index') }}">
                        <i class="fa-solid fa-grid-2"></i> Overview
                    </a>
                    <a class="btn-main btn-secondary" href="{{ route('modules.reports.energy') }}">
                        <i class="fa-solid fa-chart-column"></i> Open Reports
                    </a>
                </div>
            </div>
        </aside>
        @endif
    </div>
</div>
@endsection
