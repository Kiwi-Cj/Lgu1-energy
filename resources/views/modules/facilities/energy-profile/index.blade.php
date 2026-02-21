@extends('layouts.qc-admin')
@section('title', 'Energy Profile')

@php
    // first3months_data table removed; fallback to baseline_kwh
    $avgKwh = isset($facilityModel) ? $facilityModel->baseline_kwh : null;
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
@endphp

<style>
    /* --- Shared UI Variables (Same as Energy Report) --- */
    :root {
        --report-bg: #ffffff;
        --report-text: #333333;
        --report-subtext: #555555;
        --card-shadow: rgba(31, 38, 135, 0.08);
        --table-header-bg: #e9effc;
        --table-row-even: #f8fafc;
        --table-border: #e5e7eb;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --report-bg: #1e293b;
            --report-text: #f1f5f9;
            --report-subtext: #94a3b8;
            --card-shadow: rgba(0, 0, 0, 0.4);
            --table-header-bg: #334155;
            --table-row-even: #1e293b;
            --table-border: #475569;
        }
    }

    .profile-card {
        background: var(--report-bg);
        border-radius: 18px;
        box-shadow: 0 4px 12px var(--card-shadow);
        margin-bottom: 1.2rem;
        padding: 24px;
        color: var(--report-text);
        transition: background 0.3s ease;
    }

    .profile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 20px;
    }

    .btn-action-main {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        color: #fff;
        background: linear-gradient(90deg,#2563eb,#6366f1);
        cursor: pointer;
        transition: 0.2s;
        text-wrap: nowrap;
    }

    .btn-action-main:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--table-border);
        border-radius: 12px;
    }

    .custom-table { width: 100%; border-collapse: collapse; min-width: 1000px; }
    .custom-table thead { background: var(--table-header-bg); }
    .custom-table th { 
        padding: 14px; 
        text-align: left; 
        color: #3762c8; 
        border-bottom: 2px solid var(--table-border);
        font-size: 0.9rem;
    }
    
    @media (prefers-color-scheme: dark) {
        .custom-table th { color: #60a5fa; }
    }

    .custom-table td { padding: 12px; border-bottom: 1px solid var(--table-border); font-size: 0.95rem; }
    .row-even { background: var(--table-row-even); }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .profile-card { padding: 15px; }
        .profile-header { flex-direction: column; text-align: center; }
        .btn-action-main { width: 100%; }
        .profile-header h2 { font-size: 1.5rem !important; }
    }

    /* Alerts */
    .alert-box {
        position: fixed; top: 32px; right: 32px; z-index: 99999; 
        min-width: 280px; max-width: 420px;
        padding: 16px 24px; border-radius: 12px; font-weight: 700;
        display: flex; align-items: center; gap: 10px;
    }

    body.dark-mode .energy-profile-page .profile-card {
        background: #0f172a !important;
        border: 1px solid #334155;
        box-shadow: 0 12px 28px rgba(2, 6, 23, 0.55);
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .table-responsive {
        border-color: #334155;
    }

    body.dark-mode .energy-profile-page .custom-table thead {
        background: #111827 !important;
    }

    body.dark-mode .energy-profile-page .custom-table th,
    body.dark-mode .energy-profile-page .custom-table td {
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .row-even {
        background: #111827 !important;
    }

    body.dark-mode .energy-profile-page [style*="color:#3762c8"],
    body.dark-mode .energy-profile-page [style*="color: #3762c8"],
    body.dark-mode .energy-profile-page [style*="color:#64748b"],
    body.dark-mode .energy-profile-page [style*="color: #64748b"] {
        color: #93c5fd !important;
    }
</style>

@section('content')
<div class="energy-profile-page" style="width:100%; margin:0 auto;">

    @if(session('success'))
    <div id="successAlert" class="alert-box" style="background:#dcfce7; color:#166534; box-shadow:0 2px 8px #16a34a22;">
        <i class="fa fa-check-circle" style="font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div id="errorAlert" class="alert-box" style="background:#fee2e2; color:#b91c1c; box-shadow:0 2px 8px #e11d4822;">
        <i class="fa fa-times-circle" style="font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="profile-card">
        <div class="profile-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:700; color:#3762c8; margin:0;">📋 Energy Profile</h2>
                <p style="color:var(--report-subtext); margin-top:4px;">{{ $facilityModel->name ?? 'Facility Details' }}</p>
            </div>
            @if($userRole !== 'energy_officer')
                <button type="button" class="btn-action-main btn-add-energy-profile" 
                    @if($energyProfiles->count()) disabled @endif>
                    <i class="fa fa-plus"></i> Add Energy Profile
                </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Meter No.</th>
                        <th>Utility Provider</th>
                        <th>Contract Account</th>
                        <th>Avg kWh</th>
                        <th>Main Source</th>
                        <th>Backup</th>
                        <th>Capacity</th>
                        <th>Meters</th>
                        <th>Baseline</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($energyProfiles as $profile)
                    <tr class="{{ $loop->even ? 'row-even' : '' }}">
                        <td>{{ $profile->electric_meter_no }}</td>
                        <td>{{ $profile->utility_provider }}</td>
                        <td>{{ $profile->contract_account_no }}</td>
                        <td>{{ number_format($profile->baseline_kwh, 2) }}</td>
                        <td>{{ $profile->main_energy_source }}</td>
                        <td>{{ $profile->backup_power }}</td>
                        <td>{{ $profile->transformer_capacity ?? '-' }}</td>
                        <td>{{ $profile->number_of_meters }}</td>
                        <td>{{ $profile->baseline_source ?? '-' }}</td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:12px; justify-content:center; align-items:center;">
                                @if($userRole === 'engineer' || $userRole === 'super admin')
                                    <form method="POST" action="{{ route('energy-profile.toggle-approval', ['facility' => $facilityModel->id, 'profile' => $profile->id]) }}">
                                        @csrf
                                        <button type="submit" style="background:none; border:none; cursor:pointer;">
                                            <i class="fa {{ $profile->engineer_approved ? 'fa-check-circle' : 'fa-times-circle' }}" 
                                               style="font-size:1.4rem; color:{{ $profile->engineer_approved ? '#22c55e' : '#e11d48' }};"></i>
                                        </button>
                                    </form>
                                @else
                                    <i class="fa {{ $profile->engineer_approved ? 'fa-check-circle' : 'fa-times-circle' }}" 
                                       style="font-size:1.4rem; color:{{ $profile->engineer_approved ? '#22c55e' : '#e11d48' }};"></i>
                                @endif

                                <button type="button" onclick="openDeleteEnergyProfileModal({{ $facilityModel->id }}, {{ $profile->id }})" 
                                    style="background:none; border:none; color:#e11d48; font-size:1.1rem; cursor:pointer;">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="padding:40px; text-align:center; color:var(--report-subtext);">No energy profile data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('modules.facilities.energy-profile.partials.modals')
@include('modules.facilities.energy-profile.partials.delete-modal')

<script>
    // Auto-hide alerts
    window.addEventListener('DOMContentLoaded', function() {
        const s = document.getElementById('successAlert');
        const e = document.getElementById('errorAlert');
        if (s) setTimeout(() => s.style.opacity = '0', 3000);
        if (e) setTimeout(() => e.style.opacity = '0', 3000);
    });

    document.querySelector('.btn-add-energy-profile')?.addEventListener('click', function(){
        const modal = document.getElementById('addEnergyProfileModal');
        modal.classList.add('show-modal');
        modal.querySelector('#add_energy_facility_id').value = "{{ $facilityModel->id ?? 'null' }}";
    });

    function closeModal(modalId){ document.getElementById(modalId).classList.remove('show-modal'); }
</script>
@endsection
