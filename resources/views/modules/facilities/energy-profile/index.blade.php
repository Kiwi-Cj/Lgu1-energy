@extends('layouts.qc-admin')
@section('title', 'Energy Profile')
@section('content')

@php
    $first3mo = isset($facilityModel) ? \DB::table('first3months_data')->where('facility_id', $facilityModel->id)->first() : null;
    $hasFirst3mo = $first3mo && $first3mo->month1 && $first3mo->month2 && $first3mo->month3;
    $avgKwh = null;
    if ($first3mo) {
        $avgKwh = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
    }
@endphp

<div style="max-width:1200px;margin:0 auto;">

    <!-- HEADER -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h2 style="font-size:2rem;font-weight:700;color:#222;">Energy Profile - {{ $facilityModel->name ?? '' }}</h2>
        <button type="button" class="btn-add-energy-profile" 
            style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:10px 28px;font-weight:600;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(31,38,135,0.1);font-size:1.05rem;transition:0.2s; @if(!$hasFirst3mo || $energyProfiles->count()) opacity:0.5; pointer-events:none; @endif"
            @if(!$hasFirst3mo) disabled title="You need first 3 months data before adding an energy profile." 
            @elseif($energyProfiles->count()) disabled @endif>+ Add Energy Profile</button>
    </div>

    @if(!$hasFirst3mo)
        <div style="color:#e11d48;font-weight:500;margin-bottom:1rem;">
            You need to enter first 3 months data before you can add an energy profile.
        </div>
    @endif

    <!-- SUMMARY CARD -->
    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-bottom:1.5rem;">
        <div style="flex:1 1 220px;background:#f0fdf4;padding:20px;border-radius:14px;text-align:center;">
            <div style="font-weight:600;color:#22c55e;">Average kWh (3 months)</div>
            <div style="font-size:2rem;font-weight:700;">{{ $avgKwh ? number_format($avgKwh,2) : '-' }}</div>
        </div>
        <div style="flex:1 1 220px;background:#fff0f3;padding:20px;border-radius:14px;text-align:center;">
            <div style="font-weight:600;color:#e11d48;">Profiles Count</div>
            <div style="font-size:2rem;font-weight:700;">{{ $energyProfiles->count() }}</div>
        </div>
    </div>

    <!-- ENERGY PROFILE TABLE -->
    <div style="overflow-x:auto;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(31,38,135,0.08);">
        <table style="width:100%;border-collapse:collapse;min-width:900px;">
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
                    <th style="padding:10px 14px;">Baseline Source</th>
                    <th style="padding:10px 14px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($energyProfiles as $profile)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:10px 14px;">{{ $profile->electric_meter_no }}</td>
                        <td style="padding:10px 14px;">{{ $profile->utility_provider }}</td>
                        <td style="padding:10px 14px;">{{ $profile->contract_account_no }}</td>
                        <td style="padding:10px 14px;">{{ $profile->baseline_kwh }}</td>
                        <td style="padding:10px 14px;">{{ $profile->main_energy_source }}</td>
                        <td style="padding:10px 14px;">{{ $profile->backup_power }}</td>
                        <td style="padding:10px 14px;">{{ $profile->transformer_capacity ?? '-' }}</td>
                        <td style="padding:10px 14px;">{{ $profile->number_of_meters }}</td>
                        <td style="padding:10px 14px;">{{ $profile->baseline_source ?? '-' }}</td>
                        <td style="padding:10px 14px;display:flex;gap:8px;align-items:center;position:relative;">
                            @php
                                $role = strtolower(auth()->user()->role ?? '');
                            @endphp
                            @if($role === 'engineer' || $role === 'super admin')
                                <form method="POST" action="{{ route('energy-profile.toggle-approval', ['facility' => $facilityModel->id, 'profile' => $profile->id]) }}" style="display:inline;position:relative;">
                                    @csrf
                                    <button type="submit" title="Toggle Engineer Approval" style="background:none;border:none;cursor:pointer;position:relative;">
                                        <span style="font-size:1.5rem;">
                                            @if($profile->engineer_approved)
                                                <i class="fa fa-check-circle" style="color:#22c55e;"></i>
                                            @else
                                                <i class="fa fa-times-circle" style="color:#e11d48;"></i>
                                            @endif
                                        </span>
                                        <span class="approval-tooltip" style="display:none;position:absolute;left:50%;top:-32px;transform:translateX(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;font-weight:500;white-space:nowrap;z-index:10;">@if($profile->engineer_approved) Approved @else Not Approved @endif</span>
                                    </button>
                                </form>
                            @else
                                <span style="font-size:1.5rem;position:relative;">
                                    @if($profile->engineer_approved)
                                        <i class="fa fa-check-circle" style="color:#22c55e;"></i>
                                    @else
                                        <i class="fa fa-times-circle" style="color:#e11d48;"></i>
                                    @endif
                                    <span class="approval-tooltip" style="display:none;position:absolute;left:50%;top:-32px;transform:translateX(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;font-weight:500;white-space:nowrap;z-index:10;">@if($profile->engineer_approved) Approved @else Not Approved @endif</span>
                                </span>
                            @endif
                            <button type="button" title="Delete" onclick="deleteEnergyProfile(this)" 
                                data-id="{{ $profile->id }}" 
                                style="background:none;border:none;color:#e11d48;font-size:1.2rem;cursor:pointer;position:relative;">
                                <i class="fa fa-trash"></i>
                                <span class="delete-tooltip" style="display:none;position:absolute;left:50%;top:-32px;transform:translateX(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;font-weight:500;white-space:nowrap;z-index:10;">Delete</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:22px 0;">No energy profile data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<!-- MODALS: Add / Edit / Delete -->
@include('modules.facilities.energy-profile.partials.modals') {{-- all modals in partial --}}
@include('modules.facilities.energy-profile.partials.delete-modal')

<script>
function editEnergyProfile(btn) {
    const modal = document.getElementById('editEnergyProfileModal');
    modal.classList.add('show-modal');
    modal.querySelector('#edit_energy_profile_id').value = btn.dataset.id;
    modal.querySelector('#edit_electric_meter_no').value = btn.dataset.electric_meter_no;
    modal.querySelector('#edit_utility_provider').value = btn.dataset.utility_provider;
    modal.querySelector('#edit_contract_account_no').value = btn.dataset.contract_account_no;
    modal.querySelector('#edit_baseline_kwh').value = btn.dataset.baseline_kwh;
    modal.querySelector('#edit_main_energy_source').value = btn.dataset.main_energy_source;
    modal.querySelector('#edit_backup_power').value = btn.dataset.backup_power;
    modal.querySelector('#edit_transformer_capacity').value = btn.dataset.transformer_capacity;
    modal.querySelector('#edit_number_of_meters').value = btn.dataset.number_of_meters;
}

function deleteEnergyProfile(btn) {
    const profileId = btn.dataset.id;
    if(!profileId || !confirm('Are you sure you want to delete this energy profile?')) return;
    fetch(`/modules/facilities/{{ $facilityModel->id ?? 'null' }}/energy-profile/${profileId}`, {
        method:'DELETE',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
    }).then(res=>res.json()).then(data=>{
        if(data.success) location.reload();
        else alert('Delete failed.');
    });
}

document.querySelector('.btn-add-energy-profile')?.addEventListener('click', function(){
    const modal = document.getElementById('addEnergyProfileModal');
    modal.classList.add('show-modal');
    modal.querySelector('#add_energy_facility_id').value = {{ $facilityModel->id ?? 'null' }};
});

function closeModal(modalId){document.getElementById(modalId).classList.remove('show-modal');}
</script>

@endsection
