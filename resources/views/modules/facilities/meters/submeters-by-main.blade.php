@extends('layouts.qc-admin')
@section('title', 'Linked Sub-meters')

@php
    $mainMeter = $mainMeter ?? null;
    $subMeters = $subMeters ?? collect();
    $linkedSubCount = $linkedSubCount ?? 0;
    $activeLinkedSubCount = $activeLinkedSubCount ?? 0;
    $approvedLinkedSubCount = $approvedLinkedSubCount ?? 0;
    $archivedSubCount = $archivedSubCount ?? 0;
    $canManageMeters = $canManageMeters ?? false;
    $canApproveMeters = $canApproveMeters ?? false;
@endphp

<style>
    .submeters-page {
        --panel-bg: #ffffff;
        --panel-border: #e2e8f0;
        --panel-head: #eef2ff;
        --text-main: #0f172a;
        --text-sub: #475569;
        width:min(1440px,100%);
        margin:0 auto;
        padding:18px;
    }

    .submeter-report-card { background:#fff; border:1px solid #e2e8f0; border-radius:20px; box-shadow:0 12px 34px rgba(15,23,42,.07); padding:24px; }
    .submeter-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
    .submeter-heading { display:flex; align-items:center; gap:13px; }
    .submeter-heading-icon { width:48px; height:48px; display:grid; place-items:center; flex:0 0 auto; border-radius:14px; color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); box-shadow:0 10px 22px rgba(37,99,235,.22); }
    .submeter-page-title { margin:0; color:#0f172a; font-size:1.55rem; font-weight:900; }
    .submeter-page-subtitle { margin:5px 0 0; color:#64748b; font-size:.88rem; font-weight:650; }
    .submeter-kpis { display:flex; gap:7px; flex-wrap:wrap; margin-top:10px; }
    .submeter-kpi { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:5px 9px; font-size:.74rem; font-weight:850; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; }
    .submeter-kpi.active { border-color:#99f6e4; background:#f0fdfa; color:#0f766e; }
    .submeter-kpi.approved { border-color:#c7d2fe; background:#eef2ff; color:#4338ca; }
    .submeter-back { text-decoration:none; background:#f8fafc; color:#334155; border:1px solid #cbd5e1; border-radius:11px; padding:10px 13px; font-weight:800; display:inline-flex; align-items:center; gap:7px; }

    .main-meter-summary { margin-top:16px; border:1px solid #e2e8f0; border-radius:16px; background:#f8fafc; padding:14px; }
    .main-meter-summary-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
    .main-meter-summary-title { color:#1e293b; font-size:.82rem; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
    .main-meter-summary-title i { color:#2563eb; margin-right:6px; }
    .main-meter-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; }
    .main-meter-item { min-height:72px; border:1px solid #e2e8f0; border-radius:11px; background:#fff; padding:11px; }
    .main-meter-label { display:flex; align-items:center; gap:6px; color:#64748b; font-size:.69rem; font-weight:850; text-transform:uppercase; }
    .main-meter-label i { color:#2563eb; }
    .main-meter-value { margin-top:7px; color:#0f172a; font-size:.9rem; font-weight:850; word-break:break-word; }

    .submeter-list-card {
        background: var(--panel-bg);
        border-radius: 16px;
        box-shadow: none;
        border:1px solid var(--panel-border);
        padding: 16px 18px;
        margin-top: 14px;
    }

    .submeter-list-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .submeter-list-title {
        font-weight: 800;
        color: #1e293b;
    }
    .submeter-list-title i { color:#2563eb; margin-right:7px; }
    .submeter-list-count { display:inline-flex; align-items:center; justify-content:center; min-width:26px; margin-left:7px; padding:3px 7px; border-radius:999px; background:#dbeafe; color:#1d4ed8; font-size:.68rem; font-weight:900; vertical-align:middle; }
    .submeter-list-copy { display:block; margin-top:3px; color:#64748b; font-size:.74rem; font-weight:650; }

    .submeter-list-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .submeter-list-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        border-radius: 10px;
        padding: 8px 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .submeter-list-btn.primary {
        border: none;
        background: #2563eb;
        color: #fff;
        cursor: pointer;
        padding: 9px 12px;
    }

    .submeter-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .submeter-search-input {
        width: min(420px, 100%);
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 9px 10px 9px 36px;
        font-size: .84rem;
        color: #0f172a;
        background: #fff;
    }
    .submeter-search-wrap { position:relative; width:min(460px,100%); }
    .submeter-search-wrap > i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#64748b; pointer-events:none; }
    .submeter-search-wrap .submeter-search-input { width:100%; }

    .submeter-table-note {
        font-size: .76rem;
        color: #64748b;
        font-weight: 700;
    }
    .submeter-result-count { display:inline-flex; align-items:center; gap:5px; margin-left:6px; padding:3px 7px; border-radius:999px; background:#f1f5f9; color:#475569; font-size:.68rem; font-weight:850; }

    .submeter-table-wrap {
        border: 1px solid var(--panel-border);
        border-radius: 12px;
        overflow: auto;
        background: #fff;
    }

    .submeter-table {
        width: 100%;
        min-width: 1080px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .submeter-table th,
    .submeter-table td { box-sizing:border-box; }

    .submeter-table th:nth-child(1) { width:20%; }
    .submeter-table th:nth-child(2) { width:13%; }
    .submeter-table th:nth-child(3) { width:18%; }
    .submeter-table th:nth-child(4) { width:9%; }
    .submeter-table th:nth-child(5) { width:10%; }
    .submeter-table th:nth-child(6) { width:10%; }
    .submeter-table th:nth-child(7) { width:20%; }

    .submeter-table thead th {
        padding: 11px 12px;
        text-align: left;
        color: #1e293b;
        border-bottom: 1px solid #dbeafe;
        background: var(--panel-head);
        font-size: .92rem;
        font-weight: 800;
        position:sticky;
        top:0;
        z-index:2;
    }

    .submeter-table thead th:first-child { position:sticky; left:0; z-index:4; background:var(--panel-head); }
    .submeter-table tbody td:first-child { position:sticky; left:0; z-index:1; background:#fff; box-shadow:8px 0 14px -14px rgba(15,23,42,.55); }
    .submeter-table tbody tr:hover td:first-child { background:#f8fafc; }

    .submeter-table thead th.right,
    .submeter-table tbody td.right {
        text-align: right;
    }

    .submeter-table thead th.center,
    .submeter-table tbody td.center {
        text-align: center;
    }

    .submeter-table tbody tr {
        transition: background-color .15s ease;
    }

    .submeter-table tbody tr:hover {
        background: #f8fafc;
    }

    .submeter-table tbody td {
        padding: 12px;
        border-bottom: 1px solid var(--panel-border);
        color: #334155;
        font-size: .93rem;
        vertical-align: middle;
    }

    .submeter-table tbody tr:last-child td {
        border-bottom: none;
    }

    .submeter-name-cell {
        font-weight: 800;
        font-size: 1.04rem;
        color: var(--text-main);
    }
    .submeter-name-cell::before { content:''; display:inline-block; width:7px; height:7px; margin-right:8px; border-radius:999px; background:#3b82f6; box-shadow:0 0 0 4px rgba(59,130,246,.12); vertical-align:2px; }

    .submeter-baseline-cell {
        color: var(--text-main);
        font-weight: 800;
        font-size: 1.02rem;
        white-space:nowrap;
    }

    .submeter-status-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: 2px 9px;
        font-size: .72rem;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .submeter-action-wrap {
        display: inline-flex;
        gap: 4px;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        white-space:nowrap;
    }

    .submeter-action-wrap form { display:inline-flex !important; margin:0; }

    .submeter-action-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
        flex:0 0 auto;
        transition:transform .15s ease,filter .15s ease;
    }
    .submeter-action-btn:hover { transform:translateY(-1px); filter:brightness(.96); }

    .submeter-action-btn.danger {
        border-color: #fecaca;
        background: #fee2e2;
        color: #b91c1c;
    }
    .submeter-action-btn.is-unapprove { border-color:#fdba74; background:#fff7ed; color:#c2410c; }
    .submeter-action-btn.is-approve { border-color:#86efac; background:#dcfce7; color:#166534; }
    .submeter-action-btn.is-view { width:auto; padding:0 9px; gap:6px; background:#2563eb; border-color:#2563eb; color:#fff; }

    .submeter-status-pill.is-active { border-color:#86efac; background:#dcfce7; color:#166534; }
    .submeter-status-pill.is-inactive { border-color:#fecaca; background:#fee2e2; color:#991b1b; }
    .submeter-status-pill.is-approved { border-color:#93c5fd; background:#dbeafe; color:#1d4ed8; }
    .submeter-status-pill.is-pending { border-color:#fdba74; background:#fff7ed; color:#9a3412; }

    .submeter-filter-empty {
        display: none;
        margin-top: 10px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        color: #64748b;
        font-weight: 700;
    }

    .submeter-empty-state { border:1px dashed #cbd5e1; border-radius:12px; padding:24px 16px; color:#64748b; font-weight:700; text-align:center; background:#f8fafc; }
    .submeter-empty-state i { display:block; margin-bottom:8px; color:#2563eb; font-size:1.1rem; }

    .submeter-row-clickable {
        cursor: pointer;
    }

    .submeter-row-clickable:focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: -2px;
    }

    .submeter-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .submeter-detail-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        padding: 11px 12px;
        min-height:72px;
    }

    .submeter-detail-item.is-featured { background:#eff6ff; border-color:#bfdbfe; }
    .submeter-detail-item.is-wide { grid-column:1/-1; min-height:auto; }

    .submeter-detail-item-label {
        font-size: .74rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
        display:flex;
        align-items:center;
        gap:6px;
    }

    .submeter-detail-item-label i { color:#2563eb; width:14px; text-align:center; }

    .submeter-detail-item-value {
        margin-top: 6px;
        font-size: .9rem;
        color: #0f172a;
        font-weight: 700;
        word-break: break-word;
    }

    .submeter-detail-overlay { display:none; position:fixed; inset:0; z-index:10059; padding:16px; background:rgba(2,6,23,.7); backdrop-filter:blur(6px); align-items:center; justify-content:center; }
    .submeter-detail-modal { width:min(820px,100%); max-height:calc(100vh - 32px); overflow:auto; border:1px solid #dbeafe; border-radius:20px; background:#fff; box-shadow:0 28px 80px rgba(2,6,23,.35); }
    .submeter-detail-header { position:relative; display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding:18px 56px 16px 20px; border-bottom:1px solid #dbeafe; background:linear-gradient(135deg,#eff6ff,#fff); }
    .submeter-detail-identity { display:flex; align-items:center; gap:12px; min-width:0; }
    .submeter-detail-icon { width:42px; height:42px; display:grid; place-items:center; flex:0 0 auto; border-radius:12px; color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); }
    .submeter-detail-eyebrow { color:#2563eb; font-size:.67rem; font-weight:900; text-transform:uppercase; letter-spacing:.1em; }
    .submeter-detail-title { margin:3px 0 0; color:#0f172a; font-size:1.18rem; font-weight:900; }
    .submeter-detail-subtitle { margin:3px 0 0; color:#64748b; font-size:.78rem; }
    .submeter-detail-close { position:absolute; top:14px; right:15px; width:34px; height:34px; display:grid; place-items:center; border:1px solid #dbeafe; border-radius:10px; background:#fff; color:#64748b; cursor:pointer; }
    .submeter-detail-badges { display:flex; gap:6px; flex-wrap:wrap; }
    .submeter-detail-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:999px; border:1px solid #cbd5e1; background:#f8fafc; color:#475569; font-size:.67rem; font-weight:900; }
    .submeter-detail-badge.is-active { background:#dcfce7; border-color:#86efac; color:#166534; }
    .submeter-detail-badge.is-approved { background:#dbeafe; border-color:#93c5fd; color:#1d4ed8; }
    .submeter-detail-badge.is-warning { background:#fff7ed; border-color:#fdba74; color:#9a3412; }
    .submeter-detail-body { padding:16px 20px 18px; }
    .submeter-detail-section-title { display:flex; align-items:center; gap:7px; margin-bottom:10px; color:#475569; font-size:.7rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
    .submeter-detail-section-title i { color:#2563eb; }
    .submeter-detail-footer { display:flex; justify-content:flex-end; margin-top:12px; padding-top:12px; border-top:1px solid #e2e8f0; }
    .submeter-detail-dismiss { border:1px solid #cbd5e1; border-radius:10px; background:#f1f5f9; color:#334155; padding:9px 14px; font-weight:800; cursor:pointer; }

    @media (max-width: 840px) {
        .submeters-page { padding:10px; }
        .submeter-report-card { padding:14px; border-radius:17px; }
        .main-meter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .submeter-table-toolbar {
            align-items: stretch;
        }

        .submeter-search-input {
            width: 100%;
        }
    }

    @media (max-width:520px) {
        .submeter-hero { flex-direction:column; }
        .submeter-back { width:100%; justify-content:center; }
        .main-meter-grid { grid-template-columns:1fr; }
        .submeter-list-actions { width:100%; }
        .submeter-list-btn { flex:1; justify-content:center; }
        .submeter-detail-grid { grid-template-columns:1fr; }
        .submeter-detail-item.is-wide { grid-column:auto; }
        .submeter-detail-header { flex-direction:column; }
    }

    body.dark-mode .submeter-report-card { background:#0f172a !important; border-color:#334155 !important; box-shadow:0 18px 46px rgba(2,6,23,.42); }
    body.dark-mode .submeter-page-title { color:#f8fafc !important; }
    body.dark-mode .submeter-page-subtitle { color:#94a3b8 !important; }
    body.dark-mode .submeter-back { background:#111827 !important; border-color:#334155 !important; color:#e2e8f0 !important; }
    body.dark-mode .submeter-kpi { background:#172554 !important; border-color:#1e40af !important; color:#bfdbfe !important; }
    body.dark-mode .submeter-kpi.active { background:#042f2e !important; border-color:#0f766e !important; color:#99f6e4 !important; }
    body.dark-mode .submeter-kpi.approved { background:#1e1b4b !important; border-color:#4338ca !important; color:#c7d2fe !important; }
    body.dark-mode .main-meter-summary { background:#0b1220 !important; border-color:#273449 !important; }
    body.dark-mode .main-meter-summary-title { color:#cbd5e1 !important; }
    body.dark-mode .main-meter-item { background:#111827 !important; border-color:#334155 !important; }
    body.dark-mode .main-meter-label { color:#93c5fd !important; }
    body.dark-mode .main-meter-value { color:#f8fafc !important; }

    body.dark-mode .submeters-page .submeter-list-card,
    body.dark-mode .submeters-page .submeter-table-wrap {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-list-title,
    body.dark-mode .submeters-page .submeter-table thead th,
    body.dark-mode .submeters-page .submeter-name-cell,
    body.dark-mode .submeters-page .submeter-baseline-cell {
        color: #e2e8f0 !important;
    }
    body.dark-mode .submeters-page .submeter-list-copy { color:#94a3b8 !important; }
    body.dark-mode .submeters-page .submeter-list-count { background:#172554 !important; color:#bfdbfe !important; }
    body.dark-mode .submeters-page .submeter-result-count { background:#1e293b !important; color:#cbd5e1 !important; }

    body.dark-mode .submeters-page .submeter-table-note,
    body.dark-mode .submeters-page .submeter-table tbody td {
        color: #cbd5e1 !important;
    }

    body.dark-mode .submeters-page .submeter-table thead th {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-table tbody td {
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-table tbody td:first-child { background:#0f172a !important; }
    body.dark-mode .submeters-page .submeter-table tbody tr:hover td:first-child { background:#111827 !important; }

    body.dark-mode .submeters-page .submeter-table tbody tr:hover {
        background: #111827 !important;
    }

    body.dark-mode .submeters-page .submeter-search-input {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .submeters-page .submeter-list-btn {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }

    body.dark-mode .submeters-page .submeter-list-btn.primary {
        background: #1d4ed8 !important;
        color: #fff !important;
    }

    body.dark-mode .submeters-page .submeter-action-btn {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #93c5fd !important;
    }

    body.dark-mode .submeters-page .submeter-action-btn.danger {
        color: #fda4af !important;
    }
    body.dark-mode .submeters-page .submeter-action-btn.is-unapprove { background:#431407 !important; border-color:#f97316 !important; color:#fdba74 !important; }
    body.dark-mode .submeters-page .submeter-action-btn.is-approve { background:#14532d !important; border-color:#22c55e !important; color:#bbf7d0 !important; }

    body.dark-mode .submeters-page .submeter-action-btn.is-view { background:#2563eb !important; border-color:#3b82f6 !important; color:#fff !important; }
    body.dark-mode .submeters-page .submeter-status-pill.is-active { background:#14532d !important; border-color:#22c55e !important; color:#bbf7d0 !important; }
    body.dark-mode .submeters-page .submeter-status-pill.is-inactive { background:#7f1d1d !important; border-color:#ef4444 !important; color:#fecaca !important; }
    body.dark-mode .submeters-page .submeter-status-pill.is-approved { background:#172554 !important; border-color:#3b82f6 !important; color:#bfdbfe !important; }
    body.dark-mode .submeters-page .submeter-status-pill.is-pending { background:#431407 !important; border-color:#f97316 !important; color:#fed7aa !important; }

    body.dark-mode .submeters-page .submeter-detail-item {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-detail-item-label {
        color: #93c5fd !important;
    }

    body.dark-mode .submeters-page .submeter-detail-item-value {
        color: #e2e8f0 !important;
    }
    body.dark-mode #submeterDetailModal .submeter-detail-modal { background:#0f172a !important; border-color:#334155 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-header { background:linear-gradient(135deg,#172554,#0f172a) !important; border-color:#334155 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-title { color:#f8fafc !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-subtitle { color:#94a3b8 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-close { background:#111827 !important; border-color:#334155 !important; color:#cbd5e1 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-section-title { color:#cbd5e1 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-item { background:#111827 !important; border-color:#334155 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-item.is-featured { background:#172554 !important; border-color:#1e40af !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-item-label { color:#93c5fd !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-item-value { color:#e2e8f0 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-footer { border-color:#334155 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-dismiss { background:#111827 !important; border-color:#334155 !important; color:#e2e8f0 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-badge.is-active { background:#14532d !important; border-color:#22c55e !important; color:#bbf7d0 !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-badge.is-approved { background:#172554 !important; border-color:#3b82f6 !important; color:#bfdbfe !important; }
    body.dark-mode #submeterDetailModal .submeter-detail-badge.is-warning { background:#431407 !important; border-color:#f97316 !important; color:#fed7aa !important; }
    body.dark-mode .submeters-page .submeter-empty-state,
    body.dark-mode .submeters-page .submeter-filter-empty { background:#111827 !important; border-color:#475569 !important; color:#cbd5e1 !important; }
</style>

@section('content')
<div class="submeters-page" style="width:100%;margin:0 auto;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 14px;border-radius:12px;font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 14px;border-radius:12px;font-weight:700;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 14px;border-radius:12px;font-weight:700;">
            Please check the sub-meter form fields and try again.
        </div>
    @endif

    <div class="report-card-container submeter-report-card">
        <div class="submeter-hero">
            <div class="submeter-heading">
                <span class="submeter-heading-icon"><i class="fa fa-network-wired"></i></span>
                <div>
                    <h2 class="submeter-page-title">Linked Sub-meters</h2>
                    <p class="submeter-page-subtitle">{{ $facility->name }} · Main Meter: <strong>{{ $mainMeter->meter_name ?? 'N/A' }}</strong></p>
                    <div class="submeter-kpis">
                        <span class="submeter-kpi"><i class="fa fa-link"></i> {{ $linkedSubCount }} linked</span>
                        <span class="submeter-kpi active"><i class="fa fa-bolt"></i> {{ $activeLinkedSubCount }} active</span>
                        <span class="submeter-kpi approved"><i class="fa fa-circle-check"></i> {{ $approvedLinkedSubCount }} approved</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="submeter-back"><i class="fa fa-arrow-left"></i> Back to Energy Profile</a>
        </div>

        <section class="main-meter-summary">
            <div class="main-meter-summary-head">
                <div class="main-meter-summary-title"><i class="fa fa-gauge-high"></i> Main meter details</div>
            </div>
            <div class="main-meter-grid">
                <div class="main-meter-item"><div class="main-meter-label"><i class="fa fa-hashtag"></i> Meter No.</div><div class="main-meter-value">{{ $mainMeter->meter_number ?: 'N/A' }}</div></div>
                <div class="main-meter-item"><div class="main-meter-label"><i class="fa fa-location-dot"></i> Location</div><div class="main-meter-value">{{ $mainMeter->location ?: 'N/A' }}</div></div>
                <div class="main-meter-item"><div class="main-meter-label"><i class="fa fa-power-off"></i> Status</div><div class="main-meter-value">{{ strtoupper((string) ($mainMeter->status ?? 'N/A')) }}</div></div>
                <div class="main-meter-item"><div class="main-meter-label"><i class="fa fa-chart-line"></i> Baseline</div><div class="main-meter-value">{{ is_numeric($mainMeter->baseline_kwh) ? number_format((float) $mainMeter->baseline_kwh, 2) . ' kWh' : 'N/A' }}</div></div>
            </div>
        </section>

    <div class="submeter-list-card">
        <div class="submeter-list-head">
            <div>
                <div class="submeter-list-title"><i class="fa fa-diagram-project"></i> Linked sub-meter directory <span class="submeter-list-count">{{ $linkedSubCount }}</span></div>
                <span class="submeter-list-copy">Manage monitoring points connected to {{ $mainMeter->meter_name ?? 'this main meter' }}.</span>
            </div>
            <div class="submeter-list-actions">
                <a href="{{ route('modules.facilities.meters.archive', ['facility' => $facility->id, 'meter_type' => 'sub', 'sub_only' => '1', 'main_meter_id' => (int) ($mainMeter->id ?? 0)]) }}"
                   title="View archived sub-meters"
                   class="submeter-list-btn">
                    <i class="fa fa-box-archive"></i> Archive
                    @if($archivedSubCount > 0)
                        <span style="background:#e11d48;color:#fff;border-radius:999px;padding:1px 7px;font-size:.72rem;">{{ $archivedSubCount }}</span>
                    @endif
                </a>
                @if($canManageMeters)
                    <button type="button"
                            onclick="openAddLinkedSubmeterModal()"
                            class="submeter-list-btn primary">
                        <i class="fa fa-plus"></i> Add Sub-meter
                    </button>
                @endif
            </div>
        </div>
        @if($subMeters->isEmpty())
            <div class="submeter-empty-state"><i class="fa fa-diagram-project"></i>No linked sub-meter found for this main meter.</div>
        @else
            <div class="submeter-table-toolbar">
                <div class="submeter-search-wrap">
                    <i class="fa fa-search"></i>
                    <input type="search"
                           class="submeter-search-input"
                           data-submeter-search-target="linkedSubmeterTableBody"
                           data-submeter-result-count="linkedSubmeterResultCount"
                           aria-label="Search linked sub-meters"
                           placeholder="Search name, number, location, or status">
                </div>
                <span class="submeter-table-note"><i class="fa fa-circle-info"></i> Select a row to view full details. <span class="submeter-result-count" id="linkedSubmeterResultCount">{{ $linkedSubCount }} shown</span></span>
            </div>
            <div class="submeter-table-wrap">
                <table class="submeter-table">
                    <thead>
                        <tr>
                            <th>Sub-meter</th>
                            <th>Meter No.</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th class="right">Baseline</th>
                            @if($canManageMeters || $canApproveMeters)
                                <th class="center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="linkedSubmeterTableBody">
                        @foreach($subMeters as $sub)
                            @php
                                $isActive = strtolower((string) ($sub->status ?? '')) === 'active';
                                $isApproved = !empty($sub->approved_at);
                                $subApprovalText = $isApproved ? 'approved' : 'not approved';
                                $subMeterSearchText = strtolower(trim(implode(' ', [
                                    (string) ($sub->meter_name ?? ''),
                                    (string) ($sub->meter_number ?? ''),
                                    (string) ($sub->location ?? ''),
                                    (string) ($sub->status ?? ''),
                                    $subApprovalText,
                                    is_numeric($sub->baseline_kwh) ? number_format((float) $sub->baseline_kwh, 2, '.', '') : '',
                                ])));
                            @endphp
                            <tr data-submeter-row
                                data-submeter-search="{{ $subMeterSearchText }}"
                                data-submeter-name="{{ (string) ($sub->meter_name ?? 'N/A') }}"
                                data-submeter-number="{{ (string) ($sub->meter_number ?? 'N/A') }}"
                                data-submeter-parent="{{ (string) ($mainMeter->meter_name ?? 'N/A') }}"
                                data-submeter-location="{{ (string) ($sub->location ?? 'N/A') }}"
                                data-submeter-status="{{ strtoupper((string) ($sub->status ?? 'N/A')) }}"
                                data-submeter-approval="{{ $isApproved ? 'APPROVED' : 'NOT APPROVED' }}"
                                data-submeter-created-at="{{ $sub->created_at ? $sub->created_at->format('M d, Y h:i A') : 'N/A' }}"
                                data-submeter-approved-at="{{ $sub->approved_at ? $sub->approved_at->format('Y-m-d H:i') : 'N/A' }}"
                                data-submeter-baseline="{{ is_numeric($sub->baseline_kwh) ? number_format((float) $sub->baseline_kwh, 2) . ' kWh' : 'N/A' }}"
                                data-submeter-multiplier="{{ is_numeric($sub->multiplier) ? number_format((float) $sub->multiplier, 4) : 'N/A' }}"
                                data-submeter-notes="{{ (string) ($sub->notes ?? 'N/A') }}"
                                class="submeter-row-clickable"
                                tabindex="0"
                                role="button"
                                aria-label="View details for {{ (string) ($sub->meter_name ?? 'sub-meter') }}">
                                <td class="submeter-name-cell">{{ $sub->meter_name }}</td>
                                <td>{{ $sub->meter_number ?: 'N/A' }}</td>
                                <td>{{ $sub->location ?: 'N/A' }}</td>
                                <td>
                                    <span class="submeter-status-pill {{ $isActive ? 'is-active' : 'is-inactive' }}">
                                        {{ strtoupper((string) ($sub->status ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="submeter-status-pill {{ $isApproved ? 'is-approved' : 'is-pending' }}">
                                        {{ $isApproved ? 'APPROVED' : 'NOT APPROVED' }}
                                    </span>
                                </td>
                                <td class="right submeter-baseline-cell">
                                    {{ is_numeric($sub->baseline_kwh) ? number_format((float) $sub->baseline_kwh, 2) . ' kWh' : 'N/A' }}
                                </td>
                                @if($canManageMeters || $canApproveMeters)
                                    <td class="center">
                                        <div class="submeter-action-wrap">
                                            <button type="button"
                                                    onclick="openSubmeterDetailModalFromButton(this)"
                                                    title="View sub-meter details"
                                                    aria-label="View sub-meter details"
                                                    class="submeter-action-btn is-view">
                                                <i class="fa fa-eye"></i>
                                                <span>View</span>
                                            </button>
                                            @if($canApproveMeters)
                                                <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facility->id, $sub->id]) }}" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="_redirect_to" value="main_submeters">
                                                    <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
                                                    <button type="submit"
                                                            title="{{ $isApproved ? 'Unapprove sub-meter' : 'Approve sub-meter' }}"
                                                            aria-label="{{ $isApproved ? 'Unapprove sub-meter' : 'Approve sub-meter' }}"
                                                            class="submeter-action-btn {{ $isApproved ? 'is-unapprove' : 'is-approve' }}">
                                                        <i class="fa {{ $isApproved ? 'fa-ban' : 'fa-check' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($canManageMeters)
                                                <button type="button"
                                                        onclick="openEditLinkedSubmeterModalFromButton(this)"
                                                        data-sub-id="{{ (int) $sub->id }}"
                                                        data-sub-name="{{ (string) ($sub->meter_name ?? '') }}"
                                                        data-sub-number="{{ (string) ($sub->meter_number ?? '') }}"
                                                        data-sub-location="{{ (string) ($sub->location ?? '') }}"
                                                        data-sub-status="{{ (string) ($sub->status ?? 'active') }}"
                                                        data-sub-multiplier="{{ (string) ($sub->multiplier ?? '1') }}"
                                                        data-sub-baseline="{{ (string) ($sub->baseline_kwh ?? '') }}"
                                                        data-sub-notes="{{ (string) ($sub->notes ?? '') }}"
                                                        title="Edit sub-meter"
                                                        aria-label="Edit sub-meter"
                                                        class="submeter-action-btn">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        onclick="openArchiveLinkedSubmeterModal({{ (int) $sub->id }}, @js($sub->meter_name))"
                                                        title="Archive sub-meter"
                                                        aria-label="Archive sub-meter"
                                                        class="submeter-action-btn danger">
                                                    <i class="fa fa-archive"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="submeter-filter-empty" id="linkedSubmeterTableBodyEmpty">No matching sub-meter found.</div>
        @endif
    </div>
    </div>
</div>

<div id="submeterDetailModal" class="submeter-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="submeterDetailModalTitle">
    <div class="submeter-detail-modal">
        <header class="submeter-detail-header">
            <div class="submeter-detail-identity">
                <span class="submeter-detail-icon"><i class="fa fa-gauge-high"></i></span>
                <div>
                    <span class="submeter-detail-eyebrow">Sub-meter record</span>
                    <h3 id="submeterDetailModalTitle" class="submeter-detail-title">Sub-meter Details</h3>
                    <p class="submeter-detail-subtitle">Technical and administrative information</p>
                </div>
            </div>
            <div class="submeter-detail-badges">
                <span id="submeterDetailHeaderStatus" class="submeter-detail-badge">Status</span>
                <span id="submeterDetailHeaderApproval" class="submeter-detail-badge">Approval</span>
            </div>
            <button type="button" onclick="closeSubmeterDetailModal()" class="submeter-detail-close" aria-label="Close sub-meter details"><i class="fa fa-xmark"></i></button>
        </header>
        <div class="submeter-detail-body">
            <div class="submeter-detail-section-title"><i class="fa fa-file-lines"></i> Meter information</div>
            <div class="submeter-detail-grid">
                <div class="submeter-detail-item"><div class="submeter-detail-item-label"><i class="fa fa-hashtag"></i> Meter No.</div><div id="submeterDetailNo" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item is-featured"><div class="submeter-detail-item-label"><i class="fa fa-code-branch"></i> Parent Main Meter</div><div id="submeterDetailParent" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item is-featured"><div class="submeter-detail-item-label"><i class="fa fa-location-dot"></i> Location</div><div id="submeterDetailLocation" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item"><div class="submeter-detail-item-label"><i class="fa fa-power-off"></i> Status</div><div id="submeterDetailStatus" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item"><div class="submeter-detail-item-label"><i class="fa fa-circle-check"></i> Approval</div><div id="submeterDetailApproval" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item is-featured"><div class="submeter-detail-item-label"><i class="fa fa-chart-line"></i> Baseline</div><div id="submeterDetailBaseline" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item"><div class="submeter-detail-item-label"><i class="fa fa-calculator"></i> Multiplier</div><div id="submeterDetailMultiplier" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item"><div class="submeter-detail-item-label"><i class="fa fa-calendar-plus"></i> Date Added</div><div id="submeterDetailCreatedAt" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item"><div class="submeter-detail-item-label"><i class="fa fa-calendar-check"></i> Approved At</div><div id="submeterDetailApprovedAt" class="submeter-detail-item-value">-</div></div>
                <div class="submeter-detail-item is-wide"><div class="submeter-detail-item-label"><i class="fa fa-comment-dots"></i> Notes</div><div id="submeterDetailNotes" class="submeter-detail-item-value">-</div></div>
            </div>
            <footer class="submeter-detail-footer"><button type="button" onclick="closeSubmeterDetailModal()" class="submeter-detail-dismiss">Close</button></footer>
        </div>
    </div>
</div>

<script>
function shouldIgnoreSubmeterRowClick(target) {
    if (!target || !target.closest) return false;
    return !!target.closest('button, a, form, input, select, textarea');
}

function openSubmeterDetailModalFromRow(row) {
    if (!row) return;

    const modal = document.getElementById('submeterDetailModal');
    if (!modal) return;

    const detailMap = {
        submeterDetailNo: row.getAttribute('data-submeter-number') || 'N/A',
        submeterDetailParent: row.getAttribute('data-submeter-parent') || 'N/A',
        submeterDetailLocation: row.getAttribute('data-submeter-location') || 'N/A',
        submeterDetailStatus: row.getAttribute('data-submeter-status') || 'N/A',
        submeterDetailApproval: row.getAttribute('data-submeter-approval') || 'N/A',
        submeterDetailCreatedAt: row.getAttribute('data-submeter-created-at') || 'N/A',
        submeterDetailApprovedAt: row.getAttribute('data-submeter-approved-at') || 'N/A',
        submeterDetailBaseline: row.getAttribute('data-submeter-baseline') || 'N/A',
        submeterDetailMultiplier: row.getAttribute('data-submeter-multiplier') || 'N/A',
        submeterDetailNotes: row.getAttribute('data-submeter-notes') || 'N/A',
    };

    Object.entries(detailMap).forEach(function(entry) {
        const el = document.getElementById(entry[0]);
        if (!el) return;
        el.textContent = entry[1] || 'N/A';
    });

    const title = document.getElementById('submeterDetailModalTitle');
    const statusBadge = document.getElementById('submeterDetailHeaderStatus');
    const approvalBadge = document.getElementById('submeterDetailHeaderApproval');
    const statusText = String(row.getAttribute('data-submeter-status') || 'N/A').toUpperCase();
    const approvalText = String(row.getAttribute('data-submeter-approval') || 'N/A').toUpperCase();
    if (title) title.textContent = row.getAttribute('data-submeter-name') || 'Sub-meter Details';
    if (statusBadge) {
        statusBadge.className = 'submeter-detail-badge ' + (statusText === 'ACTIVE' ? 'is-active' : 'is-warning');
        statusBadge.textContent = statusText;
    }
    if (approvalBadge) {
        approvalBadge.className = 'submeter-detail-badge ' + (approvalText === 'APPROVED' ? 'is-approved' : 'is-warning');
        approvalBadge.textContent = approvalText;
    }

    modal.style.display = 'flex';
    modal.querySelector('.submeter-detail-close')?.focus();
}

function openSubmeterDetailModalFromButton(button) {
    if (!button || !button.closest) return;
    const row = button.closest('[data-submeter-row]');
    if (!row) return;
    openSubmeterDetailModalFromRow(row);
}

function closeSubmeterDetailModal() {
    const modal = document.getElementById('submeterDetailModal');
    if (!modal) return;
    modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-submeter-search-target]').forEach(function(input) {
        input.addEventListener('input', function() {
            const listId = String(input.getAttribute('data-submeter-search-target') || '');
            const list = listId ? document.getElementById(listId) : null;
            if (!list) return;

            const query = String(input.value || '').trim().toLowerCase();
            const rows = Array.from(list.querySelectorAll('[data-submeter-row]'));
            let visible = 0;

            rows.forEach(function(row) {
                const haystack = String(row.getAttribute('data-submeter-search') || '').toLowerCase();
                const show = query === '' || haystack.includes(query);
                row.style.display = show ? '' : 'none';
                if (show) visible += 1;
            });

            const dynamicEmpty = document.getElementById(listId + 'Empty');
            if (dynamicEmpty) {
                dynamicEmpty.style.display = rows.length > 0 && visible === 0 ? 'block' : 'none';
            }

            const resultId = String(input.getAttribute('data-submeter-result-count') || '');
            const resultCount = resultId ? document.getElementById(resultId) : null;
            if (resultCount) resultCount.textContent = visible + ' shown';
        });
    });

    document.querySelectorAll('[data-submeter-row]').forEach(function(row) {
        row.addEventListener('click', function(event) {
            if (shouldIgnoreSubmeterRowClick(event.target)) return;
            openSubmeterDetailModalFromRow(row);
        });

        row.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openSubmeterDetailModalFromRow(row);
            }
        });
    });

    const detailModal = document.getElementById('submeterDetailModal');
    if (detailModal) {
        detailModal.addEventListener('click', function(event) {
            if (event.target === detailModal) {
                closeSubmeterDetailModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSubmeterDetailModal();
        }
    });
});
</script>

@if($canManageMeters)
<div id="addLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10060;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeAddLinkedSubmeterModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 6px;color:#2563eb;font-weight:800;">Add Sub-meter</h3>
        <div style="margin-bottom:12px;color:#475569;font-weight:600;">
            Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
        </div>

        <form method="POST"
              action="{{ route('modules.facilities.meters.store', $facility->id) }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            @csrf
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="add">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="meter_type" value="sub">
            <input type="hidden" name="parent_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">

            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Name <span style="color:#e11d48;">*</span></label>
                <input type="text" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 2F Lighting">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
                <input type="text" name="meter_number" maxlength="255" value="{{ old('meter_number') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. SM-2026-001">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
                <input type="text" name="location" maxlength="255" value="{{ old('location') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. Panel 3">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
                <select name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier <span style="color:#e11d48;">*</span></label>
                <input type="number" name="multiplier" min="0.0001" max="999999" step="0.0001" value="{{ old('multiplier', '1') }}" required
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="1.0000">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Baseline kWh</label>
                <input type="number" name="baseline_kwh" min="0" step="0.01" value="{{ old('baseline_kwh') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 1200.00">
                <div style="margin-top:5px;color:#64748b;font-size:.82rem;font-weight:600;">Recommended for sub-meter alert comparison.</div>
            </div>
            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
                <textarea name="notes" rows="3" maxlength="2000"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                          placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeAddLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Save Sub-meter</button>
            </div>
        </form>
    </div>
</div>

<div id="editLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10061;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeEditLinkedSubmeterModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 6px;color:#2563eb;font-weight:800;">Edit Sub-meter</h3>
        <div style="margin-bottom:12px;color:#475569;font-weight:600;">
            Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
        </div>

        @php
            $oldEditSubmeterId = (int) old('_submeter_edit_id');
            $oldEditAction = $oldEditSubmeterId > 0
                ? route('modules.facilities.meters.update', [$facility->id, $oldEditSubmeterId])
                : '#';
        @endphp
        <form id="editLinkedSubmeterForm"
              method="POST"
              action="{{ $oldEditAction }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="edit">
            <input type="hidden" name="_submeter_edit_id" id="edit_submeter_id" value="{{ $oldEditSubmeterId > 0 ? $oldEditSubmeterId : '' }}">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="meter_type" value="sub">
            <input type="hidden" name="parent_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">

            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Name <span style="color:#e11d48;">*</span></label>
                <input type="text" id="edit_meter_name" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 2F Lighting">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
                <input type="text" id="edit_meter_number" name="meter_number" maxlength="255" value="{{ old('meter_number') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. SM-2026-001">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
                <input type="text" id="edit_location" name="location" maxlength="255" value="{{ old('location') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. Panel 3">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
                <select id="edit_status" name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier <span style="color:#e11d48;">*</span></label>
                <input type="number" id="edit_multiplier" name="multiplier" min="0.0001" max="999999" step="0.0001" value="{{ old('multiplier', '1') }}" required
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="1.0000">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Baseline kWh</label>
                <input type="number" id="edit_baseline_kwh" name="baseline_kwh" min="0" step="0.01" value="{{ old('baseline_kwh') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 1200.00">
                <div style="margin-top:5px;color:#64748b;font-size:.82rem;font-weight:600;">Recommended for sub-meter alert comparison.</div>
            </div>
            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
                <textarea id="edit_notes" name="notes" rows="3" maxlength="2000"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                          placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeEditLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Update Sub-meter</button>
            </div>
        </form>
    </div>
</div>

<div id="archiveLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10062;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(520px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeArchiveLinkedSubmeterModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 10px;color:#e11d48;font-weight:800;">Delete Sub-meter</h3>
        <div id="archiveLinkedSubmeterLabel" style="color:#334155;margin-bottom:12px;"></div>

        <form id="archiveLinkedSubmeterForm"
              method="POST"
              action="#"
              style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="archive">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="_submeter_archive_id" id="archive_submeter_id" value="">
            <div>
                <label for="archive_submeter_reason" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Reason for Delete <span style="color:#e11d48;">*</span></label>
                <textarea id="archive_submeter_reason"
                          name="archive_reason"
                          required
                          maxlength="500"
                          rows="4"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                          placeholder="Example: removed panel, duplicate entry, no longer in use">{{ old('archive_reason') }}</textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeArchiveLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#e11d48;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddLinkedSubmeterModal() {
    const modal = document.getElementById('addLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'flex';
}

function closeAddLinkedSubmeterModal() {
    const modal = document.getElementById('addLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function openEditLinkedSubmeterModal(submeter) {
    const modal = document.getElementById('editLinkedSubmeterModal');
    const form = document.getElementById('editLinkedSubmeterForm');
    if (!modal || !form || !submeter) return;

    form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + Number(submeter.id || 0);
    document.getElementById('edit_submeter_id').value = String(submeter.id || '');
    document.getElementById('edit_meter_name').value = submeter.meter_name ?? '';
    document.getElementById('edit_meter_number').value = submeter.meter_number ?? '';
    document.getElementById('edit_location').value = submeter.location ?? '';
    document.getElementById('edit_status').value = submeter.status ?? 'active';
    document.getElementById('edit_multiplier').value = submeter.multiplier ?? '1';
    document.getElementById('edit_baseline_kwh').value = submeter.baseline_kwh ?? '';
    document.getElementById('edit_notes').value = submeter.notes ?? '';

    modal.style.display = 'flex';
}

function openEditLinkedSubmeterModalFromButton(button) {
    if (!button) return;
    openEditLinkedSubmeterModal({
        id: Number(button.getAttribute('data-sub-id') || 0),
        meter_name: String(button.getAttribute('data-sub-name') || ''),
        meter_number: String(button.getAttribute('data-sub-number') || ''),
        location: String(button.getAttribute('data-sub-location') || ''),
        status: String(button.getAttribute('data-sub-status') || 'active'),
        multiplier: String(button.getAttribute('data-sub-multiplier') || '1'),
        baseline_kwh: String(button.getAttribute('data-sub-baseline') || ''),
        notes: String(button.getAttribute('data-sub-notes') || ''),
    });
}

function closeEditLinkedSubmeterModal() {
    const modal = document.getElementById('editLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function openArchiveLinkedSubmeterModal(subMeterId, meterName) {
    const modal = document.getElementById('archiveLinkedSubmeterModal');
    const form = document.getElementById('archiveLinkedSubmeterForm');
    const label = document.getElementById('archiveLinkedSubmeterLabel');
    const idInput = document.getElementById('archive_submeter_id');
    const reason = document.getElementById('archive_submeter_reason');
    if (!modal || !form || !idInput) return;

    const id = Number(subMeterId || 0);
    form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + id;
    idInput.value = String(id);
    if (label) {
        label.textContent = 'Sub-meter: ' + String(meterName || '');
    }
    if (reason) {
        reason.value = '';
    }
    modal.style.display = 'flex';
}

function closeArchiveLinkedSubmeterModal() {
    const modal = document.getElementById('archiveLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const addModal = document.getElementById('addLinkedSubmeterModal');
    const editModal = document.getElementById('editLinkedSubmeterModal');
    const archiveModal = document.getElementById('archiveLinkedSubmeterModal');

    if (addModal) {
        addModal.addEventListener('click', function(event) {
            if (event.target === addModal) {
                closeAddLinkedSubmeterModal();
            }
        });
    }

    if (editModal) {
        editModal.addEventListener('click', function(event) {
            if (event.target === editModal) {
                closeEditLinkedSubmeterModal();
            }
        });
    }

    if (archiveModal) {
        archiveModal.addEventListener('click', function(event) {
            if (event.target === archiveModal) {
                closeArchiveLinkedSubmeterModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSubmeterDetailModal();
            closeAddLinkedSubmeterModal();
            closeEditLinkedSubmeterModal();
            closeArchiveLinkedSubmeterModal();
        }
    });
});
</script>

@if($errors->any() && old('_redirect_to') === 'main_submeters' && old('_submeter_modal') === 'add')
<script>
document.addEventListener('DOMContentLoaded', function () {
    openAddLinkedSubmeterModal();
});
</script>
@endif
@if($errors->any() && old('_redirect_to') === 'main_submeters' && old('_submeter_modal') === 'edit')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editLinkedSubmeterModal');
    if (modal) modal.style.display = 'flex';
});
</script>
@endif
@if($errors->any() && old('_redirect_to') === 'main_submeters' && old('_submeter_modal') === 'archive')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('archiveLinkedSubmeterModal');
    const form = document.getElementById('archiveLinkedSubmeterForm');
    const idInput = document.getElementById('archive_submeter_id');
    const reason = document.getElementById('archive_submeter_reason');
    const archiveId = Number(@json((int) old('_submeter_archive_id')));
    if (modal) modal.style.display = 'flex';
    if (form && archiveId > 0) {
        form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + archiveId;
    }
    if (idInput && archiveId > 0) {
        idInput.value = String(archiveId);
    }
    if (reason) {
        reason.value = @json((string) old('archive_reason'));
    }
});
</script>
@endif
@endif
@endsection
