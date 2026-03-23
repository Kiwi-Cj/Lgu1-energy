<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    private static ?bool $hasAuditTable = null;
    private static ?bool $hasSettingsTable = null;

    public function handle(Request $request, Closure $next): Response
    {
        $beforeUser = auth()->user();
        $response = $next($request);

        if (! $this->shouldRun()) {
            return $response;
        }

        if ($this->isSuccessful($response)) {
            $afterUser = auth()->user();

            if (! $beforeUser && $afterUser && $this->isLoginRequest($request)) {
                $this->record(
                    $afterUser->id,
                    RoleAccess::normalize($afterUser),
                    'auth.login',
                    'authentication',
                    'User logged in successfully.',
                    'POST',
                    (string) optional($request->route())->getName(),
                    trim($request->path(), '/'),
                    $request
                );
            }

            if ($beforeUser && ! $afterUser && $this->isLogoutRequest($request)) {
                $this->record(
                    $beforeUser->id,
                    RoleAccess::normalize($beforeUser),
                    'auth.logout',
                    'authentication',
                    'User logged out.',
                    'POST',
                    (string) optional($request->route())->getName(),
                    trim($request->path(), '/'),
                    $request
                );
            }
        }

        if ($this->skipGenericAudit($request) || ! $this->isSuccessful($response)) {
            return $response;
        }

        // Keep the audit trail focused on state-changing actions.
        // Logging routine page views adds a write query to nearly every request.
        if (! $this->isMutatingRequest($request)) {
            return $response;
        }

        $actor = auth()->user() ?? $beforeUser;
        if (! $actor) {
            return $response;
        }

        $routeName = (string) optional($request->route())->getName();
        $path = trim($request->path(), '/');
        $module = $this->resolveModule($routeName, $path);
        $method = strtoupper($request->method());
        $isReadRequest = $method === 'GET';
        $action = $routeName !== ''
            ? $routeName
            : (($isReadRequest ? 'view' : strtolower($method)) . ':' . $path);
        $description = $this->buildDescription($method, $routeName, $path, $module);

        $this->record(
            $actor->id,
            RoleAccess::normalize($actor),
            $action,
            $module,
            $description,
            $method,
            $routeName,
            $path,
            $request
        );

        return $response;
    }

    private function shouldRun(): bool
    {
        if (! $this->hasAuditTable()) {
            return false;
        }

        if (! $this->auditLogsEnabled()) {
            return false;
        }

        return true;
    }

    private function hasAuditTable(): bool
    {
        if (self::$hasAuditTable !== null) {
            return self::$hasAuditTable;
        }

        try {
            self::$hasAuditTable = Schema::hasTable('audit_logs');
        } catch (\Throwable $e) {
            self::$hasAuditTable = false;
        }

        return self::$hasAuditTable;
    }

    private function auditLogsEnabled(): bool
    {
        if (self::$hasSettingsTable === null) {
            try {
                self::$hasSettingsTable = Schema::hasTable('settings');
            } catch (\Throwable $e) {
                self::$hasSettingsTable = false;
            }
        }

        if (! self::$hasSettingsTable) {
            return true;
        }

        try {
            return (string) Setting::getValue('enable_audit_logs', '1') === '1';
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function isSuccessful(Response $response): bool
    {
        return $response->getStatusCode() < 400;
    }

    private function isMutatingRequest(Request $request): bool
    {
        return in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function skipGenericAudit(Request $request): bool
    {
        if ($this->isLoginRequest($request) || $this->isLogoutRequest($request) || $request->routeIs('otp.*')) {
            return true;
        }

        if ($request->routeIs('notifications.markAllRead', 'notifications.markRead')) {
            return true;
        }

        return false;
    }

    private function isLoginRequest(Request $request): bool
    {
        if (strtoupper($request->method()) !== 'POST') {
            return false;
        }

        return $request->routeIs('login') || trim($request->path(), '/') === 'login';
    }

    private function isLogoutRequest(Request $request): bool
    {
        if (strtoupper($request->method()) !== 'POST') {
            return false;
        }

        return $request->routeIs('logout') || trim($request->path(), '/') === 'logout';
    }

    private function buildDescription(string $method, string $routeName, string $path, string $module): string
    {
        if ($method === 'GET') {
            $target = $routeName !== ''
                ? str_replace('.', ' / ', $routeName)
                : '/' . $path;

            return sprintf('Viewed %s (%s)', $target, $module);
        }

        return sprintf(
            '%s %s%s',
            $method,
            '/' . $path,
            $routeName !== '' ? ' (' . $routeName . ')' : ''
        );
    }

    private function resolveModule(string $routeName, string $path): string
    {
        if ($routeName !== '') {
            $parts = explode('.', $routeName);
            if (($parts[0] ?? '') === 'modules' && isset($parts[1])) {
                return (string) $parts[1];
            }
            return (string) ($parts[0] ?? 'system');
        }

        $segments = explode('/', $path);
        if (($segments[0] ?? '') === 'modules' && isset($segments[1])) {
            return (string) $segments[1];
        }

        return (string) ($segments[0] ?? 'system');
    }

    private function record(
        ?int $userId,
        string $role,
        string $action,
        string $module,
        string $description,
        string $method,
        string $routeName,
        string $path,
        Request $request
    ): void {
        try {
            AuditLog::query()->create([
                'user_id' => $userId,
                'role' => $role !== '' ? $role : null,
                'action' => substr($action, 0, 150),
                'module' => substr($module, 0, 80),
                'description' => substr($description, 0, 500),
                'method' => substr($method, 0, 10),
                'route_name' => $routeName !== '' ? substr($routeName, 0, 150) : null,
                'path' => substr('/' . ltrim($path, '/'), 0, 191),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'metadata' => [
                    'query' => $request->query(),
                    'payload_keys' => array_values(array_keys($request->except([
                        'password',
                        'password_confirmation',
                        'current_password',
                        'otp',
                        'otp_code',
                        '_token',
                    ]))),
                ],
            ]);

            $this->maybePruneOldAuditLogs();
        } catch (\Throwable $e) {
            // Never break the request lifecycle because of audit logging failures.
        }
    }

    private function maybePruneOldAuditLogs(): void
    {
        if (! self::$hasSettingsTable) {
            return;
        }

        try {
            $settings = Setting::getMany([
                'retention_period',
                'audit_last_pruned_at',
            ], [
                'retention_period' => '3',
                'audit_last_pruned_at' => null,
            ]);

            $retentionMonths = (int) ($settings['retention_period'] ?? 12);
            if ($retentionMonths < 1) {
                $retentionMonths = 1;
            }

            $lastPrunedAt = trim((string) ($settings['audit_last_pruned_at'] ?? ''));
            if ($lastPrunedAt !== '') {
                $lastPruned = Carbon::parse($lastPrunedAt);
                if ($lastPruned->greaterThan(now()->subDay())) {
                    return;
                }
            }

            $cutoff = now()->subMonthsNoOverflow($retentionMonths)->startOfDay();

            AuditLog::query()
                ->where('created_at', '<', $cutoff)
                ->delete();

            Setting::setValue('audit_last_pruned_at', now()->toDateTimeString());
        } catch (\Throwable $e) {
            // Pruning failures must not block user requests.
        }
    }
}
