@extends('layouts.qc-admin')
@section('title', 'Energy Profile')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
	<h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">Energy Profile</h2>
	<a href="{{ url('/modules/facilities/' . ($facilityModel->id ?? $facility->id ?? '') . '/energy-profile/create') }}" class="btn-add-energy-profile" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s;">+ Add Energy Profile</a>
</div>

@if(isset($facility))
	<div style="margin-bottom:1.2rem;">
		<strong>Facility:</strong> {{ $facility->name }}
	</div>
@endif

<div style="overflow-x:auto;">
	<table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(31,38,135,0.08);">
		<thead style="background:#f1f5f9;">
			<tr style="text-align:left;">
				<th style="padding:10px 14px;">Electric Meter No.</th>
				<th style="padding:10px 14px;">Utility Provider</th>
				<th style="padding:10px 14px;">Contract Account No.</th>
				<th style="padding:10px 14px;">Average Monthly kWh</th>
				<th style="padding:10px 14px;">Main Energy Source</th>
				<th style="padding:10px 14px;">Backup Power</th>
				<th style="padding:10px 14px;">Transformer Capacity</th>
				<th style="padding:10px 14px;">Number of Meters</th>
			</tr>
		</thead>
		<tbody>
		@forelse($energyProfiles as $profile)
			<tr>
				<td style="padding:10px 14px;">{{ $profile->electric_meter_no }}</td>
				<td style="padding:10px 14px;">{{ $profile->utility_provider }}</td>
				<td style="padding:10px 14px;">{{ $profile->contract_account_no }}</td>
				<td style="padding:10px 14px;">{{ $profile->average_monthly_kwh }}</td>
				<td style="padding:10px 14px;">{{ $profile->main_energy_source }}</td>
				<td style="padding:10px 14px;">{{ $profile->backup_power }}</td>
				<td style="padding:10px 14px;">{{ $profile->transformer_capacity ?? '-' }}</td>
				<td style="padding:10px 14px;">{{ $profile->number_of_meters }}</td>
			</tr>
		@empty
			<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:22px 0;">No energy profile data found.</td></tr>
		@endforelse
		</tbody>
	</table>
</div>
@endsection
