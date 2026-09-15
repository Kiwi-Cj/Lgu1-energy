<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

test('energy monitoring dashboard loads monthly and weekly views with proportioned math', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Weekly Test Hall',
        'source' => 'local',
        'baseline_kwh' => 3100,
    ]);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Test Meter',
        'meter_number' => 'MAIN-TEST-001',
        'meter_type' => 'main',
        'baseline_kwh' => 3100,
        'status' => 'active',
        'approved_at' => now(),
    ]);

    // Create a 31-day month record (July 2026): actual_kwh = 3100 kWh, cost = 37200 (rate 12.00)
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 3100,
        'baseline_kwh' => 3100,
        'energy_cost' => 37200,
        'review_status' => 'approved',
        'input_source' => 'manual',
    ]));

    // 1. Monthly view:
    $responseMonthly = $this->actingAs($admin)
        ->get(route('modules.energy-monitoring.index', [
            'month' => '2026-07',
            'timeframe' => 'monthly',
        ]))
        ->assertOk()
        ->assertSee('Weekly Test Hall')
        ->assertSee('3,100.00 kWh');

    // 2. Weekly view (Week 1 = 7/31 = 700.00 kWh, cost = 8400.00):
    $responseWeekly = $this->actingAs($admin)
        ->get(route('modules.energy-monitoring.index', [
            'month' => '2026-07',
            'timeframe' => 'weekly',
            'week' => 1,
        ]))
        ->assertOk()
        ->assertSee('Weekly Test Hall')
        ->assertSee('700.00 kWh')
        ->assertSee('Week 1');

    // Verify view data
    expect($responseWeekly->viewData('timeframe'))->toBe('weekly')
        ->and($responseWeekly->viewData('selectedWeek'))->toBe(1)
        ->and($responseWeekly->viewData('totalConsumptionKwh'))->toEqual(700.0);
});

test('facility monthly records page displays weekly breakdown rows when weekly timeframe is selected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Weekly Record Facility',
        'source' => 'local',
        'baseline_kwh' => 2800,
    ]);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Building Meter',
        'meter_number' => 'MB-001',
        'meter_type' => 'main',
        'baseline_kwh' => 2800,
        'status' => 'active',
        'approved_at' => now(),
    ]);

    // Create a 28-day month record (February 2026): actual_kwh = 2800, baseline_kwh = 2800
    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 2,
        'actual_kwh' => 2800,
        'baseline_kwh' => 2800,
        'energy_cost' => 33600,
        'review_status' => 'approved',
        'input_source' => 'manual',
    ]));

    $response = $this->actingAs($admin)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
            'timeframe' => 'weekly',
            'week' => 0,
        ]))
        ->assertOk()
        ->assertSee('Weekly Breakdown (Main Meter)')
        ->assertSee('Week 1')
        ->assertSee('Week 2')
        ->assertSee('Week 3')
        ->assertSee('Week 4');

    expect($response->viewData('weeklyMainMeterRows'))->toHaveCount(4);
});

test('facility monthly records page collapses past months by default in weekly view', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Multi-Month Facility',
        'source' => 'local',
        'baseline_kwh' => 2800,
    ]);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Multi Main Meter',
        'meter_number' => 'MMM-001',
        'meter_type' => 'main',
        'baseline_kwh' => 2800,
        'status' => 'active',
        'approved_at' => now(),
    ]);

    // Create Aug 2026 and Jul 2026 records
    EnergyRecord::withoutEvents(function () use ($facility, $meter) {
        EnergyRecord::create([
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 8,
            'actual_kwh' => 3000,
            'baseline_kwh' => 2800,
            'energy_cost' => 36000,
            'review_status' => 'approved',
            'input_source' => 'manual',
        ]);
        EnergyRecord::create([
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 7,
            'actual_kwh' => 2900,
            'baseline_kwh' => 2800,
            'energy_cost' => 34800,
            'review_status' => 'approved',
            'input_source' => 'manual',
        ]);
    });

    $response = $this->actingAs($admin)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
            'timeframe' => 'weekly',
        ]))
        ->assertOk()
        ->assertSee('Expand All')
        ->assertSee('Collapse All')
        ->assertSee('weekly-month-card', false)
        ->assertSee('is-collapsed', false);

    expect($response->viewData('weeklyMainMeterRows'))->toHaveCount(8);
});

