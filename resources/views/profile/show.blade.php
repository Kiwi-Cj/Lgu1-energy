@extends('layouts.qc-admin')
@section('title', 'My Profile')

@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);

    $roleValue = $user?->role;
    if (is_array($roleValue)) {
        $roleLabel = collect($roleValue)->filter()->map(fn ($item) => ucfirst((string) $item))->join(', ');
    } else {
        $roleLabel = ucfirst((string) ($roleValue ?? 'User'));
    }

    $statusLabel = ucfirst((string) ($user?->status ?? 'active'));
    $isActive = strtolower((string) ($user?->status ?? 'active')) === 'active';

    $lastLogin = 'N/A';
    if (!empty($user?->last_login_at)) {
        try {
            $lastLogin = \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y h:i A');
        } catch (\Throwable $e) {
            $lastLogin = (string) $user->last_login_at;
        }
    }
@endphp

@section('content')
<div class="profile-view-page">
    <div class="profile-header-card">
        <img src="{{ $user?->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="Profile Photo" class="profile-avatar">
        <div class="profile-header-main">
            <h1>{{ $user?->full_name ?? $user?->name ?? 'User' }}</h1>
            <p>{{ $user?->email }}</p>
            <div class="profile-meta">
                <span class="role-pill">{{ $roleLabel }}</span>
                <span class="status-pill {{ $isActive ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-circle"></i> {{ $statusLabel }}
                </span>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="profile-edit-btn">
            <i class="fa-solid fa-pen"></i> Edit Profile
        </a>
    </div>

    <div class="profile-cards-grid">
        <section class="profile-card">
            <h3>Basic Information</h3>
            <div class="info-grid">
                <div><label>User ID</label><strong>{{ $user?->id }}</strong></div>
                <div><label>Username</label><strong>{{ $user?->username ?? '-' }}</strong></div>
                <div><label>Role</label><strong>{{ $roleLabel }}</strong></div>
                <div><label>Department</label><strong>{{ $user?->department ?? '-' }}</strong></div>
                <div><label>Assigned Facility</label><strong>{{ $user?->facility?->name ?? 'None' }}</strong></div>
                <div><label>Contact Number</label><strong>{{ $user?->contact_number ?? '-' }}</strong></div>
            </div>
        </section>

        <section class="profile-card">
            <h3>Account and Security</h3>
            <div class="info-grid">
                <div><label>Status</label><strong>{{ $statusLabel }}</strong></div>
                <div><label>Last Login</label><strong>{{ $lastLogin }}</strong></div>
                <div><label>OTP</label><strong>{{ !empty($user?->otp_enabled) ? 'Enabled' : 'Disabled' }}</strong></div>
                <div><label>Created At</label><strong>{{ optional($user?->created_at)->format('M d, Y h:i A') }}</strong></div>
                <div><label>Updated At</label><strong>{{ optional($user?->updated_at)->format('M d, Y h:i A') }}</strong></div>
                <div><label>Created By</label><strong>{{ $user?->created_by ?? 'System Admin' }}</strong></div>
            </div>
        </section>

        <section class="profile-card">
            <h3>System Permissions</h3>
            <div class="permission-list">
                <div><i class="fa fa-eye"></i> View Energy Records</div>
                <div><i class="fa fa-plus-circle"></i> {{ !empty($user?->can_create_actions) ? 'Yes' : 'No' }} - Create Energy Actions</div>
                <div><i class="fa fa-check-circle"></i> {{ !empty($user?->can_approve_actions) ? 'Yes' : 'No' }} - Approve Actions</div>
                <div><i class="fa fa-cogs"></i> {{ !empty($user?->is_admin) ? 'Yes' : 'No' }} - Admin Settings</div>
            </div>
        </section>

        <section class="profile-card">
            <h3>Assignments</h3>
            <div class="stats-grid">
                <div>
                    <strong>{{ $user?->facility?->name ?? 'None' }}</strong>
                    <span>Facilities</span>
                </div>
                <div>
                    <strong>{{ $user?->assigned_equipment_count ?? 0 }}</strong>
                    <span>Equipment</span>
                </div>
                <div>
                    <strong>{{ $user?->active_actions_count ?? 0 }}</strong>
                    <span>Active Actions</span>
                </div>
                <div>
                    <strong>{{ $user?->open_incidents_count ?? 0 }}</strong>
                    <span>Open Incidents</span>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
.profile-view-page {
    max-width: 1100px;
    margin: 26px auto 40px;
}

.profile-header-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
    margin-bottom: 18px;
}

.profile-avatar {
    width: 92px;
    height: 92px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #dbeafe;
}

.profile-header-main {
    flex: 1;
}

.profile-header-main h1 {
    margin: 0;
    font-size: 1.6rem;
    color: #0f172a;
}

.profile-header-main p {
    margin: 4px 0 0;
    color: #64748b;
}

.profile-meta {
    margin-top: 12px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.role-pill,
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.84rem;
    font-weight: 700;
}

.role-pill {
    background: #eff6ff;
    color: #1d4ed8;
}

.status-pill.is-active {
    background: #dcfce7;
    color: #166534;
}

.status-pill.is-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.profile-edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-weight: 700;
    border-radius: 12px;
    padding: 10px 16px;
    color: #ffffff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.profile-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.profile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px;
}

.profile-card h3 {
    margin: 0 0 12px;
    font-size: 1rem;
    color: #0f172a;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.info-grid label {
    display: block;
    font-size: 0.78rem;
    color: #64748b;
}

.info-grid strong {
    display: block;
    margin-top: 2px;
    font-size: 0.94rem;
    color: #0f172a;
}

.permission-list {
    display: grid;
    gap: 8px;
    color: #334155;
}

.permission-list i {
    width: 18px;
    color: #2563eb;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.stats-grid div {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
}

.stats-grid strong {
    display: block;
    color: #1d4ed8;
    font-size: 1.04rem;
}

.stats-grid span {
    font-size: 0.82rem;
    color: #64748b;
}

body.dark-mode .profile-header-card,
body.dark-mode .profile-card {
    background: #0f172a;
    border-color: #334155;
    box-shadow: none;
}

body.dark-mode .profile-avatar {
    border-color: #1e3a8a;
}

body.dark-mode .profile-header-main h1,
body.dark-mode .profile-card h3,
body.dark-mode .info-grid strong,
body.dark-mode .permission-list {
    color: #e2e8f0;
}

body.dark-mode .profile-header-main p,
body.dark-mode .info-grid label,
body.dark-mode .stats-grid span {
    color: #94a3b8;
}

body.dark-mode .role-pill {
    background: #1e3a8a;
    color: #dbeafe;
}

body.dark-mode .status-pill.is-active {
    background: #14532d;
    color: #dcfce7;
}

body.dark-mode .status-pill.is-inactive {
    background: #7f1d1d;
    color: #fee2e2;
}

body.dark-mode .stats-grid div {
    border-color: #334155;
    background: #111827;
}

body.dark-mode .stats-grid strong {
    color: #93c5fd;
}

@media (max-width: 900px) {
    .profile-header-card {
        flex-wrap: wrap;
    }

    .profile-cards-grid {
        grid-template-columns: 1fr;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
