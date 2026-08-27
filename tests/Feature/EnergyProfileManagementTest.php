<?php

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
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

test('a new facility can save its energy profile and readings without a baseline', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create();
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'New Facility Main Meter',
        'meter_number' => 'NEW-MAIN-001',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('modules.facilities.energy-profile.store', $facility->id), validEnergyProfilePayload([
            'primary_meter_id' => $meter->id,
            'baseline_kwh' => null,
        ]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(EnergyProfile::where('facility_id', $facility->id)->sole()->baseline_kwh)->toBeNull();
});

test('approved monthly readings can establish a three to six month main meter baseline', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create();
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Baseline Builder Main Meter',
        'meter_number' => 'BASE-MAIN-001',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_at' => now(),
    ]);
    $profile = EnergyProfile::create(array_merge(validEnergyProfilePayload([
        'primary_meter_id' => $meter->id,
        'baseline_kwh' => null,
        'baseline_source' => null,
    ]), ['facility_id' => $facility->id]));

    foreach ([1 => 900, 2 => 1000, 3 => 1100] as $month => $actualKwh) {
        EnergyRecord::create([
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => $month,
            'actual_kwh' => $actualKwh,
            'review_status' => 'approved',
        ]);
    }

    $baselineSummary = app(\App\Services\MainMeterBaselineEstablishmentService::class)->summary($meter->fresh());
    expect($baselineSummary['status'])->toBe('preliminary_ready')
        ->and($baselineSummary['usable_reading_count'])->toBe(3)
        ->and($baselineSummary['candidate_kwh'])->toBe(1000.0);

    $page = $this->actingAs($admin)
        ->get(route('modules.facilities.energy-profile.index', $facility->id));
    expect($page->status())->toBe(200)
        ->and(str_contains($page->getContent(), 'Preliminary baseline is ready'))->toBeTrue()
        ->and(str_contains($page->getContent(), 'Approve baseline'))->toBeTrue();

    $this->actingAs($admin)
        ->post(route('modules.facilities.meters.baseline.establish', [$facility->id, $meter->id]), [
            'baseline_months' => 3,
        ])
        ->assertRedirect(route('modules.facilities.energy-profile.index', $facility->id))
        ->assertSessionHasNoErrors();

    $updatedProfile = $profile->fresh();
    expect((float) $meter->fresh()->baseline_kwh)->toBe(1000.0)
        ->and((float) $updatedProfile->baseline_kwh)->toBe(1000.0)
        ->and($updatedProfile->baseline_locked)->toBeTrue()
        ->and($updatedProfile->engineer_approved)->toBeTrue()
        ->and($updatedProfile->baseline_source)->toBe('computed_3_month_average');
});

test('baseline establishment counts approved readings only', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create();
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Approval Gate Main Meter',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_at' => now(),
    ]);

    foreach ([1, 2, 3] as $month) {
        EnergyRecord::create([
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => $month,
            'actual_kwh' => 1000 + $month,
            'review_status' => $month === 3 ? 'for_review' : 'approved',
        ]);
    }

    $this->actingAs($admin)
        ->post(route('modules.facilities.meters.baseline.establish', [$facility->id, $meter->id]), [
            'baseline_months' => 3,
        ])
        ->assertSessionHasErrors('baseline_months');

    expect($meter->fresh()->baseline_kwh)->toBeNull();
});
