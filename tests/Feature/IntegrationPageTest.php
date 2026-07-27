<?php

use App\Models\User;

test('super admin can view the integrations documentation page', function () {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);

    $response = $this->actingAs($superAdmin)->get(route('integrations.index'));

    $response
        ->assertOk()
        ->assertSee('Connected Systems &amp; API Processes', escape: false)
        ->assertSee('CIMM Maintenance Sync')
        ->assertSee('CPRF Facilities Reservation')
        ->assertSee('Main LGU Single Sign-On')
        ->assertDontSee('Submeter IoT Ingestion')
        ->assertSee('/api/v1/cprf/facility-readings');
});

test('non super admin cannot view the integrations documentation page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('integrations.index'))
        ->assertForbidden();
});
