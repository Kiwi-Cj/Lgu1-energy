@extends('layouts.qc-admin')
@section('title', 'Energy Monitoring Dashboard')

<style>
    .skip-link {
        position: absolute;
        left: -999px;
        top: 10px;
        background: #3762c8;
        color: #fff;
        padding: 8px 16px;
        z-index: 10000;
        border-radius: 6px;
        font-weight: 600;
        transition: left 0.2s;
    }
    .skip-link:focus { left: 10px; }

    .energy-monitor-page .report-card {
        background: linear-gradient(165deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 16px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        padding: 30px;
        border: 1px solid #e7eef8;
        margin-bottom: 2rem;
    }

    .energy-monitor-page .monitor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .energy-monitor-page .monitor-title {
        margin: 0;
        font-size: 1.8rem;
        color: #1e293b;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .energy-monitor-page .monitor-title-accent {
        color: #2563eb;
    }

    .energy-monitor-page .monitor-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 1rem;
    }

    .energy-monitor-page .search-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .energy-monitor-page .search-field {
        position: relative;
    }

    .energy-monitor-page .search-input {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 10px 10px 10px 35px;
        font-size: 0.9rem;
        width: 220px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #fff;
        color: #0f172a;
    }

    .energy-monitor-page .search-input:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
    }

    .energy-monitor-page .search-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .energy-monitor-page .search-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
    }

    .energy-monitor-page .clear-link {
        color: #e11d48;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .energy-monitor-page .overview-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .energy-monitor-page .metric-card {
        padding: 20px;
        border-radius: 14px;
        border-left: 4px solid transparent;
        border: 1px solid transparent;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    .energy-monitor-page .metric-card .metric-label {
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.4px;
    }

    .energy-monitor-page .metric-card .metric-value {
        font-weight: 800;
        font-size: 1.8rem;
        color: #1e293b;
    }

    .energy-monitor-page .metric-facilities {
        background: linear-gradient(140deg, #f4f9ff, #f8fbff);
        border-color: #dbeafe;
        border-left-color: #2563eb;
    }
    .energy-monitor-page .metric-facilities .metric-label { color: #64748b; }

    .energy-monitor-page .metric-alert {
        background: linear-gradient(140deg, #fff3f5, #fff7f8);
        border-color: #fecdd3;
        border-left-color: #e11d48;
    }
    .energy-monitor-page .metric-alert .metric-label { color: #e11d48; }

    .energy-monitor-page .metric-cost {
        background: linear-gradient(140deg, #f2fdf7, #f8fffb);
        border-color: #bbf7d0;
        border-left-color: #16a34a;
    }
    .energy-monitor-page .metric-cost .metric-label { color: #166534; }

    .energy-monitor-page .monitor-table-wrap {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
    }

    .energy-monitor-page .monitor-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1000px;
    }

    .energy-monitor-page .monitor-table thead tr {
        background: #f1f5f9;
    }

    .energy-monitor-page .monitor-table th {
        padding: 15px;
        color: #475569;
        font-weight: 700;
        text-align: center;
        border-bottom: 1px solid #e2e8f0;
    }

    .energy-monitor-page .monitor-table th:first-child {
        border-radius: 10px 0 0 0;
    }

    .energy-monitor-page .monitor-table th:last-child {
        border-radius: 0 10px 0 0;
    }

    .energy-monitor-page .monitor-table td {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .energy-monitor-page .monitor-row:hover {
        background: #fafcff;
    }

    .energy-monitor-page .cell-facility {
        font-weight: 700;
        color: #334155;
    }

    .energy-monitor-page .cell-type {
        color: #64748b;
    }

    .energy-monitor-page .cell-baseline {
        color: #2563eb;
        font-weight: 600;
    }

    .energy-monitor-page .trend-value {
        font-weight: 700;
    }

    .energy-monitor-page .trend-positive {
        color: #e11d48;
    }

    .energy-monitor-page .trend-normal {
        color: #16a34a;
    }

    .energy-monitor-page .unit-label {
        color: #64748b;
        font-size: 0.82rem;
    }

    .energy-monitor-page .alert-pill-level-critical {
        color: #7c1d1d;
        background: #fef2f2;
        border-color: rgba(124, 29, 29, 0.2);
    }

    .energy-monitor-page .alert-pill-level-very-high {
        color: #be123c;
        background: #fff1f2;
        border-color: rgba(190, 18, 60, 0.2);
    }

    .energy-monitor-page .alert-pill-level-high {
        color: #c2410c;
        background: #fff7ed;
        border-color: rgba(194, 65, 12, 0.2);
    }

    .energy-monitor-page .alert-pill-level-moderate,
    .energy-monitor-page .alert-pill-level-warning {
        color: #b45309;
        background: #fffbeb;
        border-color: rgba(180, 83, 9, 0.22);
    }

    .energy-monitor-page .alert-pill-level-low,
    .energy-monitor-page .alert-pill-level-normal {
        color: #166534;
        background: #f0fdf4;
        border-color: rgba(22, 101, 52, 0.2);
    }

    .energy-monitor-page .alert-pill-level-no-data {
        color: #475569;
        background: #f1f5f9;
        border-color: rgba(100, 116, 139, 0.22);
    }

    .energy-monitor-page .recommendation-btn.level-critical,
    .energy-monitor-page .recommendation-btn.level-very-high {
        color: #e11d48;
    }

    .energy-monitor-page .recommendation-btn.level-high,
    .energy-monitor-page .recommendation-btn.level-warning,
    .energy-monitor-page .recommendation-btn.level-moderate {
        color: #f59e42;
    }

    .energy-monitor-page .recommendation-btn.level-low,
    .energy-monitor-page .recommendation-btn.level-normal {
        color: #16a34a;
    }

    .energy-monitor-page .recommendation-btn.level-no-data {
        color: #64748b;
    }

    .energy-monitor-page .recommendation-icon {
        font-size: 1.3rem;
    }

    .energy-monitor-page .monitor-empty {
        padding: 50px;
        text-align: center;
        color: #94a3b8;
    }

    .energy-monitor-page .recommendation-modal {
        display: none;
        position: fixed;
        z-index: 10060;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.18);
        justify-content: center;
        align-items: center;
    }

    .energy-monitor-page .recommendation-modal-inner {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100vw;
        height: 100vh;
        padding: 18px;
    }

    .energy-monitor-page .recommendation-modal-panel {
        width: min(500px, 100%);
        background: #ffffff;
        border-radius: 22px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.16);
        border: 1px solid #e2e8f0;
        padding: 22px 22px 18px;
        position: relative;
    }

    .energy-monitor-page .recommendation-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        padding-right: 34px;
    }

    .energy-monitor-page .recommendation-title-wrap {
        min-width: 0;
    }

    .energy-monitor-page .recommendation-title {
        margin: 0;
        font-size: 1.18rem;
        font-weight: 800;
        line-height: 1.45;
        color: #0f172a;
    }

    .energy-monitor-page .recommendation-meta {
        display: none;
        margin-top: 6px;
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 600;
    }

    .energy-monitor-page .recommendation-alert-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        font-size: 1rem;
        font-weight: 800;
        flex-shrink: 0;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
    }

    .energy-monitor-page .recommendation-message-card {
        border-radius: 14px;
        padding: 16px 18px;
        border: 1px solid transparent;
        font-size: 0.96rem;
        line-height: 1.5;
        font-weight: 700;
        white-space: normal;
        word-break: break-word;
    }

    .energy-monitor-page .recommendation-close-icon {
        position: absolute;
        top: 12px;
        right: 14px;
        width: auto;
        height: auto;
        border-radius: 0;
        font-size: 1.5rem;
        line-height: 1;
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color .16s ease, transform .16s ease;
    }

    .energy-monitor-page .recommendation-close-icon:hover {
        color: #475569;
        transform: scale(1.05);
    }

    .energy-monitor-page .recommendation-close-btn {
        background: linear-gradient(90deg, #2563eb, #1d4ed8);
        color: #fff;
        padding: 9px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        box-shadow: none;
    }

    .energy-monitor-page .recommendation-footer {
        margin-top: 14px;
        display: flex;
        justify-content: flex-end;
    }

    .energy-monitor-page .monitor-alert-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .energy-monitor-page .recommendation-btn {
        background: none;
        border: none;
        font-size: 1.3rem;
        cursor: pointer;
    }

    .energy-monitor-page .pagination-wrap {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    body.dark-mode .energy-monitor-page .report-card {
        background: #0f172a !important;
        border-color: #334155 !important;
        box-shadow: 0 18px 34px rgba(2, 6, 23, 0.5);
    }

    body.dark-mode .energy-monitor-page .monitor-title,
    body.dark-mode .energy-monitor-page .metric-value,
    body.dark-mode .energy-monitor-page .monitor-table td {
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-monitor-page .monitor-subtitle,
    body.dark-mode .energy-monitor-page .metric-label,
    body.dark-mode .energy-monitor-page .monitor-table th {
        color: #94a3b8 !important;
    }

    body.dark-mode .energy-monitor-page .clear-link {
        color: #fda4af;
    }

    body.dark-mode .energy-monitor-page .search-input {
        background: #0b1220 !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .energy-monitor-page .search-input::placeholder {
        color: #64748b;
    }

    body.dark-mode .energy-monitor-page .search-btn {
        background: #1d4ed8 !important;
    }

    body.dark-mode .energy-monitor-page .search-btn:hover {
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.4);
    }

    body.dark-mode .energy-monitor-page .monitor-table thead tr {
        background: #111827 !important;
    }

    body.dark-mode .energy-monitor-page .monitor-table th,
    body.dark-mode .energy-monitor-page .monitor-table td {
        border-color: #334155 !important;
    }

    body.dark-mode .energy-monitor-page .monitor-table-wrap {
        background: #0f172a;
        border-color: #334155;
    }

    body.dark-mode .energy-monitor-page .monitor-row:hover {
        background: #1f2937 !important;
    }

    body.dark-mode .energy-monitor-page .cell-type,
    body.dark-mode .energy-monitor-page .unit-label {
        color: #94a3b8;
    }

    body.dark-mode .energy-monitor-page .cell-baseline {
        color: #93c5fd;
    }

    body.dark-mode .energy-monitor-page .trend-positive {
        color: #fb7185;
    }

    body.dark-mode .energy-monitor-page .trend-normal {
        color: #34d399;
    }

    body.dark-mode .energy-monitor-page .metric-facilities {
        background: linear-gradient(145deg, #102138, #111827) !important;
        border-color: #2f4c72 !important;
    }

    body.dark-mode .energy-monitor-page .metric-alert {
        background: linear-gradient(145deg, #321923, #111827) !important;
        border-color: #7f1d1d !important;
    }

    body.dark-mode .energy-monitor-page .metric-cost {
        background: linear-gradient(145deg, #0f2a22, #111827) !important;
        border-color: #166534 !important;
    }

    body.dark-mode .energy-monitor-page .alert-pill-level-critical {
        color: #fecaca;
        background: rgba(127, 29, 29, 0.35);
        border-color: rgba(248, 113, 113, 0.4);
    }

    body.dark-mode .energy-monitor-page .alert-pill-level-very-high,
    body.dark-mode .energy-monitor-page .alert-pill-level-high {
        color: #fda4af;
        background: rgba(190, 18, 60, 0.25);
        border-color: rgba(244, 114, 182, 0.35);
    }

    body.dark-mode .energy-monitor-page .alert-pill-level-moderate,
    body.dark-mode .energy-monitor-page .alert-pill-level-warning {
        color: #fde68a;
        background: rgba(146, 64, 14, 0.3);
        border-color: rgba(251, 191, 36, 0.35);
    }

    body.dark-mode .energy-monitor-page .alert-pill-level-low,
    body.dark-mode .energy-monitor-page .alert-pill-level-normal {
        color: #86efac;
        background: rgba(22, 101, 52, 0.25);
        border-color: rgba(74, 222, 128, 0.28);
    }

    body.dark-mode .energy-monitor-page .alert-pill-level-no-data {
        color: #cbd5e1;
        background: rgba(51, 65, 85, 0.35);
        border-color: rgba(148, 163, 184, 0.25);
    }

    body.dark-mode .energy-monitor-page .recommendation-modal-panel {
        background: #111827 !important;
        color: #e2e8f0 !important;
        border: 1px solid #334155 !important;
    }

    body.dark-mode .energy-monitor-page .recommendation-title {
        color: #f8fafc !important;
    }

    body.dark-mode .energy-monitor-page .recommendation-meta {
        color: #94a3b8 !important;
    }

    body.dark-mode .energy-monitor-page .recommendation-modal {
        background: rgba(2, 6, 23, 0.6);
    }

    body.dark-mode .energy-monitor-page .recommendation-close-icon {
        color: #94a3b8;
    }

    body.dark-mode .energy-monitor-page .recommendation-close-icon:hover {
        color: #cbd5e1;
    }

    body.dark-mode .energy-monitor-page .recommendation-close-btn {
        background: #1d4ed8;
    }

    /* Dark mode fallback for remaining inline-styled blocks */
    body.dark-mode .energy-monitor-page [style*="background:#f8fafc"],
    body.dark-mode .energy-monitor-page [style*="background: #f8fafc"] {
        background: linear-gradient(145deg, #102138, #111827) !important;
        border-color: #2f4c72 !important;
    }

    body.dark-mode .energy-monitor-page [style*="background:#fff1f2"],
    body.dark-mode .energy-monitor-page [style*="background: #fff1f2"] {
        background: linear-gradient(145deg, #321923, #111827) !important;
        border-color: #7f1d1d !important;
    }

    body.dark-mode .energy-monitor-page [style*="background:#f0fdf4"],
    body.dark-mode .energy-monitor-page [style*="background: #f0fdf4"] {
        background: linear-gradient(145deg, #0f2a22, #111827) !important;
        border-color: #166534 !important;
    }

    body.dark-mode .energy-monitor-page [style*="background:#f1f5f9"],
    body.dark-mode .energy-monitor-page [style*="background: #f1f5f9"] {
        background: #111827 !important;
    }

    body.dark-mode .energy-monitor-page [style*="border-bottom:1px solid #f1f5f9"] {
        border-bottom: 1px solid #334155 !important;
    }

    body.dark-mode .energy-monitor-page [style*="color:#1e293b"],
    body.dark-mode .energy-monitor-page [style*="color: #1e293b"],
    body.dark-mode .energy-monitor-page [style*="color:#334155"],
    body.dark-mode .energy-monitor-page [style*="color: #334155"] {
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-monitor-page [style*="color:#64748b"],
    body.dark-mode .energy-monitor-page [style*="color: #64748b"],
    body.dark-mode .energy-monitor-page [style*="color:#475569"],
    body.dark-mode .energy-monitor-page [style*="color: #475569"] {
        color: #94a3b8 !important;
    }

    body.dark-mode .energy-monitor-page [style*="color:#2563eb"],
    body.dark-mode .energy-monitor-page [style*="color: #2563eb"] {
        color: #93c5fd !important;
    }

    body.dark-mode #successAlert > div {
        background: #14532d !important;
        color: #dcfce7 !important;
        border: 1px solid #166534;
    }

    @media (max-width: 600px) {
        .energy-monitor-page .monitor-title { font-size: 1.5rem !important; }
        .energy-monitor-page .overview-cards { grid-template-columns: 1fr; gap: 12px; }
        .energy-monitor-page .report-card { padding: 15px; }
        .energy-monitor-page .search-form { width: 100%; flex-wrap: wrap; }
        .energy-monitor-page .search-field,
        .energy-monitor-page .search-input { width: 100%; }
        .energy-monitor-page .search-btn { width: 100%; }
    }
</style>

@section('content')

@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
@endphp

<a href="#main-content" class="skip-link" tabindex="0">Skip to main content</a>

{{-- Alerts --}}
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

<div class="energy-monitor-page">
<div class="report-card">
    <div class="monitor-header">
        <div>
            <h1 class="monitor-title">
                Energy Trend Monitoring <span class="monitor-title-accent">Dashboard</span>
            </h1>
            <p class="monitor-subtitle">Overview of all facility energy performance</p>
        </div>
        
        <form class="search-form" method="GET" action="">
            <div class="search-field">
                <i class="fa fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                <input class="search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Search facility...">
            </div>
            <button class="search-btn" type="submit">Search</button>
            @if(request('search'))
                <a class="clear-link" href="{{ url()->current() }}">Clear</a>
            @endif
        </form>
    </div>
    <div class="overview-cards">
        <div class="metric-card metric-facilities">
            <div class="metric-label">Total Facilities</div>
            <div class="metric-value">{{ $totalFacilities ?? '-' }}</div>
        </div>
        <div class="metric-card metric-alert">
            <div class="metric-label">High Alert Facilities</div>
            <div class="metric-value">{{ $highAlertCount ?? 0 }}</div>
        </div>
        <div class="metric-card metric-cost">
            <div class="metric-label">Total Cost (Month)</div>
            <div class="metric-value">₱{{ number_format($totalEnergyCost ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="monitor-table-wrap">
        <table id="main-content" class="monitor-table">
            <thead>
                <tr>
                    <th>Facility</th>
                    <th>Type</th>
                    <th>Month</th>
                    <th>Floor Area</th>
                    <th>Baseline kWh</th>
                    <th>Trend</th>
                    <th>EUI</th>
                    <th>Alerts</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
            @forelse($facilities as $facility)
                @php 
                    $record = $facility->currentMonthRecord;
                    $trendAnalysis = $facility->trend_analysis ?? '-';
                    $alertLevel = $facility->alert_level ?? 'No Data';
                    $eui = null;
                    $hasCurrentMonth = $record !== null;
                @endphp
                @if($hasCurrentMonth)
                    @php
                        $actualKwh = $record->actual_kwh ?? 0;
                        $floorArea = $facility->floor_area;
                        $eui = ($floorArea > 0) ? number_format($actualKwh / $floorArea, 2) : null;
                        $trendRecommendation = $facility->trend_recommendation ?? 'No recommendation';
                    @endphp
                    <tr class="monitor-row">
                        <td class="cell-facility">{{ $facility->name }}</td>
                        <td class="cell-type">{{ $facility->type }}</td>
                        <td>
                            @php $monthsArr = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
                            {{ $monthsArr[$record->month-1] ?? '-' }}
                        </td>
                        <td>{{ $facility->floor_area ?? '-' }} <small class="unit-label">m2</small></td>
                        <td class="cell-baseline">
                            @php
                                // Get baseline_kwh from the current month's energy record
                                $baselineKwh = $record->baseline_kwh ?? null;
                            @endphp
                            {{ $baselineKwh !== null ? number_format($baselineKwh, 2) : '-' }}
                        </td>
                        <td class="trend-value {{ $trendAnalysis === '-' ? '' : (str_contains($trendAnalysis, '+') ? 'trend-positive' : 'trend-normal') }}">
                            {{ $trendAnalysis }}
                        </td>
                        <td>{{ $eui ?? '-' }}</td>
                        <td>
                            <span class="monitor-alert-pill alert-pill-level-{{ \Illuminate\Support\Str::slug($alertLevel, '-') }}">
                                {{ $alertLevel }}
                            </span>
                        </td>
                        <td>
                            @php
                                $alertIcons = [
                                    'Critical' => ['icon' => '⚠️', 'color' => '#e11d48'],
                                    'High' => ['icon' => '⚡', 'color' => '#f59e42'],
                                    'Moderate' => ['icon' => '🔆', 'color' => '#fbbf24'],
                                    'Low' => ['icon' => '💡', 'color' => '#16a34a'],
                                    'Normal' => ['icon' => '✅', 'color' => '#2563eb'],
                                    'Critical' => ['icon' => '🚨', 'color' => '#7c1d1d'],
                                    'Very High' => ['icon' => '🚩', 'color' => '#e11d48'],
                                    'High' => ['icon' => '⚡', 'color' => '#f59e42'],
                                    'Warning' => ['icon' => '🔔', 'color' => '#f59e42'],
                                    'Normal' => ['icon' => '💡', 'color' => '#16a34a'],
                                ];
                                $iconData = $alertIcons[$alertLevel] ?? ['icon' => 'ℹ️', 'color' => '#64748b'];
                                $trendRecommendation = $facility->trend_recommendation ?? 'No recommendation';
                                $iconData = [
                                    'Critical' => ['icon' => '!', 'color' => '#7c1d1d'],
                                    'Very High' => ['icon' => '!', 'color' => '#e11d48'],
                                    'High' => ['icon' => '!', 'color' => '#e11d48'],
                                    'Moderate' => ['icon' => 'i', 'color' => '#f59e42'],
                                    'Warning' => ['icon' => 'i', 'color' => '#f59e42'],
                                    'Low' => ['icon' => 'i', 'color' => '#16a34a'],
                                    'Normal' => ['icon' => 'i', 'color' => '#2563eb'],
                                    'No Data' => ['icon' => 'i', 'color' => '#64748b'],
                                ][$alertLevel] ?? ['icon' => 'i', 'color' => '#64748b'];
                            @endphp
                            <button type="button" title="View Recommendation" class="recommendation-btn level-{{ \Illuminate\Support\Str::slug($alertLevel, '-') }}" onclick='openRecommendationModal(@json($facility->id), @json($facility->name), @json($alertLevel), @json($trendRecommendation))'>
                                <span class="recommendation-icon">{{ $iconData['icon'] }}</span>
                            </button>
                        </td>
                       
                    </tr>
                @endif
            @empty
                <tr><td colspan="9" class="monitor-empty">No facilities found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($facilities, 'links'))
        <div class="pagination-wrap">
            {{ $facilities->appends(request()->query())->links() }}
        </div>
    @endif
<!-- Recommendation Modal -->
<div id="recommendationModal" class="recommendation-modal">
    <div class="recommendation-modal-inner">
        <div id="recommendationModalBox" class="recommendation-modal-panel">
            <button type="button" onclick="closeRecommendationModal()" class="recommendation-close-icon">&times;</button>
            <div class="recommendation-modal-header">
                <div id="recommendationAlertBadge" class="recommendation-alert-badge">i</div>
                <div class="recommendation-title-wrap">
                    <h2 id="recommendationModalTitle" class="recommendation-title"></h2>
                    <div id="recommendationModalMeta" class="recommendation-meta"></div>
                </div>
            </div>
            <div id="recommendationText" class="recommendation-message-card"></div>
            <div class="recommendation-footer">
                <button type="button" onclick="closeRecommendationModal()" class="recommendation-close-btn">Close</button>
            </div>
        </div>
    </div>
</div>

@include('modules.facilities.partials.modals')

<script>
// Logic scripts (Reset & Approval)
function openResetBaselineModal(facilityId) {
    document.getElementById('reset_facility_id').value = facilityId;
    document.getElementById('resetBaselineModal').style.display = 'flex';
}

function toggleEngineerApproval(facilityId) {
    if(!confirm('Toggle engineer approval for this facility?')) return;
    fetch(`/modules/facilities/${facilityId}/toggle-engineer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(() => location.reload());
}

function openRecommendationModal(facilityId, facilityName, alertLevel, trendRecommendation) {
    const modal = document.getElementById('recommendationModal');
    const title = document.getElementById('recommendationModalTitle');
    const meta  = document.getElementById('recommendationModalMeta');
    const text  = document.getElementById('recommendationText');
    const box   = document.getElementById('recommendationModalBox');
    const badge = document.getElementById('recommendationAlertBadge');
    const isDark = document.body.classList.contains('dark-mode');
    const alertStyles = {
        'Critical': { color: '#991b1b', bg: '#fef2f2', border: '#fca5a5', badgeBg: '#ffffff', badgeColor: '#991b1b', darkBg: 'rgba(127,29,29,0.22)', darkBorder: 'rgba(248,113,113,0.35)', darkBadgeBg: '#0f172a', darkBadgeColor: '#fecaca', icon: '!' },
        'Very High': { color: '#be123c', bg: '#fff1f2', border: '#fda4af', badgeBg: '#ffffff', badgeColor: '#be123c', darkBg: 'rgba(190,18,60,0.18)', darkBorder: 'rgba(244,114,182,0.30)', darkBadgeBg: '#0f172a', darkBadgeColor: '#fda4af', icon: '!' },
        'High': { color: '#c2410c', bg: '#fff7ed', border: '#fdba74', badgeBg: '#ffffff', badgeColor: '#c2410c', darkBg: 'rgba(194,65,12,0.18)', darkBorder: 'rgba(251,146,60,0.30)', darkBadgeBg: '#0f172a', darkBadgeColor: '#fdba74', icon: '!' },
        'Moderate': { color: '#92400e', bg: '#fef3c7', border: '#fcd34d', badgeBg: '#ffffff', badgeColor: '#92400e', darkBg: 'rgba(146,64,14,0.18)', darkBorder: 'rgba(251,191,36,0.28)', darkBadgeBg: '#0f172a', darkBadgeColor: '#fde68a', icon: 'i' },
        'Warning': { color: '#9a4a12', bg: '#f7edc0', border: '#f3c23c', badgeBg: '#ffffff', badgeColor: '#b45309', darkBg: 'rgba(180,83,9,0.16)', darkBorder: 'rgba(251,191,36,0.24)', darkBadgeBg: '#0f172a', darkBadgeColor: '#fde68a', icon: 'i' },
        'Low': { color: '#166534', bg: '#f0fdf4', border: '#86efac', badgeBg: '#ffffff', badgeColor: '#166534', darkBg: 'rgba(22,101,52,0.16)', darkBorder: 'rgba(74,222,128,0.24)', darkBadgeBg: '#0f172a', darkBadgeColor: '#86efac', icon: 'i' },
        'Normal': { color: '#1d4ed8', bg: '#eff6ff', border: '#93c5fd', badgeBg: '#ffffff', badgeColor: '#1d4ed8', darkBg: 'rgba(37,99,235,0.14)', darkBorder: 'rgba(147,197,253,0.22)', darkBadgeBg: '#0f172a', darkBadgeColor: '#93c5fd', icon: 'i' },
        'No Data': { color: '#475569', bg: '#f1f5f9', border: '#cbd5e1', badgeBg: '#ffffff', badgeColor: '#475569', darkBg: 'rgba(51,65,85,0.22)', darkBorder: 'rgba(148,163,184,0.20)', darkBadgeBg: '#0f172a', darkBadgeColor: '#cbd5e1', icon: 'i' },
    };
    const style = alertStyles[alertLevel] || { color: '#475569', bg: '#f1f5f9', border: '#e2e8f0', badgeBg: '#64748b', badgeColor: '#ffffff', darkBg: 'rgba(51,65,85,0.25)', darkBorder: 'rgba(148,163,184,0.22)', darkBadgeBg: '#475569', darkBadgeColor: '#e2e8f0', icon: 'i' };
    title.textContent = `Recommendation for ${facilityName}`;
    if (meta) meta.textContent = '';
    text.textContent = trendRecommendation || 'No recommendation';
    text.style.color = isDark ? '#e2e8f0' : style.color;
    text.style.background = isDark ? style.darkBg : style.bg;
    text.style.borderColor = isDark ? style.darkBorder : style.border;
    if (badge) {
        badge.textContent = style.icon;
        badge.style.background = isDark ? style.darkBadgeBg : style.badgeBg;
        badge.style.color = isDark ? style.darkBadgeColor : style.badgeColor;
        badge.style.border = isDark ? `1.5px solid ${style.darkBorder}` : `1.5px solid ${style.border}`;
    }
    box.style.background = isDark ? '#111827' : '#fff';
    modal.style.display = 'flex';
}
function closeRecommendationModal() {
    document.getElementById('recommendationModal').style.display = 'none';
}

// Auto-hide alert
window.addEventListener('DOMContentLoaded', () => {
    const success = document.getElementById('successAlert');
    if(success) setTimeout(() => success.style.opacity = '0', 3000);
});
</script>

@endsection
