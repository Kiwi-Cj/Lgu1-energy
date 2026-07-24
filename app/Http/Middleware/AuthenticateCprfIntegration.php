<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCprfIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = trim((string) config('services.cprf_integration.token', ''));

        if ($configuredToken === '') {
            return new JsonResponse([
                'message' => 'The CPRF integration API is not configured.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $providedToken = (string) $request->bearerToken();

        if ($providedToken === '' || ! hash_equals($configuredToken, $providedToken)) {
            return new JsonResponse([
                'message' => 'Invalid or missing CPRF integration token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
