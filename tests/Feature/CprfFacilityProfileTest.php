<?php

use App\Models\EnergyProfile;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

function makeCprfMappedFacility(array $overrides = []): Facility
{
    return Facility::factory()->create(array_merge([
        'source' => 'cprf',
    ], $overrides));
}

function makeMainMeter(Facility $facility, array $overrides = []): FacilityMeter
{
    return FacilityMeter::create(array_merge([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter',
        'meter_type' => 'main',
        'status' => 'active',
        'multiplier' => 1,
    ], $overrides));
}

test('facility-profiles endpoint requires the cprf token', function () {
    config(['services.cprf_integration.token' => 'right-token']);

    $this->getJson('/api/v1/cprf/facility-profiles')->assertStatus(401);
});

test('facility-profiles reads engineer_approved and baseline_kwh from the approved main meter, not the profile', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    $meter = makeMainMeter($facility, [
        'meter_name' => 'Bernardo Court Main Meter',
        'meter_number' => 'CPRF-MTR-001',
        'baseline_kwh' => 4200,
        'approved_at' => now(),
        'approved_by_user_id' => null,
    ]);
    // Profile's own baseline_kwh/engineer_approved must NOT be used — this
    // profile is never editable through any UI in this app, so it can
    // never reflect real engineer sign-off.
    EnergyProfile::create([
        'facility_id' => $facility->id,
        'primary_meter_id' => $meter->id,
        'electric_meter_no' => 'N/A',
        'utility_provider' => 'Meralco',
        'contract_account_no' => '1234-5678',
        'baseline_kwh' => 1200, // deliberately different from the meter's baseline
    ]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.facility_external_ref'))->toBe(501)
        ->and($response->json('data.0.utility_provider'))->toBe('Meralco') // still from profile
        ->and($response->json('data.0.contract_account_no'))->toBe('1234-5678') // still from profile
        ->and($response->json('data.0.electric_meter_no'))->toBe('CPRF-MTR-001')
        ->and($response->json('data.0.main_meter_name'))->toBe('Bernardo Court Main Meter')
        ->and($response->json('data.0.baseline_kwh'))->toBe(4200.0) // from the meter, not the profile's 1200
        ->and($response->json('data.0.engineer_approved'))->toBeTrue(); // from the meter's approved_at
});

test('CPRF facility details show the registered main meter number instead of the profile placeholder', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    $meter = makeMainMeter($facility, [
        'meter_number' => 'CPRF-MTR-DETAIL-001',
        'baseline_kwh' => 4500,
        'approved_at' => now(),
    ]);
    EnergyProfile::create([
        'facility_id' => $facility->id,
        'primary_meter_id' => $meter->id,
        'electric_meter_no' => 'N/A',
        'baseline_kwh' => 4500,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.facilities.show', $facility->id))
        ->assertOk()
        ->assertSee('Electric Meter No.')
        ->assertSee('CPRF-MTR-DETAIL-001');
});

test('CPRF energy profile identifies both facility and energy-reading integration sources', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = makeCprfMappedFacility(['external_ref' => 502]);
    makeMainMeter($facility, [
        'meter_number' => 'CPRF-MTR-SOURCE-001',
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('modules.facilities.energy-profile.index', $facility->id))
        ->assertOk()
        ->assertSee('Facility source:')
        ->assertSee('CPRF')
        ->assertSee('Energy readings:')
        ->assertSee('via UMAN');
});

test('facility-profiles shows engineer_approved false when the main meter is not yet approved', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    makeMainMeter($facility, ['baseline_kwh' => 1000, 'approved_at' => null]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.engineer_approved'))->toBeFalse()
        ->and($response->json('data.0.baseline_kwh'))->toBe(1000.0); // still shown even though unapproved
});

test('facility-profiles prefers an approved main meter over a newer unapproved one', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    makeMainMeter($facility, [
        'meter_name' => 'Approved Main Meter',
        'baseline_kwh' => 1200,
        'approved_at' => now()->subDay(),
    ]);
    // Added after the approved one (e.g. a duplicate created during testing)
    // — should not shadow the approved meter's figures.
    makeMainMeter($facility, [
        'meter_name' => 'Newer Pending Main Meter',
        'baseline_kwh' => 4200,
        'approved_at' => null,
    ]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.main_meter_name'))->toBe('Approved Main Meter')
        ->and($response->json('data.0.baseline_kwh'))->toBe(1200.0)
        ->and($response->json('data.0.engineer_approved'))->toBeTrue();
});

test('facility-profiles shows a facility with a main meter but no energy profile', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    makeMainMeter($facility, ['baseline_kwh' => 800, 'approved_at' => now()]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.baseline_kwh'))->toBe(800.0)
        ->and($response->json('data.0.engineer_approved'))->toBeTrue()
        ->and($response->json('data.0.utility_provider'))->toBeNull();
});

test('facility-profiles omits facilities with no external_ref even if source is cprf', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = Facility::factory()->create(['source' => 'cprf', 'external_ref' => null]);
    EnergyProfile::create(['facility_id' => $facility->id, 'utility_provider' => 'Meralco']);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    expect($response->json('data'))->toHaveCount(0);
});

test('facility-profiles omits facilities with neither a main meter nor a profile', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    makeCprfMappedFacility(['external_ref' => 501]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    expect($response->json('data'))->toHaveCount(0);
});

test('facility-profiles updated_since catches a meter approval even with no profile change', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    makeMainMeter($facility, ['baseline_kwh' => 500, 'approved_at' => now()]);

    $future = now()->addDay()->toIso8601String();
    $response = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/facility-profiles?updated_since=' . urlencode($future));

    expect($response->json('data'))->toHaveCount(0);

    $past = now()->subDay()->toIso8601String();
    $response = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/facility-profiles?updated_since=' . urlencode($past));

    expect($response->json('data'))->toHaveCount(1);
});
