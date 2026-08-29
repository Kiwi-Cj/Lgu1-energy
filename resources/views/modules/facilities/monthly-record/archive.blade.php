@extends('layouts.qc-admin')
@section('title', 'Monthly Records Archive')

@section('content')
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

    .archive-period {
        font-weight: 900;
        color: #0f172a;
    }

    .archive-meter-name {
        font-weight: 700;
        color: #0f172a;
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
        margin-right: 6px;
    }

    .archive-row-actions {
        display: inline-flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .archive-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 36px;
        border: 0;
        border-radius: 8px;
        padding: 0 12px;
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
        font-size: .85rem;
    }

    .archive-btn.restore { background: #16a34a; }
    .archive-btn.restore:hover { background: #15803d; }

    .archive-btn.danger { background: #e11d48; }
    .archive-btn.danger:hover { background: #be123c; }

    .archive-alert {
        margin-bottom: 16px;
        padding: 12px 16px;
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .archive-alert.success {
        background: #dcfce7;
        color: #166534;
    }

    .archive-alert.error {
        background: #fee2e2;
        color: #b91c1c;
    }

    .archive-confirm-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(4px);
    }

    .archive-confirm-overlay.is-open {
        display: flex;
    }

    .archive-confirm-modal {
        width: min(440px, 100%);
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
        overflow: hidden;
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

    .archive-confirm-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .archive-confirm-body {
        padding: 18px 20px 4px;
        color: #475569;
        line-height: 1.55;
    }

    .archive-confirm-period {
        color: #0f172a;
        font-weight: 900;
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

    .archive-confirm-btn.delete:hover {
        background: #be123c;
    }

    .archive-confirm-btn.restore {
        background: #16a34a;
        color: #fff;
    }

    .archive-confirm-btn.restore:hover {
        background: #15803d;
    }

    body.dark-mode .archive-confirm-modal {
        background: #0f172a;
        border: 1px solid #334155;
    }

    body.dark-mode .archive-confirm-head {
        border-bottom-color: #334155;
    }

    body.dark-mode .archive-confirm-title,
    body.dark-mode .archive-confirm-period {
        color: #e2e8f0;
    }

    body.dark-mode .archive-confirm-body {
        color: #cbd5e1;
    }

    body.dark-mode .archive-confirm-btn.cancel {
        background: #1f2937;
        color: #e2e8f0;
    }

    .archive-confirm-icon.restore {
        background: #dcfce7;
        color: #16a34a;
    }
</style>

<div class="archive-page">
    @if(session('success'))
        <div class="archive-alert success">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="archive-alert error">
            <i class="fa fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div class="archive-card">
        <div class="archive-header">
            <div>
                <div style="font-size:1.9rem;font-weight:700;color:#0f172a;">Monthly Records Archive</div>
                <div style="font-size:1rem;color:#64748b;">Facility: <span style="font-weight:600;color:#1e293b;">{{ $facility->name }}</span></div>
            </div>
            <a href="{{ route('facilities.monthly-records', $facility->id) }}" class="archive-back">
                <i class="fa fa-arrow-left"></i>
                <span>Back to Records</span>
            </a>
        </div>

        <div class="archive-shell">
            <div class="archive-list-head">
                <div class="archive-count">Archived Records ({{ $archivedRecords->count() }})</div>
                <div class="archive-note">Records will be permanently deleted after 30 days in archive.</div>
            </div>

            @if($archivedRecords->isEmpty())
                <div class="archive-empty">No archived monthly records yet.</div>
            @else
                <div style="overflow-x:auto;">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;">Billing Period</th>
                                <th style="text-align:center;">Scope / Meter</th>
                                <th style="text-align:center;">Actual kWh</th>
                                <th style="text-align:center;">Energy Cost</th>
                                <th style="text-align:center;">Alert Level</th>
                                <th style="text-align:center;">Archive Details</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivedRecords as $record)
                                @php
                                    $monthLabel = ($record->month && is_numeric($record->month))
                                        ? date('F', mktime(0,0,0,(int)$record->month,1))
                                        : (string) $record->month;
                                    $deletedAt = $record->deleted_at;
                                    $daysLeft = $deletedAt ? max(0, (int) ceil(30 - $deletedAt->diffInDays(now()))) : null;
                                @endphp
                                <tr>
                                    <td style="text-align:center;">
                                        <div class="archive-period">{{ trim($monthLabel . ' ' . $record->year) }}</div>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($record->meter)
                                            <span class="archive-pill">{{ strtoupper((string) $record->meter->meter_type) }}</span>
                                            <span class="archive-meter-name">{{ $record->meter->meter_name }}</span>
                                        @else
                                            <span class="archive-pill" style="background:#f1f5f9;color:#475569;">FACILITY AGGREGATE</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;color:#0f172a;">
                                        {{ is_numeric($record->actual_kwh) ? number_format((float) $record->actual_kwh, 2) : '-' }}
                                    </td>
                                    <td style="text-align:center;color:#0f172a;font-weight:600;">
                                        PHP {{ is_numeric($record->energy_cost) ? number_format((float) $record->energy_cost, 2) : '-' }}
                                    </td>
                                    <td style="text-align:center;">
                                        {{ $record->alert ?: '-' }}
                                    </td>
                                    <td style="text-align:center;color:#475569;font-size:.9rem;">
                                        <div>{{ $deletedAt ? $deletedAt->format('M d, Y h:i A') : '-' }}</div>
                                        <div style="margin-top:4px;font-weight:700;color:#334155;">{{ $record->deletedByUser?->name ?? 'Unknown user' }}</div>
                                        <div style="margin-top:5px;color:#64748b;max-width:260px;white-space:normal;">{{ $record->archive_reason ?: 'No reason recorded (legacy archive)' }}</div>
                                        @if($daysLeft !== null)
                                            <div style="font-size:.8rem;color:#64748b;">{{ $daysLeft > 0 ? $daysLeft . ' day(s) left' : '⚠️ Due for cleanup' }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <div class="archive-row-actions" style="justify-content:center;">
                                            <form method="POST"
                                                action="{{ route('energy-records.restore', ['facility' => $facility->id, 'record' => $record->id]) }}"
                                                style="display:inline;"
                                                class="js-archive-confirm-form"
                                                data-confirm-type="restore"
                                                data-confirm-title="Restore Monthly Record"
                                                data-confirm-message="Restore monthly record for {{ trim($monthLabel . ' ' . $record->year) }}?"
                                                data-confirm-action="Restore">
                                                @csrf
                                                <button type="submit" class="archive-btn restore">
                                                    <i class="fa fa-rotate-left"></i>
                                                    <span>Restore</span>
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('energy-records.force-delete', ['facility' => $facility->id, 'record' => $record->id]) }}"
                                                style="display:inline;"
                                                class="js-archive-confirm-form"
                                                data-confirm-type="delete"
                                                data-confirm-title="Permanently Delete Record"
                                                data-confirm-message="Permanently delete monthly record for {{ trim($monthLabel . ' ' . $record->year) }}? This cannot be undone."
                                                data-confirm-action="Delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="archive-btn danger">
                                                    <i class="fa fa-trash"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div id="archiveConfirmModal" class="archive-confirm-overlay" aria-hidden="true">
        <div class="archive-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="archiveConfirmTitle">
            <div class="archive-confirm-head">
                <div id="archiveConfirmIcon" class="archive-confirm-icon" aria-hidden="true">
                    <i class="fa fa-triangle-exclamation"></i>
                </div>
                <h3 id="archiveConfirmTitle" class="archive-confirm-title"></h3>
            </div>
            <div id="archiveConfirmMessage" class="archive-confirm-body"></div>
            <div class="archive-confirm-actions">
                <button type="button" class="archive-confirm-btn cancel" id="archiveConfirmCancelBtn">Cancel</button>
                <button type="button" class="archive-confirm-btn delete" id="archiveConfirmSubmitBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('archiveConfirmModal');
    const titleEl = document.getElementById('archiveConfirmTitle');
    const messageEl = document.getElementById('archiveConfirmMessage');
    const iconEl = document.getElementById('archiveConfirmIcon');
    const cancelBtn = document.getElementById('archiveConfirmCancelBtn');
    const confirmBtn = document.getElementById('archiveConfirmSubmitBtn');
    let pendingForm = null;

    const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingForm = null;
    };

    const openModal = (form) => {
        if (!modal) return;
        const type = form.getAttribute('data-confirm-type') || 'delete';
        pendingForm = form;
        if (titleEl) titleEl.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
        if (messageEl) messageEl.textContent = form.getAttribute('data-confirm-message') || 'Continue with this action?';
        if (confirmBtn) {
            confirmBtn.textContent = form.getAttribute('data-confirm-action') || 'Confirm';
            confirmBtn.classList.toggle('restore', type === 'restore');
            confirmBtn.classList.toggle('delete', type !== 'restore');
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
    };

    document.querySelectorAll('.js-archive-confirm-form').forEach((form) => {
        form.addEventListener('submit', function(event) {
            if (this.dataset.confirmed === 'true') return;
            event.preventDefault();
            openModal(this);
        });
    });

    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
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
        if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
            closeModal();
        }
    });
});
</script>
@endsection
