<?php

use App\Models\EnergySavingRecommendation;
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

test('recommendations endpoint requires the cprf token', function () {
    config(['services.cprf_integration.token' => 'right-token']);

    $this->getJson('/api/v1/cprf/recommendations')->assertStatus(401);
});

test('recommendations default to approved status only', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();
    makeRecommendation($facility);
    makeRecommendation($facility, ['month' => 7, 'status' => 'for_review']);

    $response = $this->withToken('test-token')->getJson('/api/v1/cprf/recommendations');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('approved')
        ->and($response->json('data.0.facility.id'))->toBe($facility->id);
});

test('recommendations can be filtered by facility, period, status and updated_since', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facilityA = Facility::factory()->create();
    $facilityB = Facility::factory()->create();
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
