<?php

use App\Models\User;

test('staff is blocked from users and settings routes', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/modules/users')
        ->assertRedirect(route('modules.energy.index', absolute: false));

    $this->actingAs($staff)
        ->get('/modules/settings')
        ->assertRedirect(route('modules.energy.index', absolute: false));
});

test('admin is blocked from system settings route', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/modules/settings')
        ->assertStatus(403);
});

test('staff excel report export is blocked but report page is accessible', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/modules/reports/energy')
        ->assertOk();

    $this->actingAs($staff)
        ->get('/modules/reports/energy-export')
        ->assertRedirect('/modules/reports/energy');
});

test('staff energy monitoring excel exports are blocked', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/modules/energy/export-excel')
        ->assertRedirect(route('energy.exportReport', absolute: false));

    $this->actingAs($staff)
        ->get('/modules/energy/annual/export-excel')
        ->assertRedirect(route('modules.energy.annual', absolute: false));
});

test('energy officer cannot delete energy profile and cannot complete maintenance', function () {
    $officer = User::factory()->create(['role' => 'energy officer']);

    $this->actingAs($officer)
        ->delete('/modules/facilities/1/energy-profile/1')
        ->assertStatus(403);

    $this->actingAs($officer)
        ->postJson('/modules/maintenance/schedule', [
            'maintenance_status' => 'Completed',
        ])
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});
