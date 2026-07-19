<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceRolePermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $ability = $this->requiredAbility($request);
        if ($ability === null || RoleAccess::can($user, $ability)) {
            return $next($request);
        }

        $message = 'Your role does not have permission to perform this action.';
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json(['message' => $message], 403);
        }

        if ($request->isMethod('GET')) {
            return redirect()->route('dashboard.index')->with('error', $message);
        }

        return redirect()->back()->with('error', $message);
    }

    private function requiredAbility(Request $request): ?string
    {
        $name = (string) optional($request->route())->getName();
        $isWrite = ! $request->isMethod('GET') && ! $request->isMethod('HEAD');

        if ($request->is('modules/users*') || $request->is('users/roles*')) {
            return 'access_users';
        }

        if ($request->is('modules/settings*')) {
            return 'access_settings';
        }

        if ($request->is('modules/audit*')) {
            return 'access_audit_logs';
        }

        if ($request->is('modules/reports*')) {
            return str_contains($name, 'export') || $request->is('*export*')
                ? 'export_reports'
                : 'access_reports';
        }

        if ($request->is('modules/energy-incidents*')) {
            if ($request->is('*export*')) {
                return 'export_reports';
            }
            return $isWrite ? 'manage_energy_incidents' : 'access_reports';
        }

        if ($request->is('modules/submeters*')) {
            if (str_contains($name, 'approve')) {
                return 'approve_submeter_readings';
            }
            if ($request->is('*alerts*')) {
                return 'view_submeter_alerts';
            }
            return 'view_submeter_monitoring';
        }

        if ($request->is('modules/energy-conservation*')) {
            if (str_contains($name, 'tasks.complete')) {
                return 'approve_conservation_tasks';
            }
            if (str_contains($name, 'tasks.store') || str_contains($name, 'tasks.delete')) {
                return 'manage_conservation_tasks';
            }
            if (str_contains($name, 'daily-checklist.save') || str_contains($name, 'tasks.progress')) {
                return 'submit_conservation_progress';
            }
            return 'access_energy_conservation';
        }

        if ($request->is('modules/maintenance*') || $request->is('maintenance*')) {
            return $isWrite ? null : 'view_maintenance';
        }

        if ($request->is('modules/facilities/*/energy-profile*')) {
            if (str_contains($name, 'toggle-approval')) {
                return 'approve_energy_profile';
            }
            if ($request->isMethod('DELETE')) {
                return 'delete_energy_profile';
            }
            return $isWrite ? 'manage_energy_profile' : 'view_facilities';
        }

        if ($request->is('modules/facilities/*/meters*')) {
            if (str_contains($name, 'toggle-approval') || str_contains($name, 'unapproved')) {
                return 'approve_facility_meters';
            }
            return $isWrite ? 'manage_facility_master' : 'view_facilities';
        }

        if ($request->is('modules/facilities/*/monthly-records*')) {
            return $isWrite ? 'encode_main_meter_readings' : 'view_energy_monitoring';
        }

        if ($request->is('facilities*') || $request->is('modules/facilities*')) {
            if (str_contains($name, 'toggle-engineer-approval')) {
                return 'approve_energy_profile';
            }
            return $isWrite ? 'manage_facility_master' : 'view_facilities';
        }

        if ($request->is('modules/energy/export*') || $request->is('modules/energy/annual/export*')) {
            return 'export_reports';
        }

        if ($request->is('modules/energy*')) {
            return $isWrite ? 'encode_main_meter_readings' : 'view_energy_monitoring';
        }

        return null;
    }
}
