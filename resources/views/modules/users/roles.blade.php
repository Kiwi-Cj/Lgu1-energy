@extends('layouts.qc-admin')
@section('title', 'User Roles')

@section('content')
<style>
    .roles-page {
        max-width: 1200px;
        margin: 0 auto;
    }
    .roles-header {
        margin-bottom: 24px;
    }
    .roles-title {
        font-size: 2.1rem;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 4px;
    }
    .roles-subtitle {
        font-size: 1rem;
        color: #64748b;
    }
    .roles-meta {
        margin-top: 10px;
        font-size: 0.95rem;
        color: #475569;
        font-weight: 600;
    }
    .roles-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .roles-card {
        border-radius: 14px;
        padding: 20px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        background: #ffffff;
    }
    .roles-card.roles-card-purple { background: #f5f3ff; border-color: #ddd6fe; }
    .roles-card.roles-card-blue { background: #eff6ff; border-color: #bfdbfe; }
    .roles-card.roles-card-green { background: #f0fdf4; border-color: #bbf7d0; }
    .roles-card-label {
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
    }
    .roles-card-value {
        margin-top: 8px;
        font-size: 1.9rem;
        font-weight: 800;
        color: #0f172a;
    }
    .roles-actions {
        margin-bottom: 16px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .roles-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
        text-decoration: none;
        cursor: default;
    }
    .roles-table-wrap {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .roles-table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }
    .roles-table thead tr {
        background: #eef2ff;
    }
    .roles-table th {
        color: #334155;
        font-size: 0.82rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 12px 10px;
        border-bottom: 1px solid #dbeafe;
    }
    .roles-table td {
        padding: 14px 10px;
        border-top: 1px solid #edf2fb;
        color: #0f172a;
        vertical-align: middle;
    }
    .roles-table tbody tr:hover {
        background: #f8fbff;
    }
    .role-name-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 999px;
        color: #fff;
        font-size: 0.84rem;
        font-weight: 700;
    }
    .role-perm-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .role-desc {
        text-align: left;
    }
    .role-desc small {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-weight: 600;
    }
    .role-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        color: #64748b;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        cursor: default;
        margin: 0 3px;
    }
    .role-action.is-active {
        color: #2563eb;
        border-color: #bfdbfe;
        background: #eff6ff;
        cursor: pointer;
    }
    .role-action.is-active:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }
    .role-action.is-danger {
        color: #dc2626;
        border-color: #fecaca;
        background: #fef2f2;
    }
    .role-action.is-disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .roles-note {
        margin-top: 18px;
        color: #64748b;
        font-size: 0.9rem;
    }

    body.dark-mode .roles-title { color: #dbeafe; }
    body.dark-mode .roles-subtitle,
    body.dark-mode .roles-meta,
    body.dark-mode .roles-note { color: #94a3b8; }
    body.dark-mode .roles-card {
        background: #0f172a;
        border-color: #253043;
        box-shadow: 0 12px 26px rgba(2, 6, 23, 0.5);
    }
    body.dark-mode .roles-card.roles-card-purple { background: #1f1b3b; border-color: #4c1d95; }
    body.dark-mode .roles-card.roles-card-blue { background: #112038; border-color: #1d4ed8; }
    body.dark-mode .roles-card.roles-card-green { background: #0f2f2a; border-color: #14532d; }
    body.dark-mode .roles-card-label { color: #94a3b8; }
    body.dark-mode .roles-card-value { color: #f1f5f9; }
    body.dark-mode .roles-btn {
        background: #1e293b;
        border-color: #334155;
        color: #bfdbfe;
    }
    body.dark-mode .roles-table-wrap {
        background: #0f172a;
        border-color: #253043;
        box-shadow: 0 14px 26px rgba(2, 6, 23, 0.55);
    }
    body.dark-mode .roles-table thead tr { background: #172132; }
    body.dark-mode .roles-table th {
        color: #cbd5e1;
        border-bottom-color: #253043;
    }
    body.dark-mode .roles-table td {
        color: #e2e8f0;
        border-top-color: #1f2a3d;
    }
    body.dark-mode .roles-table tbody tr:hover { background: #132033; }
    body.dark-mode .role-perm-badge {
        background: #14532d;
        color: #dcfce7;
    }
    body.dark-mode .role-desc small { color: #94a3b8; }
    body.dark-mode .role-action {
        border-color: #334155;
        color: #94a3b8;
    }
    body.dark-mode .role-action.is-active {
        color: #bfdbfe;
        border-color: #334155;
        background: #1e293b;
    }
    body.dark-mode .role-action.is-active:hover {
        background: #334155;
    }
    body.dark-mode .role-action.is-danger {
        color: #fca5a5;
        border-color: #7f1d1d;
        background: #2b1014;
    }
</style>

<div class="roles-page">
    <div class="roles-header">
        <h1 class="roles-title">User Roles Management</h1>
        <div class="roles-subtitle">Define system roles and monitor assigned user distribution.</div>
        <div class="roles-meta">
            <span>Total Roles: {{ $totalRoles ?? 0 }}</span> |
            <span>Assigned Users: {{ $assignedUsers ?? 0 }}</span>
        </div>
    </div>

    <div class="roles-summary">
        <div class="roles-card roles-card-purple">
            <div class="roles-card-label">Total Roles</div>
            <div class="roles-card-value">{{ $totalRoles ?? 0 }}</div>
        </div>
        <div class="roles-card roles-card-blue">
            <div class="roles-card-label">Assigned Users</div>
            <div class="roles-card-value">{{ $assignedUsers ?? 0 }}</div>
        </div>
        <div class="roles-card roles-card-green">
            <div class="roles-card-label">Active Roles</div>
            <div class="roles-card-value">{{ $activeRoles ?? 0 }}</div>
        </div>
    </div>

    <div class="roles-actions">
        <span class="roles-btn"><i class="fa fa-lock"></i> Role Templates Synced</span>
    </div>

    <div class="roles-table-wrap">
        <table class="roles-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Role Name</th>
                    <th>Description</th>
                    <th style="width: 24%;">Permissions</th>
                    <th style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentUserRole = strtolower((string) (auth()->user()->role ?? ''));
                @endphp
                @forelse(($roles ?? []) as $roleItem)
                @php
                    $targetRoleKey = strtolower((string) ($roleItem['key'] ?? ''));
                    $assignedCount = (int) ($roleItem['assigned_users'] ?? 0);
                    $canViewUsers = $currentUserRole === 'super admin';
                    $canManageRole = $currentUserRole === 'super admin'
                        && $targetRoleKey !== 'super admin';
                    $canDeleteRole = $currentUserRole === 'super admin'
                        && !in_array($targetRoleKey, ['super admin', 'admin'], true)
                        && $assignedCount === 0;
                    $roleUsersUrl = route('users.index', ['role' => $targetRoleKey]);
                @endphp
                <tr>
                    <td>{{ $roleItem['id'] }}</td>
                    <td>
                        <span class="role-name-badge" style="background:{{ $roleItem['badge_color'] ?? '#6366f1' }};">
                            {{ $roleItem['name'] }}
                        </span>
                    </td>
                    <td class="role-desc">
                        <div>{{ $roleItem['description'] }}</div>
                        <small>
                            Assigned: {{ $roleItem['assigned_users'] ?? 0 }} |
                            Active: {{ $roleItem['active_users'] ?? 0 }} |
                            Inactive: {{ $roleItem['inactive_users'] ?? 0 }}
                        </small>
                    </td>
                    <td>
                        <span class="role-perm-badge">{{ $roleItem['permissions'] }}</span>
                    </td>
                    <td>
                        <a href="{{ $canViewUsers ? $roleUsersUrl : 'javascript:void(0)' }}"
                           class="role-action {{ $canViewUsers ? 'is-active' : 'is-disabled' }}"
                           title="{{ $canViewUsers ? 'View users under this role' : 'View users is available for Super Admin only' }}">
                            <i class="fa fa-users"></i>
                        </a>
                        <a href="{{ $canManageRole ? $roleUsersUrl : 'javascript:void(0)' }}"
                           class="role-action {{ $canManageRole ? 'is-active' : 'is-disabled' }}"
                           title="{{ $canManageRole ? 'Manage users for this role' : 'You do not have permission to manage this role' }}">
                            <i class="fa fa-pen"></i>
                        </a>
                        <a href="javascript:void(0)"
                           class="role-action is-danger {{ $canDeleteRole ? 'is-active' : 'is-disabled' }}"
                           title="{{ $canDeleteRole ? 'Delete role (available when no assigned users)' : 'Delete disabled: requires Super Admin and zero assigned users' }}">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No roles found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="roles-note">
        Note: Role records are derived from user role assignments. Editing role templates can be added as a next step.
    </div>
</div>
@endsection
