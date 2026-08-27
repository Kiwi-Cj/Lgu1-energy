@extends('layouts.qc-admin')
@section('title', 'User Roles')

@section('content')
@php
    $isSuperAdmin = (string) (auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')))) === 'super_admin';
    $roleTemplates = $userRoleTemplates ?? collect();
    $availablePermissions = $availablePermissions ?? collect();
@endphp

<style>
    .roles-page {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 22px;
        background: linear-gradient(155deg, #fff 0%, #f8fbff 100%);
        border: 1px solid #dce6f2;
        box-shadow: 0 16px 38px rgba(15,23,42,.07);
        border-radius: 22px;
    }
    .roles-hero {
        display: flex; justify-content: space-between; gap: 18px; flex-wrap: wrap;
        padding: 19px; border-radius: 18px; background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 55%, #eef2ff 100%);
        border: 1px solid #dbe6f5; box-shadow: 0 9px 24px rgba(37, 99, 235, .06); margin-bottom: 14px;
    }
    .roles-eyebrow { display:inline-flex; align-items:center; gap:7px; margin-bottom:6px; color:#2563eb; font-size:.65rem; font-weight:900; letter-spacing:.11em; text-transform:uppercase; }
    .roles-title { margin: 0; font-size: 1.7rem; line-height:1.15; letter-spacing:-.035em; font-weight: 900; color: #1e3a8a; }
    .roles-subtitle { margin-top: 6px; color: #475569; max-width: 70ch; font-size:.86rem; line-height:1.5; }
    .roles-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; width: 100%; margin-top: 10px; }
    .roles-stat { display:flex; align-items:center; gap:10px; min-height:68px; background: #fff; border: 1px solid #dbe6f5; border-radius: 13px; padding: 10px 12px; }
    .roles-stat-icon { display:inline-flex; width:34px; height:34px; flex:0 0 34px; align-items:center; justify-content:center; border-radius:10px; background:#eef2ff; color:#4f46e5; font-size:.8rem; }
    .roles-stat-copy { min-width:0; }
    .roles-stat-label { font-size: .65rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .045em; }
    .roles-stat-value { margin-top: 2px; font-size: 1.22rem; line-height:1.1; font-weight: 900; color: #0f172a; }
    .roles-grid { display: grid; grid-template-columns: minmax(300px, 340px) minmax(0, 1fr); gap: 13px; align-items: start; }
    .roles-panel {
        background: #fff; border: 1px solid #dbe6f5; border-radius: 16px; box-shadow: 0 8px 21px rgba(15,23,42,.05); padding: 16px;
    }
    .roles-panel-title { display:flex; align-items:center; gap:9px; margin-bottom:7px; }
    .roles-panel-title > span { display:inline-flex; width:31px; height:31px; align-items:center; justify-content:center; border-radius:9px; background:#eaf1ff; color:#2563eb; font-size:.72rem; }
    .roles-panel h2 { margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a; }
    .roles-panel p { margin: 0 0 11px; color: #64748b; font-size:.8rem; line-height:1.45; }
    .roles-form-grid { display: grid; gap: 10px; }
    .roles-field label { display:block; margin-bottom: 6px; font-size: .84rem; font-weight: 800; color: #334155; }
    .roles-field > input, .roles-field > textarea {
        width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 11px 12px; font: inherit; background: #fff;
    }
    .roles-field textarea { min-height: 68px; max-height:140px; resize: vertical; font-size:.8rem; line-height:1.45; }
    .permission-toolbar { display:grid; grid-template-columns:minmax(120px,1fr) auto; gap:6px; margin-bottom:7px; }
    .permission-search { width:100%; height:36px; border:1px solid #cbd5e1; border-radius:9px; padding:7px 9px; background:#fff; color:#0f172a; font:inherit; font-size:.72rem; }
    .permission-toolbar-actions { display:flex; gap:5px; }
    .permission-tool-btn { height:36px; padding:0 8px; border:1px solid #cbd5e1; border-radius:9px; background:#fff; color:#475569; font-size:.64rem; font-weight:800; white-space:nowrap; cursor:pointer; }
    .permission-tool-btn:hover { border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
    .permission-selection-meta { display:flex; align-items:center; justify-content:space-between; gap:8px; margin:0 1px 7px; color:#64748b; font-size:.67rem; }
    .permission-selection-count { display:inline-flex; align-items:center; min-height:24px; padding:3px 8px; border-radius:999px; background:#eaf1ff; color:#1d4ed8; font-size:.65rem; font-weight:900; white-space:nowrap; }
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 7px;
        max-height: 330px;
        overflow-y: auto;
        padding-right: 5px;
        scrollbar-color: #b9c5d6 transparent;
    }
    .perm-grid::-webkit-scrollbar { width:7px; }
    .perm-grid::-webkit-scrollbar-track { background:transparent; }
    .perm-grid::-webkit-scrollbar-thumb { border-radius:999px; background:#b8c5d6; }
    .perm-grid::-webkit-scrollbar-button { display:none; width:0; height:0; }
    .perm-item {
        border: 1px solid #dbe6f5;
        border-radius: 12px;
        min-height:64px;
        padding: 8px 9px;
        background: #f8fbff;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .perm-item.is-filtered-out { display:none; }
    .perm-item.is-selected { border-color:#93c5fd; background:#eff6ff; box-shadow:inset 3px 0 0 #2563eb; }
    .perm-item input[type="checkbox"] { width:16px; height:16px; flex:0 0 16px; margin: 3px 0 0; padding:0; accent-color:#2563eb; }
    .perm-item > div { min-width:0; }
    .perm-item label { margin: 0; cursor: pointer; font-size:.76rem; line-height:1.35; font-weight: 800; color: #1f2937; }
    .perm-item small { display: block; max-width:100%; margin-top: 4px; overflow:hidden; color: #64748b; font-size:.65rem; line-height:1.3; font-weight: 600; text-overflow:ellipsis; white-space:nowrap; }
    .roles-actions { display:flex; gap: 8px; margin-top: 7px; }
    .btn-primary, .btn-secondary {
        display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius: 12px; padding: 11px 15px; font-weight: 800; text-decoration:none; border:1px solid transparent;
    }
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-secondary { background:#eef2ff; color:#1d4ed8; border-color:#c7d2fe; }
    .roles-actions .btn-primary,
    .roles-actions .btn-secondary { flex:1 1 0; min-width:0; min-height:42px; padding:9px 8px; border-radius:10px; font-size:.74rem; line-height:1.15; white-space:nowrap; }
    .badge-color-control { display:grid; grid-template-columns:44px minmax(0,1fr); gap:8px; }
    .badge-color-control input[type="color"] { width:44px; height:42px; padding:4px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; cursor:pointer; }
    .badge-color-control input[type="color"]::-webkit-color-swatch-wrapper { padding:0; }
    .badge-color-control input[type="color"]::-webkit-color-swatch { border:0; border-radius:6px; }
    .badge-color-control input[type="color"]::-moz-color-swatch { border:0; border-radius:6px; }
    .badge-color-control input[type="text"] { width:100%; height:42px; padding:9px 11px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; color:#0f172a; font:inherit; }
    .role-list-heading-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:7px; }
    .role-list-heading-row .roles-panel-title { margin-bottom:0; }
    .role-list-search-wrap { position:relative; width:min(250px,45%); }
    .role-list-search-wrap i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none; }
    .role-list-search { width:100%; height:37px; padding:8px 10px 8px 32px; border:1px solid #cbd5e1; border-radius:9px; outline:none; background:#fff; color:#0f172a; font:inherit; font-size:.74rem; }
    .role-list-search:focus, .permission-search:focus { border-color:#60a5fa; box-shadow:0 0 0 3px rgba(59,130,246,.13); outline:none; }
    .role-search-empty { display:none; padding:28px 16px; text-align:center; color:#64748b; }
    .roles-table-wrap { overflow:auto; border-radius: 13px; border: 1px solid #dbe6f5; background: #fff; }
    .roles-table { width:100%; border-collapse: collapse; min-width: 760px; table-layout:fixed; }
    .roles-table th, .roles-table td { padding: 9px 9px; border-bottom: 1px solid #edf2fb; text-align: left; vertical-align: top; }
    .roles-table th { background: #eef2ff; color: #334155; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; white-space:nowrap; }
    .roles-table td { line-height: 1.38; font-size:.78rem; background:#fff; transition:background .15s; overflow-wrap:anywhere; }
    .roles-table tbody tr:hover td { background:#f8fbff; }
    .roles-table th:nth-child(4), .roles-table td:nth-child(4),
    .roles-table th:nth-child(5), .roles-table td:nth-child(5) { text-align:center; vertical-align:middle; }
    .roles-table th:last-child, .roles-table td:last-child { position:sticky; right:0; z-index:2; min-width:96px; box-shadow:-8px 0 16px rgba(15,23,42,.035); }
    .roles-table th:last-child { z-index:3; }
    .role-description-text { display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:3; overflow:hidden; color:#1f2937; }
    .role-user-breakdown { margin-top:5px; color:#64748b; font-size:.7rem; white-space:nowrap; }
    .role-identity-cell { display:flex; flex-direction:column; align-items:flex-start; gap:7px; }
    .role-type-inline { font-size:.66rem; }
    .role-badge { display:inline-flex; align-items:center; border-radius:999px; padding:5px 11px; color:#fff; font-weight:800; font-size:.8rem; line-height:1.2; }
    .role-perm {
        display:inline-flex;
        align-items:center;
        padding:5px 9px;
        border-radius:999px;
        background:#dcfce7;
        color:#166534;
        font-size:.74rem;
        font-weight:800;
        line-height:1.2;
        white-space: nowrap;
    }
    .role-pill { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; background:#f1f5f9; color:#475569; font-size:.76rem; font-weight:800; }
    .role-pill.system { background:#dbeafe; color:#1d4ed8; }
    .role-pill.custom { background:#f3e8ff; color:#7e22ce; }
    .role-actions-cell { white-space:nowrap; }
    .role-btn {
        display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:999px; border:1px solid #dbe6f5; color:#64748b; text-decoration:none; margin:0 2px;
    }
    .role-btn.delete { color:#dc2626; background:#fef2f2; border-color:#fecaca; }
    .role-btn.disabled { opacity:.45; pointer-events:none; }
    .roles-note { display:flex; align-items:flex-start; gap:8px; margin-top:10px; padding:10px; border:1px solid #dbe6f5; border-radius:10px; background:#f8fbff; color:#64748b; font-size:.72rem; line-height:1.45; }
    .roles-note i { flex:0 0 auto; margin-top:2px; color:#2563eb; }
    .role-perm-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .role-perm-more {
        display: inline-flex;
        align-items: center;
        margin-top: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .76rem;
        font-weight: 800;
        white-space: nowrap;
        cursor: pointer;
        border: 0;
    }
    .role-perm-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 80;
        background: rgba(15, 23, 42, .45);
        padding: 18px;
        align-items: center;
        justify-content: center;
    }
    .role-perm-modal.is-open { display: flex; }
    .role-perm-modal-card {
        width: min(720px, 100%);
        max-height: min(82vh, 760px);
        overflow: auto;
        background: #fff;
        border-radius: 22px;
        border: 1px solid #dbe6f5;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .2);
        padding: 18px;
    }
    .role-perm-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .role-perm-modal-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 900;
        color: #0f172a;
    }
    .role-perm-close {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        border: 1px solid #dbe6f5;
        background: #f8fbff;
        color: #475569;
        font-size: 1rem;
        font-weight: 900;
        cursor: pointer;
    }
    .role-perm-list { display: flex; flex-wrap: wrap; gap: 8px; }
    body.dark-mode .roles-page { background:linear-gradient(155deg,#0f172a,#111827); border-color:#334155; box-shadow:0 18px 38px rgba(2,6,23,.38); }
    body.dark-mode .roles-hero { background:linear-gradient(135deg,#14213a,#111827); border-color:#334155; box-shadow:none; }
    body.dark-mode .roles-title,
    body.dark-mode .roles-panel h2,
    body.dark-mode .roles-stat-value,
    body.dark-mode .perm-item label,
    body.dark-mode .role-perm-modal-title { color:#f8fafc; }
    body.dark-mode .roles-subtitle,
    body.dark-mode .roles-panel p,
    body.dark-mode .roles-stat-label,
    body.dark-mode .perm-item small,
    body.dark-mode .roles-note { color:#94a3b8; }
    body.dark-mode .roles-stat,
    body.dark-mode .roles-panel,
    body.dark-mode .roles-table-wrap,
    body.dark-mode .role-perm-modal-card { background:#0f172a; border-color:#334155; box-shadow:none; }
    body.dark-mode .roles-panel-title > span,
    body.dark-mode .roles-stat-icon { background:#1e3a5f !important; color:#93c5fd !important; }
    body.dark-mode .roles-field label { color:#cbd5e1; }
    body.dark-mode .roles-field > input,
    body.dark-mode .roles-field > textarea { background:#0b1220; border-color:#475569; color:#e2e8f0; }
    body.dark-mode .permission-search,
    body.dark-mode .permission-tool-btn,
    body.dark-mode .role-list-search,
    body.dark-mode .badge-color-control input[type="color"],
    body.dark-mode .badge-color-control input[type="text"] { background:#0b1220; border-color:#475569; color:#e2e8f0; }
    body.dark-mode .permission-selection-meta { color:#94a3b8; }
    body.dark-mode .permission-selection-count { color:#93c5fd; }
    body.dark-mode .perm-item { background:#111827; border-color:#334155; }
    body.dark-mode .perm-item.is-selected { border-color:#3b82f6; background:#172b4d; box-shadow:inset 3px 0 0 #60a5fa; }
    body.dark-mode .roles-note { background:#111827; border-color:#334155; }
    body.dark-mode .roles-table th { background:#172033; color:#cbd5e1; border-color:#334155; }
    body.dark-mode .roles-table td { background:#0f172a; color:#e2e8f0; border-color:#263449; }
    body.dark-mode .roles-table tbody tr:hover td { background:#1b293d; }
    body.dark-mode .role-description-text { color:#e2e8f0; }
    body.dark-mode .role-user-breakdown { color:#94a3b8; }
    body.dark-mode .roles-table th:last-child,
    body.dark-mode .roles-table td:last-child { box-shadow:-8px 0 16px rgba(2,6,23,.22); }
    body.dark-mode .role-perm-close { background:#111827; border-color:#475569; color:#cbd5e1; }
    @media (max-width: 1024px) {
        .roles-page { padding: 16px; border-radius: 18px; }
        .roles-grid { grid-template-columns: 1fr; }
        .roles-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .roles-page { padding: 12px; }
        .roles-hero { padding: 16px; border-radius: 18px; }
        .roles-title { font-size: 1.45rem; }
        .roles-stats { grid-template-columns: 1fr; }
        .roles-panel { padding: 16px; }
        .permission-toolbar { grid-template-columns:1fr; }
        .permission-toolbar-actions > * { flex:1; }
        .role-list-heading-row { align-items:stretch; flex-direction:column; }
        .role-list-search-wrap { width:100%; }
        .roles-actions { flex-direction: column; }
        .btn-primary, .btn-secondary { width: 100%; }
        .roles-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .roles-table {
            min-width: 760px;
        }
        .roles-table th, .roles-table td {
            padding: 8px 8px;
        }
        .role-perm-clamp {
            -webkit-line-clamp: 2;
        }
        .role-perm-modal-card {
            padding: 16px;
            max-height: 86vh;
        }
    }
    @media (min-width: 1025px) {
        .roles-panel.sticky-create {
            position: sticky;
            top: 92px;
        }
    }
</style>

<div class="roles-page">
    <div class="roles-hero">
        <div>
            <div class="roles-eyebrow"><i class="fa-solid fa-shield-halved"></i> Access control</div>
            <h1 class="roles-title">Roles Management</h1>
            <div class="roles-subtitle">Create custom roles and manage how many users are assigned to each role. System roles stay protected.</div>
        </div>
        <div class="roles-stats">
            <div class="roles-stat">
                <span class="roles-stat-icon"><i class="fa-solid fa-layer-group"></i></span>
                <div class="roles-stat-copy"><div class="roles-stat-label">Total Roles</div><div class="roles-stat-value">{{ $totalRoles ?? 0 }}</div></div>
            </div>
            <div class="roles-stat">
                <span class="roles-stat-icon" style="background:#ecfdf5;color:#16a34a;"><i class="fa-solid fa-users"></i></span>
                <div class="roles-stat-copy"><div class="roles-stat-label">Assigned Users</div><div class="roles-stat-value">{{ $assignedUsers ?? 0 }}</div></div>
            </div>
            <div class="roles-stat">
                <span class="roles-stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-circle-check"></i></span>
                <div class="roles-stat-copy"><div class="roles-stat-label">Active Roles</div><div class="roles-stat-value">{{ $activeRoles ?? 0 }}</div></div>
            </div>
            <div class="roles-stat">
                <span class="roles-stat-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                <div class="roles-stat-copy"><div class="roles-stat-label">Custom Roles</div><div class="roles-stat-value">{{ $customRoleCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom:14px;padding:12px 14px;border-radius:12px;background:#ecfdf5;color:#166534;font-weight:700;border:1px solid #bbf7d0;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:14px;padding:12px 14px;border-radius:12px;background:#fef2f2;color:#b91c1c;font-weight:700;border:1px solid #fecaca;">{{ session('error') }}</div>
    @endif

    <div class="roles-grid">
        <div class="roles-panel sticky-create">
            <div class="roles-panel-title"><span><i class="fa-solid fa-plus"></i></span><h2>Create Role</h2></div>
            <p>Use this for capstone demo roles like `auditor`, `viewer`, or `facility_manager`.</p>

            @if($isSuperAdmin)
                <form class="roles-form-grid" method="POST" action="{{ route('users.roles.store') }}">
                    @csrf
                    <div class="roles-field">
                        <label for="name">Role Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Auditor" required>
                        @error('name') <div style="margin-top:6px;color:#dc2626;font-size:.85rem;">{{ $message }}</div> @enderror
                    </div>
                    <div class="roles-field">
                        <label>Permissions</label>
                        <div class="permission-toolbar">
                            <input id="permissionSearch" class="permission-search" type="search" placeholder="Search permissions..." aria-label="Search permissions">
                            <div class="permission-toolbar-actions">
                                <button id="selectAllPermissions" class="permission-tool-btn" type="button">Select all</button>
                                <button id="clearPermissions" class="permission-tool-btn" type="button">Clear</button>
                            </div>
                        </div>
                        <div class="permission-selection-meta">
                            <span>Select only the access this role needs.</span>
                            <span id="permissionSelectionCount" class="permission-selection-count">0 selected</span>
                        </div>
                        <div class="perm-grid">
                            @foreach($availablePermissions as $permission)
                                @php
                                    $permKey = (string) ($permission['key'] ?? '');
                                    $permLabel = (string) ($permission['label'] ?? $permKey);
                                    $checked = in_array($permKey, old('permissions', []), true);
                                    $permRolesLabel = implode(', ', array_map(
                                        fn ($role) => ucwords(str_replace('_', ' ', (string) $role)),
                                        $permission['roles'] ?? []
                                    ));
                                @endphp
                                <div class="perm-item">
                                    <input id="perm_{{ $permKey }}" type="checkbox" name="permissions[]" value="{{ $permKey }}" @checked($checked)>
                                    <div>
                                        <label for="perm_{{ $permKey }}">{{ $permLabel }}</label>
                                        <small title="Default roles: {{ $permRolesLabel ?: 'None' }}">Default: {{ $permRolesLabel ?: 'None' }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('permissions') <div style="margin-top:6px;color:#dc2626;font-size:.85rem;">{{ $message }}</div> @enderror
                    </div>
                    <div class="roles-field">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Short description for the role">{{ old('description') }}</textarea>
                        @error('description') <div style="margin-top:6px;color:#dc2626;font-size:.85rem;">{{ $message }}</div> @enderror
                    </div>
                    <div class="roles-field">
                        <label for="badge_color">Badge Color</label>
                        <div class="badge-color-control">
                            <input id="badge_color_picker" type="color" value="{{ old('badge_color', '#6366f1') }}" aria-label="Choose badge color">
                            <input id="badge_color" type="text" name="badge_color" value="{{ old('badge_color', '#6366f1') }}" placeholder="#6366f1" maxlength="7">
                        </div>
                        @error('badge_color') <div style="margin-top:6px;color:#dc2626;font-size:.85rem;">{{ $message }}</div> @enderror
                    </div>
                    <div class="roles-actions">
                        <button class="btn-primary" type="submit"><i class="fa fa-plus"></i> Create Role</button>
                        <a class="btn-secondary" href="{{ route('users.index') }}"><i class="fa fa-users"></i> Back to Users</a>
                    </div>
                </form>
            @else
                <div style="padding:14px 0;color:#64748b;font-weight:700;">Only Super Admin can create or remove roles.</div>
                <a class="btn-secondary" href="{{ route('users.index') }}"><i class="fa fa-users"></i> Back to Users</a>
            @endif

            <div class="roles-note"><i class="fa-solid fa-circle-info"></i><span>System roles are protected. Assign custom roles from the Users page after creation.</span></div>
        </div>

        <div class="roles-panel roles-list-panel">
            <div class="role-list-heading-row">
                <div class="roles-panel-title"><span><i class="fa-solid fa-list-check"></i></span><h2>Role List</h2></div>
                <label class="role-list-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input id="roleListSearch" class="role-list-search" type="search" placeholder="Search roles..." aria-label="Search role list">
                </label>
            </div>
            <p>Current system and custom roles, plus user counts for each role.</p>
            <div class="roles-table-wrap">
                <table class="roles-table">
                    <colgroup>
                        <col style="width:145px;">
                        <col style="width:225px;">
                        <col style="width:290px;">
                        <col style="width:65px;">
                        <col style="width:96px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($roles ?? []) as $roleItem)
                            @php
                                $roleKey = str_replace([' ', '-'], '_', strtolower((string) ($roleItem['key'] ?? '')));
                                $isSystem = (bool) ($roleItem['is_system'] ?? false);
                                $canDelete = $isSuperAdmin && ! $isSystem && (int) ($roleItem['assigned_users'] ?? 0) === 0;
                            @endphp
                            <tr data-role-row>
                                <td>
                                    <div class="role-identity-cell">
                                        <span class="role-badge" style="background:{{ $roleItem['badge_color'] ?? '#6366f1' }};">{{ $roleItem['name'] }}</span>
                                        <span class="role-pill role-type-inline {{ $isSystem ? 'system' : 'custom' }}">{{ $isSystem ? 'System role' : 'Custom role' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="role-description-text" title="{{ $roleItem['description'] }}">{{ $roleItem['description'] }}</div>
                                    <div class="role-user-breakdown">Active: {{ $roleItem['active_users'] ?? 0 }} &middot; Inactive: {{ $roleItem['inactive_users'] ?? 0 }}</div>
                                </td>
                                <td>
                                    @php
                                        $perms = $roleItem['permission_labels'] ?? [];
                                        $permCount = count($perms);
                                        $showedPerms = array_slice($perms, 0, 3);
                                    @endphp
                                    @if(!empty($perms))
                                        <div class="role-perm-clamp" style="display:flex;flex-wrap:wrap;gap:5px;max-width:320px;">
                                            @foreach($showedPerms as $permLabel)
                                                <span class="role-perm">{{ $permLabel }}</span>
                                            @endforeach
                                        </div>
                                        @if($permCount > 3)
                                            <button type="button" class="role-perm-more" data-role-perms-open data-role-perms-role="{{ e($roleItem['name']) }}">
                                                +{{ $permCount - 3 }} more
                                            </button>
                                        @endif
                                    @else
                                        <span class="role-perm">None</span>
                                    @endif
                                </td>
                                <td>{{ $roleItem['assigned_users'] ?? 0 }}</td>
                                <td class="role-actions-cell">
                                    <a href="{{ route('users.index', ['role' => $roleKey]) }}" class="role-btn" title="View users"><i class="fa fa-users"></i></a>
                                    @if($canDelete)
                                        <form method="POST" action="{{ route('users.roles.destroy', $roleItem['id']) }}" style="display:inline;" onsubmit="return confirm('Delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="role-btn delete" title="Delete role" style="cursor:pointer;"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @else
                                        <span class="role-btn disabled" title="System roles and assigned roles cannot be deleted"><i class="fa fa-trash"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:20px;color:#64748b;">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div id="roleSearchEmpty" class="role-search-empty"><i class="fa-regular fa-folder-open"></i> No roles match your search.</div>
            </div>
        </div>
    </div>
</div>

@foreach(($roles ?? []) as $roleItem)
    @php
        $perms = $roleItem['permission_labels'] ?? [];
        $permCount = count($perms);
    @endphp
    @if($permCount > 3)
        <div class="role-perm-modal" data-role-perms-modal aria-hidden="true">
            <div class="role-perm-modal-card" role="dialog" aria-modal="true" aria-label="All permissions">
                <div class="role-perm-modal-head">
                    <div>
                        <h3 class="role-perm-modal-title">{{ $roleItem['name'] }} Permissions</h3>
                        <div style="margin-top:4px;color:#64748b;">All permissions for this role.</div>
                    </div>
                    <button type="button" class="role-perm-close" data-role-perms-close aria-label="Close permissions">×</button>
                </div>
                <div class="role-perm-list">
                    @foreach($perms as $permLabel)
                        <span class="role-perm">{{ $permLabel }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endforeach

<script>
    (function () {
        const openButtons = document.querySelectorAll('[data-role-perms-open]');
        const modals = document.querySelectorAll('[data-role-perms-modal]');
        const permissionSearch = document.getElementById('permissionSearch');
        const permissionItems = Array.from(document.querySelectorAll('.perm-item'));
        const permissionCheckboxes = Array.from(document.querySelectorAll('.perm-item input[type="checkbox"]'));
        const permissionCount = document.getElementById('permissionSelectionCount');
        const selectAllPermissions = document.getElementById('selectAllPermissions');
        const clearPermissions = document.getElementById('clearPermissions');
        const badgeColorPicker = document.getElementById('badge_color_picker');
        const badgeColorText = document.getElementById('badge_color');
        const roleListSearch = document.getElementById('roleListSearch');
        const roleRows = Array.from(document.querySelectorAll('[data-role-row]'));
        const roleSearchEmpty = document.getElementById('roleSearchEmpty');

        function updatePermissionCount() {
            permissionItems.forEach((item) => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                item.classList.toggle('is-selected', Boolean(checkbox?.checked));
            });
            if (!permissionCount) return;
            const selected = permissionCheckboxes.filter((checkbox) => checkbox.checked).length;
            permissionCount.textContent = `${selected} selected`;
        }

        permissionSearch?.addEventListener('input', () => {
            const query = permissionSearch.value.trim().toLowerCase();
            permissionItems.forEach((item) => {
                item.classList.toggle('is-filtered-out', query !== '' && !item.textContent.toLowerCase().includes(query));
            });
        });

        selectAllPermissions?.addEventListener('click', () => {
            permissionItems.forEach((item) => {
                if (!item.classList.contains('is-filtered-out')) {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = true;
                }
            });
            updatePermissionCount();
        });

        clearPermissions?.addEventListener('click', () => {
            permissionCheckboxes.forEach((checkbox) => { checkbox.checked = false; });
            updatePermissionCount();
        });

        permissionCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updatePermissionCount));
        updatePermissionCount();

        badgeColorPicker?.addEventListener('input', () => {
            if (badgeColorText) badgeColorText.value = badgeColorPicker.value.toUpperCase();
        });

        badgeColorText?.addEventListener('input', () => {
            const value = badgeColorText.value.trim();
            if (badgeColorPicker && /^#[0-9a-f]{6}$/i.test(value)) badgeColorPicker.value = value;
        });

        roleListSearch?.addEventListener('input', () => {
            const query = roleListSearch.value.trim().toLowerCase();
            let visibleCount = 0;
            roleRows.forEach((row) => {
                const isVisible = query === '' || row.textContent.toLowerCase().includes(query);
                row.hidden = !isVisible;
                if (isVisible) visibleCount += 1;
            });
            if (roleSearchEmpty) roleSearchEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
        });

        function closeAll() {
            modals.forEach((modal) => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            });
        }

        openButtons.forEach((button, index) => {
            const modal = modals[index];
            if (!modal) return;

            button.addEventListener('click', () => {
                closeAll();
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        modals.forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeAll();
            });

            const closeButton = modal.querySelector('[data-role-perms-close]');
            if (closeButton) {
                closeButton.addEventListener('click', closeAll);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAll();
        });
    })();
</script>
@endsection
