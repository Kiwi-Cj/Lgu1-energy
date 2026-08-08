<?php

use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\User;

test('high usage creates a conservation-owned alert without incident or maintenance duplication', function () {
    $facility = Facility::factory()->create(['baseline_kwh' => 1000]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 6,
        'actual_kwh' => 1200,
        'baseline_kwh' => 1000,
        'input_source' => 'manual',
    ]);

    expect(EnergyIncident::query()->where('facility_id', $facility->id)->exists())->toBeFalse()
        ->and(Maintenance::query()->where('facility_id', $facility->id)->exists())->toBeFalse();
});

test('critical usage is routed to one linked incident and maintenance workflow', function () {
    $facility = Facility::factory()->create(['baseline_kwh' => 1000]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 1400,
        'baseline_kwh' => 1000,
        'input_source' => 'manual',
    ]);

    $incident = EnergyIncident::query()->where('facility_id', $facility->id)->sole();
    $maintenance = Maintenance::query()->where('facility_id', $facility->id)->sole();

    expect((int) $incident->month)->toBe(7)
        ->and((int) $incident->year)->toBe(2026)
        ->and((int) $maintenance->energy_incident_id)->toBe((int) $incident->id);
});

test('AI alerts expose only the action owned by each severity workflow', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $highFacility = Facility::factory()->create(['name' => 'High Usage Office', 'baseline_kwh' => 1000]);
    $criticalFacility = Facility::factory()->create(['name' => 'Critical Usage Office', 'baseline_kwh' => 1000]);

    EnergyRecord::withoutEvents(function () use ($highFacility, $criticalFacility) {
        EnergyRecord::create([
            'facility_id' => $highFacility->id,
            'year' => 2026,
            'month' => 8,
            'actual_kwh' => 1200,
            'baseline_kwh' => 1000,
            'input_source' => 'manual',
        ]);
        EnergyRecord::create([
            'facility_id' => $criticalFacility->id,
            'year' => 2026,
            'month' => 8,
            'actual_kwh' => 1400,
            'baseline_kwh' => 1000,
            'input_source' => 'manual',
        ]);
    });

    $this->actingAs($admin)
        ->get(route('modules.ai-alerts.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('Review recommendation')
        ->assertSee('Open incident &amp; maintenance', escape: false)
        ->assertSee('Monthly energy cost')
        ->assertSee('Generate AI insight')
        ->assertDontSee('Projected monthly bill')
        ->assertDontSee('Review &amp; assign action', escape: false);
});

test('AI alerts use the approved main meter baseline and flag large drops instead of treating them as normal', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Baseline Meter Office', 'baseline_kwh' => null]);
    $meter = \App\Models\FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Baseline Main Meter',
        'meter_number' => 'AI-BASELINE-001',
        'meter_type' => 'main',
        'status' => 'active',
        'baseline_kwh' => 1800,
        'approved_at' => now(),
    ]);
    $record = EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 1000,
        'rate_per_kwh' => 14.83,
        'input_source' => 'cprf',
        'review_status' => 'approved',
    ]));

    $this->actingAs($admin)
        ->get(route('modules.ai-alerts.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('Baseline 1,800.00 kWh')
        ->assertSee('-44.4%')
        ->assertSee('Usage: Drop Critical')
        ->assertSee('CPRF via UMAN')
        ->assertSee('Record: Approved')
        ->assertSee('record_id='.$record->id, escape: false)
        ->assertSee('Validate the meter reading')
        ->assertDontSee('Usage: Normal');
});

test('AI alerts separate facilities that have readings but no approved baseline', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'New Facility Without Baseline', 'baseline_kwh' => null]);
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 900,
        'rate_per_kwh' => 14.83,
        'input_source' => 'manual',
        'review_status' => 'for_review',
    ]));

    $this->actingAs($admin)
        ->get(route('modules.ai-alerts.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('Baseline pending')
        ->assertSee('Need 3–6 approved readings')
        ->assertSee('Usage: Baseline pending')
        ->assertSee('Record: For Review')
        ->assertSee('Monthly record is awaiting approval; CPRF publishing remains locked.')
        ->assertDontSee('Usage: Normal');
});

test('live AI Alerts suggestion uses the meter baseline even without three months of history', function () {
    config(['services.ai_recommendations.enabled' => false]);
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Single Month Baseline Office', 'baseline_kwh' => null]);
    $meter = \App\Models\FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Single Month Main Meter',
        'meter_number' => 'AI-LIVE-001',
        'meter_type' => 'main',
        'status' => 'active',
        'baseline_kwh' => 1800,
        'approved_at' => now(),
    ]);
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 1000,
        'rate_per_kwh' => 14.83,
        'input_source' => 'cprf',
        'review_status' => 'approved',
    ]));

    $response = $this->actingAs($admin)
        ->getJson(route('modules.energy-monitoring.ai-recommendation', [
            'facility' => $facility->id,
            'month' => '2026-08',
        ]))
        ->assertOk()
        ->assertJsonPath('facility_id', $facility->id)
        ->assertJsonPath('alert_level', 'Drop Critical')
        ->assertJsonPath('recommendation_source', 'rules')
        ->assertJsonFragment(['facility_name' => 'Single Month Baseline Office']);

    expect($response->json('recommendation'))
        ->toContain('below baseline')
        ->not->toContain('Not enough historical data');
});
