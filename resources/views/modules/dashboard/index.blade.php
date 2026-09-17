@extends('layouts.qc-admin')
@section('title', 'Dashboard Overview')

@section('content')
<style>
    /* --- Shared Dashboard UI Aesthetic --- */
    .report-card-container {
        background: #fff; 
        border-radius: 18px; 
        box-shadow: 0 2px 12px rgba(31,38,135,0.06); 
        padding: 30px;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
    }
    .report-card-container,
    .report-card-container * {
        box-sizing: border-box;
    }

    .stat-card {
        flex: 1;
        min-width: 200px;
        padding: 24px;
        border-radius: 16px;
        transition: transform 0.2s ease;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .chart-container {
        background: #ffffff;
        padding: 24px;
        border-radius: 18px;
        border: 1px solid #f1f5f9;
        height: 100%;
    }
    .chart-canvas-wrap {
        height: 320px;
    }

    .stats-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 18px;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        min-width: 0;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 0; /* Let the header/body handle padding */
    }

    .quick-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.86rem;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        background: #fff;
        transition: all 0.16s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(30,41,59,0.08);
        border-color: #cbd5e1;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table thead th {
        padding: 16px;
        color: #3762c8;
        font-weight: 700;
        text-align: left;
        background: #f8fafc;
        border-bottom: 2px solid #e9effc;
    }

    .custom-table tbody tr td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #475569;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    .custom-table tbody tr:hover {
        background: #fcfdfe;
    }

    .insights-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(360px, .85fr);
        gap: 16px;
    }

    .insight-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
    }

    .insight-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }

    .insight-card-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.2px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .insight-card-title.consumption {
        color: #0f172a;
    }

    .insight-card-title.critical {
        color: #e11d48;
    }

    .insight-card-title i {
        font-size: 0.82rem;
    }

    .insight-card-meta { margin-top:4px; color:#94a3b8; font-size:.68rem; font-weight:700; }
    .insight-header-action { color:#2563eb; font-size:.7rem; font-weight:800; text-decoration:none; white-space:nowrap; }
    .insight-header-action:hover { color:#1d4ed8; text-decoration:underline; }

    .consumption-table { table-layout:fixed; }
    .consumption-table th:nth-child(1) { width:24%; }
    .consumption-table th:nth-child(2),
    .consumption-table th:nth-child(3) { width:16%; }
    .consumption-table th:nth-child(4) { width:13%; }
    .consumption-table th:nth-child(5) { width:15%; }
    .consumption-table th:nth-child(6) { width:16%; }

    .consumption-table th {
        padding:12px 9px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #355dc2;
        background: #f2f5fb;
    }

    .consumption-table td {
        padding:13px 9px !important;
        font-size: 0.74rem;
    }

    .consumption-table td.facility-name {
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1.25;
        color: #1e293b;
    }

    .facility-rank { display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; margin-right:6px; border-radius:7px; background:#eff6ff; color:#2563eb; font-size:.62rem; font-weight:900; vertical-align:middle; }
    .facility-name-text { vertical-align:middle; }
    .trend-indicator { display:inline-flex; align-items:center; justify-content:center; gap:4px; padding:4px 7px; border-radius:999px; font-size:.61rem; font-weight:850; white-space:nowrap; }
    .trend-indicator.spike { color:#b91c1c; background:#fef2f2; border:1px solid #fecaca; }
    .trend-indicator.stable { color:#64748b; background:#f8fafc; border:1px solid #e2e8f0; }

    .consumption-table .value-kwh {
        color: #0f172a;
        font-weight: 700;
    }

    .consumption-table .value-baseline {
        color: #64748b;
        font-weight: 700;
    }

    .consumption-table .value-deviation {
        font-weight: 700;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .notifications-body {
        padding: 14px 16px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .alert-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #f8fafc;
        border-left: 4px solid #cbd5e1;
        border-radius: 12px;
        padding: 13px 14px 13px 12px;
        color: #334155;
        font-size: 0.76rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .alert-item.critical {
        background: #fff1f2;
        border-left-color: #e11d48;
        color: #9f1239;
    }

    .alert-item.very-high {
        background: #fff1f2;
        border-left-color: #f43f5e;
        color: #9f1239;
    }

    .alert-item.high {
        background: #fff7ed;
        border-left-color: #ea580c;
        color: #9a3412;
    }

    .alert-item.warning {
        background: #fffbeb;
        border-left-color: #d97706;
        color: #92400e;
    }

    .alert-icon {
        width:28px; height:28px; flex:0 0 28px; display:inline-flex; align-items:center; justify-content:center;
        border-radius:9px; background:rgba(255,255,255,.72); font-size:.72rem; line-height:1; margin-top:0;
    }

    .alert-level {
        display: block;
        font-size: 0.72rem;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        opacity: 0.9;
        margin-bottom: 2px;
    }

    .insight-card-footer { padding:11px 16px; border-top:1px solid #eef2f7; background:#fbfdff; text-align:right; }
    .insight-footer-link { display:inline-flex; align-items:center; gap:6px; color:#2563eb; font-size:.7rem; font-weight:850; text-decoration:none; }

    /* Dashboard overview hierarchy */
    .dashboard-header {
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        align-items:start;
        gap:20px;
        margin-bottom:20px;
    }
    .dashboard-title-row { display:flex; align-items:flex-start; gap:13px; }
    .dashboard-title-icon {
        width:48px; height:48px; flex:0 0 48px; display:inline-flex; align-items:center; justify-content:center;
        border-radius:14px; color:#ea580c; background:#fff7ed; border:1px solid #fed7aa; font-size:1.2rem;
    }
    .dashboard-title { margin:0; color:#0f2450; font-size:clamp(1.55rem,2.2vw,2rem); font-weight:850; line-height:1.1; letter-spacing:-.035em; }
    .dashboard-subtitle { margin:6px 0 0; color:#64748b; font-size:.95rem; }
    .dashboard-role-badge {
        display:inline-flex; align-items:center; gap:7px; padding:9px 13px; border-radius:11px;
        background:#eef2ff; color:#4f46e5; border:1px solid #dbe3ff; font-size:.72rem; font-weight:850;
        text-transform:uppercase; letter-spacing:.04em;
    }
    .dashboard-filter-panel {
        display:flex; align-items:end; justify-content:space-between; gap:16px; flex-wrap:wrap;
        margin-bottom:22px; padding:14px 16px; border:1px solid #dbe5f2; border-radius:14px; background:#f8fafc;
    }
    .dashboard-period-summary { display:flex; align-items:center; gap:9px; color:#64748b; font-size:.78rem; }
    .dashboard-period-summary i { color:#2563eb; }
    .dashboard-period-form { display:flex; align-items:end; gap:8px; flex-wrap:wrap; }
    .dashboard-filter-field { display:flex; flex-direction:column; gap:5px; }
    .dashboard-filter-field label { color:#64748b; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
    .dashboard-filter-field input {
        min-height:38px; border:1px solid #cbd5e1; border-radius:9px; padding:6px 10px;
        color:#334155; background:#fff; font:inherit; font-size:.78rem;
    }
    .dashboard-filter-field input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .dashboard-filter-button {
        min-height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:9px;
        padding:7px 13px; font-size:.75rem; font-weight:800; text-decoration:none; cursor:pointer;
    }
    .dashboard-filter-button.primary { border:1px solid #1d4ed8; background:#2563eb; color:#fff; }
    .dashboard-filter-button.secondary { border:1px solid #cbd5e1; background:#fff; color:#334155; }

    .dashboard-page .stats-grid {
        display:grid;
        grid-template-columns:repeat(5,minmax(0,1fr));
        gap:14px;
        margin-bottom:22px;
    }
    .dashboard-page .stat-card {
        position:relative; min-width:0; min-height:142px; padding:18px; overflow:hidden;
        border:1px solid #dbe5f2; border-top:4px solid var(--kpi-accent); border-radius:15px;
        background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.045);
    }
    .dashboard-page .stat-card::after {
        content:""; position:absolute; width:86px; height:86px; top:-38px; right:-32px; border-radius:50%;
        background:var(--kpi-soft); opacity:.9;
    }
    .dashboard-page .stat-card.kpi-blue { --kpi-accent:#2563eb; --kpi-soft:#dbeafe; }
    .dashboard-page .stat-card.kpi-green { --kpi-accent:#16a34a; --kpi-soft:#dcfce7; }
    .dashboard-page .stat-card.kpi-amber { --kpi-accent:#d97706; --kpi-soft:#fef3c7; }
    .dashboard-page .stat-card.kpi-red { --kpi-accent:#e11d48; --kpi-soft:#ffe4e6; }
    .dashboard-page .stat-card.kpi-violet { --kpi-accent:#7c3aed; --kpi-soft:#ede9fe; }
    .dashboard-kpi-heading { display:flex; align-items:center; gap:7px; margin-bottom:14px; color:var(--kpi-accent); font-size:.67rem; font-weight:850; text-transform:uppercase; letter-spacing:.045em; }
    .dashboard-kpi-value { color:#0f172a; font-size:1.75rem; font-weight:850; line-height:1; letter-spacing:-.035em; white-space:nowrap; }
    .dashboard-kpi-value small { font-size:.72rem; letter-spacing:0; }
    .dashboard-kpi-note { margin-top:9px; color:#64748b; font-size:.68rem; font-weight:700; }
    .dashboard-kpi-note.is-up { color:#be123c; }
    .dashboard-kpi-note.is-down { color:#15803d; }

    .dashboard-page .summary-grid {
        display: grid;
        grid-template-columns: minmax(280px, 1fr) minmax(420px, 1.45fr);
        gap: 16px;
        margin-bottom: 22px;
        align-items: stretch;
    }
    @media (max-width: 992px) {
        .dashboard-page .summary-grid { grid-template-columns: 1fr; }
    }
    .dashboard-page .summary-card {
        padding: 16px 18px;
        border-color: #dbe5f2;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .summary-card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .summary-card-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0;
        color: #334155;
        font-size: 0.75rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.045em;
    }
    .snapshot-total-badge, .quick-actions-subtitle {
        font-size: 0.68rem;
        font-weight: 750;
        color: #64748b;
        background: #f1f5f9;
        padding: 3px 9px;
        border-radius: 6px;
        white-space: nowrap;
    }
    .operational-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .operational-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 11px;
        border: 1px solid;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 750;
    }
    .operational-pill .pill-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }
    .operational-pill.active { color: #166534; background: #ecfdf5; border-color: #bbf7d0; }
    .operational-pill.active .pill-dot { background: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,0.2); }
    .operational-pill.maintenance { color: #92400e; background: #fffbeb; border-color: #fde68a; }
    .operational-pill.maintenance .pill-dot { background: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,0.2); }
    .operational-pill.inactive { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
    .operational-pill.inactive .pill-dot { background: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,0.2); }

    .operational-status-bar {
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        display: flex;
        margin-bottom: 8px;
    }
    .operational-status-bar .bar-segment { height: 100%; transition: width 0.3s ease; }
    .operational-status-bar .seg-active { background: #22c55e; }
    .operational-status-bar .seg-maintenance { background: #f59e0b; }
    .operational-status-bar .seg-inactive { background: #ef4444; }

    .operational-status-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 650;
        color: #64748b;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    @media (max-width: 640px) {
        .quick-actions-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .dashboard-page .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 7px 10px;
        border-radius: 11px;
        font-weight: 750;
        font-size: 0.74rem;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .dashboard-page .quick-action-btn:hover {
        transform: translateY(-2px);
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }
    .qa-icon-wrap {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
        transition: transform 0.18s ease;
    }
    .dashboard-page .quick-action-btn:hover .qa-icon-wrap {
        transform: scale(1.12);
    }
    .qa-label {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .qa-badge {
        font-size: 0.62rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 999px;
        line-height: 1;
        flex-shrink: 0;
    }
    .qa-badge-danger { background: #fee2e2; color: #b91c1c; }
    .qa-badge-warning { background: #fef3c7; color: #b45309; }
    .qa-badge-violet { background: #ede9fe; color: #6d28d9; }

    /* Module specific accent styling */
    .qa-ai .qa-icon-wrap { background: #eef2ff; color: #6366f1; }
    .qa-ai:hover { border-color: #a5b4fc !important; }

    .qa-load .qa-icon-wrap { background: #e0f2fe; color: #0284c7; }
    .qa-load:hover { border-color: #7dd3fc !important; }

    .qa-facilities .qa-icon-wrap { background: #ecfdf5; color: #059669; }
    .qa-facilities:hover { border-color: #6ee7b7 !important; }

    .qa-meter .qa-icon-wrap { background: #fef3c7; color: #d97706; }
    .qa-meter:hover { border-color: #fcd34d !important; }

    .qa-incidents .qa-icon-wrap { background: #ffe4e6; color: #e11d48; }
    .qa-incidents:hover { border-color: #fda4af !important; }

    .qa-maintenance .qa-icon-wrap { background: #ede9fe; color: #7c3aed; }
    .qa-maintenance:hover { border-color: #c4b5fd !important; }

    .dashboard-page .chart-grid { display:grid !important; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px !important; margin-bottom:22px !important; }
    .dashboard-page .chart-container { padding:18px; border-color:#dbe5f2; box-shadow:0 7px 20px rgba(15,23,42,.035); }
    .dashboard-chart-title { display:flex; align-items:center; gap:9px; margin:0 0 15px; color:#334155; font-size:.9rem; font-weight:850; }
    .chart-insights-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:10px; margin-bottom:14px; }
    .chart-insight-card {
        min-width:0; padding:11px 12px; border:1px solid #dbe5f2; border-radius:12px; background:#f8fafc; transition:transform .16s ease,box-shadow .16s ease;
    }
    .chart-insight-card:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(15,23,42,.04); border-color:#cbd5e1; }
    .chart-insight-card.sustainability { border-color:#bbf7d0; background:linear-gradient(180deg,#ffffff 0%,#f0fdf4 100%); }
    .chart-insight-card.gemp { border-color:#fed7aa; background:linear-gradient(180deg,#ffffff 0%,#fff7ed 100%); }
    .chart-insight-label { display:flex; align-items:center; gap:6px; color:#64748b; font-size:.62rem; font-weight:850; text-transform:uppercase; letter-spacing:.04em; }
    .chart-insight-label i { color:#2563eb; }
    .chart-insight-card.sustainability .chart-insight-label i { color:#16a34a; }
    .chart-insight-value { margin-top:5px; color:#0f172a; font-size:.92rem; font-weight:850; line-height:1.2; display:flex; align-items:baseline; gap:6px; flex-wrap:wrap; }
    .chart-insight-value.negative { color:#15803d; }
    .chart-insight-value.positive { color:#be123c; }
    .chart-insight-value.tone-good { color:#16a34a; }
    .chart-insight-value.tone-warn { color:#d97706; }
    .chart-insight-value.tone-danger { color:#dc2626; }
    .gemp-badge { display:inline-block; font-size:.68rem; font-weight:750; padding:2px 6px; border-radius:6px; background:#fee2e2; color:#b91c1c; white-space:nowrap; }
    .gemp-badge.good { background:#dcfce7; color:#15803d; }
    .gemp-badge.warn { background:#fef3c7; color:#b45309; }
    .chart-insight-note { margin-top:4px; color:#94a3b8; font-size:.61rem; font-weight:650; }
    .chart-card-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:8px; }
    .chart-card-subtitle { margin:-8px 0 14px 17px; color:#94a3b8; font-size:.65rem; font-weight:650; }
    .chart-footnote { display:flex; align-items:flex-start; gap:7px; margin-top:10px; padding:8px 10px; border-radius:9px; background:#fffbeb; color:#92400e; font-size:.65rem; font-weight:700; }

    body.dark-mode .dashboard-page .chart-insight-card { background:#111827; border-color:#334155; }
    body.dark-mode .dashboard-page .chart-insight-card.sustainability { background:linear-gradient(180deg,#111827 0%,#052e16 100%); border-color:#166534; }
    body.dark-mode .dashboard-page .chart-insight-card.gemp { background:linear-gradient(180deg,#111827 0%,#431407 100%); border-color:#9a3412; }
    body.dark-mode .dashboard-page .chart-insight-value { color:#f8fafc; }
    body.dark-mode .dashboard-page .gemp-badge { background:#4c0519 !important; border:1px solid #be123c !important; color:#fda4af !important; }
    body.dark-mode .dashboard-page .gemp-badge.good { background:#052e16 !important; border:1px solid #166534 !important; color:#86efac !important; }
    body.dark-mode .dashboard-page .gemp-badge.warn { background:#451a03 !important; border:1px solid #9a3412 !important; color:#fdba74 !important; }
    body.dark-mode .dashboard-page .chart-footnote { background:rgba(245,158,11,.12); color:#fde68a; }

    /* Dashboard Dark Mode */
    body.dark-mode .dashboard-page .report-card-container {
        background: #0f172a !important;
        border: 1px solid #1f2937;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.42);
        color: #e5e7eb;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .stat-card,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .summary-card,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .chart-container,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-card {
        background: #0f172a !important;
        border-color: #1e293b !important;
        color: #e2e8f0 !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-card-header {
        background: #0f172a !important;
        border-bottom-color: #1e293b !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-card-meta {
        color: #94a3b8 !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .stat-card.kpi-blue { --kpi-accent:#3b82f6; --kpi-soft:rgba(59,130,246,0.18); }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .stat-card.kpi-green { --kpi-accent:#22c55e; --kpi-soft:rgba(34,197,94,0.18); }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .stat-card.kpi-amber { --kpi-accent:#f59e0b; --kpi-soft:rgba(245,158,11,0.18); }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .stat-card.kpi-red { --kpi-accent:#f43f5e; --kpi-soft:rgba(244,63,94,0.18); }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .stat-card.kpi-violet { --kpi-accent:#a855f7; --kpi-soft:rgba(168,85,247,0.18); }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-role-badge {
        background: #172554 !important;
        border-color: #1e40af !important;
        color: #bfdbfe !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .custom-table thead th {
        background: #111827 !important;
        color: #93c5fd !important;
        border-bottom-color: #334155 !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .custom-table tbody tr td {
        border-bottom-color: #1f2937 !important;
        color: #cbd5e1 !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .custom-table tbody tr:hover {
        background: #1f2937 !important;
    }

    /* High Consumption Hubs Table */
    :is(html.dark-mode, body.dark-mode) .dashboard-page .consumption-table th {
        background: #0b1220 !important;
        color: #93c5fd !important;
        border-bottom-color: #1e293b !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .consumption-table td.facility-name,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .consumption-table .value-kwh {
        color: #f8fafc !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .consumption-table .value-baseline {
        color: #94a3b8 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .facility-rank {
        background: #172554 !important;
        color: #93c5fd !important;
        border: 1px solid #1e40af !important;
    }

    /* Condition Status Pills */
    :is(html.dark-mode, body.dark-mode) .dashboard-page .status-pill.status-critical {
        background: #4c0519 !important;
        color: #fda4af !important;
        border-color: #9f1239 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .status-pill.status-very-high {
        background: #4c0519 !important;
        color: #fb7185 !important;
        border-color: #be123c !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .status-pill.status-high {
        background: #431407 !important;
        color: #fdba74 !important;
        border-color: #c2410c !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .status-pill.status-warning {
        background: #451a03 !important;
        color: #fde68a !important;
        border-color: #9a3412 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .status-pill.status-normal {
        background: #052e16 !important;
        color: #86efac !important;
        border-color: #166534 !important;
    }

    /* Trend Indicators */
    :is(html.dark-mode, body.dark-mode) .dashboard-page .trend-indicator.stable {
        background: #0b1220 !important;
        border-color: #1e293b !important;
        color: #94a3b8 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .trend-indicator.spike {
        background: #4c0519 !important;
        border-color: #9f1239 !important;
        color: #fda4af !important;
    }

    /* Critical Notifications List */
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item {
        background: #111827 !important;
        border: 1px solid #1e293b !important;
        border-left-width: 4px !important;
        color: #cbd5e1 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.critical {
        background: #1c0f16 !important;
        border-color: #4c0519 !important;
        border-left-color: #f43f5e !important;
        color: #fecdd3 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.very-high {
        background: #1c0f16 !important;
        border-color: #4c0519 !important;
        border-left-color: #fb7185 !important;
        color: #fecdd3 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.high {
        background: #1c150c !important;
        border-color: #431407 !important;
        border-left-color: #f97316 !important;
        color: #fed7aa !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.warning {
        background: #1c170c !important;
        border-color: #451a03 !important;
        border-left-color: #eab308 !important;
        color: #fef08a !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item .alert-level {
        color: #f8fafc !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-icon {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.critical .alert-icon {
        background: rgba(225, 29, 72, 0.22) !important;
        color: #fb7185 !important;
        border-color: rgba(225, 29, 72, 0.4) !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.very-high .alert-icon {
        background: rgba(244, 63, 94, 0.22) !important;
        color: #fb7185 !important;
        border-color: rgba(244, 63, 94, 0.4) !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.high .alert-icon {
        background: rgba(234, 88, 12, 0.22) !important;
        color: #fdba74 !important;
        border-color: rgba(234, 88, 12, 0.4) !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .alert-item.warning .alert-icon {
        background: rgba(217, 119, 6, 0.22) !important;
        color: #fde68a !important;
        border-color: rgba(217, 119, 6, 0.4) !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-card-footer {
        background: #0b1220 !important;
        border-top-color: #1e293b !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-header-action,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-footer-link {
        color: #60a5fa !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-header-action:hover,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-footer-link:hover {
        color: #93c5fd !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .quick-action-btn {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .quick-action-btn:hover {
        background: #1e293b !important;
        border-color: #475569 !important;
        box-shadow: 0 8px 18px rgba(2, 6, 23, 0.5) !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-badge-danger {
        background: #4c0519 !important;
        color: #fda4af !important;
        border: 1px solid #be123c !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-badge-warning {
        background: #451a03 !important;
        color: #fdba74 !important;
        border: 1px solid #9a3412 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-badge-violet {
        background: #2e1065 !important;
        color: #d8b4fe !important;
        border: 1px solid #7e22ce !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .summary-card-heading {
        color: #f1f5f9;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .snapshot-total-badge,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .quick-actions-subtitle {
        background: #1e293b;
        border: 1px solid #334155;
        color: #94a3b8;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .operational-pill.active {
        background: #052e16 !important;
        border-color: #166534 !important;
        color: #86efac !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .operational-pill.maintenance {
        background: #451a03 !important;
        border-color: #9a3412 !important;
        color: #fdba74 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .operational-pill.inactive {
        background: #4c0519 !important;
        border-color: #be123c !important;
        color: #fda4af !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .operational-status-bar {
        background: #1e293b;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .operational-status-meta {
        color: #94a3b8;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-ai .qa-icon-wrap { background: rgba(99, 102, 241, 0.18); color: #818cf8; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-load .qa-icon-wrap { background: rgba(2, 132, 199, 0.18); color: #38bdf8; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-facilities .qa-icon-wrap { background: rgba(5, 150, 105, 0.18); color: #34d399; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-meter .qa-icon-wrap { background: rgba(217, 119, 6, 0.18); color: #fbbf24; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-incidents .qa-icon-wrap { background: rgba(225, 29, 72, 0.18); color: #fb7185; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .qa-maintenance .qa-icon-wrap { background: rgba(124, 58, 237, 0.18); color: #a78bfa; }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-filter-panel,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-filter-field input {
        background:#111827;
        border-color:#334155;
        color:#e2e8f0;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-filter-button.secondary {
        background: #0f172a !important;
        border-color: #475569 !important;
        color: #cbd5e1 !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-filter-button.secondary:hover {
        background: #1e293b !important;
        border-color: #3b82f6 !important;
        color: #eff6ff !important;
    }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-title { color:#f8fafc; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-title-icon { background:#3b2417; border-color:#7c2d12; color:#fb923c; }
    :is(html.dark-mode, body.dark-mode) .dashboard-page .dashboard-kpi-value { color:#f8fafc; }

    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-card-title.consumption,
    :is(html.dark-mode, body.dark-mode) .dashboard-page .insight-card-title.critical,
    :is(html.dark-mode, body.dark-mode) .dashboard-page h1,
    :is(html.dark-mode, body.dark-mode) .dashboard-page h2,
    :is(html.dark-mode, body.dark-mode) .dashboard-page h3 {
        color: #f8fafc !important;
    }

    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#f0f7ff"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#f0fdf4"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#fffbeb"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#fef2f2"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#fff7ed"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#f5f3ff"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#eef2ff"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#ecfdf5"],
    :is(html.dark-mode, body.dark-mode) .dashboard-page [style*="background:#f8fafc"] {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .dashboard-page [style*="color:#1e293b"],
    body.dark-mode .dashboard-page [style*="color:#334155"],
    body.dark-mode .dashboard-page [style*="color:#64748b"],
    body.dark-mode .dashboard-page [style*="color:#94a3b8"],
    body.dark-mode .dashboard-page [style*="color:#3762c8"] {
        color: #e2e8f0 !important;
    }

    body.dark-mode .dashboard-page .alert-item.critical {
        background: rgba(225, 29, 72, 0.12) !important;
        border-left-color: #fb7185 !important;
        color: #fecdd3 !important;
    }

    body.dark-mode .dashboard-page .alert-item.very-high {
        background: rgba(251, 113, 133, 0.14) !important;
        border-left-color: #fb7185 !important;
        color: #fecdd3 !important;
    }

    body.dark-mode .dashboard-page .alert-item.high {
        background: rgba(249, 115, 22, 0.12) !important;
        border-left-color: #fb923c !important;
        color: #fed7aa !important;
    }

    body.dark-mode .dashboard-page .alert-item.warning {
        background: rgba(245, 158, 11, 0.12) !important;
        border-left-color: #fbbf24 !important;
        color: #fde68a !important;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .report-card-container {
            padding: 22px;
        }
        .dashboard-header {
            flex-direction: column !important;
            gap: 14px;
            align-items: flex-start !important;
            margin-bottom: 1.5rem !important;
        }
        .dashboard-header > div:last-child {
            width: 100%;
            text-align: left !important;
        }
        .stats-grid {
            gap: 12px !important;
            margin-bottom: 1.5rem !important;
        }
        .stat-card {
            min-width: calc(50% - 6px);
            padding: 18px;
        }
        .summary-grid {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        .chart-grid {
            flex-direction: column !important;
            gap: 14px !important;
            margin-bottom: 1.5rem !important;
        }
        .chart-item { width: 100% !important; }
        .insights-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .consumption-table { min-width:720px; }
        .dashboard-page .stats-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .dashboard-page .summary-grid { grid-template-columns:1fr; }
        .dashboard-page .chart-grid { grid-template-columns:1fr; }
        .chart-insights-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .quick-actions-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    }

    @media (max-width: 640px) {
        .report-card-container {
            padding: 14px;
            border-radius: 14px;
        }
        .dashboard-header h1 {
            font-size: 1.3rem !important;
            line-height: 1.2;
        }
        .dashboard-header p {
            font-size: 0.88rem !important;
        }
        .dashboard-header > div:first-child > div {
            font-size: 0.78rem !important;
            flex-wrap: wrap;
        }
        .stat-card {
            min-width: 100%;
            padding: 14px;
        }
        .quick-action-btn {
            width: 100%;
            justify-content: center;
        }
        .chart-container {
            padding: 14px;
        }
        .chart-container h3 {
            font-size: 0.92rem !important;
            margin-bottom: 12px !important;
        }
        .chart-canvas-wrap {
            height: 240px !important;
        }
        .insight-card-header {
            padding: 16px;
        }
        .insight-card-title {
            font-size: 1rem;
        }
        .notifications-body {
            padding: 14px;
        }
        .status-pill {
            min-width: 64px;
            padding: 5px 10px;
            font-size: 0.68rem;
        }
        .custom-table thead th,
        .custom-table tbody tr td {
            padding: 12px 10px;
        }
        .consumption-table td.facility-name {
            font-size: 1rem;
        }
        .dashboard-header { grid-template-columns:1fr; }
        .dashboard-role-badge { width:fit-content; }
        .dashboard-filter-panel { align-items:stretch; }
        .dashboard-period-form { width:100%; }
        .dashboard-filter-field { flex:1 1 130px; }
        .dashboard-page .stats-grid { grid-template-columns:1fr; }
        .quick-actions-grid { grid-template-columns:1fr; }
        .chart-insights-grid { grid-template-columns:1fr; }
    }

    a.stat-card {
        text-decoration: none !important;
        color: inherit !important;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease !important;
    }
    a.stat-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12) !important;
    }

    /* Executive Presentation Bar & Top Savers */
    .executive-score-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        font-size: 0.8rem;
        font-weight: 800;
    }
    .executive-score-badge .score-val {
        font-size: 1.15rem;
        font-weight: 900;
        color: #047857;
    }
    body.dark-mode .dashboard-page .executive-score-badge {
        background: rgba(6,95,70,0.25);
        border-color: #065f46;
        color: #6ee7b7;
    }
    body.dark-mode .dashboard-page .executive-score-badge .score-val {
        color: #34d399;
    }

    /* Top Energy Savers Showcase Section */
    .top-savers-section {
        margin-top: 22px;
        border: 1px solid #d1fae5;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.035);
        padding: 18px 22px;
    }
    body.dark-mode .dashboard-page .top-savers-section {
        background: #111827 !important;
        border-color: #1e3a29 !important;
        box-shadow: none !important;
    }
    .top-savers-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .top-savers-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .top-savers-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fef9c3;
        border: 1px solid #fef08a;
        color: #ca8a04;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    body.dark-mode .dashboard-page .top-savers-icon {
        background: #36280b;
        border-color: #713f12;
        color: #fde047;
    }
    .top-savers-title {
        margin: 0;
        font-size: 0.96rem;
        font-weight: 850;
        color: #065f46;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }
    body.dark-mode .dashboard-page .top-savers-title {
        color: #6ee7b7 !important;
    }
    .top-savers-subtitle {
        margin: 2px 0 0;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
    }
    body.dark-mode .dashboard-page .top-savers-subtitle {
        color: #94a3b8 !important;
    }
    .top-savers-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.035em;
    }
    body.dark-mode .dashboard-page .top-savers-badge {
        background: rgba(6, 95, 70, 0.3) !important;
        border-color: #065f46 !important;
        color: #6ee7b7 !important;
    }
    .top-savers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
    }
    .top-saver-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none !important;
        color: inherit !important;
        cursor: pointer;
        transition: transform 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .top-saver-card:hover {
        transform: translateY(-2px);
        border-color: #86efac;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.08);
        background: #ffffff;
    }
    body.dark-mode .dashboard-page .top-saver-card {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    body.dark-mode .dashboard-page .top-saver-card:hover {
        border-color: #10b981 !important;
        background: #1e293b !important;
    }
    .top-saver-rank {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 900;
        flex-shrink: 0;
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .top-saver-rank.rank-1 {
        background: #fef08a;
        color: #854d0e;
        border-color: #fde047;
    }
    .top-saver-rank.rank-2 {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    .top-saver-rank.rank-3 {
        background: #ffedd5;
        color: #9a3412;
        border-color: #fed7aa;
    }
    body.dark-mode .dashboard-page .top-saver-rank {
        background: #064e3b;
        color: #a7f3d0;
        border-color: #047857;
    }
    body.dark-mode .dashboard-page .top-saver-rank.rank-1 {
        background: #713f12;
        color: #fef08a;
        border-color: #a16207;
    }
    body.dark-mode .dashboard-page .top-saver-rank.rank-2 {
        background: #334155;
        color: #e2e8f0;
        border-color: #475569;
    }
    body.dark-mode .dashboard-page .top-saver-rank.rank-3 {
        background: #7c2d12;
        color: #fed7aa;
        border-color: #9a3412;
    }
    .top-saver-info {
        flex: 1;
        min-width: 0;
    }
    .top-saver-name {
        font-size: 0.84rem;
        font-weight: 750;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    body.dark-mode .dashboard-page .top-saver-name {
        color: #f8fafc !important;
    }
    .top-saver-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
        flex-wrap: wrap;
        font-size: 0.72rem;
    }
    .top-saver-reduction {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 6px;
        background: #dcfce7;
        color: #15803d;
        font-weight: 800;
    }
    body.dark-mode .dashboard-page .top-saver-reduction {
        background: rgba(34, 197, 94, 0.18) !important;
        color: #86efac !important;
    }
    .top-saver-kwh {
        color: #64748b;
        font-weight: 600;
    }
    body.dark-mode .dashboard-page .top-saver-kwh {
        color: #94a3b8 !important;
    }

    /* Print / Export Report Mode */
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .top-header,
        .sidebar,
        .dashboard-filter-panel form,
        .quick-action-btn,
        .insight-header-action,
        .insight-card-footer,
        .dashboard-filter-button,
        .btn-print-dashboard {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .report-card-container {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .chart-grid {
            page-break-inside: avoid;
        }
        .chart-insights-grid {
            grid-template-columns: repeat(6, 1fr) !important;
        }
        .print-header-stamp {
            display: block !important;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0f172a;
        }
    }
    .print-header-stamp {
        display: none;
    }
</style>

<div class="dashboard-page" style="width:100%; margin:0 auto;">
    <div class="report-card-container">
        
        <div class="print-header-stamp">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-size:1.4rem; font-weight:900; color:#0f172a; margin:0;">QUEZON CITY LOCAL GOVERNMENT</h2>
                    <p style="font-size:0.85rem; color:#475569; margin:2px 0 0;">Energy Management & Efficiency Executive Summary Report</p>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.75rem; font-weight:700; color:#64748b;">REPORTING PERIOD</div>
                    <strong style="font-size:0.9rem; color:#0f172a;">{{ $periodStartLabel ?? 'N/A' }} - {{ $periodEndLabel ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>

        <div class="dashboard-header">
            <div class="dashboard-title-row">
                <span class="dashboard-title-icon" aria-hidden="true"><i class="fas fa-bolt"></i></span>
                <div>
                    <h1 class="dashboard-title">Energy Efficiency Overview</h1>
                    <p class="dashboard-subtitle">Period-based energy monitoring, cost analysis, and operational alerts.</p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div class="executive-score-badge" title="Overall Energy Performance Rating based on Baseline and GEMP benchmarks">
                    <i class="fa-solid fa-gauge"></i>
                    <span>Efficiency Score:</span>
                    <span class="score-val">{{ $efficiencyScore ?? 85 }}</span>
                    <small style="font-size:0.65rem; color:#059669;">/ 100</small>
                </div>
                <span class="dashboard-role-badge"><i class="fas fa-shield-alt"></i> {{ Auth::user()->role ?? 'Administrator' }}</span>
            </div>
        </div>

        <div class="dashboard-filter-panel">
            <div class="dashboard-period-summary">
                <i class="fas fa-calendar-alt"></i>
                <span>
                    Viewing <strong>{{ $periodStartLabel ?? now()->subMonths(5)->format('F') }}</strong> to
                    <strong>{{ $periodEndLabel ?? now()->format('F Y') }}</strong>
                    <span>({{ $periodMonthCount ?? 6 }} months)</span>
                </span>
            </div>
            <form id="dashboard-period-form" class="dashboard-period-form" method="GET" action="{{ route('dashboard.index') }}">
                <div class="dashboard-filter-field">
                    <label for="start_month">From</label>
                    <input id="start_month" type="month" name="start_month" value="{{ $periodStartInput ?? now()->subMonths(5)->format('Y-m') }}">
                </div>
                <div class="dashboard-filter-field">
                    <label for="end_month">To</label>
                    <input id="end_month" type="month" name="end_month" value="{{ $periodEndInput ?? now()->format('Y-m') }}">
                </div>
                <button type="submit" class="dashboard-filter-button primary"><i class="fas fa-filter"></i>&nbsp; Apply</button>
                <a href="{{ route('dashboard.index') }}" class="dashboard-filter-button secondary">Reset</a>
                <button type="button" data-secure-print class="dashboard-filter-button btn-print-dashboard" style="background:#0f172a; color:#fff; border:1px solid #1e293b;" title="Print or Save as Executive Summary PDF">
                    <i class="fa-solid fa-print"></i>&nbsp; Print Report
                </button>
            </form>
        </div>

        @php
            $trendText = trim((string) ($kwhTrend ?? ''));
            $trendIsDown = str_starts_with($trendText, '-');
        @endphp
        <div class="stats-grid">
            <a href="{{ route('modules.facilities.index') }}" class="stat-card kpi-blue" title="View Facility Registry">
                <div class="dashboard-kpi-heading"><i class="fas fa-building"></i> Total Facilities</div>
                <div class="dashboard-kpi-value">{{ $totalFacilities ?? 0 }}</div>
                <div class="dashboard-kpi-note">Monitored facility portfolio &rarr;</div>
            </a>

            <a href="{{ route('modules.energy-monitoring.index') }}" class="stat-card kpi-green" title="View Main Meter Energy Monitoring">
                <div class="dashboard-kpi-heading"><i class="fas fa-bolt"></i> Net Consumption</div>
                <div class="dashboard-kpi-value">{{ number_format($totalKwh ?? 0) }} <small>kWh</small></div>
                @if($trendText !== '')
                    <div class="dashboard-kpi-note {{ $trendIsDown ? 'is-down' : 'is-up' }}">
                        <i class="fas {{ $trendIsDown ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                        {{ $trendText }} vs previous period &rarr;
                    </div>
                @else
                    <div class="dashboard-kpi-note">
                        <i class="fas fa-chart-line"></i> Total for selected period &rarr;
                    </div>
                @endif
            </a>

            <a href="{{ route('reports.performance-summary') }}" class="stat-card kpi-amber" title="View Performance Summary & Reports">
                <div class="dashboard-kpi-heading"><i class="fas fa-coins"></i> Total Expenditure</div>
                <div class="dashboard-kpi-value">₱{{ number_format($totalCost ?? 0, 0) }}</div>
                <div class="dashboard-kpi-note">Selected period energy cost &rarr;</div>
            </a>

            <a href="{{ route('energy-incidents.index') }}" class="stat-card kpi-red" title="View Energy Incidents">
                <div class="dashboard-kpi-heading"><i class="fas fa-exclamation-triangle"></i> Unresolved Incidents</div>
                <div class="dashboard-kpi-value">{{ $unresolvedIncidentCount ?? 0 }}</div>
                <div class="dashboard-kpi-note">Items requiring follow-up &rarr;</div>
            </a>

            <a href="{{ url('modules/maintenance/index') }}" class="stat-card kpi-violet" title="View Maintenance Operations">
                <div class="dashboard-kpi-heading"><i class="fas fa-tools"></i> Ongoing Maintenance</div>
                <div class="dashboard-kpi-value">{{ $ongoingMaintenance ?? 0 }}</div>
                <div class="dashboard-kpi-note">Active maintenance activities &rarr;</div>
            </a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-card-header-row">
                    <div class="summary-card-heading"><i class="fas fa-signal" style="color:#0284c7;"></i> Facility Operational Snapshot</div>
                    @php
                        $actCount = optional($facilityStatusCounts)->active_count ?? 0;
                        $maintCount = optional($facilityStatusCounts)->maintenance_count ?? 0;
                        $inactCount = optional($facilityStatusCounts)->inactive_count ?? 0;
                        $totalKnownFac = $actCount + $maintCount + $inactCount;
                        $actPct = $totalKnownFac > 0 ? round(($actCount / $totalKnownFac) * 100) : 0;
                        $maintPct = $totalKnownFac > 0 ? round(($maintCount / $totalKnownFac) * 100) : 0;
                        $inactPct = $totalKnownFac > 0 ? (100 - $actPct - $maintPct) : 0;
                    @endphp
                    <span class="snapshot-total-badge">{{ $totalKnownFac }} Total Monitored</span>
                </div>
                <div class="operational-pills">
                    <span class="operational-pill active" title="Operational & Metered">
                        <span class="pill-dot"></span> Active <strong>{{ $actCount }}</strong>
                    </span>
                    <span class="operational-pill maintenance" title="Under Repair or Scheduled Service">
                        <span class="pill-dot"></span> Maintenance <strong>{{ $maintCount }}</strong>
                    </span>
                    <span class="operational-pill inactive" title="Offline or Decommissioned">
                        <span class="pill-dot"></span> Inactive <strong>{{ $inactCount }}</strong>
                    </span>
                </div>
                <div>
                    <div class="operational-status-bar" title="Operational Distribution: {{ $actPct }}% Active, {{ $maintPct }}% Maintenance, {{ $inactPct }}% Inactive">
                        <div class="bar-segment seg-active" style="width: {{ $actPct }}%;"></div>
                        <div class="bar-segment seg-maintenance" style="width: {{ $maintPct }}%;"></div>
                        <div class="bar-segment seg-inactive" style="width: {{ $inactPct }}%;"></div>
                    </div>
                    <div class="operational-status-meta">
                        <span><i class="fa-solid fa-circle-check" style="color:#22c55e;"></i> {{ $actPct }}% operational</span>
                        <span>{{ $maintCount > 0 ? $maintCount . ' pending service' : 'All systems nominal' }}</span>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-header-row">
                    <div class="summary-card-heading"><i class="fas fa-bolt" style="color:#eab308;"></i> Quick Actions</div>
                    <span class="quick-actions-subtitle">Operational Shortcuts</span>
                </div>
                <div class="quick-actions-grid">
                    <a href="{{ route('modules.ai-alerts.index') }}" class="quick-action-btn qa-ai" title="View AI Alerts & Operational Triage">
                        <span class="qa-icon-wrap"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                        <span class="qa-label">AI Alerts</span>
                        @if(($activeAlerts ?? 0) > 0)
                            <span class="qa-badge qa-badge-danger">{{ $activeAlerts }}</span>
                        @endif
                    </a>
                    <a href="{{ route('modules.load-tracking.index') }}" class="quick-action-btn qa-load" title="Inspect Equipment Load Inventory & Schedules">
                        <span class="qa-icon-wrap"><i class="fa-solid fa-plug-circle-bolt"></i></span>
                        <span class="qa-label">Load Tracking</span>
                    </a>
                    <a href="{{ route('modules.facilities.index') }}" class="quick-action-btn qa-facilities" title="Manage Facilities & Baselines">
                        <span class="qa-icon-wrap"><i class="fa-solid fa-building"></i></span>
                        <span class="qa-label">Facilities</span>
                    </a>
                    <a href="{{ route('energy.dashboard') }}" class="quick-action-btn qa-meter" title="View Real-Time Main Meter Dashboard">
                        <span class="qa-icon-wrap"><i class="fa-solid fa-gauge-high"></i></span>
                        <span class="qa-label">Main Meter</span>
                    </a>
                    <a href="{{ route('energy-incidents.index') }}" class="quick-action-btn qa-incidents" title="Track & Manage Energy Incidents">
                        <span class="qa-icon-wrap"><i class="fa-solid fa-triangle-exclamation"></i></span>
                        <span class="qa-label">Incidents</span>
                        @if(($unresolvedIncidentCount ?? 0) > 0)
                            <span class="qa-badge qa-badge-danger">{{ $unresolvedIncidentCount }}</span>
                        @endif
                    </a>
                    <a href="{{ url('modules/maintenance/index') }}" class="quick-action-btn qa-maintenance" title="Inspect Ongoing & Scheduled Maintenance">
                        <span class="qa-icon-wrap"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                        <span class="qa-label">Maintenance</span>
                        @if(($ongoingMaintenance ?? 0) > 0)
                            <span class="qa-badge qa-badge-violet">{{ $ongoingMaintenance }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        @php
            $varianceValue = $periodVariancePercent ?? null;
            $varianceClass = $varianceValue === null ? '' : ($varianceValue > 0 ? 'positive' : 'negative');
            $variancePrefix = $varianceValue !== null && $varianceValue > 0 ? '+' : '';
        @endphp
        <div class="chart-insights-grid" aria-label="Selected period insights">
            <div class="chart-insight-card">
                <div class="chart-insight-label"><i class="fas fa-balance-scale"></i> Baseline Variance</div>
                <div class="chart-insight-value {{ $varianceClass }}">
                    {{ $varianceValue === null ? 'N/A' : $variancePrefix . number_format($varianceValue, 1) . '%' }}
                </div>
                <div class="chart-insight-note">{{ $varianceValue !== null && $varianceValue > 0 ? 'Consumption is above target' : 'Compared with period baseline' }}</div>
            </div>
            <div class="chart-insight-card sustainability">
                <div class="chart-insight-label"><i class="fa-solid fa-leaf"></i> Carbon Footprint</div>
                <div class="chart-insight-value">{{ number_format($carbonEmissionsMt ?? 0, 2) }} <small style="font-size:.65rem; font-weight:700;">MT CO₂e</small></div>
                <div class="chart-insight-note">~{{ number_format($treesEquivalent ?? 0) }} mature trees offset equivalent</div>
            </div>
            <div class="chart-insight-card gemp">
                <div class="chart-insight-label"><i class="fa-solid fa-award"></i> GEMP RA 11285 Target</div>
                <div class="chart-insight-value tone-{{ $gempTone ?? 'neutral' }}">
                    <span>{{ $gempStatus ?? 'N/A' }}</span>
                    @if(!empty($gempPercentageLabel))
                        <span class="gemp-badge {{ $gempTone ?? 'warn' }}">{{ $gempPercentageLabel }}</span>
                    @endif
                </div>
                <div class="chart-insight-note">10% Mandatory Gov't Reduction Goal</div>
            </div>
            <div class="chart-insight-card">
                <div class="chart-insight-label"><i class="fas fa-chart-bar"></i> Peak Usage Month</div>
                <div class="chart-insight-value">{{ $peakUsageLabel ?? 'No data' }}</div>
                <div class="chart-insight-note">{{ number_format($peakUsageValue ?? 0) }} kWh recorded</div>
            </div>
            <div class="chart-insight-card">
                <div class="chart-insight-label"><i class="fas fa-coins"></i> Effective Rate</div>
                <div class="chart-insight-value">₱{{ number_format($averageEnergyRate ?? 0, 2) }}/kWh</div>
                <div class="chart-insight-note">Average cost for recorded usage</div>
            </div>
            <div class="chart-insight-card">
                <div class="chart-insight-label"><i class="fas fa-database"></i> Data Coverage</div>
                <div class="chart-insight-value">{{ $recordedMonthCount ?? 0 }} / {{ $periodMonthCount ?? 0 }} months</div>
                <div class="chart-insight-note">{{ $missingMonthCount ?? 0 }} month(s) without usage data</div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-item">
                <div class="chart-container">
                    <h3 class="dashboard-chart-title">
                        <span style="width:8px; height:8px; background:#3762c8; border-radius:50%;"></span>
                        Actual vs Baseline Consumption
                    </h3>
                    <p class="chart-card-subtitle">Monthly consumption against the configured efficiency target</p>
                    <div class="chart-canvas-wrap"><canvas id="energyChart"></canvas></div>
                    @if(($missingMonthCount ?? 0) > 0)
                        <div class="chart-footnote"><i class="fas fa-info-circle"></i> Missing monthly readings are displayed as gaps and are not treated as zero consumption.</div>
                    @endif
                </div>
            </div>

            <div class="chart-item">
                <div class="chart-container">
                    <h3 class="dashboard-chart-title">
                        <span style="width:8px; height:8px; background:#e11d48; border-radius:50%;"></span>
                        Monthly Cost Trend
                    </h3>
                    <p class="chart-card-subtitle">Energy expenditure movement across the selected period</p>
                    <div class="chart-canvas-wrap"><canvas id="costChart"></canvas></div>
                    @if(($missingMonthCount ?? 0) > 0)
                        <div class="chart-footnote"><i class="fas fa-info-circle"></i> Months without recorded bills are displayed as gaps instead of zero cost.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="insights-grid">
            
            <div class="insight-card">
                <div class="insight-card-header">
                    <div>
                        <h3 class="insight-card-title consumption"><i class="fa-solid fa-fire-flame-curved"></i> High Consumption Hubs</h3>
                        <div class="insight-card-meta">Ranked by variance for the selected {{ $periodMonthCount ?? 6 }}-month period</div>
                    </div>
                    <a href="{{ route('energy.dashboard') }}" class="insight-header-action">View monitoring <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="table-scroll">
                    <table class="custom-table consumption-table">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th style="text-align:center;" title="Total consumption for selected period">Actual kWh</th>
                                <th style="text-align:center;" title="Total baseline for selected period">Baseline kWh</th>
                                <th style="text-align:center;">Variance</th>
                                <th style="text-align:center;">Condition</th>
                                <th style="text-align:center;">Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(collect($topFacilities ?? [])->filter(fn ($row) => (float) ($row->total_kwh ?? 0) > 0)->take(5) as $facility)
                            @php
                                $deviation = (float) ($facility->deviation ?? 0);
                                $status = (string) ($facility->status ?? 'Normal');
                                $trendSpikeDetected = (bool) ($facility->trend_spike_detected ?? false);
                                $statusStyles = [
                                    'Critical' => ['bg' => '#fee2e2', 'text' => '#7f1d1d', 'border' => '#fecaca'],
                                    'Very High' => ['bg' => '#fff1f2', 'text' => '#e11d48', 'border' => '#fecdd3'],
                                    'High' => ['bg' => '#ffedd5', 'text' => '#c2410c', 'border' => '#fdba74'],
                                    'Warning' => ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fde68a'],
                                    'Normal' => ['bg' => '#f0fdf4', 'text' => '#16a34a', 'border' => '#bbf7d0'],
                                ];
                                $theme = $statusStyles[$status] ?? $statusStyles['Normal'];
                                $deviationColor = $deviation >= 0 ? '#e11d48' : '#16a34a';
                            @endphp
                            <tr onclick="window.location.href='{{ route('facilities.monthly-records', ['facility' => $facility->id]) }}'" style="cursor:pointer;" title="Click to view monthly records for {{ $facility->name }}">
                                <td class="facility-name"><span class="facility-rank">{{ $loop->iteration }}</span><a href="{{ route('facilities.monthly-records', ['facility' => $facility->id]) }}" class="facility-name-text" title="Click to view monthly records for {{ $facility->name }}" style="text-decoration:none; color:inherit; font-weight:700;">{{ $facility->name }}</a></td>
                                <td class="value-kwh" style="text-align:center;">{{ number_format($facility->total_kwh, 2) }}</td>
                                <td class="value-baseline" style="text-align:center;">{{ number_format($facility->baseline_kwh, 2) }}</td>
                                <td class="value-deviation" style="text-align:center; color:{{ $deviationColor }};">{{ number_format($deviation, 2) }}%</td>
                                <td style="text-align:center;">
                                    <span class="status-pill status-{{ Str::slug($status) }}"
                                          title="{{ $trendSpikeDetected ? 'Escalated because consumption increased for three consecutive months.' : 'Condition based on baseline variance thresholds.' }}"
                                          style="background:{{ $theme['bg'] }}; color:{{ $theme['text'] }}; border-color:{{ $theme['border'] }};">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    @if($trendSpikeDetected)
                                        <span class="trend-indicator spike" title="Consumption increased for three consecutive months"><i class="fas fa-chart-line"></i> Spike</span>
                                    @else
                                        <span class="trend-indicator stable"><i class="fas fa-minus"></i> Stable</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No consumption records found for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="insight-card">
                <div class="insight-card-header">
                    <div>
                        <h3 class="insight-card-title critical"><i class="fa fa-bell"></i> Critical Notifications</h3>
                        <div class="insight-card-meta">{{ collect($criticalAlerts ?? [])->count() }} priority alert(s) in the selected period</div>
                    </div>
                    <a href="{{ route('energy-incidents.index') }}" class="insight-header-action">Open incidents <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="notifications-body">
                    @forelse(collect($criticalAlerts ?? [])->take(3) as $alert)
                        @php
                            $level = (string) ($alert['level'] ?? 'High');
                            $levelClass = strtolower(str_replace(' ', '-', $level));
                            $message = (string) ($alert['message'] ?? $alert);
                            $displayMessage = preg_replace('/^(Critical|Alert|Incident):\s*/i', '', $message) ?: $message;
                            $icons = [
                                'critical' => 'fa-circle-exclamation',
                                'very-high' => 'fa-triangle-exclamation',
                                'high' => 'fa-fire-flame-curved',
                                'warning' => 'fa-bell',
                            ];
                            $iconClass = $icons[$levelClass] ?? 'fa-exclamation-triangle';
                        @endphp
                        <div class="alert-item {{ $levelClass }}">
                            <span class="alert-icon"><i class="fa-solid {{ $iconClass }}"></i></span>
                            <span>
                                <strong class="alert-level">{{ $level }}</strong>
                                <span>{{ $displayMessage }}</span>
                            </span>
                        </div>
                    @empty
                        <div style="padding:20px; text-align:center; background:#f8fafc; border-radius:12px; color:#94a3b8; font-size:0.9rem;">
                            <i class="fa fa-check-circle" style="color:#22c55e;"></i> No critical alerts at the moment.
                        </div>
                    @endforelse
                </div>
                <div class="insight-card-footer">
                    <a href="{{ route('energy-incidents.index') }}" class="insight-footer-link">Review all incidents <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

        </div>

        @if(collect($topSavers ?? [])->isNotEmpty())
            <div class="top-savers-section">
                <div class="top-savers-header">
                    <div class="top-savers-title-wrap">
                        <div class="top-savers-icon">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div>
                            <h3 class="top-savers-title">Top Energy Efficiency Champions</h3>
                            <p class="top-savers-subtitle">Government facilities generating the largest energy reductions below target baseline</p>
                        </div>
                    </div>
                    <span class="top-savers-badge">
                        <i class="fa-solid fa-leaf"></i> {{ collect($topSavers)->count() }} Leading Facilities
                    </span>
                </div>

                <div class="top-savers-grid">
                    @foreach($topSavers as $saver)
                        <a href="{{ route('facilities.monthly-records', ['facility' => $saver->id]) }}" class="top-saver-card" title="Click to view monthly records for {{ $saver->name }}">
                            <div class="top-saver-rank rank-{{ $loop->iteration }}">
                                @if($loop->iteration === 1)
                                    <i class="fa-solid fa-crown" style="font-size:0.75rem;"></i>
                                @else
                                    #{{ $loop->iteration }}
                                @endif
                            </div>
                            <div class="top-saver-info">
                                <span class="top-saver-name">{{ $saver->name }}</span>
                                <div class="top-saver-meta">
                                    <span class="top-saver-reduction">
                                        <i class="fa-solid fa-arrow-trend-down"></i> {{ number_format(abs($saver->deviation), 1) }}% saved
                                    </span>
                                    <span class="top-saver-kwh">
                                        {{ number_format($saver->kwh_saved, 0) }} kWh reduced
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const numberFmt = new Intl.NumberFormat('en-US');
    let energyChartInstance = null;
    let costChartInstance = null;
    const dashboardPeriodForm = document.getElementById('dashboard-period-form');
    const startMonthInput = document.getElementById('start_month');
    const endMonthInput = document.getElementById('end_month');
    let autoSubmitTimer = null;

    const autoSubmitPeriodFilter = function () {
        const startMonth = startMonthInput ? startMonthInput.value : '';
        const endMonth = endMonthInput ? endMonthInput.value : '';

        if (!dashboardPeriodForm || !startMonth || !endMonth || startMonth > endMonth) {
            return;
        }

        if (dashboardPeriodForm.dataset.autoSubmitting === '1') {
            return;
        }

        window.clearTimeout(autoSubmitTimer);
        autoSubmitTimer = window.setTimeout(function () {
            dashboardPeriodForm.dataset.autoSubmitting = '1';

            if (typeof dashboardPeriodForm.requestSubmit === 'function') {
                dashboardPeriodForm.requestSubmit();
                return;
            }

            dashboardPeriodForm.submit();
        }, 300);
    };

    [startMonthInput, endMonthInput].forEach(function (field) {
        if (!field) {
            return;
        }

        field.addEventListener('change', autoSubmitPeriodFilter);
    });

    const getChartTheme = function() {
        const isDark = document.body.classList.contains('dark-mode');
        if (isDark) {
            return {
                textColor: '#cbd5e1',
                mutedText: '#94a3b8',
                gridColor: '#1f2937',
                energyBar: '#60a5fa',
                baselineLine: '#34d399',
                costLine: '#fb7185',
                costFill: 'rgba(251, 113, 133, 0.12)',
            };
        }
        return {
            textColor: '#334155',
            mutedText: '#64748b',
            gridColor: '#f1f5f9',
            energyBar: '#3762c8',
            baselineLine: '#22c55e',
            costLine: '#e11d48',
            costFill: 'rgba(225,29,72,0.05)',
        };
    };

    const buildChartOptions = function(prefix, suffix, theme) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: theme.textColor,
                        font: { family: 'Inter', weight: 600 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label ? context.dataset.label + ': ' : '';
                            const value = Number(context.parsed.y ?? 0);
                            return label + prefix + numberFmt.format(value) + suffix;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: theme.gridColor, drawBorder: false },
                    ticks: {
                        color: theme.mutedText,
                        font: { family: 'Inter' },
                        callback: function(value) { return prefix + numberFmt.format(value) + suffix; }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: theme.mutedText, font: { family: 'Inter' } }
                }
            }
        };
    };

    const renderDashboardCharts = function() {
        const theme = getChartTheme();

        if (energyChartInstance) {
            energyChartInstance.destroy();
        }
        if (costChartInstance) {
            costChartInstance.destroy();
        }

        const energyCanvas = document.getElementById('energyChart');
        if (energyCanvas) {
            energyChartInstance = new Chart(energyCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($energyChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun']) !!},
                    datasets: [
                        {
                            label: 'Actual Usage (kWh)',
                            data: {!! json_encode($energyChartDisplayData ?? $energyChartData ?? []) !!},
                            backgroundColor: theme.energyBar,
                            borderRadius: 8,
                            barThickness: 20
                        },
                        {
                            label: 'Efficiency Baseline',
                            data: {!! json_encode($baselineChartData ?? [1000,1400,1050,1500,1450,1350]) !!},
                            type: 'line',
                            borderColor: theme.baselineLine,
                            borderWidth: 3,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            fill: false
                        }
                    ]
                },
                options: buildChartOptions('', ' kWh', theme)
            });
        }

        const costCanvas = document.getElementById('costChart');
        if (costCanvas) {
            costChartInstance = new Chart(costCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($costChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun']) !!},
                    datasets: [{
                        label: 'Monthly Cost (PHP)',
                        data: {!! json_encode($costChartDisplayData ?? $costChartData ?? []) !!},
                        borderColor: theme.costLine,
                        backgroundColor: theme.costFill,
                        fill: true,
                        tension: 0.4,
                        spanGaps: false,
                        pointRadius: 4,
                        pointBackgroundColor: theme.costLine
                    }]
                },
                options: buildChartOptions('PHP ', '', theme)
            });
        }
    };

    renderDashboardCharts();

    let lastDarkState = document.body.classList.contains('dark-mode');
    const observer = new MutationObserver(function() {
        const currentDarkState = document.body.classList.contains('dark-mode');
        if (currentDarkState !== lastDarkState) {
            lastDarkState = currentDarkState;
            renderDashboardCharts();
        }
    });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endsection


