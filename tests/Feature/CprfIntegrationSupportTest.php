<?php

use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\Facility;

test('facility baseline resolves from latest energy profile first', function () {
    $facility = Facility::factory()->create(['baseline_kwh' => 500]);
    EnergyProfile::create(['facility_id' => $facility->id, 'baseline_kwh' => 750]);

    expect($facility->fresh()->resolveBaselineKwh())->toBe(750.0);
});

test('facility baseline falls back to facility column then null', function () {
    $withColumn = Facility::factory()->create(['baseline_kwh' => 500]);
    $without = Facility::factory()->create(['baseline_kwh' => null]);

    expect($withColumn->resolveBaselineKwh())->toBe(500.0)
        ->and($without->resolveBaselineKwh())->toBeNull();
});

test('energy records accept an input_source value', function () {
    $facility = Facility::factory()->create();

    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 120,
        'input_source' => 'cprf',
    ]);

    expect($record->fresh()->input_source)->toBe('cprf');
});
