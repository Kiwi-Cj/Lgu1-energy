<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Energy Summary</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
        h1 { margin: 0; color: #0f172a; font-size: 21px; }
        .meta { margin: 5px 0 14px; color: #64748b; }
        .kpis { width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 6px; }
        .kpis td { width: 16.66%; padding: 9px; border: 1px solid #dbe4f0; background: #f8fafc; }
        .label { color: #64748b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .value { margin-top: 4px; color: #0f172a; font-size: 13px; font-weight: bold; }
        .insights { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .insights td { width: 25%; padding: 8px; border: 1px solid #dbe4f0; vertical-align: top; }
        .insights strong { display: block; margin-top: 3px; color: #0f172a; }
        .section-title { margin: 10px 0 6px; color: #0f172a; font-size: 13px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { padding: 6px 7px; border: 1px solid #cbd5e1; }
        table.data th { background: #eaf1fb; color: #334155; font-size: 8px; text-transform: uppercase; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .center { text-align: center; }
        .bar-track { width: 95px; height: 7px; background: #e2e8f0; }
        .bar { height: 7px; background: #2563eb; }
        .up { color: #b91c1c; font-weight: bold; }
        .down { color: #15803d; font-weight: bold; }
        .stable { color: #475569; font-weight: bold; }
        .footer { margin-top: 10px; color: #64748b; font-size: 8px; }
    </style>
</head>
<body>
@php
    $months = collect($summary['months'] ?? []);
    $maxChartValue = max(1, (float) $months->max(function ($month) {
        return max((float) ($month['actual'] ?? 0), (float) ($month['baseline'] ?? 0));
    }));
    $formatNumber = fn ($value) => $value === null ? '-' : number_format((float) $value, 2);
    $formatPercent = fn ($value) => $value === null
        ? '-'
        : (((float) $value > 0 ? '+' : '') . number_format((float) $value, 2) . '%');
@endphp

<h1>Annual Energy Summary</h1>
<div class="meta">
    <strong>{{ $summary['facility'] }}</strong> &middot; {{ $summary['year'] }}
    &middot; Generated {{ $generatedAt }}
</div>

<table class="kpis">
    <tr>
        <td><div class="label">Total Actual</div><div class="value">{{ $formatNumber($summary['total_actual']) }} kWh</div></td>
        <td><div class="label">Total Baseline</div><div class="value">{{ $formatNumber($summary['total_baseline']) }} kWh</div></td>
        <td><div class="label">Annual Variance</div><div class="value">{{ $formatNumber($summary['total_variance']) }} kWh</div></td>
        <td><div class="label">Variance Rate</div><div class="value">{{ $formatPercent($summary['variance_percent']) }}</div></td>
        <td><div class="label">Monthly Average</div><div class="value">{{ $formatNumber($summary['average_actual']) }} kWh</div></td>
        <td><div class="label">Data Completeness</div><div class="value">{{ $summary['months_recorded'] }} / 12</div></td>
    </tr>
</table>

<table class="insights">
    <tr>
        <td>
            <span class="label">Annual Performance</span>
            <strong>{{ $summary['annual_status'] }} {{ $formatPercent($summary['variance_percent']) }}</strong>
        </td>
        <td>
            <span class="label">Peak / Lowest Month</span>
            <strong>
                {{ $summary['peak_month']['label'] ?? 'N/A' }} {{ $formatNumber($summary['peak_month']['actual'] ?? null) }} /
                {{ $summary['lowest_month']['label'] ?? 'N/A' }} {{ $formatNumber($summary['lowest_month']['actual'] ?? null) }} kWh
            </strong>
        </td>
        <td>
            <span class="label">Baseline Adherence</span>
            <strong>{{ $summary['months_above_baseline'] }} above &middot; {{ $summary['months_below_baseline'] }} below</strong>
        </td>
        <td>
            <span class="label">Energy Cost / Largest Moves</span>
            <strong>
                PHP {{ $formatNumber($summary['total_cost']) }}
                &middot; Up {{ $formatPercent($summary['peak_increase']['change_percent'] ?? null) }}
                &middot; Down {{ $formatPercent($summary['peak_drop']['change_percent'] ?? null) }}
            </strong>
        </td>
    </tr>
</table>

<div class="section-title">Monthly Breakdown</div>
<table class="data">
    <thead>
        <tr>
            <th>Month</th>
            <th>Actual Usage</th>
            <th class="num">Actual kWh</th>
            <th class="num">Baseline kWh</th>
            <th class="num">Variance kWh</th>
            <th class="num">Cost PHP</th>
            <th class="num">Change</th>
            <th class="center">Direction</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary['months'] as $month)
            @php
                $actual = $month['actual'];
                $barWidth = $actual === null ? 0 : max(1, min(100, ((float) $actual / $maxChartValue) * 100));
                $direction = $month['direction'] ?? 'none';
            @endphp
            <tr>
                <td><strong>{{ $month['label'] }}</strong></td>
                <td>
                    <div class="bar-track"><div class="bar" style="width:{{ $barWidth }}%;"></div></div>
                </td>
                <td class="num">{{ $formatNumber($actual) }}</td>
                <td class="num">{{ $formatNumber($month['baseline']) }}</td>
                <td class="num">{{ $formatNumber($month['variance']) }}</td>
                <td class="num">{{ $formatNumber($month['cost']) }}</td>
                <td class="num {{ $direction === 'up' ? 'up' : ($direction === 'down' ? 'down' : 'stable') }}">
                    {{ $formatPercent($month['change_percent']) }}
                </td>
                <td class="center {{ $direction === 'up' ? 'up' : ($direction === 'down' ? 'down' : 'stable') }}">
                    {{ $direction === 'none' ? '-' : ucfirst($direction) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Change percentages compare each recorded month with the previous available recorded month.
    LGU Energy Monitoring System.
</div>
</body>
</html>
