@extends('layouts.qc-admin')

@section('content')
<!-- 🌙 Dark Mode Toggle & Notification Icon (Header Right, Top Only) -->
<div style="width:100%;display:flex;justify-content:flex-end;align-items:center;gap:16px;position:relative;margin-bottom:0;">
    <button class="dark-toggle" id="darkToggleHeader" style="display:flex;align-items:center;gap:8px;padding:8px 22px;border-radius:999px;background:#181E29;color:#fff;font-weight:600;font-size:1rem;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.10);margin-top:18px;margin-right:8px;">
        <span style="font-size:1.2em;">🌙</span> Dark Mode
    </button>
    <button id="notifBtn" style="background:#181E29;border:none;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;margin-top:18px;margin-right:18px;">
        <span style="color:#FFD580;font-size:1.5em;">
            <i class="fa-solid fa-bell"></i>
        </span>
        <span id="notifBadge" style="display:{{ ($alerts ?? []) && count($alerts) > 0 ? 'block' : 'none' }};position:absolute;top:8px;right:8px;background:#e11d48;color:#fff;font-size:0.8em;padding:2px 6px;border-radius:999px;min-width:18px;text-align:center;">{{ count($alerts ?? []) }}</span>
    </button>
    <!-- Dropdown for alerts -->
    <div id="notifDropdown" style="display:none;position:absolute;top:54px;right:0;min-width:320px;max-width:90vw;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.18);z-index:1000;padding:0;overflow:hidden;">
        @if(!empty($alerts) && count($alerts) > 0)
            <ul style="list-style:none;margin:0;padding:0;">
                @foreach($alerts as $alert)
                    <li style="padding:14px 18px;border-bottom:1px solid #f0f0f0;font-size:1rem;color:#222;display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#e11d48;"></i>
                        <span>{{ $alert }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div style="padding:18px;text-align:center;color:#888;font-size:1rem;">No active alerts</div>
        @endif
    </div>
</div>

<div style="max-width:1200px;margin:0 auto;">
	<!-- 1️⃣ Page Header -->
	<div style="margin-bottom:24px;">
		<h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">LGU Energy Efficiency & Conservation Management System</h1>
		<div style="font-size:1.2rem;color:#555;">Dashboard Overview</div>
		<div style="margin-top:8px;font-size:1rem;color:#888;">
			<span id="current-date">{{ date('F j, Y') }}</span> |
			<span id="user-role">{{ Auth::user()->role ?? 'User' }}</span>
		</div>
	</div>

	<!-- 2️⃣ SUMMARY CARDS -->
	<div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#3762c8;">🏢 Total Facilities</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $totalFacilities ?? '-' }} Buildings</div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#22c55e;">⚡ Total Energy Consumption</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $totalKwh ?? '-' }} kWh</div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#f59e42;">💰 Total Energy Cost</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">₱{{ $totalCost ?? '-' }}</div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🚨 Active Alerts</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $activeAlerts ?? '-' }}</div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#e0f7fa;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(0,188,212,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#0097a7;">🛠 Ongoing Maintenance</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $ongoingMaintenance ?? '-' }}</div>
		</div>
	</div>

	<!-- 3️⃣ ENERGY OVERVIEW (Charts Section) -->
	<div style="margin-bottom:2rem;">
		<h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">Energy Overview</h3>
		<div style="display:flex;gap:32px;flex-wrap:wrap;">
			<div style="flex:1 1 400px;min-width:400px;background:#fff;padding:18px;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.07);">
				<div style="font-weight:600;margin-bottom:8px;">Monthly Energy Consumption</div>
				<canvas id="energyChart" height="180"></canvas>
			</div>
			<div style="flex:1 1 400px;min-width:400px;background:#fff;padding:18px;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.07);">
				<div style="font-weight:600;margin-bottom:8px;">Energy Cost Trend</div>
				<canvas id="costChart" height="180"></canvas>
			</div>
		</div>
	</div>

	<!-- 4️⃣ RECENT ACTIVITY / LOGS -->
	<div style="margin-bottom:2rem;">
		<h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">Recent Activity</h3>
		<ul style="list-style:none;padding:0;">
			@forelse($recentLogs ?? [] as $log)
				<li style="margin-bottom:8px;font-size:1.05rem;color:#444;">{{ $log }}</li>
			@empty
				<li style="color:#888;">No recent activity.</li>
			@endforelse
		</ul>
	</div>

	<!-- 5️⃣ QUICK ACCESS / SHORTCUTS -->
	@php
		$userRole = strtolower(auth()->user()->role ?? '');
	@endphp
	<div style="margin-bottom:2rem;display:flex;gap:18px;flex-wrap:wrap;">
		@if($userRole !== 'staff')
			<a href="{{ route('facilities.create') }}" class="btn btn-success" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:1rem;display:flex;align-items:center;gap:8px;"><span>➕</span> Add Facility</a>
		@endif
		<a href="{{ route('reports.monthly') }}" class="btn btn-primary" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:1rem;display:flex;align-items:center;gap:8px;"><span>📄</span> View Monthly Report</a>
		@if($userRole === 'admin')
			<a href="{{ route('settings.index') }}" class="btn btn-secondary" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:1rem;display:flex;align-items:center;gap:8px;"><span>⚙️</span> System Settings</a>
			<a href="{{ route('users.index') }}" class="btn btn-info" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:1rem;display:flex;align-items:center;gap:8px;"><span>👤</span> Manage Users</a>
		@endif
	</div>

	<!-- 6️⃣ ALERTS & NOTIFICATIONS -->
	<div style="margin-bottom:2rem;">
		<h3 style="font-size:1.3rem;font-weight:700;color:#e11d48;margin-bottom:12px;">Alerts & Notifications</h3>
		<ul style="list-style:none;padding:0;">
			@forelse($alerts ?? [] as $alert)
				<li style="margin-bottom:8px;font-size:1.05rem;color:#e11d48;">{{ $alert }}</li>
			@empty
				<li style="color:#888;">No alerts or notifications.</li>
			@endforelse
		</ul>
	</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Example chart data, replace with dynamic data
var energyCtx = document.getElementById('energyChart').getContext('2d');
var energyChart = new Chart(energyCtx, {
	type: 'bar',
	data: {
		labels: {!! json_encode($energyChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun']) !!},
		datasets: [{
			label: 'kWh',
			data: {!! json_encode($energyChartData ?? [1200, 1100, 1300, 1250, 1400, 1350]) !!},
			backgroundColor: '#3762c8',
		}]
	},
	options: { responsive: true }
});

var costCtx = document.getElementById('costChart').getContext('2d');
var costChart = new Chart(costCtx, {
	type: 'line',
	data: {
		labels: {!! json_encode($costChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun']) !!},
		datasets: [{
			label: '₱',
			data: {!! json_encode($costChartData ?? [50000, 48000, 52000, 51000, 53000, 55000]) !!},
			borderColor: '#e11d48',
			backgroundColor: 'rgba(225,29,72,0.08)',
			fill: true,
		}]
	},
	options: { responsive: true }
});

document.addEventListener('DOMContentLoaded', function() {
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    let dropdownOpen = false;
    notifBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownOpen = !dropdownOpen;
        notifDropdown.style.display = dropdownOpen ? 'block' : 'none';
    });
    document.addEventListener('click', function(e) {
        if (dropdownOpen && !notifDropdown.contains(e.target) && e.target !== notifBtn) {
            notifDropdown.style.display = 'none';
            dropdownOpen = false;
        }
    });
});
</script>
@endpush
@endsection
