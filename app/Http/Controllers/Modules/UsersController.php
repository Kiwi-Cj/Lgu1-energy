<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\User;
use App\Models\UserRole;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index()
    {
        if (! $this->canAccessUserManagement()) {
            return redirect()->route('modules.energy-monitoring.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $selectedRole = RoleAccess::normalize(request('role', ''));
        $actorRole = RoleAccess::normalize(auth()->user());
        $usersQuery = User::with('facilities');

        if ($actorRole === 'admin') {
            $usersQuery->whereRaw('LOWER(role) NOT IN (?, ?)', ['super admin', 'admin']);
        }

        if ($selectedRole !== '') {
            $usersQuery->whereRaw(
                "REPLACE(REPLACE(LOWER(role), ' ', '_'), '-', '_') = ?",
                [$selectedRole]
            );
        }

        $users = $usersQuery->get();
        $facilities = Facility::all();
        $totalUsers = $users->count();
        $activeUsers = $users->where('status', 'active')->count();
        $inactiveUsers = $users->where('status', 'inactive')->count();
        $rolesList = $users->pluck('role')->unique()->implode(', ');
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $availableRoleOptions = collect($this->roleTemplates())
            ->mapWithKeys(fn ($meta, $slug) => [$slug => $meta['name']])
            ->merge(
                UserRole::query()
                    ->orderBy('name')
                    ->get(['name', 'slug'])
                    ->mapWithKeys(fn (UserRole $customRole) => [
                        RoleAccess::normalize($customRole->slug) => $customRole->name,
                    ])
            );

        return view('modules.users.index', compact(
            'users',
            'facilities',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'rolesList',
            'role',
            'user',
            'selectedRole',
            'availableRoleOptions'
        ));
    }

    public function edit($id)
    {
        if (! $this->canAccessUserManagement()) {
            return redirect()->route('modules.energy-monitoring.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        User::with('facilities')->findOrFail($id);

        return redirect()->route('users.index')
            ->with('info', 'User editing is managed from the users list page.');
    }

    public function store(Request $request)
    {
        if (! $this->canAccessUserManagement()) {
            return redirect()->route('modules.energy-monitoring.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string',
            'status' => 'required|string|in:active,inactive',
            'facility_id' => 'array',
            'facility_id.*' => 'nullable|exists:facilities,id',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $actorRole = RoleAccess::normalize(auth()->user());
        $targetRole = RoleAccess::normalize($validated['role'] ?? '');

        if (! in_array($targetRole, $this->allowedRoleSlugs(), true)) {
            return redirect()->route('users.index')
                ->withInput()
                ->with('error', 'Selected role is not available.');
        }

        if ($actorRole !== 'super_admin' && in_array($targetRole, ['super_admin', 'admin'], true)) {
            return redirect()->route('users.index')
                ->withInput()
                ->with('error', 'Only Super Admin can assign Admin or Super Admin roles.');
        }

        $facilityIds = $request->input('facility_id', []);
        $validated['password'] = Hash::make($validated['password']);
        unset($validated['facility_id']);

        $user = User::create($validated);
        if (strtolower($validated['role']) === 'staff') {
            $user->facilities()->sync($facilityIds);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    public function update(Request $request, $id)
    {
        if (! $this->canAccessUserManagement()) {
            return redirect()->route('modules.energy-monitoring.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $user = User::findOrFail($id);
        $actorRole = RoleAccess::normalize(auth()->user());
        $existingRole = RoleAccess::normalize($user->role ?? '');

        if ($actorRole !== 'super_admin' && in_array($existingRole, ['super_admin', 'admin'], true)) {
            return redirect()->route('users.index')
                ->with('error', 'Only Super Admin can edit Admin or Super Admin accounts.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string',
            'status' => 'required|string|in:active,inactive',
            'facility_id' => 'array',
            'facility_id.*' => 'nullable|exists:facilities,id',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $targetRole = RoleAccess::normalize($validated['role'] ?? '');
        if (! in_array($targetRole, $this->allowedRoleSlugs(), true)) {
            return redirect()->route('users.index')
                ->withInput()
                ->with('error', 'Selected role is not available.');
        }

        if ($actorRole !== 'super_admin' && in_array($targetRole, ['super_admin', 'admin'], true)) {
            return redirect()->route('users.index')
                ->withInput()
                ->with('error', 'Only Super Admin can assign Admin or Super Admin roles.');
        }

        $facilityIds = $request->input('facility_id', []);
        unset($validated['facility_id']);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if (strtolower($validated['role']) === 'staff') {
            $user->facilities()->sync($facilityIds);
        } else {
            $user->facilities()->sync([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    public function roles()
    {
        if (! $this->canAccessUserManagement() || RoleAccess::normalize(auth()->user()) !== 'super_admin') {
            return redirect()->route('modules.energy-monitoring.index')
                ->with('error', 'You do not have permission to access User Roles.');
        }

        $actorRole = RoleAccess::normalize(auth()->user());
        $usersQuery = User::query()->select(['id', 'role', 'status']);

        $users = $usersQuery->get();
        $groupedUsers = $users->groupBy(function ($user) {
            return RoleAccess::normalize($user->role ?? '');
        });

        $roleMeta = $this->roleTemplates();

        $customRoles = UserRole::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (UserRole $role) {
                $slug = RoleAccess::normalize($role->slug ?: $role->name);
                $permissions = $this->decodeRolePermissions($role->permissions);

                return [
                    $slug => [
                        'name' => $role->name,
                        'description' => $role->description ?: 'Custom role created by Super Admin.',
                        'permissions' => $permissions,
                        'badge_color' => $role->badge_color ?: '#6366f1',
                        'is_system' => (bool) $role->is_system,
                    ],
                ];
            })
            ->all();

        $roleMeta = array_merge($roleMeta, $customRoles);

        $roles = collect($roleMeta)->map(function ($meta, $key) use ($groupedUsers) {
            $usersPerRole = $groupedUsers->get($key, collect());
            $activeUsers = $usersPerRole->filter(function ($user) {
                return strtolower((string) ($user->status ?? '')) === 'active';
            })->count();

            return [
                'id' => null,
                'key' => $key,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'permissions' => $meta['permissions'],
                'permission_labels' => $this->permissionLabels($meta['permissions'] ?? []),
                'badge_color' => $meta['badge_color'],
                'assigned_users' => $usersPerRole->count(),
                'active_users' => $activeUsers,
                'inactive_users' => max($usersPerRole->count() - $activeUsers, 0),
                'is_system' => (bool) ($meta['is_system'] ?? false),
            ];
        })->values();

        foreach ($groupedUsers as $roleKey => $usersPerRole) {
            if ($roleKey === '' || collect($roleMeta)->has($roleKey)) {
                continue;
            }

            $activeUsers = $usersPerRole->filter(function ($user) {
                return strtolower((string) ($user->status ?? '')) === 'active';
            })->count();

            $roles->push([
                'id' => null,
                'key' => $roleKey,
                'name' => ucwords(str_replace('_', ' ', $roleKey)),
                'description' => 'Custom role detected from existing user records.',
                'permissions' => [],
                'permission_labels' => [],
                'badge_color' => '#9333ea',
                'assigned_users' => $usersPerRole->count(),
                'active_users' => $activeUsers,
                'inactive_users' => max($usersPerRole->count() - $activeUsers, 0),
                'is_system' => false,
            ]);
        }

        $roles = $roles->values()->map(function ($role, $index) {
            $role['id'] = $index + 1;
            return $role;
        });

        $totalRoles = $roles->count();
        $assignedUsers = $users->count();
        $activeRoles = $roles->filter(function ($role) {
            return (int) ($role['active_users'] ?? 0) > 0;
        })->count();
        $customRoleCount = $roles->filter(fn ($role) => empty($role['is_system']))->count();
        $userRoleTemplates = collect($roleMeta)
            ->map(fn ($meta, $key) => array_merge($meta, ['slug' => $key]))
            ->values();
        $availablePermissions = collect(config('role_permissions.abilities', []))
            ->map(function ($roles, $ability) {
                return [
                    'key' => $ability,
                    'label' => $this->permissionLabel($ability),
                    'roles' => $roles,
                ];
            })
            ->values();

        return view('modules.users.roles', compact(
            'roles',
            'totalRoles',
            'assignedUsers',
            'activeRoles',
            'customRoleCount',
            'userRoleTemplates',
            'availablePermissions'
        ));
    }

    public function storeRole(Request $request)
    {
        if (! $this->canAccessUserManagement() || RoleAccess::normalize(auth()->user()) !== 'super_admin') {
            return redirect()->route('users.roles')
                ->with('error', 'Only Super Admin can create roles.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(array_keys(config('role_permissions.abilities', [])))],
            'badge_color' => 'nullable|string|max:32',
        ]);

        $slug = RoleAccess::normalize($validated['name']);
        if ($slug === '' || array_key_exists($slug, $this->roleTemplates())) {
            return redirect()->route('users.roles')
                ->withInput()
                ->with('error', 'That role name is reserved or invalid.');
        }

        $exists = UserRole::query()
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(REPLACE(REPLACE(name, " ", "_"), "-", "_")) = ?', [$slug])
            ->exists();

        if ($exists) {
            return redirect()->route('users.roles')
                ->withInput()
                ->with('error', 'That role already exists.');
        }

        UserRole::create([
            'name' => trim((string) $validated['name']),
            'slug' => $slug,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
            'badge_color' => trim((string) ($validated['badge_color'] ?? '')) ?: '#6366f1',
            'is_system' => false,
        ]);

        return redirect()->route('users.roles')->with('success', 'Custom role created successfully.');
    }

    public function destroyRole(UserRole $role)
    {
        if (! $this->canAccessUserManagement() || RoleAccess::normalize(auth()->user()) !== 'super_admin') {
            return redirect()->route('users.roles')
                ->with('error', 'Only Super Admin can delete roles.');
        }

        if ($role->is_system) {
            return redirect()->route('users.roles')
                ->with('error', 'System roles cannot be deleted.');
        }

        $roleKey = RoleAccess::normalize($role->slug ?: $role->name);
        $assigned = User::query()
            ->whereRaw("REPLACE(REPLACE(LOWER(role), ' ', '_'), '-', '_') = ?", [$roleKey])
            ->exists();

        if ($assigned) {
            return redirect()->route('users.roles')
                ->with('error', 'This role still has assigned users.');
        }

        $role->delete();

        return redirect()->route('users.roles')->with('success', 'Role deleted successfully.');
    }

    public function disable($id)
    {
        if (! $this->canAccessUserManagement()) {
            return redirect()->route('modules.energy-monitoring.index')
                ->with('error', 'You do not have permission to manage users.');
        }

        $user = User::findOrFail($id);
        $actor = auth()->user();
        $actorRole = RoleAccess::normalize($actor);
        $targetRole = RoleAccess::normalize($user->role ?? '');

        if ($actor && (int) $actor->id === (int) $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot disable your own account.');
        }

        if ($actorRole !== 'super_admin' && in_array($targetRole, ['super_admin', 'admin'], true)) {
            return redirect()->route('users.index')
                ->with('error', 'Only Super Admin can disable Admin or Super Admin accounts.');
        }

        $user->status = 'inactive';
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User disabled successfully.');
    }

    private function canAccessUserManagement(): bool
    {
        return RoleAccess::can(auth()->user(), 'access_users');
    }

    private function roleTemplates(): array
    {
        return [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access including security, configuration, and user management.',
                'permissions' => $this->permissionsForRole('super_admin'),
                'badge_color' => '#4f46e5',
                'is_system' => true,
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Operational management with access to reports, users, and maintenance modules.',
                'permissions' => $this->permissionsForRole('admin'),
                'badge_color' => '#2563eb',
                'is_system' => true,
            ],
            'energy_officer' => [
                'name' => 'Energy Officer',
                'description' => 'Monitors energy trends, validates records, and manages analytics outputs.',
                'permissions' => $this->permissionsForRole('energy_officer'),
                'badge_color' => '#0ea5e9',
                'is_system' => true,
            ],
            'engineer' => [
                'name' => 'Engineer',
                'description' => 'Reviews technical approvals, meter validation, and facility engineering checks.',
                'permissions' => $this->permissionsForRole('engineer'),
                'badge_color' => '#0891b2',
                'is_system' => true,
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'Limited access for facility-level data entry and daily operational updates.',
                'permissions' => $this->permissionsForRole('staff'),
                'badge_color' => '#6b7280',
                'is_system' => true,
            ],
        ];
    }

    private function permissionsForRole(string $role): array
    {
        $role = RoleAccess::normalize($role);

        return collect(config('role_permissions.abilities', []))
            ->filter(fn ($roles) => is_array($roles) && RoleAccess::in($role, $roles))
            ->keys()
            ->values()
            ->all();
    }

    private function allowedRoleSlugs(): array
    {
        $roleSlugs = array_keys($this->roleTemplates());
        $customRoles = UserRole::query()
            ->pluck('slug')
            ->map(fn ($slug) => RoleAccess::normalize($slug))
            ->all();

        return array_values(array_unique(array_merge($roleSlugs, $customRoles)));
    }

    private function decodeRolePermissions(null|string|array $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value)));
        }

        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded)));
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function permissionLabel(string $key): string
    {
        return str_replace('_', ' ', ucwords($key, '_'));
    }

    private function permissionLabels(array $keys): array
    {
        return array_map(fn (string $key) => $this->permissionLabel($key), $keys);
    }
}
