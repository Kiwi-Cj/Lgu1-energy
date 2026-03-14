@extends('layouts.qc-admin')
@section('title', 'Main Meter Monitoring')

@section('content')
@php
    $widgets = $widgets ?? [];
    $top5 = $widgets['top5HighestIncrease'] ?? collect();
    $criticalCount = $widgets['criticalAlertsThisMonth'] ?? 0;
    $dashboard = $dashboard ?? [];
    $trend = $trend ?? ['labels' => [], 'kwh' => [], 'baseline' => []];
    $badge = strtolower((string) ($dashboard['alert_badge'] ?? 'none'));
    $badgeColor = $badge === 'critical' ? '#b91c1c' : ($badge === 'warning' ? '#b45309' : '#15803d');
    $badgeBg = $badge === 'critical' ? '#fee2e2' : ($badge === 'warning' ? '#fef3c7' : '#dcfce7');
    $selectedOverloadOnly = (bool) ($selectedOverloadOnly ?? false);
@endphp

<style>
    .main-meter-filter-bar {
        padding: 14px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .main-meter-filter-form {
        display: grid;
        grid-template-columns: 210px minmax(280px, 1fr) auto auto;
        gap: 10px;
        align-items: end;
    }

    .main-meter-filter-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .main-meter-filter-field label {
        font-size: .8rem;
        font-weight: 800;
        color: #475569;
        letter-spacing: .01em;
    }

    .main-meter-filter-field input,
    .main-meter-filter-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
        font-weight: 600;
    }

    .main-meter-filter-check {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 44px;
        padding: 0 8px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
    }

    .main-meter-filter-check input {
        width: 17px;
        height: 17px;
    }

    .main-meter-filter-check label {
        font-size: .88rem;
        font-weight: 800;
        color: #334155;
        cursor: pointer;
    }

    .main-meter-filter-actions {
        display: inline-flex;
        gap: 8px;
    }

    .main-meter-filter-btn {
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
    }

    .main-meter-filter-btn.primary {
        background: #1d4ed8;
        color: #fff;
    }

    .main-meter-filter-btn.ghost {
        background: #eef2f7;
        color: #334155;
    }

    .main-meter-top5 {
        padding: 16px 14px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .main-meter-top5__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .main-meter-top5__title {
        margin: 0;
        font-size: 1.05rem;
        color: #1e293b;
        font-weight: 800;
    }

    .main-meter-top5__subtitle {
        font-size: .76rem;
        color: #1d4ed8;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        border-radius: 999px;
        padding: 4px 10px;
    }

    .main-meter-top5__list {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 10px;
    }

    .main-meter-top5__chip {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #dbeafe;
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(30, 64, 175, .06);
    }

    .main-meter-top5__rank {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 800;
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    .main-meter-top5__facility {
        flex: 1;
        min-width: 0;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.28;
        font-size: 1.02rem;
    }

    .main-meter-top5__percent {
        flex-shrink: 0;
        font-weight: 800;
        font-size: .86rem;
        color: #be123c;
        background: #fff1f2;
        border: 1px solid #fecdd3;
        border-radius: 999px;
        padding: 5px 10px;
    }

    .main-meter-top5__empty {
        margin-top: 12px;
        color: #64748b;
        font-size: .9rem;
        padding: 12px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #fff;
    }

    @media (max-width: 960px) {
        .main-meter-filter-form {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 700px) {
        .main-meter-filter-form {
            grid-template-columns: 1fr;
        }

        .main-meter-filter-actions {
            width: 100%;
        }

        .main-meter-filter-btn {
            flex: 1;
        }

        .main-meter-top5__chip {
            align-items: flex-start;
        }

        .main-meter-top5__percent {
            margin-left: auto;
        }
    }

    body.dark-mode .main-meter-filter-bar {
        background: #0f172a;
        border-bottom-color: #334155;
    }

    body.dark-mode .main-meter-filter-field label,
    body.dark-mode .main-meter-filter-check label {
        color: #cbd5e1;
    }

    body.dark-mode .main-meter-filter-field input,
    body.dark-mode .main-meter-filter-field select,
    body.dark-mode .main-meter-filter-check {
        background: #111827;
        color: #e2e8f0;
        border-color: #334155;
    }

    body.dark-mode .main-meter-filter-btn.ghost {
        background: #1e293b;
        color: #e2e8f0;
    }

    body.dark-mode .main-meter-top5 {
        background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
        border-bottom-color: #334155;
    }

    body.dark-mode .main-meter-top5__title,
    body.dark-mode .main-meter-top5__facility {
        color: #e2e8f0;
    }

    body.dark-mode .main-meter-top5__subtitle {
        background: #172554;
        border-color: #1e3a8a;
        color: #bfdbfe;
    }

    body.dark-mode .main-meter-top5__chip {
        background: #111827;
        border-color: #334155;
        box-shadow: none;
    }

    body.dark-mode .main-meter-top5__rank {
        background: #1e293b;
        border-color: #334155;
        color: #93c5fd;
    }

    body.dark-mode .main-meter-top5__percent {
        background: #3f1d2e;
        border-color: #6b213f;
        color: #fda4af;
    }

    body.dark-mode .main-meter-top5__empty {
        background: #111827;
        border-color: #334155;
        color: #cbd5e1;
    }

    .main-meter-data-wrap {
        overflow-x: auto;
        background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
    }

    .main-meter-data-table {
        width: 100%;
        min-width: 1080px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .main-meter-data-table thead th {
        padding: 10px 10px !important;
        text-align: center !important;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-size: .75rem;
        color: #475569;
        font-weight: 800;
        background: #f8fafc !important;
        border-bottom: 1px solid #dbe4f2 !important;
    }

    .main-meter-data-table td {
        padding: 10px 10px !important;
        text-align: center !important;
        border-bottom: 1px solid #edf2f7 !important;
        font-size: .95rem;
    }

    .main-meter-data-table td span[style*="border-radius:999px"] {
        padding: 4px 10px !important;
        font-size: .74rem !important;
    }

    body.dark-mode .main-meter-data-wrap {
        background: linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
    }

    body.dark-mode .main-meter-data-table thead th {
        background: #0f172a !important;
        color: #cbd5e1;
        border-bottom-color: #334155 !important;
    }

    body.dark-mode .main-meter-data-table td {
        border-bottom-color: #334155 !important;
        color: #e2e8f0;
    }
</style>

<div class="em-page">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 16px;border-radius:12px;font-weight:700;">
            Please check the form fields.
        </div>
    @endif

    @php
        $energyTab = 'main';
    @endphp
    @include('layouts.partials.energy_monitoring_switcher')

    <div class="em-header">
        <div>
            <h2>Main Meter Baseline and Alert Detection</h2>
            <div class="em-header-subtitle">City Hall facility-level electrical consumption and demand monitoring.</div>
        </div>
        <div class="em-header-actions">
            <a href="{{ route('modules.main-meter.reports.monthly', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}"
                class="em-action-btn soft">
                Monthly Report
            </a>
            <a href="{{ route('modules.main-meter.alerts', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}"
                class="em-action-btn soft">
                View Alerts
            </a>
        </div>
    </div>

    <div style="margin-top:14px;display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;">
        <div style="background:linear-gradient(135deg,#eff6ff,#ffffff);border:1px solid #dbeafe;border-radius:14px;padding:14px;">
            <div style="font-size:.78rem;color:#1e40af;font-weight:800;letter-spacing:.05em;">CURRENT MONTH KWH</div>
            <div style="font-size:1.55rem;font-weight:900;color:#0f172a;">{{ number_format((float) ($dashboard['current_kwh'] ?? 0), 2) }}</div>
        </div>
        <div style="background:linear-gradient(135deg,#ecfeff,#ffffff);border:1px solid #bae6fd;border-radius:14px;padding:14px;">
            <div style="font-size:.78rem;color:#0f766e;font-weight:800;letter-spacing:.05em;">BASELINE KWH</div>
            <div style="font-size:1.55rem;font-weight:900;color:#0f172a;">{{ number_format((float) ($dashboard['baseline_kwh'] ?? 0), 2) }}</div>
        </div>
        <div style="background:linear-gradient(135deg,#fffbeb,#ffffff);border:1px solid #fde68a;border-radius:14px;padding:14px;">
            <div style="font-size:.78rem;color:#a16207;font-weight:800;letter-spacing:.05em;">INCREASE %</div>
            <div style="font-size:1.55rem;font-weight:900;color:#0f172a;">
                {{ $dashboard['increase_percent'] !== null ? number_format((float) $dashboard['increase_percent'], 2) . '%' : '-' }}
            </div>
        </div>
        <div style="background:linear-gradient(135deg,#f8fafc,#ffffff);border:1px solid #e2e8f0;border-radius:14px;padding:14px;">
            <div style="font-size:.78rem;color:#334155;font-weight:800;letter-spacing:.05em;">PEAK DEMAND (KW)</div>
            <div style="font-size:1.55rem;font-weight:900;color:#0f172a;">{{ number_format((float) ($dashboard['peak_demand_kw'] ?? 0), 2) }}</div>
        </div>
        <div style="background:linear-gradient(135deg,#f8fafc,#ffffff);border:1px solid #e2e8f0;border-radius:14px;padding:14px;">
            <div style="font-size:.78rem;color:#334155;font-weight:800;letter-spacing:.05em;">ALERT BADGE</div>
            <div style="margin-top:8px;display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:{{ $badgeBg }};color:{{ $badgeColor }};font-weight:800;">
                {{ strtoupper($badge) }}
            </div>
        </div>
        <div style="background:linear-gradient(135deg,#fef2f2,#ffffff);border:1px solid #fecaca;border-radius:14px;padding:14px;">
            <div style="font-size:.78rem;color:#b91c1c;font-weight:800;letter-spacing:.05em;">CRITICAL ALERTS THIS MONTH</div>
            <div style="font-size:1.55rem;font-weight:900;color:#991b1b;">{{ $criticalCount }}</div>
        </div>
    </div>

    <div style="margin-top:14px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <div class="main-meter-filter-bar">
        <form method="GET" action="{{ route('modules.main-meter.monitoring') }}" class="main-meter-filter-form">
            <div class="main-meter-filter-field">
                <label>Month</label>
                <input type="month" name="month" value="{{ $selectedMonth }}">
            </div>
            <div class="main-meter-filter-field">
                <label>Facility</label>
                <select name="facility_id">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="main-meter-filter-check">
                <input type="checkbox" id="overload_only" name="overload_only" value="1" @checked($selectedOverloadOnly)>
                <label for="overload_only">Overload only</label>
            </div>
            <div class="main-meter-filter-actions">
                <button type="submit" class="main-meter-filter-btn primary">Filter</button>
                <a href="{{ route('modules.main-meter.monitoring') }}" class="main-meter-filter-btn ghost">Reset</a>
            </div>
        </form>
        </div>

        <div class="main-meter-top5">
            <div class="main-meter-top5__header">
                <h3 class="main-meter-top5__title">Top 5 Facilities with Highest % Increase</h3>
                <span class="main-meter-top5__subtitle">Current month vs baseline</span>
            </div>
            @forelse($top5 as $item)
                @if($loop->first)
                    <div class="main-meter-top5__list">
                @endif
                        <div class="main-meter-top5__chip">
                            <span class="main-meter-top5__rank">#{{ $loop->iteration }}</span>
                            <span class="main-meter-top5__facility">{{ $item->facility?->name ?? 'Facility' }}</span>
                            <span class="main-meter-top5__percent">{{ number_format((float) $item->increase_percent, 2) }}%</span>
                        </div>
                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="main-meter-top5__empty">No high increase alerts for this period.</div>
            @endforelse
        </div>

        <div style="padding:12px;border-bottom:1px solid #e2e8f0;background:#ffffff;">
            <h3 style="margin:0 0 10px;color:#1e293b;font-weight:800;">12-Month Trend (kWh vs Baseline)</h3>
            <div style="position:relative;height:320px;max-height:320px;width:100%;">
                <canvas id="mainMeterTrendChart" style="display:block;width:100%;height:100%;"></canvas>
            </div>
        </div>

        <div class="main-meter-data-wrap">
            <table class="main-meter-data-table">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Facility</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Current kWh</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Baseline kWh</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">% Increase</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Peak Demand (kW)</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Overload</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Power Factor</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Alert Level</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Status</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $level = strtolower((string) ($row->monitor_alert_level ?? 'none'));
                            $increase = $row->monitor_increase_percent;
                            $isOverload = (bool) ($row->monitor_is_overload ?? false);
                            $overloadPercent = $row->monitor_overload_percent;
                            $rowBg = '#ffffff';
                            if ($level === 'critical') {
                                $rowBg = '#fef2f2';
                            } elseif ($level === 'warning') {
                                $rowBg = '#fffbeb';
                            } elseif ($isOverload) {
                                $rowBg = '#fff7ed';
                            }
                        @endphp
                        <tr style="background:{{ $rowBg }};">
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;">
                                <div style="font-weight:800;color:#1e293b;">{{ $row->facility?->name }}</div>
                                <div style="font-size:.82rem;color:#64748b;">{{ $row->periodLabel() }}</div>
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:700;color:#0f172a;">
                                {{ number_format((float) $row->kwh_used, 2) }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;color:#1d4ed8;font-weight:700;">
                                {{ $row->monitor_baseline_kwh !== null ? number_format((float) $row->monitor_baseline_kwh, 2) : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:800;color:{{ ($increase ?? 0) > 0 ? '#be123c' : '#166534' }};">
                                {{ $increase !== null ? number_format((float) $increase, 2) . '%' : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:700;color:#334155;">
                                {{ $row->peak_demand_kw !== null ? number_format((float) $row->peak_demand_kw, 2) : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                @if($isOverload)
                                    <span style="background:#fee2e2;color:#991b1b;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">
                                        OVERLOAD{{ $overloadPercent !== null ? ' (' . number_format((float) $overloadPercent, 2) . '%)' : '' }}
                                    </span>
                                @else
                                    <span style="background:#e2e8f0;color:#334155;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">NO</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:700;color:#334155;">
                                {{ $row->power_factor !== null ? number_format((float) $row->power_factor, 3) : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                @if($level === 'critical')
                                    <span style="background:#fee2e2;color:#991b1b;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">CRITICAL</span>
                                @elseif($level === 'warning')
                                    <span style="background:#fef3c7;color:#92400e;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">WARNING</span>
                                @else
                                    <span style="background:#e2e8f0;color:#334155;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">NONE</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                <span style="background:#dcfce7;color:#166534;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">RECORDED</span>
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                <span style="color:#94a3b8;font-size:.82rem;">-</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:22px 14px;text-align:center;color:#64748b;">No main meter readings found for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('mainMeterTrendChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const labels = @json($trend['labels'] ?? []);
    const kwhData = @json($trend['kwh'] ?? []);
    const baselineData = @json($trend['baseline'] ?? []);

    if (window.mainMeterTrendChartInstance) {
        window.mainMeterTrendChartInstance.destroy();
    }

    window.mainMeterTrendChartInstance = new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Actual kWh',
                    data: kwhData,
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29, 78, 216, 0.12)',
                    tension: 0.32,
                    fill: true
                },
                {
                    label: 'Baseline kWh',
                    data: baselineData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.08)',
                    borderDash: [6, 4],
                    tension: 0.32,
                    fill: false
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
</script>
@endsection
