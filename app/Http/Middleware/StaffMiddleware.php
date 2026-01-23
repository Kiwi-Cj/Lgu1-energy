<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        $user = Auth::user();
        $role = strtolower($user->role ?? '');

        // Block Staff from accessing User Management routes
        if ($role === 'staff' && $request->is('modules/users*')) {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access User Management.');
        }

        // Allow Staff to access other routes
        if ($role === 'staff') {
            return $next($request);
        }

        // For non-staff users, allow access (Admin, Energy Officer, etc.)
        return $next($request);
    }
}
