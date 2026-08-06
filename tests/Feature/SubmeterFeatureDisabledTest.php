<?php

use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

beforeEach(function () {
    config()->set('features.submeters_enabled', false);
});

test('submeter monitoring and sensor endpoints are unavailable while disabled', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)
        ->get(route('modules.submeters.monitoring'))
        ->assertNotFound();

    $this->get('/api/submeter/sensor-readings')
        ->assertNotFound();
});

test('a submeter cannot be created while the feature is disabled', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create();
    $mainMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->postJson(route('modules.facilities.meters.store', $facility->id), [
            'meter_name' => 'Disabled Submeter',
            'meter_type' => 'sub',
            'parent_meter_id' => $mainMeter->id,
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('meter_type');

    $this->assertDatabaseMissing('facility_meters', [
        'facility_id' => $facility->id,
        'meter_name' => 'Disabled Submeter',
    ]);
});

test('energy profile exposes main meter controls without a submeter directory link', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create();
    FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('modules.facilities.energy-profile.index', $facility->id))
        ->assertOk()
        ->assertSee('Add Main Meter')
        ->assertSee('data-meter-submeters-page-url=""', false)
        ->assertDontSee('id="meterDetailSubmetersBtn"', false);
});
