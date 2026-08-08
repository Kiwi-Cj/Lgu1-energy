<?php

use App\Models\EnergySavingRecommendation;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;

function makeRecommendation(Facility $facility, array $overrides = []): EnergySavingRecommendation
{
    return EnergySavingRecommendation::create(array_merge([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 6,
        'generated_message' => 'Shift aircon pre-cooling 30 minutes later.',
        'status' => 'approved',
    ], $overrides));
}

function makeEnergyOwnedRecord(Facility $facility, int $month = 6, string $reviewStatus = 'approved', string $inputSource = 'manual'): EnergyRecord
{
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => "Main Meter {$month}",
        'meter_number' => "MAIN-{$facility->id}-{$month}",
        'meter_type' => 'main',
        'status' => 'active',
        'approved_at' => now(),
    ]);

    return EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => $month,
        'day' => 28,
        'actual_kwh' => 1200,
        'input_source' => $inputSource,
        'review_status' => $reviewStatus,
    ]);
}

test('recommendations endpoint requires the cprf token', function () {
    config(['services.cprf_integration.token' => 'right-token']);

    $this->getJson('/api/v1/cprf/recommendations')->assertStatus(401);
});

test('recommendations default to approved status only', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create(['source' => 'cprf']);
    makeEnergyOwnedRecord($facility, 6);
    makeEnergyOwnedRecord($facility, 7);
    makeRecommendation($facility);
    makeRecommendation($facility, ['month' => 7, 'status' => 'for_review']);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/recommendations');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('approved')
        ->and($response->json('data.0.facility.id'))->toBe($facility->id);
});

test('approved recommendations for UMAN imported CPRF readings are exposed to CPRF', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create(['source' => 'cprf']);
    makeEnergyOwnedRecord($facility, 8, 'approved', 'cprf');
    makeRecommendation($facility, [
        'month' => 8,
        'engineer_recommendation' => 'Reduce lighting use outside booked hours.',
    ]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/recommendations?month=8');

    $response->assertOk()
        ->assertJsonPath('data.0.engineer_recommendation', 'Reduce lighting use outside booked hours.')
        ->assertJsonPath('data.0.monthly_record_assessment', 'Shift aircon pre-cooling 30 minutes later.')
        ->assertJsonPath('data.0.recommendation', 'Reduce lighting use outside booked hours.');
});

test('cprf can sync recommendation implementation progress back to Energy', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create(['source' => 'cprf']);
    makeEnergyOwnedRecord($facility);
    $recommendation = makeRecommendation($facility);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$recommendation->id}/implementation", [
            'implementation_status' => 'in_progress',
            'actual_savings_kwh' => 125.5,
            'implementation_notes' => 'Adjusted operating hours after the last booking.',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('recommendation.implementation_status', 'in_progress')
        ->assertJsonPath('recommendation.actual_savings_kwh', 125.5);

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'id' => $recommendation->id,
        'implementation_status' => 'in_progress',
        'actual_savings_kwh' => 125.5,
        'implementation_notes' => 'Adjusted operating hours after the last booking.',
    ]);
});

test('cprf cannot update an ineligible or Energy verified recommendation', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $localFacility = Facility::factory()->create(['source' => 'local']);
    makeEnergyOwnedRecord($localFacility);
    $localRecommendation = makeRecommendation($localFacility);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$localRecommendation->id}/implementation", [
            'implementation_status' => 'implemented',
        ])
        ->assertNotFound();

    $cprfFacility = Facility::factory()->create(['source' => 'cprf']);
    makeEnergyOwnedRecord($cprfFacility);
    $verifiedRecommendation = makeRecommendation($cprfFacility, [
        'implementation_status' => 'verified',
        'verified_at' => now(),
    ]);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$verifiedRecommendation->id}/implementation", [
            'implementation_status' => 'in_progress',
        ])
        ->assertStatus(409);
});

test('cprf cannot receive or manage recommendations while the monthly record is for review', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create(['source' => 'cprf']);
    makeEnergyOwnedRecord($facility, 6, 'for_review');
    $recommendation = makeRecommendation($facility);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/recommendations');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);

});

test('recommendations can be filtered by facility, period, status and updated_since', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facilityA = Facility::factory()->create(['source' => 'cprf']);
    $facilityB = Facility::factory()->create(['source' => 'cprf']);
    makeEnergyOwnedRecord($facilityA, 6);
    makeEnergyOwnedRecord($facilityB, 5);
    makeRecommendation($facilityA);
    makeRecommendation($facilityB, ['month' => 5]);

    $byFacility = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/recommendations?facility_id=' . $facilityA->id);
    expect($byFacility->json('data'))->toHaveCount(1);

    $byStatus = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/recommendations?status=for_review');
    expect($byStatus->json('data'))->toHaveCount(0);

    $future = now()->addDay()->toIso8601String();
    $sinceFuture = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/recommendations?updated_since=' . urlencode($future));
    expect($sinceFuture->json('data'))->toHaveCount(0);
});

test('cprf facilities listing excludes local Energy facilities', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $localFacility = Facility::factory()->create(['source' => 'local', 'external_ref' => null]);
    $cprfFacility = Facility::factory()->create(['source' => 'cprf', 'external_ref' => 8812]);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facilities');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($cprfFacility->id)
        ->and(collect($response->json('data'))->pluck('id'))->not->toContain($localFacility->id);
});

test('cprf energy reports expose only approved Energy-owned records for CPRF facilities', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $cprfFacility = Facility::factory()->create(['source' => 'cprf']);
    $approvedRecord = makeEnergyOwnedRecord($cprfFacility, 6, 'approved');
    makeEnergyOwnedRecord($cprfFacility, 7, 'for_review');

    $localFacility = Facility::factory()->create(['source' => 'local']);
    makeEnergyOwnedRecord($localFacility, 6, 'approved');

    $response = $this->withToken('test-token')
        ->getJson('/api/v1/cprf/energy-reports?year=2026');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($approvedRecord->id)
        ->and($response->json('data.0.review_status'))->toBe('approved');
});
