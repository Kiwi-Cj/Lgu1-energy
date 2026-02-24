@extends('layouts.qc-admin')
@section('title', 'My Profile')

@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);

    $roleValue = $user?->role;
    $formatRoleText = static function ($value): string {
        $text = trim(str_replace(['_', '-'], ' ', (string) $value));
        return $text === '' ? 'User' : ucwords($text);
    };

    if (is_array($roleValue)) {
        $roleLabel = collect($roleValue)->filter()->map(fn ($item) => $formatRoleText($item))->join(', ');
    } else {
        $roleLabel = $formatRoleText($roleValue ?? 'User');
    }
    $roleKey = str_replace(' ', '_', strtolower((string) ($user?->role ?? 'user')));

    $statusLabel = ucfirst((string) ($user?->status ?? 'active'));
    $isActive = strtolower((string) ($user?->status ?? 'active')) === 'active';

    $formatDateValue = static function ($value): string {
        if (empty($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y h:i A');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $lastLogin = $formatDateValue($user?->last_login_at ?? $user?->last_login ?? null);

    $otpEnabledValue = $user?->otp_enabled ?? config('otp.enabled', true);
    if (is_string($otpEnabledValue)) {
        $otpEnabledValue = in_array(strtolower(trim($otpEnabledValue)), ['1', 'true', 'yes', 'on', 'enabled'], true);
    }
    $otpStatusLabel = (bool) $otpEnabledValue ? 'Enabled' : 'Disabled';

    $createdAtLabel = $formatDateValue($user?->created_at);
    $updatedAtLabel = $formatDateValue($user?->updated_at);

    $createdByRaw = $user?->created_by ?? null;
    $createdByLabel = 'N/A';
    if (is_numeric($createdByRaw)) {
        $creator = \App\Models\User::query()->select(['id', 'full_name', 'name'])->find((int) $createdByRaw);
        if ($creator) {
            $createdByLabel = $creator->full_name ?? $creator->name ?? ('User #' . $creator->id);
        } else {
            $createdByLabel = 'User #' . (int) $createdByRaw;
        }
    } elseif (is_string($createdByRaw) && trim($createdByRaw) !== '') {
        $createdByLabel = trim($createdByRaw);
    }

    $assignedFacilitiesCount = 0;
    try {
        if ($user && method_exists($user, 'facilities')) {
            $assignedFacilitiesCount = $user->relationLoaded('facilities')
                ? (int) $user->facilities->count()
                : (int) $user->facilities()->count();
        }
    } catch (\Throwable $e) {
        $assignedFacilitiesCount = 0;
    }

    $assignedFacilityNames = collect();
    try {
        if ($user && method_exists($user, 'facilities')) {
            $assignedFacilityNames = $user->relationLoaded('facilities')
                ? $user->facilities->pluck('name')->filter()->values()
                : $user->facilities()->pluck('facilities.name');
        }
    } catch (\Throwable $e) {
        $assignedFacilityNames = collect();
    }

    $basicFacilityLabel = in_array($roleKey, ['super_admin', 'admin'], true)
        ? 'Facility Scope'
        : (($assignedFacilitiesCount > 1) ? 'Assigned Facilities' : 'Assigned Facility');

    if (in_array($roleKey, ['super_admin', 'admin'], true)) {
        $basicFacilityValue = 'All Facilities';
    } elseif ($assignedFacilityNames->count() > 1) {
        $basicFacilityValue = $assignedFacilityNames->count() . ' facilities assigned';
    } elseif ($assignedFacilityNames->count() === 1) {
        $basicFacilityValue = (string) $assignedFacilityNames->first();
    } else {
        $basicFacilityValue = $user?->facility?->name ?? 'None';
    }

    $permissionItems = match ($roleKey) {
        'super_admin' => [
            ['icon' => 'fa-shield', 'text' => 'Full system access (all modules)'],
            ['icon' => 'fa-users', 'text' => 'Manage users and roles'],
            ['icon' => 'fa-building', 'text' => 'Create / edit / delete facilities'],
            ['icon' => 'fa-clipboard-list', 'text' => 'Manage energy profiles and records'],
            ['icon' => 'fa-chart-line', 'text' => 'Reports and analytics (PDF/Excel exports)'],
            ['icon' => 'fa-gear', 'text' => 'System settings and configuration'],
        ],
        'admin' => [
            ['icon' => 'fa-users', 'text' => 'Users module access (limited role visibility)'],
            ['icon' => 'fa-building', 'text' => 'Create / edit / delete facilities'],
            ['icon' => 'fa-clipboard-list', 'text' => 'Manage energy profiles and records'],
            ['icon' => 'fa-chart-bar', 'text' => 'Analytics / reports access'],
            ['icon' => 'fa-screwdriver-wrench', 'text' => 'Maintenance scheduling and updates'],
            ['icon' => 'fa-ban', 'text' => 'No system settings access'],
        ],
        'energy_officer' => [
            ['icon' => 'fa-eye', 'text' => 'View facilities and energy monitoring'],
            ['icon' => 'fa-id-card', 'text' => 'Add / edit energy profiles (auto-approved on create)'],
            ['icon' => 'fa-ban', 'text' => 'Cannot delete energy profiles'],
            ['icon' => 'fa-chart-line', 'text' => 'Reports and analytics access (PDF/Excel)'],
            ['icon' => 'fa-wrench', 'text' => 'Maintenance schedule/update (no Complete/archive)'],
            ['icon' => 'fa-ban', 'text' => 'No users / settings access'],
        ],
        'staff' => [
            ['icon' => 'fa-building', 'text' => 'Assigned facilities only'],
            ['icon' => 'fa-bolt', 'text' => 'Energy monitoring and analytics access'],
            ['icon' => 'fa-file-pdf-o', 'text' => 'Reports PDF export only (Excel blocked)'],
            ['icon' => 'fa-wrench', 'text' => 'Maintenance view only (actions restricted)'],
            ['icon' => 'fa-ban', 'text' => 'No facility master-data create/edit/delete'],
            ['icon' => 'fa-ban', 'text' => 'No users / settings access'],
        ],
        default => [
            ['icon' => 'fa-eye', 'text' => 'Standard authenticated access'],
            ['icon' => 'fa-user', 'text' => 'Profile and account management'],
        ],
    };

    $assignmentCards = match ($roleKey) {
        'super_admin' => [
            ['value' => 'All', 'label' => 'Facility Scope'],
            ['value' => 'Full', 'label' => 'Admin Control'],
            ['value' => 'All', 'label' => 'Reports Access'],
            ['value' => 'All', 'label' => 'System Modules'],
        ],
        'admin' => [
            ['value' => 'All', 'label' => 'Facility Scope'],
            ['value' => 'Users', 'label' => 'Admin Module'],
            ['value' => 'Full', 'label' => 'Reports Access'],
            ['value' => 'Restricted', 'label' => 'Settings Access'],
        ],
        'energy_officer' => [
            ['value' => $assignedFacilitiesCount > 0 ? $assignedFacilitiesCount : 'All', 'label' => 'Facility Scope'],
            ['value' => 'Yes', 'label' => 'Energy Profile Edit'],
            ['value' => 'Yes', 'label' => 'Reports Access'],
            ['value' => 'No', 'label' => 'Maintenance Complete'],
        ],
        'staff' => [
            ['value' => max($assignedFacilitiesCount, 1), 'label' => 'Assigned Facilities'],
            ['value' => 'PDF Only', 'label' => 'Report Export'],
            ['value' => 'No', 'label' => 'Facility Admin'],
            ['value' => 'Restricted', 'label' => 'Maintenance Actions'],
        ],
        default => [
            ['value' => $user?->facility?->name ?? 'None', 'label' => 'Facility'],
            ['value' => $user?->active_actions_count ?? 0, 'label' => 'Active Actions'],
            ['value' => $user?->open_incidents_count ?? 0, 'label' => 'Open Incidents'],
            ['value' => '-', 'label' => 'Scope'],
        ],
    };
@endphp

@section('content')
<div class="report-card-container profile-report-card-container">
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
                <div><label>{{ $basicFacilityLabel }}</label><strong>{{ $basicFacilityValue }}</strong></div>
                <div><label>Contact Number</label><strong>{{ $user?->contact_number ?? '-' }}</strong></div>
            </div>
        </section>

        <section class="profile-card">
            <h3>Account and Security</h3>
            <div class="info-grid">
                <div><label>Status</label><strong>{{ $statusLabel }}</strong></div>
                <div><label>Last Login</label><strong>{{ $lastLogin }}</strong></div>
                <div><label>OTP</label><strong>{{ $otpStatusLabel }}</strong></div>
                <div><label>Created At</label><strong>{{ $createdAtLabel }}</strong></div>
                <div><label>Updated At</label><strong>{{ $updatedAtLabel }}</strong></div>
                <div><label>Created By</label><strong>{{ $createdByLabel }}</strong></div>
            </div>
        </section>

        <section class="profile-card">
            <h3>System Permissions</h3>
            <div class="permission-list">
                @foreach($permissionItems as $item)
                    <div><i class="fa {{ $item['icon'] }}"></i> {{ $item['text'] }}</div>
                @endforeach
            </div>
        </section>

        <section class="profile-card">
            <h3>Assignments</h3>
            <div class="stats-grid">
                @foreach($assignmentCards as $card)
                    <div>
                        <strong>{{ $card['value'] }}</strong>
                        <span>{{ $card['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
</div>

<style>
.report-card-container.profile-report-card-container {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(31,38,135,0.06);
    padding: 30px;
    margin-bottom: 2rem;
    font-family: 'Inter', sans-serif;
}

.report-card-container.profile-report-card-container,
.report-card-container.profile-report-card-container * {
    box-sizing: border-box;
}

.profile-view-page {
    max-width: 1100px;
    margin: 0 auto;
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

body.dark-mode .profile-report-card-container {
    background: #0f172a;
    border: 1px solid #1f2937;
    box-shadow: 0 12px 28px rgba(2, 6, 23, 0.55);
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
    .report-card-container.profile-report-card-container {
        padding: 16px;
        border-radius: 16px;
    }

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
