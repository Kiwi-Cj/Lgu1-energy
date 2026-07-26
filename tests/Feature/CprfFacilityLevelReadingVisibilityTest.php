<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
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
