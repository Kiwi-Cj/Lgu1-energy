<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Facility;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        // Block Staff from accessing User Management
        // Only restrict staff; super admin, admin, and energy_officer have access
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }
        // Super admin has full access (no block)

        // Get all users with their assigned facility (if any)
        $users = User::with('facility')->get();
        $facilities = Facility::all();
        $totalUsers = $users->count();
        $activeUsers = $users->where('status', 'active')->count();
        $inactiveUsers = $users->where('status', 'inactive')->count();
        $rolesList = $users->pluck('role')->unique()->implode(', ');
        return view('modules.users.index', compact('users', 'facilities', 'totalUsers', 'activeUsers', 'inactiveUsers', 'rolesList'));
    }

    public function edit($id)
    {
        // Block Staff from accessing User Management
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $user = User::with('facility')->findOrFail($id);
        $facilities = Facility::all();
        return view('modules.users.edit', compact('user', 'facilities'));
    }

    public function store(Request $request)
    {
        // Block Staff from accessing User Management
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
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
            'facility_id' => 'nullable|exists:facilities,id',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        // Only assign facility_id if role is staff
        if (strtolower($validated['role']) !== 'staff') {
            $validated['facility_id'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    public function update(Request $request, $id)
    {
        // Block Staff from accessing User Management
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'role' => 'required|string|in:super admin,admin,staff,energy_officer',
            'status' => 'required|string|in:active,inactive',
            'facility_id' => 'nullable|exists:facilities,id',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        // Only assign facility_id if role is staff
        if (strtolower($validated['role']) !== 'staff') {
            $validated['facility_id'] = null;
        }

        $user->update($validated);
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }
}
