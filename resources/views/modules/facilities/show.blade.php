
@extends('layouts.qc-admin')
@section('title','Facility Details')

@section('content')
@php
	// Ensure notifications and unreadNotifCount are available for the notification bell
	$user = auth()->user();
	$notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
	$unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif
@if(session('error'))
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
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
</style>

<div class="facility-show-page" style="width:100%;margin:40px 0;">
<div class="facility-show-shell" style="background:linear-gradient(135deg,#f8fafc,#eef2ff);border-radius:26px;padding:40px;box-shadow:0 12px 40px rgba(37,99,235,.18);position:relative;width:100%;">

<!-- BACK BUTTON -->
<a href="{{ route('modules.facilities.index') }}" style="
position:absolute;left:28px;top:-22px;
background:#fff;padding:10px 22px;
border-radius:14px;font-weight:800;
color:#2563eb;text-decoration:none;
box-shadow:0 4px 16px #2563eb33;">
<i class="fa fa-arrow-left" style="margin-right:6px;"></i> Back
</a>


@php
$imageUrl = $facility->resolved_image_url;
@endphp

<!-- HEADER -->
<div style="display:flex;gap:28px;align-items:center;margin-bottom:30px;">
@if($imageUrl)
<img src="{{ $imageUrl }}" style="width:160px;height:120px;border-radius:18px;
object-fit:cover;box-shadow:0 6px 20px rgba(0,0,0,.2);">
@else
<div style="width:160px;height:120px;border-radius:18px;
background:#e5e7eb;display:flex;
align-items:center;justify-content:center;
font-size:2.5rem;color:#9ca3af;">
<i class="fa fa-image"></i>
</div>
@endif

<div style="flex:1;">
<h1 style="margin:0;font-size:2.2rem;font-weight:900;color:#1e293b;">
	{{ $facility->name }}
</h1>
<div style="color:#6366f1;font-weight:700;margin-top:6px;">
	{{ $facility->type }} &bull; {{ $facility->department }}
</div>
@if(!in_array((auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')))), ['staff', 'energy_officer'], true))
<button type="button" onclick="openEditFacilityModal()" style="margin-top:18px;background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:8px;font-weight:600;font-size:1.05rem;cursor:pointer;">
	<i class="fa fa-edit" style="margin-right:6px;"></i> Edit Facility
</button>
@endif

<div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;">
<span style="padding:6px 18px;border-radius:999px;
font-weight:800;font-size:.9rem;
background:
{{ $facility->status=='active'?'#dcfce7':($facility->status=='maintenance'?'#fef3c7':'#fee2e2') }};
color:
{{ $facility->status=='active'?'#166534':($facility->status=='maintenance'?'#92400e':'#991b1b') }};">
{{ ucfirst($facility->status) }}
</span>

@if($facility->engineer_approved ?? false)
<span style="padding:6px 18px;border-radius:999px;
font-weight:800;font-size:.9rem;
background:#e0f2fe;color:#0369a1;">
<i class="fa fa-check" style="margin-right:6px;"></i> Engineer Approved
</span>
@else
<span style="padding:6px 18px;border-radius:999px;
font-weight:800;font-size:.9rem;
background:#f1f5f9;color:#64748b;">
<i class="fa fa-times" style="margin-right:6px;"></i> Not Approved
</span>
@endif
</div>
</div>
</div>


<!-- DETAILS GRID -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-top:24px;">

@php
// Facility size should be based on baseline kWh (Energy Profile baseline first, fallback to facility baseline).
$latestProfileForSize = $facility->energyProfiles()->latest()->first();
$baselineForSize = null;
$sizeLabel = '-';

if ($latestProfileForSize && is_numeric($latestProfileForSize->baseline_kwh) && (float) $latestProfileForSize->baseline_kwh > 0) {
	$baselineForSize = (float) $latestProfileForSize->baseline_kwh;
} elseif (is_numeric($facility->baseline_kwh) && (float) $facility->baseline_kwh > 0) {
	$baselineForSize = (float) $facility->baseline_kwh;
}

if ($baselineForSize !== null) {
	$sizeLabel = \App\Models\Facility::resolveSizeLabelFromBaseline($baselineForSize) ?? '-';
}
@endphp

@foreach([
	['<i class="fa fa-map-marker"></i>','Address',$facility->address],
	['<i class="fa fa-map"></i>','Barangay',$facility->barangay],
	['<i class="fa fa-expand"></i>','Floor Area',$facility->floor_area.' sqm'],
	['<i class="fa fa-building"></i>','Floors',$facility->floors],
	['<i class="fa fa-calendar"></i>','Year Built',$facility->year_built],
	['<i class="fa fa-clock-o"></i>','Operating Hours',$facility->operating_hours],
	['<i class="fa fa-bar-chart"></i>','Facility Size',$sizeLabel]
] as $info)
	<div style="background:#fff;padding:18px;border-radius:16px;display:flex;gap:14px;box-shadow:0 6px 18px rgba(0,0,0,.08);">
		<div style="width:44px;height:44px;border-radius:14px;background:#2563eb1a;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#2563eb;">
			{!! $info[0] !!}
		</div>
		<div>
			<div style="font-size:.85rem;color:#64748b;font-weight:700;">{{ $info[1] }}</div>
			<div style="font-size:1.05rem;font-weight:800;color:#1e293b;">{{ $info[2] ?: '-' }}</div>
		</div>
	</div>
@endforeach
</div>


<!-- ENERGY PROFILE DETAILS -->
@php
$profile = $facility->energyProfiles()->latest()->first();
@endphp
<div class="energy-profile-details-card" style="margin-top:32px;padding:26px 32px;border-radius:22px;background:linear-gradient(135deg,#f8fafc,#e0f2fe);box-shadow:0 8px 28px rgba(37,99,235,.10);">
	<h3 style="margin:0 0 18px;font-weight:900;color:#0ea5e9;font-size:1.18rem;display:flex;align-items:center;gap:8px;">
		<i class="fa fa-id-card"></i> Energy Profile Details
	</h3>
	@if($profile)
		@if(!$profile->engineer_approved)
			<div style="color:#64748b;font-weight:700;font-size:1.1rem;padding:18px 0;text-align:center;">
				<i class="fa fa-clock" style="color:#2563eb;font-size:1.5rem;margin-bottom:8px;"></i><br>
				Energy profile is pending approval.<br>
				Please wait for engineer approval before details are shown.
			</div>
		@else
			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
				<div><span style="color:#64748b;font-weight:700;">Electric Meter No:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->electric_meter_no ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Utility Provider:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->utility_provider ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Contract Account No:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->contract_account_no ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Baseline kWh:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->baseline_kwh ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Main Energy Source:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->main_energy_source ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Backup Power:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->backup_power ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Transformer Capacity:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->transformer_capacity ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Number of Meters:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->number_of_meters ?? '-' }}</span></div>
				<div><span style="color:#64748b;font-weight:700;">Engineer Approved:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->engineer_approved ? 'Yes' : 'No' }}</span></div>
				<div>
					<span style="color:#64748b;font-weight:700;">Baseline Locked:</span><br>
					@php
						$role = auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')));
					@endphp
					@if($role === 'staff')
						<span style="display:inline-flex;align-items-center;gap:6px;font-weight:800;color:#0ea5e9;background:#e0f2fe;padding:6px 14px;border-radius:999px;" title="Baseline is locked for staff and cannot be changed.">
							<i class="fa fa-lock"></i> Locked
						</span>
					@else
						<span style="display:inline-flex;align-items-center;gap:6px;font-weight:800;color:#64748b;background:#f1f5f9;padding:6px 14px;border-radius:999px;" title="Baseline is editable for admin and super admin.">
							<i class="fa fa-unlock"></i> Editable
						</span>
					@endif
				</div>
				<div><span style="color:#64748b;font-weight:700;">Baseline Source:</span><br><span style="font-weight:800;color:#1e293b;">{{ $profile->baseline_source ?? '-' }}</span></div>
				@if($profile->bill_image)
					<div><span style="color:#64748b;font-weight:700;">Bill Image:</span><br><img src="{{ asset('storage/'.$profile->bill_image) }}" alt="Bill Image" style="max-width:120px;border-radius:8px;box-shadow:0 2px 8px #2563eb22;"></div>
				@endif
			</div>
		@endif
	@else
		<div class="energy-profile-empty" style="color:#64748b;font-weight:700;">No energy profile data available for this facility.</div>
	@endif
</div>

<!-- ENERGY SUMMARY -->
@php
	$energyProfile = $facility->energyProfiles()->latest()->first();
	$baselineKwh = $energyProfile && is_numeric($energyProfile->baseline_kwh) ? floatval($energyProfile->baseline_kwh) : null;
	$baselineSource = $energyProfile && $energyProfile->baseline_source ? $energyProfile->baseline_source : 'Energy Profile';
	$hasBaseline = $baselineKwh > 0;
@endphp

@if($energyProfile && !$energyProfile->engineer_approved)
<div class="energy-performance-card" style="margin-top:32px;padding:26px;border-radius:22px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 30px rgba(37,99,235,.15);text-align:center;">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		<i class="fa fa-bolt" style="margin-right:6px;"></i> Energy Performance
	</h3>
	<div style="color:#64748b;font-weight:700;font-size:1.1rem;padding:18px 0;">
		<i class="fa fa-clock" style="color:#2563eb;font-size:1.5rem;margin-bottom:8px;"></i><br>
		Energy profile is pending approval.<br>
		Please wait for engineer approval before performance details are shown.
	</div>
</div>
@elseif($hasBaseline)
<div class="energy-performance-card" style="margin-top:32px;padding:26px;border-radius:22px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 30px rgba(37,99,235,.15);">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		<i class="fa fa-bolt" style="margin-right:6px;"></i> Energy Performance
	</h3>
	<div style="font-size:1.7rem;font-weight:900;color:#2563eb;">
		{{ number_format($baselineKwh,2) }} kWh
	</div>
	<div style="font-size:.9rem;color:#475569;">
		Baseline consumption ({{ $baselineSource }})
	</div>
</div>
@else
<div class="energy-performance-card" style="margin-top:32px;padding:26px;border-radius:22px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 30px rgba(37,99,235,.15);">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		<i class="fa fa-bolt" style="margin-right:6px;"></i> Energy Performance
	</h3>
	<div class="energy-warning" style="color:#b91c1c;font-weight:700;">
		<i class="fa fa-exclamation-triangle" style="margin-right:6px;"></i> Insufficient data (no baseline set in energy profile)
	</div>
</div>
@endif

@php
	$auditFilters = [
		'action' => trim((string) request('audit_action', '')),
		'date_from' => trim((string) request('audit_date_from', '')),
		'date_to' => trim((string) request('audit_date_to', '')),
		'user_id' => trim((string) request('audit_user_id', '')),
	];

	if ($auditFilters['date_from'] !== '' && $auditFilters['date_to'] !== '' && $auditFilters['date_from'] > $auditFilters['date_to']) {
		[$auditFilters['date_from'], $auditFilters['date_to']] = [$auditFilters['date_to'], $auditFilters['date_from']];
	}

	$auditActorOptions = \App\Models\FacilityAuditLog::with('actor')
		->where('facility_id', $facility->id)
		->whereNotNull('performed_by')
		->get()
		->map(function ($log) {
			return [
				'id' => (string) $log->performed_by,
				'label' => $log->actor?->full_name ?? $log->actor?->name ?? $log->actor?->username ?? ('User #' . $log->performed_by),
			];
		})
		->unique('id')
		->sortBy('label')
		->values();

	$facilityAuditLogsQuery = \App\Models\FacilityAuditLog::with('actor')
		->where('facility_id', $facility->id);

	if ($auditFilters['action'] !== '') {
		$facilityAuditLogsQuery->where('action', $auditFilters['action']);
	}
	if ($auditFilters['date_from'] !== '') {
		$facilityAuditLogsQuery->whereDate('created_at', '>=', $auditFilters['date_from']);
	}
	if ($auditFilters['date_to'] !== '') {
		$facilityAuditLogsQuery->whereDate('created_at', '<=', $auditFilters['date_to']);
	}
	if ($auditFilters['user_id'] !== '') {
		$facilityAuditLogsQuery->where('performed_by', $auditFilters['user_id']);
	}

	$facilityAuditLogs = $facilityAuditLogsQuery
		->latest()
		->paginate(8, ['*'], 'audit_page')
		->withQueryString();
@endphp
<div style="margin-top:32px;padding:26px;border-radius:22px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.08);">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		<i class="fa fa-clock-rotate-left" style="margin-right:6px;"></i> Facility Activity / Audit Trail
	</h3>
	<form method="GET" action="{{ url()->current() }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end;margin-bottom:14px;padding:12px;border:1px solid #e5e7eb;border-radius:14px;background:#fcfdff;">
		<div style="display:flex;flex-direction:column;gap:5px;min-width:180px;">
			<label for="audit_action" style="font-size:.82rem;font-weight:700;color:#475569;">Action</label>
			<select id="audit_action" name="audit_action" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;">
				<option value="">All Actions</option>
				<option value="archived" @selected($auditFilters['action'] === 'archived')>Archived</option>
				<option value="restored" @selected($auditFilters['action'] === 'restored')>Restored</option>
				<option value="permanently_deleted" @selected($auditFilters['action'] === 'permanently_deleted')>Permanently Deleted</option>
			</select>
		</div>
		<div style="display:flex;flex-direction:column;gap:5px;min-width:165px;">
			<label for="audit_date_from" style="font-size:.82rem;font-weight:700;color:#475569;">Date From</label>
			<input id="audit_date_from" type="date" name="audit_date_from" value="{{ $auditFilters['date_from'] }}" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;">
		</div>
		<div style="display:flex;flex-direction:column;gap:5px;min-width:165px;">
			<label for="audit_date_to" style="font-size:.82rem;font-weight:700;color:#475569;">Date To</label>
			<input id="audit_date_to" type="date" name="audit_date_to" value="{{ $auditFilters['date_to'] }}" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;">
		</div>
		<div style="display:flex;flex-direction:column;gap:5px;min-width:220px;flex:1 1 220px;">
			<label for="audit_user_id" style="font-size:.82rem;font-weight:700;color:#475569;">User</label>
			<select id="audit_user_id" name="audit_user_id" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;">
				<option value="">All Users</option>
				@foreach($auditActorOptions as $actorOpt)
					<option value="{{ $actorOpt['id'] }}" @selected($auditFilters['user_id'] === $actorOpt['id'])>{{ $actorOpt['label'] }}</option>
				@endforeach
			</select>
		</div>
		<div style="display:flex;gap:8px;align-items:center;">
			<button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:9px 14px;font-weight:700;">Filter</button>
			<a href="{{ url()->current() }}" style="background:#f1f5f9;color:#334155;border-radius:10px;padding:9px 14px;font-weight:700;text-decoration:none;">Reset</a>
		</div>
	</form>
	@if($facilityAuditLogs->isEmpty())
		<div style="color:#64748b;font-weight:600;">
			{{ array_filter($auditFilters) ? 'No audit trail entries match the selected filters.' : 'No audit trail entries yet for this facility.' }}
		</div>
	@else
		<div style="display:flex;flex-direction:column;gap:10px;">
			@foreach($facilityAuditLogs as $log)
				@php
					$actorName = $log->actor?->full_name ?? $log->actor?->name ?? $log->actor?->username ?? 'System/Unknown';
					$actionLabel = match((string) $log->action) {
						'archived' => 'Archived Facility',
						'restored' => 'Restored Facility',
						'permanently_deleted' => 'Permanently Deleted Facility',
						default => ucwords(str_replace('_', ' ', (string) $log->action)),
					};
					$chipBg = match((string) $log->action) {
						'archived' => '#fff1f2',
						'restored' => '#ecfdf5',
						'permanently_deleted' => '#fee2e2',
						default => '#eff6ff',
					};
					$chipColor = match((string) $log->action) {
						'archived' => '#be123c',
						'restored' => '#166534',
						'permanently_deleted' => '#991b1b',
						default => '#1d4ed8',
					};
				@endphp
				<div style="border:1px solid #e5e7eb;border-radius:14px;padding:12px 14px;background:#fcfdff;">
					<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
						<span style="display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:.78rem;font-weight:800;background:{{ $chipBg }};color:{{ $chipColor }};">
							{{ $actionLabel }}
						</span>
						<span style="font-weight:700;color:#1e293b;">{{ $actorName }}</span>
						<span style="color:#64748b;">{{ $log->created_at?->format('M d, Y h:i A') }}</span>
					</div>
					@if(!empty($log->reason))
						<div style="margin-top:8px;color:#334155;line-height:1.5;">
							<span style="font-weight:700;">Reason:</span> {{ $log->reason }}
						</div>
					@endif
				</div>
			@endforeach
		</div>
		@if($facilityAuditLogs->hasPages())
			<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
				<div style="color:#64748b;font-size:0.92rem;">
					Showing {{ $facilityAuditLogs->firstItem() }} to {{ $facilityAuditLogs->lastItem() }} of {{ $facilityAuditLogs->total() }} logs
				</div>
				<div style="display:flex;gap:8px;align-items:center;">
					@if($facilityAuditLogs->onFirstPage())
						<span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Previous</span>
					@else
						<a href="{{ $facilityAuditLogs->previousPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:700;">Previous</a>
					@endif
					<span style="padding:8px 12px;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;">
						Page {{ $facilityAuditLogs->currentPage() }} / {{ $facilityAuditLogs->lastPage() }}
					</span>
					@if($facilityAuditLogs->hasMorePages())
						<a href="{{ $facilityAuditLogs->nextPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:700;">Next</a>
					@else
						<span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Next</span>
					@endif
				</div>
			</div>
		@endif
	@endif
</div>

<!-- ACTIONS -->
<div style="display:flex;gap:14px;justify-content:flex-end;margin-top:30px;">
	<!-- Edit Facility button removed -->
	<a href="{{ route('modules.facilities.meters.index', $facility->id) }}" style="background:#2563eb;color:#fff;padding:12px 26px;border:none;border-radius:999px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;">
		<i class="fa fa-gauge-high" style="margin-right:6px;"></i> Meters
	</a>
	@if(!in_array((auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')))), ['staff', 'energy_officer'], true))
	<button type="button" onclick="openDeleteFacilityModal({{ $facility->id }}, '{{ route('facilities.destroy', $facility->id) }}')" style="background:#e11d48;color:#fff;padding:12px 26px;border:none;border-radius:999px;font-weight:800;cursor:pointer;"><i class="fa fa-box-archive" style="margin-right:6px;"></i> Archive</button>
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
	document.getElementById('edit_status').value = facility.status || '';
	// Set form action dynamically
	document.getElementById('editFacilityForm').action = '/facilities/' + facility.id;
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
		preview.innerHTML = '<div style="width:100%;height:80px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;"><i class="fa fa-image"></i></div>';
	}
	document.getElementById('editFacilityModal').style.display = 'flex';
}
</script>
