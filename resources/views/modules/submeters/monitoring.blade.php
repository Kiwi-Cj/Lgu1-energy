@extends('layouts.qc-admin')
@section('title', 'Submeter Monitoring')

@section('content')
<style>
    .submeter-shell {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding-bottom: 8px;
    }

    .report-card-container.submeter-card-container {
        width: 100%;
        padding: 24px;
        border: 1px solid #dbe5f2;
        border-radius: 24px;
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 58%, #eef4ff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
        box-sizing: border-box;
    }

    .submeter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .submeter-card-body {
        padding: 18px 20px;
    }

    .submeter-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
    }

    .submeter-header-identity {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .submeter-header-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #6366f1);
        box-shadow: 0 9px 20px rgba(37,99,235,.2);
        font-size: 1.25rem;
    }

    .submeter-header h1 {
        margin: 0;
        color: #2563eb;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .submeter-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .92rem;
    }

    .submeter-context-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 10px;
    }

    .submeter-context-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border: 1px solid #dbe5f2;
        border-radius: 999px;
        color: #475569;
        background: #fff;
        font-size: .7rem;
        font-weight: 800;
    }

    .submeter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Timeframe Switcher */
    .submeter-timeframe-switch {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .submeter-timeframe-btn {
        text-decoration: none;
        padding: 7px 14px;
        font-size: 0.8rem;
        font-weight: 800;
        border-radius: 9px;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }

    .submeter-timeframe-btn.is-active {
        background: #ffffff;
        color: #2563eb;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
    }

    .submeter-action-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        min-height: 42px;
        padding: 0 16px;
        font-weight: 800;
        font-size: .88rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .submeter-action-btn:hover {
        transform: translateY(-1px);
    }

    .submeter-action-btn.is-primary {
        background: linear-gradient(90deg, #2563eb, #6366f1);
        color: #fff;
        border: none;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.22);
    }

    .submeter-action-btn.is-warn {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }

    .submeter-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .submeter-kpi-card {
        position: relative;
        overflow: hidden;
        min-height: 106px;
        padding: 16px 18px;
        border: 1px solid #dbe5f2;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
    }

    .submeter-kpi-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: var(--kpi-accent, #2563eb);
    }

    .submeter-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .submeter-kpi-label {
        color: #64748b;
        font-size: .68rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .045em;
    }

    .submeter-kpi-icon {
        width: 33px;
        height: 33px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        color: var(--kpi-accent, #2563eb);
        background: var(--kpi-soft, #eff6ff);
        font-size: 1rem;
    }

    .submeter-kpi-value {
        margin-top: 10px;
        color: #0f172a;
        font-size: 1.4rem;
        line-height: 1;
        font-weight: 950;
    }

    .submeter-kpi-note {
        margin-top: 6px;
        color: #64748b;
        font-size: .66rem;
        font-weight: 650;
    }

    .submeter-kpi-card.records { --kpi-accent: #2563eb; --kpi-soft: #eff6ff; }
    .submeter-kpi-card.approved { --kpi-accent: #059669; --kpi-soft: #ecfdf5; }
    .submeter-kpi-card.pending { --kpi-accent: #8b5cf6; --kpi-soft: #f5f3ff; }
    .submeter-kpi-card.attention { --kpi-accent: #e11d48; --kpi-soft: #fff1f2; }

    .submeter-analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .submeter-box-header {
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fcfdff;
    }

    .submeter-box-title {
        color: #0f172a;
        font-weight: 800;
        font-size: .95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .submeter-distribution-list {
        max-height: 255px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .submeter-distribution-list::-webkit-scrollbar,
    .submeter-table-wrap::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .submeter-distribution-list::-webkit-scrollbar-track,
    .submeter-table-wrap::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .submeter-distribution-list::-webkit-scrollbar-thumb,
    .submeter-table-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .submeter-distribution-list::-webkit-scrollbar-thumb:hover,
    .submeter-table-wrap::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .submeter-distribution-item {
        padding: 12px 18px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .submeter-distribution-item:last-child {
        border-bottom: none;
    }

    .submeter-bar-track {
        width: 100%;
        height: 8px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }

    .submeter-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #6366f1);
    }

    .submeter-reconcile-card {
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .submeter-recon-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .submeter-recon-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 700;
    }

    .submeter-recon-val {
        font-size: 0.95rem;
        font-weight: 900;
        color: #0f172a;
    }

    .submeter-filter-bar {
        padding: 12px 18px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .submeter-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
    }

    .submeter-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .submeter-field label {
        color: #475569;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .submeter-field select {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 7px 12px;
        font-size: .85rem;
        color: #1e293b;
        background: #fff;
        min-height: 38px;
    }

    .submeter-table-wrap {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 440px;
        background: #ffffff;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .submeter-table {
        width: 100%;
        min-width: 900px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .submeter-table th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8fafc;
        color: #475569;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .035em;
        text-align: left;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .submeter-table td {
        color: #1e293b;
        font-size: .82rem;
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .submeter-table tbody tr:hover {
        background: #f1f7ff;
    }

    .submeter-dial-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        font-size: 0.72rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .submeter-dial-chip i {
        color: #15803d;
        font-size: 0.72rem;
    }

    .submeter-dial-chip .dial-arrow {
        color: #22c55e;
        font-weight: 900;
        font-size: 0.74rem;
    }

    @media (max-width: 900px) {
        .submeter-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .submeter-analytics-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
        .submeter-kpi-grid { grid-template-columns: 1fr; }
        .report-card-container.submeter-card-container { padding: 14px; border-radius: 18px; }
    }

    /* Dark Mode Styles */
    body.dark-mode .submeter-card { background: #111827; border-color: #334155; }
    body.dark-mode .submeter-card-container { background: #0f172a; border-color: #334155; }
    body.dark-mode .submeter-table th { background: linear-gradient(180deg, #1e293b 0%, #111827 100%) !important; color: #cbd5e1 !important; border-color: #334155 !important; box-shadow: inset 0 -1px 0 #334155; }
    body.dark-mode .submeter-table td { border-color: #334155; color: #e2e8f0; }
    body.dark-mode .submeter-table tbody tr:hover { background: #1e293b; }
    body.dark-mode .submeter-kpi-card { background: #111827; border-color: #334155; }
    body.dark-mode .submeter-kpi-value { color: #f8fafc; }
    body.dark-mode .submeter-kpi-label, body.dark-mode .submeter-kpi-note { color: #94a3b8; }
    body.dark-mode .submeter-context-chip { background: #1e293b; border-color: #334155; color: #cbd5e1; }
    body.dark-mode .submeter-box-header { background: #111827; border-color: #334155; }
    body.dark-mode .submeter-box-title { color: #f8fafc; }
    body.dark-mode .submeter-reconcile-card { background: #0f172a; border-color: #334155; }
    body.dark-mode .submeter-recon-item { background: #111827; border-color: #334155; }
    body.dark-mode .submeter-recon-val { color: #f8fafc; }
</style>

<div class="submeter-shell">
    <div class="report-card-container submeter-card-container">

        {{-- Header Card --}}
        <div class="submeter-card" style="margin-bottom:14px;">
            <div class="submeter-card-body">
                <div class="submeter-header">
                    <div class="submeter-header-identity">
                        <span class="submeter-header-icon"><i class="fa-solid fa-diagram-project"></i></span>
                        <div>
                            <h1>Submeter Monitoring & Downstream Accounting</h1>
                            <p>Track department & equipment-level electricity consumption, baseline variance, and reconciliation against facility main meters.</p>
                            <div class="submeter-context-chips">
                                <span class="submeter-context-chip"><i class="fa-solid fa-calendar"></i> {{ $timeframe === 'weekly' ? 'Weekly Monitoring' : 'Monthly Monitoring' }} ({{ $selectedYear }})</span>
                                <span class="submeter-context-chip"><i class="fa-solid fa-code-branch"></i> {{ $submeterCount }} Active Submeters</span>
                                @if($selectedFacilityId > 0)
                                    @php $activeFac = $facilitiesWithSubmeters->firstWhere('id', $selectedFacilityId); @endphp
                                    <span class="submeter-context-chip" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
                                        <i class="fa-solid fa-building"></i> {{ $activeFac ? $activeFac->name : 'Filtered Facility' }}
                                    </span>
                                @else
                                    <span class="submeter-context-chip"><i class="fa-solid fa-city"></i> All Facilities</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="submeter-actions">
                        {{-- Timeframe Switcher --}}
                        <div class="submeter-timeframe-switch">
                            <a href="{{ route('modules.submeters.monitoring', array_merge(request()->query(), ['timeframe' => 'monthly'])) }}"
                               class="submeter-timeframe-btn {{ $timeframe === 'monthly' ? 'is-active' : '' }}">
                                <i class="fa-solid fa-calendar-days"></i> Monthly View
                            </a>
                            <a href="{{ route('modules.submeters.monitoring', array_merge(request()->query(), ['timeframe' => 'weekly'])) }}"
                               class="submeter-timeframe-btn {{ $timeframe === 'weekly' ? 'is-active' : '' }}">
                                <i class="fa-solid fa-chart-column"></i> Weekly View
                            </a>
                        </div>

                        @if($selectedFacilityId > 0)
                            <a href="{{ route($timeframe === 'weekly' ? 'facilities.weekly-records.submeters' : 'facilities.monthly-records.submeters', $selectedFacilityId) }}" class="submeter-action-btn is-primary">
                                <i class="fa-solid fa-table-list"></i> Encode / View {{ $timeframe === 'weekly' ? 'Weekly' : 'Monthly' }} Records
                            </a>
                        @endif
                        <a href="{{ route('modules.submeters.alerts') }}" class="submeter-action-btn is-warn">
                            <i class="fa-solid fa-triangle-exclamation"></i> Review Alerts
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 Executive KPI Cards --}}
        <section class="submeter-kpi-grid">
            <article class="submeter-kpi-card records">
                <div class="submeter-kpi-top">
                    <span class="submeter-kpi-label">{{ $timeframe === 'weekly' ? 'Total Submeter Energy' : 'Submeter Energy (kWh)' }}</span>
                    <span class="submeter-kpi-icon"><i class="fa-solid fa-bolt"></i></span>
                </div>
                <div class="submeter-kpi-value">{{ number_format($totalSubmeterKwh, 2) }}</div>
                <div class="submeter-kpi-note">{{ $timeframe === 'weekly' ? 'Cumulative period load' : 'Total downstream consumption' }}</div>
            </article>

            <article class="submeter-kpi-card approved">
                <div class="submeter-kpi-top">
                    <span class="submeter-kpi-label">Total Submeter Cost</span>
                    <span class="submeter-kpi-icon"><i class="fa-solid fa-peso-sign"></i></span>
                </div>
                <div class="submeter-kpi-value">PHP {{ number_format($totalSubmeterCost, 2) }}</div>
                <div class="submeter-kpi-note">Calculated electricity cost</div>
            </article>

            <article class="submeter-kpi-card pending">
                <div class="submeter-kpi-top">
                    <span class="submeter-kpi-label">Main Meter Coverage</span>
                    <span class="submeter-kpi-icon"><i class="fa-solid fa-chart-pie"></i></span>
                </div>
                <div class="submeter-kpi-value">{{ $accountedPercent > 0 ? $accountedPercent . '%' : 'N/A' }}</div>
                <div class="submeter-kpi-note">Accounted of Main utility load</div>
            </article>

            <article class="submeter-kpi-card attention">
                <div class="submeter-kpi-top">
                    <span class="submeter-kpi-label">Variance Alerts</span>
                    <span class="submeter-kpi-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                </div>
                <div class="submeter-kpi-value">{{ $criticalAlertCount }}</div>
                <div class="submeter-kpi-note">Readings exceeding baseline</div>
            </article>
        </section>

        @if($timeframe === 'weekly')
            {{-- Weekly Analytics Section: Weekly Trend Bar Chart & Weekly Profile --}}
            <div class="submeter-analytics-grid">
                {{-- Left: Weekly Trend Progression Chart --}}
                <div class="submeter-card">
                    <div class="submeter-box-header">
                        <div class="submeter-box-title">
                            <i class="fa-solid fa-chart-column" style="color:#2563eb;"></i> Weekly Submeter Progression Trend
                        </div>
                        <span style="font-size:0.75rem;color:#2563eb;font-weight:800;">
                            {{ $selectedYear }} {{ $monthLabels[$selectedMonth] ?? 'Month' }}
                        </span>
                    </div>
                    <div class="submeter-card-body" style="padding:16px;">
                        <div style="position:relative;height:240px;width:100%;">
                            <canvas id="submeterWeeklyBarChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Right: Main Meter vs Submeters Weekly Reconciliation --}}
                <div class="submeter-card">
                    <div class="submeter-box-header">
                        <div class="submeter-box-title">
                            <i class="fa-solid fa-scale-balanced" style="color:#059669;"></i> Main vs. Submeters Reconciliation
                        </div>
                        <span style="font-size:0.75rem;color:#059669;font-weight:800;">
                            {{ $accountedPercent > 0 ? $accountedPercent . '% Accounted' : 'Reconciliation' }}
                        </span>
                    </div>
                    <div class="submeter-reconcile-card">
                        <div class="submeter-recon-item" style="border-left:4px solid #2563eb;">
                            <div>
                                <div class="submeter-recon-label">MAIN UTILITY METER USAGE</div>
                                <div style="font-size:0.72rem;color:#64748b;">Facility master energy bill</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="submeter-recon-val">{{ number_format($mainMeterKwh, 2) }} kWh</div>
                                <div style="font-size:0.75rem;color:#15803d;font-weight:800;">PHP {{ number_format($mainMeterCost, 2) }}</div>
                            </div>
                        </div>

                        <div class="submeter-recon-item" style="border-left:4px solid #10b981;">
                            <div>
                                <div class="submeter-recon-label">DOWNSTREAM SUBMETERS TOTAL</div>
                                <div style="font-size:0.72rem;color:#64748b;">Sum of all monitored submeters</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="submeter-recon-val" style="color:#059669;">{{ number_format($totalSubmeterKwh, 2) }} kWh</div>
                                <div style="font-size:0.75rem;color:#15803d;font-weight:800;">PHP {{ number_format($totalSubmeterCost, 2) }}</div>
                            </div>
                        </div>

                        <div class="submeter-recon-item" style="border-left:4px solid #f59e0b;background:#fffbeb;">
                            <div>
                                <div class="submeter-recon-label" style="color:#92400e;">UNACCOUNTED / GENERAL BASE LOAD</div>
                                <div style="font-size:0.72rem;color:#b45309;">Distribution losses or unmetered circuits</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="submeter-recon-val" style="color:#b45309;">{{ number_format($unaccountedKwh, 2) }} kWh</div>
                                <div style="font-size:0.75rem;color:#92400e;font-weight:800;">PHP {{ number_format($unaccountedCost, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Monthly Analytics Section: Donut Distribution & Reconciliation --}}
            <div class="submeter-analytics-grid">
                {{-- Left: Submeter Energy Share Distribution with Donut Chart --}}
                <div class="submeter-card">
                    <div class="submeter-box-header">
                        <div class="submeter-box-title">
                            <i class="fa-solid fa-chart-pie" style="color:#2563eb;"></i> Submeter Energy Distribution
                        </div>
                        <span style="font-size:0.75rem;color:#64748b;font-weight:700;">Share of total usage</span>
                    </div>
                    
                    @if($submeterLoadShares->isNotEmpty() && $totalSubmeterKwh > 0)
                        <div style="display:flex;align-items:center;justify-content:center;padding:16px 16px 12px;gap:18px;border-bottom:1px solid #f1f5f9;background:#fcfdff;flex-wrap:wrap;">
                            <div style="position:relative;width:140px;height:140px;flex:0 0 140px;">
                                <canvas id="submeterDistributionChart" width="140" height="140"></canvas>
                                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                                    <span style="font-size:0.62rem;font-weight:850;color:#64748b;text-transform:uppercase;">Total</span>
                                    <strong style="font-size:0.85rem;font-weight:900;color:#0f172a;line-height:1.1;">{{ number_format($totalSubmeterKwh, 0) }}</strong>
                                    <small style="font-size:0.6rem;color:#64748b;font-weight:700;">kWh</small>
                                </div>
                            </div>
                            <div style="flex:1;min-width:180px;display:flex;flex-direction:column;gap:6px;">
                                <div style="font-size:0.78rem;font-weight:850;color:#1e293b;">Load Share Overview</div>
                                <div style="font-size:0.72rem;color:#64748b;">Visual breakdown of electricity consumption per monitored submeter.</div>
                                <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:2px;">
                                    @php
                                        $palette = ['#2563eb', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#14b8a6'];
                                    @endphp
                                    @foreach($submeterLoadShares->take(4) as $idx => $share)
                                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.68rem;font-weight:750;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;padding:2px 7px;border-radius:6px;">
                                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $palette[$idx % count($palette)] }};display:inline-block;"></span>
                                            {{ Str::limit($share['meter_name'], 15) }}: <strong>{{ $share['share_percent'] }}%</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="submeter-card-body submeter-distribution-list" style="padding:0;">
                        @php
                            $palette = ['#2563eb', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#14b8a6'];
                        @endphp
                        @forelse($submeterLoadShares as $idx => $share)
                            @php $color = $palette[$idx % count($palette)]; @endphp
                            <div class="submeter-distribution-item">
                                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.84rem;">
                                    <div style="display:flex;align-items:center;gap:7px;min-width:0;">
                                        <span style="width:9px;height:9px;border-radius:50%;background:{{ $color }};flex:0 0 9px;"></span>
                                        <strong style="color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $share['meter_name'] }}</strong>
                                        <span style="font-size:0.72rem;color:#64748b;margin-left:2px;">({{ $share['facility_name'] }})</span>
                                    </div>
                                    <div style="display:flex;gap:8px;align-items:center;flex:0 0 auto;">
                                        <span style="font-weight:800;color:#2563eb;">{{ number_format($share['total_kwh'], 2) }} kWh</span>
                                        <span style="font-weight:900;color:#0f172a;background:#eff6ff;padding:2px 7px;border-radius:6px;font-size:0.75rem;">{{ $share['share_percent'] }}%</span>
                                    </div>
                                </div>
                                <div class="submeter-bar-track">
                                    <div class="submeter-bar-fill" style="width: {{ min(100, max(4, $share['share_percent'])) }}%; background: {{ $color }};"></div>
                                </div>
                            </div>
                        @empty
                            <div style="padding:28px 16px;text-align:center;color:#64748b;font-size:0.85rem;">
                                No submeter load records available for the selected period.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right: Main Meter vs Submeters Reconciliation --}}
                <div class="submeter-card">
                    <div class="submeter-box-header">
                        <div class="submeter-box-title">
                            <i class="fa-solid fa-scale-balanced" style="color:#059669;"></i> Main vs. Submeters Reconciliation
                        </div>
                        <span style="font-size:0.75rem;color:#059669;font-weight:800;">
                            {{ $accountedPercent > 0 ? $accountedPercent . '% Accounted' : 'Reconciliation' }}
                        </span>
                    </div>
                    <div class="submeter-reconcile-card">
                        <div class="submeter-recon-item" style="border-left:4px solid #2563eb;">
                            <div>
                                <div class="submeter-recon-label">MAIN UTILITY METER USAGE</div>
                                <div style="font-size:0.72rem;color:#64748b;">Facility master energy bill</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="submeter-recon-val">{{ number_format($mainMeterKwh, 2) }} kWh</div>
                                <div style="font-size:0.75rem;color:#15803d;font-weight:800;">PHP {{ number_format($mainMeterCost, 2) }}</div>
                            </div>
                        </div>

                        <div class="submeter-recon-item" style="border-left:4px solid #10b981;">
                            <div>
                                <div class="submeter-recon-label">DOWNSTREAM SUBMETERS TOTAL</div>
                                <div style="font-size:0.72rem;color:#64748b;">Sum of all monitored submeters</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="submeter-recon-val" style="color:#059669;">{{ number_format($totalSubmeterKwh, 2) }} kWh</div>
                                <div style="font-size:0.75rem;color:#15803d;font-weight:800;">PHP {{ number_format($totalSubmeterCost, 2) }}</div>
                            </div>
                        </div>

                        <div class="submeter-recon-item" style="border-left:4px solid #f59e0b;background:#fffbeb;">
                            <div>
                                <div class="submeter-recon-label" style="color:#92400e;">UNACCOUNTED / GENERAL BASE LOAD</div>
                                <div style="font-size:0.72rem;color:#b45309;">Distribution losses or unmetered circuits</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="submeter-recon-val" style="color:#b45309;">{{ number_format($unaccountedKwh, 2) }} kWh</div>
                                <div style="font-size:0.75rem;color:#92400e;font-weight:800;">PHP {{ number_format($unaccountedCost, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Table Card: Submeter Records Ledger --}}
        <div class="submeter-card">
            <div class="submeter-filter-bar">
                <div style="min-width:140px;">
                    <strong style="display:flex;align-items:center;gap:6px;font-size:0.85rem;color:#0f172a;">
                        <i class="fa-solid fa-filter" style="color:#2563eb;"></i> Filter Ledger
                    </strong>
                    <span style="font-size:0.7rem;color:#64748b;">{{ $timeframe === 'weekly' ? 'Weekly records view' : 'Monthly records view' }}</span>
                </div>

                <form method="GET" action="{{ route('modules.submeters.monitoring') }}" class="submeter-filter-form">
                    <input type="hidden" name="timeframe" value="{{ $timeframe }}">

                    <div class="submeter-field" style="min-width:220px;flex:1;">
                        <label>Facility</label>
                        <select name="facility_id" onchange="this.form.submit()">
                            <option value="0" @selected($selectedFacilityId === 0)>All Facilities with Submeters</option>
                            @foreach($facilitiesWithSubmeters as $fac)
                                <option value="{{ $fac->id }}" @selected($selectedFacilityId === (int) $fac->id)>{{ $fac->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="submeter-field" style="min-width:110px;">
                        <label>Year</label>
                        <select name="year" onchange="this.form.submit()">
                            @foreach($yearOptions as $yr)
                                <option value="{{ $yr }}" @selected((int) $selectedYear === (int) $yr)>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="submeter-field" style="min-width:130px;">
                        <label>Month</label>
                        <select name="month" onchange="this.form.submit()">
                            @if($timeframe === 'monthly')
                                <option value="0" @selected($selectedMonth === 0)>All Months</option>
                            @endif
                            @foreach($monthLabels as $num => $lbl)
                                <option value="{{ $num }}" @selected($selectedMonth === (int) $num)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($timeframe === 'weekly')
                        <div class="submeter-field" style="min-width:150px;">
                            <label>Week</label>
                            <select name="week" onchange="this.form.submit()">
                                <option value="0" @selected($selectedWeek === 0)>All 4 Weeks</option>
                                @foreach(($weekLabels ?? [1 => 'Week 1 (Days 1–7)', 2 => 'Week 2 (Days 8–14)', 3 => 'Week 3 (Days 15–21)', 4 => 'Week 4 (Days 22–End)']) as $wNum => $wLabel)
                                    <option value="{{ $wNum }}" @selected($selectedWeek === $wNum)>{{ $wLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('modules.submeters.monitoring', ['timeframe' => $timeframe]) }}" style="border:1px solid #cbd5e1;background:#f8fafc;color:#334155;border-radius:10px;padding:8px 14px;font-size:.82rem;font-weight:800;text-decoration:none;min-height:38px;display:inline-flex;align-items:center;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="submeter-table-wrap">
                <table class="submeter-table">
                    <thead>
                        <tr>
                            <th style="padding-left:18px;">Facility / Sub-meter</th>
                            <th>Parent Meter</th>
                            <th>{{ $timeframe === 'weekly' ? 'Weekly Period' : 'Period' }}</th>
                            <th>Consumption (kWh)</th>
                            <th>Baseline (kWh)</th>
                            <th>Variance %</th>
                            <th>Status</th>
                            <th>Cost (PHP)</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $displayRows = $timeframe === 'weekly' ? $weeklyRows : $mappedRows;
                        @endphp
                        @forelse($displayRows as $row)
                            @php
                                $prev = $row['previous_reading_kwh'];
                                $curr = $row['current_reading_kwh'];
                                $hasDials = ($prev !== null && $curr !== null);
                            @endphp
                            <tr>
                                <td style="padding-left:18px;">
                                    <div style="font-weight:800;color:#0f172a;font-size:0.88rem;">
                                        {{ $row['meter_name'] }}
                                    </div>
                                    <div style="font-size:0.72rem;color:#64748b;font-weight:700;margin-top:2px;">
                                        <i class="fa-solid fa-building" style="color:#94a3b8;font-size:0.68rem;"></i> {{ $row['facility_name'] }}
                                    </div>
                                </td>

                                <td style="color:#475569;font-weight:700;">
                                    {{ $row['parent_meter_name'] }}
                                </td>

                                <td style="font-weight:800;color:#1e293b;">
                                    @if($timeframe === 'weekly')
                                        <span style="display:inline-block;padding:2px 8px;border-radius:6px;background:#eff6ff;color:#1d4ed8;font-size:0.76rem;">
                                            {{ $row['period_display'] ?? ($row['year'] . ' Week ' . ($row['week_num'] ?? 1)) }}
                                        </span>
                                    @else
                                        {{ $row['year'] }} {{ $monthLabels[$row['month']] ?? $row['month'] }}
                                    @endif
                                </td>

                                {{-- Consumption with Dial Chip --}}
                                <td>
                                    <div style="font-weight:900;color:#0f172a;font-size:0.92rem;">
                                        {{ isset($row['actual_kwh']) ? number_format((float) $row['actual_kwh'], 2) : '-' }} <small style="color:#64748b;font-weight:700;">kWh</small>
                                    </div>
                                    @if($hasDials)
                                        <div class="submeter-dial-chip">
                                            <i class="fa-solid fa-gauge-high"></i>
                                            <span>{{ number_format((float) $prev, 0) }}</span>
                                            <span class="dial-arrow">&rarr;</span>
                                            <span>{{ number_format((float) $curr, 0) }}</span>
                                        </div>
                                    @endif
                                </td>

                                <td style="color:#475569;font-weight:700;">
                                    {{ isset($row['baseline_kwh']) ? number_format((float) $row['baseline_kwh'], 2) : '-' }}
                                </td>

                                <td style="font-weight:800;color:{{ isset($row['deviation']) && $row['deviation'] > 0 ? '#b91c1c' : '#15803d' }};">
                                    {{ isset($row['deviation']) ? ($row['deviation'] > 0 ? '+' : '') . number_format((float) $row['deviation'], 2) . '%' : '-' }}
                                </td>

                                <td>
                                    <span style="display:inline-flex;padding:3px 9px;border-radius:999px;font-size:0.72rem;font-weight:900;background:{{ $row['alert_bg'] }};color:{{ $row['alert_color'] }};">
                                        {{ $row['alert_label'] }}
                                    </span>
                                </td>

                                <td style="color:#15803d;font-weight:900;font-size:0.88rem;">
                                    PHP {{ number_format((float) $row['cost'], 2) }}
                                </td>

                                <td style="text-align:center;">
                                    <a href="{{ route('facilities.monthly-records.submeters', ['facility' => $row['facility_id'], 'year' => $row['year'], 'month' => $row['month']]) }}"
                                       style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:8px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;text-decoration:none;font-size:0.75rem;font-weight:800;"
                                       title="View in Facility Records">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Records
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="padding:32px 16px;text-align:center;color:#64748b;">
                                    <div style="font-weight:800;color:#334155;margin-bottom:4px;">No submeter records found</div>
                                    <div>Select another facility or period from the filter above.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($timeframe === 'weekly')
        // Weekly Trend Bar Chart
        const barCanvas = document.getElementById('submeterWeeklyBarChart');
        if (barCanvas) {
            const labels = @json($weeklyChartLabels);
            const datasets = @json($weeklyChartDatasets);

            new Chart(barCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: 'bold', size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function (val) {
                                    return Number(val).toLocaleString() + ' kWh';
                                },
                                font: { size: 10 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                font: { weight: 'bold', size: 11 },
                                padding: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ` ${context.dataset.label}: ${Number(context.raw).toLocaleString()} kWh`;
                                }
                            }
                        }
                    }
                }
            });
        }
    @else
        // Monthly Donut Chart
        const canvas = document.getElementById('submeterDistributionChart');
        if (canvas) {
            const sharesData = @json($submeterLoadShares);
            if (sharesData && sharesData.length > 0) {
                const labels = sharesData.map(s => s.meter_name);
                const dataValues = sharesData.map(s => s.total_kwh);
                const palette = ['#2563eb', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#14b8a6'];

                new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: dataValues,
                            backgroundColor: palette.slice(0, dataValues.length),
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const val = context.raw || 0;
                                        const pct = sharesData[context.dataIndex]?.share_percent || 0;
                                        return ` ${context.label}: ${Number(val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} kWh (${pct}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        }
    @endif
});
</script>
@endsection
