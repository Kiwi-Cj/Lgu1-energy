@extends('layouts.qc-admin')
@section('title', 'Unapproved Meters')

@section('content')
@php
    $filters = $filters ?? ['q' => '', 'status' => ''];
    $totalMain = $unapprovedMainMeters->count();
    $totalSub = $unapprovedSubMeters->count();
    $totalUnapproved = $totalMain + $totalSub;
@endphp

<style>
    .approval-page {
        display: grid;
        gap: 14px;
        padding: 12px;
    }

    .approval-alert {
        position: fixed;
        top: 22px;
        right: 22px;
        z-index: 10070;
        width: min(380px, calc(100vw - 32px));
        border-radius: 12px;
        padding: 14px 16px;
        font-weight: 800;
        border: 1px solid transparent;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
        opacity: 1;
        transform: translateY(0);
        transition: opacity .22s ease, transform .22s ease;
    }

    .approval-alert.is-hidden {
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
    }

    .approval-alert.success {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }

    .approval-alert.error {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .approval-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .approval-kicker {
        color: #2563eb;
        font-size: .78rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .approval-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.55rem;
        font-weight: 900;
    }

    .approval-subtitle {
        color: #64748b;
        margin-top: 5px;
        font-weight: 650;
    }

    .approval-header-actions,
    .approval-form-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .approval-btn {
        border: 1px solid transparent;
        border-radius: 10px;
        padding: 10px 13px;
        font-weight: 850;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        white-space: nowrap;
    }

    .approval-btn.primary {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 8px 18px rgba(37, 99, 235, .18);
    }

    .approval-btn.secondary {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #e2e8f0;
    }

    .approval-btn.ghost {
        background: #fff;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    .approval-btn.approve {
        background: #16a34a;
        color: #fff;
        border-color: #15803d;
        box-shadow: 0 8px 18px rgba(22, 163, 74, .18);
    }

    .approval-btn.approve:hover {
        background: #15803d;
    }

    .approval-shell {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
        overflow: hidden;
    }

    .approval-filter {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(150px, 190px) auto;
        gap: 12px;
        align-items: end;
        padding: 16px 18px;
        background: #fbfdff;
        border-bottom: 1px solid #e2e8f0;
    }

    .approval-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .approval-label {
        color: #334155;
        font-size: .82rem;
        font-weight: 850;
    }

    .approval-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 11px 13px;
        color: #0f172a;
        background: #fff;
        min-height: 42px;
    }

    .approval-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        outline: none;
    }

    .approval-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        padding: 14px 18px 0;
    }

    .approval-stat {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        padding: 12px;
    }

    .approval-stat-label {
        color: #64748b;
        font-size: .75rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .approval-stat-value {
        margin-top: 3px;
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 950;
    }

    .approval-section {
        padding: 18px;
    }

    .approval-section + .approval-section {
        padding-top: 8px;
    }

    .approval-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }

    .approval-section-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.08rem;
        font-weight: 950;
    }

    .approval-count {
        display: inline-flex;
        min-width: 36px;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .82rem;
        font-weight: 950;
    }

    .approval-count.main {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .approval-count.sub {
        background: #ede9fe;
        color: #6d28d9;
    }

    .approval-table-wrap {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .approval-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1040px;
    }

    .approval-table.sub {
        min-width: 1120px;
    }

    .approval-table th,
    .approval-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #edf2f7;
        text-align: left;
        vertical-align: middle;
    }

    .approval-table th {
        color: #334155;
        background: #f8fafc;
        font-size: .78rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .approval-table tr:last-child td {
        border-bottom: 0;
    }

    .approval-table tbody tr:hover {
        background: #fbfdff;
    }

    .meter-name-cell {
        color: #0f172a;
        font-weight: 950;
    }

    .muted-cell {
        color: #475569;
    }

    .status-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: 4px 9px;
        background: #ecfdf5;
        color: #047857;
        font-size: .78rem;
        font-weight: 900;
    }

    .empty-approval {
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        color: #64748b;
        padding: 18px 20px;
        font-weight: 750;
    }

    .empty-approval.warning {
        background: #fffbeb;
        border-color: #f59e0b;
        color: #92400e;
    }

    .approval-modal {
        position: fixed;
        inset: 0;
        z-index: 10050;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(3px);
    }

    .approval-modal.is-open {
        display: flex;
    }

    .approval-modal-card {
        width: min(440px, 100%);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
        overflow: hidden;
    }

    .approval-modal-head {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 18px 18px 12px;
    }

    .approval-modal-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dcfce7;
        color: #15803d;
        flex: 0 0 auto;
    }

    .approval-modal-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.18rem;
        font-weight: 950;
    }

    .approval-modal-copy {
        margin: 5px 0 0;
        color: #64748b;
        line-height: 1.45;
        font-weight: 650;
    }

    .approval-modal-target {
        margin: 0 18px 16px;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        color: #1e293b;
        font-weight: 850;
    }

    .approval-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        padding: 14px 18px 18px;
        border-top: 1px solid #e2e8f0;
        background: #fbfdff;
    }

    @media (max-width: 760px) {
        .approval-filter {
            grid-template-columns: 1fr;
        }

        .approval-form-actions,
        .approval-btn {
            width: 100%;
        }
    }
</style>

<div class="approval-page">
    @if(session('success'))
        <div class="approval-alert success" data-approval-toast>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="approval-alert error" data-approval-toast>
            <i class="fa fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div class="approval-header">
        <div>
            <div class="approval-kicker">Meter Approval</div>
            <h2 class="approval-title">Unapproved Meters</h2>
            <div class="approval-subtitle">Facility: <strong>{{ $facility->name }}</strong></div>
        </div>
        <div class="approval-header-actions">
            <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="approval-btn secondary">
                <i class="fa fa-arrow-left"></i> Energy Profile
            </a>
            <a href="{{ route('modules.facilities.meters.archive', $facility->id) }}" class="approval-btn ghost">
                <i class="fa fa-box-archive"></i> Meter Archive
            </a>
        </div>
    </div>

    <div class="approval-shell">
        <form method="GET" action="{{ route('modules.facilities.meters.unapproved', $facility->id) }}" class="approval-filter">
            <div class="approval-field">
                <label class="approval-label">Search</label>
                <input class="approval-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Meter name / number / location / notes">
            </div>
            <div class="approval-field">
                <label class="approval-label">Status</label>
                <select class="approval-input" name="status">
                    <option value="">All</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="approval-form-actions">
                <button type="submit" class="approval-btn primary"><i class="fa fa-filter"></i> Filter</button>
                <a href="{{ route('modules.facilities.meters.unapproved', $facility->id) }}" class="approval-btn secondary">Reset</a>
            </div>
        </form>

        <div class="approval-summary">
            <div class="approval-stat">
                <div class="approval-stat-label">Pending Total</div>
                <div class="approval-stat-value">{{ $totalUnapproved }}</div>
            </div>
            <div class="approval-stat">
                <div class="approval-stat-label">Main Meters</div>
                <div class="approval-stat-value">{{ $totalMain }}</div>
            </div>
            <div class="approval-stat">
                <div class="approval-stat-label">Sub Meters</div>
                <div class="approval-stat-value">{{ $totalSub }}</div>
            </div>
        </div>

        @if($totalUnapproved === 0)
            <div class="approval-section">
                <div class="empty-approval">No unapproved meters found for current filters.</div>
            </div>
        @endif

        <section class="approval-section">
            <div class="approval-section-head">
                <h3 class="approval-section-title">Unapproved Main Meters</h3>
                <span class="approval-count main">{{ $totalMain }}</span>
            </div>

            @if($totalMain === 0)
                <div class="empty-approval">No unapproved main meters found.</div>
            @else
                <div class="approval-table-wrap">
                    <table class="approval-table">
                        <thead>
                            <tr>
                                <th>Meter</th>
                                <th>Number</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th style="text-align:right;">Baseline kWh</th>
                                <th>Notes</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unapprovedMainMeters as $meter)
                                <tr>
                                    <td class="meter-name-cell">{{ $meter->meter_name }}</td>
                                    <td class="muted-cell">{{ $meter->meter_number ?: '-' }}</td>
                                    <td class="muted-cell">{{ $meter->location ?: '-' }}</td>
                                    <td><span class="status-pill">{{ ucfirst((string) $meter->status) }}</span></td>
                                    <td class="muted-cell">{{ $meter->created_at ? $meter->created_at->format('M d, Y h:i A') : '-' }}</td>
                                    <td class="muted-cell" style="text-align:right;">{{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) : '-' }}</td>
                                    <td class="muted-cell" style="max-width:260px;">
                                        <span title="{{ $meter->notes ?: '' }}">{{ \Illuminate\Support\Str::limit($meter->notes ?: '-', 60) }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($canApproveMeters)
                                            <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facility->id, $meter->id]) }}" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="_redirect_to" value="meters_unapproved">
                                                <button type="button" class="approval-btn approve" data-approval-trigger data-meter-type="main meter" data-meter-name="{{ $meter->meter_name }}">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </form>
                                        @else
                                            <span class="muted-cell">View only</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="approval-section">
            <div class="approval-section-head">
                <h3 class="approval-section-title">Unapproved Sub Meters</h3>
                <span class="approval-count sub">{{ $totalSub }}</span>
            </div>

            @if(! $hasMainMeter)
                <div class="empty-approval warning">No main meter found for this facility. Sub meter list is hidden.</div>
            @elseif($totalSub === 0)
                <div class="empty-approval">No unapproved sub meters found.</div>
            @else
                <div class="approval-table-wrap">
                    <table class="approval-table sub">
                        <thead>
                            <tr>
                                <th>Meter</th>
                                <th>Number</th>
                                <th>Parent</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th style="text-align:right;">Baseline kWh</th>
                                <th>Notes</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unapprovedSubMeters as $meter)
                                <tr>
                                    <td class="meter-name-cell">{{ $meter->meter_name }}</td>
                                    <td class="muted-cell">{{ $meter->meter_number ?: '-' }}</td>
                                    <td class="muted-cell">{{ $meter->parentMeter?->meter_name ?: '-' }}</td>
                                    <td class="muted-cell">{{ $meter->location ?: '-' }}</td>
                                    <td><span class="status-pill">{{ ucfirst((string) $meter->status) }}</span></td>
                                    <td class="muted-cell">{{ $meter->created_at ? $meter->created_at->format('M d, Y h:i A') : '-' }}</td>
                                    <td class="muted-cell" style="text-align:right;">{{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) : '-' }}</td>
                                    <td class="muted-cell" style="max-width:260px;">
                                        <span title="{{ $meter->notes ?: '' }}">{{ \Illuminate\Support\Str::limit($meter->notes ?: '-', 60) }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($canApproveMeters)
                                            <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facility->id, $meter->id]) }}" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="_redirect_to" value="meters_unapproved">
                                                <button type="button" class="approval-btn approve" data-approval-trigger data-meter-type="sub meter" data-meter-name="{{ $meter->meter_name }}">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </form>
                                        @else
                                            <span class="muted-cell">View only</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>

<div class="approval-modal" id="approvalConfirmModal" aria-hidden="true">
    <div class="approval-modal-card" role="dialog" aria-modal="true" aria-labelledby="approvalConfirmTitle">
        <div class="approval-modal-head">
            <div class="approval-modal-icon"><i class="fa fa-check"></i></div>
            <div>
                <h3 class="approval-modal-title" id="approvalConfirmTitle">Approve Meter</h3>
                <p class="approval-modal-copy">This meter will become available for records, monitoring, and linked meter workflows.</p>
            </div>
        </div>
        <div class="approval-modal-target" id="approvalConfirmTarget">Meter</div>
        <div class="approval-modal-actions">
            <button type="button" class="approval-btn secondary" id="approvalCancelBtn">Cancel</button>
            <button type="button" class="approval-btn approve" id="approvalSubmitBtn"><i class="fa fa-check"></i> Approve</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('approvalConfirmModal');
    const target = document.getElementById('approvalConfirmTarget');
    const cancelBtn = document.getElementById('approvalCancelBtn');
    const submitBtn = document.getElementById('approvalSubmitBtn');
    let pendingForm = null;

    function closeApprovalModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        pendingForm = null;
    }

    document.querySelectorAll('[data-approval-trigger]').forEach(function(button) {
        button.addEventListener('click', function() {
            pendingForm = button.closest('form');
            const meterType = button.getAttribute('data-meter-type') || 'meter';
            const meterName = button.getAttribute('data-meter-name') || 'Selected meter';

            if (target) {
                target.textContent = meterName + ' (' + meterType + ')';
            }

            if (modal) {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }
        });
    });

    cancelBtn?.addEventListener('click', closeApprovalModal);

    submitBtn?.addEventListener('click', function() {
        if (pendingForm) {
            pendingForm.submit();
        }
    });

    modal?.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeApprovalModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeApprovalModal();
        }
    });

    document.querySelectorAll('[data-approval-toast]').forEach(function(toast) {
        setTimeout(function() {
            toast.classList.add('is-hidden');
        }, 2800);

        setTimeout(function() {
            toast.remove();
        }, 3300);
    });
});
</script>
@endsection
