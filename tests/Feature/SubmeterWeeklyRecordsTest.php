<?php

use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;

beforeEach(function () {
    config()->set('features.submeters_enabled', true);
});

test('submeter weekly records page renders properly for active facility with meters', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create(['name' => 'Demo Testing Facility']);

    $mainMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter Alpha',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $subMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'parent_meter_id' => $mainMeter->id,
        'meter_name' => 'Submeter Lab 1',
        'meter_type' => 'sub',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.weekly-records.submeters', $facility->id))
        ->assertOk()
        ->assertSee('Sub-meter Weekly Records')
        ->assertSee('Weekly Records')
        ->assertSee('Monthly Records')
        ->assertSee('All Weeks (1–4)')
        ->assertSee('Submeter Lab 1');
});

test('submeter monthly records page can switch to weekly timeframe', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create(['name' => 'Demo Testing Facility']);

    $mainMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter Beta',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $subMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'parent_meter_id' => $mainMeter->id,
        'meter_name' => 'Submeter Office 2',
        'meter_type' => 'sub',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records.submeters', ['facility' => $facility->id, 'timeframe' => 'weekly']))
        ->assertOk()
        ->assertSee('Sub-meter Weekly Records')
        ->assertSee('Weekly Records')
        ->assertSee('Monthly Records');
});

test('submeter weekly records dynamically adjusts week 4 days for february and april', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create(['name' => 'Dynamic Days Facility']);

    $mainMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter Gamma',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    FacilityMeter::create([
        'facility_id' => $facility->id,
        'parent_meter_id' => $mainMeter->id,
        'meter_name' => 'Submeter Gamma 1',
        'meter_type' => 'sub',
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    // February 2026 (28 days) -> Week 4 (Days 22–28)
    $this->actingAs($admin)
        ->get(route('facilities.weekly-records.submeters', ['facility' => $facility->id, 'year' => 2026, 'month' => 2]))
        ->assertOk()
        ->assertSee('Week 4 (Days 22–28)');

    // April 2026 (30 days) -> Week 4 (Days 22–30)
    $this->actingAs($admin)
        ->get(route('facilities.weekly-records.submeters', ['facility' => $facility->id, 'year' => 2026, 'month' => 4]))
        ->assertOk()
        ->assertSee('Week 4 (Days 22–30)');

    // May 2026 (31 days) -> Week 4 (Days 22–31)
    $this->actingAs($admin)
        ->get(route('facilities.weekly-records.submeters', ['facility' => $facility->id, 'year' => 2026, 'month' => 5]))
        ->assertOk()
        ->assertSee('Week 4 (Days 22–31)');
});

test('linked submeters by main page renders with baseline calculator, equipment load, and bills history', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create(['name' => 'City Hospital Facility']);

    $mainMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Hospital Main Feeder',
        'meter_type' => 'main',
        'baseline_kwh' => 8500.00,
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $subMeter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'parent_meter_id' => $mainMeter->id,
        'meter_name' => 'ICU & Emergency Sub-meter',
        'meter_type' => 'sub',
        'baseline_kwh' => 2500.00,
        'status' => 'active',
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    \App\Models\SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'facility_meter_id' => $mainMeter->id,
        'meter_scope' => 'main',
        'equipment_name' => 'Central Chiller Unit',
        'quantity' => 2,
        'rated_watts' => 1500,
        'operating_hours_per_day' => 12,
        'operating_days_per_month' => 30,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.facilities.meters.main-submeters', [$facility->id, $mainMeter->id]))
        ->assertOk()
        ->assertSee('Hospital Main Feeder')
        ->assertSee('ICU & Emergency Sub-meter')
        ->assertSee('Main Baseline: 8,500.00 kWh')
        ->assertSee('Baseline Calculator')
        ->assertSee('Equipment Load');
});
