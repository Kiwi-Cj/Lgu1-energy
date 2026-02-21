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
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 24px;
    }

    .insight-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
    }

    .insight-card-header {
        padding: 22px 24px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .insight-card-title {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.2px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .insight-card-title.consumption {
        color: #0f172a;
    }

    .insight-card-title.critical {
        color: #e11d48;
    }

    .insight-card-title i {
        font-size: 0.95rem;
    }

    .consumption-table th {
        font-size: 1rem;
        font-weight: 800;
        color: #355dc2;
        background: #f2f5fb;
    }

    .consumption-table td {
        font-size: 0.92rem;
    }

    .consumption-table td.facility-name {
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1.3;
        color: #1e293b;
    }

    .consumption-table .value-kwh {
        color: #0f172a;
        font-weight: 800;
    }

    .consumption-table .value-baseline {
        color: #64748b;
        font-weight: 800;
    }

    .consumption-table .value-deviation {
        font-weight: 800;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .notifications-body {
        padding: 18px 20px 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .alert-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #f8fafc;
        border-left: 4px solid #cbd5e1;
        border-radius: 12px;
        padding: 16px 16px 16px 14px;
        color: #334155;
        font-size: 0.95rem;
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
        font-size: 0.95rem;
        line-height: 1.5;
        margin-top: 1px;
    }

    .alert-level {
        display: block;
        font-size: 0.72rem;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        opacity: 0.9;
        margin-bottom: 2px;
    }

    /* Dashboard Dark Mode */
    body.dark-mode .dashboard-page .report-card-container {
        background: #0f172a !important;
        border: 1px solid #1f2937;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.42);
        color: #e5e7eb;
    }

    body.dark-mode .dashboard-page .stat-card,
    body.dark-mode .dashboard-page .summary-card,
    body.dark-mode .dashboard-page .chart-container,
    body.dark-mode .dashboard-page .insight-card,
    body.dark-mode .dashboard-page .insight-card-header,
    body.dark-mode .dashboard-page .alert-item {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .dashboard-page .custom-table thead th {
        background: #111827 !important;
        color: #93c5fd !important;
        border-bottom-color: #334155 !important;
    }

    body.dark-mode .dashboard-page .custom-table tbody tr td {
        border-bottom-color: #1f2937 !important;
        color: #cbd5e1 !important;
    }

    body.dark-mode .dashboard-page .custom-table tbody tr:hover {
        background: #1f2937 !important;
    }

    body.dark-mode .dashboard-page .quick-action-btn {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .dashboard-page .quick-action-btn:hover {
        box-shadow: 0 8px 18px rgba(2, 6, 23, 0.5) !important;
    }

    body.dark-mode .dashboard-page .consumption-table th {
        background: #0f172a !important;
        color: #93c5fd !important;
    }

    body.dark-mode .dashboard-page .consumption-table td.facility-name,
    body.dark-mode .dashboard-page .consumption-table .value-kwh {
        color: #f8fafc !important;
    }

    body.dark-mode .dashboard-page .consumption-table .value-baseline {
        color: #94a3b8 !important;
    }

    body.dark-mode .dashboard-page .insight-card-title.consumption,
    body.dark-mode .dashboard-page .insight-card-title.critical,
    body.dark-mode .dashboard-page h1,
    body.dark-mode .dashboard-page h2,
    body.dark-mode .dashboard-page h3 {
        color: #f8fafc !important;
    }

    body.dark-mode .dashboard-page [style*="background:#f0f7ff"],
    body.dark-mode .dashboard-page [style*="background:#f0fdf4"],
    body.dark-mode .dashboard-page [style*="background:#fffbeb"],
    body.dark-mode .dashboard-page [style*="background:#fef2f2"],
    body.dark-mode .dashboard-page [style*="background:#fff7ed"],
    body.dark-mode .dashboard-page [style*="background:#f5f3ff"],
    body.dark-mode .dashboard-page [style*="background:#eef2ff"],
    body.dark-mode .dashboard-page [style*="background:#ecfdf5"],
    body.dark-mode .dashboard-page [style*="background:#f8fafc"] {
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
    }
</style>

<div class="dashboard-page" style="width:100%; margin:0 auto;">
    <div class="report-card-container">
        
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2.5rem;">
            <div>
                <h1 style="font-size:1.8rem; font-weight:800; color:#1e293b; margin:0; letter-spacing:-0.5px;">⚡ Energy Efficiency Overview</h1>
                <p style="font-size:1rem; color:#64748b; margin-top:4px;">Real-time monitoring and analytics for LGU facilities.</p>
                <div style="font-size:0.85rem; color:#94a3b8; margin-top:8px; display:flex; align-items:center; gap:10px;">
                    <i class="fa fa-calendar"></i>
                    <span>Period: <strong>{{ now()->subMonths(5)->format('F') }}</strong> – <strong>{{ now()->format('F Y') }}</strong></span>
                </div>
            </div>
            <div style="text-align:right;">
                <span style="background:#eef2ff; color:#4f46e5; padding:10px 18px; border-radius:12px; font-weight:800; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; border: 1px solid #e0e7ff;">
                    <i class="fa fa-shield"></i> {{ Auth::user()->role ?? 'Administrator' }}
                </span>
            </div>
        </div>

        <div class="stats-grid" style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:2.5rem;">
            <div class="stat-card" style="background:#f0f7ff;">
                <div style="color:#3762c8; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Total Facilities</div>
                <div style="font-size:2rem; font-weight:800; color:#1e3a8a;">{{ $totalFacilities ?? 0 }}</div>
            </div>

            <div class="stat-card" style="background:#f0fdf4;">
                <div style="color:#16a34a; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Net Consumption</div>
                <div style="font-size:2rem; font-weight:800; color:#14532d;">{{ number_format($totalKwh ?? 0) }} <small style="font-size:0.9rem;">kWh</small></div>
                <div style="font-size:0.8rem; font-weight:700; color:#166534; margin-top:5px;">
                    <i class="fa fa-caret-up"></i> {{ $kwhTrend ?? '0%' }} <span style="font-weight:500; opacity:0.8;">vs last period</span>
                </div>
            </div>

            <div class="stat-card" style="background:#fffbeb;">
                <div style="color:#d97706; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Total Expenditure</div>
                <div style="font-size:2rem; font-weight:800; color:#78350f;">₱{{ number_format($totalCost ?? 0, 0) }}</div>
            </div>

            <div class="stat-card" style="background:#fef2f2;">
                <div style="color:#dc2626; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">System Alerts</div>
                <div style="font-size:2rem; font-weight:800; color:#7f1d1d;">{{ $activeAlerts ?? 0 }}</div>
            </div>

            <div class="stat-card" style="background:#fff7ed;">
                <div style="color:#c2410c; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Unresolved Incidents</div>
                <div style="font-size:2rem; font-weight:800; color:#9a3412;">{{ $unresolvedIncidentCount ?? 0 }}</div>
            </div>

            <div class="stat-card" style="background:#f5f3ff;">
                <div style="color:#6d28d9; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Ongoing Maintenance</div>
                <div style="font-size:2rem; font-weight:800; color:#4c1d95;">{{ $ongoingMaintenance ?? 0 }}</div>
            </div>
        </div>

        <div class="summary-grid" style="display:grid; grid-template-columns: 1.5fr 1fr; gap:18px; margin-bottom:2rem;">
            <div class="summary-card" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px;">
                <div style="font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; margin-bottom:12px;">
                    Facility Operational Snapshot
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <span style="background:#ecfdf5; color:#166534; border:1px solid #bbf7d0; padding:8px 12px; border-radius:999px; font-weight:700; font-size:0.82rem;">
                        Active: {{ optional($facilityStatusCounts)->active_count ?? 0 }}
                    </span>
                    <span style="background:#fffbeb; color:#92400e; border:1px solid #fde68a; padding:8px 12px; border-radius:999px; font-weight:700; font-size:0.82rem;">
                        Maintenance: {{ optional($facilityStatusCounts)->maintenance_count ?? 0 }}
                    </span>
                    <span style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:8px 12px; border-radius:999px; font-weight:700; font-size:0.82rem;">
                        Inactive: {{ optional($facilityStatusCounts)->inactive_count ?? 0 }}
                    </span>
                </div>
            </div>

            <div class="summary-card" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px;">
                <div style="font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; margin-bottom:12px;">
                    Quick Actions
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <a href="{{ route('modules.facilities.index') }}" class="quick-action-btn"><i class="fa-solid fa-building"></i> Facilities</a>
                    <a href="{{ route('energy.dashboard') }}" class="quick-action-btn"><i class="fa-solid fa-bolt"></i> Energy Monitoring</a>
                    <a href="{{ route('modules.energy.annual') }}" class="quick-action-btn"><i class="fa-solid fa-calendar-days"></i> Annual Monitoring</a>
                    <a href="{{ route('energy-incidents.index') }}" class="quick-action-btn"><i class="fa-solid fa-triangle-exclamation"></i> Incidents</a>
                </div>
            </div>
        </div>

        <div class="chart-grid" style="display:flex; gap:24px; margin-bottom:2.5rem;">
            <div class="chart-item" style="flex:1;">
                <div class="chart-container">
                    <h3 style="font-size:1rem; font-weight:800; color:#334155; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span style="width:8px; height:8px; background:#3762c8; border-radius:50%;"></span>
                        Actual vs Baseline Consumption
                    </h3>
                    <div class="chart-canvas-wrap" style="height:320px;"><canvas id="energyChart"></canvas></div>
                </div>
            </div>

            <div class="chart-item" style="flex:1;">
                <div class="chart-container">
                    <h3 style="font-size:1rem; font-weight:800; color:#334155; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span style="width:8px; height:8px; background:#e11d48; border-radius:50%;"></span>
                        Monthly Cost Trend
                    </h3>
                    <div class="chart-canvas-wrap" style="height:320px;"><canvas id="costChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="insights-grid">
            
            <div class="insight-card">
                <div class="insight-card-header">
                    <h3 class="insight-card-title consumption"><i class="fa-solid fa-fire-flame-curved"></i> High Consumption Hubs</h3>
                </div>
                <div class="table-scroll" style="overflow-x:auto;">
                    <table class="custom-table consumption-table">
                        <thead>
                            <tr>
                                <th>Facility Name</th>
                                <th style="text-align:center;">Total kWh (6mo)</th>
                                <th style="text-align:center;">Total Baseline (6mo)</th>
                                <th style="text-align:center;">Deviation %</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($topFacilities ?? collect())->take(5) as $facility)
                            @php
                                $deviation = (float) ($facility->deviation ?? 0);
                                $status = (string) ($facility->status ?? 'Normal');
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
                            <tr>
                                <td class="facility-name">{{ $facility->name }}</td>
                                <td class="value-kwh" style="text-align:center;">{{ number_format($facility->total_kwh, 2) }}</td>
                                <td class="value-baseline" style="text-align:center;">{{ number_format($facility->baseline_kwh, 2) }}</td>
                                <td class="value-deviation" style="text-align:center; color:{{ $deviationColor }};">{{ number_format($deviation, 2) }}%</td>
                                <td style="text-align:center;">
                                    <span class="status-pill" style="background:{{ $theme['bg'] }}; color:{{ $theme['text'] }}; border-color:{{ $theme['border'] }};">
                                        {{ $status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="text-align:center; padding:30px; color:#94a3b8;">No records found for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="insight-card">
                <div class="insight-card-header">
                    <h3 class="insight-card-title critical">
                        <i class="fa fa-bell"></i> Critical Notifications
                    </h3>
                </div>

                <div class="notifications-body">
                    @forelse(collect($criticalAlerts ?? [])->take(3) as $alert)
                        @php
                            $level = (string) ($alert['level'] ?? 'High');
                            $levelClass = strtolower(str_replace(' ', '-', $level));
                            $message = (string) ($alert['message'] ?? $alert);
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
                                <span>{{ $message }}</span>
                            </span>
                        </div>
                    @empty
                        <div style="padding:20px; text-align:center; background:#f8fafc; border-radius:12px; color:#94a3b8; font-size:0.9rem;">
                            <i class="fa fa-check-circle" style="color:#22c55e;"></i> No critical alerts at the moment.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const numberFmt = new Intl.NumberFormat('en-US');
    let energyChartInstance = null;
    let costChartInstance = null;

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
                            data: {!! json_encode($energyChartData ?? [1200,1500,1100,1700,1600,1400]) !!},
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
                        data: {!! json_encode($costChartData ?? [5000,6200,4800,7100,6600,5800]) !!},
                        borderColor: theme.costLine,
                        backgroundColor: theme.costFill,
                        fill: true,
                        tension: 0.4,
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


