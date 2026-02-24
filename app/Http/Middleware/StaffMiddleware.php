<?php
namespace App\Http\Middleware;

use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $role = RoleAccess::normalize($user);

        if (! RoleAccess::in($user, ['staff', 'energy_officer'])) {
            return $next($request);
        }

        if ($this->isBlockedForRestrictedRoles($request, $user)) {
            return $this->deny($request, 'You do not have permission to access this page.');
        }

        // Facility assignment scoping only applies to staff. Energy officers can access facility pages,
        // but are still blocked from facility master-data create/edit/delete actions above.
        if ($role !== 'staff') {
            return $next($request);
        }

        $facilityId = $this->resolveFacilityId($request);
        if ($facilityId !== null) {
            $hasAccess = $user->facilities()->where('facilities.id', $facilityId)->exists();
            if (! $hasAccess) {
                return $this->deny($request, 'You do not have access to this facility.', 'modules.facilities.index');
            }
        }

        return $next($request);
    }

    private function isBlockedForRestrictedRoles(Request $request, $user): bool
    {
        if (
            (($request->is('modules/users*') || $request->is('users/roles*')) && ! RoleAccess::can($user, 'access_users')) ||
            ($request->is('modules/settings*') && ! RoleAccess::can($user, 'access_settings'))
        ) {
            return true;
        }

        if (
            (
                $request->is('modules/facilities/create') ||
                $request->is('modules/facilities/*/edit') ||
                $request->is('facilities/create') ||
                $request->is('facilities/*/edit')
            ) &&
            ! RoleAccess::can($user, 'manage_facility_master')
        ) {
            return true;
        }

        if (
            ($request->is('facilities') && $request->isMethod('post')) ||
            ($request->is('facilities/*') && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'], true))
        ) {
            return ! RoleAccess::can($user, 'manage_facility_master');
        }

        return false;
    }

    private function resolveFacilityId(Request $request): ?int
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        $facilityParam = $route->parameter('facility');
        if (is_object($facilityParam) && isset($facilityParam->id)) {
            return (int) $facilityParam->id;
        }
        if (is_numeric($facilityParam) && $this->pathUsesFacilityParam($request)) {
            return (int) $facilityParam;
        }

        $idParam = $route->parameter('id');
        if (is_object($idParam) && isset($idParam->id)) {
            return (int) $idParam->id;
        }
        if (is_numeric($idParam) && $this->pathUsesGenericFacilityId($request)) {
            return (int) $idParam;
        }

        return null;
    }

    private function pathUsesFacilityParam(Request $request): bool
    {
        return $request->is('modules/facilities/*/monthly-records*')
            || $request->is('modules/facilities/*/energy-profile*')
            || $request->is('modules/facilities/*/modal-detail');
    }

    private function pathUsesGenericFacilityId(Request $request): bool
    {
        return $request->is('modules/facilities/*/show')
            || ($request->is('facilities/*') && ! $request->is('facilities/*/edit'));
    }

    private function deny(Request $request, string $message, string $routeName = 'modules.energy.index')
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route($routeName)->with('error', $message);
    }
}

