<?php

use App\Models\Facility;
use App\Models\SubmeterEquipment;
use App\Models\User;

test('authenticated user can view load tracking dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'City Hall Main',
        'baseline_kwh' => 4500,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.load-tracking.index', ['facility_id' => $facility->id]))
        ->assertOk()
        ->assertSee('Facility Load Tracking')
        ->assertSee('City Hall Main')
        ->assertSee('Connected Load')
        ->assertSee('Daily Consumption')
        ->assertSee('Monthly Consumption');
});

test('user can create equipment load entry with calculated energy consumption', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Health Office']);

    $payload = [
        'facility_id' => $facility->id,
        'equipment_name' => 'Inverter Split AC 1.5HP',
        'category' => 'HVAC / Cooling',
        'location' => 'Doctor Clinic 101',
        'meter_scope' => 'facility',
        'quantity' => 2,
        'rated_watts' => 1150,
        'operating_hours_per_day' => 8,
        'operating_days_per_month' => 22,
        'notes' => 'Energy efficient inverter test',
    ];

    $this->actingAs($admin)
        ->post(route('modules.load-tracking.equipment.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('submeter_equipments', [
        'facility_id' => $facility->id,
        'equipment_name' => 'Inverter Split AC 1.5HP',
        'quantity' => 2,
        'rated_watts' => 1150,
    ]);

    $created = SubmeterEquipment::where('equipment_name', 'Inverter Split AC 1.5HP')->first();
    expect($created)->not->toBeNull();
    // (1150 * 2 * 8 * 22) / 1000 = 404.80 kWh
    expect((float) $created->monthly_kwh)->toEqual(404.80);
    // (1150 * 2 * 8) / 1000 = 18.40 kWh
    expect((float) $created->daily_kwh)->toEqual(18.40);
});

test('user can update equipment load entry', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Engineering Center']);
    $equipment = SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Old Server Rack',
        'category' => 'IT & Office Equipment',
        'quantity' => 1,
        'rated_watts' => 1000,
        'operating_hours_per_day' => 24,
        'operating_days_per_month' => 30,
    ]);

    $this->actingAs($admin)
        ->put(route('modules.load-tracking.equipment.update', $equipment->id), [
            'equipment_name' => 'Upgraded Eco Server Rack',
            'category' => 'IT & Office Equipment',
            'location' => 'Level 2 Server Room',
            'quantity' => 1,
            'rated_watts' => 600,
            'operating_hours_per_day' => 24,
            'operating_days_per_month' => 30,
            'notes' => 'Lowered power draw to 600W',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('submeter_equipments', [
        'id' => $equipment->id,
        'equipment_name' => 'Upgraded Eco Server Rack',
        'rated_watts' => 600,
    ]);
});

test('user can delete equipment load entry', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Fire Station']);
    $equipment = SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Temporary Generator Load',
        'category' => 'Other',
        'quantity' => 1,
        'rated_watts' => 5000,
        'operating_hours_per_day' => 2,
        'operating_days_per_month' => 5,
    ]);

    $this->actingAs($admin)
        ->delete(route('modules.load-tracking.equipment.destroy', $equipment->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('submeter_equipments', [
        'id' => $equipment->id,
    ]);
});

test('user can export facility load schedule as CSV', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Treasury Hall']);
    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Desktop PC Fleet',
        'category' => 'IT & Office Equipment',
        'quantity' => 10,
        'rated_watts' => 200,
        'operating_hours_per_day' => 8,
        'operating_days_per_month' => 22,
    ]);

    $authResponse = $this->actingAs($admin)
        ->postJson(route('downloads.authorize'), [
            'download_password' => 'password',
            'target' => route('modules.load-tracking.export', $facility->id),
        ]);

    $authResponse->assertOk()->assertJson(['success' => true]);
    $redirectUrl = $authResponse->json('redirect_url');

    $response = $this->actingAs($admin)->get($redirectUrl);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('load tracking summary includes category specific wattages and simulator targets', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Sports Center', 'baseline_kwh' => 45000]);

    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Stadium Floodlights',
        'category' => 'Lighting',
        'quantity' => 10,
        'rated_watts' => 1500, // 15,000 W
        'operating_hours_per_day' => 6,
        'operating_days_per_month' => 26,
    ]);

    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Arena AC',
        'category' => 'HVAC / Cooling',
        'quantity' => 2,
        'rated_watts' => 10000, // 20,000 W
        'operating_hours_per_day' => 10,
        'operating_days_per_month' => 26,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('modules.load-tracking.index', ['facility_id' => $facility->id]));

    $response->assertOk();
    $summary = $response->viewData('summary');
    expect($summary['lighting_watts'])->toEqual(15000.0);
    expect($summary['cooling_watts'])->toEqual(20000.0);
    expect($summary['cooling_lighting_watts'])->toEqual(35000.0);
    expect($summary['total_connected_watts'])->toEqual(35000.0);

    // Verify What-If simulator target pills are rendered
    $response->assertSee('Select Target Load Category')
        ->assertSee('Cooling + Lighting')
        ->assertSee('Cooling Only')
        ->assertSee('Lighting Only')
        ->assertSee('All Loads');
});

test('seeder populates realistic equipment for Amoranto Sports Complex', function () {
    $facility = Facility::firstOrCreate(
        ['name' => 'Amoranto Sports Complex'],
        ['baseline_kwh' => 45000]
    );

    $seeder = new \Database\Seeders\FacilityEquipmentLoadSeeder();
    $seeder->run();

    $equipments = SubmeterEquipment::where('facility_id', $facility->id)->get();
    expect($equipments->count())->toBeGreaterThanOrEqual(5);

    // Check specific realistic sports complex equipment
    $equipmentNames = $equipments->pluck('equipment_name')->toArray();
    expect(collect($equipmentNames)->some(fn ($n) => str_contains(strtolower($n), 'floodlight')))->toBeTrue();
    expect(collect($equipmentNames)->some(fn ($n) => str_contains(strtolower($n), 'pool')))->toBeTrue();
    expect(collect($equipmentNames)->some(fn ($n) => str_contains(strtolower($n), 'scoreboard')))->toBeTrue();
});

test('user can toggle equipment status between active and inactive', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Legislative Building']);
    $equipment = SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Standby Emergency Generator Chiller',
        'category' => 'HVAC / Cooling',
        'quantity' => 1,
        'rated_watts' => 5000,
        'operating_hours_per_day' => 8,
        'operating_days_per_month' => 22,
        'status' => 'active',
    ]);

    // Deactivate
    $this->actingAs($admin)
        ->post(route('modules.load-tracking.equipment.toggle-status', $equipment->id))
        ->assertRedirect();

    $equipment->refresh();
    expect($equipment->status)->toBe('inactive');

    // Reactivate
    $this->actingAs($admin)
        ->post(route('modules.load-tracking.equipment.toggle-status', $equipment->id))
        ->assertRedirect();

    $equipment->refresh();
    expect($equipment->status)->toBe('active');
});

test('inactive equipment is excluded from active connected load and energy computations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Civic Center Building', 'baseline_kwh' => 20000]);

    // 1 Active AC: 2000W, 1 qty, 10h/day, 20 days/mo => 2 kW, 400 kWh/mo
    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Main Office AC',
        'category' => 'HVAC / Cooling',
        'quantity' => 1,
        'rated_watts' => 2000,
        'operating_hours_per_day' => 10,
        'operating_days_per_month' => 20,
        'status' => 'active',
    ]);

    // 1 Inactive / Decommissioned Chiller: 10000W, 1 qty => 10 kW, 2000 kWh/mo
    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Decommissioned Old Chiller',
        'category' => 'HVAC / Cooling',
        'quantity' => 1,
        'rated_watts' => 10000,
        'operating_hours_per_day' => 10,
        'operating_days_per_month' => 20,
        'status' => 'inactive',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('modules.load-tracking.index', ['facility_id' => $facility->id]));

    $response->assertOk();
    $summary = $response->viewData('summary');

    // Total items is 2 (inventory count), but active items is 1, inactive is 1
    expect($summary['total_items'])->toEqual(2);
    expect($summary['active_items'])->toEqual(1);
    expect($summary['inactive_items'])->toEqual(1);

    // Active connected load must be 2 kW (2000 W), NOT 12 kW (12000 W)
    expect($summary['total_connected_watts'])->toEqual(2000.0);
    expect($summary['total_connected_kw'])->toEqual(2.0);
    // Active monthly kWh must be 400 kWh, NOT 2400 kWh
    expect($summary['total_monthly_kwh'])->toEqual(400.0);
});

test('status filtering displays active or inactive equipment correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Hall of Justice']);

    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Active Courtroom AC',
        'status' => 'active',
        'quantity' => 1,
        'rated_watts' => 1500,
        'operating_hours_per_day' => 8,
        'operating_days_per_month' => 22,
    ]);

    SubmeterEquipment::create([
        'facility_id' => $facility->id,
        'equipment_name' => 'Decommissioned Floor Heater',
        'status' => 'inactive',
        'quantity' => 1,
        'rated_watts' => 2000,
        'operating_hours_per_day' => 8,
        'operating_days_per_month' => 22,
    ]);

    // Filter active
    $resActive = $this->actingAs($admin)
        ->get(route('modules.load-tracking.index', ['facility_id' => $facility->id, 'status' => 'active']));
    $resActive->assertOk()
        ->assertSee('Active Courtroom AC')
        ->assertDontSee('Decommissioned Floor Heater');

    // Filter inactive
    $resInactive = $this->actingAs($admin)
        ->get(route('modules.load-tracking.index', ['facility_id' => $facility->id, 'status' => 'inactive']));
    $resInactive->assertOk()
        ->assertSee('Decommissioned Floor Heater')
        ->assertDontSee('Active Courtroom AC');
});


