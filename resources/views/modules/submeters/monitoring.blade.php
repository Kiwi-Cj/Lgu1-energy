@extends('layouts.qc-admin')
@section('title', 'Submeter Monitoring')

<style>
    .submeter-ui { padding: 16px; display: grid; gap: 14px; }
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
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(31, 38, 135, 0.06);
        border: 1px solid #e2e8f0;
        padding: 20px;
        display: grid;
        gap: 14px;
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

    .submeter-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
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

    .submeter-table tbody tr:nth-child(even):not(.critical):not(.warning) {
        background: #fbfdff;
    }

    .submeter-table tbody tr:nth-child(even):not(.critical):not(.warning) .sticky-col {
        background: #fbfdff;
    }

    .submeter-table tbody tr:hover:not(.critical):not(.warning) {
        background: #f4f8ff;
    }

    .submeter-table tbody tr:hover:not(.critical):not(.warning) .sticky-col {
        background: #f4f8ff;
    }

    .submeter-row.critical { background: #fef2f2; }
    .submeter-row.critical .sticky-col { background: #fef2f2; }
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
    .pill-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .pill-normal { background: #dcfce7; color: #166534; border-color: #86efac; }
    .pill-none { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
    .baseline-pill { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 5px 10px; font-size: .73rem; font-weight: 800; border: 1px solid transparent; min-width: 104px; }
    .baseline-pill.norm-day { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .baseline-pill.ma3 { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .baseline-pill.seasonal { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .baseline-pill.ma6 { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
    .baseline-pill.equipment { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .baseline-pill.na { background: #e2e8f0; color: #475569; border-color: #cbd5e1; }

    .submeter-empty-row {
        padding: 26px 14px;
        text-align: center;
        color: #64748b;
        font-weight: 600;
        background: #fcfdff;
    }

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
    .submeter-modal-text.tone-warning { border-color: #fcd34d; background: #fffbeb; color: #92400e; }
    .submeter-modal-text.tone-normal { border-color: #86efac; background: #f0fdf4; color: #166534; }
    .submeter-modal-text.tone-none { border-color: #cbd5e1; background: #f8fafc; color: #334155; }
    .submeter-modal-foot { margin-top: 14px; padding: 14px 24px 18px; display: flex; justify-content: flex-end; border-top: 1px solid #e2e8f0; background: #f8fafc; }

    .submeter-encode { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px; }
    .submeter-encode h3 { margin: 0 0 10px; color: #1e293b; font-weight: 800; }
    .submeter-encode-form { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 10px; }
    .submeter-encode-note { margin-top: 8px; color: #64748b; font-size: .85rem; }

    body.dark-mode .submeter-panel,
    body.dark-mode .submeter-encode,
    body.dark-mode .submeter-modal-card {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .report-card-container {
        background: #0f172a;
        border: 1px solid #1f2937;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.55);
    }

    body.dark-mode .submeter-title,
    body.dark-mode .submeter-name,
    body.dark-mode .submeter-table td,
    body.dark-mode .submeter-table th,
    body.dark-mode .submeter-encode h3,
    body.dark-mode .submeter-modal-title {
        color: #e2e8f0;
    }

    body.dark-mode .submeter-subtitle,
    body.dark-mode .submeter-meta,
    body.dark-mode .submeter-encode-note,
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
    body.dark-mode .submeter-table thead th {
        background: #0f172a;
        border-color: #334155;
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

    body.dark-mode .submeter-table tbody tr:nth-child(even):not(.critical):not(.warning) {
        background: #121b2b;
    }

    body.dark-mode .submeter-table tbody tr:nth-child(even):not(.critical):not(.warning) .sticky-col {
        background: #121b2b;
    }

    body.dark-mode .submeter-table tbody tr:hover:not(.critical):not(.warning) {
        background: #182437;
    }

    body.dark-mode .submeter-table tbody tr:hover:not(.critical):not(.warning) .sticky-col {
        background: #182437;
    }

    body.dark-mode .submeter-row.critical { background: #3b1f29; }
    body.dark-mode .submeter-row.critical .sticky-col { background: #3b1f29; }
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
    body.dark-mode .baseline-pill.na { background: #334155; color: #cbd5e1; border-color: #475569; }

    @media (max-width: 1200px) {
        .submeter-filter { grid-template-columns: repeat(2, minmax(200px,1fr)); }
        .submeter-filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 680px) {
        .submeter-ui { padding: 10px; }
        .submeter-title { font-size: 1.28rem; }
        .submeter-filter { grid-template-columns: 1fr; }
        .submeter-head-actions { width: 100%; }
        .submeter-head-actions .sm-btn { flex: 1; }
        .submeter-table-shell { margin: 8px; }
        .submeter-modal-head { padding: 18px 16px 10px; padding-right: 52px; }
        .submeter-modal-alert { margin: 10px 16px 0; }
        .submeter-modal-text { margin: 8px 16px 0; font-size: .95rem; }
        .submeter-modal-foot { padding: 12px 16px 14px; }
        .submeter-modal-title { font-size: 1.12rem; }
    }
</style>

@section('content')
@php
    $widgets = $widgets ?? [];
    $top5 = $widgets['top5HighestIncrease'] ?? collect();
    $criticalCount = $widgets['criticalAlertsThisMonth'] ?? 0;
    $facilitiesMostAlerts = $widgets['facilitiesWithMostAlerts'] ?? collect();
    $submetersForEncode = $submetersForEncode ?? ($submeters ?? collect());
@endphp

<div class="submeter-ui">
    @if(session('success'))
        <div class="submeter-flash ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="submeter-flash err">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="submeter-flash warn">Please check the form fields.</div>
    @endif

    @php
        $energyTab = 'sub';
    @endphp
    @include('layouts.partials.energy_monitoring_switcher')

    <section class="report-card-container">
        <section class="submeter-head">
            <div>
                <h2 class="submeter-title">Submeter Monitoring, Baseline, and Alerts</h2>
                <div class="submeter-subtitle">Track department and floor-level usage, baseline variance, and recommended actions.</div>
            </div>
            <div class="submeter-head-actions">
                <a class="sm-btn soft" href="{{ route('modules.submeters.alerts', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}">View Alerts</a>
            </div>
        </section>

        <div class="submeter-kpis">
            <article class="submeter-kpi alert">
                <div class="label">CRITICAL ALERTS (SELECTED MONTH)</div>
                <div class="number">{{ $criticalCount }}</div>
            </article>
            <article class="submeter-kpi top">
                <div class="label">TOP 5 HIGHEST INCREASE</div>
                <div class="value">{{ $top5->count() }} submeters flagged</div>
            </article>
            <article class="submeter-kpi fac">
                <div class="label">FACILITIES WITH ALERTS</div>
                <div class="value">{{ $facilitiesMostAlerts->count() }} facilities</div>
            </article>
        </div>

        <section class="submeter-panel">
            <form method="GET" action="{{ route('modules.submeters.monitoring') }}" class="submeter-filter">
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
                    <button type="submit" class="sm-btn primary">Filter</button>
                    <a href="{{ route('modules.submeters.monitoring') }}" class="sm-btn neutral">Reset</a>
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
                                $baselineSource = strtolower((string) ($row->monitor_baseline_source ?? ''));
                                $rowClass = match ($level) {
                                    'critical' => 'critical',
                                    'warning' => 'warning',
                                    default => '',
                                };
                                [$baselineSourceLabel, $baselineSourceClass] = match ($baselineSource) {
                                    'normalized_per_day' => ['Normalized per Day', 'norm-day'],
                                    'moving_avg_3' => ['3-Period Moving Avg', 'ma3'],
                                    'seasonal_month' => ['Seasonal Pattern', 'seasonal'],
                                    'moving_avg_6' => ['6-Period Moving Avg', 'ma6'],
                                    'equipment_estimate' => ['Equipment Estimate', 'equipment'],
                                    'alert' => ['Alert Baseline', 'na'],
                                    default => ['No Baseline', 'na'],
                                };
                                $alertDisplay = match ($level) {
                                    'critical' => 'CRITICAL',
                                    'warning' => 'WARNING',
                                    'normal' => 'NORMAL',
                                    default => 'NO DATA',
                                };
                                $alertPillClass = match ($level) {
                                    'critical' => 'pill-critical',
                                    'warning' => 'pill-warning',
                                    'normal' => 'pill-normal',
                                    default => 'pill-none',
                                };
                                $fallbackAlertForAi = match ($level) {
                                    'critical' => 'Critical',
                                    'warning' => 'Warning',
                                    default => (($row->monitor_has_reading ?? false) ? 'Normal' : 'No Data'),
                                };
                                $fallbackRecommendationForAi = match ($fallbackAlertForAi) {
                                    'Critical' => 'Critical submeter increase detected. Check department loads immediately and reduce non-essential usage this period.',
                                    'Warning' => 'Submeter increase is above expected. Review operating schedule and inspect high-consumption equipment.',
                                    'Normal' => 'Submeter usage is within expected range. Continue monitoring and maintain current controls.',
                                    default => 'No reading data is available for this submeter in the selected period.',
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
                                <td class="sticky-col">
                                    <div class="submeter-name">
                                        <a href="{{ route('modules.submeters.show', $row->submeter_id) }}" class="submeter-name-link">{{ $row->submeter?->submeter_name }}</a>
                                    </div>
                                    @if($row->monitor_has_reading ?? false)
                                        <div class="submeter-meta">{{ strtoupper($row->period_type) }} | {{ $row->periodLabel() }}</div>
                                    @else
                                        <div class="submeter-meta muted">No submitted reading for {{ $selectedMonth }}</div>
                                    @endif
                                </td>
                                <td class="facility-cell" title="{{ $row->submeter?->facility?->name ?? '-' }}">{{ $row->submeter?->facility?->name ?? '-' }}</td>
                                <td class="num metric">{{ ($row->monitor_has_reading ?? false) ? number_format((float) $row->kwh_used, 2) : '-' }}</td>
                                <td class="num metric base">{{ $row->monitor_baseline_kwh !== null ? number_format((float) $row->monitor_baseline_kwh, 2) : '-' }}</td>
                                <td class="center">
                                    <span class="baseline-pill {{ $baselineSourceClass }}">{{ $baselineSourceLabel }}</span>
                                </td>
                                <td class="num metric inc {{ ($increase ?? 0) > 0 ? 'up' : 'down' }}">{{ $increase !== null ? number_format((float) $increase, 2) . '%' : '-' }}</td>
                                <td class="center">
                                    <span data-alert-pill data-alert-level="{{ strtolower($fallbackAlertForAi) }}" class="alert-pill {{ $alertPillClass }}">{{ $alertDisplay }}</span>
                                </td>
                                <td class="recommendation-cell">
                                    <button
                                        type="button"
                                        class="ai-rec-btn"
                                        title="View AI recommendation"
                                        aria-label="View AI recommendation"
                                        data-open-ai-modal
                                    >
                                        <span class="ai-rec-icon">AI</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="submeter-empty-row">No submeter records match the selected filters.</td>
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
            <button type="button" class="submeter-modal-close" onclick="closeSubmeterAiModal()">&times;</button>
            <div class="submeter-modal-head">
                <div id="submeterAiBadge" class="submeter-modal-badge">i</div>
                <div>
                    <h3 id="submeterAiTitle" class="submeter-modal-title">AI Insight</h3>
                    <div id="submeterAiMeta" class="submeter-modal-meta">Rule-based recommendation</div>
                </div>
            </div>
            <div class="submeter-modal-alert">
                <span id="submeterAiAlert" class="alert-pill pill-none">NONE</span>
            </div>
            <div id="submeterAiText" class="submeter-modal-text tone-none">No recommendation.</div>
            <div class="submeter-modal-foot">
                <button type="button" class="sm-btn primary" onclick="closeSubmeterAiModal()">Close</button>
            </div>
        </div>
    </div>

</div>

<script>
const submeterAiCache = {};

function normalizeSubmeterAiAlert(level) {
    const raw = String(level || '').trim().toLowerCase();
    if (raw === 'critical') return 'Critical';
    if (raw === 'warning' || raw === 'moderate') return 'Warning';
    if (raw === 'normal' || raw === 'none' || raw === 'low') return 'Normal';
    return 'No Data';
}

function submeterAiAlertInfo(level) {
    const normalized = normalizeSubmeterAiAlert(level);
    if (normalized === 'Critical') return { label: 'CRITICAL', pillClass: 'pill-critical', tone: 'tone-critical', icon: '!' };
    if (normalized === 'Warning') return { label: 'WARNING', pillClass: 'pill-warning', tone: 'tone-warning', icon: '!' };
    if (normalized === 'Normal') return { label: 'NORMAL', pillClass: 'pill-normal', tone: 'tone-normal', icon: 'i' };
    return { label: 'NONE', pillClass: 'pill-none', tone: 'tone-none', icon: 'i' };
}

function applySubmeterModalAlert(level) {
    const info = submeterAiAlertInfo(level);
    const badge = document.getElementById('submeterAiBadge');
    const alert = document.getElementById('submeterAiAlert');
    const text = document.getElementById('submeterAiText');

    if (badge) badge.textContent = info.icon;
    if (alert) {
        alert.classList.remove('pill-critical', 'pill-warning', 'pill-normal', 'pill-none');
        alert.classList.add(info.pillClass);
        alert.textContent = info.label;
    }
    if (text) {
        text.classList.remove('tone-critical', 'tone-warning', 'tone-normal', 'tone-none');
        text.classList.add(info.tone);
    }
}

function updateSubmeterAlertPill(submeterId, level) {
    const row = document.querySelector(`[data-submeter-row][data-submeter-id="${submeterId}"]`);
    if (!row) return;
    const pill = row.querySelector('[data-alert-pill]');
    if (!pill) return;
    const info = submeterAiAlertInfo(level);
    pill.classList.remove('pill-critical', 'pill-warning', 'pill-normal', 'pill-none');
    pill.classList.add(info.pillClass);
    pill.textContent = info.label;
    pill.dataset.alertLevel = normalizeSubmeterAiAlert(level).toLowerCase();

    row.classList.remove('critical', 'warning');
    const normalized = normalizeSubmeterAiAlert(level);
    if (normalized === 'Critical') row.classList.add('critical');
    if (normalized === 'Warning') row.classList.add('warning');
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
