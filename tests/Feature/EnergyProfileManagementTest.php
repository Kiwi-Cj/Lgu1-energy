<?php

use App\Models\EnergyProfile;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

function validEnergyProfilePayload(array $overrides = []): array
{
    return array_merge([
        'electric_meter_no' => 'FORM-METER-001',
        'utility_provider' => 'Meralco',
        'contract_account_no' => 'CA-10001',
        'baseline_kwh' => 100,
        'main_energy_source' => 'Electricity',
        'backup_power' => 'None',
        'transformer_capacity' => '50 kVA',
        'number_of_meters' => 99,
        'baseline_source' => 'manual_entry',
    ], $overrides);
}

test('an energy profile uses approved primary meter facts and only one profile is allowed per facility', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create();
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Billing Meter',
        'meter_number' => 'REGISTERED-001',
        'meter_type' => 'main',
        'status' => 'active',
        'multiplier' => 1,
        'baseline_kwh' => 725.50,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('modules.facilities.energy-profile.store', $facility->id), validEnergyProfilePayload([
            'primary_meter_id' => $meter->id,
        ]))
        ->assertRedirect();

    $profile = EnergyProfile::where('facility_id', $facility->id)->sole();
    expect($profile->electric_meter_no)->toBe('REGISTERED-001')
        ->and((float) $profile->baseline_kwh)->toBe(725.5)
        ->and($profile->baseline_source)->toBe('main_meter')
        ->and($profile->number_of_meters)->toBe(1);

    $this->actingAs($admin)
        ->post(route('modules.facilities.energy-profile.store', $facility->id), validEnergyProfilePayload([
            'primary_meter_id' => $meter->id,
            'contract_account_no' => 'CA-SECOND',
        ]))
        ->assertRedirect(route('modules.facilities.energy-profile.index', $facility->id))
        ->assertSessionHas('error');

    expect(EnergyProfile::where('facility_id', $facility->id)->count())->toBe(1);
});

test('an energy profile cannot be updated through another facility URL', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = Facility::factory()->create();
    $otherFacility = Facility::factory()->create();
    $profile = EnergyProfile::create(array_merge(validEnergyProfilePayload(), [
        'facility_id' => $owner->id,
    ]));

    $this->actingAs($admin)
        ->put(route('modules.facilities.energy-profile.update', [$otherFacility->id, $profile->id]), validEnergyProfilePayload())
        ->assertNotFound();

    expect($profile->fresh()->contract_account_no)->toBe('CA-10001');
});

test('the energy profile page exposes the LGU managed profile action', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['source' => 'cprf']);

    $this->actingAs($admin)
        ->get(route('modules.facilities.energy-profile.index', $facility->id))
        ->assertOk()
        ->assertSee('LGU-managed information')
        ->assertSee('Add Profile');
});
