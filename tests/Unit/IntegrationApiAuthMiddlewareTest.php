<?php

namespace Tests\Unit;

use App\Http\Middleware\AuthenticateIntegrationApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class IntegrationApiAuthMiddlewareTest extends TestCase
{
    public function test_it_returns_service_unavailable_when_no_token_is_configured(): void
    {
        config()->set('services.integration_api.token', null);

        $response = (new AuthenticateIntegrationApi)->handle(
            Request::create('/api/v1/facilities', 'GET'),
            fn () => new JsonResponse(['ok' => true]),
        );

        $this->assertSame(503, $response->getStatusCode());
    }

    public function test_it_rejects_an_invalid_token(): void
    {
        config()->set('services.integration_api.token', 'correct-secret');
        $request = Request::create('/api/v1/facilities', 'GET');
        $request->headers->set('Authorization', 'Bearer wrong-secret');

        $response = (new AuthenticateIntegrationApi)->handle(
            $request,
            fn () => new JsonResponse(['ok' => true]),
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_it_accepts_the_configured_token(): void
    {
        config()->set('services.integration_api.token', 'correct-secret');
        $request = Request::create('/api/v1/facilities', 'GET');
        $request->headers->set('Authorization', 'Bearer correct-secret');

        $response = (new AuthenticateIntegrationApi)->handle(
            $request,
            fn () => new JsonResponse(['ok' => true]),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['ok' => true], $response->getData(true));
    }
}
