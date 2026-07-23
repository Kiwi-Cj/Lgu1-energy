<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use Illuminate\Database\QueryException;

function validReadingPayload(Facility $facility, array $overrides = []): array
{
    return array_merge([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'previous_reading_kwh' => 500,
        'current_reading_kwh' => 620,
        'reading_date' => '2026-07-21',
    ], $overrides);
}

test('readings endpoint returns 503 when token is not configured', function () {
    config(['services.cprf_integration.token' => '']);
    $facility = Facility::factory()->create();

    $this->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility))
        ->assertStatus(503);
});

test('readings endpoint rejects a missing or wrong token', function () {
    config(['services.cprf_integration.token' => 'right-token']);
    $facility = Facility::factory()->create();

    $this->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility))
        ->assertStatus(401);

    $this->withToken('wrong-token')
        ->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility))
        ->assertStatus(401);
});

test('a valid reading stores a cprf-sourced energy record with computed kwh', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create(['baseline_kwh' => 100]);

    $response = $this->withToken('test-token')
        ->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility));

    $response->assertCreated()
        ->assertJsonPath('record.actual_kwh', 120.0)
        ->assertJsonPath('record.input_source', 'cprf');

    $this->assertDatabaseHas('energy_records', [
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'input_source' => 'cprf',
    ]);
    expect((float) EnergyRecord::first()->actual_kwh)->toBe(120.0);
});

test('pushing the same period twice updates instead of duplicating', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();

    $this->withToken('test-token')
        ->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility))
        ->assertCreated();

    $this->withToken('test-token')
        ->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility, [
            'current_reading_kwh' => 650,
        ]))
        ->assertOk();

    expect(EnergyRecord::count())->toBe(1)
        ->and((float) EnergyRecord::first()->actual_kwh)->toBe(150.0);
});

test('validation rejects a current reading below the previous one', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();

    $this->withToken('test-token')
        ->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility, [
            'current_reading_kwh' => 400,
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['current_reading_kwh']);
});

test('validation rejects an unknown facility', function () {
    config(['services.cprf_integration.token' => 'test-token']);
    $facility = Facility::factory()->create();

    $this->withToken('test-token')
        ->postJson('/api/v1/cprf/facility-readings', validReadingPayload($facility, [
            'facility_id' => 999999,
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['facility_id']);
});

test('database blocks duplicate active facility-level periods', function () {
    $facility = Facility::factory()->create();

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 100,
    ]);

    expect(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 100,
    ]))->toThrow(QueryException::class);
});

test('soft-deleted period can be re-created', function () {
    $facility = Facility::factory()->create();

    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 100,
    ]);
    $record->delete();

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 120,
    ]);

    expect(EnergyRecord::withTrashed()->count())->toBe(2);
});
