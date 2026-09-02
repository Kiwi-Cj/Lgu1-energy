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
@php
    $mainMeterBaseline = is_numeric($mainMeter->baseline_kwh) ? (float) $mainMeter->baseline_kwh : 0;
    $historyReadings = $historicalMonthlyReadings ?? collect();
    $equipList = $facilityEquipments ?? collect();
    $equipTotalMonthlyKwh = round((float) $equipList->sum(fn ($eq) => $eq->monthly_kwh), 2);
    $equipTotalWatts = (float) $equipList->sum(fn ($eq) => $eq->total_watts);
    $equipTotalUnits = (int) $equipList->sum('quantity');
    $equipTotalItems = $equipList->count();
@endphp

<style>
.submeter-calc-tab-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 10px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #475569;
    font-size: 0.76rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.15s ease;
}
.submeter-calc-tab-btn.active {
    background: #ffffff;
    color: #2563eb;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
}
.submeter-calc-tab-btn:hover:not(.active) {
    color: #0f172a;
}
.submeter-dur-btn {
    border: none;
    background: transparent;
    padding: 5px 12px;
    font-size: 0.74rem;
    font-weight: 800;
    color: #475569;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.submeter-dur-btn.active {
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 1px 3px rgba(37,99,235,0.3);
}
.submeter-month-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 8px;
}
.submeter-month-input {
    width: 100%;
    padding: 5px 6px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 750;
    color: #0f172a;
    box-sizing: border-box;
}
</style>

{{-- Add Sub-meter Modal --}}
<div id="addLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10060;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(780px,100%);max-height:92vh;overflow-y:auto;background:#fff;border-radius:18px;box-shadow:0 20px 45px rgba(0,0,0,.25);padding:24px;position:relative;">
        <button type="button" onclick="closeAddLinkedSubmeterModal()" style="position:absolute;top:14px;right:16px;border:none;background:none;font-size:1.4rem;color:#64748b;cursor:pointer;">&times;</button>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <span style="width:36px;height:36px;border-radius:10px;background:#eff6ff;color:#2563eb;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="fa-solid fa-plus"></i></span>
            <div>
                <h3 style="margin:0;color:#0f172a;font-weight:800;font-size:1.2rem;">Add Sub-meter</h3>
                <div style="color:#64748b;font-size:0.84rem;font-weight:600;">
                    Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
                    @if($mainMeterBaseline > 0)
                        &bull; <span style="color:#2563eb;font-weight:800;">Main Baseline: {{ number_format($mainMeterBaseline, 2) }} kWh</span>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('modules.facilities.meters.store', $facility->id) }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px;">
            @csrf
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="add">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="meter_type" value="sub">
            <input type="hidden" name="parent_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">

            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Name <span style="color:#e11d48;">*</span></label>
                <input type="text" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="e.g. 2F Lighting">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
                <input type="text" name="meter_number" maxlength="255" value="{{ old('meter_number') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="e.g. SM-2026-001">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
                <input type="text" name="location" maxlength="255" value="{{ old('location') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="e.g. Panel 3">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
                <select name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier <span style="color:#e11d48;">*</span></label>
                <input type="number" name="multiplier" min="0.0001" max="999999" step="0.0001" value="{{ old('multiplier', '1') }}" required
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="1.0000">
            </div>

            {{-- Smart Sub-meter Baseline kWh Field with 3-Option Calculator --}}
            <div style="grid-column: 1 / -1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-sizing: border-box;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                    <div>
                        <label style="font-weight:800; color:#1e293b; margin:0; font-size:0.88rem;">
                            Sub-meter Baseline kWh
                        </label>
                        <span style="display:block; font-size:0.75rem; color:#64748b; font-weight:600;">Monthly target consumption for this sub-meter (portion of Main Meter).</span>
                    </div>
                    @if($mainMeterBaseline > 0)
                        <span style="font-size:0.76rem; font-weight:800; background:#eff6ff; color:#1d4ed8; padding:3px 9px; border-radius:8px; border:1px solid #bfdbfe;">
                            <i class="fa-solid fa-gauge-high"></i> Main Baseline: <strong>{{ number_format($mainMeterBaseline, 2) }} kWh</strong>
                        </span>
                    @endif
                </div>

                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="number" name="baseline_kwh" id="add_baseline_kwh" min="0" step="0.01" value="{{ old('baseline_kwh') }}"
                           oninput="validateSubmeterBaseline('add')"
                           style="flex:1; border:1px solid #cbd5e1; border-radius:10px; padding:10px 12px; font-size:1rem; font-weight:800; color:#0f172a; font-family:monospace; box-sizing:border-box;"
                           placeholder="e.g. 1200.00">
                    <button type="button" onclick="toggleSubmeterBaselineCalc('add')"
                            style="display:inline-flex; align-items:center; gap:6px; background:#2563eb; color:#fff; border:none; border-radius:10px; padding:10px 14px; font-weight:800; font-size:0.82rem; cursor:pointer; white-space:nowrap; box-shadow:0 2px 6px rgba(37,99,235,0.2);">
                        <i class="fa-solid fa-calculator"></i> <span id="add_calc_btn_text">Baseline Calculator</span>
                    </button>
                </div>

                <div id="add_baseline_warning" style="display:none; margin-top:8px; padding:8px 12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#b91c1c; font-size:0.78rem; font-weight:750;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Notice:</strong> The entered baseline (<span id="add_warn_val">0</span> kWh) meets or exceeds the Main Meter baseline ({{ number_format($mainMeterBaseline, 2) }} kWh). A sub-meter baseline should represent only a portion of the Main Meter.
                </div>

                {{-- Interactive 3-Tab Calculator Expandable Panel --}}
                <div id="add_baseline_calc_box" style="display:none; margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
                    <div style="display:flex; gap:4px; background:#e2e8f0; padding:3px; border-radius:10px; margin-bottom:12px;">
                        <button type="button" class="submeter-calc-tab-btn active" id="add_tab_btn_pct" onclick="switchSubmeterBaselineTab('add', 'pct')">
                            <i class="fa-solid fa-percent"></i> % Share of Main Meter
                        </button>
                        <button type="button" class="submeter-calc-tab-btn" id="add_tab_btn_bills" onclick="switchSubmeterBaselineTab('add', 'bills')">
                            <i class="fa-solid fa-clock-rotate-left"></i> 3–6 Months Bills
                        </button>
                        <button type="button" class="submeter-calc-tab-btn" id="add_tab_btn_equip" onclick="switchSubmeterBaselineTab('add', 'equip')">
                            <i class="fa-solid fa-plug-circle-bolt"></i> Equipment Load @if($equipTotalMonthlyKwh > 0)({{ number_format($equipTotalMonthlyKwh, 0) }} kWh)@endif
                        </button>
                    </div>

                    <!-- TAB 1: % Share of Main Meter -->
                    <div id="add_tab_content_pct" style="display:block;">
                        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:12px;">
                            <strong style="display:block; font-size:0.82rem; color:#0f172a; margin-bottom:6px;">
                                <i class="fa-solid fa-percent" style="color:#2563eb;"></i> Option A: Percentage (% Share) of Main Meter
                            </strong>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="number" id="add_calc_pct" min="1" max="100" step="1" placeholder="e.g. 20"
                                       oninput="previewCalcPct('add')"
                                       style="width:80px; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-weight:800; text-align:center; font-size:0.9rem;">
                                <span style="font-weight:800; color:#475569;">%</span>
                                <button type="button" onclick="applyCalcPct('add')"
                                        style="flex:1; background:#059669; color:#fff; border:none; border-radius:8px; padding:8px 14px; font-weight:800; font-size:0.8rem; cursor:pointer;">
                                    Apply to Baseline (<span id="add_calc_pct_val">0.00</span> kWh)
                                </button>
                            </div>
                            <div style="font-size:0.75rem; color:#64748b; margin-top:6px;">
                                Example: 20% of {{ number_format($mainMeterBaseline, 0) }} kWh Main Meter baseline.
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Past 3-6 Months Bills -->
                    <div id="add_tab_content_bills" style="display:none;">
                        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:12px;">
                            @php
                                $threeMoAvg = $historyReadings->count() >= 3 ? round($historyReadings->take(3)->avg('actual_kwh'), 2) : null;
                                $sixMoAvg = $historyReadings->count() >= 6 ? round($historyReadings->take(6)->avg('actual_kwh'), 2) : null;
                            @endphp

                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:8px 10px;">
                                <span style="font-size:0.75rem; font-weight:800; color:#334155;">
                                    <i class="fa-solid fa-calendar-days" style="color:#2563eb;"></i> Evaluation Period:
                                </span>
                                <div style="display:inline-flex; background:#e2e8f0; padding:2px; border-radius:8px;">
                                    <button type="button" class="submeter-dur-btn active" id="add_dur_btn_3" onclick="setSubmeterBaselineDuration('add', 3, {{ $threeMoAvg ?? 'null' }})">
                                        <i class="fa-solid fa-clock-rotate-left"></i> 3 Months
                                    </button>
                                    <button type="button" class="submeter-dur-btn" id="add_dur_btn_6" onclick="setSubmeterBaselineDuration('add', 6, {{ $sixMoAvg ?? 'null' }})">
                                        <i class="fa-solid fa-calendar-check"></i> 6 Months
                                    </button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:10px;" id="add_calc_months_grid">
                                @for($m = 1; $m <= 6; $m++)
                                    @php
                                        $histItem = $historyReadings->get($m - 1);
                                        $monthName = $histItem ? date('M Y', mktime(0, 0, 0, (int)$histItem->month, 1, (int)$histItem->year)) : "Month {$m}";
                                        $val = $histItem ? round((float)$histItem->actual_kwh, 2) : '';
                                    @endphp
                                    <div class="submeter-month-card" id="add_calc_card_m{{ $m }}" style="{{ $m > 3 ? 'display:none;' : 'display:block;' }}">
                                        <label style="display:block; font-size:0.68rem; font-weight:750; color:#64748b; margin-bottom:2px;">{{ $monthName }}</label>
                                        <input type="number" step="0.01" min="0" class="submeter-month-input" id="add_calc_m{{ $m }}" placeholder="0.00" value="{{ $val }}" oninput="recomputeSubmeterBillsCalc('add')">
                                    </div>
                                @endfor
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:0.78rem;">
                                <div>
                                    <span style="color:#64748b;">Average:</span> <strong id="add_calc_avg" style="color:#2563eb; font-size:0.9rem;">0.00 kWh</strong>
                                    <span style="color:#94a3b8; margin-left:6px;">(<span id="add_calc_count">0</span> months)</span>
                                </div>
                                <button type="button" onclick="applySubmeterBillsAvg('add')" style="background:#059669; color:#fff; border:none; border-radius:6px; padding:5px 10px; font-weight:800; font-size:0.75rem; cursor:pointer;">
                                    Apply Average
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Connected Equipment Load -->
                    <div id="add_tab_content_equip" style="display:none;">
                        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:12px;">
                            @if($equipTotalMonthlyKwh > 0)
                                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px; display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                    <div>
                                        <span style="font-size:0.68rem; color:#166534; font-weight:800; text-transform:uppercase;">Sub-meter Registered Equipment</span>
                                        <strong style="display:block; font-size:1.1rem; color:#047857; font-weight:900;">{{ number_format($equipTotalMonthlyKwh, 2) }} <small style="font-size:0.75rem;">kWh/mo</small></strong>
                                        <span style="font-size:0.7rem; color:#64748b;">{{ $equipTotalItems }} equipment types &bull; {{ number_format($equipTotalUnits) }} units</span>
                                    </div>
                                    <button type="button" onclick="quickApplySubmeterAvg('add', {{ $equipTotalMonthlyKwh }})" style="background:#059669; color:#fff; border:none; border-radius:8px; padding:8px 12px; font-weight:800; font-size:0.76rem; cursor:pointer;">
                                        <i class="fa-solid fa-plug-circle-bolt"></i> Apply Equipment Baseline
                                    </button>
                                </div>
                            @else
                                <div style="display:flex; align-items:center; gap:10px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:0.78rem; color:#0369a1;">
                                    <i class="fa-solid fa-circle-info" style="font-size:1.1rem; color:#0284c7;"></i>
                                    <div>
                                        <strong>No equipment assigned to this sub-meter yet.</strong>
                                        <div style="font-size:0.72rem; color:#0284c7; margin-top:2px;">Use the formula below to calculate estimated monthly consumption of connected devices.</div>
                                    </div>
                                </div>
                            @endif

                            <strong style="display:block; font-size:0.78rem; color:#0f172a; margin-bottom:6px;">
                                <i class="fa-solid fa-calculator" style="color:#d97706;"></i> Custom Connected Load Formula:
                            </strong>
                            <div style="display:flex; gap:4px; align-items:center; flex-wrap:wrap;">
                                <input type="number" id="add_calc_kw" step="0.1" min="0" placeholder="kW"
                                       oninput="previewCalcLoad('add')"
                                       style="width:65px; border:1px solid #cbd5e1; border-radius:6px; padding:6px; font-size:0.8rem;" title="Total kW">
                                <span style="font-size:0.74rem; color:#64748b;">kW ×</span>
                                <input type="number" id="add_calc_hrs" step="0.5" min="0" max="24" value="8" placeholder="hrs"
                                       oninput="previewCalcLoad('add')"
                                       style="width:50px; border:1px solid #cbd5e1; border-radius:6px; padding:6px; font-size:0.8rem;" title="Hours per day">
                                <span style="font-size:0.74rem; color:#64748b;">h/d ×</span>
                                <input type="number" id="add_calc_days" step="1" min="1" max="31" value="22" placeholder="days"
                                       oninput="previewCalcLoad('add')"
                                       style="width:50px; border:1px solid #cbd5e1; border-radius:6px; padding:6px; font-size:0.8rem;" title="Days per month">
                                <span style="font-size:0.74rem; color:#64748b;">d/mo</span>
                                <button type="button" onclick="applyCalcLoad('add')"
                                        style="background:#059669; color:#fff; border:none; border-radius:8px; padding:6px 12px; font-weight:800; font-size:0.76rem; cursor:pointer;">
                                    Apply (<span id="add_calc_load_val">0.00</span> kWh)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
                <textarea name="notes" rows="3" maxlength="2000"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;box-sizing:border-box;"
                          placeholder="Optional notes (e.g. connected area, purpose, feeder panel)">{{ old('notes') }}</textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" onclick="closeAddLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 16px;font-weight:700;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 18px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,0.25);">Save Sub-meter</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Sub-meter Modal --}}
<div id="editLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10061;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(780px,100%);max-height:92vh;overflow-y:auto;background:#fff;border-radius:18px;box-shadow:0 20px 45px rgba(0,0,0,.25);padding:24px;position:relative;">
        <button type="button" onclick="closeEditLinkedSubmeterModal()" style="position:absolute;top:14px;right:16px;border:none;background:none;font-size:1.4rem;color:#64748b;cursor:pointer;">&times;</button>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <span style="width:36px;height:36px;border-radius:10px;background:#eff6ff;color:#2563eb;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="fa-solid fa-pen-to-square"></i></span>
            <div>
                <h3 style="margin:0;color:#0f172a;font-weight:800;font-size:1.2rem;">Edit Sub-meter</h3>
                <div style="color:#64748b;font-size:0.84rem;font-weight:600;">
                    Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
                    @if($mainMeterBaseline > 0)
                        &bull; <span style="color:#2563eb;font-weight:800;">Main Baseline: {{ number_format($mainMeterBaseline, 2) }} kWh</span>
                    @endif
                </div>
            </div>
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
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px;">
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
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="e.g. 2F Lighting">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
                <input type="text" id="edit_meter_number" name="meter_number" maxlength="255" value="{{ old('meter_number') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="e.g. SM-2026-001">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
                <input type="text" id="edit_location" name="location" maxlength="255" value="{{ old('location') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="e.g. Panel 3">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
                <select id="edit_status" name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier <span style="color:#e11d48;">*</span></label>
                <input type="number" id="edit_multiplier" name="multiplier" min="0.0001" max="999999" step="0.0001" value="{{ old('multiplier', '1') }}" required
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;box-sizing:border-box;"
                       placeholder="1.0000">
            </div>

            {{-- Smart Sub-meter Baseline kWh Field with 3-Option Calculator --}}
            <div style="grid-column: 1 / -1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-sizing: border-box;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                    <div>
                        <label style="font-weight:800; color:#1e293b; margin:0; font-size:0.88rem;">
                            Sub-meter Baseline kWh
                        </label>
                        <span style="display:block; font-size:0.75rem; color:#64748b; font-weight:600;">Monthly target consumption for this sub-meter (portion of Main Meter).</span>
                    </div>
                    @if($mainMeterBaseline > 0)
                        <span style="font-size:0.76rem; font-weight:800; background:#eff6ff; color:#1d4ed8; padding:3px 9px; border-radius:8px; border:1px solid #bfdbfe;">
                            <i class="fa-solid fa-gauge-high"></i> Main Baseline: <strong>{{ number_format($mainMeterBaseline, 2) }} kWh</strong>
                        </span>
                    @endif
                </div>

                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="number" id="edit_baseline_kwh" name="baseline_kwh" min="0" step="0.01" value="{{ old('baseline_kwh') }}"
                           oninput="validateSubmeterBaseline('edit')"
                           style="flex:1; border:1px solid #cbd5e1; border-radius:10px; padding:10px 12px; font-size:1rem; font-weight:800; color:#0f172a; font-family:monospace; box-sizing:border-box;"
                           placeholder="e.g. 1200.00">
                    <button type="button" onclick="toggleSubmeterBaselineCalc('edit')"
                            style="display:inline-flex; align-items:center; gap:6px; background:#2563eb; color:#fff; border:none; border-radius:10px; padding:10px 14px; font-weight:800; font-size:0.82rem; cursor:pointer; white-space:nowrap; box-shadow:0 2px 6px rgba(37,99,235,0.2);">
                        <i class="fa-solid fa-calculator"></i> <span id="edit_calc_btn_text">Baseline Calculator</span>
                    </button>
                </div>

                <div id="edit_baseline_warning" style="display:none; margin-top:8px; padding:8px 12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#b91c1c; font-size:0.78rem; font-weight:750;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Notice:</strong> The entered baseline (<span id="edit_warn_val">0</span> kWh) meets or exceeds the Main Meter baseline ({{ number_format($mainMeterBaseline, 2) }} kWh). A sub-meter baseline should represent only a portion of the Main Meter.
                </div>

                {{-- Interactive 3-Tab Calculator Expandable Panel --}}
                <div id="edit_baseline_calc_box" style="display:none; margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
                    <div style="display:flex; gap:4px; background:#e2e8f0; padding:3px; border-radius:10px; margin-bottom:12px;">
                        <button type="button" class="submeter-calc-tab-btn active" id="edit_tab_btn_pct" onclick="switchSubmeterBaselineTab('edit', 'pct')">
                            <i class="fa-solid fa-percent"></i> % Share of Main Meter
                        </button>
                        <button type="button" class="submeter-calc-tab-btn" id="edit_tab_btn_bills" onclick="switchSubmeterBaselineTab('edit', 'bills')">
                            <i class="fa-solid fa-clock-rotate-left"></i> 3–6 Months Bills
                        </button>
                        <button type="button" class="submeter-calc-tab-btn" id="edit_tab_btn_equip" onclick="switchSubmeterBaselineTab('edit', 'equip')">
                            <i class="fa-solid fa-plug-circle-bolt"></i> Equipment Load @if($equipTotalMonthlyKwh > 0)({{ number_format($equipTotalMonthlyKwh, 0) }} kWh)@endif
                        </button>
                    </div>

                    <!-- TAB 1: % Share of Main Meter -->
                    <div id="edit_tab_content_pct" style="display:block;">
                        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:12px;">
                            <strong style="display:block; font-size:0.82rem; color:#0f172a; margin-bottom:6px;">
                                <i class="fa-solid fa-percent" style="color:#2563eb;"></i> Option A: Percentage (% Share) of Main Meter
                            </strong>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="number" id="edit_calc_pct" min="1" max="100" step="1" placeholder="e.g. 20"
                                       oninput="previewCalcPct('edit')"
                                       style="width:80px; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-weight:800; text-align:center; font-size:0.9rem;">
                                <span style="font-weight:800; color:#475569;">%</span>
                                <button type="button" onclick="applyCalcPct('edit')"
                                        style="flex:1; background:#059669; color:#fff; border:none; border-radius:8px; padding:8px 14px; font-weight:800; font-size:0.8rem; cursor:pointer;">
                                    Apply to Baseline (<span id="edit_calc_pct_val">0.00</span> kWh)
                                </button>
                            </div>
                            <div style="font-size:0.75rem; color:#64748b; margin-top:6px;">
                                Example: 20% of {{ number_format($mainMeterBaseline, 0) }} kWh Main Meter baseline.
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Past 3-6 Months Bills -->
                    <div id="edit_tab_content_bills" style="display:none;">
                        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:12px;">
                            @php
                                $threeMoAvg = $historyReadings->count() >= 3 ? round($historyReadings->take(3)->avg('actual_kwh'), 2) : null;
                                $sixMoAvg = $historyReadings->count() >= 6 ? round($historyReadings->take(6)->avg('actual_kwh'), 2) : null;
                            @endphp

                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:8px 10px;">
                                <span style="font-size:0.75rem; font-weight:800; color:#334155;">
                                    <i class="fa-solid fa-calendar-days" style="color:#2563eb;"></i> Evaluation Period:
                                </span>
                                <div style="display:inline-flex; background:#e2e8f0; padding:2px; border-radius:8px;">
                                    <button type="button" class="submeter-dur-btn active" id="edit_dur_btn_3" onclick="setSubmeterBaselineDuration('edit', 3, {{ $threeMoAvg ?? 'null' }})">
                                        <i class="fa-solid fa-clock-rotate-left"></i> 3 Months
                                    </button>
                                    <button type="button" class="submeter-dur-btn" id="edit_dur_btn_6" onclick="setSubmeterBaselineDuration('edit', 6, {{ $sixMoAvg ?? 'null' }})">
                                        <i class="fa-solid fa-calendar-check"></i> 6 Months
                                    </button>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:10px;" id="edit_calc_months_grid">
                                @for($m = 1; $m <= 6; $m++)
                                    @php
                                        $histItem = $historyReadings->get($m - 1);
                                        $monthName = $histItem ? date('M Y', mktime(0, 0, 0, (int)$histItem->month, 1, (int)$histItem->year)) : "Month {$m}";
                                        $val = $histItem ? round((float)$histItem->actual_kwh, 2) : '';
                                    @endphp
                                    <div class="submeter-month-card" id="edit_calc_card_m{{ $m }}" style="{{ $m > 3 ? 'display:none;' : 'display:block;' }}">
                                        <label style="display:block; font-size:0.68rem; font-weight:750; color:#64748b; margin-bottom:2px;">{{ $monthName }}</label>
                                        <input type="number" step="0.01" min="0" class="submeter-month-input" id="edit_calc_m{{ $m }}" placeholder="0.00" value="{{ $val }}" oninput="recomputeSubmeterBillsCalc('edit')">
                                    </div>
                                @endfor
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:0.78rem;">
                                <div>
                                    <span style="color:#64748b;">Average:</span> <strong id="edit_calc_avg" style="color:#2563eb; font-size:0.9rem;">0.00 kWh</strong>
                                    <span style="color:#94a3b8; margin-left:6px;">(<span id="edit_calc_count">0</span> months)</span>
                                </div>
                                <button type="button" onclick="applySubmeterBillsAvg('edit')" style="background:#059669; color:#fff; border:none; border-radius:6px; padding:5px 10px; font-weight:800; font-size:0.75rem; cursor:pointer;">
                                    Apply Average
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Connected Equipment Load -->
                    <div id="edit_tab_content_equip" style="display:none;">
                        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:12px;">
                            @if($equipTotalMonthlyKwh > 0)
                                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px; display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                    <div>
                                        <span style="font-size:0.68rem; color:#166534; font-weight:800; text-transform:uppercase;">Sub-meter Registered Equipment</span>
                                        <strong style="display:block; font-size:1.1rem; color:#047857; font-weight:900;">{{ number_format($equipTotalMonthlyKwh, 2) }} <small style="font-size:0.75rem;">kWh/mo</small></strong>
                                        <span style="font-size:0.7rem; color:#64748b;">{{ $equipTotalItems }} equipment types &bull; {{ number_format($equipTotalUnits) }} units</span>
                                    </div>
                                    <button type="button" onclick="quickApplySubmeterAvg('edit', {{ $equipTotalMonthlyKwh }})" style="background:#059669; color:#fff; border:none; border-radius:8px; padding:8px 12px; font-weight:800; font-size:0.76rem; cursor:pointer;">
                                        <i class="fa-solid fa-plug-circle-bolt"></i> Apply Equipment Baseline
                                    </button>
                                </div>
                            @else
                                <div style="display:flex; align-items:center; gap:10px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:0.78rem; color:#0369a1;">
                                    <i class="fa-solid fa-circle-info" style="font-size:1.1rem; color:#0284c7;"></i>
                                    <div>
                                        <strong>No equipment assigned to this sub-meter yet.</strong>
                                        <div style="font-size:0.72rem; color:#0284c7; margin-top:2px;">Use the formula below to calculate estimated monthly consumption of connected devices.</div>
                                    </div>
                                </div>
                            @endif

                            <strong style="display:block; font-size:0.78rem; color:#0f172a; margin-bottom:6px;">
                                <i class="fa-solid fa-calculator" style="color:#d97706;"></i> Custom Connected Load Formula:
                            </strong>
                            <div style="display:flex; gap:4px; align-items:center; flex-wrap:wrap;">
                                <input type="number" id="edit_calc_kw" step="0.1" min="0" placeholder="kW"
                                       oninput="previewCalcLoad('edit')"
                                       style="width:65px; border:1px solid #cbd5e1; border-radius:6px; padding:6px; font-size:0.8rem;" title="Total kW">
                                <span style="font-size:0.74rem; color:#64748b;">kW ×</span>
                                <input type="number" id="edit_calc_hrs" step="0.5" min="0" max="24" value="8" placeholder="hrs"
                                       oninput="previewCalcLoad('edit')"
                                       style="width:50px; border:1px solid #cbd5e1; border-radius:6px; padding:6px; font-size:0.8rem;" title="Hours per day">
                                <span style="font-size:0.74rem; color:#64748b;">h/d ×</span>
                                <input type="number" id="edit_calc_days" step="1" min="1" max="31" value="22" placeholder="days"
                                       oninput="previewCalcLoad('edit')"
                                       style="width:50px; border:1px solid #cbd5e1; border-radius:6px; padding:6px; font-size:0.8rem;" title="Days per month">
                                <span style="font-size:0.74rem; color:#64748b;">d/mo</span>
                                <button type="button" onclick="applyCalcLoad('edit')"
                                        style="background:#059669; color:#fff; border:none; border-radius:8px; padding:6px 12px; font-weight:800; font-size:0.76rem; cursor:pointer;">
                                    Apply (<span id="edit_calc_load_val">0.00</span> kWh)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
                <textarea id="edit_notes" name="notes" rows="3" maxlength="2000"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;box-sizing:border-box;"
                          placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" onclick="closeEditLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 16px;font-weight:700;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 18px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,0.25);">Update Sub-meter</button>
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
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;box-sizing:border-box;"
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
const mainMeterBaselineKwh = {{ $mainMeterBaseline }};
window.submeterBaselineDuration = window.submeterBaselineDuration || { add: 3, edit: 3 };

function toggleSubmeterBaselineCalc(prefix) {
    const box = document.getElementById(prefix + '_baseline_calc_box');
    const text = document.getElementById(prefix + '_calc_btn_text');
    if (!box) return;
    const isHidden = box.style.display === 'none';
    box.style.display = isHidden ? 'block' : 'none';
    if (text) {
        text.textContent = isHidden ? 'Close Calculator' : 'Baseline Calculator';
    }
    if (isHidden) {
        previewCalcPct(prefix);
        previewCalcLoad(prefix);
        setSubmeterBaselineDuration(prefix, window.submeterBaselineDuration[prefix] || 3);
    }
}

function switchSubmeterBaselineTab(prefix, tab) {
    const tabs = ['pct', 'bills', 'equip'];
    tabs.forEach(t => {
        const btn = document.getElementById(prefix + '_tab_btn_' + t);
        const content = document.getElementById(prefix + '_tab_content_' + t);
        if (btn) btn.classList.toggle('active', t === tab);
        if (content) content.style.display = (t === tab) ? 'block' : 'none';
    });
}

function setSubmeterBaselineDuration(prefix, duration, maybeValue) {
    window.submeterBaselineDuration[prefix] = duration;

    // Show/hide month cards: if 3, show 1 to 3, hide 4 to 6; if 6, show 1 to 6
    for (let m = 1; m <= 6; m++) {
        const card = document.getElementById(prefix + '_calc_card_m' + m);
        if (card) {
            card.style.display = (m <= duration) ? 'block' : 'none';
        }
    }

    // Toggle active button styling
    const btn3 = document.getElementById(prefix + '_dur_btn_3');
    const btn6 = document.getElementById(prefix + '_dur_btn_6');
    if (btn3 && btn6) {
        btn3.classList.toggle('active', duration === 3);
        btn6.classList.toggle('active', duration === 6);
    }

    // Recompute summary for visible cards
    recomputeSubmeterBillsCalc(prefix);

    // If a value is provided, apply to baseline input
    if (maybeValue !== undefined && maybeValue !== null && maybeValue > 0) {
        quickApplySubmeterAvg(prefix, maybeValue);
    }
}

function validateSubmeterBaseline(prefix) {
    const input = document.getElementById(prefix === 'add' ? 'add_baseline_kwh' : 'edit_baseline_kwh');
    const warnBox = document.getElementById(prefix + '_baseline_warning');
    const warnVal = document.getElementById(prefix + '_warn_val');
    if (!input || !warnBox) return;

    const val = parseFloat(input.value || '0');
    if (mainMeterBaselineKwh > 0 && val >= mainMeterBaselineKwh && val > 0) {
        if (warnVal) warnVal.textContent = val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        warnBox.style.display = 'block';
    } else {
        warnBox.style.display = 'none';
    }
}

function previewCalcPct(prefix) {
    const pctInput = document.getElementById(prefix + '_calc_pct');
    const previewSpan = document.getElementById(prefix + '_calc_pct_val');
    if (!pctInput || !previewSpan) return;

    const pct = parseFloat(pctInput.value || '0');
    const calculated = (mainMeterBaselineKwh > 0 && pct > 0) ? (mainMeterBaselineKwh * (pct / 100)) : 0;
    previewSpan.textContent = calculated.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function applyCalcPct(prefix) {
    const pctInput = document.getElementById(prefix + '_calc_pct');
    const baseInput = document.getElementById(prefix === 'add' ? 'add_baseline_kwh' : 'edit_baseline_kwh');
    if (!pctInput || !baseInput) return;

    const pct = parseFloat(pctInput.value || '0');
    if (pct > 0 && mainMeterBaselineKwh > 0) {
        const calculated = mainMeterBaselineKwh * (pct / 100);
        baseInput.value = calculated.toFixed(2);
        validateSubmeterBaseline(prefix);
    }
}

function recomputeSubmeterBillsCalc(prefix) {
    const maxMonths = (window.submeterBaselineDuration && window.submeterBaselineDuration[prefix]) ? window.submeterBaselineDuration[prefix] : 3;
    let count = 0;
    let sum = 0;
    for (let m = 1; m <= maxMonths; m++) {
        const inp = document.getElementById(prefix + '_calc_m' + m);
        if (inp) {
            const val = parseFloat(inp.value || '0');
            if (!isNaN(val) && val > 0) {
                count++;
                sum += val;
            }
        }
    }
    const avg = count > 0 ? (sum / count) : 0;
    const countSpan = document.getElementById(prefix + '_calc_count');
    const avgSpan = document.getElementById(prefix + '_calc_avg');
    if (countSpan) countSpan.textContent = count;
    if (avgSpan) avgSpan.textContent = avg.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kWh';
}

function applySubmeterBillsAvg(prefix) {
    const maxMonths = (window.submeterBaselineDuration && window.submeterBaselineDuration[prefix]) ? window.submeterBaselineDuration[prefix] : 3;
    let count = 0;
    let sum = 0;
    for (let m = 1; m <= maxMonths; m++) {
        const inp = document.getElementById(prefix + '_calc_m' + m);
        if (inp) {
            const val = parseFloat(inp.value || '0');
            if (!isNaN(val) && val > 0) {
                count++;
                sum += val;
            }
        }
    }
    if (count > 0) {
        const avg = sum / count;
        const baseInput = document.getElementById(prefix === 'add' ? 'add_baseline_kwh' : 'edit_baseline_kwh');
        if (baseInput) {
            baseInput.value = avg.toFixed(2);
            validateSubmeterBaseline(prefix);
        }
    }
}

function quickApplySubmeterAvg(prefix, val) {
    const baseInput = document.getElementById(prefix === 'add' ? 'add_baseline_kwh' : 'edit_baseline_kwh');
    if (baseInput && val > 0) {
        baseInput.value = parseFloat(val).toFixed(2);
        validateSubmeterBaseline(prefix);
    }
}

function previewCalcLoad(prefix) {
    const kw = parseFloat(document.getElementById(prefix + '_calc_kw')?.value || '0');
    const hrs = parseFloat(document.getElementById(prefix + '_calc_hrs')?.value || '8');
    const days = parseFloat(document.getElementById(prefix + '_calc_days')?.value || '22');
    const previewSpan = document.getElementById(prefix + '_calc_load_val');
    if (!previewSpan) return;

    const monthlyKwh = (kw > 0 && hrs > 0 && days > 0) ? (kw * hrs * days) : 0;
    previewSpan.textContent = monthlyKwh.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function applyCalcLoad(prefix) {
    const kw = parseFloat(document.getElementById(prefix + '_calc_kw')?.value || '0');
    const hrs = parseFloat(document.getElementById(prefix + '_calc_hrs')?.value || '8');
    const days = parseFloat(document.getElementById(prefix + '_calc_days')?.value || '22');
    const baseInput = document.getElementById(prefix === 'add' ? 'add_baseline_kwh' : 'edit_baseline_kwh');
    if (!baseInput) return;

    if (kw > 0 && hrs > 0 && days > 0) {
        const monthlyKwh = kw * hrs * days;
        baseInput.value = monthlyKwh.toFixed(2);
        validateSubmeterBaseline(prefix);
    }
}

function openAddLinkedSubmeterModal() {
    const modal = document.getElementById('addLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'flex';
    validateSubmeterBaseline('add');
    setSubmeterBaselineDuration('add', 3);
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

    validateSubmeterBaseline('edit');
    setSubmeterBaselineDuration('edit', 3);
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
