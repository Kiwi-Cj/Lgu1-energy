@extends('layouts.qc-admin')
@section('title', 'Submeter Alerts')

<style>
    .sa-page { display:grid; gap:18px; padding:14px; color:#0f172a; }
    .sa-hero { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; padding:24px 26px; border:1px solid #dbe5f2; border-radius:22px; background:linear-gradient(135deg,#fff 0%,#f8fbff 58%,#eef4ff 100%); box-shadow:0 14px 34px rgba(15,23,42,.08); }
    .sa-heading { display:flex; gap:14px; align-items:flex-start; }
    .sa-heading-icon { width:48px; height:48px; flex:0 0 48px; display:grid; place-items:center; border-radius:14px; color:#fff; background:linear-gradient(135deg,#f97316,#e11d48); box-shadow:0 9px 20px rgba(225,29,72,.18); }
    .sa-title { margin:0; font-size:1.55rem; font-weight:900; letter-spacing:-.02em; color:#172554; }
    .sa-subtitle { margin-top:5px; color:#64748b; font-size:.9rem; font-weight:600; }
    .sa-back { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:9px 14px; border:1px solid #cbd5e1; border-radius:11px; color:#334155; background:#fff; text-decoration:none; font-weight:800; white-space:nowrap; }

    .sa-flow { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:0; border:1px solid #dbe5f2; border-radius:18px; overflow:hidden; background:#fff; }
    .sa-flow-step { position:relative; display:flex; align-items:center; gap:11px; min-height:86px; padding:16px 18px; border-right:1px solid #e2e8f0; }
    .sa-flow-step:last-child { border-right:0; }
    .sa-flow-number { width:34px; height:34px; flex:0 0 34px; display:grid; place-items:center; border-radius:10px; color:#1d4ed8; background:#eff6ff; font-weight:900; }
    .sa-flow-title { font-size:.82rem; font-weight:900; color:#1e293b; }
    .sa-flow-text { margin-top:3px; font-size:.7rem; line-height:1.35; color:#64748b; font-weight:600; }

    .sa-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .sa-kpi { position:relative; overflow:hidden; min-height:112px; padding:17px 18px; border:1px solid #dbe5f2; border-radius:16px; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.05); }
    .sa-kpi::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:var(--sa-color,#2563eb); }
    .sa-kpi-top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .sa-kpi-label { color:#64748b; font-size:.72rem; font-weight:850; text-transform:uppercase; letter-spacing:.045em; }
    .sa-kpi-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; color:var(--sa-color,#2563eb); background:var(--sa-soft,#eff6ff); }
    .sa-kpi-value { margin-top:11px; color:#0f172a; font-size:1.7rem; line-height:1; font-weight:950; }
    .sa-kpi.total { --sa-color:#2563eb; --sa-soft:#eff6ff; }
    .sa-kpi.critical { --sa-color:#e11d48; --sa-soft:#fff1f2; }
    .sa-kpi.increase { --sa-color:#f97316; --sa-soft:#fff7ed; }
    .sa-kpi.drop { --sa-color:#4f46e5; --sa-soft:#eef2ff; }

    .sa-panel { overflow:hidden; border:1px solid #dbe5f2; border-radius:18px; background:#fff; box-shadow:0 10px 28px rgba(15,23,42,.06); }
    .sa-panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 18px; border-bottom:1px solid #e2e8f0; }
    .sa-panel-title { margin:0; color:#1e293b; font-size:1rem; font-weight:900; }
    .sa-count { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; color:#1d4ed8; background:#eff6ff; font-size:.72rem; font-weight:850; }
    .sa-filter { display:grid; grid-template-columns:150px 140px minmax(220px,1fr) 190px auto; align-items:end; gap:10px; padding:14px 18px; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
    .sa-field { display:grid; gap:6px; min-width:0; }
    .sa-field label { color:#475569; font-size:.72rem; font-weight:850; text-transform:uppercase; letter-spacing:.04em; }
    .sa-input { width:100%; min-height:42px; padding:9px 11px; border:1px solid #cbd5e1; border-radius:10px; color:#0f172a; background:#fff; font-weight:700; }
    .sa-input:focus { outline:0; border-color:#60a5fa; box-shadow:0 0 0 3px rgba(59,130,246,.13); }
    .sa-actions { display:flex; gap:8px; }
    .sa-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:42px; padding:9px 13px; border:1px solid transparent; border-radius:10px; text-decoration:none; font-weight:850; cursor:pointer; white-space:nowrap; }
    .sa-btn.primary { color:#fff; background:#2563eb; }
    .sa-btn.soft { color:#334155; background:#f1f5f9; border-color:#e2e8f0; }

    .sa-table-wrap { overflow:hidden; padding:10px; }
    .sa-table-shell { overflow:hidden; border:1px solid #dbe5f2; border-radius:13px; }
    .sa-table { width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed; }
    .sa-table th { padding:12px 11px; color:#475569; background:#f1f5f9; border-bottom:1px solid #dbe5f2; font-size:.69rem; line-height:1.25; text-transform:uppercase; letter-spacing:.05em; text-align:left; }
    .sa-table td { padding:13px 11px; border-bottom:1px solid #edf2f7; vertical-align:middle; color:#334155; font-size:.8rem; }
    .sa-table tbody tr:last-child td { border-bottom:0; }
    .sa-table tbody tr:hover { background:#f8fbff; }
    .sa-meter { display:flex; align-items:flex-start; gap:9px; }
    .sa-meter-icon { width:34px; height:34px; flex:0 0 34px; display:grid; place-items:center; border-radius:10px; color:#2563eb; background:#eff6ff; }
    .sa-meter a { color:#172554; text-decoration:none; font-weight:900; line-height:1.25; }
    .sa-meter-facility { margin-top:3px; color:#64748b; font-size:.7rem; font-weight:650; }
    .sa-number { font-variant-numeric:tabular-nums; font-weight:900; color:#0f172a; }
    .sa-unit { display:block; margin-top:2px; color:#94a3b8; font-size:.61rem; font-weight:800; text-transform:uppercase; }
    .sa-baseline-source { display:block; margin-top:3px; color:#64748b; font-size:.62rem; font-weight:700; }
    .sa-variance { font-weight:950; }
    .sa-variance.up { color:#be123c; }
    .sa-variance.down { color:#4338ca; }
    .sa-pill { display:inline-flex; align-items:center; justify-content:center; gap:5px; min-width:98px; padding:6px 9px; border:1px solid transparent; border-radius:999px; font-size:.66rem; line-height:1.15; text-align:center; font-weight:900; }
    .sa-pill.critical { color:#991b1b; background:#fee2e2; border-color:#fecaca; }
    .sa-pill.very-high { color:#9a3412; background:#ffedd5; border-color:#fdba74; }
    .sa-pill.high { color:#a16207; background:#fef3c7; border-color:#fcd34d; }
    .sa-pill.warning { color:#92400e; background:#fffbeb; border-color:#fde68a; }
    .sa-pill.drop-critical { color:#6d28d9; background:#ede9fe; border-color:#c4b5fd; }
    .sa-pill.drop-high { color:#4338ca; background:#e0e7ff; border-color:#a5b4fc; }
    .sa-pill.drop-warning { color:#0e7490; background:#cffafe; border-color:#67e8f9; }
    .sa-reason { color:#475569; line-height:1.45; font-weight:600; }
    .sa-row-action { display:inline-flex; align-items:center; gap:5px; margin-top:7px; color:#2563eb; text-decoration:none; font-size:.69rem; font-weight:850; }
    .sa-empty { padding:46px 20px !important; text-align:center; }
    .sa-empty-icon { width:52px; height:52px; display:grid; place-items:center; margin:0 auto 10px; border-radius:16px; color:#059669; background:#ecfdf5; font-size:1.25rem; }
    .sa-empty strong { display:block; color:#334155; font-size:.95rem; }
    .sa-empty span { display:block; margin-top:5px; color:#64748b; }
    .sa-footer { padding:12px 16px; border-top:1px solid #e2e8f0; background:#fcfdff; }

    body.dark-mode .sa-hero, body.dark-mode .sa-flow, body.dark-mode .sa-kpi, body.dark-mode .sa-panel { background:#111827; border-color:#334155; }
    body.dark-mode .sa-title, body.dark-mode .sa-panel-title, body.dark-mode .sa-kpi-value, body.dark-mode .sa-flow-title, body.dark-mode .sa-number, body.dark-mode .sa-meter a { color:#f1f5f9; }
    body.dark-mode .sa-filter, body.dark-mode .sa-table th, body.dark-mode .sa-footer { background:#0f172a; border-color:#334155; }
    body.dark-mode .sa-input, body.dark-mode .sa-back { color:#e2e8f0; background:#111827; border-color:#475569; }
    body.dark-mode .sa-table-shell, body.dark-mode .sa-table td, body.dark-mode .sa-flow-step { border-color:#334155; }
    body.dark-mode .sa-table tbody tr:hover { background:#172033; }

    @media (max-width:1050px) {
        .sa-flow, .sa-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-flow-step:nth-child(2) { border-right:0; }
        .sa-flow-step:nth-child(-n+2) { border-bottom:1px solid #e2e8f0; }
        .sa-filter { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-actions { grid-column:1/-1; }
    }
    @media (max-width:720px) {
        .sa-page { padding:8px; }
        .sa-hero { padding:18px; flex-direction:column; }
        .sa-back { width:100%; }
        .sa-flow, .sa-kpis, .sa-filter { grid-template-columns:1fr; }
        .sa-flow-step { border-right:0; border-bottom:1px solid #e2e8f0; }
        .sa-flow-step:last-child { border-bottom:0; }
        .sa-actions .sa-btn { flex:1; }
        .sa-table-wrap { padding:10px; overflow:visible; }
        .sa-table-shell { border:0; overflow:visible; }
        .sa-table, .sa-table tbody { display:block; }
        .sa-table thead, .sa-table colgroup { display:none; }
        .sa-table tbody { display:grid; gap:10px; }
        .sa-table tbody tr { display:grid; grid-template-columns:1fr 1fr; overflow:hidden; border:1px solid #dbe5f2; border-radius:13px; background:#fff; }
        .sa-table td { display:block; border-bottom:1px solid #edf2f7; }
        .sa-table td::before { content:attr(data-label); display:block; margin-bottom:5px; color:#94a3b8; font-size:.61rem; font-weight:850; text-transform:uppercase; }
        .sa-table td:first-child, .sa-table td:last-child { grid-column:1/-1; }
        body.dark-mode .sa-table tbody tr { background:#111827; border-color:#334155; }
    }
</style>

@section('content')
@include('layouts.partials.energy_monitoring_switcher', ['energyTab' => 'sub'])
@php
    $summary = $alertSummary ?? ['total' => 0, 'critical' => 0, 'increases' => 0, 'drops' => 0];
    $baselineLabels = [
        'configured_meter' => 'Configured baseline',
        'normalized_per_day' => 'Normalized/day',
        'moving_avg_3' => '3-period average',
        'seasonal_month' => 'Seasonal baseline',
        'moving_avg_6' => '6-period average',
        'equipment_estimate' => 'Equipment estimate',
        'alert' => 'Stored alert baseline',
    ];
@endphp

<div class="sa-page">
    <header class="sa-hero">
        <div class="sa-heading">
            <span class="sa-heading-icon"><i class="fa-solid fa-bell"></i></span>
            <div>
                <h1 class="sa-title">Submeter Alert Review</h1>
                <div class="sa-subtitle">Actionable deviations calculated live from readings, each submeter baseline, and your threshold settings.</div>
            </div>
        </div>
        <a href="{{ route('modules.submeters.monitoring', ['month' => $selectedMonth, 'period_type' => $selectedPeriodType, 'facility_id' => $selectedFacility]) }}" class="sa-back"><i class="fa-solid fa-arrow-left"></i> Back to Monitoring</a>
    </header>

    <section class="sa-flow" aria-label="Alert evaluation flow">
        <div class="sa-flow-step"><span class="sa-flow-number">1</span><div><div class="sa-flow-title">Receive reading</div><div class="sa-flow-text">Use the latest IoT reading for each submeter in the selected period.</div></div></div>
        <div class="sa-flow-step"><span class="sa-flow-number">2</span><div><div class="sa-flow-title">Match baseline</div><div class="sa-flow-text">Prefer computed baseline, then the configured submeter baseline.</div></div></div>
        <div class="sa-flow-step"><span class="sa-flow-number">3</span><div><div class="sa-flow-title">Apply thresholds</div><div class="sa-flow-text">Evaluate increase or drop using the baseline-size rules in Settings.</div></div></div>
        <div class="sa-flow-step"><span class="sa-flow-number">4</span><div><div class="sa-flow-title">Review and act</div><div class="sa-flow-text">Open the meter, validate the cause, and follow the recommended checks.</div></div></div>
    </section>

    <section class="sa-kpis">
        <article class="sa-kpi total"><div class="sa-kpi-top"><span class="sa-kpi-label">Actionable alerts</span><span class="sa-kpi-icon"><i class="fa-solid fa-bell"></i></span></div><div class="sa-kpi-value">{{ number_format((int) $summary['total']) }}</div></article>
        <article class="sa-kpi critical"><div class="sa-kpi-top"><span class="sa-kpi-label">Critical priority</span><span class="sa-kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></span></div><div class="sa-kpi-value">{{ number_format((int) $summary['critical']) }}</div></article>
        <article class="sa-kpi increase"><div class="sa-kpi-top"><span class="sa-kpi-label">Above baseline</span><span class="sa-kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></span></div><div class="sa-kpi-value">{{ number_format((int) $summary['increases']) }}</div></article>
        <article class="sa-kpi drop"><div class="sa-kpi-top"><span class="sa-kpi-label">Below baseline</span><span class="sa-kpi-icon"><i class="fa-solid fa-arrow-trend-down"></i></span></div><div class="sa-kpi-value">{{ number_format((int) $summary['drops']) }}</div></article>
    </section>

    <section class="sa-panel">
        <div class="sa-panel-head"><h2 class="sa-panel-title">Alert queue</h2><span class="sa-count"><i class="fa-solid fa-list-check"></i> {{ $alerts->total() }} matching</span></div>
        <form method="GET" action="{{ route('modules.submeters.alerts') }}" class="sa-filter">
            <div class="sa-field"><label for="alert_month">Month</label><input id="alert_month" class="sa-input" type="month" name="month" value="{{ $selectedMonth }}"></div>
            <div class="sa-field"><label for="alert_period">Period</label><select id="alert_period" class="sa-input" name="period_type"><option value="daily" @selected($selectedPeriodType === 'daily')>Daily</option><option value="weekly" @selected($selectedPeriodType === 'weekly')>Weekly</option><option value="monthly" @selected($selectedPeriodType === 'monthly')>Monthly</option></select></div>
            <div class="sa-field"><label for="alert_facility">Facility</label><select id="alert_facility" class="sa-input" name="facility_id"><option value="">All Facilities</option>@foreach($facilities as $facility)<option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>@endforeach</select></div>
            <div class="sa-field"><label for="alert_level">Alert level</label><select id="alert_level" class="sa-input" name="alert_level"><option value="">All actionable</option><option value="warning" @selected($selectedLevel === 'warning')>Warning</option><option value="high" @selected($selectedLevel === 'high')>High</option><option value="very_high" @selected($selectedLevel === 'very_high')>Very High</option><option value="critical" @selected($selectedLevel === 'critical')>Critical</option><option value="drop_warning" @selected($selectedLevel === 'drop_warning')>Drop Warning</option><option value="drop_high" @selected($selectedLevel === 'drop_high')>Drop High</option><option value="drop_critical" @selected($selectedLevel === 'drop_critical')>Drop Critical</option></select></div>
            <div class="sa-actions"><button class="sa-btn primary" type="submit"><i class="fa-solid fa-filter"></i> Apply</button><a class="sa-btn soft" href="{{ route('modules.submeters.alerts') }}"><i class="fa-solid fa-rotate-left"></i> Reset</a></div>
        </form>

        <div class="sa-table-wrap"><div class="sa-table-shell">
            <table class="sa-table">
                <colgroup><col style="width:20%"><col style="width:10%"><col style="width:11%"><col style="width:12%"><col style="width:9%"><col style="width:12%"><col style="width:26%"></colgroup>
                <thead><tr><th>Submeter / Facility</th><th>Period</th><th style="text-align:right">Actual</th><th style="text-align:right">Baseline</th><th style="text-align:right">Variance</th><th style="text-align:center">Status</th><th>Reason / Action</th></tr></thead>
                <tbody>
                @forelse($alerts as $alert)
                    @php
                        $level = (string) $alert->alert_evaluated_level;
                        [$levelLabel, $pillClass, $levelIcon] = match ($level) {
                            'critical' => ['CRITICAL', 'critical', 'fa-triangle-exclamation'],
                            'very_high' => ['VERY HIGH', 'very-high', 'fa-arrow-trend-up'],
                            'high' => ['HIGH', 'high', 'fa-arrow-trend-up'],
                            'warning' => ['WARNING', 'warning', 'fa-circle-exclamation'],
                            'drop_critical' => ['DROP CRITICAL', 'drop-critical', 'fa-arrow-trend-down'],
                            'drop_high' => ['DROP HIGH', 'drop-high', 'fa-arrow-trend-down'],
                            'drop_warning' => ['DROP WARNING', 'drop-warning', 'fa-arrow-trend-down'],
                            default => ['NOT EVALUATED', 'warning', 'fa-circle-minus'],
                        };
                        $variance = (float) $alert->alert_variance_percent;
                        $detailUrl = route('modules.submeters.show', [
                            'submeter' => $alert->submeter_id,
                            'period_type' => $selectedPeriodType,
                            'return_period_type' => $selectedPeriodType,
                            'from' => 'alerts',
                            'month' => $selectedMonth,
                            'facility_id' => $selectedFacility,
                            'alert_level' => $selectedLevel,
                        ]);
                    @endphp
                    <tr>
                        <td data-label="Submeter / Facility"><div class="sa-meter"><span class="sa-meter-icon"><i class="fa-solid fa-gauge-high"></i></span><div><a href="{{ $detailUrl }}">{{ $alert->submeter?->submeter_name ?? 'Unknown submeter' }}</a><div class="sa-meter-facility">{{ $alert->submeter?->facility?->name ?? 'Unknown facility' }}</div></div></div></td>
                        <td data-label="Period"><span class="sa-number">{{ $alert->periodLabel() }}</span><span class="sa-unit">{{ $alert->period_type }}</span></td>
                        <td data-label="Actual" style="text-align:right"><span class="sa-number">{{ number_format((float) $alert->kwh_used, 2) }}</span><span class="sa-unit">kWh</span></td>
                        <td data-label="Baseline" style="text-align:right"><span class="sa-number">{{ number_format((float) $alert->alert_baseline_kwh, 2) }}</span><span class="sa-unit">kWh</span><span class="sa-baseline-source">{{ $baselineLabels[$alert->alert_baseline_source] ?? 'Baseline' }}</span></td>
                        <td data-label="Variance" style="text-align:right"><span class="sa-variance {{ $variance < 0 ? 'down' : 'up' }}">{{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}%</span></td>
                        <td data-label="Status" style="text-align:center"><span class="sa-pill {{ $pillClass }}"><i class="fa-solid {{ $levelIcon }}"></i> {{ $levelLabel }}</span></td>
                        <td data-label="Reason / Action"><div class="sa-reason">{{ $alert->alert_reason }}</div><a class="sa-row-action" href="{{ $detailUrl }}">Review meter details <i class="fa-solid fa-arrow-right"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="sa-empty"><div class="sa-empty-icon"><i class="fa-solid fa-circle-check"></i></div><strong>No actionable alerts for these filters</strong><span>All evaluated readings are within their configured threshold range, or no valid readings are available.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
        @if($alerts->hasPages())<div class="sa-footer">{{ $alerts->links() }}</div>@endif
    </section>
</div>
@endsection
