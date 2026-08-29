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

    $response = $this->actingAs($admin)
        ->get(route('modules.load-tracking.export', $facility->id));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});
