<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;

class RequireDownloadAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        if (RoleAccess::is($request->user(), 'staff') && (
            $request->is('modules/reports/energy-export')
            || $request->is('modules/energy/export-excel')
            || $request->is('modules/energy/annual/export-excel')
        )) {
            return $next($request);
        }

        $token = (string) $request->query('download_token', '');
        $sessionKey = 'download_authorizations.' . $token;
        $authorization = $token !== '' ? $request->session()->get($sessionKey) : null;

        if (! is_array($authorization) || (int) ($authorization['expires_at'] ?? 0) < now()->timestamp) {
            return redirect()
                ->back()
                ->with('error', 'Please confirm your password before downloading reports.');
        }

        if (($authorization['target'] ?? '') !== $this->normalizeCurrentRequest($request)) {
            $request->session()->forget($sessionKey);

            return redirect()
                ->back()
                ->with('error', 'Download confirmation did not match this report request.');
        }

        $request->session()->forget($sessionKey);

        return $next($request);
    }

    private function normalizeCurrentRequest(Request $request): string
    {
        $query = $request->query();
        unset($query['download_token']);
        ksort($query);

        return '/' . ltrim($request->path(), '/') . ($query ? '?' . http_build_query($query) : '');
    }
}
