<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockStaffFromReports
{
    public function handle(Request $request, Closure $next)
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));

        if ($role === 'staff') {
            return redirect()->route('modules.energy.index')
                ->with('error', 'You do not have permission to access Reports.');
        }

        return $next($request);
    }
}
