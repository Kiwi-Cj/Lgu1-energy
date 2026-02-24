@extends('layouts.qc-admin')
@section('title', 'Annual Energy Monitoring')

@php
    $roleKey = auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')));
@endphp

@section('content')
<style>
    .annual-energy-actions {
        display: inline-flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .annual-btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        text-decoration: none;
        font-weight: 800;
        font-size: 0.86rem;
        border-radius: 10px;
        padding: 10px 14px;
    }

    .annual-btn-excel {
        background: linear-gradient(90deg, #15803d, #16a34a);
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.25);
    }

    .annual-btn-pdf {
        background: linear-gradient(90deg, #be123c, #e11d48);
        box-shadow: 0 6px 16px rgba(225, 29, 72, 0.25);
    }

    @media (max-width: 640px) {
        .annual-energy-actions {
            width: 100%;
        }

        .annual-btn-action {
            flex: 1;
            justify-content: center;
        }
    }

    .annual-chart-card {
        margin-top: 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
    }

    .annual-chart-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .annual-chart-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #1e293b;
    }

    .annual-chart-sub {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .annual-chart-wrap {
        position: relative;
        height: 280px;
    }

    @media (max-width: 640px) {
        .annual-chart-wrap {
            height: 240px;
        }
    }
</style>
<div class="report card" style="padding:32px 24px; background:#f8fafc; border-radius:18px; box-shadow:0 8px 32px rgba(37,99,235,0.09);">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:18px;">
        <div>
            <h1 style="font-size:1.9rem; font-weight:800; color:#1e293b; margin:0;">Annual Energy Monitoring</h1>
            <p style="margin:6px 0 0; color:#64748b;">Year-level summary of actual vs baseline energy usage.</p>
        </div>
        <div class="annual-energy-actions">
            <a href="{{ route('modules.energy.annual.export-pdf', request()->query()) }}"
               class="annual-btn-action annual-btn-pdf">
                <i class="fa fa-file-pdf-o"></i> Export PDF
            </a>
            @if($roleKey !== 'staff')
            <a href="{{ route('modules.energy.annual.export-excel', request()->query()) }}"
               class="annual-btn-action annual-btn-excel">
                <i class="fa fa-download"></i> Export Excel
            </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('modules.energy.annual') }}"
          style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px; margin-bottom:18px;">
        <div style="min-width:180px;">
            <label for="year" style="display:block; margin-bottom:6px; color:#334155; font-weight:600;">Year</label>
            <select id="year" name="year" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:9px 10px;">
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width:220px;">
            <label for="facility_id" style="display:block; margin-bottom:6px; color:#334155; font-weight:600;">Facility</label>
            <select id="facility_id" name="facility_id" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:9px 10px;">
                <option value="">All Facilities</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ (string)$selectedFacility === (string)$facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit"
                style="background:#2563eb; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-weight:700; cursor:pointer;">
            Apply
        </button>
    </form>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:18px;">
        <div style="background:#fff; border-left:4px solid #2563eb; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Annual Actual kWh</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;">{{ number_format($totalActualKwh ?? 0, 2) }}</div>
        </div>
        <div style="background:#fff; border-left:4px solid #16a34a; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Annual Baseline kWh</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;">{{ number_format($annualBaseline ?? 0, 2) }}</div>
        </div>
        <div style="background:#fff; border-left:4px solid #f59e0b; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Difference</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;">{{ number_format($annualDifference ?? 0, 2) }}</div>
        </div>
        <div style="background:#fff; border-left:4px solid #7c3aed; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Annual Status</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;">{{ $annualStatus ?? '-' }}</div>
        </div>
    </div>

    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:760px;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:12px; text-align:left;">Month</th>
                    <th style="padding:12px; text-align:right;">Actual kWh</th>
                    <th style="padding:12px; text-align:right;">Baseline kWh</th>
                    <th style="padding:12px; text-align:right;">Difference</th>
                    <th style="padding:12px; text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyBreakdown as $row)
                    @php
                        $status = $row['status'] ?? '-';
                        $badgeMap = [
                            'Critical' => ['bg' => '#7f1d1d', 'color' => '#ffffff'],
                            'Very High' => ['bg' => '#e11d48', 'color' => '#ffffff'],
                            'High' => ['bg' => '#f59e42', 'color' => '#ffffff'],
                            'Warning' => ['bg' => '#fde68a', 'color' => '#1f2937'],
                            'Normal' => ['bg' => '#bbf7d0', 'color' => '#14532d'],
                            '-' => ['bg' => '#f8fafc', 'color' => '#64748b'],
                        ];
                        $badgeBg = $badgeMap[$status]['bg'] ?? '#f8fafc';
                        $badgeColor = $badgeMap[$status]['color'] ?? '#64748b';
                    @endphp
                    <tr style="border-top:1px solid #f1f5f9;">
                        <td style="padding:12px; font-weight:700; color:#334155;">{{ $row['label'] ?? '-' }}</td>
                        <td style="padding:12px; text-align:right;">{{ number_format($row['actual'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right;">{{ number_format($row['baseline'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right;">{{ number_format($row['diff'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:center;">
                            <span style="display:inline-block; padding:4px 10px; border-radius:999px; background:{{ $badgeBg }}; color:{{ $badgeColor }}; font-weight:700; font-size:0.82rem;">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:28px; text-align:center; color:#94a3b8;">No annual data available for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php
        $annualChartLabels = collect($monthlyBreakdown ?? [])->map(fn ($row) => $row['label'] ?? '-')->values();
        $annualChartActual = collect($monthlyBreakdown ?? [])->map(fn ($row) => (float) ($row['actual'] ?? 0))->values();
        $annualChartBaseline = collect($monthlyBreakdown ?? [])->map(fn ($row) => (float) ($row['baseline'] ?? 0))->values();
        $annualChartDiff = collect($monthlyBreakdown ?? [])->map(fn ($row) => (float) ($row['diff'] ?? 0))->values();
    @endphp

    <div class="annual-chart-card">
        <div class="annual-chart-head">
            <h3 class="annual-chart-title">Actual vs Baseline kWh (Monthly)</h3>
            <span class="annual-chart-sub">Selected Year: {{ $selectedYear }}</span>
        </div>
        @if($annualChartLabels->isNotEmpty())
            <div class="annual-chart-wrap">
                <canvas id="annualEnergyComparisonChart"></canvas>
            </div>
        @else
            <div style="padding:18px; border-radius:10px; background:#f8fafc; color:#64748b; text-align:center; font-weight:600;">
                No chart data available for selected filters.
            </div>
        @endif
    </div>
</div>

@if($annualChartLabels->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('annualEnergyComparisonChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = @json($annualChartLabels);
    const actualData = @json($annualChartActual);
    const baselineData = @json($annualChartBaseline);
    const diffData = @json($annualChartDiff);

    const isDark = document.body.classList.contains('dark-mode');
    const axisColor = isDark ? '#cbd5e1' : '#475569';
    const gridColor = isDark ? 'rgba(148, 163, 184, 0.18)' : 'rgba(148, 163, 184, 0.20)';
    const actualBarBg = actualData.map((actual, i) => {
        const baseline = Number(baselineData[i] ?? 0);
        return Number(actual) > baseline
            ? (isDark ? 'rgba(244, 63, 94, 0.70)' : 'rgba(239, 68, 68, 0.72)')
            : (isDark ? 'rgba(59, 130, 246, 0.72)' : 'rgba(37, 99, 235, 0.75)');
    });
    const actualBarBorder = actualData.map((actual, i) => {
        const baseline = Number(baselineData[i] ?? 0);
        return Number(actual) > baseline ? '#e11d48' : '#2563eb';
    });

    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Actual kWh',
                    data: actualData,
                    backgroundColor: actualBarBg,
                    borderColor: actualBarBorder,
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 28,
                },
                {
                    type: 'line',
                    label: 'Baseline kWh',
                    data: baselineData,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.18)',
                    tension: 0.28,
                    fill: false,
                    pointRadius: 3,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#16a34a',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Difference (Actual - Baseline)',
                    data: diffData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.18)',
                    tension: 0.22,
                    fill: false,
                    pointRadius: 2.5,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.2,
                    borderDash: [6, 4],
                    yAxisID: 'y',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: axisColor,
                        usePointStyle: true,
                        boxWidth: 10,
                        font: { weight: 700 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const value = Number(ctx.parsed.y ?? 0);
                            return `${ctx.dataset.label}: ${value.toLocaleString(undefined, { maximumFractionDigits: 2, minimumFractionDigits: 2 })} kWh`;
                        },
                        afterBody: function (items) {
                            const i = items?.[0]?.dataIndex;
                            if (typeof i !== 'number') return [];
                            const actual = Number(actualData[i] ?? 0);
                            const baseline = Number(baselineData[i] ?? 0);
                            if (actual > baseline) {
                                return ['Actual is above baseline'];
                            }
                            if (actual < baseline) {
                                return ['Actual is below baseline'];
                            }
                            return ['Actual matches baseline'];
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: axisColor, font: { weight: 600 } },
                    grid: { color: gridColor, drawBorder: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: axisColor,
                        callback: (value) => Number(value).toLocaleString()
                    },
                    grid: { color: gridColor, drawBorder: false },
                    title: {
                        display: true,
                        text: 'kWh',
                        color: axisColor,
                        font: { weight: 700 }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
