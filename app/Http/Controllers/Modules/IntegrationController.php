<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Services\CprfFacilitySyncService;
use App\Support\RoleAccess;
use Illuminate\Http\RedirectResponse;
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
        $cimmSync = Cache::get('integrations.cimm_maintenance', []);

        return view('modules.integrations.index', [
            'statuses' => [
                'cimm' => filled(config('services.cimm_maintenance_sync.token')),
                'cprf' => filled(config('services.cprf_integration.token')),
                'cprf_feed' => filled(config('services.cprf_integration.facilities_feed_url')),
                'sso' => filled(config('services.sso.secret')),
                'uman' => $umanConfigured,
            ],
            'umanSync' => $umanSync,
            'cimmSync' => $cimmSync,
        ]);
    }

    public function syncCimm(): RedirectResponse
    {
        $this->ensureIntegrationAccess();

        if (! filled(config('services.cimm_maintenance_sync.token'))) {
            return redirect()->route('integrations.index')
                ->with('error', 'CIMM sync is not configured. Add the CIMM maintenance sync token first.');
        }

        $records = Maintenance::query()->count();
        Cache::put('integrations.cimm_maintenance', [
            'last_synced_at' => now()->toIso8601String(),
            'records' => $records,
        ], now()->addDays(30));

        return redirect()->route('integrations.index')
            ->with('success', "CIMM sync refreshed. The latest {$records} maintenance record(s) are ready for CIMM.");
    }

    public function syncCprf(CprfFacilitySyncService $service): RedirectResponse
    {
        $this->ensureIntegrationAccess();
        $result = $service->sync();

        if (! $result['success']) {
            return redirect()->route('integrations.index')
                ->with('error', 'CPRF sync failed: ' . ($result['error'] ?? 'unknown error'));
        }

        return redirect()->route('integrations.index')->with('success', sprintf(
            'CPRF facilities synced: %d added, %d updated, %d deactivated, %d unchanged.',
            $result['created'], $result['updated'], $result['deactivated'], $result['unchanged']
        ));
    }

    private function ensureIntegrationAccess(): void
    {
        abort_unless(RoleAccess::can(auth()->user(), 'access_settings'), 403, 'You do not have permission to manage Integrations.');
    }
}
