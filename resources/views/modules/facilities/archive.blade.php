@extends('layouts.qc-admin')
@section('title', 'Facilities Archive')

@section('content')
@php
    $filters = $filters ?? ['q' => '', 'type' => '', 'status' => '', 'archived_from' => '', 'archived_to' => ''];
    $typeOptions = $typeOptions ?? collect();
    $statusOptions = $statusOptions ?? collect();
    $exportColumnOptions = $exportColumnOptions ?? [
        'facility' => 'Facility',
        'type' => 'Type',
        'status' => 'Status',
        'barangay' => 'Barangay',
        'archive_reason' => 'Archive Reason',
        'deleted_by' => 'Deleted By',
        'archived_at' => 'Archived At',
    ];
    $selectedExportColumns = $selectedExportColumns ?? array_keys($exportColumnOptions);
    $canForceDelete = $canForceDelete ?? false;
    $archiveCount = method_exists($archivedFacilities, 'total') ? $archivedFacilities->total() : $archivedFacilities->count();
@endphp

<style>
    .archive-page {
        padding: 10px 12px 24px;
        color: #1e293b;
    }

    .archive-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(31, 38, 135, .06);
        padding: 26px;
    }

    .archive-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .archive-title {
        margin: 0;
        font-size: clamp(1.55rem, 2.2vw, 2.1rem);
        font-weight: 800;
        color: #2563eb;
        letter-spacing: 0;
    }

    .archive-subtitle {
        margin: 5px 0 0;
        color: #64748b;
        font-size: .98rem;
    }

    .archive-back {
        display: inline-flex;
        align-items: center;
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

    .archive-shell {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
    }

    .archive-toolbar {
        display: grid;
        grid-template-columns: minmax(240px, 1.4fr) minmax(150px, .55fr) minmax(150px, .55fr) minmax(290px, .85fr) auto;
        gap: 10px;
        align-items: end;
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fbff;
    }

    .archive-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .archive-field label {
        font-size: .8rem;
        font-weight: 800;
        color: #475569;
    }

    .archive-input,
    .archive-select {
        width: 100%;
        min-height: 42px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
        padding: 9px 12px;
        font-size: .94rem;
        outline: none;
    }

    .archive-input:focus,
    .archive-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .13);
    }

    .archive-date-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .archive-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .archive-btn,
    .archive-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        border: 0;
        border-radius: 12px;
        padding: 0 14px;
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .archive-icon-btn {
        width: 42px;
        padding: 0;
    }

    .archive-btn.primary { background: #2563eb; }
    .archive-btn.soft,
    .archive-icon-btn.soft { background: #eaf0f7; color: #334155; }
    .archive-btn.csv { background: #0f766e; }
    .archive-btn.excel { background: #166534; }
    .archive-btn.restore { background: #16a34a; }
    .archive-btn.danger { background: #e11d48; }

    .archive-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .archive-count {
        font-weight: 800;
        color: #1e293b;
    }

    .archive-note {
        color: #64748b;
        font-size: .86rem;
        text-align: right;
    }

    .archive-empty {
        padding: 34px 16px;
        text-align: center;
        color: #64748b;
    }

    .archive-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .archive-table th {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: .78rem;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
    }

    .archive-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
    }

    .archive-col-facility { width: 32%; }
    .archive-col-reason { width: 27%; }
    .archive-col-date { width: 21%; }
    .archive-col-actions { width: 20%; text-align: right; }

    .archive-facility-name {
        font-weight: 900;
        color: #0f172a;
        line-height: 1.25;
    }

    .archive-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 7px;
        color: #64748b;
        font-size: .84rem;
    }

    .archive-pill {
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

    .archive-pill.neutral {
        background: #f1f5f9;
        color: #475569;
    }

    .archive-reason {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .archive-reason-text {
        overflow: hidden;
        color: #334155;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .archive-link-btn {
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 4px 10px;
        font-size: .78rem;
        font-weight: 900;
        cursor: pointer;
    }

    .archive-date {
        color: #1e293b;
        font-weight: 800;
    }

    .archive-by {
        margin-top: 4px;
        color: #64748b;
        font-size: .84rem;
    }

    .archive-row-actions {
        display: inline-flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .archive-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 14px 16px;
        background: #f8fbff;
    }

    .archive-page-link,
    .archive-page-current {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        border-radius: 10px;
        padding: 0 12px;
        font-weight: 800;
        text-decoration: none;
    }

    .archive-page-link { background: #eaf0f7; color: #1e293b; }
    .archive-page-link.disabled { color: #94a3b8; }
    .archive-page-current { background: #2563eb; color: #fff; }

    .archive-modal {
        position: fixed;
        inset: 0;
        z-index: 10050;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(3px);
    }

    .archive-modal-panel {
        position: relative;
        width: min(560px, 100%);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .2);
        padding: 20px;
    }

    .archive-modal-close {
        position: absolute;
        top: 10px;
        right: 12px;
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 1.35rem;
        cursor: pointer;
    }

    .archive-modal-label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .archive-modal-title {
        margin: 6px 34px 12px 0;
        color: #1e293b;
        font-size: 1.15rem;
        font-weight: 900;
    }

    .archive-modal-text {
        max-height: 45vh;
        overflow: auto;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        color: #334155;
        line-height: 1.55;
        padding: 14px;
        white-space: pre-wrap;
    }

    .archive-confirm-overlay {
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

    .archive-confirm-overlay.is-open {
        display: flex;
    }

    .archive-confirm-panel {
        width: min(440px, 100%);
        overflow: hidden;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
    }

    .archive-confirm-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .archive-confirm-icon {
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

    .archive-confirm-icon.restore {
        background: #dcfce7;
        color: #16a34a;
    }

    .archive-confirm-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .archive-confirm-message {
        padding: 18px 20px 4px;
        color: #475569;
        line-height: 1.55;
    }

    .archive-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 20px 20px;
    }

    .archive-confirm-btn {
        min-height: 40px;
        border: 0;
        border-radius: 9px;
        padding: 0 16px;
        font-weight: 900;
        cursor: pointer;
    }

    .archive-confirm-btn.cancel {
        background: #f1f5f9;
        color: #334155;
    }

    .archive-confirm-btn.delete {
        background: #e11d48;
        color: #fff;
    }

    .archive-confirm-btn.restore {
        background: #16a34a;
        color: #fff;
    }

    .archive-alert {
        margin-bottom: 12px;
        border-radius: 12px;
        padding: 12px 14px;
        font-weight: 800;
    }

    .archive-alert.success { background: #dcfce7; color: #166534; }
    .archive-alert.error { background: #fee2e2; color: #b91c1c; }

    @media (max-width: 1180px) {
        .archive-toolbar {
            grid-template-columns: 1fr 1fr;
        }

        .archive-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 820px) {
        .archive-card {
            padding: 18px;
            border-radius: 16px;
        }

        .archive-header {
            flex-direction: column;
        }

        .archive-back {
            width: 100%;
            justify-content: center;
        }

        .archive-toolbar {
            grid-template-columns: 1fr;
        }

        .archive-date-row {
            grid-template-columns: 1fr;
        }

        .archive-list-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .archive-note {
            text-align: left;
        }

        .archive-table,
        .archive-table tbody,
        .archive-table tr,
        .archive-table td {
            display: block;
            width: 100%;
        }

        .archive-table thead {
            display: none;
        }

        .archive-table tr {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .archive-table td {
            padding: 8px 0;
            border-bottom: 0;
        }

        .archive-col-actions {
            text-align: left;
        }

        .archive-row-actions,
        .archive-row-actions form,
        .archive-row-actions .archive-btn {
            width: 100%;
        }
    }
</style>

<div class="archive-page">
    @if(session('success'))
        <div class="archive-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="archive-alert error">{{ session('error') }}</div>
    @endif

    <div class="archive-card">
        <div class="archive-header">
            <div>
                <h2 class="archive-title">Facilities Archive</h2>
                <p class="archive-subtitle">Restore archived facilities or permanently remove old entries.</p>
            </div>
            <a href="{{ route('modules.facilities.index') }}" class="archive-back">
                <i class="fa fa-arrow-left"></i>
                <span>Back to Facilities</span>
            </a>
        </div>

        <div class="archive-shell">
            <div class="archive-list-head">
                <div class="archive-count">Archived Facilities ({{ $archiveCount }})</div>
                <div class="archive-note">Auto permanent delete after 30 days in archive.</div>
            </div>

            @if($archivedFacilities->count() === 0)
                <div class="archive-empty">No archived facilities yet.</div>
            @else
                <table class="archive-table">
                    <thead>
                        <tr>
                            <th class="archive-col-facility">Facility</th>
                            <th class="archive-col-reason">Reason</th>
                            <th class="archive-col-date">Archived</th>
                            <th class="archive-col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedFacilities as $facility)
                            @php
                                $fullReason = trim((string) ($facility->archive_reason ?? ''));
                                $archivedAt = $facility->deleted_at;
                                $daysLeft = $archivedAt ? max(0, (int) ceil(30 - $archivedAt->diffInDays(now()))) : null;
                            @endphp
                            <tr>
                                <td class="archive-col-facility">
                                    <div class="archive-facility-name">{{ $facility->name }}</div>
                                    <div class="archive-meta">
                                        <span class="archive-pill">{{ $facility->type ?: 'No type' }}</span>
                                        <span class="archive-pill neutral">{{ ucfirst((string) ($facility->status ?: 'unknown')) }}</span>
                                        <span>{{ $facility->barangay ?: 'No barangay' }}</span>
                                    </div>
                                </td>
                                <td class="archive-col-reason">
                                    <div class="archive-reason">
                                        <span class="archive-reason-text" title="{{ $fullReason !== '' ? $fullReason : 'No reason provided' }}">
                                            {{ $fullReason !== '' ? $fullReason : 'No reason provided' }}
                                        </span>
                                        @if($fullReason !== '')
                                            <button type="button"
                                                class="archive-link-btn"
                                                onclick="openArchiveReasonModal(@js($facility->name), @js($fullReason))">
                                                View
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="archive-col-date">
                                    <div class="archive-date">{{ $archivedAt ? $archivedAt->format('M d, Y h:i A') : '-' }}</div>
                                    <div class="archive-by">
                                        By {{ $facility->deletedByUser?->full_name ?? $facility->deletedByUser?->name ?? $facility->deletedByUser?->username ?? 'Unknown' }}
                                        @if($daysLeft !== null)
                                            <span>- {{ $daysLeft > 0 ? $daysLeft . ' day(s) left' : 'Due for cleanup' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="archive-col-actions">
                                    <div class="archive-row-actions">
                                        <form method="POST"
                                            action="{{ route('modules.facilities.restore', $facility->id) }}"
                                            class="js-archive-confirm-form"
                                            data-confirm-type="restore"
                                            data-confirm-title="Restore Facility"
                                            data-confirm-message="Restore facility {{ $facility->name }}?"
                                            data-confirm-action="Restore">
                                            @csrf
                                            <button type="submit" class="archive-btn restore">
                                                <i class="fa fa-rotate-left"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>

                                        @if($canForceDelete)
                                            <form method="POST"
                                                action="{{ route('modules.facilities.force-delete', $facility->id) }}"
                                                class="js-archive-confirm-form"
                                                data-confirm-type="delete"
                                                data-confirm-title="Permanently Delete Facility"
                                                data-confirm-message="Permanently delete {{ $facility->name }}? This cannot be undone."
                                                data-confirm-action="Delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="archive-btn danger">
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

                @if(method_exists($archivedFacilities, 'hasPages') && $archivedFacilities->hasPages())
                    <div class="archive-pagination">
                        <div class="archive-note">
                            Showing {{ $archivedFacilities->firstItem() }} to {{ $archivedFacilities->lastItem() }} of {{ $archivedFacilities->total() }}
                        </div>
                        <div class="archive-actions">
                            @if($archivedFacilities->onFirstPage())
                                <span class="archive-page-link disabled">Previous</span>
                            @else
                                <a href="{{ $archivedFacilities->previousPageUrl() }}" class="archive-page-link">Previous</a>
                            @endif
                            <span class="archive-page-current">{{ $archivedFacilities->currentPage() }} / {{ $archivedFacilities->lastPage() }}</span>
                            @if($archivedFacilities->hasMorePages())
                                <a href="{{ $archivedFacilities->nextPageUrl() }}" class="archive-page-link">Next</a>
                            @else
                                <span class="archive-page-link disabled">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<div id="archiveConfirmModal" class="archive-confirm-overlay" aria-hidden="true">
    <div class="archive-confirm-panel" role="dialog" aria-modal="true" aria-labelledby="archiveConfirmTitle">
        <div class="archive-confirm-head">
            <div id="archiveConfirmIcon" class="archive-confirm-icon" aria-hidden="true">
                <i class="fa fa-triangle-exclamation"></i>
            </div>
            <h3 id="archiveConfirmTitle" class="archive-confirm-title"></h3>
        </div>
        <div id="archiveConfirmMessage" class="archive-confirm-message"></div>
        <div class="archive-confirm-actions">
            <button type="button" id="archiveConfirmCancelBtn" class="archive-confirm-btn cancel">Cancel</button>
            <button type="button" id="archiveConfirmSubmitBtn" class="archive-confirm-btn delete">Delete</button>
        </div>
    </div>
</div>

<div id="archiveReasonModal" class="archive-modal">
    <div class="archive-modal-panel">
        <button type="button" onclick="closeArchiveReasonModal()" class="archive-modal-close" aria-label="Close">&times;</button>
        <div class="archive-modal-label">Archive Reason</div>
        <h3 id="archiveReasonModalFacility" class="archive-modal-title"></h3>
        <div id="archiveReasonModalText" class="archive-modal-text"></div>
        <div class="archive-actions" style="margin-top:14px;">
            <button type="button" onclick="closeArchiveReasonModal()" class="archive-btn primary">Close</button>
        </div>
    </div>
</div>

<script>
function openArchiveReasonModal(facilityName, reasonText) {
    var modal = document.getElementById('archiveReasonModal');
    var facilityEl = document.getElementById('archiveReasonModalFacility');
    var textEl = document.getElementById('archiveReasonModalText');
    if (!modal || !facilityEl || !textEl) return;
    facilityEl.textContent = facilityName || 'Facility';
    textEl.textContent = reasonText || '-';
    modal.style.display = 'flex';
}

function closeArchiveReasonModal() {
    var modal = document.getElementById('archiveReasonModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    var confirmModal = document.getElementById('archiveConfirmModal');
    var confirmTitle = document.getElementById('archiveConfirmTitle');
    var confirmMessage = document.getElementById('archiveConfirmMessage');
    var confirmIcon = document.getElementById('archiveConfirmIcon');
    var cancelBtn = document.getElementById('archiveConfirmCancelBtn');
    var submitBtn = document.getElementById('archiveConfirmSubmitBtn');
    var pendingForm = null;

    function closeConfirmModal() {
        if (!confirmModal) return;
        confirmModal.classList.remove('is-open');
        confirmModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingForm = null;
    }

    function openConfirmModal(form) {
        if (!confirmModal) return;
        var type = form.getAttribute('data-confirm-type') || 'delete';
        pendingForm = form;
        if (confirmTitle) confirmTitle.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
        if (confirmMessage) confirmMessage.textContent = form.getAttribute('data-confirm-message') || 'Continue with this action?';
        if (submitBtn) {
            submitBtn.textContent = form.getAttribute('data-confirm-action') || 'Confirm';
            submitBtn.classList.toggle('restore', type === 'restore');
            submitBtn.classList.toggle('delete', type !== 'restore');
        }
        if (confirmIcon) {
            confirmIcon.classList.toggle('restore', type === 'restore');
            confirmIcon.innerHTML = type === 'restore'
                ? '<i class="fa fa-rotate-left"></i>'
                : '<i class="fa fa-triangle-exclamation"></i>';
        }
        confirmModal.classList.add('is-open');
        confirmModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (cancelBtn) cancelBtn.focus();
    }

    document.querySelectorAll('.js-archive-confirm-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (this.dataset.confirmed === 'true') return;
            event.preventDefault();
            openConfirmModal(this);
        });
    });

    if (cancelBtn) cancelBtn.addEventListener('click', closeConfirmModal);
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = 'true';
            pendingForm.submit();
        });
    }
    if (confirmModal) {
        confirmModal.addEventListener('click', function(event) {
            if (event.target === confirmModal) closeConfirmModal();
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') return;
        closeArchiveReasonModal();
        closeConfirmModal();
    });

    document.addEventListener('click', function(event) {
        var modal = document.getElementById('archiveReasonModal');
        if (modal && event.target === modal) {
            closeArchiveReasonModal();
        }
    });
});
</script>
@endsection
