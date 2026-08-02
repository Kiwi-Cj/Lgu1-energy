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

    /* Maintenance work queue */
    .maintenance-table-card {
        overflow: hidden;
        border: 1px solid #dbe5f2;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .055);
    }
    .maintenance-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 20px;
        border-bottom: 1px solid #e7edf5;
        background: linear-gradient(135deg, #fbfdff 0%, #f5f8ff 100%);
    }
    .maintenance-table-title {
        display: flex;
        align-items: center;
        gap: 11px;
        color: #0f172a;
        font-size: 1.03rem;
        font-weight: 850;
    }
    .maintenance-table-title-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        border: 1px solid #bfdbfe;
        border-radius: 11px;
        background: #eff6ff;
        color: #2563eb;
    }
    .maintenance-table-subtitle {
        margin-top: 3px;
        color: #64748b;
        font-size: .8rem;
        font-weight: 600;
    }
    .table-count-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid #dbe5f2;
        border-radius: 999px;
        padding: 7px 11px;
        background: #fff;
        color: #475569;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .maint-table-wrapper {
        max-height: 610px;
        overflow: auto;
        scrollbar-gutter: stable;
        overscroll-behavior: contain;
    }
    .maint-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        text-align: left;
    }
    .maint-table thead { background: #f8fafc; }
    .maint-table th {
        position: sticky;
        top: 0;
        z-index: 5;
        padding: 12px 16px;
        border-bottom: 1px solid #dbe5f2;
        background: #f8fafc;
        color: #64748b;
        font-size: .72rem;
        font-weight: 850;
        letter-spacing: .055em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .maint-table td {
        padding: 15px 16px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        font-size: .9rem;
        vertical-align: middle;
    }
    .maint-table tbody tr[data-maintenance-row] {
        position: relative;
        transition: background-color .18s ease, box-shadow .18s ease;
    }
    .maint-table tbody tr[data-maintenance-row]:hover {
        background: #f8fbff;
        box-shadow: inset 3px 0 0 #93c5fd;
    }
    .maint-table th:nth-child(1) { width: 22%; }
    .maint-table th:nth-child(2) { width: 21%; }
    .maint-table th:nth-child(3) { width: 11%; }
    .maint-table th:nth-child(4) { width: 10%; }
    .maint-table th:nth-child(5) { width: 17%; }
    .maint-table th:nth-child(6) { width: 22%; }
    .maint-table th:last-child,
    .maint-table td:last-child { text-align: center; }
    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 20px;
        border-bottom: 1px solid #edf2f7;
        background: #fff;
        flex-wrap: wrap;
    }
    .table-search-wrap {
        position: relative;
        width: min(460px, 100%);
    }
    .table-search-wrap > i {
        position: absolute;
        top: 50%;
        left: 13px;
        color: #94a3b8;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .table-search {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 13px 10px 38px;
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
        gap: 7px;
        padding: 5px 10px;
        font-size: 0.76rem;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .status-pill.pending { background: #fffbeb; color: #a16207; border-color: #fde68a; }
    .status-pill.ongoing { background: #ecfeff; color: #0e7490; border-color: #bae6fd; }
    .status-pill.completed { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .status-pill::before {
        content: '';
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 3px rgba(100, 116, 139, .12);
    }
    .priority-pill { display:inline-flex; margin-top:5px; padding:3px 7px; border-radius:999px; font-size:.67rem; font-weight:800; text-transform:uppercase; letter-spacing:.03em; }
    .priority-pill.normal { background:#f1f5f9; color:#475569; }
    .priority-pill.high { background:#fff7ed; color:#c2410c; }
    .priority-pill.critical { background:#fee2e2; color:#b91c1c; }
    .overdue-label { display:block; margin-top:4px; color:#dc2626; font-size:.7rem; font-weight:800; white-space:nowrap; }
    .proof-link { display:inline-flex; align-items:center; gap:5px; margin-top:5px; color:#2563eb; font-size:.72rem; font-weight:800; text-decoration:none; }
    .remarks-cell {
        color: #64748b;
        max-width: 330px;
        margin: 0 auto;
        text-align: left;
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        line-height: 1.45;
    }
    .facility-cell {
        font-weight: 700;
    }
    .facility-name {
        display: block;
        color: #0f172a;
        font-size: .94rem;
        font-weight: 850;
        line-height: 1.3;
    }
    .facility-record-id {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: .69rem;
        font-weight: 750;
        letter-spacing: .03em;
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
    .issue-cell {
        display: flex;
        align-items: flex-start;
        gap: 9px;
    }
    .issue-cell-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        border-radius: 9px;
        background: #fff7ed;
        color: #ea580c;
    }
    .issue-name { color:#1e293b; font-weight:800; line-height:1.35; }
    .date-stack { display:grid; gap:4px; color:#334155; font-weight:700; }
    .date-stack > span { display:inline-flex; align-items:center; gap:6px; }
    .date-stack i { width:13px; color:#94a3b8; }
    .assignee-name { color:#64748b; font-size:.75rem; font-weight:700; }
    .schedule-btn {
        min-width: 104px;
        min-height: 36px;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        border: 1px solid #bfdbfe !important;
        border-radius: 9px !important;
        padding: 8px 12px !important;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        font-size: .76rem !important;
        font-weight: 850 !important;
        box-shadow: none !important;
    }
    .schedule-btn:hover { border-color:#93c5fd !important; background:#dbeafe !important; }
    tr[data-status="ongoing"] .schedule-btn { border-color:#a7f3d0 !important; background:#ecfdf5 !important; color:#047857 !important; }
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
        padding: 16px 20px 20px;
        display: grid;
        gap: 10px;
        border-left: 3px solid #bfdbfe;
    }
    .facility-issues-heading-row { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .facility-issues-heading {
        color: #475569;
        font-size: .78rem;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .facility-issues-count {
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:5px 9px;
        border:1px solid #dbe5f2;
        border-radius:999px;
        background:#fff;
        color:#64748b;
        font-size:.7rem;
        font-weight:800;
    }
    .facility-issue-item {
        display: grid;
        grid-template-columns: minmax(230px, 1.25fr) minmax(105px, .5fr) minmax(110px, .55fr) minmax(240px, 1.4fr) auto;
        align-items: center;
        gap: 14px;
        padding: 13px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        text-align: left;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .facility-issue-item:hover { border-color:#bfdbfe; box-shadow:0 7px 18px rgba(15,23,42,.055); transform:translateY(-1px); }
    .facility-issue-main { display:flex; align-items:flex-start; gap:10px; min-width:0; }
    .facility-issue-icon {
        width:32px;
        height:32px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex:0 0 32px;
        border-radius:9px;
        background:#fff7ed;
        color:#ea580c;
    }
    .facility-issue-name { display:block; color:#1e293b; font-size:.86rem; line-height:1.35; }
    .facility-issue-period,
    .facility-issue-remarks { color: #64748b; font-size: .86rem; }
    .facility-issue-period { display:inline-flex; align-items:center; gap:6px; font-weight:700; }
    .facility-issue-period i { color:#94a3b8; }
    .facility-issue-remarks {
        display:-webkit-box;
        overflow:hidden;
        overflow-wrap:anywhere;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:2;
        line-clamp:2;
        line-height:1.45;
    }
    @media (max-width: 900px) {
        .facility-issues-heading-row { align-items:flex-start; flex-wrap:wrap; }
        .facility-issues-panel { padding:14px; }
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
        width: min(760px, calc(100vw - 24px));
        background: #fff;
        border: 1px solid #dbe5f2;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15,23,42,.30);
        max-height: calc(100vh - 24px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .maintenance-modal-header {
        flex: 0 0 auto;
        padding: 22px 68px 20px 24px;
        background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
        border-bottom: 1px solid #dbe5f2;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
    }
    .maintenance-modal-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: linear-gradient(135deg,#2563eb,#6366f1);
        color: #fff;
        box-shadow: 0 9px 20px rgba(79,70,229,.20);
    }
    .maintenance-modal-heading {
        min-width: 0;
    }
    .maintenance-modal-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .84rem;
        font-weight: 600;
        line-height: 1.4;
    }
    .maintenance-modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -.02em;
    }
    .maintenance-modal-close {
        position: absolute;
        z-index: 5;
        top: 18px;
        right: 18px;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.9);
        border: 1px solid #dbe5f2;
        border-radius: 11px;
        font-size: 1rem;
        color: #64748b;
        cursor: pointer;
    }
    .maintenance-modal-close:hover { color:#e11d48; background:#fff1f2; border-color:#fecdd3; }
    .maintenance-modal-body {
        flex: 1 1 auto;
        min-height: 0;
        padding: 20px 26px 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .maintenance-form { display: flex; flex-direction: column; gap: 14px; }
    .maintenance-form-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 5px 0 -2px;
        color: #475569;
        font-size: .73rem;
        font-weight: 850;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    .maintenance-form-section-title i { color:#2563eb; }
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
        position: sticky;
        z-index: 4;
        bottom: 0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin: 20px -26px 0;
        padding: 14px 26px;
        border-top: 1px solid #e2e8f0;
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(8px);
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
        min-width: 170px;
        box-shadow: 0 7px 16px rgba(37,99,235,.20);
    }

    @media (max-width: 600px) {
        .modal-overlay { padding: 6px; }
        .maintenance-modal { width:calc(100vw - 12px); max-height:calc(100vh - 12px); border-radius:17px; }
        .maintenance-modal-header { padding:17px 55px 16px 16px; }
        .maintenance-modal-icon { width:42px; height:42px; flex-basis:42px; border-radius:12px; }
        .maintenance-modal-body { padding:16px 16px 0; }
        .maintenance-form-grid { grid-template-columns:1fr; }
        .maintenance-modal-actions { margin:18px -16px 0; padding:12px 16px; }
        .maintenance-btn-cancel, .maintenance-btn-save { flex:1; }
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
    body.dark-mode .maintenance-page .maint-table,
    body.dark-mode .maintenance-page .maintenance-table-card {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .maintenance-table-head,
    body.dark-mode .maintenance-page .table-toolbar {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .maintenance-table-title { color:#e2e8f0; }
    body.dark-mode .maintenance-page .maintenance-table-title-icon { background:#172554; border-color:#1e40af; color:#93c5fd; }
    body.dark-mode .maintenance-page .maintenance-table-subtitle { color:#94a3b8; }
    body.dark-mode .maintenance-page .table-count-chip { background:#0f172a; border-color:#334155; color:#cbd5e1; }
    body.dark-mode .maintenance-page .facility-name,
    body.dark-mode .maintenance-page .issue-name { color:#e2e8f0; }
    body.dark-mode .maintenance-page .facility-record-id,
    body.dark-mode .maintenance-page .assignee-name { color:#94a3b8; }
    body.dark-mode .maintenance-page .issue-cell-icon { background:#431407; color:#fdba74; }
    body.dark-mode .maintenance-page .facility-issues-count { background:#0f172a; border-color:#334155; color:#94a3b8; }
    body.dark-mode .maintenance-page .facility-issue-name { color:#e2e8f0; }
    body.dark-mode .maintenance-page .facility-issue-icon { background:#431407; color:#fdba74; }
    body.dark-mode .maintenance-page .facility-issues-cell,
    body.dark-mode .maintenance-page .facility-issues-panel { background:#111827; border-color:#334155; }
    body.dark-mode .maintenance-page .facility-issue-item { background:#0f172a; border-color:#334155; }
    body.dark-mode .maintenance-page .facility-issue-item:hover { border-color:#475569; box-shadow:none; }
    body.dark-mode .maintenance-page .maint-table thead {
        background: #111827;
    }
    body.dark-mode .maintenance-page .maint-table th,
    body.dark-mode .maintenance-page .maint-table td {
        border-color: #334155;
    }
    body.dark-mode .maintenance-page .maint-table th {
        background: #111827;
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
        background: linear-gradient(135deg,#111827,#172033);
        border-bottom-color: #334155;
    }
    body.dark-mode .maintenance-page .maintenance-modal-subtitle { color:#94a3b8; }
    body.dark-mode .maintenance-page .maintenance-form-section-title { color:#cbd5e1; }
    body.dark-mode .maintenance-page .maintenance-modal-close {
        color: #94a3b8;
        background: #111827;
        border-color: #334155;
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
    body.dark-mode .maintenance-page .maintenance-modal-actions {
        background: rgba(15,23,42,.97);
        border-color: #334155;
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
        .maintenance-table-head { align-items:flex-start; padding:15px; }
        .maintenance-table-subtitle { max-width:280px; }
        .table-count-chip { padding:6px 9px; }
        .table-toolbar { padding:12px 15px; }
        .result-count { display:none; }
        .maint-table-wrapper { max-height:none; overflow: visible; border: 0; background: transparent; padding:12px; }
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
        .maint-table tbody tr.facility-issues-row.hidden-row,
        .maint-table tbody tr#maintenanceNoMatchRow.hidden-row { display: none; }
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
                CIMM Integration &middot; {{ $cimmIntegrationEnabled ? 'Active' : 'Not Configured' }}
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
            <div class="stat-label"><i class="fa fa-triangle-exclamation"></i> Needing Maint.</div>
            <div class="stat-value">{{ $needingCount ?? 0 }}</div>
        </div>
        <div class="stat-box stat-pending">
            <div class="stat-label"><i class="fa fa-clock"></i> Pending</div>
            <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
        </div>
        <div class="stat-box stat-ongoing">
            <div class="stat-label"><i class="fa fa-screwdriver-wrench"></i> Ongoing</div>
            <div class="stat-value">{{ $ongoingCount ?? 0 }}</div>
        </div>
        <div class="stat-box stat-completed">
            <div class="stat-label"><i class="fa fa-circle-check"></i> Completed</div>
            <div class="stat-value">{{ $completedCount ?? 0 }}</div>
        </div>
    </div>

    <form method="GET" action="" class="filter-section">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" @selected(!request()->filled('facility_id'))>All Facilities</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @if(request('facility_id') == $facility->id) selected @endif>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" @selected(!request()->filled('month'))>All Months</option>
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

    @php
        $maintenanceGroups = collect($maintenanceRows ?? [])->groupBy('facility_id');
    @endphp
    <section class="maintenance-table-card" aria-labelledby="maintenanceQueueTitle">
        <div class="maintenance-table-head">
            <div>
                <div class="maintenance-table-title" id="maintenanceQueueTitle">
                    <span class="maintenance-table-title-icon"><i class="fa-solid fa-list-check"></i></span>
                    <span>Maintenance Work Queue</span>
                </div>
                <div class="maintenance-table-subtitle">Review facility issues, schedules, assignments, and current work status.</div>
            </div>
            <span class="table-count-chip"><i class="fa-solid fa-building-circle-exclamation"></i> <span id="maintenanceVisibleCount">{{ $maintenanceGroups->count() }}</span> facilities</span>
        </div>
        <div class="table-toolbar">
            <div class="table-search-wrap">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    type="search"
                    id="maintenanceSearch"
                    class="table-search"
                    placeholder="Search facility, issue, status, assignee, or remarks..."
                    autocomplete="off"
                    aria-label="Search maintenance work queue"
                >
            </div>
            <div class="result-count"><i class="fa-solid fa-circle-info"></i> Use "View issues" to inspect multiple records under one facility.</div>
        </div>

        <div class="maint-table-wrapper">
        <table class="maint-table">
            <thead>
                <tr>
                    <th scope="col">Facility</th>
                    <th scope="col">Issue Type</th>
                    <th scope="col">Reported</th>
                    <!-- Efficiency column removed -->
                    <th scope="col">Status</th>
                    <th scope="col">Work Plan</th>
                    <th scope="col">Remarks</th>
                    @if($userRole !== 'staff')
                    <th scope="col">Action</th>
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
                        $issue['maintenance_status'] ?? '', $issue['scheduled_date'] ?? '', $issue['assigned_to'] ?? '', $issue['remarks'] ?? '',
                    ]))->implode(' '));
                @endphp
                <tr class="maintenance-row" data-id="{{ $row['id'] ?? $i }}"
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
                    data-completed_date="{{ $row['completed_date'] ?? '' }}"
                    data-photo_requirement="{{ $row['photo_requirement'] ?? 'Optional' }}"
                    data-proof_photo_url="{{ $row['proof_photo_url'] ?? '' }}">
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
                                <span class="facility-name">{{ $row['facility'] }}</span>
                                <span class="facility-record-id">FACILITY #{{ $facilityId }}</span>
                                @if($issueCount > 1)
                                    <button type="button" class="facility-issues-toggle" data-issues-target="facility-issues-{{ $facilityId }}" data-issue-count="{{ $issueCount - 1 }}" aria-expanded="false">
                                        <span>View {{ $issueCount - 1 }} more</span><i class="fa fa-chevron-down" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <button type="button" class="mobile-row-toggle" aria-expanded="false">
                            <span>Details</span><i class="fa fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </td>
                    <td data-label="Issue Type">
                        <div class="issue-cell">
                            <span class="issue-cell-icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                            <div>
                                <div class="issue-name">{{ $row['issue_type'] }}</div>
                                <span class="priority-pill {{ strtolower($row['priority'] ?? 'Normal') }}">{{ $row['priority'] ?? 'Normal' }} priority</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Reported">
                        <div class="date-stack"><span><i class="fa-regular fa-calendar"></i>{{ $row['trigger_date'] ?? $row['trigger_month'] }}</span></div>
                    </td>
                    <!-- Efficiency value removed -->
                    <td data-label="Status"><span class="status-pill {{ $statusClass }}">{{ $row['maintenance_status'] }}</span></td>
                    <td data-label="Work Plan">
                        <div class="date-stack">
                            <span><i class="fa-regular fa-calendar-check"></i>{{ $row['scheduled_date'] ?: 'Not scheduled' }}</span>
                            <span class="assignee-name"><i class="fa-regular fa-user"></i>{{ filled($row['assigned_to'] ?? null) ? $row['assigned_to'] : 'Unassigned' }}</span>
                        </div>
                        @if($row['is_overdue'] ?? false)
                            <span class="overdue-label"><i class="fa fa-clock"></i> {{ $row['overdue_days'] }} day(s) overdue</span>
                        @endif
                    </td>
                    <td class="remarks-muted" data-label="Remarks">
                        <div class="remarks-cell" title="{{ $row['remarks'] ?? '-' }}">{{ \Illuminate\Support\Str::limit((string) ($row['remarks'] ?? '-'), 95) }}</div>
                        @if(!empty($row['proof_photo_url']))
                            <a class="proof-link" href="{{ $row['proof_photo_url'] }}" target="_blank" rel="noopener"><i class="fa fa-image"></i> View proof</a>
                        @endif
                    </td>
                    @if($userRole !== 'staff')
                    <td data-label="Action">{!! str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $row['action']) !!}</td>
                    @endif
                </tr>
                @if($issueCount > 1)
                    <tr id="facility-issues-{{ $facilityId }}" class="facility-issues-row hidden-row">
                        <td colspan="{{ $userRole === 'staff' ? 6 : 7 }}" class="facility-issues-cell">
                            <div class="facility-issues-panel">
                                <div class="facility-issues-heading-row">
                                    <div class="facility-issues-heading">Additional maintenance issues &mdash; latest first</div>
                                    <span class="facility-issues-count"><i class="fa-solid fa-layer-group"></i> {{ $issueCount - 1 }} more</span>
                                </div>
                                @foreach($facilityIssues->skip(1) as $issue)
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
                                        data-photo_requirement="{{ $issue['photo_requirement'] ?? 'Optional' }}"
                                        data-proof_photo_url="{{ $issue['proof_photo_url'] ?? '' }}"
                                        data-remarks="{{ $issue['remarks'] ?? '' }}">
                                        <div class="facility-issue-main">
                                            <span class="facility-issue-icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                                            <div>
                                                <strong class="facility-issue-name">{{ $issue['issue_type'] ?? '-' }}</strong>
                                                <span class="priority-pill {{ strtolower($issue['priority'] ?? 'Normal') }}">{{ $issue['priority'] ?? 'Normal' }} priority</span>
                                            </div>
                                        </div>
                                        <span class="facility-issue-period"><i class="fa-regular fa-calendar"></i>{{ $issue['trigger_date'] ?? $issue['trigger_month'] ?? '-' }}</span>
                                        <span><span class="status-pill {{ $issueStatusClass }}">{{ $issue['maintenance_status'] ?? '-' }}</span></span>
                                        <span class="facility-issue-remarks" title="{{ $issue['remarks'] ?? '-' }}">{{ \Illuminate\Support\Str::limit((string) ($issue['remarks'] ?? '-'), 145) }}</span>
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
</section>
</div>

<div id="scheduleModal" class="modal-overlay">
    <div class="maintenance-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="maintenance-modal-header">
            <div class="maintenance-modal-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div class="maintenance-modal-heading">
                <h3 id="modalTitle" class="maintenance-modal-title">Schedule Maintenance</h3>
                <p class="maintenance-modal-subtitle">Set the work schedule, assignee, requirements, and completion status.</p>
            </div>
            <button type="button" onclick="closeScheduleModal()" class="maintenance-modal-close" aria-label="Close maintenance form"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="maintenance-modal-body">
            <form id="scheduleForm" class="maintenance-form">
                <input type="hidden" name="maintenance_id" id="modalMaintenanceId">

                <div class="maintenance-form-section-title"><i class="fa-solid fa-clipboard-list"></i> Work Details</div>
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

                <div class="maintenance-form-section-title"><i class="fa-solid fa-user-gear"></i> Assignment</div>
                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalAssigneeRole" class="field-label">Assignee Category</label>
                        <select id="modalAssigneeRole" class="field-control">
                            <option value="">Select category</option>
                            <option value="engineer">Engineer</option>
                            <option value="energy_officer">Energy Officer</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="modalAssignedTo" class="field-label">Assigned To</label>
                        <select id="modalAssignedTo" class="field-control" disabled>
                            <option value="">Select a category first</option>
                            @foreach($assignableUsers ?? collect() as $assignableUser)
                                <option value="{{ $assignableUser['name'] }}" data-role="{{ $assignableUser['role'] }}">
                                    {{ $assignableUser['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label for="modalRemarks" class="field-label">Remarks</label>
                    <textarea id="modalRemarks" class="field-control" placeholder="Add notes or maintenance details..."></textarea>
                </div>

                <div class="maintenance-form-section-title"><i class="fa-solid fa-circle-check"></i> Completion &amp; Evidence</div>
                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalPhotoRequirement" class="field-label">Completion Photo</label>
                        <select id="modalPhotoRequirement" class="field-control">
                            <option value="Optional">Optional</option>
                            <option value="Required">Required</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="modalProofPhoto" class="field-label">Proof Photo</label>
                        <input type="file" id="modalProofPhoto" class="field-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <a id="modalExistingProof" class="proof-link" href="#" target="_blank" rel="noopener" style="display:none;"><i class="fa fa-image"></i> View existing proof</a>
                    </div>
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
                    <button type="submit" class="maintenance-btn-save"><i class="fa-solid fa-floppy-disk"></i> Save Maintenance</button>
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
    const modalAssigneeRole = document.getElementById('modalAssigneeRole');
    const modalAssignedTo = document.getElementById('modalAssignedTo');
    const modalRemarks = document.getElementById('modalRemarks');
    const modalPhotoRequirement = document.getElementById('modalPhotoRequirement');
    const modalProofPhoto = document.getElementById('modalProofPhoto');
    const modalExistingProof = document.getElementById('modalExistingProof');
    const modalStatus = document.getElementById('modalStatus');
    const modalCompletedDate = document.getElementById('modalCompletedDate');
    const maintenanceSearch = document.getElementById('maintenanceSearch');
    const visibleCountEl = document.getElementById('maintenanceVisibleCount');
    const noMatchRow = document.getElementById('maintenanceNoMatchRow');
    const tableRows = Array.from(document.querySelectorAll('.maint-table tbody tr[data-search]'));
    const assigneeOptions = modalAssignedTo
        ? Array.from(modalAssignedTo.querySelectorAll('option[data-role]')).map((option) => ({
            value: option.value,
            label: option.textContent.trim(),
            role: option.dataset.role || ''
        }))
        : [];

    const filterAssignees = (role, selectedName = '') => {
        if (!modalAssignedTo) return;

        modalAssignedTo.replaceChildren();
        const matchingUsers = assigneeOptions.filter((option) => option.role === role);
        const placeholder = !role
            ? 'Select a category first'
            : (matchingUsers.length ? 'Select a name' : 'No active users in this category');
        modalAssignedTo.add(new Option(placeholder, ''));

        matchingUsers.forEach((option) => {
            modalAssignedTo.add(new Option(option.label, option.value));
        });

        modalAssignedTo.disabled = !role || matchingUsers.length === 0;
        modalAssignedTo.value = matchingUsers.some((option) => option.value === selectedName)
            ? selectedName
            : '';
    };

    if (modalAssigneeRole) {
        modalAssigneeRole.addEventListener('change', () => {
            filterAssignees(modalAssigneeRole.value);
        });
    }

    filterAssignees('');

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
        const modalBody = scheduleModal?.querySelector('.maintenance-modal-body');
        if (modalBody) modalBody.scrollTop = 0;
        window.requestAnimationFrame(function() {
            scheduleForm?.querySelector('.field-control:not([disabled])')?.focus();
        });
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
            if (label) label.textContent = willExpand ? 'Hide issues' : `View ${count} more`;
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
            if (modalAssignedTo) {
                const assignedTo = row.getAttribute('data-assigned_to') || '';
                const matchingAssignee = assigneeOptions.find((option) => option.value === assignedTo);

                if (modalAssigneeRole) {
                    modalAssigneeRole.value = matchingAssignee?.role || '';
                }
                filterAssignees(matchingAssignee?.role || '', assignedTo);

                if (assignedTo && !matchingAssignee) {
                    modalAssignedTo.disabled = false;
                    modalAssignedTo.add(new Option(`${assignedTo} · Existing assignment`, assignedTo));
                    modalAssignedTo.value = assignedTo;
                }
            }
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
            if (modalPhotoRequirement) modalPhotoRequirement.value = row.getAttribute('data-photo_requirement') || 'Optional';
            if (modalProofPhoto) modalProofPhoto.value = '';
            if (modalExistingProof) {
                const proofUrl = row.getAttribute('data-proof_photo_url') || '';
                modalExistingProof.href = proofUrl || '#';
                modalExistingProof.dataset.hasProof = proofUrl ? '1' : '0';
                modalExistingProof.style.display = proofUrl ? 'inline-flex' : 'none';
            }
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
            if (modalAssigneeRole) modalAssigneeRole.value = '';
            filterAssignees('');
            if (modalPhotoRequirement) modalPhotoRequirement.value = 'Optional';
            if (modalProofPhoto) modalProofPhoto.value = '';
            if (modalExistingProof) {
                modalExistingProof.href = '#';
                modalExistingProof.dataset.hasProof = '0';
                modalExistingProof.style.display = 'none';
            }
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
            const proofFile = modalProofPhoto?.files?.[0] || null;
            const hasExistingProof = modalExistingProof?.dataset?.hasProof === '1';
            if (status === 'Completed' && modalPhotoRequirement?.value === 'Required' && !proofFile && !hasExistingProof) {
                window.alert('Please upload the required proof photo before completing this maintenance task.');
                return false;
            }

            const monthNum = modalTriggerMonth?.value;
            const yearVal = modalTriggerYear?.value;
            const monthName = monthNum
                ? new Date(2000, parseInt(monthNum, 10) - 1, 1).toLocaleString('default', { month: 'long' })
                : '';
            const triggerMonth = monthName && yearVal ? `${monthName} ${yearVal}` : '';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const payload = new FormData();
            payload.append('maintenance_id', modalMaintenanceId?.value || '');
            payload.append('facility_id', modalFacility?.value || '');
            payload.append('trigger_month', triggerMonth);
            payload.append('issue_type', modalIssueType?.value || '');
            payload.append('maintenance_type', modalMaintType?.value || '');
            payload.append('scheduled_date', modalScheduleDate?.value || '');
            payload.append('assigned_to', modalAssignedTo?.value || '');
            payload.append('remarks', modalRemarks?.value || '');
            payload.append('maintenance_status', status || '');
            payload.append('completed_date', completedDate || '');
            payload.append('photo_requirement', modalPhotoRequirement?.value || 'Optional');
            payload.append('_token', csrfToken);
            if (proofFile) payload.append('proof_photo', proofFile);

            const saveButton = scheduleForm.querySelector('.maintenance-btn-save');
            const restoreSaveButton = () => {
                if (!saveButton) return;
                saveButton.disabled = false;
                saveButton.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Maintenance';
            };
            if (saveButton) {
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            }

            try {
                const response = await fetch("{{ route('modules.maintenance.schedule') }}", {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: payload,
                });
                const body = await response.json().catch(() => ({}));
                if (!response.ok || !body.success) {
                    const errors = body?.errors ? Object.values(body.errors).flat().join('\n') : '';
                    window.alert(body?.message || errors || 'Failed to save maintenance.');
                    restoreSaveButton();
                    return;
                }
                location.reload();
            } catch (err) {
                window.alert('Network error while saving maintenance.');
                restoreSaveButton();
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
