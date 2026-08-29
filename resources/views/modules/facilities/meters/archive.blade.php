@extends('layouts.qc-admin')
@section('title', 'Meter Archive')

@section('content')
@php
    $subOnlyMode = (bool) ($subOnlyMode ?? false);
    $mainMeterId = (int) ($mainMeterId ?? 0);
    $archiveCount = method_exists($archivedMeters, 'total') ? $archivedMeters->total() : $archivedMeters->count();
    $backUrl = $subOnlyMode && $mainMeterId > 0
        ? route('modules.facilities.meters.main-submeters', [$facility->id, $mainMeterId])
        : route('modules.facilities.energy-profile.index', $facility->id);
@endphp

<style>
    .meter-archive-page {
        width:min(1440px,100%);
        margin:0 auto;
        padding: 18px;
        color: #1e293b;
    }

    .meter-archive-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(31, 38, 135, .06);
        padding: 24px;
    }

    .meter-archive-heading { display:flex; align-items:center; gap:13px; }
    .meter-archive-heading-icon {
        width:48px; height:48px; display:grid; place-items:center; flex:0 0 auto; border-radius:14px;
        color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); box-shadow:0 10px 22px rgba(37,99,235,.22);
    }

    .meter-archive-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .meter-archive-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.55rem, 2.2vw, 2.1rem);
        font-weight: 800;
        letter-spacing: 0;
    }

    .meter-archive-subtitle {
        margin: 5px 0 0;
        color: #64748b;
        font-size: .98rem;
    }

    .meter-archive-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        border-radius: 12px;
        padding: 0 16px;
        background: #2563eb;
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 10px 22px rgba(37, 99, 235, .18);
    }

    .meter-archive-shell {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
    }

    .meter-archive-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        background:#f8fafc;
    }

    .meter-archive-count {
        color: #1e293b;
        font-weight: 800;
    }

    .meter-archive-note {
        color: #64748b;
        font-size: .86rem;
        text-align: right;
    }

    .meter-archive-policy {
        display:inline-flex; align-items:center; gap:7px; padding:6px 9px; border:1px solid #fed7aa;
        border-radius:999px; background:#fff7ed; color:#9a3412; font-size:.75rem; font-weight:800;
    }

    .meter-archive-empty {
        min-height:190px;
        padding: 34px 16px;
        text-align: center;
        color: #64748b;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;
        gap:8px;
    }
    .meter-archive-empty-icon { width:50px; height:50px; display:grid; place-items:center; border-radius:15px; background:#eff6ff; color:#2563eb; font-size:1.05rem; }
    .meter-archive-empty strong { color:#1e293b; font-size:.98rem; }
    .meter-archive-empty span { font-size:.83rem; }

    .meter-archive-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .meter-archive-table th {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: .78rem;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
    }

    .meter-archive-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        transition:background-color .15s ease;
    }

    .meter-archive-table tbody tr:hover td { background:#fbfdff; }

    .meter-col-name { width: 32%; }
    .meter-col-reason { width: 25%; }
    .meter-col-date { width: 25%; }
    .meter-col-actions { width: 18%; text-align: right; }

    .meter-identity-cell { display:flex; align-items:center; gap:11px; min-width:0; }
    .meter-identity-icon {
        width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; border-radius:12px;
        background:#eff6ff; color:#2563eb;
    }

    .meter-name {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.25;
    }

    .meter-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 7px;
        color: #64748b;
        font-size: .84rem;
    }

    .meter-pill {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        border-radius: 999px;
        padding: 3px 9px;
        background: #eef4ff;
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 800;
    }

    .meter-pill.neutral {
        background: #f1f5f9;
        color: #475569;
    }

    .meter-reason {
        overflow: hidden;
        color: #334155;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .meter-reason-card {
        display:flex; align-items:flex-start; gap:9px; padding:9px 10px; border-radius:10px;
        background:#f8fafc; border:1px solid #e2e8f0;
    }
    .meter-reason-card i { color:#64748b; margin-top:2px; flex:0 0 auto; }

    .meter-date-row { display:flex; align-items:center; gap:7px; }
    .meter-date-row i { color:#2563eb; }

    .meter-retention-pill {
        display:inline-flex; align-items:center; gap:6px; margin-top:7px; padding:4px 8px; border-radius:999px;
        background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; font-size:.72rem; font-weight:800;
    }

    .meter-date {
        color: #1e293b;
        font-weight: 800;
    }

    .meter-by {
        margin-top: 4px;
        color: #64748b;
        font-size: .84rem;
    }

    .meter-actions {
        display: inline-flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .meter-actions form { margin:0; }

    .meter-btn,
    .meter-page-link,
    .meter-page-current {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        border: 0;
        border-radius: 12px;
        padding: 0 13px;
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .meter-btn.restore { background: #16a34a; }
    .meter-btn.danger { background: #e11d48; }
    .meter-btn { min-height:38px; padding:0 11px; font-size:.8rem; transition:transform .15s ease,filter .15s ease; }
    .meter-btn:hover { transform:translateY(-1px); filter:brightness(.96); }

    .meter-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 14px 16px;
        background: #f8fbff;
    }

    .meter-page-link {
        background: #eaf0f7;
        color: #1e293b;
    }

    .meter-page-link.disabled {
        color: #94a3b8;
    }

    .meter-page-current {
        background: #2563eb;
        color: #fff;
    }

    .meter-alert {
        margin-bottom: 12px;
        border-radius: 12px;
        padding: 12px 14px;
        font-weight: 800;
    }

    .meter-alert.success { background: #dcfce7; color: #166534; }
    .meter-alert.error { background: #fee2e2; color: #b91c1c; }

    .meter-confirm-overlay {
        position: fixed;
        inset: 0;
        z-index: 10060;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(4px);
    }

    .meter-confirm-overlay.is-open {
        display: flex;
    }

    .meter-confirm-panel {
        width: min(440px, 100%);
        overflow: hidden;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
    }

    .meter-confirm-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .meter-confirm-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        background: #fee2e2;
        color: #e11d48;
        flex: 0 0 auto;
    }

    .meter-confirm-icon.restore {
        background: #dcfce7;
        color: #16a34a;
    }

    .meter-confirm-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .meter-confirm-message {
        padding: 18px 20px 4px;
        color: #475569;
        line-height: 1.55;
    }

    .meter-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 20px 20px;
    }

    .meter-confirm-btn {
        min-height: 40px;
        border: 0;
        border-radius: 9px;
        padding: 0 16px;
        font-weight: 900;
        cursor: pointer;
    }

    .meter-confirm-btn.cancel {
        background: #f1f5f9;
        color: #334155;
    }

    .meter-confirm-btn.delete {
        background: #e11d48;
        color: #fff;
    }

    .meter-confirm-btn.restore {
        background: #16a34a;
        color: #fff;
    }

    body.dark-mode .meter-archive-page { color:#e2e8f0; }
    body.dark-mode .meter-archive-card {
        background:#0f172a !important;
        border-color:#334155 !important;
        box-shadow:0 18px 46px rgba(2,6,23,.42);
    }
    body.dark-mode .meter-archive-title { color:#f8fafc !important; }
    body.dark-mode .meter-archive-subtitle { color:#94a3b8 !important; }
    body.dark-mode .meter-archive-back { background:#2563eb !important; color:#fff !important; }
    body.dark-mode .meter-archive-shell { background:#0f172a !important; border-color:#334155 !important; }
    body.dark-mode .meter-archive-list-head { background:#111827 !important; border-color:#334155 !important; }
    body.dark-mode .meter-archive-count { color:#f8fafc !important; }
    body.dark-mode .meter-archive-note { color:#94a3b8 !important; }
    body.dark-mode .meter-archive-policy { background:#431407 !important; border-color:#9a3412 !important; color:#fed7aa !important; }
    body.dark-mode .meter-archive-empty { color:#94a3b8 !important; }
    body.dark-mode .meter-archive-empty-icon { background:#172554 !important; color:#60a5fa !important; }
    body.dark-mode .meter-archive-empty strong { color:#f8fafc !important; }
    body.dark-mode .meter-archive-table th { background:#111827 !important; border-color:#334155 !important; color:#93c5fd !important; }
    body.dark-mode .meter-archive-table td { background:#0f172a !important; border-color:#263449 !important; color:#e2e8f0 !important; }
    body.dark-mode .meter-archive-table tbody tr:hover td { background:#111827 !important; }
    body.dark-mode .meter-name,
    body.dark-mode .meter-date { color:#f8fafc !important; }
    body.dark-mode .meter-meta,
    body.dark-mode .meter-by,
    body.dark-mode .meter-reason { color:#cbd5e1 !important; }
    body.dark-mode .meter-pill { background:#172554 !important; color:#bfdbfe !important; }
    body.dark-mode .meter-pill.neutral { background:#1e293b !important; color:#cbd5e1 !important; }
    body.dark-mode .meter-identity-icon { background:#172554 !important; color:#60a5fa !important; }
    body.dark-mode .meter-reason-card { background:#111827 !important; border-color:#334155 !important; }
    body.dark-mode .meter-retention-pill { background:#431407 !important; border-color:#9a3412 !important; color:#fed7aa !important; }
    body.dark-mode .meter-pagination { background:#111827 !important; }
    body.dark-mode .meter-page-link { background:#1e293b !important; color:#cbd5e1 !important; }
    body.dark-mode .meter-confirm-overlay { background:rgba(2,6,23,.76) !important; }
    body.dark-mode .meter-confirm-panel { background:#0f172a !important; border:1px solid #334155; }
    body.dark-mode .meter-confirm-head { border-color:#334155 !important; }
    body.dark-mode .meter-confirm-title { color:#f8fafc !important; }
    body.dark-mode .meter-confirm-message { color:#cbd5e1 !important; }
    body.dark-mode .meter-confirm-btn.cancel { background:#1e293b !important; color:#e2e8f0 !important; }

    @media (max-width: 820px) {
        .meter-archive-page { padding:10px; }
        .meter-archive-card {
            padding: 18px;
            border-radius: 16px;
        }

        .meter-archive-header,
        .meter-archive-list-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .meter-archive-back {
            width: 100%;
        }

        .meter-archive-note {
            text-align: left;
        }

        .meter-archive-table,
        .meter-archive-table tbody,
        .meter-archive-table tr,
        .meter-archive-table td {
            display: block;
            width: 100%;
        }

        .meter-archive-table thead {
            display: none;
        }

        .meter-archive-table tr {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .meter-archive-table td {
            padding: 8px 0;
            border-bottom: 0;
        }

        .meter-col-actions {
            text-align: left;
        }

        .meter-actions,
        .meter-actions form,
        .meter-actions .meter-btn {
            width: 100%;
        }

        .meter-actions { flex-wrap:wrap; }
    }
</style>

<div class="meter-archive-page">
    @if(session('success'))
        <div class="meter-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="meter-alert error">{{ session('error') }}</div>
    @endif

    <div class="report-card-container meter-archive-card">
        <div class="meter-archive-header">
            <div class="meter-archive-heading">
                <span class="meter-archive-heading-icon"><i class="fa fa-box-archive"></i></span>
                <div>
                    <h2 class="meter-archive-title">{{ $subOnlyMode ? 'Sub-meter Archive' : 'Meter Archive' }}</h2>
                    <p class="meter-archive-subtitle">Review and restore archived meters for <strong>{{ $facility->name }}</strong>.</p>
                </div>
            </div>
            <a href="{{ $backUrl }}" class="meter-archive-back">
                <i class="fa fa-arrow-left"></i>
                <span>{{ $subOnlyMode ? 'Back to Sub-meters' : 'Back to Energy Profile' }}</span>
            </a>
        </div>

        <div class="meter-archive-shell">
            <div class="meter-archive-list-head">
                <div class="meter-archive-count"><i class="fa fa-box-archive" style="color:#2563eb;margin-right:6px;"></i> Archived Meters ({{ $archiveCount }})</div>
                <div class="meter-archive-policy"><i class="fa fa-shield-clock"></i> 30-day recovery window</div>
            </div>

            @if($archivedMeters->count() === 0)
                <div class="meter-archive-empty">
                    <span class="meter-archive-empty-icon"><i class="fa fa-box-open"></i></span>
                    <strong>No archived meters</strong>
                    <span>Meters removed from the active directory will appear here for 30 days.</span>
                </div>
            @else
                <table class="meter-archive-table">
                    <thead>
                        <tr>
                            <th class="meter-col-name">Meter</th>
                            <th class="meter-col-reason">Reason</th>
                            <th class="meter-col-date">Archived</th>
                            <th class="meter-col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedMeters as $meter)
                            @php
                                $reason = trim((string) ($meter->archive_reason ?? ''));
                                $archivedAt = $meter->deleted_at;
                                $daysLeft = $archivedAt ? max(0, (int) ceil(30 - $archivedAt->diffInDays(now()))) : null;
                                $expiresAt = $archivedAt ? $archivedAt->copy()->addDays(30) : null;
                            @endphp
                            <tr>
                                <td class="meter-col-name">
                                    <div class="meter-identity-cell">
                                        <span class="meter-identity-icon"><i class="fa fa-gauge-high"></i></span>
                                        <div>
                                            <div class="meter-name">{{ $meter->meter_name }}</div>
                                            <div class="meter-meta">
                                                <span class="meter-pill">{{ strtoupper((string) ($meter->meter_type ?: 'meter')) }}</span>
                                                <span class="meter-pill neutral">{{ $meter->meter_number ?: 'No number' }}</span>
                                                <span>{{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) . ' kWh baseline' : 'No baseline' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="meter-col-reason">
                                    <div class="meter-reason-card">
                                        <i class="fa fa-message"></i>
                                        <div class="meter-reason" title="{{ $reason !== '' ? $reason : 'No reason provided' }}">
                                            {{ $reason !== '' ? $reason : 'No reason provided' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="meter-col-date">
                                    <div class="meter-date-row">
                                        <i class="fa fa-calendar-xmark"></i>
                                        <div class="meter-date">{{ $archivedAt ? $archivedAt->format('M d, Y h:i A') : '-' }}</div>
                                    </div>
                                    <div class="meter-by">
                                        By {{ $meter->deletedByUser?->full_name ?? $meter->deletedByUser?->name ?? $meter->deletedByUser?->username ?? 'Unknown' }}
                                    </div>
                                    @if($daysLeft !== null)
                                        <span class="meter-retention-pill" title="{{ $expiresAt ? 'Scheduled cleanup: ' . $expiresAt->format('M d, Y h:i A') : '' }}">
                                            <i class="fa fa-hourglass-half"></i>
                                            {{ $daysLeft > 0 ? $daysLeft . ' days remaining' : 'Due for cleanup' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="meter-col-actions">
                                    <div class="meter-actions">
                                        @if($canManageMeters)
                                            <form method="POST"
                                                action="{{ route('modules.facilities.meters.restore', [$facility->id, $meter->id]) }}"
                                                class="js-meter-confirm-form"
                                                data-confirm-type="restore"
                                                data-confirm-title="Restore Meter"
                                                data-confirm-message="Restore meter {{ $meter->meter_name }}?"
                                                data-confirm-action="Restore">
                                                @csrf
                                                <button type="submit" class="meter-btn restore">
                                                    <i class="fa fa-rotate-left"></i>
                                                    <span>Restore</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canForceDeleteMeters)
                                            <form method="POST"
                                                action="{{ route('modules.facilities.meters.force-delete', [$facility->id, $meter->id]) }}"
                                                class="js-meter-confirm-form"
                                                data-confirm-type="delete"
                                                data-confirm-title="Permanently Delete Meter"
                                                data-confirm-message="Permanently delete meter {{ $meter->meter_name }}? This cannot be undone."
                                                data-confirm-action="Delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="meter-btn danger">
                                                    <i class="fa fa-trash"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($archivedMeters->hasPages())
                    <div class="meter-pagination">
                        <div class="meter-archive-note">
                            Showing {{ $archivedMeters->firstItem() }} to {{ $archivedMeters->lastItem() }} of {{ $archivedMeters->total() }}
                        </div>
                        <div class="meter-actions">
                            @if($archivedMeters->onFirstPage())
                                <span class="meter-page-link disabled">Previous</span>
                            @else
                                <a href="{{ $archivedMeters->previousPageUrl() }}" class="meter-page-link">Previous</a>
                            @endif
                            <span class="meter-page-current">{{ $archivedMeters->currentPage() }} / {{ $archivedMeters->lastPage() }}</span>
                            @if($archivedMeters->hasMorePages())
                                <a href="{{ $archivedMeters->nextPageUrl() }}" class="meter-page-link">Next</a>
                            @else
                                <span class="meter-page-link disabled">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<div id="meterConfirmModal" class="meter-confirm-overlay" aria-hidden="true">
    <div class="meter-confirm-panel" role="dialog" aria-modal="true" aria-labelledby="meterConfirmTitle">
        <div class="meter-confirm-head">
            <div id="meterConfirmIcon" class="meter-confirm-icon" aria-hidden="true">
                <i class="fa fa-triangle-exclamation"></i>
            </div>
            <h3 id="meterConfirmTitle" class="meter-confirm-title"></h3>
        </div>
        <div id="meterConfirmMessage" class="meter-confirm-message"></div>
        <div class="meter-confirm-actions">
            <button type="button" id="meterConfirmCancelBtn" class="meter-confirm-btn cancel">Cancel</button>
            <button type="button" id="meterConfirmSubmitBtn" class="meter-confirm-btn delete">Delete</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('meterConfirmModal');
    var titleEl = document.getElementById('meterConfirmTitle');
    var messageEl = document.getElementById('meterConfirmMessage');
    var iconEl = document.getElementById('meterConfirmIcon');
    var cancelBtn = document.getElementById('meterConfirmCancelBtn');
    var submitBtn = document.getElementById('meterConfirmSubmitBtn');
    var pendingForm = null;

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingForm = null;
    }

    function openModal(form) {
        if (!modal) return;
        var type = form.getAttribute('data-confirm-type') || 'delete';
        pendingForm = form;
        if (titleEl) titleEl.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
        if (messageEl) messageEl.textContent = form.getAttribute('data-confirm-message') || 'Continue with this action?';
        if (submitBtn) {
            submitBtn.textContent = form.getAttribute('data-confirm-action') || 'Confirm';
            submitBtn.classList.toggle('restore', type === 'restore');
            submitBtn.classList.toggle('delete', type !== 'restore');
        }
        if (iconEl) {
            iconEl.classList.toggle('restore', type === 'restore');
            iconEl.innerHTML = type === 'restore'
                ? '<i class="fa fa-rotate-left"></i>'
                : '<i class="fa fa-triangle-exclamation"></i>';
        }
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (cancelBtn) cancelBtn.focus();
    }

    document.querySelectorAll('.js-meter-confirm-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (this.dataset.confirmed === 'true') return;
            event.preventDefault();
            openModal(this);
        });
    });

    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = 'true';
            pendingForm.submit();
        });
    }
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) closeModal();
        });
    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeModal();
    });
});
</script>
@endsection
