<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;
use App\Services\EnergyPerformanceSummaryService;

test('performance summary works without floor area', function () {
    $facility = Facility::factory()->create(['source' => 'cprf', 'floor_area' => null, 'floor_area_sqm' => null]);
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 1200,
        'baseline_kwh' => 1000,
        'review_status' => 'approved',
        'input_source' => 'cprf',
    ]));

    $row = app(EnergyPerformanceSummaryService::class)->build(collect([$facility]))['rows'][0];

    expect($row['actual_kwh'])->toBe(1200.0)
        ->and($row['baseline_kwh'])->toBe(1000.0)
        ->and($row['deviation'])->toBe(20.0)
        ->and($row['status'])->toBe('requires_action');
});

test('missing baseline keeps consumption visible and requests data', function () {
    $facility = Facility::factory()->create(['floor_area' => null]);
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 750,
        'baseline_kwh' => null,
        'review_status' => 'approved',
        'input_source' => 'manual',
    ]));

    $summary = app(EnergyPerformanceSummaryService::class)->build(collect([$facility]));
    $row = $summary['rows'][0];

    expect($row['actual_kwh'])->toBe(750.0)
        ->and($row['deviation'])->toBeNull()
        ->and($row['status'])->toBe('data_required')
        ->and($row['assessment'])->toContain('no complete approved baseline')
        ->and($summary['missing_baseline_count'])->toBe(1)
        ->and($summary['missing_reading_count'])->toBe(0)
        ->and($summary['assessment_ready_count'])->toBe(0);
});

test('UMAN reading falls back to its approved main meter baseline', function () {
    $facility = Facility::factory()->create(['source' => 'cprf', 'baseline_kwh' => 1800]);
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Bernardo Main Meter',
        'meter_number' => 'BERNARDO-001',
        'meter_type' => 'main',
        'baseline_kwh' => 1800,
        'status' => 'active',
        'approved_at' => now(),
    ]);
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 1000,
        'baseline_kwh' => null,
        'review_status' => 'approved',
        'input_source' => 'cprf',
        'external_source' => 'uman_cprf',
    ]));

    $row = app(EnergyPerformanceSummaryService::class)->build(collect([$facility]))['rows'][0];

    expect($row['baseline_kwh'])->toBe(1800.0)
        ->and($row['deviation'])->toBe(-44.44)
        ->and($row['reading_source'])->toBe('UMAN')
        ->and($row['status'])->toBe('requires_action');
});

test('summary separates missing readings from missing baselines', function () {
    $withoutReading = Facility::factory()->create();
    $withoutBaseline = Facility::factory()->create();
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $withoutBaseline->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 500,
        'review_status' => 'approved',
        'input_source' => 'manual',
    ]));

    $summary = app(EnergyPerformanceSummaryService::class)
        ->build(collect([$withoutReading, $withoutBaseline]));

    expect($summary['data_required_count'])->toBe(2)
        ->and($summary['missing_reading_count'])->toBe(1)
        ->and($summary['missing_baseline_count'])->toBe(1);
});

test('performance summary page is available to an authenticated user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Facility::factory()->create();

    $this->actingAs($admin)
        ->get(route('reports.performance-summary'))
        ->assertOk()
        ->assertSee('Energy Performance Summary')
        ->assertDontSee('No floor area required')
        ->assertDontSee('Assessment readiness');
});
