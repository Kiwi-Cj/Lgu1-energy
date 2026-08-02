
@extends('layouts.qc-admin')
@section('title','Facility Details')

@section('content')
@php
	$user = auth()->user();
@endphp

@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif
@if(session('error'))
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-circle-xmark" style="color:#e11d48;font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>

<style>
body.dark-mode .facility-show-page .facility-show-shell {
    background: linear-gradient(145deg, #0f172a, #111827) !important;
    box-shadow: 0 14px 34px rgba(2, 6, 23, 0.6);
}

body.dark-mode .facility-show-page [style*="background:#fff"],
body.dark-mode .facility-show-page [style*="background: #fff"],
body.dark-mode .facility-show-page [style*="background:#ffffff"],
body.dark-mode .facility-show-page [style*="background: #ffffff"],
body.dark-mode .facility-show-page [style*="background:#f8fafc"],
body.dark-mode .facility-show-page [style*="background: #f8fafc"],
body.dark-mode .facility-show-page [style*="background:#f1f5f9"],
body.dark-mode .facility-show-page [style*="background: #f1f5f9"],
body.dark-mode .facility-show-page [style*="background:#e0f2fe"],
body.dark-mode .facility-show-page [style*="background: #e0f2fe"] {
    background: #111827 !important;
    border-color: #334155 !important;
}

body.dark-mode .facility-show-page [style*="color:#222"],
body.dark-mode .facility-show-page [style*="color: #222"],
body.dark-mode .facility-show-page [style*="color:#1e293b"],
body.dark-mode .facility-show-page [style*="color: #1e293b"],
body.dark-mode .facility-show-page [style*="color:#475569"],
body.dark-mode .facility-show-page [style*="color: #475569"],
body.dark-mode .facility-show-page [style*="color:#64748b"],
body.dark-mode .facility-show-page [style*="color: #64748b"],
body.dark-mode .facility-show-page [style*="color:#9ca3af"],
body.dark-mode .facility-show-page [style*="color: #9ca3af"] {
    color: #e2e8f0 !important;
}

body.dark-mode .facility-show-page [style*="color:#2563eb"],
body.dark-mode .facility-show-page [style*="color: #2563eb"],
body.dark-mode .facility-show-page [style*="color:#0ea5e9"],
body.dark-mode .facility-show-page [style*="color: #0ea5e9"] {
    color: #93c5fd !important;
}

body.dark-mode .facility-show-page .energy-profile-details-card,
body.dark-mode .facility-show-page .energy-performance-card {
    background: #0f172a !important;
    border: 1px solid #334155;
    box-shadow: 0 12px 28px rgba(2, 6, 23, 0.55);
    color: #e2e8f0 !important;
}

body.dark-mode .facility-show-page .energy-profile-details-card h3,
body.dark-mode .facility-show-page .energy-performance-card h3 {
    color: #93c5fd !important;
}

body.dark-mode .facility-show-page .energy-profile-empty {
    color: #cbd5e1 !important;
}

body.dark-mode .facility-show-page .energy-warning {
    color: #fda4af !important;
}

.facility-show-page-container {
    width: 100%;
    margin: 28px 0;
}

.facility-show-shell {
    background: linear-gradient(145deg,#ffffff 0%,#f8fbff 62%,#eef4ff 100%);
    border: 1px solid #dbe5f2;
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 18px 45px rgba(15,23,42,.10);
    position: relative;
    width: 100%;
}

.facility-back-btn {
    position: absolute;
    left: 28px;
    top: -18px;
    background: #fff;
    padding: 10px 22px;
    border-radius: 14px;
    font-weight: 800;
    color: #2563eb;
    text-decoration: none;
    border: 1px solid #dbe5f2;
    box-shadow: 0 8px 22px rgba(37,99,235,.14);
    transition: transform .16s ease, box-shadow .16s ease;
}
.facility-back-btn:hover { transform:translateY(-2px); box-shadow:0 12px 26px rgba(37,99,235,.20); }

.facility-header {
    display: flex;
    gap: 28px;
    align-items: center;
    margin: 14px 0 26px;
    padding: 24px;
    border: 1px solid #dbe5f2;
    border-radius: 20px;
    background: rgba(255,255,255,.78);
    box-shadow: 0 10px 28px rgba(15,23,42,.06);
}

.facility-hero-image {
    width: 176px;
    height: 136px;
    border-radius: 18px;
    object-fit: cover;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .2);
}

.facility-hero-image-placeholder {
    width: 176px;
    height: 136px;
    border-radius: 18px;
    background: linear-gradient(135deg,#eff6ff,#e0e7ff);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #7c8da8;
    border: 1px dashed #b9c8df;
    flex-direction: column;
    gap: 7px;
}
.facility-image-empty-label { font-size:.7rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }

.facility-header-main {
    flex: 1;
}

.facility-title {
    margin: 0;
    font-size: 2.2rem;
    font-weight: 900;
    color: #1e293b;
}

.facility-subtitle {
    color: #6366f1;
    font-weight: 700;
    margin-top: 6px;
}

.facility-edit-btn {
    margin-top: 18px;
    background: #2563eb;
    color: #fff;
    padding: 8px 22px;
    border: none;
    border-radius: 11px;
    font-weight: 800;
    font-size: .9rem;
    cursor: pointer;
}

.facility-pill-row {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.facility-pill {
    padding: 7px 13px;
    border-radius: 999px;
    font-weight: 800;
    font-size: .78rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
}

.facility-pill.status-active {
    background: #dcfce7;
    color: #166534;
}

.facility-pill.status-maintenance {
    background: #fef3c7;
    color: #92400e;
}

.facility-pill.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.facility-pill.main {
    background: #eff6ff;
    color: #1d4ed8;
}

.facility-pill.sub {
    background: #f3e8ff;
    color: #7c3aed;
}

.facility-pill.approved {
    background: #ecfeff;
    color: #0f766e;
}

.facility-pill.primary {
    background: #f1f5f9;
    color: #334155;
}

.facility-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-top: 18px;
}

.facility-info-card {
    background: #fff;
    padding: 18px;
    border-radius: 16px;
    display: flex;
    gap: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 22px rgba(15,23,42,.06);
    align-items: center;
    min-height: 92px;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}
.facility-info-card:hover { transform:translateY(-2px); border-color:#bfdbfe; box-shadow:0 12px 26px rgba(37,99,235,.10); }

.facility-info-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: #2563eb1a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #2563eb;
}

.facility-info-label {
    font-size: .85rem;
    color: #64748b;
    font-weight: 700;
}

.facility-info-value {
    font-size: 1.05rem;
    font-weight: 800;
    color: #1e293b;
}

.facility-action-row {
    display: flex;
    gap: 14px;
    justify-content: flex-start;
    margin-top: 24px;
    padding: 16px;
    border: 1px solid #dbe5f2;
    border-radius: 16px;
    background: rgba(255,255,255,.86);
    flex-wrap: wrap;
}

.facility-action-link,
.facility-action-btn {
    color: #fff;
    padding: 12px 26px;
    border: none;
    border-radius: 11px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: transform .16s ease, filter .16s ease;
}
.facility-action-link:hover,.facility-action-btn:hover { transform:translateY(-2px); filter:brightness(.96); }
.facility-actions-title { flex:0 0 100%; color:#475569; font-size:.73rem; font-weight:850; letter-spacing:.07em; text-transform:uppercase; }

.facility-action-link.records {
    background: #0f766e;
}

.facility-action-link.submeters {
    background: #7c3aed;
}

.facility-action-link.profile {
    background: #2563eb;
}

.facility-action-btn.archive {
    background: #e11d48;
}

.facility-section-card {
    margin-top: 24px;
    padding: 24px;
    border: 1px solid #dbe5f2;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15,23,42,.06);
}
.facility-section-header { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:18px; flex-wrap:wrap; }
.facility-section-heading { display:flex; align-items:center; gap:11px; min-width:0; }
.facility-section-heading-icon { width:42px; height:42px; flex:0 0 42px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:#eff6ff; color:#2563eb; }
.facility-section-heading h3 { margin:0; color:#0f172a; font-size:1.05rem; font-weight:900; }
.facility-section-heading p { margin:3px 0 0; color:#64748b; font-size:.78rem; font-weight:600; }
.facility-section-link { display:inline-flex; align-items:center; gap:7px; padding:9px 12px; border:1px solid #bfdbfe; border-radius:10px; color:#1d4ed8; background:#eff6ff; text-decoration:none; font-size:.78rem; font-weight:800; }
.energy-profile-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; }
.energy-profile-item { padding:13px 14px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; min-width:0; }
.energy-profile-label { display:block; color:#64748b; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.035em; }
.energy-profile-value { display:block; margin-top:5px; color:#0f172a; font-size:.92rem; font-weight:850; overflow-wrap:anywhere; }
.meter-readiness { margin-top:14px; padding:13px 14px; border:1px solid #dbe5f2; border-radius:13px; background:#f8fafc; }
.meter-readiness-head { display:flex; justify-content:space-between; gap:12px; color:#475569; font-size:.76rem; font-weight:800; }
.meter-readiness-track { height:8px; margin-top:9px; overflow:hidden; border-radius:999px; background:#e2e8f0; }
.meter-readiness-bar { height:100%; border-radius:inherit; background:linear-gradient(90deg,#2563eb,#14b8a6); transition:width .25s ease; }
.energy-performance-layout { display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; }
.energy-performance-value { color:#2563eb; font-size:1.75rem; font-weight:950; letter-spacing:-.03em; }
.energy-performance-caption { margin-top:3px; color:#64748b; font-size:.78rem; font-weight:650; }
.energy-performance-badge { padding:9px 12px; border-radius:10px; background:#ecfdf5; color:#047857; font-size:.76rem; font-weight:850; }
.energy-profile-empty-state { padding:22px; border:1px dashed #cbd5e1; border-radius:14px; background:#f8fafc; text-align:center; }
.energy-profile-empty-state i { color:#94a3b8; font-size:1.5rem; }
.energy-profile-empty-state strong { display:block; margin-top:8px; color:#334155; }
.energy-profile-empty-state span { display:block; margin-top:4px; color:#64748b; font-size:.8rem; }

body.dark-mode .facility-show-page .facility-back-btn,
body.dark-mode .facility-show-page .facility-info-card,
body.dark-mode .facility-show-page .facility-pill.primary {
    background: #111827 !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}

body.dark-mode .facility-show-page .facility-title,
body.dark-mode .facility-show-page .facility-info-value {
    color: #e2e8f0 !important;
}

body.dark-mode .facility-show-page .facility-header,
body.dark-mode .facility-show-page .facility-section-card,
body.dark-mode .facility-show-page .facility-action-row,
body.dark-mode .facility-show-page .energy-profile-item,
body.dark-mode .facility-show-page .meter-readiness,
body.dark-mode .facility-show-page .energy-profile-empty-state {
    background: #111827 !important;
    border-color: #334155 !important;
}
body.dark-mode .facility-show-page .facility-section-heading h3,
body.dark-mode .facility-show-page .energy-profile-value,
body.dark-mode .facility-show-page .energy-profile-empty-state strong { color:#e2e8f0 !important; }
body.dark-mode .facility-show-page .facility-section-heading p,
body.dark-mode .facility-show-page .energy-profile-label,
body.dark-mode .facility-show-page .energy-performance-caption,
body.dark-mode .facility-show-page .meter-readiness-head,
body.dark-mode .facility-show-page .energy-profile-empty-state span { color:#94a3b8 !important; }
body.dark-mode .facility-show-page .meter-readiness-track { background:#334155; }

@media (max-width: 720px) {
    .facility-show-page-container {
        margin: 18px 0 0;
    }

    .facility-show-shell {
        padding: 28px 14px 18px;
        border-radius: 18px;
    }

    .facility-header { padding:16px; }

    .facility-back-btn {
        left: 14px;
        top: -16px;
        padding: 8px 14px;
    }

    .facility-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 18px;
    }

    .facility-hero-image,
    .facility-hero-image-placeholder {
        width: 100%;
        height: 180px;
    }

    .facility-title {
        font-size: 1.55rem;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .facility-info-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 10px;
    }

    .facility-info-card {
        min-width: 0;
        padding: 14px;
    }

    .facility-info-value {
        overflow-wrap: anywhere;
    }

    .facility-action-row {
        align-items: stretch;
        flex-direction: column;
        margin-top: 18px;
    }

    .facility-action-link,
    .facility-action-btn {
        width: 100%;
        justify-content: center;
    }

    .facility-section-card { padding:16px; border-radius:16px; }
    .facility-section-header { align-items:flex-start; }
    .facility-section-link { width:100%; justify-content:center; }
    .energy-profile-grid { grid-template-columns:1fr; }
}
</style>

<div class="facility-show-page facility-show-page-container">
<div class="facility-show-shell">

<!-- BACK BUTTON -->
<a href="{{ route('modules.facilities.index') }}" class="facility-back-btn">
<i class="fa-solid fa-arrow-left" style="margin-right:6px;"></i> Back
</a>


@php
$imageUrl = $facility->resolved_image_url;
$profile = $facility->energyProfiles()->with('primaryMeter')->latest()->first();
$facilityMeters = $facility->meters()->get();
$mainMeters = $facilityMeters->where('meter_type', 'main')->values();
$subMeters = $facilityMeters->where('meter_type', 'sub')->values();
$activeApprovedMainMeters = $mainMeters->filter(fn ($meter) => !empty($meter->approved_at) && strtolower((string) $meter->status) === 'active');
$allMeters = $mainMeters->concat($subMeters);
$approvedMeterCount = $allMeters->filter(fn ($meter) => !empty($meter->approved_at))->count();
$totalMeterCount = $allMeters->count();
$meterApprovalPercent = $totalMeterCount > 0 ? (int) round(($approvedMeterCount / $totalMeterCount) * 100) : 0;
$primaryMainMeter = ($profile?->primaryMeter && !empty($profile->primaryMeter->approved_at)) ? $profile->primaryMeter : null;
@endphp

<!-- HEADER -->
<div class="facility-header">
@if($imageUrl)
<img src="{{ $imageUrl }}" class="facility-hero-image" alt="{{ $facility->name }} facility image">
@else
<div class="facility-hero-image-placeholder">
<i class="fa-solid fa-image"></i>
<span class="facility-image-empty-label">No facility image</span>
</div>
@endif

<div class="facility-header-main">
<h1 class="facility-title">
	{{ $facility->name }}
</h1>
<div class="facility-subtitle">
	{{ $facility->type }} &bull; {{ $facility->department }}
</div>
@if($facility->isCprfManaged())
<span title="Synced from the CPRF Facilities Reservation System — identity details are read-only here"
	  style="display:inline-flex; align-items:center; gap:6px; background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; border-radius:999px; padding:4px 12px; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.03em; margin-top:6px;">
	<i class="fa-solid fa-link"></i> Public Facility — managed by CPRF
</span>
@elseif(\App\Support\RoleAccess::can(auth()->user(), 'manage_facility_master'))
<button type="button" onclick="openEditFacilityModal()" class="facility-edit-btn">
	<i class="fa-solid fa-pen-to-square" style="margin-right:6px;"></i> Edit Facility
</button>
@endif

@php
	$statusClass = $facility->status === 'active' ? 'status-active' : ($facility->status === 'maintenance' ? 'status-maintenance' : 'status-inactive');
@endphp
<div class="facility-pill-row">
<span class="facility-pill {{ $statusClass }}">
<i class="fa-solid fa-circle" style="font-size:.45rem;"></i> {{ ucfirst($facility->status) }}
</span>

<span class="facility-pill main">
<i class="fa-solid fa-gauge-high"></i> Main Meters: {{ $mainMeters->count() }}
</span>

<span class="facility-pill sub">
<i class="fa-solid fa-network-wired"></i> Sub-meters: {{ $subMeters->count() }}
</span>

<span class="facility-pill approved">
<i class="fa-solid fa-circle-check"></i> Approved: {{ $approvedMeterCount }} / {{ $totalMeterCount }}
</span>

@if($primaryMainMeter)
<span class="facility-pill primary"><i class="fa-solid fa-star"></i> Primary: {{ $primaryMainMeter->meter_name }}</span>
@endif

</div>

<div class="meter-readiness">
    <div class="meter-readiness-head">
        <span>Meter approval readiness</span>
        <span>{{ $meterApprovalPercent }}%</span>
    </div>
    <div class="meter-readiness-track" role="progressbar" aria-label="Meter approval readiness" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $meterApprovalPercent }}">
        <div class="meter-readiness-bar" style="width:{{ $meterApprovalPercent }}%;"></div>
    </div>
</div>
</div>
</div>


<!-- DETAILS GRID -->
<div class="facility-info-grid">

@php
// Facility size is based on total approved + active MAIN meter baseline.
$baselineForSize = (float) $activeApprovedMainMeters->sum(function ($meter) {
	return (is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0)
		? (float) $meter->baseline_kwh
		: 0;
});
$sizeLabel = '-';

if ($baselineForSize <= 0) {
	$baselineForSize = null;
}

if ($baselineForSize !== null) {
	$sizeLabel = \App\Models\Facility::resolveSizeLabelFromBaseline($baselineForSize) ?? '-';
}
@endphp

@foreach([
	['<i class="fa-solid fa-location-dot"></i>','Address',$facility->address],
	['<i class="fa-solid fa-map-location-dot"></i>','Barangay',$facility->barangay],
	['<i class="fa-solid fa-maximize"></i>','Floor Area',is_numeric($facility->floor_area) ? number_format((float) $facility->floor_area, 2).' sqm' : '-'],
	['<i class="fa-solid fa-building"></i>','Floors',$facility->floors],
	['<i class="fa-solid fa-calendar-days"></i>','Year Built',$facility->year_built],
	['<i class="fa-solid fa-clock"></i>','Operating Hours',$facility->operating_hours],
	['<i class="fa-solid fa-chart-simple"></i>','Facility Size',$sizeLabel]
] as $info)
	<div class="facility-info-card">
		<div class="facility-info-icon">
			{!! $info[0] !!}
		</div>
		<div>
			<div class="facility-info-label">{{ $info[1] }}</div>
			<div class="facility-info-value">{{ $info[2] ?: '-' }}</div>
		</div>
	</div>
@endforeach
</div>


<!-- ENERGY PROFILE DETAILS -->
<div class="energy-profile-details-card facility-section-card">
	<div class="facility-section-header">
		<div class="facility-section-heading">
			<span class="facility-section-heading-icon"><i class="fa-solid fa-id-card"></i></span>
			<div><h3>Energy Profile Details</h3><p>Utility account, baseline, and facility power configuration.</p></div>
		</div>
		<a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="facility-section-link"><i class="fa-solid fa-pen-to-square"></i> Manage Profile</a>
	</div>
	@if($profile)
		<div class="energy-profile-grid">
			@foreach([
				['Electric Meter No.', $profile->electric_meter_no ?? '-'],
				['Utility Provider', $profile->utility_provider ?? '-'],
				['Contract Account No.', $profile->contract_account_no ?? '-'],
				['Profile Baseline', is_numeric($profile->baseline_kwh) ? number_format((float) $profile->baseline_kwh, 2).' kWh' : '-'],
				['Main Energy Source', $profile->main_energy_source ?? '-'],
				['Backup Power', $profile->backup_power ?? '-'],
				['Transformer Capacity', $profile->transformer_capacity ?? '-'],
				['Meters Declared', $profile->number_of_meters ?? '-'],
				['Meters Registered', $totalMeterCount],
				['Baseline Source', $profile->baseline_source ?? '-'],
			] as $profileInfo)
				<div class="energy-profile-item"><span class="energy-profile-label">{{ $profileInfo[0] }}</span><span class="energy-profile-value">{{ $profileInfo[1] }}</span></div>
			@endforeach
			@if($profile->bill_image)
				<div class="energy-profile-item"><span class="energy-profile-label">Reference Bill</span><a href="{{ asset('storage/'.$profile->bill_image) }}" target="_blank" rel="noopener" class="facility-section-link" style="margin-top:7px;"><i class="fa-solid fa-image"></i> View Bill</a></div>
			@endif
		</div>
	@else
		<div class="energy-profile-empty energy-profile-empty-state"><i class="fa-solid fa-bolt"></i><strong>No energy profile yet</strong><span>Add utility and baseline details to enable complete monitoring.</span></div>
	@endif
</div>

<!-- ENERGY SUMMARY -->
@php
	$baselineKwh = (float) $activeApprovedMainMeters->sum(function ($meter) {
		return (is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0)
			? (float) $meter->baseline_kwh
			: 0;
	});
	$baselineSource = 'Total Main Meter Baseline';

	$hasBaseline = $baselineKwh > 0;
@endphp

<div class="energy-performance-card facility-section-card">
	<div class="facility-section-header">
		<div class="facility-section-heading">
			<span class="facility-section-heading-icon"><i class="fa-solid fa-bolt"></i></span>
			<div><h3>Energy Performance</h3><p>Current monitoring baseline from approved active main meters.</p></div>
		</div>
		<a href="{{ route('facilities.monthly-records', ['facility' => $facility->id, 'record_scope' => 'main']) }}" class="facility-section-link"><i class="fa-solid fa-chart-line"></i> View Records</a>
	</div>
	@if($hasBaseline)
		<div class="energy-performance-layout">
			<div><div class="energy-performance-value">{{ number_format($baselineKwh,2) }} kWh</div><div class="energy-performance-caption">Combined baseline · {{ $baselineSource }}</div></div>
			<span class="energy-performance-badge"><i class="fa-solid fa-circle-check"></i> Baseline ready</span>
		</div>
	@else
		<div class="energy-profile-empty-state energy-warning"><i class="fa-solid fa-triangle-exclamation"></i><strong>Baseline is not ready</strong><span>Add a baseline to an approved, active Main Meter to enable comparisons and alerts.</span></div>
	@endif
</div>


<!-- ACTIONS -->
<div class="facility-action-row">
	<span class="facility-actions-title"><i class="fa-solid fa-wand-magic-sparkles" style="margin-right:6px;color:#2563eb;"></i> Quick Actions</span>
	<!-- Edit Facility button removed -->
	<a href="{{ route('facilities.monthly-records', ['facility' => $facility->id, 'record_scope' => 'main']) }}" class="facility-action-link records">
		<i class="fa-solid fa-chart-line"></i> Monthly Records
	</a>
	<a href="{{ route('facilities.monthly-records.submeters', $facility->id) }}" class="facility-action-link submeters">
		<i class="fa-solid fa-network-wired"></i> Sub-meter Records
	</a>
	<a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="facility-action-link profile">
		<i class="fa-solid fa-bolt"></i> Energy Profile
	</a>
	@if(!$facility->isCprfManaged() && !in_array((auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')))), ['staff', 'energy_officer'], true))
	<button type="button" onclick="openDeleteFacilityModal({{ $facility->id }}, '{{ route('facilities.destroy', $facility->id) }}')" class="facility-action-btn archive"><i class="fa-solid fa-box-archive"></i> Move to Archive</button>
	@endif
</div>

</div>
</div>




@endsection
@include('modules.facilities.partials.modals')

<script>
function openEditFacilityModal() {
	var facility = @json($facility);
	document.getElementById('edit_facility_id').value = facility.id || '';
	document.getElementById('edit_name').value = facility.name || '';
	document.getElementById('edit_type').value = facility.type || '';
	document.getElementById('edit_department').value = facility.department || '';
	document.getElementById('edit_address').value = facility.address || '';
	document.getElementById('edit_barangay').value = facility.barangay || '';
	document.getElementById('edit_floor_area').value = facility.floor_area || '';
	document.getElementById('edit_floors').value = facility.floors || '';
	document.getElementById('edit_year_built').value = facility.year_built || '';
	document.getElementById('edit_operating_hours').value = facility.operating_hours || '';
	var normalizedStatus = String(facility.status || 'active').trim().toLowerCase();
	document.getElementById('edit_status').value = ['active', 'inactive', 'maintenance'].includes(normalizedStatus)
		? normalizedStatus
		: 'active';
	// Set form action dynamically
	var updateUrl = @json(route('facilities.update', ['id' => '__FACILITY_ID__']));
	document.getElementById('editFacilityForm').action = updateUrl.replace('__FACILITY_ID__', facility.id);
	// Image preview
	var preview = document.getElementById('edit_image_preview');
	var imagePath = facility.image_path || facility.image || '';
	if (imagePath) {
		let imageUrl = '';
		if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
			imageUrl = imagePath;
		} else if (imagePath.startsWith('img/') || imagePath.startsWith('uploads/') || imagePath.startsWith('storage/')) {
			imageUrl = '/' + imagePath;
		} else {
			imageUrl = '/storage/' + imagePath;
		}
		preview.innerHTML = '<img src="' + imageUrl + '" style="max-width:100%;max-height:120px;border-radius:10px;">';
	} else {
		preview.innerHTML = '<div style="width:100%;height:80px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;"><i class="fa-solid fa-image"></i></div>';
	}
	document.getElementById('editFacilityModal').style.display = 'flex';
	var editForm = document.getElementById('editFacilityForm');
	if (editForm) editForm.scrollTop = 0;
	window.requestAnimationFrame(function () {
		document.getElementById('edit_name')?.focus();
	});
}

document.addEventListener('DOMContentLoaded', function () {
	var imageInput = document.getElementById('edit_image');
	var imagePreview = document.getElementById('edit_image_preview');
	var imageError = document.getElementById('edit_image_error');
	var allowedExtensions = @json($facilityAllowedImageTypes);
	var maxImageBytes = @json($facilityImageMaxMb) * 1024 * 1024;

	if (imageInput) {
		imageInput.addEventListener('change', function () {
			var file = this.files && this.files[0] ? this.files[0] : null;
			if (!file) return;

			var extension = String(file.name || '').split('.').pop().toLowerCase();
			if (!allowedExtensions.includes(extension)) {
				this.value = '';
				if (imageError) {
					imageError.textContent = 'Unsupported image format. Use: ' + allowedExtensions.join(', ').toUpperCase() + '.';
					imageError.style.display = 'block';
				}
				return;
			}

			if (file.size > maxImageBytes) {
				this.value = '';
				if (imageError) {
					imageError.textContent = 'The selected image is larger than ' + @json($facilityImageMaxMb) + ' MB.';
					imageError.style.display = 'block';
				}
				return;
			}

			if (imageError) {
				imageError.textContent = '';
				imageError.style.display = 'none';
			}

			var reader = new FileReader();
			reader.onload = function (event) {
				if (imagePreview) {
					imagePreview.innerHTML = '<img src="' + event.target.result + '" alt="Selected facility image" style="max-width:100%;max-height:160px;border-radius:10px;object-fit:cover;">';
				}
			};
			reader.readAsDataURL(file);
		});
	}

	@if($errors->has('image'))
		openEditFacilityModal();
	@endif
});
</script>
