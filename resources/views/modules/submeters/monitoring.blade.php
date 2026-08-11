@extends('layouts.qc-admin')
@section('title', 'Submeter Monitoring')

<style>
    .submeter-ui { width: 100%; margin: 0; display: grid; gap: 14px; }
    .submeter-flash { border-radius: 12px; padding: 12px 14px; font-weight: 700; border: 1px solid transparent; }
    .submeter-flash.ok { background: #dcfce7; color: #166534; border-color: #86efac; }
    .submeter-flash.err { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
    .submeter-flash.warn { background: #fff7ed; color: #9a3412; border-color: #fdba74; }

    .submeter-head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; align-items: flex-start; }
    .submeter-title { margin: 0; color: #1e3a8a; font-size: 1.48rem; font-weight: 800; }
    .submeter-subtitle { margin-top: 4px; color: #64748b; }
    .submeter-head-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .sm-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; padding: 10px 14px; font-size: .9rem; font-weight: 700; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
    .sm-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 16px rgba(15,23,42,.10); }
    .sm-btn.primary { background: #1d4ed8; color: #fff; }
    .sm-btn.soft { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
    .sm-btn.neutral { background: #f1f5f9; color: #334155; border-color: #e2e8f0; }
    .report-card-container {
        width: 100%;
        background: linear-gradient(135deg, #f8fafc, #eef2ff);
        border-radius: 26px;
        box-shadow: 0 12px 40px rgba(37, 99, 235, .18);
        border: 0;
        padding: 28px 40px 40px;
        display: grid;
        gap: 18px;
    }

    .submeter-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
    .submeter-kpi { border-radius: 14px; border: 1px solid #e2e8f0; padding: 14px; }
    .submeter-kpi .label { font-size: .78rem; font-weight: 800; letter-spacing: .05em; }
    .submeter-kpi .value { margin-top: 6px; font-weight: 700; color: #334155; }
    .submeter-kpi .number { font-size: 1.72rem; font-weight: 900; line-height: 1.1; color: #991b1b; }
    .submeter-kpi.alert { background: linear-gradient(135deg,#eff6ff,#fff); border-color: #dbeafe; }
    .submeter-kpi.alert .label { color: #1e40af; }
    .submeter-kpi.top { background: linear-gradient(135deg,#ecfeff,#fff); border-color: #bae6fd; }
    .submeter-kpi.top .label { color: #0f766e; }
    .submeter-kpi.fac { background: linear-gradient(135deg,#f8fafc,#fff); }
    .submeter-kpi.fac .label { color: #334155; }

    .submeter-sensor-panel { background: #fff; border: 1px solid #dbe4f2; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 18px rgba(15, 23, 42, .08); }
    .submeter-sensor-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px; border-bottom: 1px solid #e2e8f0; background: #ffffff; }
    .submeter-sensor-title { margin: 0; color: #1e293b; font-size: 1rem; font-weight: 900; }
    .submeter-sensor-subtitle { margin-top: 3px; color: #64748b; font-size: .84rem; font-weight: 600; }
    .submeter-sensor-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .submeter-sensor-controls { display: flex; align-items: end; gap: 10px; flex-wrap: wrap; }
    .submeter-sensor-picker { display: grid; grid-template-columns: repeat(2, minmax(190px, 280px)); gap: 8px; }
    .submeter-sensor-picker-field { display: grid; gap: 4px; }
    .submeter-sensor-picker label { color: #475569; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
    .submeter-sensor-picker select { min-height: 36px; padding: 7px 34px 7px 10px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: #0f172a; font-weight: 700; }
    .submeter-sensor-tab { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; border-radius: 10px; border: 1px solid #cbd5e1; background: #fff; color: #334155; padding: 7px 12px; font-weight: 900; text-decoration: none; font-size: .84rem; }
    .submeter-sensor-tab.active { border-color: #22d3ee; background: #ecfeff; color: #0f766e; }
    .submeter-sensor-body { padding: 14px; }
    .submeter-sensor-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-bottom: 12px; }
    .submeter-sensor-stat { border: 1px solid #dbeafe; border-radius: 12px; background: #f8fbff; padding: 11px 12px; }
    .submeter-sensor-stat-label { color: #475569; font-size: .76rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
    .submeter-sensor-stat-value { margin-top: 4px; color: #0f172a; font-size: 1.28rem; font-weight: 900; }
    .submeter-sensor-chart { position: relative; height: 300px; max-height: 300px; width: 100%; }

    .submeter-panel { background: #fff; border: 1px solid #dbe4f2; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 18px rgba(15, 23, 42, .08); }
    .submeter-filter { padding: 12px; display: grid; grid-template-columns: minmax(140px,170px) minmax(160px,200px) minmax(220px,1fr) minmax(220px,1fr) auto; gap: 10px; align-items: end; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .submeter-field { display: grid; gap: 6px; }
    .submeter-field label { font-size: .8rem; font-weight: 700; color: #475569; }
    .submeter-input { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: #0f172a; font-size: .95rem; }
    .submeter-input:focus { outline: none; border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.14); }
    .submeter-filter-actions { display: inline-flex; gap: 8px; }

    .submeter-table-wrap {
        overflow-x: auto;
        overflow-y: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .submeter-table-wrap::-webkit-scrollbar { height: 10px; }
    .submeter-table-wrap::-webkit-scrollbar-track { background: #f8fafc; }
    .submeter-table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; border: 2px solid #f8fafc; }

    .submeter-table-shell {
        margin: 10px;
        border: 1px solid #dbe4f2;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        box-shadow: inset 0 1px 0 #ffffff, 0 8px 22px rgba(15, 23, 42, .05);
    }

    .submeter-table {
        width: 100%;
        min-width: 1090px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .submeter-table col.col-submeter { width: 170px; }
    .submeter-table col.col-facility { width: 170px; }
    .submeter-table col.col-current,
    .submeter-table col.col-baseline { width: 110px; }
    .submeter-table col.col-baseline-source { width: 130px; }
    .submeter-table col.col-increase { width: 100px; }
    .submeter-table col.col-alert { width: 110px; }
    .submeter-table col.col-recommendation { width: 190px; }

    .submeter-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 10px 10px;
        border-bottom: 1px solid #d7e0ee;
        color: #475569;
        text-align: left;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 800;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f5fb 100%);
        white-space: normal;
        line-height: 1.2;
    }

    .submeter-table th.center,
    .submeter-table td.center { text-align: center; }

    .submeter-table th.num,
    .submeter-table td.num { text-align: right; }

    .submeter-table .sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
    }

    .submeter-table thead .sticky-col {
        z-index: 4;
        box-shadow: inset -1px 0 0 #d7e0ee;
    }

    .submeter-table tbody .sticky-col {
        background: #fff;
        box-shadow: inset -1px 0 0 #e2e8f0;
    }

    .submeter-table td {
        padding: 10px 10px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        vertical-align: middle;
        background: transparent;
    }

    .submeter-table tbody tr:nth-child(even):not(.critical):not(.high):not(.warning) {
        background: #fbfdff;
    }

    .submeter-table tbody tr:nth-child(even):not(.critical):not(.high):not(.warning) .sticky-col {
        background: #fbfdff;
    }

    .submeter-table tbody tr:hover:not(.critical):not(.high):not(.warning) {
        background: #f4f8ff;
    }

    .submeter-table tbody tr:hover:not(.critical):not(.high):not(.warning) .sticky-col {
        background: #f4f8ff;
    }

    .submeter-row.critical { background: #fef2f2; }
    .submeter-row.critical .sticky-col { background: #fef2f2; }
    .submeter-row.high { background: #fff7ed; }
    .submeter-row.high .sticky-col { background: #fff7ed; }
    .submeter-row.warning { background: #fffbeb; }
    .submeter-row.warning .sticky-col { background: #fffbeb; }

    .submeter-name {
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }
    .submeter-name-link { color: inherit; text-decoration: none; }
    .submeter-name-link:hover { text-decoration: underline; }

    .submeter-meta { margin-top: 3px; color: #64748b; font-size: .82rem; }
    .submeter-meta.muted { color: #94a3b8; }

    .facility-cell {
        font-weight: 700;
        color: #334155;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .metric {
        font-weight: 800;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
        letter-spacing: .01em;
    }

    .metric.base { color: #1d4ed8; }
    .metric.inc.up { color: #be123c; }
    .metric.inc.down { color: #166534; }

    .submeter-table td.recommendation-cell { white-space: normal; text-align: center; }
    .ai-rec-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        cursor: pointer;
        transition: all .15s ease;
    }
    .ai-rec-btn:hover {
        background: #dbeafe;
        border-color: #60a5fa;
        transform: translateY(-1px);
    }
    .ai-rec-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59,130,246,.2);
    }
    .ai-rec-icon {
        font-size: .95rem;
        font-weight: 800;
        line-height: 1;
    }

    .alert-pill { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 5px 10px; font-size: .78rem; font-weight: 800; border: 1px solid transparent; min-width: 80px; }
    .pill-critical { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
    .pill-very-high { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .pill-high { background: #fef3c7; color: #a16207; border-color: #fcd34d; }
    .pill-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .pill-drop-critical { background: #ede9fe; color: #6d28d9; border-color: #c4b5fd; }
    .pill-drop-high { background: #e0e7ff; color: #4338ca; border-color: #a5b4fc; }
    .pill-drop-warning { background: #cffafe; color: #0e7490; border-color: #67e8f9; }
    .pill-normal { background: #dcfce7; color: #166534; border-color: #86efac; }
    .pill-none { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
    .baseline-pill { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 5px 10px; font-size: .73rem; font-weight: 800; border: 1px solid transparent; min-width: 104px; }
    .baseline-pill.norm-day { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .baseline-pill.ma3 { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .baseline-pill.seasonal { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .baseline-pill.ma6 { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
    .baseline-pill.equipment { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .baseline-pill.configured { background: #dcfce7; color: #166534; border-color: #86efac; }
    .baseline-pill.na { background: #e2e8f0; color: #475569; border-color: #cbd5e1; }

    .submeter-empty-row {
        padding: 26px 14px;
        text-align: center;
        color: #64748b;
        font-weight: 600;
        background: #fcfdff;
    }
    .submeter-empty-content { display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px; }
    .submeter-empty-content i { color:#94a3b8; font-size:1.45rem; }
    .submeter-empty-content strong { color:#334155; }
    .submeter-empty-content span { color:#64748b; font-size:.78rem; font-weight:600; }

    .submeter-modal { display: none; position: fixed; inset: 0; z-index: 10080; background: rgba(15,23,42,.42); backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 18px; }
    .submeter-modal.open { display: flex; }
    .submeter-modal-card { width: min(720px, 100%); max-height: calc(100vh - 36px); overflow: auto; background: #fff; border: 1px solid #dbe3f1; border-radius: 18px; padding: 0; position: relative; box-shadow: 0 26px 56px rgba(15,23,42,.24); }
    .submeter-modal-close { position: absolute; top: 14px; right: 14px; width: 34px; height: 34px; border-radius: 999px; border: 1px solid #d1d9e6; background: #f8fafc; font-size: 1.4rem; line-height: 1; color: #64748b; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease; }
    .submeter-modal-close:hover { background: #eef2ff; border-color: #a5b4fc; color: #334155; }
    .submeter-modal-head { display: flex; gap: 12px; align-items: flex-start; padding: 24px 24px 12px; padding-right: 60px; border-bottom: 1px solid #e2e8f0; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }
    .submeter-modal-badge { width: 38px; height: 38px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
    .submeter-modal-title { margin: 0; color: #0f172a; font-size: 1.35rem; font-weight: 900; line-height: 1.15; }
    .submeter-modal-meta { margin-top: 5px; font-size: .9rem; color: #64748b; font-weight: 700; }
    .submeter-modal-alert { margin: 12px 24px 0; }
    .submeter-modal-text { margin: 10px 24px 0; border: 1px solid #dbe3f1; border-radius: 14px; padding: 16px 16px; font-size: 1.02rem; line-height: 1.42; font-weight: 700; color: #334155; background: #f8fafc; }
    .submeter-modal-text.tone-critical { border-color: #fca5a5; background: #fef2f2; color: #7f1d1d; }
    .submeter-modal-text.tone-high { border-color: #fdba74; background: #fff7ed; color: #9a3412; }
    .submeter-modal-text.tone-drop { border-color: #a5b4fc; background: #eef2ff; color: #4338ca; }
    .submeter-modal-text.tone-warning { border-color: #fcd34d; background: #fffbeb; color: #92400e; }
    .submeter-modal-text.tone-normal { border-color: #86efac; background: #f0fdf4; color: #166534; }
    .submeter-modal-text.tone-none { border-color: #cbd5e1; background: #f8fafc; color: #334155; }
    .submeter-modal-foot { margin-top: 14px; padding: 14px 24px 18px; display: flex; justify-content: flex-end; border-top: 1px solid #e2e8f0; background: #f8fafc; }

    body.dark-mode .submeter-panel,
    body.dark-mode .submeter-sensor-panel,
    body.dark-mode .submeter-modal-card {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .report-card-container {
        background: #111827;
        box-shadow: none;
    }

    body.dark-mode .submeter-title,
    body.dark-mode .submeter-sensor-title,
    body.dark-mode .submeter-sensor-stat-value,
    body.dark-mode .submeter-name,
    body.dark-mode .submeter-table td,
    body.dark-mode .submeter-table th,
    body.dark-mode .submeter-modal-title {
        color: #e2e8f0;
    }

    body.dark-mode .submeter-subtitle,
    body.dark-mode .submeter-sensor-subtitle,
    body.dark-mode .submeter-meta,
    body.dark-mode .submeter-modal-meta {
        color: #94a3b8;
    }
    body.dark-mode .submeter-modal-head {
        background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
        border-color: #334155;
    }
    body.dark-mode .submeter-modal-foot {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .submeter-modal-close {
        background: #0f172a;
        border-color: #334155;
        color: #cbd5e1;
    }
    body.dark-mode .submeter-modal-close:hover {
        background: #1e293b;
        border-color: #475569;
    }
    body.dark-mode .submeter-modal-badge {
        background: #1e3a8a;
        border-color: #3b82f6;
        color: #dbeafe;
    }

    body.dark-mode .ai-rec-btn {
        background: #1e3a8a;
        border-color: #3b82f6;
        color: #dbeafe;
    }
    body.dark-mode .ai-rec-btn:hover {
        background: #1d4ed8;
        border-color: #60a5fa;
    }

    body.dark-mode .submeter-filter,
    body.dark-mode .submeter-sensor-head,
    body.dark-mode .submeter-table thead th {
        background: #0f172a;
        border-color: #334155;
    }

    body.dark-mode .submeter-sensor-tab,
    body.dark-mode .submeter-sensor-stat {
        background: #111827;
        border-color: #334155;
        color: #cbd5e1;
    }

    body.dark-mode .submeter-sensor-tab.active {
        background: #164e63;
        border-color: #155e75;
        color: #cffafe;
    }

    body.dark-mode .submeter-table-shell {
        border-color: #334155;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .03), 0 10px 26px rgba(2, 6, 23, .35);
    }

    body.dark-mode .submeter-table-wrap {
        background: linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
        scrollbar-color: #475569 #0f172a;
    }

    body.dark-mode .submeter-table-wrap::-webkit-scrollbar-track { background: #0f172a; }
    body.dark-mode .submeter-table-wrap::-webkit-scrollbar-thumb {
        background: #475569;
        border: 2px solid #0f172a;
    }

    body.dark-mode .submeter-table tbody tr:nth-child(even):not(.critical):not(.high):not(.warning) {
        background: #121b2b;
    }

    body.dark-mode .submeter-table tbody tr:nth-child(even):not(.critical):not(.high):not(.warning) .sticky-col {
        background: #121b2b;
    }

    body.dark-mode .submeter-table tbody tr:hover:not(.critical):not(.high):not(.warning) {
        background: #182437;
    }

    body.dark-mode .submeter-table tbody tr:hover:not(.critical):not(.high):not(.warning) .sticky-col {
        background: #182437;
    }

    body.dark-mode .submeter-row.critical { background: #3b1f29; }
    body.dark-mode .submeter-row.critical .sticky-col { background: #3b1f29; }
    body.dark-mode .submeter-row.high { background: #431f0f; }
    body.dark-mode .submeter-row.high .sticky-col { background: #431f0f; }
    body.dark-mode .submeter-row.warning { background: #3a3319; }
    body.dark-mode .submeter-row.warning .sticky-col { background: #3a3319; }

    body.dark-mode .submeter-table tbody .sticky-col {
        background: #111827;
        box-shadow: inset -1px 0 0 #334155;
    }

    body.dark-mode .submeter-table thead .sticky-col {
        box-shadow: inset -1px 0 0 #334155;
    }

    body.dark-mode .submeter-table td,
    body.dark-mode .submeter-table th {
        border-color: #334155;
    }

    body.dark-mode .submeter-input {
        background: #0b1220;
        border-color: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .submeter-input::placeholder {
        color: #64748b;
    }

    body.dark-mode .sm-btn.neutral,
    body.dark-mode .sm-btn.soft {
        background: #1f2937;
        border-color: #334155;
        color: #cbd5e1;
    }
    body.dark-mode .baseline-pill.norm-day { background: #1e3a8a; color: #dbeafe; border-color: #3b82f6; }
    body.dark-mode .baseline-pill.ma3 { background: #0c4a6e; color: #dbeafe; border-color: #38bdf8; }
    body.dark-mode .baseline-pill.seasonal { background: #4c1d95; color: #ede9fe; border-color: #8b5cf6; }
    body.dark-mode .baseline-pill.ma6 { background: #713f12; color: #fef9c3; border-color: #f59e0b; }
    body.dark-mode .baseline-pill.equipment { background: #7c2d12; color: #ffedd5; border-color: #fb923c; }
    body.dark-mode .baseline-pill.configured { background: #14532d; color: #dcfce7; border-color: #22c55e; }
    body.dark-mode .baseline-pill.na { background: #334155; color: #cbd5e1; border-color: #475569; }

    /* Enhanced monitoring overview */
    .report-card-container {
        background: linear-gradient(145deg,#ffffff 0%,#f8fbff 60%,#eef4ff 100%);
        border: 1px solid #dbe5f2;
        border-radius: 24px;
        padding: 28px 30px 32px;
        box-shadow: 0 18px 45px rgba(15,23,42,.10);
    }
    .submeter-head { align-items:center; padding:2px 0 4px; }
    .submeter-heading { display:flex; align-items:center; gap:14px; min-width:0; }
    .submeter-heading-icon { width:48px; height:48px; flex:0 0 48px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; color:#fff; background:linear-gradient(135deg,#2563eb,#06b6d4); box-shadow:0 9px 20px rgba(37,99,235,.20); }
    .submeter-title { color:#0f172a; font-size:1.58rem; font-weight:900; letter-spacing:-.025em; }
    .submeter-subtitle { font-size:.88rem; font-weight:600; }
    .submeter-context-chips { display:flex; gap:7px; flex-wrap:wrap; margin-top:8px; }
    .submeter-context-chip { display:inline-flex; align-items:center; gap:6px; border:1px solid #dbe5f2; border-radius:999px; padding:5px 9px; background:#fff; color:#475569; font-size:.7rem; font-weight:800; }
    .sm-btn { gap:7px; min-height:42px; }

    .submeter-kpis { grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .submeter-kpi { position:relative; min-height:122px; padding:17px 18px; overflow:hidden; background:#fff !important; border-radius:16px; box-shadow:0 8px 22px rgba(15,23,42,.06); }
    .submeter-kpi::before { content:""; position:absolute; left:0; top:0; right:0; height:4px; background:var(--kpi-color,#2563eb); }
    .submeter-kpi-row { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
    .submeter-kpi-icon { width:40px; height:40px; flex:0 0 40px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; color:var(--kpi-color,#2563eb); background:var(--kpi-soft,#eff6ff); }
    .submeter-kpi .label { color:#64748b !important; font-size:.68rem; }
    .submeter-kpi .number { margin-top:12px; color:#0f172a; font-size:1.75rem; }
    .submeter-kpi .value { margin-top:12px; color:#0f172a; font-size:1.05rem; font-weight:900; }
    .submeter-kpi-note { margin-top:4px; color:#64748b; font-size:.7rem; font-weight:650; }
    .submeter-kpi.critical { --kpi-color:#e11d48; --kpi-soft:#fff1f2; }
    .submeter-kpi.increase { --kpi-color:#0891b2; --kpi-soft:#ecfeff; }
    .submeter-kpi.facilities { --kpi-color:#7c3aed; --kpi-soft:#f5f3ff; }
    .submeter-kpi.monitored { --kpi-color:#059669; --kpi-soft:#ecfdf5; }

    .submeter-sensor-panel,.submeter-panel { border-radius:18px; box-shadow:0 10px 28px rgba(15,23,42,.07); }
    .submeter-sensor-head { display:block; padding:18px; background:linear-gradient(135deg,#ffffff,#f8fbff); }
    .submeter-sensor-topline,.submeter-records-head { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; }
    .submeter-section-heading { display:flex; align-items:center; gap:11px; }
    .submeter-section-icon { width:40px; height:40px; flex:0 0 40px; display:inline-flex; align-items:center; justify-content:center; border-radius:11px; background:#ecfeff; color:#0891b2; }
    .sensor-health-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 9px; border-radius:999px; background:#ecfdf5; color:#047857; font-size:.7rem; font-weight:850; }
    .sensor-health-chip.no-data { background:#f1f5f9; color:#64748b; }
    .submeter-sensor-controls { margin-top:16px; padding-top:14px; border-top:1px solid #e2e8f0; justify-content:space-between; }
    .submeter-sensor-picker { flex:1; grid-template-columns:repeat(2,minmax(220px,1fr)); max-width:720px; }
    .submeter-sensor-picker select { min-height:44px; font-size:.82rem; }
    .submeter-sensor-tab { min-height:44px; padding:9px 14px; }
    .submeter-sensor-body { padding:18px; }
    .submeter-sensor-stats { grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .submeter-sensor-stat { padding:13px 14px; background:#f8fafc; border-color:#dbe5f2; }
    .submeter-sensor-stat-value { font-size:1.35rem; }
    .submeter-sensor-chart { height:320px; max-height:320px; border-top:1px solid #eef2f7; padding-top:10px; }
    .submeter-chart-notice { display:flex; align-items:center; gap:8px; margin:2px 0 12px; padding:9px 11px; border:1px solid #fde68a; border-radius:10px; background:#fffbeb; color:#92400e; font-size:.76rem; font-weight:700; }
    .submeter-chart-empty { min-height:220px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:7px; border:1px dashed #cbd5e1; border-radius:13px; background:#f8fafc; color:#64748b; text-align:center; padding:20px; }
    .submeter-chart-empty i { font-size:1.6rem; color:#94a3b8; }
    .submeter-chart-empty strong { color:#334155; }

    .submeter-records-head { padding:16px 18px; border-bottom:1px solid #e2e8f0; background:#fff; }
    .submeter-records-count { display:inline-flex; align-items:center; gap:6px; padding:6px 9px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:.72rem; font-weight:850; }
    .submeter-filter { padding:14px 18px; }
    .submeter-filter-actions .sm-btn { min-width:92px; }

    .submeter-table { min-width:1160px; }
    .submeter-table col.col-submeter { width:190px; }
    .submeter-table col.col-facility { width:175px; }
    .submeter-table col.col-current,.submeter-table col.col-baseline { width:120px; }
    .submeter-table col.col-baseline-source { width:145px; }
    .submeter-table col.col-increase { width:110px; }
    .submeter-table col.col-alert { width:145px; }
    .submeter-table col.col-recommendation { width:150px; }
    .submeter-table thead th { padding:13px 12px; background:#f1f5f9; color:#334155; }
    .submeter-table td { padding:13px 12px; }
    .submeter-row { position:relative; }
    .submeter-row.critical { box-shadow:inset 4px 0 #e11d48; }
    .submeter-row.high { box-shadow:inset 4px 0 #f97316; }
    .submeter-row.warning { box-shadow:inset 4px 0 #f59e0b; }
    .submeter-identity { display:flex; align-items:flex-start; gap:10px; min-width:0; }
    .submeter-identity-icon { width:34px; height:34px; flex:0 0 34px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; background:#eff6ff; color:#2563eb; }
    .submeter-name-link { display:inline-block; transition:color .15s ease; }
    .submeter-name-link:hover { color:#2563eb; text-decoration:none; }
    .reading-value { display:block; color:#0f172a; font-size:1rem; font-weight:900; }
    .reading-unit { display:block; margin-top:2px; color:#94a3b8; font-size:.66rem; font-weight:750; text-transform:uppercase; }
    .metric-na { display:inline-flex; align-items:center; gap:5px; color:#94a3b8; font-size:.76rem; font-weight:750; }
    .baseline-pill.na { background:#fff7ed; color:#9a3412; border-color:#fed7aa; min-width:118px; }
    .alert-pill.pill-none { min-width:122px; background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
    .alert-pill { gap:6px; }
    .ai-rec-btn { width:auto; height:36px; padding:0 11px; gap:6px; border-radius:10px; font-size:.74rem; font-weight:850; }
    .ai-rec-icon { display:inline-flex; align-items:center; gap:6px; font-size:.74rem; }
    .baseline-help { display:block; margin-top:5px; color:#9a3412; font-size:.64rem; font-weight:700; }

    body.dark-mode .submeter-identity-icon { background:#1e3a8a; color:#bfdbfe; }
    body.dark-mode .reading-value { color:#e2e8f0; }
    body.dark-mode .baseline-pill.na { background:#422006; color:#fed7aa; border-color:#9a3412; }

    /* Keep all eight desktop columns inside the panel. The table previously
       retained a fixed minimum width, which clipped Recommendation on the right. */
    @media (min-width: 681px) {
        .submeter-table-wrap { overflow-x: hidden; }
        .submeter-table-shell { width: calc(100% - 20px); }
        .submeter-table { width: 100%; min-width: 0; }
        .submeter-table col.col-submeter { width: 18%; }
        .submeter-table col.col-facility { width: 16%; }
        .submeter-table col.col-current { width: 10%; }
        .submeter-table col.col-baseline { width: 10%; }
        .submeter-table col.col-baseline-source { width: 13%; }
        .submeter-table col.col-increase { width: 9%; }
        .submeter-table col.col-alert { width: 12%; }
        .submeter-table col.col-recommendation { width: 12%; }
        .submeter-table th,
        .submeter-table td { overflow-wrap: anywhere; }
        .submeter-table .ai-rec-btn { width: 100%; max-width: 132px; white-space: nowrap; }
    }
    body.dark-mode .alert-pill.pill-none { background:#1e293b; color:#cbd5e1; border-color:#475569; }

    body.dark-mode .submeter-context-chip,
    body.dark-mode .submeter-kpi,
    body.dark-mode .submeter-records-head,
    body.dark-mode .submeter-sensor-head,
    body.dark-mode .submeter-sensor-stat,
    body.dark-mode .submeter-chart-empty { background:#111827 !important; border-color:#334155; }
    body.dark-mode .submeter-kpi .number,
    body.dark-mode .submeter-kpi .value,
    body.dark-mode .submeter-chart-empty strong { color:#e2e8f0; }
    body.dark-mode .submeter-kpi .label,
    body.dark-mode .submeter-kpi-note,
    body.dark-mode .submeter-context-chip { color:#94a3b8 !important; }
    body.dark-mode .submeter-sensor-controls { border-color:#334155; }
    body.dark-mode .sensor-health-chip { background:#064e3b; color:#a7f3d0; }
    body.dark-mode .sensor-health-chip.no-data { background:#1e293b; color:#94a3b8; }
    body.dark-mode .submeter-chart-notice { background:#422006; border-color:#854d0e; color:#fde68a; }

    @media (max-width: 1200px) {
        .submeter-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .submeter-filter { grid-template-columns: repeat(2, minmax(200px,1fr)); }
        .submeter-filter-actions { grid-column: 1 / -1; }
        .submeter-sensor-controls { align-items:stretch; }
        .submeter-sensor-picker { max-width:none; width:100%; }
    }

    @media (max-width: 680px) {
        .submeter-ui,
        .submeter-ui > *,
        .submeter-head > *,
        .submeter-sensor-panel,
        .submeter-panel { min-width: 0; }
        .submeter-ui { margin: 0; }
        .report-card-container { padding: 16px; gap: 16px; border-radius: 20px; }
        .submeter-title { font-size: 1.28rem; }
        .submeter-subtitle { font-size: .92rem; line-height: 1.55; }
        .submeter-kpis { grid-template-columns: minmax(0, 1fr); }
        .submeter-heading { align-items:flex-start; }
        .submeter-heading-icon { width:42px; height:42px; flex-basis:42px; border-radius:12px; }
        .submeter-context-chips { margin-left:-56px; margin-top:12px; }
        .submeter-filter { grid-template-columns: 1fr; }
        .submeter-head-actions { width: 100%; }
        .submeter-head-actions .sm-btn { flex: 1; }
        .submeter-sensor-head { align-items: stretch; }
        .submeter-sensor-topline,.submeter-records-head { align-items:flex-start; }
        .submeter-sensor-picker { grid-template-columns:1fr; }
        .submeter-sensor-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }
        .submeter-sensor-tab { width: 100%; padding-inline: 8px; }
        .submeter-sensor-stats { grid-template-columns: minmax(0, 1fr); }
        .submeter-sensor-chart { height: 240px; max-height: 240px; }
        .submeter-table-wrap { overflow: visible; padding: 10px; }
        .submeter-table-shell { margin: 0; border: 0; overflow: visible; background: transparent; box-shadow: none; }
        .submeter-table,
        .submeter-table tbody { display: block; width: 100%; min-width: 0; }
        .submeter-table colgroup,
        .submeter-table thead { display: none; }
        .submeter-table tbody { display: grid; gap: 12px; }
        .submeter-table tbody tr.submeter-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            overflow: hidden;
            border: 1px solid #dbe4f2;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 5px 14px rgba(15, 23, 42, .06);
        }
        .submeter-table tbody tr.submeter-row.critical { background: #fef2f2; border-color: #fecaca; }
        .submeter-table tbody tr.submeter-row.high { background: #fff7ed; border-color: #fdba74; }
        .submeter-table tbody tr.submeter-row.warning { background: #fffbeb; border-color: #fde68a; }
        .submeter-table tbody tr.submeter-row td {
            position: static;
            display: grid;
            grid-template-columns: minmax(105px, .8fr) minmax(0, 1.2fr);
            align-items: center;
            gap: 10px;
            min-height: 45px;
            padding: 10px 12px;
            border: 0;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
            background: transparent;
            box-shadow: none;
        }
        .submeter-table tbody tr.submeter-row td:last-child { border-bottom: 0; }
        .submeter-table tbody tr.submeter-row td::before {
            content: attr(data-label);
            color: #64748b;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .045em;
            text-transform: uppercase;
        }
        .submeter-table tbody tr.submeter-row td:first-child {
            display: block;
            padding: 14px 12px;
            background: #f8fbff;
        }
        .submeter-table tbody tr.submeter-row td:first-child::before {
            display: block;
            margin-bottom: 5px;
        }
        .submeter-table tbody tr.submeter-row.critical td:first-child { background: #fee2e2; }
        .submeter-table tbody tr.submeter-row.high td:first-child { background: #ffedd5; }
        .submeter-table tbody tr.submeter-row.warning td:first-child { background: #fef3c7; }
        .submeter-table .facility-cell {
            display: grid;
            -webkit-line-clamp: unset;
            overflow: visible;
        }
        .submeter-table .baseline-pill,
        .submeter-table .alert-pill { justify-self: start; }
        .submeter-table .ai-rec-btn { justify-self: start; }
        .submeter-table tbody tr:not(.submeter-row) { display: block; }
        .submeter-table .submeter-empty-row { display: block; width: 100%; border: 1px solid #dbe4f2; border-radius: 12px; }
        body.dark-mode .submeter-table tbody tr.submeter-row {
            background: #111827;
            border-color: #334155;
        }
        body.dark-mode .submeter-table tbody tr.submeter-row.critical { background: #3b1f29; border-color: #7f1d1d; }
        body.dark-mode .submeter-table tbody tr.submeter-row.high { background: #431f0f; border-color: #9a3412; }
        body.dark-mode .submeter-table tbody tr.submeter-row.warning { background: #3a3319; border-color: #854d0e; }
        body.dark-mode .submeter-table tbody tr.submeter-row td { background: transparent; border-color: #334155; box-shadow: none; }
        body.dark-mode .submeter-table tbody tr.submeter-row td:first-child { background: #182437; }
        body.dark-mode .submeter-table tbody tr.submeter-row.critical td:first-child { background: #4c1d2a; }
        body.dark-mode .submeter-table tbody tr.submeter-row.high td:first-child { background: #50240f; }
        body.dark-mode .submeter-table tbody tr.submeter-row.warning td:first-child { background: #463b16; }
        body.dark-mode .submeter-table tbody tr.submeter-row td::before { color: #94a3b8; }
        .submeter-modal-head { padding: 18px 16px 10px; padding-right: 52px; }
        .submeter-modal-alert { margin: 10px 16px 0; }
        .submeter-modal-text { margin: 8px 16px 0; font-size: .95rem; }
        .submeter-modal-foot { padding: 12px 16px 14px; }
        .submeter-modal-title { font-size: 1.12rem; }
    }

    @media (max-width: 380px) {
        .report-card-container { padding: 13px; border-radius: 16px; }
        .submeter-title { font-size: 1.16rem; }
        .submeter-sensor-head,
        .submeter-sensor-body { padding: 12px; }
    }
</style>

@section('content')
@php
    $widgets = $widgets ?? [];
    $top5 = $widgets['top5HighestIncrease'] ?? collect();
    $criticalCount = $widgets['criticalAlertsThisMonth'] ?? 0;
    $facilitiesMostAlerts = $widgets['facilitiesWithMostAlerts'] ?? collect();
    $facilitiesWithAlertsCount = (int) ($widgets['facilitiesWithAlertsCount'] ?? $facilitiesMostAlerts->count());
    $sensorTrend = $sensorTrend ?? ['labels' => [], 'kwh' => [], 'total_kwh' => 0, 'reading_count' => 0];
    $selectedSensorPeriod = $selectedSensorPeriod ?? 'daily';
    $sensorReadingCount = (int) ($sensorTrend['reading_count'] ?? 0);
    $sensorTotalKwh = (float) ($sensorTrend['total_kwh'] ?? 0);
    $sensorAverageKwh = $sensorReadingCount > 0 ? $sensorTotalKwh / $sensorReadingCount : 0;
    $hasSensorData = $sensorReadingCount > 0 && count($sensorTrend['labels'] ?? []) > 0;
    $selectedMonthLabel = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
    $activeFilterCount = collect([$selectedFacility, $selectedDepartment])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="submeter-ui">
    @if(session('success'))
        <div class="submeter-flash ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="submeter-flash err">{{ session('error') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="submeter-flash warn">Please check the form fields.</div>
    @endif

    <section class="report-card-container">
        <section class="submeter-head">
            <div class="submeter-heading">
                <span class="submeter-heading-icon"><i class="fa-solid fa-network-wired"></i></span>
                <div>
                    <h2 class="submeter-title">Submeter Monitoring</h2>
                    <div class="submeter-subtitle">Track department and floor-level usage, baseline variance, and recommended actions.</div>
                    <div class="submeter-context-chips">
                        <span class="submeter-context-chip"><i class="fa-solid fa-calendar"></i> {{ $selectedMonthLabel }}</span>
                        <span class="submeter-context-chip"><i class="fa-solid fa-clock"></i> {{ ucfirst($periodType) }} records</span>
                        @if($activeFilterCount > 0)<span class="submeter-context-chip"><i class="fa-solid fa-filter"></i> {{ $activeFilterCount }} active {{ \Illuminate\Support\Str::plural('filter', $activeFilterCount) }}</span>@endif
                    </div>
                </div>
            </div>
            <div class="submeter-head-actions">
                <a class="sm-btn soft" href="{{ route('modules.submeters.alerts', ['month' => $selectedMonth, 'period_type' => $periodType, 'facility_id' => $selectedFacility]) }}"><i class="fa-solid fa-triangle-exclamation"></i> Review Alerts</a>
            </div>
        </section>

        <div class="submeter-kpis">
            <article class="submeter-kpi critical">
                <div class="submeter-kpi-row"><div><div class="label">CRITICAL ALERTS</div><div class="number">{{ $criticalCount }}</div></div><span class="submeter-kpi-icon"><i class="fa-solid fa-circle-exclamation"></i></span></div>
                <div class="submeter-kpi-note">For {{ $selectedMonthLabel }}</div>
            </article>
            <article class="submeter-kpi increase">
                <div class="submeter-kpi-row"><div><div class="label">HIGHEST INCREASES</div><div class="value">{{ $top5->count() }} flagged</div></div><span class="submeter-kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></span></div>
                <div class="submeter-kpi-note">Up to five positive variances</div>
            </article>
            <article class="submeter-kpi facilities">
                <div class="submeter-kpi-row"><div><div class="label">FACILITIES WITH ALERTS</div><div class="value">{{ $facilitiesWithAlertsCount }} facilities</div></div><span class="submeter-kpi-icon"><i class="fa-solid fa-building-circle-exclamation"></i></span></div>
                <div class="submeter-kpi-note">Facilities requiring review</div>
            </article>
            <article class="submeter-kpi monitored">
                <div class="submeter-kpi-row"><div><div class="label">MONITORED RECORDS</div><div class="number">{{ $rows->count() }}</div></div><span class="submeter-kpi-icon"><i class="fa-solid fa-gauge-high"></i></span></div>
                <div class="submeter-kpi-note">Matching the current period and filters</div>
            </article>
        </div>

        <section class="submeter-sensor-panel">
            <div class="submeter-sensor-head">
                <div class="submeter-sensor-topline">
                    <div class="submeter-section-heading">
                        <span class="submeter-section-icon"><i class="fa-solid fa-chart-column"></i></span>
                        <div><h3 class="submeter-sensor-title">Sensor Consumption Trend</h3><div class="submeter-sensor-subtitle">IoT readings grouped by the selected meter and time range.</div></div>
                    </div>
                    <span class="sensor-health-chip{{ $hasSensorData ? '' : ' no-data' }}"><i class="fa-solid {{ $hasSensorData ? 'fa-signal' : 'fa-circle-minus' }}"></i> {{ $hasSensorData ? 'Sensor data available' : 'No sensor data' }}</span>
                </div>
                <div class="submeter-sensor-controls">
                    <form method="GET" action="{{ route('modules.submeters.monitoring') }}" class="submeter-sensor-picker">
                        <input type="hidden" name="period_type" value="{{ $periodType }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="facility_id" value="{{ $selectedFacility }}">
                        <input type="hidden" name="department" value="{{ $selectedDepartment }}">
                        <input type="hidden" name="sensor_period" value="{{ $selectedSensorPeriod }}">
                        <div class="submeter-sensor-picker-field">
                            <label for="sensor_main_meter_id">Main Meter</label>
                            <select id="sensor_main_meter_id" aria-label="Select main meter">
                                @forelse($sensorMeterGroups as $meterGroup)
                                    <option value="{{ $meterGroup['id'] }}" @selected((int) $selectedSensorMainMeter === (int) $meterGroup['id'])>{{ $meterGroup['label'] }}</option>
                                @empty
                                    <option value="" disabled selected>No linked Main Meter available</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="submeter-sensor-picker-field">
                            <label for="sensor_submeter_id">Submeter</label>
                            <select id="sensor_submeter_id" name="sensor_submeter_id" onchange="this.form.submit()">
                                @forelse(($sensorMeterGroups->firstWhere('id', $selectedSensorMainMeter)['submeters'] ?? collect()) as $sensorSubmeter)
                                    <option value="{{ $sensorSubmeter->id }}" @selected((int) $selectedSensorSubmeter === (int) $sensorSubmeter->id)>{{ $sensorSubmeter->submeter_name }}</option>
                                @empty
                                    <option value="" disabled selected>No linked Submeter available</option>
                                @endforelse
                            </select>
                        </div>
                    </form>
                    <div class="submeter-sensor-tabs">
                    @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $periodKey => $periodLabel)
                        <a
                            href="{{ route('modules.submeters.monitoring', array_filter([
                                'period_type' => $periodType,
                                'month' => $selectedMonth,
                                'facility_id' => $selectedFacility,
                                'department' => $selectedDepartment,
                                'sensor_period' => $periodKey,
                                'sensor_submeter_id' => $selectedSensorSubmeter,
                            ], fn ($value) => $value !== null && $value !== '')) }}"
                            class="submeter-sensor-tab{{ $selectedSensorPeriod === $periodKey ? ' active' : '' }}"
                            @if($selectedSensorPeriod === $periodKey) aria-current="page" @endif
                        >
                            {{ $periodLabel }}
                        </a>
                    @endforeach
                    </div>
                </div>
            </div>
            <div class="submeter-sensor-body">
                <div class="submeter-sensor-stats">
                    <div class="submeter-sensor-stat">
                        <div class="submeter-sensor-stat-label">Sensor kWh</div>
                        <div class="submeter-sensor-stat-value">{{ number_format($sensorTotalKwh, 2) }}</div>
                    </div>
                    <div class="submeter-sensor-stat">
                        <div class="submeter-sensor-stat-label">Sensor Readings</div>
                        <div class="submeter-sensor-stat-value">{{ number_format($sensorReadingCount) }}</div>
                    </div>
                    <div class="submeter-sensor-stat">
                        <div class="submeter-sensor-stat-label">Average per Reading</div>
                        <div class="submeter-sensor-stat-value">{{ number_format($sensorAverageKwh, 2) }} kWh</div>
                    </div>
                </div>
                @if($sensorReadingCount === 1)
                    <div class="submeter-chart-notice"><i class="fa-solid fa-circle-info"></i> One reading is available. Add more readings to reveal a meaningful usage trend.</div>
                @endif
                @if($hasSensorData)
                    <div class="submeter-sensor-chart"><canvas id="submeterSensorChart" role="img" aria-label="{{ ucfirst($selectedSensorPeriod) }} submeter sensor consumption chart" style="display:block;width:100%;height:100%;"></canvas></div>
                @else
                    <div class="submeter-chart-empty"><i class="fa-solid fa-chart-line"></i><strong>No readings for this selection</strong><span>Choose another meter or time range, or wait for the IoT source to submit data.</span></div>
                @endif
            </div>
        </section>

        <section class="submeter-panel">
            <div class="submeter-records-head">
                <div class="submeter-section-heading"><span class="submeter-section-icon"><i class="fa-solid fa-table-list"></i></span><div><h3 class="submeter-sensor-title">Monitoring Records</h3><div class="submeter-sensor-subtitle">Compare actual consumption against the selected baseline method.</div></div></div>
                <span class="submeter-records-count"><i class="fa-solid fa-list-check"></i> {{ $rows->count() }} {{ \Illuminate\Support\Str::plural('record', $rows->count()) }}</span>
            </div>
            <form method="GET" action="{{ route('modules.submeters.monitoring') }}" class="submeter-filter">
                <input type="hidden" name="sensor_period" value="{{ $selectedSensorPeriod }}">
                <input type="hidden" name="sensor_submeter_id" value="{{ $selectedSensorSubmeter }}">
                <div class="submeter-field">
                    <label for="period_type">Period Type</label>
                    <select id="period_type" name="period_type" class="submeter-input">
                        <option value="daily" @selected($periodType === 'daily')>Daily</option>
                        <option value="weekly" @selected($periodType === 'weekly')>Weekly</option>
                        <option value="monthly" @selected($periodType === 'monthly')>Monthly</option>
                    </select>
                </div>
                <div class="submeter-field">
                    <label for="month">Month</label>
                    <input id="month" type="month" name="month" value="{{ $selectedMonth }}" class="submeter-input">
                </div>
                <div class="submeter-field">
                    <label for="facility_id">Facility</label>
                    <select id="facility_id" name="facility_id" class="submeter-input">
                        <option value="">All Facilities</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="submeter-field">
                    <label for="department">Submeter / Department</label>
                    <input id="department" type="text" name="department" value="{{ $selectedDepartment }}" placeholder="Example: Engineering Office, 2F Lighting" class="submeter-input">
                </div>
                <div class="submeter-filter-actions">
                    <button type="submit" class="sm-btn primary"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a href="{{ route('modules.submeters.monitoring') }}" class="sm-btn neutral"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>

            <div class="submeter-table-wrap">
                <div class="submeter-table-shell">
                    <table class="submeter-table">
                        <colgroup>
                            <col class="col-submeter">
                            <col class="col-facility">
                            <col class="col-current">
                            <col class="col-baseline">
                            <col class="col-baseline-source">
                            <col class="col-increase">
                            <col class="col-alert">
                            <col class="col-recommendation">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="sticky-col">Submeter Name</th>
                                <th>Facility</th>
                                <th class="num">Actual (kWh)</th>
                                <th class="num">Baseline (kWh)</th>
                                <th class="center">Baseline Method</th>
                                <th class="num">Variance (%)</th>
                                <th class="center">Alert Status</th>
                                <th class="center">Recommendation</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $row)
                            @php
                                $level = strtolower((string) ($row->monitor_alert_level ?? 'none'));
                                $increase = $row->monitor_increase_percent;
                                $hasBaselineForRow = $row->monitor_baseline_kwh !== null && (float) $row->monitor_baseline_kwh > 0;
                                $baselineSource = strtolower((string) ($row->monitor_baseline_source ?? ''));
                                $rowClass = match ($level) {
                                    'critical', 'drop_critical' => 'critical',
                                    'very_high', 'high', 'drop_high' => 'high',
                                    'warning', 'drop_warning' => 'warning',
                                    default => '',
                                };
                                [$baselineSourceLabel, $baselineSourceClass] = match ($baselineSource) {
                                    'normalized_per_day' => ['Normalized per Day', 'norm-day'],
                                    'moving_avg_3' => ['3-Period Moving Avg', 'ma3'],
                                    'seasonal_month' => ['Seasonal Pattern', 'seasonal'],
                                    'moving_avg_6' => ['6-Period Moving Avg', 'ma6'],
                                    'equipment_estimate' => ['Equipment Estimate', 'equipment'],
                                    'configured_meter' => ['Configured Baseline', 'configured'],
                                    'alert' => ['Alert Baseline', 'na'],
                                    default => ['No Baseline', 'na'],
                                };
                                $alertDisplay = match ($level) {
                                    'critical' => 'CRITICAL',
                                    'very_high' => 'VERY HIGH',
                                    'high' => 'HIGH',
                                    'warning' => 'WARNING',
                                    'drop_critical' => 'DROP CRITICAL',
                                    'drop_high' => 'DROP HIGH',
                                    'drop_warning' => 'DROP WARNING',
                                    'normal' => 'NORMAL',
                                    default => 'NOT EVALUATED',
                                };
                                $alertPillClass = match ($level) {
                                    'critical' => 'pill-critical',
                                    'very_high' => 'pill-very-high',
                                    'high' => 'pill-high',
                                    'warning' => 'pill-warning',
                                    'drop_critical' => 'pill-drop-critical',
                                    'drop_high' => 'pill-drop-high',
                                    'drop_warning' => 'pill-drop-warning',
                                    'normal' => 'pill-normal',
                                    default => 'pill-none',
                                };
                                $alertIcon = match ($level) {
                                    'critical' => 'fa-triangle-exclamation',
                                    'very_high', 'high' => 'fa-arrow-trend-up',
                                    'warning' => 'fa-circle-exclamation',
                                    'drop_critical', 'drop_high', 'drop_warning' => 'fa-arrow-trend-down',
                                    'normal' => 'fa-circle-check',
                                    default => 'fa-circle-minus',
                                };
                                $fallbackAlertForAi = match ($level) {
                                    'critical' => 'Critical',
                                    'very_high' => 'Very High',
                                    'high' => 'High',
                                    'warning' => 'Warning',
                                    'drop_critical' => 'Drop Critical',
                                    'drop_high' => 'Drop High',
                                    'drop_warning' => 'Drop Warning',
                                    'normal' => 'Normal',
                                    default => 'No Data',
                                };
                                $fallbackRecommendationForAi = match ($fallbackAlertForAi) {
                                    'Critical' => 'Critical submeter increase detected. Check department loads immediately and reduce non-essential usage this period.',
                                    'Very High' => 'Very high consumption detected. Audit major loads and operating schedules today.',
                                    'High' => 'Consumption is materially above baseline. Inspect major equipment and after-hours usage.',
                                    'Warning' => 'Submeter increase is above expected. Review operating schedule and inspect high-consumption equipment.',
                                    'Drop Critical' => 'Critical consumption drop detected. Check the meter, power availability, outage, and operating status immediately.',
                                    'Drop High' => 'Consumption is substantially below baseline. Validate the sensor and confirm whether shutdowns are intentional.',
                                    'Drop Warning' => 'Consumption is below the expected range. Verify the reading before treating the reduction as savings.',
                                    'Normal' => 'Submeter usage is within expected range. Continue monitoring and maintain current controls.',
                                    default => ($row->monitor_has_reading ?? false)
                                        ? 'A reading is available, but no valid baseline exists for comparison. Configure or compute a baseline before evaluating variance and alert status.'
                                        : 'No reading data is available for this submeter in the selected period.',
                                };
                                $insightUrl = route('modules.submeters.ai-insight', [
                                    'submeter' => $row->submeter_id,
                                    'period_type' => $periodType,
                                    'month' => $selectedMonth,
                                ]);
                            @endphp
                            <tr
                                class="submeter-row {{ $rowClass }}"
                                data-submeter-row
                                data-submeter-id="{{ (int) $row->submeter_id }}"
                                data-ai-url="{{ $insightUrl }}"
                                data-fallback-alert="{{ strtolower($fallbackAlertForAi) }}"
                                data-fallback-recommendation="{{ $fallbackRecommendationForAi }}"
                                data-submeter-name="{{ $row->submeter?->submeter_name }}"
                            >
                                <td class="sticky-col" data-label="Submeter">
                                    <div class="submeter-identity">
                                        <span class="submeter-identity-icon"><i class="fa-solid fa-gauge-high"></i></span>
                                        <div><div class="submeter-name"><a href="{{ route('modules.submeters.show', ['submeter' => $row->submeter_id, 'period_type' => $periodType, 'return_period_type' => $periodType, 'from' => 'monitoring', 'month' => $selectedMonth, 'facility_id' => $selectedFacility, 'department' => $selectedDepartment]) }}" class="submeter-name-link">{{ $row->submeter?->submeter_name }}</a></div>
                                        @if($row->monitor_has_reading ?? false)<div class="submeter-meta">{{ strtoupper($row->period_type) }} · {{ $row->periodLabel() }}</div>@else<div class="submeter-meta muted">No submitted reading for {{ $selectedMonth }}</div>@endif</div>
                                    </div>
                                </td>
                                <td class="facility-cell" data-label="Facility" title="{{ $row->submeter?->facility?->name ?? '-' }}">{{ $row->submeter?->facility?->name ?? '-' }}</td>
                                <td class="num metric" data-label="Actual (kWh)">@if($row->monitor_has_reading ?? false)<span class="reading-value">{{ number_format((float) $row->kwh_used, 2) }}</span><span class="reading-unit">kWh</span>@else<span class="metric-na">Not available</span>@endif</td>
                                <td class="num metric base" data-label="Baseline (kWh)">@if($hasBaselineForRow)<span class="reading-value">{{ number_format((float) $row->monitor_baseline_kwh, 2) }}</span><span class="reading-unit">kWh</span>@else<span class="metric-na"><i class="fa-solid fa-minus"></i> Not set</span>@endif</td>
                                <td class="center" data-label="Baseline Method">
                                    <span class="baseline-pill {{ $baselineSourceClass }}">{{ $hasBaselineForRow ? $baselineSourceLabel : 'Needs Baseline' }}</span>
                                    @if(!$hasBaselineForRow)<span class="baseline-help">Configure before evaluation</span>@endif
                                </td>
                                <td class="num metric inc {{ ($increase ?? 0) > 0 ? 'up' : 'down' }}" data-label="Variance">@if($increase !== null){{ number_format((float) $increase, 2) }}%@else<span class="metric-na" title="A baseline is required to calculate variance"><i class="fa-solid fa-minus"></i> Not available</span>@endif</td>
                                <td class="center" data-label="Alert Status">
                                    <span data-alert-pill data-alert-level="{{ strtolower($fallbackAlertForAi) }}" class="alert-pill {{ $alertPillClass }}"><i class="fa-solid {{ $alertIcon }}"></i> {{ $alertDisplay }}</span>
                                </td>
                                <td class="recommendation-cell" data-label="Recommendation">
                                    <button
                                        type="button"
                                        class="ai-rec-btn"
                                        title="View AI recommendation"
                                        aria-label="View AI recommendation"
                                        data-open-ai-modal
                                    >
                                        <span class="ai-rec-icon"><i class="fa-solid fa-wand-magic-sparkles"></i> View Insight</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="submeter-empty-row"><div class="submeter-empty-content"><i class="fa-solid fa-magnifying-glass-chart"></i><strong>No matching monitoring records</strong><span>Try changing the month, period type, facility, or search text.</span></div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </section>

    <div id="submeterAiModal" class="submeter-modal" aria-hidden="true">
        <div class="submeter-modal-card" role="dialog" aria-modal="true" aria-labelledby="submeterAiTitle">
            <button type="button" class="submeter-modal-close" onclick="closeSubmeterAiModal()" aria-label="Close AI insight"><i class="fa-solid fa-xmark"></i></button>
            <div class="submeter-modal-head">
                <div id="submeterAiBadge" class="submeter-modal-badge"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div>
                    <h3 id="submeterAiTitle" class="submeter-modal-title">AI Insight</h3>
                    <div id="submeterAiMeta" class="submeter-modal-meta">Rule-based recommendation</div>
                </div>
            </div>
            <div class="submeter-modal-alert">
                <span id="submeterAiAlert" class="alert-pill pill-none"><i class="fa-solid fa-circle-minus"></i> NOT EVALUATED</span>
            </div>
            <div id="submeterAiText" class="submeter-modal-text tone-none">No recommendation.</div>
            <div class="submeter-modal-foot">
                <button type="button" class="sm-btn primary" onclick="closeSubmeterAiModal()">Close</button>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@php
    $sensorMeterGroupsPayload = $sensorMeterGroups->map(function ($group) {
        return [
            'id' => $group['id'],
            'submeters' => $group['submeters']->map(function ($submeter) {
                return [
                    'id' => $submeter->id,
                    'name' => $submeter->submeter_name,
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp
<script>
const submeterAiCache = {};

const sensorMeterGroups = {{ Illuminate\Support\Js::from($sensorMeterGroupsPayload) }};

window.addEventListener('DOMContentLoaded', function () {
    const mainMeterSelect = document.getElementById('sensor_main_meter_id');
    const submeterSelect = document.getElementById('sensor_submeter_id');
    if (mainMeterSelect && submeterSelect) {
        mainMeterSelect.addEventListener('change', function () {
            const group = sensorMeterGroups.find((item) => String(item.id) === String(this.value));
            submeterSelect.innerHTML = '';
            (group?.submeters || []).forEach((submeter) => {
                const option = document.createElement('option');
                option.value = submeter.id;
                option.textContent = submeter.name;
                submeterSelect.appendChild(option);
            });
            if (submeterSelect.options.length > 0) {
                submeterSelect.form.submit();
            }
        });
    }

    if (typeof Chart === 'undefined') {
        return;
    }

    const sensorCanvas = document.getElementById('submeterSensorChart');
    if (!sensorCanvas) {
        return;
    }

    const sensorLabels = @json($sensorTrend['labels'] ?? []);
    const sensorKwhData = @json($sensorTrend['kwh'] ?? []);
    const sensorPeriod = @json(ucfirst((string) ($selectedSensorPeriod ?? 'daily')));

    if (window.submeterSensorChartInstance) {
        window.submeterSensorChartInstance.destroy();
    }

    window.submeterSensorChartInstance = new Chart(sensorCanvas, {
        type: 'bar',
        data: {
            labels: sensorLabels,
            datasets: [
                {
                    label: sensorPeriod + ' Sensor kWh',
                    data: sensorKwhData,
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(8, 145, 178, 0.72)',
                    borderRadius: 6,
                    maxBarThickness: 42
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 150,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return Number(value).toLocaleString();
                        }
                    }
                }
            }
        }
    });
});

function normalizeSubmeterAiAlert(level) {
    const raw = String(level || '').trim().toLowerCase().replaceAll('_', ' ');
    if (raw === 'critical') return 'Critical';
    if (raw === 'very high') return 'Very High';
    if (raw === 'high') return 'High';
    if (raw === 'warning' || raw === 'moderate') return 'Warning';
    if (raw === 'drop critical') return 'Drop Critical';
    if (raw === 'drop high') return 'Drop High';
    if (raw === 'drop warning') return 'Drop Warning';
    if (raw === 'normal' || raw === 'low') return 'Normal';
    return 'No Data';
}

function submeterAiAlertInfo(level) {
    const normalized = normalizeSubmeterAiAlert(level);
    if (normalized === 'Critical') return { label: 'CRITICAL', pillClass: 'pill-critical', tone: 'tone-critical', icon: 'fa-triangle-exclamation' };
    if (normalized === 'Very High') return { label: 'VERY HIGH', pillClass: 'pill-very-high', tone: 'tone-high', icon: 'fa-arrow-trend-up' };
    if (normalized === 'High') return { label: 'HIGH', pillClass: 'pill-high', tone: 'tone-high', icon: 'fa-arrow-trend-up' };
    if (normalized === 'Warning') return { label: 'WARNING', pillClass: 'pill-warning', tone: 'tone-warning', icon: 'fa-circle-exclamation' };
    if (normalized === 'Drop Critical') return { label: 'DROP CRITICAL', pillClass: 'pill-drop-critical', tone: 'tone-drop', icon: 'fa-arrow-trend-down' };
    if (normalized === 'Drop High') return { label: 'DROP HIGH', pillClass: 'pill-drop-high', tone: 'tone-drop', icon: 'fa-arrow-trend-down' };
    if (normalized === 'Drop Warning') return { label: 'DROP WARNING', pillClass: 'pill-drop-warning', tone: 'tone-drop', icon: 'fa-arrow-trend-down' };
    if (normalized === 'Normal') return { label: 'NORMAL', pillClass: 'pill-normal', tone: 'tone-normal', icon: 'fa-circle-check' };
    return { label: 'NOT EVALUATED', pillClass: 'pill-none', tone: 'tone-none', icon: 'fa-circle-minus' };
}

function applySubmeterModalAlert(level) {
    const info = submeterAiAlertInfo(level);
    const badge = document.getElementById('submeterAiBadge');
    const alert = document.getElementById('submeterAiAlert');
    const text = document.getElementById('submeterAiText');

    if (badge) badge.innerHTML = `<i class="fa-solid ${info.icon}"></i>`;
    if (alert) {
        alert.classList.remove('pill-critical', 'pill-very-high', 'pill-high', 'pill-warning', 'pill-drop-critical', 'pill-drop-high', 'pill-drop-warning', 'pill-normal', 'pill-none');
        alert.classList.add(info.pillClass);
        alert.innerHTML = `<i class="fa-solid ${info.icon}"></i> ${info.label}`;
    }
    if (text) {
        text.classList.remove('tone-critical', 'tone-high', 'tone-warning', 'tone-drop', 'tone-normal', 'tone-none');
        text.classList.add(info.tone);
    }
}

function updateSubmeterAlertPill(submeterId, level) {
    const row = document.querySelector(`[data-submeter-row][data-submeter-id="${submeterId}"]`);
    if (!row) return;
    const pill = row.querySelector('[data-alert-pill]');
    if (!pill) return;
    const info = submeterAiAlertInfo(level);
    pill.classList.remove('pill-critical', 'pill-very-high', 'pill-high', 'pill-warning', 'pill-drop-critical', 'pill-drop-high', 'pill-drop-warning', 'pill-normal', 'pill-none');
    pill.classList.add(info.pillClass);
    pill.innerHTML = `<i class="fa-solid ${info.icon}"></i> ${info.label}`;
    pill.dataset.alertLevel = normalizeSubmeterAiAlert(level).toLowerCase();

    row.classList.remove('critical', 'high', 'warning');
    const normalized = normalizeSubmeterAiAlert(level);
    if (normalized === 'Critical' || normalized === 'Drop Critical') row.classList.add('critical');
    if (normalized === 'Very High' || normalized === 'High' || normalized === 'Drop High') row.classList.add('high');
    if (normalized === 'Warning' || normalized === 'Drop Warning') row.classList.add('warning');
}

function updateSubmeterRecommendationText(submeterId, recommendation, source) {
    const row = document.querySelector(`[data-submeter-row][data-submeter-id="${submeterId}"]`);
    if (!row) return;

    const recommendationEl = row.querySelector('[data-ai-recommendation]');
    if (recommendationEl) {
        recommendationEl.textContent = recommendation || 'No recommendation.';
    }

    const sourceEl = row.querySelector('[data-ai-source]');
    if (sourceEl) {
        sourceEl.textContent = source === 'ai' ? 'AI recommendation' : 'Rule-based recommendation';
    }
}

async function fetchSubmeterAiInsight(submeterId, fallbackAlert, fallbackRecommendation, insightUrl) {
    if (submeterAiCache[submeterId]) {
        return submeterAiCache[submeterId];
    }

    if (!insightUrl) {
        const fallback = {
            recommendation: fallbackRecommendation || 'No recommendation.',
            alertLevel: normalizeSubmeterAiAlert(fallbackAlert),
            source: 'rules',
        };
        submeterAiCache[submeterId] = fallback;
        return fallback;
    }

    try {
        const response = await fetch(insightUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch AI insight');
        }

        const data = await response.json();
        const recommendation = String((data && data.recommendation) ? data.recommendation : '').trim();
        const alertLevel = normalizeSubmeterAiAlert((data && data.alert_level) ? data.alert_level : fallbackAlert);
        const source = String((data && data.recommendation_source) ? data.recommendation_source : 'rules').toLowerCase();

        const resolved = {
            recommendation: recommendation !== '' ? recommendation : (fallbackRecommendation || 'No recommendation.'),
            alertLevel,
            source,
        };
        submeterAiCache[submeterId] = resolved;
        return resolved;
    } catch (error) {
        const fallback = {
            recommendation: fallbackRecommendation || 'No recommendation.',
            alertLevel: normalizeSubmeterAiAlert(fallbackAlert),
            source: 'rules',
        };
        submeterAiCache[submeterId] = fallback;
        return fallback;
    }
}

async function openSubmeterAiModal(submeterId, submeterName, fallbackAlert, fallbackRecommendation, insightUrl) {
    const modal = document.getElementById('submeterAiModal');
    const title = document.getElementById('submeterAiTitle');
    const meta = document.getElementById('submeterAiMeta');
    const text = document.getElementById('submeterAiText');
    if (!modal || !title || !meta || !text) return;

    title.textContent = `AI Insight: ${submeterName}`;
    modal.dataset.submeterId = String(submeterId);
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    meta.textContent = 'Rule-based recommendation';
    text.textContent = fallbackRecommendation || 'No recommendation.';
    applySubmeterModalAlert(fallbackAlert);

    if (!submeterAiCache[submeterId]) {
        meta.textContent = 'Loading AI insight...';
    }

    const insight = await fetchSubmeterAiInsight(submeterId, fallbackAlert, fallbackRecommendation, insightUrl);
    updateSubmeterAlertPill(submeterId, insight.alertLevel);
    updateSubmeterRecommendationText(submeterId, insight.recommendation, insight.source);

    if (modal.dataset.submeterId === String(submeterId)) {
        text.textContent = insight.recommendation;
        applySubmeterModalAlert(insight.alertLevel);
        meta.textContent = insight.source === 'ai' ? 'AI recommendation + AI alert' : 'Rule-based recommendation';
    }
}

function closeSubmeterAiModal() {
    const modal = document.getElementById('submeterAiModal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

function openSubmeterAiModalFromButton(button) {
    if (!button) return;
    const row = button.closest('[data-submeter-row]');
    if (!row) return;

    const submeterId = Number(row.getAttribute('data-submeter-id') || 0);
    if (!submeterId) return;

    const submeterName = row.getAttribute('data-submeter-name') || 'Submeter';
    const fallbackAlert = row.getAttribute('data-fallback-alert') || 'No Data';
    const fallbackRecommendation = row.getAttribute('data-fallback-recommendation') || 'No recommendation.';
    const insightUrl = row.getAttribute('data-ai-url') || '';

    openSubmeterAiModal(
        submeterId,
        submeterName,
        fallbackAlert,
        fallbackRecommendation,
        insightUrl
    );
}

window.addEventListener('click', function (event) {
    const modal = document.getElementById('submeterAiModal');
    if (modal && event.target === modal) closeSubmeterAiModal();
});

window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeSubmeterAiModal();
});

async function prefetchSubmeterAiAlerts() {
    const rows = Array.from(document.querySelectorAll('[data-submeter-row][data-ai-url]'));
    if (rows.length === 0) return;

    for (const row of rows) {
        const submeterId = Number(row.getAttribute('data-submeter-id') || 0);
        if (!submeterId) continue;
        const insightUrl = row.getAttribute('data-ai-url') || '';
        const fallbackAlert = row.getAttribute('data-fallback-alert') || 'No Data';
        const fallbackRecommendation = row.getAttribute('data-fallback-recommendation') || 'No recommendation.';

        const insight = await fetchSubmeterAiInsight(submeterId, fallbackAlert, fallbackRecommendation, insightUrl);
        updateSubmeterAlertPill(submeterId, insight.alertLevel);
        updateSubmeterRecommendationText(submeterId, insight.recommendation, insight.source);
    }
}

window.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-open-ai-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            openSubmeterAiModalFromButton(button);
        });
    });
    prefetchSubmeterAiAlerts();
});
</script>
@endsection
