@extends('layouts.qc-admin')

@section('title', 'Monthly Record Activity')

@section('content')
<style>
    .monthly-activity-page { font-family:Inter,sans-serif; color:#1e293b; }
    .activity-header { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:22px; }
    .activity-header h1 { margin:0 0 7px; font-size:1.75rem; color:#0f172a; }
    .activity-header p { margin:0; color:#64748b; }
    .activity-overview { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
    .overview-card { background:#fff; border:1px solid #e2e8f0; border-radius:15px; padding:17px; display:flex; align-items:flex-start; gap:13px; box-shadow:0 5px 18px rgba(15,23,42,.04); }
    .overview-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; }
    .overview-label { color:#64748b; font-size:.75rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .overview-value { color:#0f172a; font-size:1.25rem; line-height:1.2; font-weight:900; margin-top:4px; }
    .overview-detail { color:#64748b; font-size:.78rem; margin-top:5px; }
    .missing-list { margin-top:8px; }
    .missing-list summary { color:#3762c8; font-size:.78rem; font-weight:800; cursor:pointer; }
    .missing-list-items { margin:8px 0 0; padding:8px 0 0 18px; max-height:130px; overflow:auto; border-top:1px solid #e2e8f0; color:#475569; font-size:.78rem; }
    .activity-card { background:#fff; border:1px solid #e2e8f0; border-radius:18px; box-shadow:0 8px 28px rgba(15,23,42,.05); overflow:hidden; }
    .activity-filters { display:grid; grid-template-columns:minmax(220px,1fr) 175px 175px 175px auto; gap:10px; padding:18px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
    .activity-filters input, .activity-filters select { width:100%; min-height:42px; padding:9px 12px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; color:#334155; }
    .filter-actions { display:flex; gap:8px; }
    .filter-btn { min-height:42px; padding:9px 15px; display:inline-flex; align-items:center; justify-content:center; gap:7px; border:0; border-radius:10px; font-weight:800; text-decoration:none; cursor:pointer; }
    .filter-btn.primary { background:#3762c8; color:#fff; }
    .filter-btn.reset { background:#e2e8f0; color:#334155; }
    .activity-table-wrap { overflow-x:auto; }
    .activity-table { width:100%; border-collapse:collapse; min-width:1250px; }
    .activity-table th { padding:14px 16px; text-align:left; color:#64748b; background:#fff; border-bottom:1px solid #e2e8f0; font-size:.76rem; text-transform:uppercase; letter-spacing:.045em; }
    .activity-table td { padding:15px 16px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .activity-table tr:last-child td { border-bottom:0; }
    .encoder { display:flex; align-items:center; gap:10px; }
    .encoder-avatar { width:36px; height:36px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#e0e7ff; color:#3730a3; font-weight:900; flex:0 0 auto; }
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
    body.dark-mode .activity-header h1 { color:#f8fafc; }
    body.dark-mode .activity-card, body.dark-mode .activity-table th, body.dark-mode .overview-card, body.dark-mode .review-dialog { background:#18181b; border-color:#334155; }
    body.dark-mode .overview-value { color:#f8fafc; }
    body.dark-mode .review-dialog h3 { color:#f8fafc; }
    body.dark-mode .review-dialog { color:#e2e8f0; }
    body.dark-mode .review-dialog textarea { background:#0f172a; color:#e2e8f0; border-color:#475569; }
    body.dark-mode .activity-filters { background:#111827; border-color:#334155; }
    body.dark-mode .activity-filters input, body.dark-mode .activity-filters select { background:#0f172a; color:#e2e8f0; border-color:#475569; }
    body.dark-mode .activity-table td, body.dark-mode .activity-table th, body.dark-mode .activity-pagination { border-color:#334155; }
    body.dark-mode .encoder-name { color:#f1f5f9; }
    body.dark-mode .activity-pager-summary, body.dark-mode .activity-pager-summary strong { color:#cbd5e1; }
    body.dark-mode .activity-page-link { color:#cbd5e1; background:#18181b; border-color:#475569; }
    body.dark-mode .activity-page-link:hover { color:#c7d2fe; background:#312e81; border-color:#6366f1; }
    body.dark-mode .activity-page-disabled { color:#64748b; background:#111827; border-color:#334155; }
    @media(max-width:900px) {
        .activity-overview { grid-template-columns:1fr; }
        .activity-filters { grid-template-columns:1fr 1fr; }
        .activity-filters .search-field { grid-column:1 / -1; }
    }
    @media(max-width:600px) {
        .activity-header { display:block; }
        .activity-filters { grid-template-columns:1fr; }
        .activity-filters .search-field { grid-column:auto; }
        .filter-actions { width:100%; }
        .filter-actions > * { flex:1; }
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
    <div class="activity-header">
        <div>
            <h1>Monthly Record Activity</h1>
            <p>See who submitted each monthly energy record and when it entered the system.</p>
        </div>
    </div>

    <div class="activity-overview">
        <div class="overview-card">
            <div class="overview-icon" style="background:#fff7ed;color:#c2410c;"><i class="fa-solid fa-clipboard-check"></i></div>
            <div>
                <div class="overview-label">For Review</div>
                <div class="overview-value">{{ number_format((int) ($reviewCounts['for_review'] ?? 0)) }}</div>
                <div class="overview-detail">{{ number_format((int) ($reviewCounts['approved'] ?? 0)) }} approved &middot; {{ number_format((int) ($reviewCounts['returned'] ?? 0)) }} returned</div>
            </div>
        </div>

        <div class="overview-card">
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
        <div class="overview-card">
            <div class="overview-icon" style="background:{{ $healthTheme['bg'] }};color:{{ $healthTheme['color'] }};"><i class="fa-solid {{ $healthTheme['icon'] }}"></i></div>
            <div>
                <div class="overview-label">CPRF Integration</div>
                <div class="overview-value" style="color:{{ $healthTheme['color'] }};">{{ $integrationHealth['label'] }}</div>
                <div class="overview-detail">{{ $integrationHealth['detail'] }}</div>
            </div>
        </div>
    </div>

    <div class="activity-card">
        <form class="activity-filters" method="GET" action="{{ route('monthly-record-activity.index') }}">
            <input class="search-field" type="search" name="search" value="{{ $search }}" placeholder="Search staff, facility, or department">
            <select name="source" aria-label="Input source">
                <option value="">All sources</option>
                <option value="manual" @selected($source === 'manual')>Manual Entry</option>
                <option value="cprf" @selected($source === 'cprf')>Integrated System (CPRF)</option>
            </select>
            <select name="status" aria-label="Review status">
                <option value="">All statuses</option>
                <option value="for_review" @selected($status === 'for_review')>For Review</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
                <option value="returned" @selected($status === 'returned')>Returned</option>
            </select>
            <input type="month" name="month" value="{{ $month }}" aria-label="Reporting month">
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
                        <th></th>
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
                                <strong>{{ optional($activityAt)->timezone('Asia/Manila')->format('M d, Y') }}</strong>
                                <div class="muted">{{ optional($activityAt)->timezone('Asia/Manila')->format('h:i A') }}{!! $isCprf && $record->updated_at?->gt($record->created_at) ? ' &middot; Updated' : '' !!}</div>
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
