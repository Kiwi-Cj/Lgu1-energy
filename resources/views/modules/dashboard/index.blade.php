@extends('layouts.qc-admin')

@section('content')

<div style="max-width:1200px;margin:0 auto;">

    <!-- 🔹 PAGE HEADER -->
    <div style="margin-bottom:24px;">
        <h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">
            LGU Energy Efficiency & Conservation Management System
        </h1>

        <div style="font-size:1.2rem;color:#555;">Dashboard Overview</div>

        <div style="margin-top:6px;font-size:0.95rem;color:#777;">
            Reporting Period:
            <strong>{{ $reportStart ?? 'January 2025' }}</strong> –
            <strong>{{ $reportEnd ?? 'March 2025' }}</strong>
        </div>

        <div style="margin-top:4px;font-size:0.9rem;color:#888;">
            {{ date('F j, Y') }} | Role: {{ Auth::user()->role ?? 'User' }}
        </div>
    </div>

    <!-- 🔹 SUMMARY KPI CARDS -->
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:2rem;">

        <div class="card" style="flex:1;min-width:220px;background:#f5f8ff;padding:20px;border-radius:14px;">
            <div style="color:#3762c8;font-weight:600;">🏢 Total Facilities</div>
            <div style="font-size:2rem;font-weight:700;">
                {{ $totalFacilities ?? 0 }}
            </div>
        </div>

        <div class="card" style="flex:1;min-width:220px;background:#f0fdf4;padding:20px;border-radius:14px;">
            <div style="color:#22c55e;font-weight:600;">⚡ Total Energy Consumption</div>
            <div style="font-size:2rem;font-weight:700;">
                {{ number_format($totalKwh ?? 0) }} kWh
            </div>
            <div style="font-size:0.9rem;color:#16a34a;">
                {{ $kwhTrend ?? '+5.2%' }} vs last period
            </div>
        </div>

        <div class="card" style="flex:1;min-width:220px;background:#fff7ed;padding:20px;border-radius:14px;">
            <div style="color:#f59e0b;font-weight:600;">💰 Total Energy Cost</div>
            <div style="font-size:2rem;font-weight:700;">
                ₱{{ number_format($totalCost ?? 0, 2) }}
            </div>
        </div>

        <div class="card" style="flex:1;min-width:220px;background:#fff0f3;padding:20px;border-radius:14px;">
            <div style="color:#e11d48;font-weight:600;">🚨 Active Alerts</div>
            <div style="font-size:2rem;font-weight:700;">
                {{ $activeAlerts ?? 0 }}
            </div>
        </div>
    </div>

    <!-- 🔹 ENERGY CHARTS -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">
            Energy Performance Overview
        </h3>

        <div style="display:flex;gap:28px;flex-wrap:wrap;">
            <div style="flex:1;min-width:420px;background:#fff;padding:18px;border-radius:12px;">
                <strong>Monthly Energy Consumption (Actual vs Baseline)</strong>
                <canvas id="energyChart" height="180"></canvas>
            </div>

            <div style="flex:1;min-width:420px;background:#fff;padding:18px;border-radius:12px;">
                <strong>Energy Cost Trend</strong>
                <canvas id="costChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- 🔹 TOP ENERGY CONSUMERS -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">
            🔥 Top Energy-Consuming Facilities
        </h3>

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f7fb;">
                    <th style="padding:10px;text-align:left;">Facility</th>
                    <th style="padding:10px;">Monthly kWh</th>
                    <th style="padding:10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topFacilities ?? [] as $facility)
                    <tr>
                        <td style="padding:10px;">{{ $facility->name }}</td>
                        <td style="padding:10px;text-align:center;">
                            {{ number_format($facility->monthly_kwh) }}
                        </td>
                        <td style="padding:10px;text-align:center;">
                            @if($facility->status === 'High')
                                <span style="color:#e11d48;font-weight:700;">High</span>
                            @elseif($facility->status === 'Medium')
                                <span style="color:#f59e0b;font-weight:700;">Medium</span>
                            @elseif($facility->status === 'Normal')
                                <span style="color:#22c55e;font-weight:700;">Normal</span>
                            @else
                                <span style="color:#888;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding:12px;color:#888;">
                            No data available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 🔹 RECENT SYSTEM ACTIVITY -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;">Recent System Activity</h3>
        <ul style="padding-left:18px;color:#444;">
            @forelse($recentLogs ?? [] as $log)
                <li>{{ $log }}</li>
            @empty
                <li style="color:#888;">No recent activity recorded.</li>
            @endforelse
        </ul>
    </div>

    <!-- 🔹 ALERTS -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#e11d48;">
            Alerts & Notifications
        </h3>

        <ul style="padding-left:18px;">
            @forelse($alerts ?? [] as $alert)
                <li style="color:#e11d48;">{{ $alert }}</li>
            @empty
                <li style="color:#888;">No alerts generated.</li>
            @endforelse
        </ul>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const energyChart = new Chart(document.getElementById('energyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($energyChartLabels ?? []) !!},
        datasets: [
            {
                label: 'Actual kWh',
                data: {!! json_encode($energyChartData ?? []) !!},
                backgroundColor: '#3762c8'
            },
            {
                label: 'Baseline kWh',
                data: {!! json_encode($baselineChartData ?? []) !!},
                type: 'line',
                borderColor: '#22c55e',
                fill: false
            }
        ]
    }
});

const costChart = new Chart(document.getElementById('costChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($costChartLabels ?? []) !!},
        datasets: [{
            label: 'Energy Cost (₱)',
            data: {!! json_encode($costChartData ?? []) !!},
            borderColor: '#e11d48',
            fill: true
        }]
    }
});
</script>
@endpush

@endsection
