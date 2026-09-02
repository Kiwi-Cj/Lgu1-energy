<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;

test('dashboard displays carbon footprint and gemp ra 11285 sustainability metrics', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['baseline_kwh' => 1000]);
    $currentYear = (int) date('Y');
    $currentMonth = (int) date('n');

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => $currentYear,
        'month' => $currentMonth,
        'actual_kwh' => 850,
        'baseline_kwh' => 1000,
        'energy_cost' => 8500,
        'input_source' => 'manual',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.index'));

    $response->assertOk();
    // Check view variables passed to dashboard
    $response->assertViewHas('carbonEmissionsMt');
    $response->assertViewHas('treesEquivalent');
    $response->assertViewHas('gempStatus');
    $response->assertViewHas('gempTone');
    $response->assertViewHas('efficiencyScore');
    $response->assertViewHas('topSavers');

    // Check UI elements in rendered HTML
    $response->assertSee('Carbon Footprint');
    $response->assertSee('MT CO₂e');
    $response->assertSee('GEMP RA 11285 Target');
    $response->assertSee('Efficiency Score:');
    $response->assertSee('AI Alerts');
    $response->assertSee('Load Tracking');
    $response->assertSee('Facilities');
    $response->assertSee('Main Meter');
    $response->assertSee('Incidents');
    $response->assertSee('Maintenance');
    $response->assertSee('Operational Shortcuts');
});

