@extends('layouts.qc-admin')
@section('title', 'Energy Monitoring Dashboard')

<style>
    .skip-link {
        position: absolute;
        left: -999px;
        top: 10px;
        background: #3762c8;
        color: #fff;
        padding: 8px 16px;
        z-index: 10000;
        border-radius: 6px;
        font-weight: 600;
        transition: left 0.2s;
    }
    .skip-link:focus { left: 10px; }
    
    /* Report Card Styling */
    .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        border: 1px solid #eef2f6;
        margin-bottom: 2rem;
    }

    @media (max-width: 600px) {
        h1 { font-size: 1.5rem !important; }
        .overview-cards { flex-direction: column !important; gap: 12px !important; }
        .report-card { padding: 15px; }
    }
</style>

@section('content')

@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
@endphp

<a href="#main-content" class="skip-link" tabindex="0">Skip to main content</a>

{{-- Alerts --}}
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

<div class="report-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:25px; gap: 20px; flex-wrap: wrap;">
        <div>
            <h1 style="margin:0; font-size:1.8rem; color:#1e293b; font-weight:800; letter-spacing:-0.5px;">
                Energy Monitoring <span style="color:#2563eb;">Dashboard</span>
            </h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:1rem;">Overview of all facility energy performance</p>
        </div>
        
        <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
            <div style="position:relative;">
                <i class="fa fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search facility..." 
                    style="border-radius:10px; border:1px solid #e2e8f0; padding:10px 10px 10px 35px; font-size:0.9rem; width:220px; outline:none; transition:border 0.2s;">
            </div>
            <button type="submit" style="background:#2563eb; color:#fff; border:none; border-radius:10px; padding:10px 20px; font-weight:600; cursor:pointer; transition:0.2s;">Search</button>
            @if(request('search'))
                <a href="{{ url()->current() }}" style="color:#e11d48; text-decoration:none; font-weight:600; font-size:0.9rem;">Clear</a>
            @endif
        </form>
    </div>

    <div class="overview-cards" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background:#f8fafc; padding:20px; border-radius:14px; border-left:4px solid #2563eb;">
            <div style="font-weight:700; font-size:0.75rem; color:#64748b; text-transform:uppercase; margin-bottom:8px;">Total Facilities</div>
            <div style="font-weight:800; font-size:1.8rem; color:#1e293b;">{{ $totalFacilities ?? '-' }}</div>
        </div>

        <div style="background:#fff1f2; padding:20px; border-radius:14px; border-left:4px solid #e11d48;">
            <div style="font-weight:700; font-size:0.75rem; color:#e11d48; text-transform:uppercase; margin-bottom:8px;">High Alert Facilities</div>
            <div style="font-weight:800; font-size:1.8rem; color:#1e293b;">{{ $highAlertCount ?? 0 }}</div>
        </div>

        <div style="background:#f0fdf4; padding:20px; border-radius:14px; border-left:4px solid #16a34a;">
            <div style="font-weight:700; font-size:0.75rem; color:#166534; text-transform:uppercase; margin-bottom:8px;">Total Cost (Month)</div>
            <div style="font-weight:800; font-size:1.8rem; color:#1e293b;">₱{{ number_format($totalEnergyCost ?? 0, 2) }}</div>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table id="main-content" style="width:100%; border-collapse:separate; border-spacing:0; min-width:1000px;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:15px; border-radius:10px 0 0 10px; color:#475569; font-weight:700; text-align:center;">Facility</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Type</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Month</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Floor Area</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Baseline kWh</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Trend</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">EUI</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Alerts</th>
                    @if($userRole !== 'staff')
                        <th style="padding:15px; border-radius:0 10px 10px 0; color:#475569; font-weight:700; text-align:center;">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            @forelse($facilities as $facility)
                @php 
                    $record = $facility->currentMonthRecord;
                    $trendAnalysis = '-';
                    $alertLevel = '-';
                    $eui = null;
                    $hasCurrentMonth = $record !== null;
                @endphp
                @if($hasCurrentMonth)
                    @php
                        $actualKwh = $record->actual_kwh ?? 0;
                        $floorArea = $facility->floor_area;
                        $eui = ($floorArea > 0) ? number_format($actualKwh / $floorArea, 2) : null;

                        $previousRecord = \App\Models\EnergyRecord::where('facility_id', $facility->id)
                            ->where(function($q) use ($record) {
                                $q->where('year', '<', $record->year)
                                  ->orWhere(function($q2) use ($record){
                                      $q2->where('year', $record->year)->where('month', '<', $record->month);
                                  });
                            })->orderBy('year', 'desc')->orderBy('month', 'desc')->first();

                        if($previousRecord){
                            $trend = $previousRecord->actual_kwh > 0 
                                ? round((($record->actual_kwh - $previousRecord->actual_kwh)/$previousRecord->actual_kwh)*100, 2) 
                                : null;
                            $trendAnalysis = $trend !== null ? ($trend > 0 ? '+'.$trend : $trend) . '%' : '-';
                            
                            $size = $facility->size_label ?? 'Medium';
                            $alert = 'Normal';
                            if($trend !== null){
                                if($size === 'Small'){
                                    if($trend > 40) $alert = 'Critical'; elseif($trend > 30) $alert = 'High';
                                    elseif($trend > 20) $alert = 'Moderate'; elseif($trend > 10) $alert = 'Low';
                                } elseif($size === 'Medium'){
                                    if($trend > 30) $alert = 'Critical'; elseif($trend > 20) $alert = 'High';
                                    elseif($trend > 15) $alert = 'Moderate'; elseif($trend > 7) $alert = 'Low';
                                } else {
                                    if($trend > 20) $alert = 'Critical'; elseif($trend > 12) $alert = 'High';
                                    elseif($trend > 8) $alert = 'Moderate'; elseif($trend > 4) $alert = 'Low';
                                }
                            }
                            $alertLevel = $alert;
                        }
                    @endphp
                    <tr style="border-bottom:1px solid #f1f5f9; transition: background 0.2s;">
                        <td style="padding:15px; text-align:center; font-weight:700; color:#334155;">{{ $facility->name }}</td>
                        <td style="padding:15px; text-align:center; color:#64748b;">{{ $facility->type }}</td>
                        <td style="padding:15px; text-align:center; font-weight:600;">
                            @php $monthsArr = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
                            {{ $monthsArr[$record->month-1] ?? '-' }}
                        </td>
                        <td style="padding:15px; text-align:center;">{{ $facility->floor_area ?? '-' }} <small>m²</small></td>
                        <td style="padding:15px; text-align:center; color:#2563eb; font-weight:600;">
                            @php
                                $energyProfile = $facility->energyProfiles()->latest()->first();
                                $baselineKwh = $energyProfile && is_numeric($energyProfile->baseline_kwh) ? floatval($energyProfile->baseline_kwh) : null;
                            @endphp
                            {{ $baselineKwh ? number_format($baselineKwh, 2) : '-' }}
                        </td>
                        <td style="padding:15px; text-align:center; font-weight:700; color:{{ str_contains($trendAnalysis, '+') ? '#e11d48' : '#16a34a' }};">
                            {{ $trendAnalysis }}
                        </td>
                        <td style="padding:15px; text-align:center;">{{ $eui ?? '-' }}</td>
                        <td style="padding:15px; text-align:center;">
                            @php
                                $colors = ['Critical'=>'#7c1d1d','High'=>'#e11d48','Moderate'=>'#f59e42','Low'=>'#16a34a','Normal'=>'#2563eb'];
                                $bgs = ['Critical'=>'#fef2f2','High'=>'#fff1f2','Moderate'=>'#fff7ed','Low'=>'#f0fdf4','Normal'=>'#eff6ff'];
                                $c = $colors[$alertLevel] ?? '#64748b';
                                $b = $bgs[$alertLevel] ?? '#f8fafc';
                            @endphp
                            <span style="background:{{$b}}; color:{{$c}}; padding:4px 10px; border-radius:20px; font-size:0.8rem; font-weight:700; border:1px solid {{$c}}33;">
                                {{ $alertLevel }}
                            </span>
                        </td>
                        @if($userRole !== 'staff')
                        <td style="padding:15px; text-align:center;">
                            <div style="display:flex; justify-content:center; gap:8px;">
                                <a href="{{ url('/modules/facilities/'.$facility->id.'/energy-profile') }}" title="View Profile" style="color:#2563eb;"><i class="fa fa-eye"></i></a>
                                <button onclick="openResetBaselineModal({{ $facility->id }})" title="Reset" style="background:none; border:none; color:#f59e42; cursor:pointer;"><i class="fa fa-repeat"></i></button>
                                <button onclick="toggleEngineerApproval({{ $facility->id }})" title="Approve" style="background:none; border:none; color:#16a34a; cursor:pointer;"><i class="fa fa-check-circle"></i></button>
                                @if($alertLevel === 'High' || $alertLevel === 'Critical')
                                    <a href="{{ url('/energy-actions/create?facility='.$facility->id) }}" title="Action" style="color:#e11d48;"><i class="fa fa-bolt"></i></a>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @endif
            @empty
                <tr><td colspan="9" style="padding:50px; text-align:center; color:#94a3b8;">No facilities found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($facilities, 'links'))
        <div style="margin-top:20px; display:flex; justify-content:center;">
            {{ $facilities->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@include('modules.facilities.partials.modals')

<script>
// Logic scripts (Reset & Approval)
function openResetBaselineModal(facilityId) {
    document.getElementById('reset_facility_id').value = facilityId;
    document.getElementById('resetBaselineModal').style.display = 'flex';
}

function toggleEngineerApproval(facilityId) {
    if(!confirm('Toggle engineer approval for this facility?')) return;
    fetch(`/modules/facilities/${facilityId}/toggle-engineer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(() => location.reload());
}

// Auto-hide alert
window.addEventListener('DOMContentLoaded', () => {
    const success = document.getElementById('successAlert');
    if(success) setTimeout(() => success.style.opacity = '0', 3000);
});
</script>

@endsection