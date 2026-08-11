<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('super admin can view the integrations documentation page', function () {
    config([
        'services.uman_monthly_records.url' => 'https://uman.test/api/monthly-energy-records.php',
        'services.uman_monthly_records.key' => 'test-key',
    ]);
    Cache::put('integrations.uman_monthly_records', [
        'state' => 'connected',
        'last_attempt_at' => now()->toIso8601String(),
        'message' => 'UMAN records synchronized successfully.',
    ]);
    $superAdmin = User::factory()->create(['role' => 'super_admin']);

    $response = $this->actingAs($superAdmin)->get(route('integrations.index'));

    $response
        ->assertOk()
        ->assertSee('System Integrations')
        ->assertSee('CIMM Maintenance Sync')
        ->assertSee('CPRF Facilities Reservation')
        ->assertSee('Main LGU Single Sign-On')
        ->assertDontSee('Submeter IoT Ingestion')
        ->assertDontSee('/api/v1/cprf/facility-readings')
        ->assertSee('/api/v1/cprf/energy-reports')
        ->assertSee('UMAN Monthly Energy Records')
        ->assertSee('Connected')
        ->assertSee('Energy-owned records');
});

test('non super admin cannot view the integrations documentation page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('integrations.index'))
        ->assertForbidden();
});
