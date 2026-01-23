@extends('layouts.qc-admin')
@section('title', 'Annual Energy Summary')
@section('content')
<div class="energy-annual-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
    <div style="display:flex;align-items:center;gap:18px;">
        <a href="{{ route('modules.energy.index') }}" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:5px;padding:2px 10px;font-size:0.85rem;min-width:0;height:32px;display:flex;align-items:center;gap:4px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M15 8a.5.5 0 0 1-.5.5H3.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L3.707 7.5H14.5A.5.5 0 0 1 15 8z"/></svg>
            <span style="display:none;display:inline-block;">Back</span>
        </a>
        <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin:0;">Annual Energy Summary</h2>
    </div>
    <form method="GET" action="" style="display:flex;align-items:center;gap:12px;">
        <label for="facility_id" style="font-weight:600;margin-right:6px;">Facility</label>
        <select name="facility_id" id="facility_id" class="form-control" style="min-width:140px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" required>
            <option value="" disabled selected hidden>Select Facility</option>
            @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" @if($selectedFacility == $facility->id) selected @endif>{{ $facility->name }}</option>
            @endforeach
        </select>
        <label for="year" style="font-weight:600;margin-right:6px;margin-left:10px;">Year</label>
        <select name="year" id="year" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
            @foreach($years as $y)
                <option value="{{ $y }}" @if($selectedYear == $y) selected @endif>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;">Go</button>
        <a
            href="{{ route('modules.energy.annual.export-excel', array_filter([
                'facility_id' => request('facility_id'),
                'year' => request('year'),
            ])) }}"
            class="btn btn-success"
            style="background: linear-gradient(90deg,#22c55e,#16a34a); color:#fff; font-weight:600; border:none; border-radius:5px; padding:2px 10px; font-size:0.85rem; min-width:0; height:32px; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s; display:flex; align-items:center; gap:4px;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5V13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2.6a.5.5 0 0 1 1 0V13a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3v-2.6a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
            <span style="display:none;display:inline-block;">Excel</span>
        </a>
    </form>
</div>
@php
    $filterActive = request()->has('facility_id') && request('facility_id');
@endphp
@if($filterActive)
<div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Total Actual kWh</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ ($totalActualKwh ?? 0) != 0 ? $totalActualKwh : '-' }} kWh</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Annual Baseline</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ ($annualBaseline ?? 0) != 0 ? $annualBaseline : '-' }} kWh</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Annual Difference</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ ($annualDifference ?? 0) != 0 ? $annualDifference : '-' }} kWh</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Annual Status</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">
            @if(isset($annualStatus))
                <span style="color:{{ $annualStatus == 'High' ? '#e11d48' : '#22c55e' }};">{{ $annualStatus }}</span>
            @else
                -
            @endif
        </div>
    </div>
</div>
<div class="mb-5">
    <h4 style="font-weight:600;color:#3762c8;margin-bottom:12px;">Monthly Breakdown</h4>
    <table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
        <thead style="background:#e9effc;">
            <tr>
                <th style="text-align:center;">Month</th>
                <th style="text-align:center;">Actual kWh</th>
                <th style="text-align:center;">Baseline kWh</th>
                <th style="text-align:center;">Difference</th>
                <th style="text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @if(count($monthlyBreakdown) > 0)
                @foreach($monthlyBreakdown as $month)
                <tr>
                    <td>{{ $month['label'] }}</td>
                    <td>{{ $month['actual'] != 0 ? $month['actual'] : '-' }}</td>
                    <td>{{ $month['baseline'] != 0 ? $month['baseline'] : '-' }}</td>
                    <td>{{ $month['diff'] != 0 ? $month['diff'] : '-' }}</td>
                    <td>
                        <span style="font-weight:600;color:{{ $month['status'] == 'High' ? '#e11d48' : '#22c55e' }};">
                            {{ $month['status'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="5" class="text-center">No data for this filter.</td></tr>
            @endif
        </tbody>
    </table>
</div>
<div class="mb-5">
    <h4 style="font-weight:600;color:#3762c8;margin-bottom:12px;">Monthly kWh Graph</h4>
    <canvas id="annualChart" height="120"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('annualChart').getContext('2d');
const annualChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json(array_column($monthlyBreakdown, 'label')),
        datasets: [
            {
                label: 'Actual kWh',
                data: @json(array_column($monthlyBreakdown, 'actual')),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Baseline kWh',
                data: @json(array_column($monthlyBreakdown, 'baseline')),
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.08)',
                fill: true,
                tension: 0.3,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endif
@endsection
