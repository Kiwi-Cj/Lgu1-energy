<?php

use App\Models\EnergyProfile;
use App\Models\Facility;

function makeCprfMappedFacility(array $overrides = []): Facility
{
    return Facility::factory()->create(array_merge([
        'source' => 'cprf',
    ], $overrides));
}

test('facility-profiles endpoint requires the cprf token', function () {
    config(['services.cprf_integration.token' => 'right-token']);

    $this->getJson('/api/v1/cprf/facility-profiles')->assertStatus(401);
});

test('facility-profiles returns only cprf-mapped facilities that have a profile', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $withProfile = makeCprfMappedFacility(['external_ref' => 501]);
    $profile = EnergyProfile::create([
        'facility_id' => $withProfile->id,
        'utility_provider' => 'Meralco',
        'contract_account_no' => '1234-5678',
        'baseline_kwh' => 7820,
        'main_energy_source' => 'Grid',
        'backup_power' => 'Generator',
        'number_of_meters' => 3,
    ]);
    $profile->engineer_approved = true;
    $profile->baseline_locked = true;
    $profile->save();

    makeCprfMappedFacility(['external_ref' => 502]); // no profile yet — must be omitted
    Facility::factory()->create(['source' => 'local']); // not cprf-mapped — must be omitted

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.facility_external_ref'))->toBe(501)
        ->and($response->json('data.0.utility_provider'))->toBe('Meralco')
        ->and($response->json('data.0.contract_account_no'))->toBe('1234-5678')
        ->and($response->json('data.0.baseline_kwh'))->toBe(7820.0)
        ->and($response->json('data.0.engineer_approved'))->toBeTrue()
        ->and($response->json('data.0.baseline_locked'))->toBeTrue();
});

test('facility-profiles omits facilities with no external_ref even if source is cprf', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = Facility::factory()->create(['source' => 'cprf', 'external_ref' => null]);
    EnergyProfile::create(['facility_id' => $facility->id, 'utility_provider' => 'Meralco']);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facility-profiles');

    expect($response->json('data'))->toHaveCount(0);
});

test('facility-profiles can be filtered by updated_since', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = makeCprfMappedFacility(['external_ref' => 501]);
    EnergyProfile::create(['facility_id' => $facility->id, 'utility_provider' => 'Meralco']);

    $future = now()->addDay()->toIso8601String();
    $response = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/facility-profiles?updated_since=' . urlencode($future));

    expect($response->json('data'))->toHaveCount(0);
});
