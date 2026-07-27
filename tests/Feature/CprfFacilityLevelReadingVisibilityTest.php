<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

test('cprf facility-level readings appear in monthly records with a distinct badge', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create();

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 7820,
        'input_source' => 'cprf',
    ]);

    $response = $this->actingAs($admin)->get("/modules/facilities/{$facility->id}/monthly-records");

    $response->assertOk();
    $response->assertSee('Facility-Level (CPRF)');
    $response->assertSee('7,820.00');
});

test('cprf facility-level readings still appear when the table is filtered to one specific meter', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create();

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter',
        'meter_type' => 'main',
        'status' => 'active',
        'multiplier' => 1,
        'approved_at' => now(),
    ]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 300,
    ]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 7820,
        'input_source' => 'cprf',
    ]);

    $response = $this->actingAs($admin)
        ->get("/modules/facilities/{$facility->id}/monthly-records?table_meter_id={$meter->id}");

    $response->assertOk();
    $response->assertSee('Facility-Level (CPRF)');
    $response->assertSee('7,820.00');
});

test('cprf facility-level readings show their stored baseline and default rate, not zeros', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create();

    // Mirrors exactly what CprfFacilityReadingController::store() persists
    // when CPRF doesn't report a rate/cost: baseline_kwh computed from the
    // facility's resolved baseline, rate_per_kwh/energy_cost defaulted to 0
    // (not null) as a sentinel for "not provided".
    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => 2026,
        'month' => 9,
        'actual_kwh' => 10000,
        'baseline_kwh' => 1200,
        'deviation' => 733.33,
        'alert' => 'Critical',
        'rate_per_kwh' => 0,
        'energy_cost' => 0,
        'input_source' => 'cprf',
    ]);

    $response = $this->actingAs($admin)->get("/modules/facilities/{$facility->id}/monthly-records");

    $response->assertOk();
    $response->assertSee('1,200.00'); // baseline now shown, not "-"
    $response->assertDontSee('No baseline');
    $response->assertSee(number_format(\App\Support\EnergyCost::DEFAULT_RATE_PER_KWH, 2)); // rate falls back, not 0.00
    $response->assertSee(number_format(10000 * \App\Support\EnergyCost::DEFAULT_RATE_PER_KWH, 2)); // cost uses the fallback rate
});

test('cprf facility-level readings are included in dashboard consumption totals', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create();
    $currentYear = (int) date('Y');
    $currentMonth = (int) date('n');

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => null,
        'year' => $currentYear,
        'month' => $currentMonth,
        'actual_kwh' => 500,
        'energy_cost' => 100,
        'input_source' => 'cprf',
    ]);

    $response = $this->actingAs($admin)->get('/modules/energy-monitoring');

    $response->assertOk();
    $response->assertViewHas('totalConsumptionKwh', fn ($total) => $total >= 500);
});
