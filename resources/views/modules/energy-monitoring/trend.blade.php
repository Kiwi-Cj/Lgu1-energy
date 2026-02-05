@extends('layouts.qc-admin')
@section('title', 'Energy Trend')

@section('content')
<div class="energy-trend-dashboard">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1>
            <i class="fa-solid fa-chart-line"></i>
            Energy Consumption Trend
        </h1>
        <p>
            This page analyzes energy consumption patterns across facilities to
            support data-driven energy efficiency decisions.
        </p>
    </div>

    <!-- FILTER BAR -->
    <form class="filter-bar" method="GET" action="{{ route('energy.trend') }}">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" disabled selected>Select Facility</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Year</label>
            <select name="year" id="year">
                <option value="" disabled>Select Year</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" disabled selected>Select Month</option>
                @foreach($months as $month)
                    <option value="{{ $month['value'] }}" {{ request('month') == $month['value'] ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m', substr($month['value'],0,7))->format('F') }}</option>
                @endforeach
            </select>
        </div>

        <button class="filter-btn" type="submit">Apply Filter</button>
    </form>

    <!-- SUMMARY CARDS -->
    <div class="summary-grid">
        <div class="summary-card">
            <span>Total Consumption</span>
            <h2>{{ $totalConsumption ?? '0' }} kWh</h2>
            <p>Total recorded energy usage</p>
        </div>

        <div class="summary-card">
            <span>Peak Usage</span>
            <h2>{{ $peakUsage ?? '0' }} kWh</h2>
            <p>Highest consumption recorded</p>
        </div>

        <div class="summary-card">
            <span>Lowest Usage</span>
            <h2>{{ $lowestUsage ?? '0' }} kWh</h2>
            <p>Lowest consumption recorded</p>
        </div>
    </div>

    <!-- CHART + ANALYSIS -->
    <div class="analysis-card">
        <div class="analysis-header">
            <i class="fa-solid fa-bolt"></i>
            Energy Trend Analysis
        </div>

        <div class="analysis-body">
            <p class="analysis-text">
                The chart below presents the trend of energy consumption over the selected
                period. Monitoring these trends helps identify peak demand periods,
                inefficiencies, and opportunities for energy conservation initiatives.
            </p>

            {{-- CHART --}}
            @include('modules.energy-monitoring.partials.charts', [
                'chartData' => $trendData
            ])

            <!-- INSIGHT BOX -->
            <div class="insight-box">
                <strong>Trend Insight:</strong>
                <span>
                    Energy consumption tends to increase during high operational periods,
                    indicating greater facility activity. These peaks may be targeted for
                    efficiency improvements and load management strategies.
                </span>
            </div>
        </div>
    </div>

</div>

<style>
.energy-trend-dashboard{
    width:100%;
    margin:0;
    padding:2rem 0;
}

/* HEADER */
.page-header h1{
    font-size:2.2rem;
    font-weight:700;
    color:#3762c8;
    display:flex;
    align-items:center;
    gap:12px;
}
.page-header p{
    margin-top:6px;
    color:#6b7280;
    max-width:700px;
}

/* FILTER BAR */
.filter-bar{
    display:flex;
    gap:1rem;
    align-items:end;
    margin:2rem 0;
    flex-wrap:wrap;
}
.filter-group{
    display:flex;
    flex-direction:column;
    gap:4px;
}
.filter-group label{
    font-size:0.9rem;
    font-weight:500;
}
.filter-group select,
.filter-group input{
    padding:0.55rem 1rem;
    border-radius:8px;
    border:1px solid #d1d5db;
}
.filter-btn{
    background:#3762c8;
    color:#fff;
    padding:0.6rem 1.6rem;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

/* SUMMARY */
.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:1.5rem;
    margin-bottom:2rem;
}
.summary-card{
    background:#f5f7fa;
    border-radius:14px;
    padding:1.3rem;
    box-shadow:0 4px 14px rgba(0,0,0,0.08);
}
.summary-card span{
    color:#6b7280;
    font-size:0.95rem;
}
.summary-card h2{
    margin:6px 0;
    font-size:1.9rem;
    color:#3762c8;
}
.summary-card p{
    font-size:0.85rem;
    color:#9ca3af;
}

/* ANALYSIS CARD */
.analysis-card{
    background:#fff;
    border-radius:20px;
    box-shadow:0 8px 30px rgba(55,98,200,0.15);
    overflow:hidden;
}
.analysis-header{
    background:linear-gradient(135deg,#3762c8,#4f7cff);
    color:#fff;
    padding:1rem 1.5rem;
    font-weight:600;
    display:flex;
    gap:10px;
    align-items:center;
}
.analysis-body{
    padding:1.5rem;
}
.analysis-text{
    margin-bottom:1rem;
    color:#4b5563;
    line-height:1.6;
}

/* INSIGHT */
.insight-box{
    margin-top:1.8rem;
    background:#eef2ff;
    padding:1rem 1.2rem;
    border-radius:12px;
    font-size:0.95rem;
}
.insight-box strong{
    color:#3762c8;
    margin-right:6px;
}
</style>
@endsection
