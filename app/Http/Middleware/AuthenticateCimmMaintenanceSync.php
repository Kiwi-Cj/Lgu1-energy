<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the CIMM <-> Energy maintenance sync routes (services.cimm_maintenance_sync.token).
 * Deliberately separate from AuthenticateIntegrationApi/services.integration_api.token:
 * that token gates read access to the general integration API (facilities, meters,
 * energy records, incidents...) and may already be depended on elsewhere with a
 * real secret configured. This token is scoped to just the maintenance sync
 * integration and defaults to a shared dev key so it works out of the box.
 */
class AuthenticateCimmMaintenanceSync
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = trim((string) config('services.cimm_maintenance_sync.token', ''));

        if ($configuredToken === '') {
            return new JsonResponse([
                'message' => 'The CIMM maintenance sync integration is not configured.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $providedToken = (string) $request->bearerToken();

        if ($providedToken === '' || !hash_equals($configuredToken, $providedToken)) {
            return new JsonResponse([
                'message' => 'Invalid or missing CIMM maintenance sync token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
