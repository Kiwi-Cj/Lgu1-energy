<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareLayoutData
{
    public function handle(Request $request, Closure $next)
    {
        if (! $this->shouldShareLayoutData($request)) {
            return $next($request);
        }

        $user = auth()->user();
        $shared = [
            'user' => $user,
            'role' => RoleAccess::normalize($user),
            'notifications' => collect(),
            'unreadNotifCount' => 0,
        ];

        if ($user) {
            $shared = array_merge($shared, $user->notificationPanelData());
        }

        View::share($shared);

        return $next($request);
    }

    private function shouldShareLayoutData(Request $request): bool
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return false;
        }

        return ! $request->is('api/*');
    }
}
