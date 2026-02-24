<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Facility;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        // Block Staff from accessing User Management
        // Only restrict staff; super admin, admin, and energy_officer have access
        if (RoleAccess::is(auth()->user(), 'staff')) {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }
        // Super admin has full access (no block)

        // Get users with optional role filter (used by roles action buttons)
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
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.users.index', compact('users', 'facilities', 'totalUsers', 'activeUsers', 'inactiveUsers', 'rolesList', 'role', 'user', 'notifications', 'unreadNotifCount', 'selectedRole'));
    }

    public function edit($id)
    {
        // Block Staff from accessing User Management
        if (RoleAccess::is(auth()->user(), 'staff')) {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $user = User::with('facilities')->findOrFail($id);
        $facilities = Facility::all();
        return view('modules.users.edit', compact('user', 'facilities'));
    }

    public function store(Request $request)
    {
        // Block Staff from accessing User Management
        if (RoleAccess::is(auth()->user(), 'staff')) {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:super admin,admin,staff,energy_officer',
            'status' => 'required|string|in:active,inactive',
            'facility_id' => 'array',
            'facility_id.*' => 'nullable|exists:facilities,id',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $actorRole = RoleAccess::normalize(auth()->user());
        $targetRole = RoleAccess::normalize($validated['role'] ?? '');
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
        // Block Staff from accessing User Management
        if (RoleAccess::is(auth()->user(), 'staff')) {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|in:super admin,admin,staff,energy_officer',
            'status' => 'required|string|in:active,inactive',
            'facility_id' => 'array',
            'facility_id.*' => 'nullable|exists:facilities,id',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $actorRole = RoleAccess::normalize(auth()->user());
        $targetRole = RoleAccess::normalize($validated['role'] ?? '');
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
        // Block Staff from accessing Roles page
        if (RoleAccess::is(auth()->user(), 'staff')) {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Roles.');
        }

        $actorRole = RoleAccess::normalize(auth()->user());
        $usersQuery = User::query()->select(['id', 'role', 'status']);
        if ($actorRole === 'admin') {
            $usersQuery->whereRaw('LOWER(role) NOT IN (?, ?)', ['super admin', 'admin']);
        }

        $users = $usersQuery->get();
        $groupedUsers = $users->groupBy(function ($user) {
            return RoleAccess::normalize($user->role ?? '');
        });

        $roleMeta = [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access including security, configuration, and user management.',
                'permissions' => 'All Permissions',
                'badge_color' => '#4f46e5',
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Operational management with access to reports, users, and maintenance modules.',
                'permissions' => 'Manage Users / Reports / Operations',
                'badge_color' => '#2563eb',
            ],
            'energy_officer' => [
                'name' => 'Energy Officer',
                'description' => 'Monitors energy trends, validates records, and manages analytics outputs.',
                'permissions' => 'Energy Monitoring / Reports',
                'badge_color' => '#0ea5e9',
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'Limited access for facility-level data entry and daily operational updates.',
                'permissions' => 'View / Encode',
                'badge_color' => '#6b7280',
            ],
        ];

        if ($actorRole === 'admin') {
            unset($roleMeta['super_admin'], $roleMeta['admin']);
        }

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
                'badge_color' => $meta['badge_color'],
                'assigned_users' => $usersPerRole->count(),
                'active_users' => $activeUsers,
                'inactive_users' => max($usersPerRole->count() - $activeUsers, 0),
            ];
        })->values();

        // Include unexpected role strings found in DB so they are visible in the table.
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
                'permissions' => 'Custom',
                'badge_color' => '#9333ea',
                'assigned_users' => $usersPerRole->count(),
                'active_users' => $activeUsers,
                'inactive_users' => max($usersPerRole->count() - $activeUsers, 0),
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

        return view('modules.users.roles', compact('roles', 'totalRoles', 'assignedUsers', 'activeRoles'));
    }

    public function disable($id)
    {
        // Block Staff from accessing User Management actions
        if (RoleAccess::is(auth()->user(), 'staff')) {
            return redirect()->route('modules.energy.index')
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
}



