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
        padding: 8px 4px 18px;
        background: linear-gradient(180deg, rgba(239,246,255,.55) 0%, rgba(248,251,255,.92) 18%, #f8fbff 100%);
        border-radius: 22px;
    }
    .roles-hero {
        display: flex; justify-content: space-between; gap: 18px; flex-wrap: wrap;
        padding: 22px 22px 18px; border-radius: 24px; background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 55%, #eef2ff 100%);
        border: 1px solid #dbe6f5; box-shadow: 0 14px 34px rgba(37, 99, 235, .08); margin-bottom: 16px;
    }
    .roles-title { margin: 0; font-size: 2rem; font-weight: 900; color: #1e3a8a; }
    .roles-subtitle { margin-top: 6px; color: #475569; max-width: 70ch; }
    .roles-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; width: 100%; margin-top: 12px; }
    .roles-stat { background: #fff; border: 1px solid #dbe6f5; border-radius: 16px; padding: 12px 14px; }
    .roles-stat-label { font-size: .78rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .roles-stat-value { margin-top: 8px; font-size: 1.7rem; font-weight: 900; color: #0f172a; }
    .roles-grid { display: grid; grid-template-columns: minmax(320px, 380px) minmax(0, 1fr); gap: 14px; align-items: start; }
    .roles-panel {
        background: #fff; border: 1px solid #dbe6f5; border-radius: 20px; box-shadow: 0 10px 24px rgba(15,23,42,.06); padding: 18px;
    }
    .roles-panel h2 { margin: 0 0 8px; font-size: 1.1rem; font-weight: 800; color: #0f172a; }
    .roles-panel p { margin: 0 0 12px; color: #64748b; }
    .roles-form-grid { display: grid; gap: 10px; }
    .roles-field label { display:block; margin-bottom: 6px; font-size: .84rem; font-weight: 800; color: #334155; }
    .roles-field input, .roles-field textarea {
        width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 11px 12px; font: inherit; background: #fff;
    }
    .roles-field textarea { min-height: 76px; resize: vertical; }
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }
    .perm-item {
        border: 1px solid #dbe6f5;
        border-radius: 12px;
        padding: 10px 12px;
        background: #f8fbff;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .perm-item input { margin-top: 3px; }
    .perm-item label { margin: 0; cursor: pointer; font-weight: 700; color: #1f2937; }
    .perm-item small { display: block; margin-top: 4px; color: #64748b; font-weight: 600; }
    .roles-actions { display:flex; gap: 10px; margin-top: 6px; }
    .btn-primary, .btn-secondary {
        display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius: 12px; padding: 11px 15px; font-weight: 800; text-decoration:none; border:1px solid transparent;
    }
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-secondary { background:#eef2ff; color:#1d4ed8; border-color:#c7d2fe; }
    .roles-table-wrap { overflow:auto; border-radius: 18px; border: 1px solid #dbe6f5; background: #fff; box-shadow: 0 10px 24px rgba(15,23,42,.06); }
    .roles-table { width:100%; border-collapse: collapse; min-width: 980px; }
    .roles-table th, .roles-table td { padding: 9px 10px; border-bottom: 1px solid #edf2fb; text-align: left; vertical-align: top; }
    .roles-table th { background: #eef2ff; color: #334155; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; }
    .roles-table td { line-height: 1.45; }
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
    .role-btn {
        display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:999px; border:1px solid #dbe6f5; color:#64748b; text-decoration:none; margin-right:6px;
    }
    .role-btn.delete { color:#dc2626; background:#fef2f2; border-color:#fecaca; }
    .role-btn.disabled { opacity:.45; pointer-events:none; }
    .roles-note { margin-top: 10px; color:#64748b; font-size:.88rem; }
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
    @media (max-width: 1024px) {
        .roles-page { padding: 6px 0 20px; border-radius: 18px; }
        .roles-grid { grid-template-columns: 1fr; }
        .roles-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .roles-page { padding: 0; background: transparent; }
        .roles-hero { padding: 16px; border-radius: 18px; }
        .roles-title { font-size: 1.45rem; }
        .roles-stats { grid-template-columns: 1fr; }
        .roles-panel { padding: 16px; }
        .roles-actions { flex-direction: column; }
        .btn-primary, .btn-secondary { width: 100%; }
        .roles-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .roles-table {
            min-width: 860px;
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
            max-height: calc(100vh - 118px);
            overflow: auto;
        }
        .roles-panel.roles-list-panel {
            min-height: calc(100vh - 170px);
        }
    }
</style>

<div class="roles-page">
    <div class="roles-hero">
        <div>
            <h1 class="roles-title">Roles Management</h1>
            <div class="roles-subtitle">Create custom roles and manage how many users are assigned to each role. System roles stay protected.</div>
        </div>
        <div class="roles-stats">
            <div class="roles-stat">
                <div class="roles-stat-label">Total Roles</div>
                <div class="roles-stat-value">{{ $totalRoles ?? 0 }}</div>
            </div>
            <div class="roles-stat">
                <div class="roles-stat-label">Assigned Users</div>
                <div class="roles-stat-value">{{ $assignedUsers ?? 0 }}</div>
            </div>
            <div class="roles-stat">
                <div class="roles-stat-label">Active Roles</div>
                <div class="roles-stat-value">{{ $activeRoles ?? 0 }}</div>
            </div>
            <div class="roles-stat">
                <div class="roles-stat-label">Custom Roles</div>
                <div class="roles-stat-value">{{ $customRoleCount ?? 0 }}</div>
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
            <h2>Create Role</h2>
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
                        <div class="perm-grid">
                            @foreach($availablePermissions as $permission)
                                @php
                                    $permKey = (string) ($permission['key'] ?? '');
                                    $permLabel = (string) ($permission['label'] ?? $permKey);
                                    $checked = in_array($permKey, old('permissions', []), true);
                                @endphp
                                <div class="perm-item">
                                    <input id="perm_{{ $permKey }}" type="checkbox" name="permissions[]" value="{{ $permKey }}" @checked($checked)>
                                    <div>
                                        <label for="perm_{{ $permKey }}">{{ $permLabel }}</label>
                                        <small>{{ implode(', ', array_map(fn ($role) => str_replace('_', ' ', $role), $permission['roles'] ?? [])) }}</small>
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
                        <input id="badge_color" type="text" name="badge_color" value="{{ old('badge_color', '#6366f1') }}" placeholder="#6366f1">
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

            <div class="roles-note">System roles are built in. Custom roles are stored in the database and can be assigned to users from the Users page.</div>
        </div>

        <div class="roles-panel roles-list-panel">
            <h2>Role List</h2>
            <p>Current system and custom roles, plus user counts for each role.</p>
            <div class="roles-table-wrap">
                <table class="roles-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th>Role</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th style="width:160px;">Users</th>
                            <th style="width:150px;">Type</th>
                            <th style="width:150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($roles ?? []) as $roleItem)
                            @php
                                $roleKey = str_replace([' ', '-'], '_', strtolower((string) ($roleItem['key'] ?? '')));
                                $isSystem = (bool) ($roleItem['is_system'] ?? false);
                                $canDelete = $isSuperAdmin && ! $isSystem && (int) ($roleItem['assigned_users'] ?? 0) === 0;
                            @endphp
                            <tr>
                                <td>{{ $roleItem['id'] }}</td>
                                <td><span class="role-badge" style="background:{{ $roleItem['badge_color'] ?? '#6366f1' }};">{{ $roleItem['name'] }}</span></td>
                                <td>
                                    {{ $roleItem['description'] }}
                                    <div style="margin-top:6px;color:#64748b;font-size:.82rem;">Active: {{ $roleItem['active_users'] ?? 0 }} | Inactive: {{ $roleItem['inactive_users'] ?? 0 }}</div>
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
                                <td>
                                    <span class="role-pill {{ $isSystem ? 'system' : 'custom' }}" style="white-space:nowrap;">
                                        {{ $isSystem ? 'System' : 'Custom' }}
                                    </span>
                                </td>
                                <td>
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
                                <td colspan="7" style="padding:20px;color:#64748b;">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
