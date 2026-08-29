@extends('layouts.qc-admin')
@section('title', 'My Profile')

@php
    $user = auth()->user();
    $profileDisplayName = $user?->full_name ?? $user?->name ?? $user?->username ?? 'User';
    $profileInitials = collect(preg_split('/\s+/', trim((string) $profileDisplayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr((string) $part, 0, 1)))
        ->implode('');

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
        <div class="profile-avatar-wrap">
            <img src="{{ $user?->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="{{ $profileDisplayName }}" class="profile-avatar" onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
            <span class="profile-avatar-fallback" hidden>{{ $profileInitials ?: 'U' }}</span>
        </div>
        <div class="profile-header-main">
            <div class="profile-eyebrow"><i class="fa-solid fa-id-card"></i> Account profile</div>
            <h1>{{ $profileDisplayName }}</h1>
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
            <h3><span class="profile-section-icon"><i class="fa-solid fa-user"></i></span> Basic Information</h3>
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
            <h3><span class="profile-section-icon"><i class="fa-solid fa-shield-halved"></i></span> Account and Security</h3>
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
            <h3><span class="profile-section-icon"><i class="fa-solid fa-key"></i></span> System Permissions</h3>
            <div class="permission-list">
                @foreach($permissionItems as $item)
                    <div><i class="fa {{ $item['icon'] }}"></i> {{ $item['text'] }}</div>
                @endforeach
            </div>
        </section>

        <section class="profile-card">
            <h3><span class="profile-section-icon"><i class="fa-solid fa-layer-group"></i></span> Assignments</h3>
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
    background: linear-gradient(155deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #dce6f2;
    border-radius: 22px;
    box-shadow: 0 16px 38px rgba(15, 23, 42, 0.07);
    padding: 24px;
    margin-bottom: 2rem;
    font-family: 'Inter', sans-serif;
}

.report-card-container.profile-report-card-container,
.report-card-container.profile-report-card-container * {
    box-sizing: border-box;
}

.profile-view-page {
    max-width: 1180px;
    margin: 0 auto;
}

.profile-header-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 18px;
    min-height: 170px;
    padding: 22px 24px;
    overflow: hidden;
    border: 1px solid #d7e3f2;
    border-radius: 18px;
    background:
        radial-gradient(circle at 92% 0%, rgba(37, 99, 235, .13), transparent 32%),
        linear-gradient(135deg, #ffffff, #f7faff);
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
    margin-bottom: 16px;
}

.profile-header-card::before {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    height: 4px;
    background: linear-gradient(90deg, #1d4ed8, #60a5fa);
    content: '';
}

.profile-avatar-wrap {
    position: relative;
    width: 84px;
    height: 84px;
    flex: 0 0 84px;
    overflow: hidden;
    border-radius: 50%;
}

.profile-avatar {
    position: absolute;
    inset: 2px;
    display: block;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    outline: 2px solid #bfdbfe;
    background: #eef4ff;
    box-shadow: 0 8px 20px rgba(30, 64, 175, .15);
}

.profile-avatar[hidden] {
    display: none !important;
}

.profile-avatar-fallback {
    position: absolute;
    inset: 2px;
    width: 80px;
    height: 80px;
    align-items: center;
    justify-content: center;
    border: 4px solid #fff;
    outline: 2px solid #bfdbfe;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #e0e7ff);
    color: #1e40af;
    font-size: 1.35rem;
    font-weight: 900;
    box-shadow: 0 8px 20px rgba(30, 64, 175, .15);
}

.profile-avatar-fallback:not([hidden]) {
    display: inline-flex;
}

.profile-header-main {
    flex: 1;
}

.profile-header-main h1 {
    margin: 0;
    font-size: clamp(1.45rem, 2vw, 1.75rem);
    line-height: 1.2;
    letter-spacing: -.035em;
    color: #0f172a;
}

.profile-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
    color: #2563eb;
    font-size: .66rem;
    font-weight: 900;
    letter-spacing: .11em;
    text-transform: uppercase;
}

.profile-header-main p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: .88rem;
    overflow-wrap: anywhere;
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
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 0.76rem;
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
    border-radius: 11px;
    padding: 10px 15px;
    color: #ffffff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 8px 18px rgba(37, 99, 235, .22);
    transition: transform .15s ease, box-shadow .15s ease;
}

.profile-edit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 22px rgba(37, 99, 235, .28);
}

.profile-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.profile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
}

.profile-card h3 {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 13px;
    font-size: .94rem;
    color: #0f172a;
}

.profile-section-icon {
    display: inline-flex;
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #eaf1ff;
    color: #2563eb;
    font-size: .74rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.info-grid > div {
    min-width: 0;
    padding: 10px 11px;
    border: 1px solid #edf2f7;
    border-radius: 10px;
    background: #f8fafc;
}

.info-grid label {
    display: block;
    font-size: 0.7rem;
    color: #64748b;
}

.info-grid strong {
    display: block;
    margin-top: 2px;
    font-size: 0.84rem;
    overflow-wrap: anywhere;
    color: #0f172a;
}

.permission-list {
    display: grid;
    gap: 6px;
    color: #334155;
    font-size: .82rem;
}

.permission-list > div {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid #edf2f7;
    border-radius: 9px;
    background: #f8fafc;
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
    padding: 11px;
    background: #fbfdff;
}

.stats-grid strong {
    display: block;
    color: #1d4ed8;
    font-size: .96rem;
}

.stats-grid span {
    font-size: 0.82rem;
    color: #64748b;
}

body.dark-mode .profile-header-card,
body.dark-mode .profile-card {
    background: linear-gradient(145deg, #0f172a, #111827);
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

body.dark-mode .profile-avatar-fallback {
    border-color: #172554;
    outline-color: #31538c;
    background: linear-gradient(135deg, #1e3a8a, #312e81);
    color: #dbeafe;
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

body.dark-mode .info-grid > div,
body.dark-mode .permission-list > div {
    border-color: #29384d;
    background: #111827;
}

body.dark-mode .profile-section-icon {
    background: #1e3a5f;
    color: #93c5fd;
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

    .profile-edit-btn {
        width: 100%;
        justify-content: center;
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
