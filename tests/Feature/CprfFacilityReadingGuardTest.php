<?php

use App\Models\Facility;
use App\Models\EnergyRecord;

test('cprf facility reading handles dial rollover smoothly', function () {
    config(['services.cprf_integration.token' => 'test-cprf-token']);

    $facility = Facility::factory()->create(['name' => 'Culiat Basketball Gym']);

    // Meter at 99,800 rolled over to 350 on a 100k capacity meter
    $response = $this->withToken('test-cprf-token')
        ->postJson('/api/v1/cprf/facility-readings', [
            'facility_id' => $facility->id,
            'year' => 2026,
            'month' => 9,
            'previous_reading_kwh' => 99800,
            'current_reading_kwh' => 350,
            'is_rollover' => true,
            'dial_capacity' => 100000,
            'reading_date' => '2026-09-30',
        ]);

    $response->assertCreated();
    // (100,000 - 99,800) + 350 = 550 kWh
    $response->assertJsonPath('record.actual_kwh', 550.0);
    $response->assertJsonPath('guards.is_rollover', true);
});

test('cprf facility reading rejects lower current reading without rollover or reset flag', function () {
    config(['services.cprf_integration.token' => 'test-cprf-token']);

    $facility = Facility::factory()->create(['name' => 'Culiat Daycare']);

    $response = $this->withToken('test-cprf-token')
        ->postJson('/api/v1/cprf/facility-readings', [
            'facility_id' => $facility->id,
            'year' => 2026,
            'month' => 9,
            'previous_reading_kwh' => 5000,
            'current_reading_kwh' => 4000,
            'reading_date' => '2026-09-30',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['current_reading_kwh']);
});

test('cprf facility reading handles meter replacement reset', function () {
    config(['services.cprf_integration.token' => 'test-cprf-token']);

    $facility = Facility::factory()->create(['name' => 'Culiat Health Clinic']);

    // Meter was replaced; old meter was at 45,000, new meter is at 250
    $response = $this->withToken('test-cprf-token')
        ->postJson('/api/v1/cprf/facility-readings', [
            'facility_id' => $facility->id,
            'year' => 2026,
            'month' => 9,
            'previous_reading_kwh' => 45000,
            'current_reading_kwh' => 250,
            'is_meter_reset' => true,
            'reading_date' => '2026-09-30',
        ]);

    $response->assertCreated();
    $response->assertJsonPath('record.actual_kwh', 250.0);
    $response->assertJsonPath('guards.is_meter_reset', true);
});
