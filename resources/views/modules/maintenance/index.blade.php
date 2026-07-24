<meta name="csrf-token" content="{{ csrf_token() }}">
@extends('layouts.qc-admin')
@section('title', 'Facilities Needing Maintenance')

@php
    $user = auth()->user();
    $userRole = strtolower($user->role ?? '');
@endphp

@section('content')
<style>
    /* Report Card Container */
    .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        border: 1px solid #eef2f6;
        margin-bottom: 2rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .page-header h2 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .page-header h2 span { color: #2563eb; }
    .page-title-group {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .cimm-integration-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border: 1px solid #a7f3d0;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .cimm-integration-badge.is-disabled {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }
    .cimm-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .14);
    }
    .cimm-integration-badge.is-disabled .cimm-status-dot {
        background: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .12);
    }
    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .quick-add-btn,
    .history-link-btn {
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .quick-add-btn { background: #10b981; }
    .history-link-btn { background: #2563eb; }
    .quick-add-btn:hover,
    .history-link-btn:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-box {
        padding: 24px 20px;
        border-radius: 14px;
        transition: transform 0.2s;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .stat-box:hover { transform: translateY(-3px); }
    .stat-label { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
    .stat-value { font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: #1e293b; }
    .stat-needing { background: #fff1f2; }
    .stat-needing .stat-label { color: #e11d48; }
    .stat-pending { background: #fefce8; }
    .stat-pending .stat-label { color: #a16207; }
    .stat-ongoing { background: #f0fdf4; }
    .stat-ongoing .stat-label { color: #15803d; }
    .stat-completed { background: #ecfeff; }
    .stat-completed .stat-label { color: #0e7490; }

    /* Filter Section */
    .filter-section {
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 25px;
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-group label { font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; }
    .filter-group select, .filter-group input {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        min-width: 160px;
        background: #fff;
        font-size: 0.95rem;
    }
    .btn-filter {
        background: linear-gradient(90deg,#2563eb,#6366f1);
        color: #fff;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-filter:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-filter.btn-reset {
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
        text-decoration: none;
    }

    /* Table Styling */
    .maint-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .maint-table { width: 100%; border-collapse: collapse; background: #fff; text-align: center; }
    .maint-table thead { background: #f1f5f9; }
    .maint-table th { padding: 15px; font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase; }
    .maint-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.95rem; }
    .maint-table tr:hover { background-color: #f8fafc; }
    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .table-search {
        width: min(420px, 100%);
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 8px 12px;
        font-size: 0.92rem;
        color: #1e293b;
        background: #fff;
    }
    .table-search:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.25);
    }
    .result-count {
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 700;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .status-pill.pending { background: #fffbeb; color: #a16207; border-color: #fde68a; }
    .status-pill.ongoing { background: #ecfeff; color: #0e7490; border-color: #bae6fd; }
    .status-pill.completed { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .remarks-cell {
        color: #64748b;
        max-width: 300px;
        margin: 0 auto;
        text-align: left;
    }
    .facility-cell {
        font-weight: 700;
    }
    .facility-identity {
        display: flex;
        align-items: center;
        gap: 11px;
        text-align: left;
    }
    .facility-thumbnail,
    .facility-thumbnail-fallback {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 10px;
        border: 1px solid #dbeafe;
    }
    .facility-thumbnail {
        display: block;
        object-fit: cover;
        background: #f1f5f9;
    }
    .facility-thumbnail-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #60a5fa;
    }
    .facility-issues-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 7px;
        padding: 5px 9px;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .75rem;
        font-weight: 800;
        cursor: pointer;
    }
    .facility-issues-toggle i { transition: transform .2s ease; }
    .facility-issues-toggle[aria-expanded="true"] i { transform: rotate(180deg); }
    .facility-issues-row.hidden-row { display: none; }
    .facility-issues-cell { padding: 0 !important; background: #f8fafc; }
    .facility-issues-panel {
        padding: 14px 18px 18px;
        display: grid;
        gap: 9px;
    }
    .facility-issues-heading {
        color: #475569;
        font-size: .78rem;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .facility-issue-item {
        display: grid;
        grid-template-columns: minmax(190px, 1.25fr) minmax(90px, .55fr) minmax(90px, .55fr) minmax(220px, 1.5fr) auto;
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        text-align: left;
    }
    .facility-issue-period,
    .facility-issue-remarks { color: #64748b; font-size: .86rem; }
    .facility-issue-remarks { overflow-wrap: anywhere; }
    @media (max-width: 900px) {
        .facility-issue-item { grid-template-columns: 1fr; }
    }
    .remarks-muted {
        color: #64748b;
    }
    .empty-row-cell {
        padding: 40px;
        color: #94a3b8;
    }
    .empty-row-cell.compact {
        padding: 28px;
    }
    .hidden-row {
        display: none;
    }

    /* Maintenance Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.6);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        padding: 16px;
    }
    .maintenance-modal {
        width: min(560px, 95vw);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
        max-height: 92vh;
        overflow-y: auto;
    }
    .maintenance-modal-header {
        padding: 20px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 18px 18px 0 0;
    }
    .maintenance-modal-title {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e293b;
    }
    .maintenance-modal-close {
        background: none;
        border: none;
        font-size: 1.6rem;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
    }
    .maintenance-modal-close:hover { color: #334155; }
    .maintenance-modal-body { padding: 20px 22px; }
    .maintenance-form { display: flex; flex-direction: column; gap: 14px; }
    .maintenance-form .field-group { display: flex; flex-direction: column; gap: 6px; }
    .maintenance-form .field-label {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .maintenance-form .field-control {
        width: 100%;
        padding: 11px 12px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        font-size: 0.95rem;
        background: #fff;
        color: #1e293b;
    }
    .maintenance-form .field-control:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.24);
    }
    .maintenance-form .field-control[disabled] {
        background: #f8fafc;
        color: #64748b;
    }
    .maintenance-form textarea.field-control {
        min-height: 84px;
        resize: vertical;
    }
    .maintenance-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .trigger-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 8px;
    }
    .maintenance-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 4px;
    }
    .maintenance-btn-cancel,
    .maintenance-btn-save {
        border: none;
        border-radius: 9px;
        padding: 10px 16px;
        font-weight: 700;
        cursor: pointer;
    }
    .maintenance-btn-cancel {
        background: #f1f5f9;
        color: #475569;
    }
    .maintenance-btn-save {
        background: #2563eb;
        color: #fff;
    }

    body.dark-mode .maintenance-page .report-card {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 18px 34px rgba(2, 6, 23, 0.5);
    }
    body.dark-mode .maintenance-page .page-header h2,
    body.dark-mode .maintenance-page .stat-value,
    body.dark-mode .maintenance-page .maint-table td,
    body.dark-mode .maintenance-page .maintenance-modal-title {
        color: #e2e8f0;
    }
    body.dark-mode .maintenance-page .cimm-integration-badge {
        background: rgba(6, 78, 59, .35);
        border-color: #047857;
        color: #a7f3d0;
    }
    body.dark-mode .maintenance-page .cimm-integration-badge.is-disabled {
        background: rgba(127, 29, 29, .3);
        border-color: #b91c1c;
        color: #fecaca;
    }
    body.dark-mode .maintenance-page .filter-group label,
    body.dark-mode .maintenance-page .result-count,
    body.dark-mode .maintenance-page .maintenance-form .field-label,
    body.dark-mode .maintenance-page .remarks-cell,
    body.dark-mode .maintenance-page .remarks-muted {
        color: #94a3b8;
    }
    body.dark-mode .maintenance-page .filter-section {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .filter-group select,
    body.dark-mode .maintenance-page .filter-group input,
    body.dark-mode .maintenance-page .table-search,
    body.dark-mode .maintenance-page .maintenance-form .field-control {
        background: #0b1220;
        color: #e2e8f0;
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .table-search::placeholder,
    body.dark-mode .maintenance-page .maintenance-form .field-control::placeholder {
        color: #64748b;
    }
    body.dark-mode .maintenance-page .btn-filter.btn-reset {
        background: #111827;
        color: #e2e8f0;
        border-color: #475569;
    }
    body.dark-mode .maintenance-page .maint-table-wrapper,
    body.dark-mode .maintenance-page .maint-table {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .maint-table thead {
        background: #111827;
    }
    body.dark-mode .maintenance-page .maint-table th,
    body.dark-mode .maintenance-page .maint-table td {
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .maint-table th {
        color: #94a3b8;
    }
    body.dark-mode .maintenance-page .maint-table tr:hover {
        background-color: #1f2937;
    }
    body.dark-mode .maintenance-page .status-pill.pending {
        background: rgba(146, 64, 14, 0.3);
        color: #fde68a;
        border-color: rgba(251, 191, 36, 0.35);
    }
    body.dark-mode .maintenance-page .status-pill.ongoing {
        background: rgba(14, 116, 144, 0.25);
        color: #67e8f9;
        border-color: rgba(125, 211, 252, 0.35);
    }
    body.dark-mode .maintenance-page .status-pill.completed {
        background: rgba(22, 101, 52, 0.25);
        color: #86efac;
        border-color: rgba(74, 222, 128, 0.3);
    }
    body.dark-mode .maintenance-page .stat-box {
        border-color: #334155;
        box-shadow: none;
    }
    body.dark-mode .maintenance-page .stat-needing {
        background: rgba(190, 24, 93, 0.18);
    }
    body.dark-mode .maintenance-page .stat-needing .stat-label {
        color: #fda4af;
    }
    body.dark-mode .maintenance-page .stat-pending {
        background: rgba(146, 64, 14, 0.22);
    }
    body.dark-mode .maintenance-page .stat-pending .stat-label {
        color: #fde68a;
    }
    body.dark-mode .maintenance-page .stat-ongoing {
        background: rgba(22, 101, 52, 0.22);
    }
    body.dark-mode .maintenance-page .stat-ongoing .stat-label {
        color: #86efac;
    }
    body.dark-mode .maintenance-page .stat-completed {
        background: rgba(14, 116, 144, 0.22);
    }
    body.dark-mode .maintenance-page .stat-completed .stat-label {
        color: #67e8f9;
    }
    body.dark-mode .maintenance-page .maintenance-modal {
        background: #111827;
        border: 1px solid #334155;
    }
    body.dark-mode .maintenance-page .maintenance-modal-header {
        background: #0f172a;
        border-bottom-color: #334155;
    }
    body.dark-mode .maintenance-page .maintenance-modal-close {
        color: #94a3b8;
    }
    body.dark-mode .maintenance-page .maintenance-modal-close:hover {
        color: #e2e8f0;
    }
    body.dark-mode .maintenance-page .maintenance-form .field-control[disabled] {
        background: #1f2937;
        color: #94a3b8;
    }
    body.dark-mode .maintenance-page .maintenance-btn-cancel {
        background: #1f2937;
        color: #cbd5e1;
    }
    body.dark-mode .maintenance-page .maintenance-btn-save {
        background: #1d4ed8;
    }
    body.dark-mode .maintenance-page .empty-row-cell {
        color: #94a3b8;
    }
    body.dark-mode #successAlert > div {
        background: #14532d !important;
        color: #dcfce7 !important;
        border: 1px solid #166534;
    }

    @media (max-width: 760px) {
        .maintenance-form-grid {
            grid-template-columns: 1fr;
        }
        .report-card { padding: 16px; }
        .table-toolbar { align-items: stretch; }
        .table-search { width: 100%; }
        .maint-table-wrapper { overflow: visible; border: 0; background: transparent; }
        .maint-table,
        .maint-table tbody { display: block; width: 100%; }
        .maint-table thead { display: none; }
        .maint-table tbody { display: grid; gap: 12px; }
        .maint-table tbody tr[data-maintenance-row] {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            overflow: hidden;
            border: 1px solid #dbe4f2;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
        }
        .maint-table tbody tr[data-maintenance-row] > td {
            display: grid;
            grid-template-columns: minmax(100px, .75fr) minmax(0, 1.25fr);
            align-items: center;
            gap: 12px;
            min-height: 46px;
            padding: 10px 12px;
            border: 0;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }
        .maint-table tbody tr[data-maintenance-row] > td::before {
            content: attr(data-label);
            color: #64748b;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .045em;
            text-transform: uppercase;
        }
        .maint-table tbody tr[data-maintenance-row] > td:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 12px;
            background: #f8fbff;
        }
        .maint-table tbody tr[data-maintenance-row] > td:first-child::before { display: none; }
        .maint-table tbody tr[data-maintenance-row] > td:last-child { border-bottom: 0; }
        .maint-table tbody tr[data-maintenance-row] > td:nth-child(n+5) { display: none; }
        .maint-table tbody tr[data-maintenance-row].is-expanded > td:nth-child(n+5) { display: grid; }
        .maint-table tbody tr[data-maintenance-row].is-expanded > td:first-child { background: #eff6ff; }
        .mobile-row-toggle {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            background: #fff;
            color: #1d4ed8;
            padding: 6px 9px;
            font-size: .72rem;
            font-weight: 800;
            cursor: pointer;
        }
        .mobile-row-toggle i { transition: transform .2s ease; }
        .is-expanded .mobile-row-toggle i { transform: rotate(180deg); }
        .maint-table .remarks-cell { max-width: none; margin: 0; }
        .maint-table tbody tr:not([data-maintenance-row]) { display: block; }
        .maint-table .empty-row-cell { display: block; width: 100%; border: 1px solid #dbe4f2; border-radius: 12px; }
        body.dark-mode .maintenance-page .maint-table tbody tr[data-maintenance-row] {
            background: #111827;
            border-color: #334155;
        }
        body.dark-mode .maintenance-page .maint-table tbody tr[data-maintenance-row] > td { border-color: #334155; }
        body.dark-mode .maintenance-page .maint-table tbody tr[data-maintenance-row] > td:first-child { background: #182437; }
        body.dark-mode .maintenance-page .maint-table tbody tr[data-maintenance-row].is-expanded > td:first-child { background: #1e3a5f; }
        body.dark-mode .maintenance-page .maint-table tbody tr[data-maintenance-row] > td::before { color: #94a3b8; }
        body.dark-mode .maintenance-page .mobile-row-toggle { background: #0f172a; color: #93c5fd; border-color: #334155; }
    }

    @media (min-width: 761px) {
        .mobile-row-toggle { display: none; }
    }
</style>

{{-- Alerts --}}
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e; font-size: 1.2rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

<div class="maintenance-page">
<div class="report-card">
    <div class="page-header">
        @php
            $cimmIntegrationEnabled = trim((string) config('services.cimm_maintenance_sync.token', '')) !== '';
        @endphp
        <div class="page-title-group">
            <h2>Facilities Needing <span>Maintenance</span></h2>
            <span
                class="cimm-integration-badge{{ $cimmIntegrationEnabled ? '' : ' is-disabled' }}"
                title="CIMM maintenance synchronization API {{ $cimmIntegrationEnabled ? 'is configured' : 'is not configured' }}"
            >
                <span class="cimm-status-dot" aria-hidden="true"></span>
                CIMM Integration · {{ $cimmIntegrationEnabled ? 'Active' : 'Not Configured' }}
            </span>
        </div>
        <div class="header-actions">
             <button id="addMaintenanceBtn" class="btn btn-primary quick-add-btn">
                <i class="fa fa-plus"></i> Add Manual
            </button>
            <a href="{{ route('maintenance.history') }}" class="history-link-btn">
                <i class="fa fa-history"></i> History
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-box stat-needing">
            <div class="stat-label">🔴 Needing Maint.</div>
            <div class="stat-value">{{ $needingCount ?? 0 }}</div>
        </div>
        <div class="stat-box stat-pending">
            <div class="stat-label">🟡 Pending</div>
            <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
        </div>
        <div class="stat-box stat-ongoing">
            <div class="stat-label">🔧 Ongoing</div>
            <div class="stat-value">{{ $ongoingCount ?? 0 }}</div>
        </div>
        <div class="stat-box stat-completed">
            <div class="stat-label">✅ Completed</div>
            <div class="stat-value">{{ $completedCount ?? 0 }}</div>
        </div>
    </div>

    <form method="GET" action="" class="filter-section">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" disabled selected>Select Facility</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @if(request('facility_id') == $facility->id) selected @endif>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" disabled selected>Select Month</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}" @if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)) selected @endif>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select name="maintenance_type" id="maintenance_type">
                <option value="">All Types</option>
                <option value="Preventive" @if(request('maintenance_type') == 'Preventive') selected @endif>Preventive</option>
                <option value="Corrective" @if(request('maintenance_type') == 'Corrective') selected @endif>Corrective</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" id="status">
                <option value="">All Status</option>
                <option value="Pending" @if(request('status') == 'Pending') selected @endif>Pending</option>
                <option value="Ongoing" @if(request('status') == 'Ongoing') selected @endif>Ongoing</option>
                <option value="Completed" @if(request('status') == 'Completed') selected @endif>Completed</option>
            </select>
        </div>
        <button type="submit" class="btn-filter">Filter</button>
        <a href="{{ route('modules.maintenance.index') }}" class="btn-filter btn-reset">Reset</a>
    </form>

    <div class="table-toolbar">
        <input
            type="text"
            id="maintenanceSearch"
            class="table-search"
            placeholder="Quick search: facility, issue type, status, remarks..."
        >
        @php
            $maintenanceGroups = collect($maintenanceRows ?? [])->groupBy('facility_id');
        @endphp
        <div class="result-count">Visible facilities: <span id="maintenanceVisibleCount">{{ $maintenanceGroups->count() }}</span></div>
    </div>

    <div class="maint-table-wrapper">
        <table class="maint-table">
            <thead>
                <tr>
                    <th>Facility</th>
                    <th>Issue Type</th>
                    <th>Trigger Date</th>
                    <!-- Efficiency column removed -->
                    <th>Status</th>
                    <th>Scheduled</th>
                    <th>Remarks</th>
                    @if($userRole !== 'staff')
                    <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($maintenanceGroups as $facilityId => $facilityIssues)
                @php
                    $row = $facilityIssues->first();
                    $i = $row['id'] ?? $facilityId;
                    $issueCount = $facilityIssues->count();
                    $statusKey = strtolower((string) ($row['maintenance_status'] ?? ''));
                    $statusClass = str_contains($statusKey, 'complete') ? 'completed' : (str_contains($statusKey, 'ongoing') ? 'ongoing' : 'pending');
                    $searchText = strtolower($facilityIssues->map(fn ($issue) => implode(' ', [
                        $issue['facility'] ?? '', $issue['issue_type'] ?? '', $issue['trigger_date'] ?? $issue['trigger_month'] ?? '',
                        $issue['maintenance_status'] ?? '', $issue['scheduled_date'] ?? '', $issue['remarks'] ?? '',
                    ]))->implode(' '));
                @endphp
                <tr data-id="{{ $row['id'] ?? $i }}"
                    data-maintenance-row
                    data-maintenance-item
                    data-facility_name="{{ $row['facility'] ?? '' }}"
                    data-issue_type="{{ $row['issue_type'] ?? '' }}"
                    data-remarks="{{ $row['remarks'] ?? '' }}"
                    data-trigger_month="{{ $row['trigger_month'] ?? '' }}"
                    data-status="{{ $statusClass }}"
                    data-search="{{ $searchText }}"
                    data-maintenance_type="{{ $row['maintenance_type'] ?? '' }}" 
                    data-scheduled_date="{{ $row['scheduled_date'] ?? '' }}" 
                    data-assigned_to="{{ $row['assigned_to'] ?? '' }}" 
                    data-completed_date="{{ $row['completed_date'] ?? '' }}">
                    <td class="facility-cell">
                        <div class="facility-identity">
                            @if(!empty($row['facility_image_url']))
                                <img
                                    src="{{ $row['facility_image_url'] }}"
                                    alt="{{ $row['facility'] }}"
                                    class="facility-thumbnail"
                                    loading="lazy"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                >
                                <span class="facility-thumbnail-fallback" style="display:none" aria-hidden="true">
                                    <i class="fa fa-building"></i>
                                </span>
                            @else
                                <span class="facility-thumbnail-fallback" aria-hidden="true">
                                    <i class="fa fa-building"></i>
                                </span>
                            @endif
                            <div>
                                <span>{{ $row['facility'] }}</span>
                                @if($issueCount > 1)
                                    <button type="button" class="facility-issues-toggle" data-issues-target="facility-issues-{{ $facilityId }}" data-issue-count="{{ $issueCount }}" aria-expanded="false">
                                        <span>View {{ $issueCount }} issues</span><i class="fa fa-chevron-down" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <button type="button" class="mobile-row-toggle" aria-expanded="false">
                            <span>Details</span><i class="fa fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </td>
                    <td data-label="Issue Type">{{ $row['issue_type'] }}</td>
                    <td data-label="Trigger Date">{{ $row['trigger_date'] ?? $row['trigger_month'] }}</td>
                    <!-- Efficiency value removed -->
                    <td data-label="Status"><span class="status-pill {{ $statusClass }}">{{ $row['maintenance_status'] }}</span></td>
                    <td data-label="Scheduled">{{ $row['scheduled_date'] }}</td>
                    <td class="remarks-muted" data-label="Remarks">
                        <div class="remarks-cell" title="{{ $row['remarks'] ?? '-' }}">{{ \Illuminate\Support\Str::limit((string) ($row['remarks'] ?? '-'), 95) }}</div>
                    </td>
                    @if($userRole !== 'staff')
                    <td data-label="Action">{!! str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $row['action']) !!}</td>
                    @endif
                </tr>
                @if($issueCount > 1)
                    <tr id="facility-issues-{{ $facilityId }}" class="facility-issues-row hidden-row">
                        <td colspan="{{ $userRole === 'staff' ? 6 : 7 }}" class="facility-issues-cell">
                            <div class="facility-issues-panel">
                                <div class="facility-issues-heading">All maintenance issues — latest first</div>
                                @foreach($facilityIssues as $issue)
                                    @php
                                        $issueStatusKey = strtolower((string) ($issue['maintenance_status'] ?? ''));
                                        $issueStatusClass = str_contains($issueStatusKey, 'complete') ? 'completed' : (str_contains($issueStatusKey, 'ongoing') ? 'ongoing' : 'pending');
                                    @endphp
                                    <div class="facility-issue-item"
                                        data-maintenance-item
                                        data-id="{{ $issue['id'] }}"
                                        data-facility_name="{{ $issue['facility'] ?? '' }}"
                                        data-issue_type="{{ $issue['issue_type'] ?? '' }}"
                                        data-trigger_month="{{ $issue['trigger_month'] ?? '' }}"
                                        data-maintenance_type="{{ $issue['maintenance_type'] ?? '' }}"
                                        data-scheduled_date="{{ $issue['scheduled_date'] ?? '' }}"
                                        data-assigned_to="{{ $issue['assigned_to'] ?? '' }}"
                                        data-completed_date="{{ $issue['completed_date'] ?? '' }}"
                                        data-remarks="{{ $issue['remarks'] ?? '' }}">
                                        <strong>{{ $issue['issue_type'] ?? '-' }}</strong>
                                        <span class="facility-issue-period">{{ $issue['trigger_date'] ?? $issue['trigger_month'] ?? '-' }}</span>
                                        <span><span class="status-pill {{ $issueStatusClass }}">{{ $issue['maintenance_status'] ?? '-' }}</span></span>
                                        <span class="facility-issue-remarks">{{ \Illuminate\Support\Str::limit((string) ($issue['remarks'] ?? '-'), 120) }}</span>
                                        @if($userRole !== 'staff')
                                            <span>{!! str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $issue['action']) !!}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endif
                @empty
                <tr><td colspan="{{ $userRole === 'staff' ? 6 : 7 }}" class="empty-row-cell">No facilities needing maintenance found.</td></tr>
                @endforelse
                <tr id="maintenanceNoMatchRow" class="hidden-row">
                    <td colspan="{{ $userRole === 'staff' ? 6 : 7 }}" class="empty-row-cell compact">No matching maintenance records found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" class="modal-overlay">
    <div class="maintenance-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="maintenance-modal-header">
            <h3 id="modalTitle" class="maintenance-modal-title">Schedule Maintenance</h3>
            <button type="button" onclick="closeScheduleModal()" class="maintenance-modal-close" aria-label="Close modal">&times;</button>
        </div>
        <div class="maintenance-modal-body">
            <form id="scheduleForm" class="maintenance-form">
                <input type="hidden" name="maintenance_id" id="modalMaintenanceId">

                <div class="field-group">
                    <label for="modalFacility" class="field-label">Facility</label>
                    <select id="modalFacility" class="field-control">
                        <option value="" disabled selected>Select Facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalTriggerMonth" class="field-label">Trigger Month and Year</label>
                        <div class="trigger-grid">
                            <select id="modalTriggerMonth" class="field-control">
                                @foreach(range(1,12) as $m)
                                    <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endforeach
                            </select>
                            <select id="modalTriggerYear" class="field-control">
                                @php $currentYear = date('Y'); @endphp
                                @for($y = $currentYear-2; $y <= $currentYear+2; $y++)
                                    <option value="{{ $y }}" @if($y==$currentYear) selected @endif>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="modalIssueType" class="field-label">Issue Type</label>
                        <select id="modalIssueType" class="field-control">
                            <option value="" disabled selected>Select Issue</option>
                            <option value="Electrical - Power Outage">Electrical - Power Outage</option>
                            <option value="Electrical - Circuit Overload">Electrical - Circuit Overload</option>
                            <option value="Lighting - Bulb Replacement">Lighting - Bulb Replacement</option>
                            <option value="Lighting - Fixture Repair">Lighting - Fixture Repair</option>
                            <option value="Aircon - Not Cooling">Aircon - Not Cooling</option>
                            <option value="Aircon - Cleaning Needed">Aircon - Cleaning Needed</option>
                            <option value="Plumbing - Leak">Plumbing - Leak</option>
                            <option value="Plumbing - Clogged Drain">Plumbing - Clogged Drain</option>
                            <option value="Roof - Leak">Roof - Leak</option>
                            <option value="Roof - Gutter Cleaning">Roof - Gutter Cleaning</option>
                            <option value="Pest Control">Pest Control</option>
                            <option value="General - Preventive Check">General - Preventive Check</option>
                            <option value="General - Other">General - Other</option>
                        </select>
                    </div>
                </div>

                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalMaintType" class="field-label">Maintenance Type</label>
                        <select id="modalMaintType" class="field-control">
                            <option value="Preventive">Preventive</option>
                            <option value="Corrective">Corrective</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="modalScheduleDate" class="field-label">Scheduled Date</label>
                        <input type="date" id="modalScheduleDate" class="field-control">
                    </div>
                </div>

                <div class="field-group">
                    <label for="modalAssignedTo" class="field-label">Assigned To</label>
                    <input type="text" id="modalAssignedTo" class="field-control" placeholder="e.g. Engr. Cruz">
                </div>

                <div class="field-group">
                    <label for="modalRemarks" class="field-label">Remarks</label>
                    <textarea id="modalRemarks" class="field-control" placeholder="Add notes or maintenance details..."></textarea>
                </div>

                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalStatus" class="field-label">Status</label>
                        <select id="modalStatus" class="field-control">
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            @if($userRole !== 'energy_officer')
                            <option value="Completed">Completed</option>
                            @endif
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="modalCompletedDate" class="field-label">Completed Date</label>
                        <input type="date" id="modalCompletedDate" class="field-control" disabled>
                    </div>
                </div>

                <div class="maintenance-modal-actions">
                    <button type="button" onclick="closeScheduleModal()" class="maintenance-btn-cancel">Cancel</button>
                    <button type="submit" class="maintenance-btn-save">Save Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('successAlert');
    if (successAlert) setTimeout(() => { successAlert.style.opacity = '0'; setTimeout(() => successAlert.remove(), 500); }, 3000);

    const scheduleModal = document.getElementById('scheduleModal');
    const scheduleForm = document.getElementById('scheduleForm');
    const modalTitle = document.getElementById('modalTitle');
    const modalMaintenanceId = document.getElementById('modalMaintenanceId');
    const modalFacility = document.getElementById('modalFacility');
    const modalTriggerMonth = document.getElementById('modalTriggerMonth');
    const modalTriggerYear = document.getElementById('modalTriggerYear');
    const modalIssueType = document.getElementById('modalIssueType');
    const modalMaintType = document.getElementById('modalMaintType');
    const modalScheduleDate = document.getElementById('modalScheduleDate');
    const modalAssignedTo = document.getElementById('modalAssignedTo');
    const modalRemarks = document.getElementById('modalRemarks');
    const modalStatus = document.getElementById('modalStatus');
    const modalCompletedDate = document.getElementById('modalCompletedDate');
    const maintenanceSearch = document.getElementById('maintenanceSearch');
    const visibleCountEl = document.getElementById('maintenanceVisibleCount');
    const noMatchRow = document.getElementById('maintenanceNoMatchRow');
    const tableRows = Array.from(document.querySelectorAll('.maint-table tbody tr[data-search]'));

    const updateCompletedDateState = () => {
        if (!modalStatus || !modalCompletedDate) return;
        const completed = modalStatus.value === 'Completed';
        modalCompletedDate.disabled = !completed;
        if (!completed) {
            modalCompletedDate.value = '';
            return;
        }
        if (!modalCompletedDate.value) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            modalCompletedDate.value = `${yyyy}-${mm}-${dd}`;
        }
    };

    const updateIssueTypeState = () => {
        if (!modalStatus || !modalIssueType) return;
        const completed = modalStatus.value === 'Completed';
        modalIssueType.disabled = completed;
    };

    const parseTriggerMonth = (triggerText) => {
        const text = String(triggerText || '').trim();
        const match = text.match(/^([A-Za-z]+)\s+(\d{4})$/);
        if (!match) return { month: '', year: '' };
        const monthMap = {
            january: '01', february: '02', march: '03', april: '04', may: '05', june: '06',
            july: '07', august: '08', september: '09', october: '10', november: '11', december: '12',
            jan: '01', feb: '02', mar: '03', apr: '04', jun: '06', jul: '07', aug: '08', sep: '09', oct: '10', nov: '11', dec: '12'
        };
        const month = monthMap[match[1].toLowerCase()] || '';
        return { month, year: match[2] || '' };
    };

    const openScheduleModal = () => {
        if (scheduleModal) scheduleModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    const applyLocalSearch = () => {
        if (!tableRows.length) return;
        const query = String(maintenanceSearch?.value || '').trim().toLowerCase();
        let visible = 0;
        tableRows.forEach((row) => {
            const haystack = String(row.getAttribute('data-search') || '');
            const matched = query === '' || haystack.includes(query);
            row.classList.toggle('hidden-row', !matched);
            if (!matched) {
                const toggle = row.querySelector('.facility-issues-toggle');
                const target = toggle ? document.getElementById(toggle.getAttribute('data-issues-target')) : null;
                if (target) target.classList.add('hidden-row');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            }
            if (matched) visible++;
        });
        if (visibleCountEl) visibleCountEl.textContent = String(visible);
        if (noMatchRow) noMatchRow.classList.toggle('hidden-row', visible !== 0);
    };

    document.querySelectorAll('.mobile-row-toggle').forEach((button) => {
        button.addEventListener('click', function () {
            const row = this.closest('[data-maintenance-row]');
            if (!row) return;
            const expanded = row.classList.toggle('is-expanded');
            this.setAttribute('aria-expanded', String(expanded));
            const label = this.querySelector('span');
            if (label) label.textContent = expanded ? 'Less' : 'Details';
        });
    });

    document.querySelectorAll('.facility-issues-toggle').forEach((button) => {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            const target = document.getElementById(this.getAttribute('data-issues-target'));
            if (!target) return;
            const willExpand = target.classList.contains('hidden-row');
            target.classList.toggle('hidden-row', !willExpand);
            this.setAttribute('aria-expanded', String(willExpand));
            const label = this.querySelector('span');
            const count = this.getAttribute('data-issue-count') || '';
            if (label) label.textContent = willExpand ? 'Hide issues' : `View ${count} issues`;
        });
    });

    document.querySelectorAll('.schedule-btn').forEach((btn) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('[data-maintenance-item]');
            if (!row) return;

            if (modalTitle) modalTitle.innerText = 'Update Maintenance';
            if (modalMaintenanceId) modalMaintenanceId.value = row.getAttribute('data-id') || '';

            const facilityName = row.getAttribute('data-facility_name') || '';
            if (modalFacility && facilityName) {
                for (let i = 0; i < modalFacility.options.length; i++) {
                    if (modalFacility.options[i].text === facilityName) {
                        modalFacility.selectedIndex = i;
                        break;
                    }
                }
                modalFacility.disabled = true;
            }

            if (modalIssueType) {
                modalIssueType.value = row.getAttribute('data-issue_type') || '';
            }

            const triggerMonthText = row.getAttribute('data-trigger_month') || '';
            const parsed = parseTriggerMonth(triggerMonthText);
            if (modalTriggerMonth) {
                if (parsed.month) modalTriggerMonth.value = parsed.month;
                modalTriggerMonth.disabled = true;
            }
            if (modalTriggerYear) {
                if (parsed.year) modalTriggerYear.value = parsed.year;
                modalTriggerYear.disabled = true;
            }

            if (modalMaintType) modalMaintType.value = row.getAttribute('data-maintenance_type') || 'Preventive';
            if (modalScheduleDate) modalScheduleDate.value = row.getAttribute('data-scheduled_date') || '';
            if (modalAssignedTo) modalAssignedTo.value = row.getAttribute('data-assigned_to') || '';
            if (modalRemarks) {
                const remarksText = row.getAttribute('data-remarks') || '';
                modalRemarks.value = remarksText === '-' ? '' : remarksText;
            }
            if (modalStatus) {
                const statusText = row.querySelector('.status-pill')?.innerText?.trim() || 'Pending';
                const canUseStatus = Array.from(modalStatus.options || []).some((opt) => opt.value === statusText);
                modalStatus.value = canUseStatus ? statusText : 'Ongoing';
            }
            if (modalCompletedDate) modalCompletedDate.value = row.getAttribute('data-completed_date') || '';
            updateCompletedDateState();
            updateIssueTypeState();
            openScheduleModal();
        });
    });

    const addMaintenanceBtn = document.getElementById('addMaintenanceBtn');
    if (addMaintenanceBtn) {
        addMaintenanceBtn.addEventListener('click', function() {
            if (scheduleForm) scheduleForm.reset();
            if (modalTitle) modalTitle.innerText = 'Schedule Maintenance';
            if (modalMaintenanceId) modalMaintenanceId.value = '';
            if (modalFacility) modalFacility.disabled = false;
            if (modalIssueType) modalIssueType.disabled = false;
            if (modalTriggerMonth) modalTriggerMonth.disabled = false;
            if (modalTriggerYear) modalTriggerYear.disabled = false;
            if (modalStatus) modalStatus.value = 'Pending';
            updateCompletedDateState();
            updateIssueTypeState();
            openScheduleModal();
        });
    }

    if (modalStatus) {
        modalStatus.addEventListener('change', updateCompletedDateState);
        modalStatus.addEventListener('change', updateIssueTypeState);
    }

    if (scheduleForm) {
        scheduleForm.onsubmit = async function(e) {
            e.preventDefault();
            const status = modalStatus?.value;
            const completedDate = modalCompletedDate?.value;
            if (status === 'Completed' && !completedDate) {
                window.alert('Completed Date is required!');
                return false;
            }

            const monthNum = modalTriggerMonth?.value;
            const yearVal = modalTriggerYear?.value;
            const monthName = monthNum
                ? new Date(2000, parseInt(monthNum, 10) - 1, 1).toLocaleString('default', { month: 'long' })
                : '';
            const triggerMonth = monthName && yearVal ? `${monthName} ${yearVal}` : '';
            const payload = {
                maintenance_id: modalMaintenanceId?.value || '',
                facility_id: modalFacility?.value,
                trigger_month: triggerMonth,
                issue_type: modalIssueType?.value,
                maintenance_type: modalMaintType?.value,
                scheduled_date: modalScheduleDate?.value,
                assigned_to: modalAssignedTo?.value,
                remarks: modalRemarks?.value,
                maintenance_status: status,
                completed_date: completedDate,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            };

            try {
                const response = await fetch("{{ route('modules.maintenance.schedule') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': payload._token },
                    body: JSON.stringify(payload),
                });
                const body = await response.json().catch(() => ({}));
                if (!response.ok || !body.success) {
                    const errors = body?.errors ? Object.values(body.errors).flat().join('\n') : '';
                    window.alert(body?.message || errors || 'Failed to save maintenance.');
                    return;
                }
                location.reload();
            } catch (err) {
                window.alert('Network error while saving maintenance.');
            }
        };
    }

    if (maintenanceSearch) maintenanceSearch.addEventListener('input', applyLocalSearch);
    applyLocalSearch();

    if (scheduleModal) {
        scheduleModal.addEventListener('click', function(e) {
            if (e.target === scheduleModal) closeScheduleModal();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeScheduleModal();
    });
});

function closeScheduleModal() {
    const scheduleModal = document.getElementById('scheduleModal');
    if (scheduleModal) scheduleModal.style.display = 'none';
    document.body.style.overflow = '';
}
</script>
@endsection
