@extends('layouts.qc-admin')
@section('title', 'Energy Monitoring Dashboard')
@section('content')

@php
    $userRole = strtolower(auth()->user()->role ?? '');
@endphp

<h2 style="font-size:2rem; font-weight:700; margin-bottom:1.5rem;">Energy Monitoring Dashboard</h2>

<!-- Summary Cards -->
<div style="display:flex; gap:24px; flex-wrap:wrap; margin-bottom:2rem;">
    <div style="flex:1 1 180px; background:#f5f8ff; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-weight:500; color:#3762c8;">🏢 Total Facilities</div>
        <div style="font-weight:700; font-size:1.8rem;">{{ $totalFacilities ?? '-' }}</div>
    </div>
    <div style="flex:1 1 180px; background:#f0fdf4; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(34,197,94,0.08);">
        <div style="font-weight:500; color:#22c55e;">🟢 Active</div>
        <div style="font-weight:700; font-size:1.8rem;">{{ $activeFacilities ?? '-' }}</div>
    </div>
    <div style="flex:1 1 180px; background:#fff7ed; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(234,179,8,0.08);">
        <div style="font-weight:500; color:#f59e42;">🛠 Maintenance</div>
        <div style="font-weight:700; font-size:1.8rem;">{{ $maintenanceFacilities ?? '-' }}</div>
    </div>
    <div style="flex:1 1 180px; background:#fff0f3; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(225,29,72,0.08);">
        <div style="font-weight:500; color:#e11d48;">🚫 Inactive</div>
        <div style="font-weight:700; font-size:1.8rem;">{{ $inactiveFacilities ?? '-' }}</div>
    </div>
    <div style="flex:1 1 180px; background:#e0f2fe; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(14,165,233,0.08);">
        <div style="font-weight:500; color:#0284c7;">⚡ Avg Monthly kWh</div>
        <div style="font-weight:700; font-size:1.8rem;">{{ round($avgMonthlyKwh ?? 0,2) }}</div>
    </div>
</div>

<!-- Facility Table -->
<div style="overflow-x:auto; margin-bottom:2rem;">
<table style="width:100%; border-collapse:collapse;">
    <thead style="background:#f3f4f6; color:#111;">
        <tr>
            <th style="padding:10px 12px; text-align:left;">Facility</th>
            <th>Type</th>
            <th>Status</th>
            <th>Floor Area</th>
            <th>Baseline kWh</th>
            <th>Trend</th>
            <th>EUI (kWh/m²)</th>
            <th>Last Maint</th>
            <th>Next Maint</th>
            <th>Alerts</th>
            @if($userRole !== 'staff') <th>Actions</th> @endif
        </tr>
    </thead>
    <tbody>
        @forelse($facilities as $facility)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td>{{ $facility->name }}</td>
                <td>{{ $facility->type }}</td>
                <td>{{ $facility->status }}</td>
                <td>{{ $facility->floor_area ?? '-' }}</td>
                <td>{{ $facility->baseline_kwh ?? '-' }}</td>
                <td>{{ $facility->trend_analysis ?? '-' }}</td>
                <td>{{ $facility->monthly_eui ?? '-' }}</td>
                <td>{{ $facility->last_maintenance ?? '-' }}</td>
                <td>{{ $facility->next_maintenance ?? '-' }}</td>
                <td>
                    @if($facility->alert_level)
                        <span style="color:red; font-weight:600;">{{ $facility->alert_level }}</span>
                    @else
                        -
                    @endif
                </td>
                @if($userRole !== 'staff')
                <td style="display:flex; gap:6px;">
                    <a href="{{ url('/modules/facilities/'.$facility->id.'/energy-profile') }}" title="View"><i class="fa fa-eye"></i></a>
                    <button onclick="openResetBaselineModal({{ $facility->id }})" title="Reset Baseline"><i class="fa fa-repeat"></i></button>
                    <button onclick="toggleEngineerApproval({{ $facility->id }})" title="Toggle Approval"><i class="fa fa-check-circle"></i></button>
                </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align:center; padding:20px;">No facilities found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>

<!-- Trend Chart -->
<div style="margin-bottom:2rem;">
    <canvas id="energyTrendChart" style="width:100%; max-width:800px; height:300px;"></canvas>
</div>

<!-- Modals -->
@include('modules.facilities.partials.modals')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const ctx = document.getElementById('energyTrendChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($trendLabels ?? []),
            datasets: [{
                label: 'kWh Consumption',
                data: @json($trendData ?? []),
                borderColor:'#2563eb',
                backgroundColor:'rgba(37,99,235,0.2)',
                fill:true,
                tension:0.2,
                pointRadius:5
            }]
        },
        options:{
            responsive:true,
            plugins:{
                legend:{display:true},
                tooltip:{mode:'index', intersect:false}
            },
            scales:{
                y:{beginAtZero:true},
                x:{title:{display:true, text:'Month'}}
            }
        }
    });
});

// Reset Baseline
function openResetBaselineModal(facilityId){
    document.getElementById('reset_facility_id').value = facilityId;
    document.getElementById('resetBaselineModal').style.display='flex';
}

document.getElementById('resetBaselineForm')?.addEventListener('submit', function(e){
    e.preventDefault();
    const id = document.getElementById('reset_facility_id').value;
    const reason = document.getElementById('reset_reason').value;

    fetch(`/modules/facilities/${id}/reset-baseline`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({reason})
    }).then(res=>res.json()).then(data=>{
        alert(data.message||'Baseline reset!');
        document.getElementById('resetBaselineModal').style.display='none';
        location.reload();
    });
});

// Engineer Approval Toggle
function toggleEngineerApproval(facilityId){
    fetch(`/modules/facilities/${facilityId}/toggle-engineer`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}
    }).then(res=>res.json()).then(data=>{
        alert(data.message||'Engineer approval toggled!');
        location.reload();
    });
}
</script>

@endsection
