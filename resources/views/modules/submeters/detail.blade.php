@extends('layouts.qc-admin')
@section('title', 'Submeter Detail')

<style>
    .sd-page { display:grid; gap:16px; padding:14px; color:#0f172a; }
    .sd-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding:24px 26px; border:1px solid #dbe5f2; border-radius:22px; background:linear-gradient(135deg,#fff,#f7faff 62%,#eef4ff); box-shadow:0 14px 34px rgba(15,23,42,.08); }
    .sd-heading { display:flex; align-items:flex-start; gap:14px; }
    .sd-heading-icon { width:50px; height:50px; flex:0 0 50px; display:grid; place-items:center; border-radius:15px; color:#fff; background:linear-gradient(135deg,#2563eb,#06b6d4); box-shadow:0 9px 20px rgba(37,99,235,.2); }
    .sd-title { margin:0; color:#172554; font-size:1.55rem; font-weight:950; letter-spacing:-.025em; }
    .sd-subtitle { margin-top:5px; color:#64748b; font-size:.88rem; font-weight:650; }
    .sd-hero-meta { display:flex; flex-wrap:wrap; gap:7px; margin-top:10px; }
    .sd-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 9px; border:1px solid #dbe5f2; border-radius:999px; color:#475569; background:#fff; font-size:.68rem; font-weight:850; }
    .sd-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .sd-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:42px; padding:9px 13px; border:1px solid #cbd5e1; border-radius:10px; color:#334155; background:#fff; text-decoration:none; font-weight:850; white-space:nowrap; }
    .sd-btn.alerts { color:#be123c; border-color:#fecdd3; background:#fff1f2; }

    .sd-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .sd-kpi { position:relative; overflow:hidden; min-height:116px; padding:17px 18px; border:1px solid #dbe5f2; border-radius:16px; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.05); }
    .sd-kpi::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:var(--sd-color,#2563eb); }
    .sd-kpi-top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .sd-kpi-label { color:#64748b; font-size:.7rem; font-weight:850; text-transform:uppercase; letter-spacing:.045em; }
    .sd-kpi-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; color:var(--sd-color,#2563eb); background:var(--sd-soft,#eff6ff); }
    .sd-kpi-value { margin-top:11px; color:#0f172a; font-size:1.45rem; line-height:1; font-weight:950; }
    .sd-kpi-note { margin-top:6px; color:#64748b; font-size:.67rem; font-weight:650; }
    .sd-kpi.actual { --sd-color:#2563eb; --sd-soft:#eff6ff; }
    .sd-kpi.baseline { --sd-color:#0891b2; --sd-soft:#ecfeff; }
    .sd-kpi.variance { --sd-color:#f97316; --sd-soft:#fff7ed; }
    .sd-kpi.status { --sd-color:#7c3aed; --sd-soft:#f5f3ff; }

    .sd-panel { overflow:hidden; border:1px solid #dbe5f2; border-radius:18px; background:#fff; box-shadow:0 10px 28px rgba(15,23,42,.06); }
    .sd-panel-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:16px 18px; border-bottom:1px solid #e2e8f0; background:#fff; }
    .sd-panel-heading { display:flex; align-items:center; gap:10px; }
    .sd-panel-icon { width:38px; height:38px; display:grid; place-items:center; border-radius:11px; color:#2563eb; background:#eff6ff; }
    .sd-panel-title { margin:0; color:#1e293b; font-size:1rem; font-weight:900; }
    .sd-panel-subtitle { margin-top:3px; color:#64748b; font-size:.72rem; font-weight:650; }
    .sd-period-tabs { display:flex; gap:7px; }
    .sd-period-tab { display:inline-flex; min-height:38px; align-items:center; justify-content:center; padding:7px 11px; border:1px solid #cbd5e1; border-radius:9px; color:#475569; background:#fff; text-decoration:none; font-size:.75rem; font-weight:850; }
    .sd-period-tab.active { color:#fff; border-color:#2563eb; background:#2563eb; }
    .sd-chart-body { padding:16px 18px 18px; }
    .sd-chart-note { display:flex; align-items:center; gap:7px; margin-bottom:12px; padding:9px 11px; border:1px solid #bae6fd; border-radius:10px; color:#0e7490; background:#ecfeff; font-size:.72rem; font-weight:700; }
    .sd-chart { position:relative; height:330px; border:1px solid #e2e8f0; border-radius:13px; background:#f8fafc; padding:10px; }

    .sd-table-wrap { overflow:hidden; padding:10px; }
    .sd-table-shell { overflow:hidden; border:1px solid #dbe5f2; border-radius:13px; }
    .sd-table { width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; }
    .sd-table th { padding:12px 10px; border-bottom:1px solid #dbe5f2; color:#475569; background:#f1f5f9; font-size:.68rem; text-transform:uppercase; letter-spacing:.045em; text-align:left; }
    .sd-table td { padding:12px 10px; border-bottom:1px solid #edf2f7; color:#334155; vertical-align:middle; font-size:.78rem; }
    .sd-table tbody tr:last-child td { border-bottom:0; }
    .sd-table tbody tr:hover { background:#f8fbff; }
    .sd-number { color:#0f172a; font-weight:900; font-variant-numeric:tabular-nums; }
    .sd-unit { display:block; margin-top:2px; color:#94a3b8; font-size:.59rem; text-transform:uppercase; font-weight:800; }
    .sd-source { display:block; margin-top:3px; color:#64748b; font-size:.61rem; font-weight:650; }
    .sd-variance { font-weight:950; }
    .sd-variance.up { color:#be123c; }
    .sd-variance.down { color:#4338ca; }
    .sd-pill { display:inline-flex; align-items:center; justify-content:center; gap:5px; min-width:94px; padding:6px 9px; border:1px solid transparent; border-radius:999px; font-size:.64rem; line-height:1.1; text-align:center; font-weight:900; }
    .sd-pill.normal { color:#166534; background:#dcfce7; border-color:#86efac; }
    .sd-pill.warning { color:#92400e; background:#fffbeb; border-color:#fde68a; }
    .sd-pill.high { color:#a16207; background:#fef3c7; border-color:#fcd34d; }
    .sd-pill.very-high { color:#9a3412; background:#ffedd5; border-color:#fdba74; }
    .sd-pill.critical { color:#991b1b; background:#fee2e2; border-color:#fecaca; }
    .sd-pill.drop-warning { color:#0e7490; background:#cffafe; border-color:#67e8f9; }
    .sd-pill.drop-high { color:#4338ca; background:#e0e7ff; border-color:#a5b4fc; }
    .sd-pill.drop-critical { color:#6d28d9; background:#ede9fe; border-color:#c4b5fd; }
    .sd-pill.none { color:#475569; background:#f1f5f9; border-color:#cbd5e1; }
    .sd-data-state { display:flex; flex-wrap:wrap; justify-content:center; gap:5px; }
    .sd-state { display:inline-flex; align-items:center; gap:4px; padding:5px 7px; border-radius:999px; color:#047857; background:#ecfdf5; font-size:.61rem; font-weight:850; }
    .sd-state.pending { color:#92400e; background:#fffbeb; }

    .sd-timeline { display:grid; gap:10px; padding:14px 16px 16px; }
    .sd-event { display:grid; grid-template-columns:130px 135px minmax(0,1fr); gap:12px; align-items:center; padding:13px 14px; border:1px solid #e2e8f0; border-radius:13px; background:#fff; }
    .sd-event-period { color:#334155; font-weight:900; }
    .sd-event-reason { color:#475569; font-size:.76rem; line-height:1.45; font-weight:600; }
    .sd-empty { padding:34px 20px; text-align:center; color:#64748b; }
    .sd-empty i { display:block; margin-bottom:8px; color:#10b981; font-size:1.5rem; }

    body.dark-mode .sd-hero, body.dark-mode .sd-kpi, body.dark-mode .sd-panel, body.dark-mode .sd-event { background:#111827; border-color:#334155; }
    body.dark-mode .sd-title, body.dark-mode .sd-kpi-value, body.dark-mode .sd-panel-title, body.dark-mode .sd-number { color:#f1f5f9; }
    body.dark-mode .sd-table th { background:#0f172a; border-color:#334155; }
    body.dark-mode .sd-table td, body.dark-mode .sd-table-shell { border-color:#334155; }
    body.dark-mode .sd-table tbody tr:hover { background:#172033; }
    body.dark-mode .sd-chart { background:#0f172a; border-color:#334155; }

    @media (max-width:1000px) { .sd-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:700px) {
        .sd-page { padding:8px; }
        .sd-hero { flex-direction:column; padding:18px; }
        .sd-actions, .sd-actions .sd-btn { width:100%; }
        .sd-heading-icon { width:44px; height:44px; flex-basis:44px; }
        .sd-kpis { grid-template-columns:1fr; }
        .sd-period-tabs { display:grid; grid-template-columns:repeat(3,1fr); width:100%; }
        .sd-chart { height:260px; }
        .sd-table-wrap { overflow:visible; }
        .sd-table-shell { border:0; overflow:visible; }
        .sd-table, .sd-table tbody { display:block; }
        .sd-table thead, .sd-table colgroup { display:none; }
        .sd-table tbody { display:grid; gap:10px; }
        .sd-table tbody tr { display:grid; grid-template-columns:1fr 1fr; overflow:hidden; border:1px solid #dbe5f2; border-radius:13px; }
        .sd-table td { display:block; }
        .sd-table td::before { content:attr(data-label); display:block; margin-bottom:5px; color:#94a3b8; font-size:.59rem; font-weight:850; text-transform:uppercase; }
        .sd-table td:first-child, .sd-table td:last-child { grid-column:1/-1; }
        .sd-event { grid-template-columns:1fr; }
        body.dark-mode .sd-table tbody tr { border-color:#334155; }
    }
</style>

@section('content')
@include('layouts.partials.energy_monitoring_switcher', ['energyTab' => 'sub'])
@php
    $summary = $detailSummary ?? [];
    $levelMeta = [
        'normal' => ['NORMAL', 'normal', 'fa-circle-check'],
        'warning' => ['WARNING', 'warning', 'fa-circle-exclamation'],
        'high' => ['HIGH', 'high', 'fa-arrow-trend-up'],
        'very_high' => ['VERY HIGH', 'very-high', 'fa-arrow-trend-up'],
        'critical' => ['CRITICAL', 'critical', 'fa-triangle-exclamation'],
        'drop_warning' => ['DROP WARNING', 'drop-warning', 'fa-arrow-trend-down'],
        'drop_high' => ['DROP HIGH', 'drop-high', 'fa-arrow-trend-down'],
        'drop_critical' => ['DROP CRITICAL', 'drop-critical', 'fa-arrow-trend-down'],
        'none' => ['NOT EVALUATED', 'none', 'fa-circle-minus'],
    ];
    $sourceLabels = [
        'configured_meter' => 'Configured baseline', 'normalized_per_day' => 'Normalized/day',
        'moving_avg_3' => '3-period average', 'seasonal_month' => 'Seasonal baseline',
        'moving_avg_6' => '6-period average', 'equipment_estimate' => 'Equipment estimate',
        'alert' => 'Stored alert baseline',
    ];
    [$latestLabel, $latestClass, $latestIcon] = $levelMeta[$summary['alert_level'] ?? 'none'] ?? $levelMeta['none'];
    $latestVariance = $summary['variance_percent'] ?? null;
    $detailOrigin = request()->query('from') === 'alerts' ? 'alerts' : 'monitoring';
    $returnPeriodType = request()->query('return_period_type', $periodType);
    $backQuery = array_filter([
        'month' => request()->query('month', $loadTrackingMonth),
        'period_type' => $returnPeriodType,
        'facility_id' => request()->query('facility_id', $submeter->facility_id),
        'alert_level' => $detailOrigin === 'alerts' ? request()->query('alert_level') : null,
        'department' => $detailOrigin === 'monitoring' ? request()->query('department') : null,
    ], fn ($value) => $value !== null && $value !== '');
    $backUrl = $detailOrigin === 'alerts'
        ? route('modules.submeters.alerts', $backQuery)
        : route('modules.submeters.monitoring', $backQuery);
    $backLabel = $detailOrigin === 'alerts' ? 'Back to Alert Queue' : 'Back to Monitoring';
    $detailContextQuery = array_filter([
        'from' => $detailOrigin,
        'return_period_type' => $returnPeriodType,
        'month' => request()->query('month', $loadTrackingMonth),
        'facility_id' => request()->query('facility_id', $submeter->facility_id),
        'alert_level' => request()->query('alert_level'),
        'department' => request()->query('department'),
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="sd-page">
    <header class="sd-hero">
        <div class="sd-heading"><span class="sd-heading-icon"><i class="fa-solid fa-gauge-high"></i></span><div><h1 class="sd-title">{{ $submeter->submeter_name }}</h1><div class="sd-subtitle">Investigation view for consumption, baseline variance, and alert history.</div><div class="sd-hero-meta"><span class="sd-chip"><i class="fa-solid fa-building"></i> {{ $submeter->facility?->name }}</span><span class="sd-chip"><i class="fa-solid fa-calendar"></i> {{ ucfirst($periodType) }}</span><span class="sd-chip"><i class="fa-solid fa-clock-rotate-left"></i> Last {{ $readings->count() }} periods</span></div></div></div>
        <div class="sd-actions"><a class="sd-btn" href="{{ $backUrl }}"><i class="fa-solid fa-arrow-left"></i> {{ $backLabel }}</a></div>
    </header>

    <section class="sd-kpis">
        <article class="sd-kpi actual"><div class="sd-kpi-top"><span class="sd-kpi-label">Latest consumption</span><span class="sd-kpi-icon"><i class="fa-solid fa-bolt"></i></span></div><div class="sd-kpi-value">{{ $summary['actual_kwh'] !== null ? number_format((float) $summary['actual_kwh'], 2).' kWh' : 'No data' }}</div><div class="sd-kpi-note">Period: {{ $summary['period_label'] ?? '-' }}</div></article>
        <article class="sd-kpi baseline"><div class="sd-kpi-top"><span class="sd-kpi-label">Comparison baseline</span><span class="sd-kpi-icon"><i class="fa-solid fa-bullseye"></i></span></div><div class="sd-kpi-value">{{ $summary['baseline_kwh'] !== null ? number_format((float) $summary['baseline_kwh'], 2).' kWh' : 'Not set' }}</div><div class="sd-kpi-note">{{ $sourceLabels[$summary['baseline_source'] ?? ''] ?? 'Baseline unavailable' }}</div></article>
        <article class="sd-kpi variance"><div class="sd-kpi-top"><span class="sd-kpi-label">Latest variance</span><span class="sd-kpi-icon"><i class="fa-solid fa-chart-line"></i></span></div><div class="sd-kpi-value">{{ $latestVariance !== null ? (($latestVariance > 0 ? '+' : '').number_format((float) $latestVariance, 2).'%') : 'Not evaluated' }}</div><div class="sd-kpi-note">12-period average: {{ $summary['average_kwh'] !== null ? number_format((float) $summary['average_kwh'], 2).' kWh' : '-' }}</div></article>
        <article class="sd-kpi status"><div class="sd-kpi-top"><span class="sd-kpi-label">Current condition</span><span class="sd-kpi-icon"><i class="fa-solid {{ $latestIcon }}"></i></span></div><div class="sd-kpi-value"><span class="sd-pill {{ $latestClass }}"><i class="fa-solid {{ $latestIcon }}"></i> {{ $latestLabel }}</span></div><div class="sd-kpi-note">{{ number_format((int) ($summary['actionable_periods'] ?? 0)) }} actionable period(s) in this view</div></article>
    </section>

    <section class="sd-panel">
        <div class="sd-panel-head"><div class="sd-panel-heading"><span class="sd-panel-icon"><i class="fa-solid fa-chart-area"></i></span><div><h2 class="sd-panel-title">Actual vs. baseline trend</h2><div class="sd-panel-subtitle">The gap between both lines determines the variance and alert condition.</div></div></div><div class="sd-period-tabs">@foreach(['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly'] as $key=>$label)<a class="sd-period-tab {{ $periodType === $key ? 'active' : '' }}" href="{{ route('modules.submeters.show', array_merge(['submeter' => $submeter->id, 'period_type' => $key], $detailContextQuery)) }}">{{ $label }}</a>@endforeach</div></div>
        <div class="sd-chart-body"><div class="sd-chart-note"><i class="fa-solid fa-circle-info"></i> Blue is actual consumption; orange is the selected computed/configured baseline for each period.</div><div class="sd-chart"><canvas id="trendChart" role="img" aria-label="Submeter actual consumption and baseline trend"></canvas></div></div>
    </section>

    <section class="sd-panel">
        <div class="sd-panel-head"><div class="sd-panel-heading"><span class="sd-panel-icon"><i class="fa-solid fa-table-list"></i></span><div><h2 class="sd-panel-title">Reading evaluation</h2><div class="sd-panel-subtitle">Latest first. Every status is recalculated using the current threshold settings.</div></div></div></div>
        <div class="sd-table-wrap"><div class="sd-table-shell"><table class="sd-table"><colgroup><col style="width:12%"><col style="width:12%"><col style="width:12%"><col style="width:13%"><col style="width:14%"><col style="width:12%"><col style="width:13%"><col style="width:12%"></colgroup><thead><tr><th>Period</th><th style="text-align:right">Start</th><th style="text-align:right">End</th><th style="text-align:right">Usage</th><th style="text-align:right">Baseline</th><th style="text-align:right">Variance</th><th style="text-align:center">Condition</th><th style="text-align:center">Data state</th></tr></thead><tbody>
        @forelse($readingsForTable as $reading)
            @php
                $level = (string) ($reading->detail_alert_level ?? 'none');
                [$label, $class, $icon] = $levelMeta[$level] ?? $levelMeta['none'];
                $variance = $reading->detail_variance_percent;
            @endphp
            <tr><td data-label="Period"><span class="sd-number">{{ $reading->periodLabel() }}</span><span class="sd-unit">{{ $reading->period_type }}</span></td><td data-label="Start" style="text-align:right"><span class="sd-number">{{ number_format((float) $reading->reading_start_kwh, 2) }}</span></td><td data-label="End" style="text-align:right"><span class="sd-number">{{ number_format((float) $reading->reading_end_kwh, 2) }}</span></td><td data-label="Usage" style="text-align:right"><span class="sd-number">{{ number_format((float) $reading->kwh_used, 2) }}</span><span class="sd-unit">kWh</span></td><td data-label="Baseline" style="text-align:right">@if($reading->detail_baseline_kwh !== null)<span class="sd-number">{{ number_format((float) $reading->detail_baseline_kwh, 2) }}</span><span class="sd-unit">kWh</span><span class="sd-source">{{ $sourceLabels[$reading->detail_baseline_source] ?? 'Baseline' }}</span>@else<span class="sd-source">Not available</span>@endif</td><td data-label="Variance" style="text-align:right">@if($variance !== null)<span class="sd-variance {{ $variance < 0 ? 'down' : 'up' }}">{{ $variance > 0 ? '+' : '' }}{{ number_format((float) $variance, 2) }}%</span>@else<span class="sd-source">Not evaluated</span>@endif</td><td data-label="Condition" style="text-align:center"><span class="sd-pill {{ $class }}"><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span></td><td data-label="Data state"><div class="sd-data-state"><span class="sd-state"><i class="fa-solid fa-satellite-dish"></i> {{ strtoupper($reading->input_source) }}</span><span class="sd-state {{ $reading->approved_at ? '' : 'pending' }}"><i class="fa-solid {{ $reading->approved_at ? 'fa-circle-check' : 'fa-clock' }}"></i> {{ $reading->approved_at ? 'Approved' : 'Pending' }}</span></div></td></tr>
        @empty
            <tr><td colspan="8" class="sd-empty"><i class="fa-solid fa-chart-line"></i>No readings found for this period type.</td></tr>
        @endforelse
        </tbody></table></div></div>
    </section>

    <section class="sd-panel">
        <div class="sd-panel-head"><div class="sd-panel-heading"><span class="sd-panel-icon"><i class="fa-solid fa-clock-rotate-left"></i></span><div><h2 class="sd-panel-title">Actionable deviation timeline</h2><div class="sd-panel-subtitle">Only non-normal periods are listed for faster investigation.</div></div></div><span class="sd-chip">{{ $alertsTimeline->count() }} event(s)</span></div>
        @if($alertsTimeline->isEmpty())<div class="sd-empty"><i class="fa-solid fa-circle-check"></i>No actionable deviations in the displayed periods.</div>@else<div class="sd-timeline">@foreach($alertsTimeline as $event)@php $eventLevel=(string)$event->detail_alert_level; [$eventLabel,$eventClass,$eventIcon]=$levelMeta[$eventLevel] ?? $levelMeta['none']; @endphp<div class="sd-event"><div class="sd-event-period">{{ $event->periodLabel() }}<span class="sd-unit">{{ $event->period_type }}</span></div><div><span class="sd-pill {{ $eventClass }}"><i class="fa-solid {{ $eventIcon }}"></i> {{ $eventLabel }}</span></div><div class="sd-event-reason">{{ $event->detail_reason }}</div></div>@endforeach</div>@endif
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(() => {
    const canvas = document.getElementById('trendChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const labels = {{ Illuminate\Support\Js::from($labels) }};
    const actual = {{ Illuminate\Support\Js::from($kwhSeries) }};
    const baseline = {{ Illuminate\Support\Js::from($baselineSeries) }};
    new Chart(canvas, {
        type: 'line',
        data: { labels, datasets: [
            { label:'Actual kWh', data:actual, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,.10)', borderWidth:3, tension:.28, fill:true, pointRadius:3, pointHoverRadius:5 },
            { label:'Selected baseline', data:baseline, borderColor:'#f97316', backgroundColor:'transparent', borderWidth:2.5, borderDash:[7,5], tension:.2, fill:false, pointRadius:2, spanGaps:true }
        ]},
        options: { responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:18}}}, scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{callback:value => Number(value).toLocaleString()+' kWh'}}} }
    });
})();
</script>
@endsection
