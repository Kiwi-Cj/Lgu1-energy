@extends('layouts.qc-admin')
@section('title', 'Energy Trend')

@section('content')
@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

<style>
    /* Report Card Container */
    .energy-trend-page .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        border: 1px solid #eef2f6;
        margin-bottom: 2rem;
    }

    /* Header Styling */
    .energy-trend-page .page-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }
    .energy-trend-page .page-header h1 span { color: #2563eb; }
    .energy-trend-page .page-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 1rem;
    }

    /* Filter Bar */
    .energy-trend-page .filter-bar {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        margin: 25px 0;
        flex-wrap: wrap;
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    .energy-trend-page .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .energy-trend-page .filter-group label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
    }
    .energy-trend-page .filter-group select {
        padding: 10px 15px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #fff;
        min-width: 180px;
        outline: none;
    }
    .energy-trend-page .filter-btn {
        background: #2563eb;
        color: #fff;
        padding: 11px 24px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
    }
    .energy-trend-page .filter-btn:hover { background: #1d4ed8; }

    /* Summary Grid */
    .energy-trend-page .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .energy-trend-page .summary-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        transition: transform 0.2s;
    }
    .energy-trend-page .summary-card:hover { transform: translateY(-3px); }
    .energy-trend-page .summary-card span {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .energy-trend-page .summary-card h2 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
        color: #1e293b;
    }
    .energy-trend-page .summary-card p {
        margin: 5px 0 0;
        font-size: 0.85rem;
        color: #94a3b8;
    }
    .energy-trend-page .summary-unit {
        font-size: 0.9rem;
        color: #64748b;
    }

    /* Analysis Box */
    .energy-trend-page .analysis-container {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }
    .energy-trend-page .analysis-header {
        background: #f1f5f9;
        padding: 15px 20px;
        font-weight: 700;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .energy-trend-page .analysis-body { padding: 25px; }
    .energy-trend-page .analysis-description {
        color: #64748b;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    .energy-trend-page .chart-shell {
        min-height: 300px;
        background: #fcfcfc;
        border-radius: 8px;
        padding: 15px;
    }
    .energy-trend-page .insight-box {
        margin-top: 25px;
        background: #eff6ff;
        padding: 15px 20px;
        border-radius: 12px;
        border-left: 4px solid #2563eb;
        font-size: 0.95rem;
        color: #1e3a8a;
    }

    body.dark-mode .energy-trend-page .report-card {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 18px 34px rgba(2, 6, 23, 0.5);
    }
    body.dark-mode .energy-trend-page .page-header h1,
    body.dark-mode .energy-trend-page .summary-card h2,
    body.dark-mode .energy-trend-page .analysis-header {
        color: #e2e8f0;
    }
    body.dark-mode .energy-trend-page .page-header p,
    body.dark-mode .energy-trend-page .summary-card span,
    body.dark-mode .energy-trend-page .summary-card p,
    body.dark-mode .energy-trend-page .summary-unit,
    body.dark-mode .energy-trend-page .analysis-description {
        color: #94a3b8;
    }
    body.dark-mode .energy-trend-page .filter-bar {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .energy-trend-page .filter-group label {
        color: #cbd5e1;
    }
    body.dark-mode .energy-trend-page .filter-group select {
        background: #0b1220;
        color: #e2e8f0;
        border-color: #334155;
    }
    body.dark-mode .energy-trend-page .filter-btn {
        background: #1d4ed8;
    }
    body.dark-mode .energy-trend-page .summary-card {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .energy-trend-page .analysis-container {
        border-color: #334155;
    }
    body.dark-mode .energy-trend-page .analysis-header {
        background: #111827;
        border-bottom-color: #334155;
    }
    body.dark-mode .energy-trend-page .chart-shell {
        background: #0b1220;
    }
    body.dark-mode .energy-trend-page .insight-box {
        background: #172554;
        border-left-color: #3b82f6;
        color: #dbeafe;
    }
    body.dark-mode #successAlert > div {
        background: #14532d !important;
        color: #dcfce7 !important;
        border: 1px solid #166534;
    }

    @media (max-width: 600px) {
        .energy-trend-page .filter-bar { flex-direction: column; align-items: stretch; }
        .energy-trend-page .filter-group select { min-width: 100%; }
    }
</style>

{{-- Success Alert --}}
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

<div class="energy-trend-page">
<div class="report-card">
    <div class="page-header">
        <h1>
            <i class="fa-solid fa-chart-line" style="color: #2563eb;"></i>
            Energy <span>Trend Analysis</span>
        </h1>
        <p>Monitor and analyze energy consumption patterns to improve facility efficiency.</p>
    </div>

    <form class="filter-bar" method="GET" action="{{ route('energy.trend') }}">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" {{ request('facility_id') ? '' : 'selected' }}>Select Facility</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Year</label>
            <select name="year" id="year">
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" {{ request('month') ? '' : 'selected' }}>All Months</option>
                @foreach($months as $month)
                    <option value="{{ $month['value'] }}" {{ request('month') == $month['value'] ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', substr($month['value'],0,7))->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="filter-btn" type="submit">Apply Filter</button>
    </form>

    <div class="summary-grid">
        <div class="summary-card" style="border-top: 4px solid #2563eb;">
            <span>Total Consumption</span>
            <h2>{{ number_format($totalConsumption ?? 0, 2) }} <small class="summary-unit">kWh</small></h2>
            <p>Aggregate usage for period</p>
        </div>

        <div class="summary-card" style="border-top: 4px solid #e11d48;">
            <span>Peak Usage</span>
            <h2>{{ number_format($peakUsage ?? 0, 2) }} <small class="summary-unit">kWh</small></h2>
            <p>Highest recorded point</p>
        </div>

        <div class="summary-card" style="border-top: 4px solid #16a34a;">
            <span>Trend Direction</span>
            <h2>{{ $trendDirection ?? 'Stable' }}</h2>
            <p>
                @if(isset($trendChangePercent) && $trendChangePercent !== null)
                    Change: {{ $trendChangePercent > 0 ? '+' : '' }}{{ number_format($trendChangePercent, 2) }}%
                @else
                    Insufficient baseline for percent change
                @endif
            </p>
        </div>
    </div>

    <div class="analysis-container">
        <div class="analysis-header">
            <i class="fa-solid fa-bolt" style="color:#eab308;"></i>
            Consumption Visual Trend
        </div>

        <div class="analysis-body">
            <p class="analysis-description">
                The visualization below represents your facility's energy demand. Significant spikes may indicate equipment malfunction or 
                increased operational hours that require review.
            </p>

            {{-- CHART PARTIAL --}}
            <div class="chart-shell">
                @include('modules.energy-monitoring.partials.charts', [
                    'chartData' => $trendData
                ])
            </div>

            <div class="insight-box">
                <i class="fa-solid fa-lightbulb" style="margin-right:8px;"></i>
                <strong>Trend Insight:</strong>
                <span>
                    {{ $trendInsight ?? 'Select a facility and period to generate trend analysis.' }}
                </span>
            </div>
        </div>
    </div>
</div>
</div>

<script>
window.addEventListener('DOMContentLoaded', function() {
    var success = document.getElementById('successAlert');
    if (success) {
        setTimeout(() => {
            success.style.transition = 'opacity 0.5s ease';
            success.style.opacity = '0';
            setTimeout(() => success.remove(), 500);
        }, 3000);
    }
});
</script>
@endsection
