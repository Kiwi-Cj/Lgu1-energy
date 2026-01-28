@extends('layouts.qc-admin')
@section('title', 'Energy Monitoring Dashboard')

@section('content')
@php
    $userRole = strtolower(auth()->user()->role ?? '');
@endphp


<h2 style="font-size:2rem; font-weight:700; margin-bottom:1.5rem;">Energy Monitoring Dashboard</h2>

<!-- OVERVIEW CARDS -->
<div style="width:100%; display:flex; gap:24px; flex-wrap:wrap; margin-bottom:2.5rem;">
    <div style="flex:1 1 220px; background:#f5f8ff; padding:20px; border-radius:14px;">
        <div style="font-weight:500; color:#3762c8;">🏢 Total Facilities</div>
        <div style="font-weight:700; font-size:2rem;">{{ $totalFacilities ?? '-' }}</div>
    </div>

    <div style="flex:1 1 220px; background:#fff0f3; padding:20px; border-radius:14px;">
        <div style="font-weight:500; color:#e11d48;">
            🚨 Facilities with <b>HIGH</b> Alert
        </div>
        <div style="font-weight:700; font-size:2rem; color:#e11d48;">
            {{ $highAlertCount ?? 0 }}
        </div>
    </div>

    <div style="flex:1 1 220px; background:#e0f2fe; padding:20px; border-radius:14px;">
        <div style="font-weight:500; color:#0284c7;">
            ⚡ Avg kWh vs Baseline (This Month)
        </div>
        <div style="font-weight:700; font-size:2rem;">
            {{ $avgKwhVsBaseline ?? '-' }}
        </div>
    </div>

    <div style="flex:1 1 220px; background:#f0fdf4; padding:20px; border-radius:14px;">
        <div style="font-weight:500; color:#22c55e;">
            � Total Energy Cost (This Month)
        </div>
        <div style="font-weight:700; font-size:2rem; color:#22c55e;">
            ₱{{ number_format($totalEnergyCost ?? 0, 2) }}
        </div>
    </div>
</div>

<!-- FACILITY TABLE (Dynamic: search + pagination) -->
<form method="GET" action="" style="margin-bottom:18px; display:flex; gap:10px; align-items:center;">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search facility..." style="border-radius:8px; border:1px solid #c3cbe5; padding:8px 14px; font-size:1rem; width:220px;">
    <button type="submit" style="background:#2563eb; color:#fff; border:none; border-radius:8px; padding:8px 18px; font-weight:600; font-size:1rem; cursor:pointer;">Search</button>
    @if(request('search'))
        <a href="{{ url()->current() }}" style="margin-left:8px; color:#e11d48; text-decoration:underline; font-size:0.98rem;">Clear</a>
    @endif
</form>
<div style="background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(31,38,135,0.08); margin-bottom:1.2rem;">
    <table style="width:100%; border-collapse:collapse; font-size:0.93rem;">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:center;">
                <th style="padding:6px 8px; text-align:center;">Facility</th>
                <th style="padding:6px 8px; text-align:center;">Type</th>
                <th style="padding:6px 8px; text-align:center;">Status</th>
                <th style="padding:6px 8px; text-align:center;">Month</th>
                <th style="padding:6px 8px; text-align:center;">Floor Area</th>
                <th style="padding:6px 8px; text-align:center;">Baseline kWh</th>
                <th style="padding:6px 8px; text-align:center;">Trend</th>
                <th style="padding:6px 8px; text-align:center;">EUI (kWh/m²)</th>
                <th style="padding:6px 8px; text-align:center;">Last Maint</th>
                <th style="padding:6px 8px; text-align:center;">Next Maint</th>
                <th style="padding:6px 8px; text-align:center;">Alerts</th>
                @if($userRole !== 'staff')
                    <th style="padding:6px 8px; text-align:center;">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse($facilities as $facility)
            @php 
                $record = $facility->energyRecords->first(); 
                $currentMonth = date('n');
                $currentYear = date('Y');
                $trendAnalysis = '-';
                $alertLevel = '-';
                $eui = null;
                $hasCurrentMonth = $record && $record->month == $currentMonth && $record->year == $currentYear;
            @endphp
            @if($hasCurrentMonth)
                @php
                    $actualKwh = $record->actual_kwh ?? 0;
                    $floorArea = $facility->floor_area;
                    $eui = (isset($floorArea) && $floorArea > 0) ? number_format($actualKwh / $floorArea, 2) : null;

                    // Trend and Alert Logic
                    $previousRecord = \App\Models\EnergyRecord::where('facility_id', $facility->id)
                        ->where(function($q) use ($record) {
                            $q->where('year', '<', $record->year)
                              ->orWhere(function($q2) use ($record){
                                  $q2->where('year', $record->year)
                                     ->where('month', '<', $record->month);
                              });
                        })
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->first();

                    if($previousRecord){
                        $trend = $previousRecord->actual_kwh > 0 
                            ? round((($record->actual_kwh - $previousRecord->actual_kwh)/$previousRecord->actual_kwh)*100, 2) 
                            : null;
                        $trendAnalysis = $trend !== null ? $trend . '%' : '-';

                        // Facility size alert thresholds
                        $size = $facility->size_label ?? 'Medium'; // Small, Medium, Large, Extra Large
                        $alert = 'Low';

                        if($trend !== null){
                            if($size === 'Small'){
                                if($trend > 30) $alert = 'High';
                                elseif($trend > 15) $alert = 'Medium';
                            } elseif($size === 'Medium'){
                                if($trend > 20) $alert = 'High';
                                elseif($trend > 10) $alert = 'Medium';
                            } elseif($size === 'Large' || $size === 'Extra Large'){
                                if($trend > 15) $alert = 'High';
                                elseif($trend > 5) $alert = 'Medium';
                            }
                        }
                        $alertLevel = $alert;
                    }
                @endphp
                <tr style="border-bottom:1px solid #e5e7eb; text-align:center; background:{{ $loop->even ? '#f8fafc' : '#fff' }}; font-size:0.93rem;">
                    <td style="padding:6px 8px; text-align:center;">{{ $facility->name }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $facility->type }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $facility->status }}</td>
                    <td style="padding:6px 8px; text-align:center;">
                        @php
                            $monthsArr = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        @endphp
                        {{ isset($record->month) ? $monthsArr[$record->month-1] : '-' }}
                    </td>
                    <td style="padding:6px 8px; text-align:center;">{{ $facility->floor_area ?? '-' }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $facility->average_monthly_kwh ?? '-' }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $trendAnalysis }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $eui !== null ? $eui : '-' }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $record->last_maintenance ?? '-' }}</td>
                    <td style="padding:6px 8px; text-align:center;">{{ $record->next_maintenance ?? '-' }}</td>

                    <td style="padding:6px 8px; text-align:center;">
                        @if($alertLevel === 'High')
                            <span style="color:#e11d48; font-weight:600;">High</span>
                        @elseif($alertLevel === 'Medium')
                            <span style="color:#f59e42; font-weight:600;">Medium</span>
                        @elseif($alertLevel === 'Low')
                            <span style="color:#22c55e; font-weight:600;">Low</span>
                        @else
                            -
                        @endif
                    </td>

                    @if($userRole !== 'staff')
                    <td style="padding:12px; height:100%; text-align:center; vertical-align:middle;">
                        <div style="display:inline-flex; gap:10px; justify-content:center; align-items:center;">
                            <a href="{{ url('/modules/facilities/'.$facility->id.'/energy-profile') }}" title="View"
                                style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; background:#2563eb1a; color:#2563eb; border-radius:50%; font-size:1rem; transition:background 0.18s, color 0.18s; border:none; text-decoration:none;"
                                onmouseover="this.style.background='#2563eb';this.style.color='#fff'" onmouseout="this.style.background='#2563eb1a';this.style.color='#2563eb'">
                                <i class="fa fa-eye" style="font-size:1rem;"></i>
                            </a>
                            <button onclick="openResetBaselineModal({{ $facility->id }})" title="Reset Baseline"
                                style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; background:#f59e421a; color:#f59e42; border-radius:50%; font-size:1rem; transition:background 0.18s, color 0.18s; border:none; cursor:pointer;"
                                onmouseover="this.style.background='#f59e42';this.style.color='#fff'" onmouseout="this.style.background='#f59e421a';this.style.color='#f59e42'">
                                <i class="fa fa-repeat" style="font-size:1rem;"></i>
                            </button>
                            <button onclick="toggleEngineerApproval({{ $facility->id }})" title="Toggle Approval"
                                style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; background:#22c55e1a; color:#22c55e; border-radius:50%; font-size:1rem; transition:background 0.18s, color 0.18s; border:none; cursor:pointer;"
                                onmouseover="this.style.background='#22c55e';this.style.color='#fff'" onmouseout="this.style.background='#22c55e1a';this.style.color='#22c55e'">
                                <i class="fa fa-check-circle" style="font-size:1rem;"></i>
                            </button>
                            <!-- 🔥 ADDED: CREATE ENERGY ACTION -->
                            @if($alertLevel === 'High' || $alertLevel === 'Medium')
                            <a href="{{ url('/energy-actions/create?facility='.$facility->id) }}"
                               title="Create Energy Action"
                               style="width:28px;height:28px;border-radius:50%;
                                      background:#e11d481a;color:#e11d48;
                                      display:flex;align-items:center;justify-content:center;
                                      text-decoration:none;">
                                <i class="fa fa-bolt"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="11" style="padding:20px; text-align:center;">
                    No facilities found.
                </td>
            </tr>
        @endforelse
        </tbody>

    </table>
</div>

@if(method_exists($facilities, 'links'))
    <div style="margin:18px 0; text-align:center;">
        {{ $facilities->appends(request()->query())->links() }}
    </div>
@endif


@include('modules.facilities.partials.modals')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('energyTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trendLabels ?? []),
                datasets: [{
                    label: 'kWh Consumption',
                    data: @json($trendData ?? []),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});

function openResetBaselineModal(facilityId) {
    document.getElementById('reset_facility_id').value = facilityId;
    document.getElementById('resetBaselineModal').style.display = 'flex';
}

function toggleEngineerApproval(facilityId) {
    fetch(`/modules/facilities/${facilityId}/toggle-engineer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || 'Updated');
        location.reload();
    });
}
</script>

@endsection
