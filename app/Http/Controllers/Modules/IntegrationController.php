<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Support\RoleAccess;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        abort_unless(
            RoleAccess::can(auth()->user(), 'access_settings'),
            403,
            'You do not have permission to access Integrations.'
        );

        return view('modules.integrations.index', [
            'statuses' => [
                'cimm' => filled(config('services.cimm_maintenance_sync.token')),
                'cprf' => filled(config('services.cprf_integration.token')),
                'cprf_feed' => filled(config('services.cprf_integration.facilities_feed_url')),
                'sso' => filled(config('services.sso.secret')),
            ],
        ]);
    }
}
