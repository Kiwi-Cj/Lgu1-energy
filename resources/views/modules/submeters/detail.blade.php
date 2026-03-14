@extends('layouts.qc-admin')
@section('title', 'Submeter Detail')

@section('content')
<div style="padding:14px;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('error') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;color:#1e3a8a;font-size:1.45rem;font-weight:800;">{{ $submeter->submeter_name }}</h2>
            <div style="margin-top:4px;color:#64748b;">Facility: <strong style="color:#1e293b;">{{ $submeter->facility?->name }}</strong> | Type: {{ strtoupper($periodType) }}</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.load-tracking.index', ['month' => ($loadTrackingMonth ?? now()->format('Y-m')), 'facility_id' => $submeter->facility_id, 'submeter_id' => $submeter->id]) }}"
               style="text-decoration:none;background:#eff6ff;color:#1d4ed8;padding:10px 14px;border-radius:10px;border:1px solid #bfdbfe;font-weight:700;">
                View Equipment
            </a>
            <a href="{{ route('modules.submeters.monitoring') }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">
                Back to Monitoring
            </a>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <h3 style="margin:0;color:#1e293b;font-size:1.05rem;font-weight:800;">Trend Chart (Last 12 Periods)</h3>
            <form method="GET" action="{{ route('modules.submeters.show', $submeter->id) }}" style="display:flex;gap:8px;align-items:center;">
                <label style="font-size:.8rem;color:#475569;font-weight:700;">Period Type</label>
                <select name="period_type" onchange="this.form.submit()" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="daily" @selected($periodType === 'daily')>Daily</option>
                    <option value="weekly" @selected($periodType === 'weekly')>Weekly</option>
                    <option value="monthly" @selected($periodType === 'monthly')>Monthly</option>
                </select>
            </form>
        </div>
        <div style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:10px;">
            <canvas id="trendChart" height="120"></canvas>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <div style="padding:12px 14px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
            <h3 style="margin:0;color:#1e293b;font-size:1rem;font-weight:800;">Readings Snapshot</h3>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;min-width:940px;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Period</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Start</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">End</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">kWh Used</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Alert</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readings as $reading)
                        @php $level = strtolower((string) ($reading->alert?->alert_level ?? 'none')); @endphp
                        <tr>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;font-weight:700;color:#1e293b;">{{ $reading->periodLabel() }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#334155;">{{ number_format((float) $reading->reading_start_kwh, 2) }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#334155;">{{ number_format((float) $reading->reading_end_kwh, 2) }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:800;color:#0f172a;">{{ number_format((float) $reading->kwh_used, 2) }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:20px;text-align:center;color:#64748b;">No readings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <div style="padding:12px 14px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
            <h3 style="margin:0;color:#1e293b;font-size:1rem;font-weight:800;">Alerts Timeline</h3>
        </div>
        @if($alertsTimeline->count() === 0)
            <div style="padding:16px;color:#64748b;">No alert history for this submeter yet.</div>
        @else
            <div style="padding:10px 14px;">
                @foreach($alertsTimeline as $alert)
                    <div style="border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;margin-bottom:8px;background:#fff;">
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                            <div style="font-weight:800;color:#1e293b;">
                                {{ strtoupper($alert->alert_level) }} | {{ number_format((float) $alert->increase_percent, 2) }}%
                            </div>
                            <div style="color:#64748b;font-size:.84rem;">
                                {{ optional($alert->reading?->period_end_date)->format('M d, Y') ?: '-' }}
                            </div>
                        </div>
                        <div style="margin-top:6px;color:#334155;font-size:.9rem;">{{ $alert->reason ?: '-' }}</div>
                    </div>
                @endforeach
                <div style="margin-top:10px;">
                    {{ $alertsTimeline->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var labels = @json($labels);
    var kwhSeries = @json($kwhSeries);
    var baselineSeries = @json($baselineSeries);
    var canvas = document.getElementById('trendChart');
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'kWh Used',
                    data: kwhSeries,
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29,78,216,.08)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3
                },
                {
                    label: 'Baseline (Adaptive Priority)',
                    data: baselineSeries,
                    borderColor: '#f97316',
                    borderDash: [6, 6],
                    backgroundColor: 'transparent',
                    tension: 0.3,
                    fill: false,
                    pointRadius: 2
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) { return value + ' kWh'; }
                    }
                }
            }
        }
    });
})();
</script>
@endsection
