@extends('layouts.qc-admin')

@section('title', 'Monthly Record Activity')

@section('content')
<style>
    .monthly-activity-page { font-family:Inter,sans-serif; color:#1e293b; max-width:1500px; margin:0 auto; }
    .monthly-report-card { padding:24px; border:1px solid #dce6f2; border-radius:22px; background:linear-gradient(155deg,#fff 0%,#f8fbff 100%); box-shadow:0 16px 38px rgba(15,23,42,.07); }
    .activity-header { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:19px; }
    .activity-eyebrow { display:inline-flex; align-items:center; gap:7px; margin-bottom:8px; color:#2563eb; font-size:.68rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    .activity-header h1 { margin:0 0 6px; font-size:clamp(1.55rem,2vw,1.85rem); line-height:1.15; letter-spacing:-.035em; color:#0f172a; }
    .activity-header p { margin:0; color:#64748b; font-size:.86rem; line-height:1.5; }
    .activity-overview { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:13px; margin-bottom:16px; }
    .overview-card { --card-accent:#2563eb; position:relative; min-height:134px; overflow:hidden; background:#fff; border:1px solid #dce6f2; border-radius:15px; padding:16px; display:flex; align-items:flex-start; gap:12px; box-shadow:0 7px 18px rgba(15,23,42,.045); }
    .overview-card::before { position:absolute; top:0; right:0; left:0; height:4px; background:var(--card-accent); content:''; }
    .overview-card.review-summary { --card-accent:#ea580c; }
    .overview-card.missing-summary { --card-accent:#dc2626; }
    .overview-card.integration-summary { --card-accent:var(--integration-color,#2563eb); }
    .overview-icon { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; font-size:.82rem; }
    .overview-label { color:#64748b; font-size:.75rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .overview-value { color:#0f172a; font-size:1.16rem; line-height:1.2; font-weight:900; margin-top:4px; letter-spacing:-.025em; }
    .overview-detail { color:#64748b; font-size:.72rem; line-height:1.4; margin-top:5px; }
    .missing-list { margin-top:8px; }
    .missing-list summary { color:#3762c8; font-size:.78rem; font-weight:800; cursor:pointer; }
    .missing-list-items { margin:8px 0 0; padding:8px 0 0 18px; max-height:130px; overflow:auto; border-top:1px solid #e2e8f0; color:#475569; font-size:.78rem; }
    .activity-card { background:#fff; border:1px solid #dce6f2; border-radius:16px; box-shadow:0 9px 24px rgba(15,23,42,.045); overflow:hidden; }
    .activity-card-heading { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 16px 0; background:#f8fafc; }
    .activity-card-heading strong { color:#0f172a; font-size:.95rem; }
    .activity-result-count { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:999px; background:#eaf1ff; color:#315cca; font-size:.72rem; font-weight:800; }
    .activity-filters { display:grid; grid-template-columns:minmax(220px,1fr) 165px 165px 165px auto; align-items:end; gap:9px; padding:12px 16px 15px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
    .activity-filter-field { display:flex; min-width:0; flex-direction:column; gap:6px; }
    .activity-filter-label { color:#64748b; font-size:.65rem; font-weight:800; letter-spacing:.055em; text-transform:uppercase; }
    .activity-filters input, .activity-filters select { width:100%; height:40px; padding:8px 10px; border:1px solid #cbd5e1; border-radius:10px; outline:none; background:#fff; color:#334155; font-size:.8rem; transition:border-color .15s,box-shadow .15s; }
    .activity-filters input:focus, .activity-filters select:focus { border-color:#60a5fa; box-shadow:0 0 0 3px rgba(59,130,246,.14); }
    .filter-actions { display:flex; gap:8px; }
    .filter-btn { height:40px; padding:8px 13px; display:inline-flex; align-items:center; justify-content:center; gap:6px; border:1px solid transparent; border-radius:10px; font-size:.78rem; font-weight:800; text-decoration:none; cursor:pointer; }
    .filter-btn.primary { background:#315cca; color:#fff; box-shadow:0 6px 14px rgba(49,92,202,.18); }
    .filter-btn.primary:hover { background:#244bb5; transform:translateY(-1px); }
    .filter-btn.reset { background:#e2e8f0; color:#334155; }
    .filter-btn.reset:hover { background:#d6dee9; }
    .activity-table-wrap { overflow-x:auto; scrollbar-color:#b8c5d6 transparent; }
    .activity-table { width:100%; border-collapse:collapse; min-width:1180px; }
    .activity-table th { padding:12px 14px; text-align:left; color:#64748b; background:#fff; border-bottom:1px solid #dfe7f1; font-size:.67rem; text-transform:uppercase; letter-spacing:.065em; white-space:nowrap; }
    .activity-table td { padding:12px 14px; border-bottom:1px solid #edf2f7; vertical-align:middle; background:#fff; font-size:.86rem; transition:background .15s; }
    .activity-table tr:last-child td { border-bottom:0; }
    .activity-table tbody tr:hover td { background:#f8fbff; }
    .activity-table th:last-child, .activity-table td:last-child { position:sticky; right:0; z-index:2; min-width:210px; box-shadow:-10px 0 18px rgba(15,23,42,.035); }
    .activity-table th:last-child { z-index:3; }
    .encoder { display:flex; align-items:center; gap:10px; }
    .encoder-avatar { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#e0e7ff; color:#3730a3; font-size:.78rem; font-weight:900; flex:0 0 auto; }
    .encoder-name { font-weight:800; color:#1e293b; }
    .muted { color:#64748b; font-size:.8rem; margin-top:3px; }
    .activity-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 9px; border-radius:999px; font-size:.74rem; font-weight:800; white-space:nowrap; }
    .activity-badge.latest { background:#dcfce7; color:#166534; }
    .activity-badge.manual { background:#dbeafe; color:#1d4ed8; }
    .activity-badge.cprf { background:#f3e8ff; color:#7e22ce; }
    .activity-badge.for-review { background:#fff7ed; color:#c2410c; }
    .activity-badge.approved { background:#dcfce7; color:#166534; }
    .activity-badge.returned { background:#fee2e2; color:#b91c1c; }
    .record-actions { display:flex; align-items:center; gap:7px; flex-wrap:nowrap; min-width:max-content; }
    .record-actions form { display:inline-flex; margin:0; }
    .record-link, .review-btn {
        min-height:36px;
        padding:8px 11px;
        border:1px solid transparent;
        border-radius:9px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        font-size:.75rem;
        line-height:1;
        font-weight:800;
        text-decoration:none;
        white-space:nowrap;
        box-sizing:border-box;
    }
    .record-link { color:#315cca; background:#eff6ff; border-color:#bfdbfe; }
    .record-link:hover { color:#1d4ed8; background:#dbeafe; border-color:#93c5fd; }
    .review-btn { font-family:inherit; cursor:pointer; }
    .review-btn.approve { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
    .review-btn.approve:hover { background:#bbf7d0; }
    .review-btn.return { background:#fee2e2; color:#b91c1c; border-color:#fecaca; }
    .review-btn.return:hover { background:#fecaca; }
    .review-dialog {
        position:fixed;
        inset:0;
        width:min(460px,calc(100% - 28px));
        max-height:calc(100vh - 32px);
        margin:auto;
        padding:0;
        border:0;
        border-radius:16px;
        color:#1e293b;
        box-shadow:0 24px 70px rgba(15,23,42,.28);
        overflow:auto;
    }
    .review-dialog::backdrop { background:rgba(15,23,42,.58); backdrop-filter:blur(2px); }
    .review-dialog-body { padding:22px; }
    .review-dialog h3 { margin:0 0 7px; font-size:1.15rem; color:#0f172a; }
    .review-dialog p { margin:0 0 15px; color:#64748b; font-size:.84rem; }
    .review-dialog textarea { width:100%; min-height:110px; resize:vertical; border:1px solid #cbd5e1; border-radius:10px; padding:10px; font:inherit; font-size:.84rem; box-sizing:border-box; }
    .review-dialog-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:13px; }
    .dialog-cancel { background:#e2e8f0; color:#334155; }
    .activity-empty { padding:44px 20px; text-align:center; color:#64748b; }
    .activity-pagination { padding:16px 18px; border-top:1px solid #e2e8f0; }
    .activity-pager { display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .activity-pager-summary { color:#64748b; font-size:.84rem; white-space:nowrap; }
    .activity-pager-summary strong { color:#334155; }
    .activity-pager-links { display:flex; align-items:center; justify-content:flex-end; gap:6px; flex-wrap:wrap; }
    .activity-page-link, .activity-page-current, .activity-page-disabled {
        min-width:38px;
        height:38px;
        padding:0 11px;
        border-radius:10px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        border:1px solid #e2e8f0;
        font-size:.82rem;
        font-weight:800;
        text-decoration:none;
        line-height:1;
    }
    .activity-page-link { color:#334155; background:#fff; transition:border-color .15s, color .15s, background .15s; }
    .activity-page-link:hover { color:#3762c8; border-color:#a5b4fc; background:#eef2ff; }
    .activity-page-current { color:#fff; background:#3762c8; border-color:#3762c8; box-shadow:0 5px 12px rgba(55,98,200,.22); }
    .activity-page-disabled { color:#94a3b8; background:#f8fafc; cursor:not-allowed; }
    .activity-page-ellipsis { min-width:28px; color:#94a3b8; text-align:center; font-weight:800; }
    body.dark-mode .monthly-activity-page { color:#cbd5e1; }
    body.dark-mode .monthly-report-card { background:linear-gradient(155deg,#0f172a 0%,#111827 100%); border-color:#334155; box-shadow:0 18px 38px rgba(2,6,23,.38); }
    body.dark-mode .activity-header h1 { color:#f8fafc; }
    body.dark-mode .activity-card, body.dark-mode .activity-table th, body.dark-mode .activity-table td, body.dark-mode .overview-card, body.dark-mode .review-dialog { background:#18181b; border-color:#334155; }
    body.dark-mode .overview-value { color:#f8fafc; }
    body.dark-mode .review-dialog h3 { color:#f8fafc; }
    body.dark-mode .review-dialog { color:#e2e8f0; }
    body.dark-mode .review-dialog textarea { background:#0f172a; color:#e2e8f0; border-color:#475569; }
    body.dark-mode .activity-card-heading, body.dark-mode .activity-filters { background:#111827; border-color:#334155; }
    body.dark-mode .activity-card-heading strong { color:#f8fafc; }
    body.dark-mode .activity-result-count { background:#1e3a5f; color:#bfdbfe; }
    body.dark-mode .activity-filters input, body.dark-mode .activity-filters select { background:#0f172a; color:#e2e8f0; border-color:#475569; }
    body.dark-mode .activity-table td, body.dark-mode .activity-table th, body.dark-mode .activity-pagination { border-color:#334155; }
    body.dark-mode .activity-table tbody tr:hover td { background:#1d2a3d; }
    body.dark-mode .activity-table th:last-child, body.dark-mode .activity-table td:last-child { box-shadow:-10px 0 18px rgba(2,6,23,.2); }
    body.dark-mode .encoder-name { color:#f1f5f9; }
    body.dark-mode .activity-pager-summary, body.dark-mode .activity-pager-summary strong { color:#cbd5e1; }
    body.dark-mode .activity-page-link { color:#cbd5e1; background:#18181b; border-color:#475569; }
    body.dark-mode .activity-page-link:hover { color:#c7d2fe; background:#312e81; border-color:#6366f1; }
    body.dark-mode .activity-page-disabled { color:#64748b; background:#111827; border-color:#334155; }
    @media(max-width:900px) {
        .activity-overview { grid-template-columns:1fr; }
        .activity-filters { grid-template-columns:1fr 1fr; }
        .activity-filter-field.search-filter { grid-column:1 / -1; }
    }
    @media(max-width:600px) {
        .monthly-report-card { padding:14px; border-radius:17px; }
        .activity-header { display:block; }
        .activity-header p { font-size:.86rem; }
        .overview-card { min-height:0; }
        .activity-card-heading { align-items:flex-start; flex-direction:column; gap:8px; }
        .activity-filters { grid-template-columns:1fr; }
        .activity-filter-field.search-filter { grid-column:auto; }
        .filter-actions { width:100%; }
        .filter-actions > * { flex:1; }
        .activity-table th:last-child, .activity-table td:last-child { position:static; box-shadow:none; }
        .activity-pager { align-items:stretch; flex-direction:column; }
        .activity-pager-summary { text-align:center; white-space:normal; }
        .activity-pager-links { justify-content:center; }
        .activity-page-number { display:none; }
    }
</style>

@php
    $displayName = static function ($record): string {
        $externalName = trim((string) ($record->recorded_by_name ?? ''));
        if ($externalName !== '') {
            return $externalName;
        }

        return trim((string) (
            $record->recordedBy?->full_name
            ?? $record->recordedBy?->name
            ?? $record->recordedBy?->username
            ?? (strtolower((string) $record->input_source) === 'cprf' ? 'CPRF Integration' : 'Unknown user')
        ));
    };
@endphp

<div class="monthly-activity-page">
<div class="monthly-report-card">
    <div class="activity-header">
        <div>
            <div class="activity-eyebrow"><i class="fa-solid fa-clock-rotate-left"></i> Submission audit trail</div>
            <h1>Monthly Record Activity</h1>
            <p>See who submitted each monthly energy record and when it entered the system.</p>
        </div>
    </div>

    <div class="activity-overview">
        <div class="overview-card review-summary">
            <div class="overview-icon" style="background:#fff7ed;color:#c2410c;"><i class="fa-solid fa-clipboard-check"></i></div>
            <div>
                <div class="overview-label">For Review</div>
                <div class="overview-value">{{ number_format((int) ($reviewCounts['for_review'] ?? 0)) }}</div>
                <div class="overview-detail">{{ number_format((int) ($reviewCounts['approved'] ?? 0)) }} approved &middot; {{ number_format((int) ($reviewCounts['returned'] ?? 0)) }} returned</div>
            </div>
        </div>

        <div class="overview-card missing-summary">
            <div class="overview-icon" style="background:#fef2f2;color:#dc2626;"><i class="fa-solid fa-calendar-xmark"></i></div>
            <div style="min-width:0;">
                <div class="overview-label">Missing {{ $currentMonth->format('F Y') }}</div>
                <div class="overview-value">{{ number_format($missingMonthlyFacilities->count()) }} facilities</div>
                <div class="overview-detail">Active facilities without a monthly record.</div>
                @if($missingMonthlyFacilities->isNotEmpty())
                    <details class="missing-list">
                        <summary>View facilities</summary>
                        <ul class="missing-list-items">
                            @foreach($missingMonthlyFacilities as $missingFacility)
                                <li>{{ $missingFacility->name }}{{ $missingFacility->department ? ' · '.$missingFacility->department : '' }}</li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        </div>

        @php
            $healthColors = [
                'healthy' => ['bg' => '#ecfdf5', 'color' => '#047857', 'icon' => 'fa-circle-check'],
                'waiting' => ['bg' => '#eff6ff', 'color' => '#2563eb', 'icon' => 'fa-clock'],
                'attention' => ['bg' => '#fff7ed', 'color' => '#c2410c', 'icon' => 'fa-triangle-exclamation'],
                'not_configured' => ['bg' => '#f1f5f9', 'color' => '#64748b', 'icon' => 'fa-plug-circle-xmark'],
            ];
            $healthTheme = $healthColors[$integrationHealth['key']] ?? $healthColors['not_configured'];
        @endphp
        <div class="overview-card integration-summary" style="--integration-color:{{ $healthTheme['color'] }};">
            <div class="overview-icon" style="background:{{ $healthTheme['bg'] }};color:{{ $healthTheme['color'] }};"><i class="fa-solid {{ $healthTheme['icon'] }}"></i></div>
            <div>
                <div class="overview-label">CPRF Integration</div>
                <div class="overview-value" style="color:{{ $healthTheme['color'] }};">{{ $integrationHealth['label'] }}</div>
                <div class="overview-detail">{{ $integrationHealth['detail'] }}</div>
            </div>
        </div>
    </div>

    <div class="activity-card">
        <div class="activity-card-heading">
            <strong><i class="fa-solid fa-list-check" style="color:#3762c8;margin-right:7px;"></i>Submission records</strong>
            <span class="activity-result-count"><i class="fa-solid fa-database"></i> {{ number_format($records->total()) }} {{ \Illuminate\Support\Str::plural('record', $records->total()) }}</span>
        </div>
        <form class="activity-filters" method="GET" action="{{ route('monthly-record-activity.index') }}">
            <label class="activity-filter-field search-filter">
                <span class="activity-filter-label">Search records</span>
                <input class="search-field" type="search" name="search" value="{{ $search }}" placeholder="Staff, facility, or department">
            </label>
            <label class="activity-filter-field">
                <span class="activity-filter-label">Input source</span>
                <select name="source">
                    <option value="">All sources</option>
                    <option value="manual" @selected($source === 'manual')>Manual Entry</option>
                    <option value="cprf" @selected($source === 'cprf')>Integrated System (CPRF)</option>
                </select>
            </label>
            <label class="activity-filter-field">
                <span class="activity-filter-label">Review status</span>
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="for_review" @selected($status === 'for_review')>For Review</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="returned" @selected($status === 'returned')>Returned</option>
                </select>
            </label>
            <label class="activity-filter-field">
                <span class="activity-filter-label">Reporting month</span>
                <input type="month" name="month" value="{{ $month }}">
            </label>
            <div class="filter-actions">
                <button class="filter-btn primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
                <a class="filter-btn reset" href="{{ route('monthly-record-activity.index') }}">Reset</a>
            </div>
        </form>

        <div class="activity-table-wrap">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Submitted by</th>
                        <th>Facility</th>
                        <th>Reporting month</th>
                        <th>Source</th>
                        <th>Review status</th>
                        <th>Date submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $name = $displayName($record);
                            $initial = mb_strtoupper(mb_substr($name, 0, 1));
                            $isCprf = strtolower((string) ($record->input_source ?? 'manual')) === 'cprf';
                            $periodLabel = ($record->month && $record->year)
                                ? \Carbon\Carbon::create((int) $record->year, (int) $record->month, 1)->format('F Y')
                                : 'Unknown period';
                            $reviewStatus = $record->review_status ?: 'for_review';
                            $reviewLabels = ['for_review' => 'For Review', 'approved' => 'Approved', 'returned' => 'Returned'];
                        @endphp
                        <tr>
                            <td>
                                <div class="encoder">
                                    <span class="encoder-avatar">{{ $initial ?: '?' }}</span>
                                    <div>
                                        <div class="encoder-name">
                                            {{ $name }}
                                            @if((int) $record->id === (int) $latestRecordId)
                                                <span class="activity-badge latest">Latest</span>
                                            @endif
                                        </div>
                                        <div class="muted">{{ $record->recordedBy?->role ? ucwords(str_replace('_', ' ', $record->recordedBy->role)) : ($isCprf ? 'External system' : 'User account unavailable') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $record->facility?->name ?? 'Unknown Facility' }}</strong>
                                @if($record->facility?->department)
                                    <div class="muted">{{ $record->facility->department }}</div>
                                @endif
                            </td>
                            <td>{{ $periodLabel }}</td>
                            <td>
                                <span class="activity-badge {{ $isCprf ? 'cprf' : 'manual' }}">
                                    <i class="fa-solid {{ $isCprf ? 'fa-plug' : 'fa-keyboard' }}"></i>
                                    {{ $isCprf ? 'CPRF Integration' : 'Manual Entry' }}
                                </span>
                            </td>
                            <td>
                                <span class="activity-badge {{ str_replace('_', '-', $reviewStatus) }}">
                                    <i class="fa-solid {{ $reviewStatus === 'approved' ? 'fa-circle-check' : ($reviewStatus === 'returned' ? 'fa-rotate-left' : 'fa-clock') }}"></i>
                                    {{ $reviewLabels[$reviewStatus] ?? 'For Review' }}
                                </span>
                                @if($record->reviewer)
                                    <div class="muted">by {{ $record->reviewer->full_name ?? $record->reviewer->name ?? $record->reviewer->username }}</div>
                                @endif
                                @if($record->review_remarks)
                                    <div class="muted" title="{{ $record->review_remarks }}">{{ \Illuminate\Support\Str::limit($record->review_remarks, 42) }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $activityAt = $isCprf ? $record->updated_at : $record->created_at;
                                @endphp
                                <strong>{{ optional($activityAt)->timezone(config('app.timezone'))->format('M d, Y') }}</strong>
                                <div class="muted">{{ optional($activityAt)->timezone(config('app.timezone'))->format('h:i A') }}{!! $isCprf && $record->updated_at?->gt($record->created_at) ? ' &middot; Updated' : '' !!}</div>
                            </td>
                            <td>
                                <div class="record-actions">
                                    @if($record->facility_id)
                                        <a class="record-link" href="{{ route('facilities.monthly-records', ['facility' => $record->facility_id, 'year' => $record->year]) }}">
                                            View <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    @endif
                                    @if($reviewStatus === 'for_review' || $reviewStatus === 'returned')
                                        <form method="POST" action="{{ route('monthly-record-activity.review', $record) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="review_status" value="approved">
                                            <button class="review-btn approve" type="submit"><i class="fa-solid fa-check"></i> Approve</button>
                                        </form>
                                    @endif
                                    @if($reviewStatus !== 'returned')
                                        <button
                                            class="review-btn return"
                                            type="button"
                                            data-return-record
                                            data-return-url="{{ route('monthly-record-activity.review', $record) }}"
                                            data-record-label="{{ $periodLabel }} · {{ $record->facility?->name ?? 'Unknown Facility' }}"
                                        ><i class="fa-solid fa-rotate-left"></i> Return</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="activity-empty">
                                    <i class="fa-regular fa-folder-open" style="font-size:2rem;margin-bottom:10px;"></i>
                                    <div>No monthly submissions match the selected filters.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="activity-pagination">
                {{ $records->onEachSide(1)->links('pagination.monthly-activity') }}
            </div>
        @endif
    </div>
</div>
</div>

<dialog class="review-dialog" id="returnRecordDialog">
    <div class="review-dialog-body">
        <h3>Return monthly record</h3>
        <p id="returnRecordLabel">Tell the encoder what needs to be corrected.</p>
        <form id="returnRecordForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="review_status" value="returned">
            <textarea name="review_remarks" required maxlength="2000" placeholder="Explain what the encoder needs to correct..."></textarea>
            <div class="review-dialog-actions">
                <button class="review-btn dialog-cancel" type="button" data-close-return-dialog>Cancel</button>
                <button class="review-btn return" type="submit">Return for correction</button>
            </div>
        </form>
    </div>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('returnRecordDialog');
    const form = document.getElementById('returnRecordForm');
    const label = document.getElementById('returnRecordLabel');
    if (!dialog || !form) return;

    document.querySelectorAll('[data-return-record]').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = button.dataset.returnUrl || '';
            label.textContent = (button.dataset.recordLabel || 'Monthly record') + ' — tell the encoder what needs to be corrected.';
            dialog.showModal();
        });
    });

    document.querySelector('[data-close-return-dialog]')?.addEventListener('click', function () {
        dialog.close();
    });

    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) dialog.close();
    });
});
</script>
@endsection
