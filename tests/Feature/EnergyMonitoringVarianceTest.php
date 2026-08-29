<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

test('monitoring dashboard shows variance against the approved baseline', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Bernardo Test Court', 'baseline_kwh' => 1800]);
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Bernardo Main Meter',
        'meter_number' => 'BER-001',
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

    $this->actingAs($admin)
        ->get(route('modules.energy-monitoring.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('Baseline Variance')
        ->assertSee('-800.00 kWh')
        ->assertSee('-44.44% vs baseline')
        ->assertSee('Drop Critical');
});
