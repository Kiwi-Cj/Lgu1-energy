<?php

use App\Models\EnergySavingRecommendation;
use App\Models\EnergyRecord;
use App\Models\Facility;

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

function makeCprfRecord(Facility $facility, int $month = 6, string $reviewStatus = 'approved'): EnergyRecord
{
    return EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => $month,
        'day' => 28,
        'actual_kwh' => 1200,
        'input_source' => 'cprf',
        'review_status' => $reviewStatus,
    ]);
}

test('recommendations endpoint requires the cprf token', function () {
    config(['services.cprf_integration.token' => 'right-token']);

    $this->getJson('/api/v1/cprf/recommendations')->assertStatus(401);
});

test('recommendations default to approved status only', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();
    makeCprfRecord($facility, 6);
    makeCprfRecord($facility, 7);
    makeRecommendation($facility);
    makeRecommendation($facility, ['month' => 7, 'status' => 'for_review']);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/recommendations');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('approved')
        ->and($response->json('data.0.facility.id'))->toBe($facility->id);
});

test('cprf can update implementation progress but cannot verify recommendations', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();
    makeCprfRecord($facility);
    $recommendation = makeRecommendation($facility, [
        'implementation_status' => 'pending',
    ]);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$recommendation->id}/implementation", [
            'implementation_status' => 'implemented',
            'actual_savings_kwh' => 84.5,
            'implementation_notes' => 'Lighting schedule was corrected.',
        ])
        ->assertOk()
        ->assertJsonPath('recommendation.implementation_status', 'implemented')
        ->assertJsonPath('recommendation.actual_savings_kwh', 84.5);

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'id' => $recommendation->id,
        'implementation_status' => 'implemented',
        'actual_savings_kwh' => 84.5,
        'implementation_notes' => 'Lighting schedule was corrected.',
    ]);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$recommendation->id}/implementation", [
            'implementation_status' => 'verified',
        ])
        ->assertUnprocessable();
});

test('cprf cannot update a recommendation that did not come from a cprf reading', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();
    $recommendation = makeRecommendation($facility);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$recommendation->id}/implementation", [
            'implementation_status' => 'in_progress',
        ])
        ->assertStatus(409);
});

test('cprf cannot receive or manage recommendations while the monthly record is for review', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();
    makeCprfRecord($facility, 6, 'for_review');
    $recommendation = makeRecommendation($facility);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/recommendations');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(0);

    $this->withToken('test-token')
        ->patchJson("/api/v1/cprf/recommendations/{$recommendation->id}/implementation", [
            'implementation_status' => 'in_progress',
        ])
        ->assertStatus(409);
});

test('recommendations can be filtered by facility, period, status and updated_since', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facilityA = Facility::factory()->create();
    $facilityB = Facility::factory()->create();
    makeCprfRecord($facilityA, 6);
    makeCprfRecord($facilityB, 5);
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

test('cprf facilities endpoint lists facilities with the shared token', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    Facility::factory()->count(2)->create();

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/facilities');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});
