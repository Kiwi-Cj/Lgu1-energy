<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (! RoleAccess::can(auth()->user(), 'access_audit_logs')) {
            return redirect()->route('dashboard.index')
                ->with('error', 'You do not have permission to access Audit Logs.');
        }

        if (! Schema::hasTable('audit_logs')) {
            return redirect()->route('dashboard.index')
                ->with('error', 'Audit Logs table is missing. Please run migrations first.');
        }

        $hasNameColumn = Schema::hasColumn('users', 'name');
        $hasFullNameColumn = Schema::hasColumn('users', 'full_name');
        $userSelectColumns = ['id', 'username', 'email', 'role'];
        if ($hasFullNameColumn) {
            $userSelectColumns[] = 'full_name';
        }
        if ($hasNameColumn) {
            $userSelectColumns[] = 'name';
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'user_id' => trim((string) $request->query('user_id', '')),
            'module' => trim((string) $request->query('module', '')),
            'action' => trim((string) $request->query('action', '')),
            'method' => trim((string) $request->query('method', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'scope' => trim((string) $request->query('scope', 'essential')),
        ];

        if (! in_array($filters['scope'], ['essential', 'all'], true)) {
            $filters['scope'] = 'essential';
        }

        if ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']) {
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        $query = AuditLog::query()
            ->with(['user' => function ($userQuery) use ($userSelectColumns) {
                $userQuery->select($userSelectColumns);
            }]);

        if ($filters['scope'] === 'essential') {
            $this->applyEssentialScope($query);
        }

        if ($filters['q'] !== '') {
            $needle = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($needle, $hasFullNameColumn, $hasNameColumn) {
                $q->where('action', 'like', $needle)
                    ->orWhere('module', 'like', $needle)
                    ->orWhere('description', 'like', $needle)
                    ->orWhere('path', 'like', $needle)
                    ->orWhereHas('user', function ($userQuery) use ($needle, $hasFullNameColumn, $hasNameColumn) {
                        $userQuery
                            ->where('username', 'like', $needle)
                            ->orWhere('email', 'like', $needle);

                        if ($hasFullNameColumn) {
                            $userQuery->orWhere('full_name', 'like', $needle);
                        }
                        if ($hasNameColumn) {
                            $userQuery->orWhere('name', 'like', $needle);
                        }
                    });
            });
        }

        if ($filters['user_id'] !== '') {
            $query->where('user_id', (int) $filters['user_id']);
        }
        if ($filters['module'] !== '') {
            $query->where('module', $filters['module']);
        }
        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }
        if ($filters['method'] !== '') {
            $query->where('method', strtoupper($filters['method']));
        }
        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $baseOptionsQuery = AuditLog::query();
        if ($filters['scope'] === 'essential') {
            $this->applyEssentialScope($baseOptionsQuery);
        }
        $moduleOptions = (clone $baseOptionsQuery)->whereNotNull('module')->select('module')->distinct()->orderBy('module')->pluck('module');
        $actionOptions = (clone $baseOptionsQuery)->select('action')->distinct()->orderBy('action')->limit(150)->pluck('action');
        $userOptionColumns = ['id', 'username'];
        if ($hasFullNameColumn) {
            $userOptionColumns[] = 'full_name';
        }
        if ($hasNameColumn) {
            $userOptionColumns[] = 'name';
        }
        $userSortColumn = $hasFullNameColumn ? 'full_name' : ($hasNameColumn ? 'name' : 'username');
        $userOptions = User::query()
            ->whereIn('id', (clone $baseOptionsQuery)->select('user_id')->whereNotNull('user_id')->distinct())
            ->orderBy($userSortColumn)
            ->get($userOptionColumns);

        $statsQuery = AuditLog::query();
        if ($filters['scope'] === 'essential') {
            $this->applyEssentialScope($statsQuery);
        }
        $totalLogs = (clone $statsQuery)->count();
        $todayLogs = (clone $statsQuery)->whereDate('created_at', now()->toDateString())->count();
        $activeUsers = (clone $statsQuery)->whereDate('created_at', '>=', now()->subDays(30)->toDateString())->distinct('user_id')->count('user_id');

        $user = auth()->user();
        $role = RoleAccess::normalize($user);

        return view('modules.audit.index', compact(
            'logs',
            'filters',
            'moduleOptions',
            'actionOptions',
            'userOptions',
            'totalLogs',
            'todayLogs',
            'activeUsers',
            'role',
            'user'
        ));
    }

    private function applyEssentialScope(Builder $query): Builder
    {
        return $query->where(function (Builder $essentialQuery) {
            $essentialQuery
                ->where(function (Builder $mutatingQuery) {
                    $mutatingQuery
                        ->whereIn('method', ['POST', 'PUT', 'PATCH', 'DELETE'])
                        ->whereNotIn('action', ['auth.login', 'auth.logout', 'notifications.markAllRead', 'notifications.markRead']);
                })
                ->orWhere(function (Builder $loginQuery) {
                    $loginQuery
                        ->where('action', 'auth.login')
                        ->where('method', 'POST')
                        ->where(function (Builder $pathQuery) {
                            $pathQuery->where('route_name', 'login')->orWhere('path', '/login');
                        });
                })
                ->orWhere(function (Builder $logoutQuery) {
                    $logoutQuery
                        ->where('action', 'auth.logout')
                        ->where('method', 'POST')
                        ->where(function (Builder $pathQuery) {
                            $pathQuery->where('route_name', 'logout')->orWhere('path', '/logout');
                        });
                });
        });
    }
}
