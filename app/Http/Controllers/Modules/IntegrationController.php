<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Cache;
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

        $umanConfigured = filled(config('services.uman_monthly_records.url'))
            && filled(config('services.uman_monthly_records.key'));
        $umanSync = Cache::get('integrations.uman_monthly_records', []);

        return view('modules.integrations.index', [
            'statuses' => [
                'cimm' => filled(config('services.cimm_maintenance_sync.token')),
                'cprf' => filled(config('services.cprf_integration.token')),
                'cprf_feed' => filled(config('services.cprf_integration.facilities_feed_url')),
                'sso' => filled(config('services.sso.secret')),
                'uman' => $umanConfigured,
            ],
            'umanSync' => $umanSync,
        ]);
    }
}
