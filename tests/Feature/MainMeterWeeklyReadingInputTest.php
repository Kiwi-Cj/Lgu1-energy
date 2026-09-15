<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\FacilityMeterWeeklyReading;
use App\Models\User;

test('storing partial weekly readings (1 to 3 weeks) records data but does not seal monthly total', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Weekly Input Test Facility',
        'source' => 'local',
        'baseline_kwh' => 4000,
    ]);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Input Meter',
        'meter_number' => 'MIM-001',
        'meter_type' => 'main',
        'baseline_kwh' => 4000,
        'status' => 'active',
        'approved_at' => now(),
    ]);

    // Store Week 1 reading (250 kWh)
    $response = $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 8,
            'week_number' => 1,
            'reading_date' => '2026-08-07',
            'actual_kwh' => 250,
            'rate_per_kwh' => 12.00,
            'notes' => 'Week 1 reading test',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Assert weekly reading exists
    $this->assertDatabaseHas('facility_meter_weekly_readings', [
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 8,
        'week_number' => 1,
        'actual_kwh' => 250,
        'cost' => 3000.00,
    ]);

    // Assert Monthly EnergyRecord is NOT created because only 1/4 weeks logged
    $monthlyRecord = EnergyRecord::where('facility_id', $facility->id)
        ->where('meter_id', $meter->id)
        ->where('year', 2026)
        ->where('month', 8)
        ->first();

    expect($monthlyRecord)->toBeNull();

    // Store Week 2 reading (300 kWh)
    $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 8,
            'week_number' => 2,
            'reading_date' => '2026-08-14',
            'actual_kwh' => 300,
            'rate_per_kwh' => 12.00,
        ]);

    // Store Week 3 reading (270 kWh)
    $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 8,
            'week_number' => 3,
            'reading_date' => '2026-08-21',
            'actual_kwh' => 270,
            'rate_per_kwh' => 12.00,
        ]);

    // 3 weeks logged: monthly record still null
    $monthlyRecordAfter3 = EnergyRecord::where('facility_id', $facility->id)
        ->where('meter_id', $meter->id)
        ->where('year', 2026)
        ->where('month', 8)
        ->first();

    expect($monthlyRecordAfter3)->toBeNull();
});

test('storing all 4 weeks automatically synchronizes monthly total with input_source weekly_aggregate', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Auto Aggregate Facility',
        'source' => 'local',
        'baseline_kwh' => 4000,
    ]);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Aggregate Meter',
        'meter_number' => 'MAM-001',
        'meter_type' => 'main',
        'baseline_kwh' => 4000,
        'status' => 'active',
        'approved_at' => now(),
    ]);

    // Add 4 weeks: 200, 250, 220, 230 => Total 900 kWh, Total Cost 10,800 PHP
    $weeksData = [
        1 => ['kwh' => 200, 'date' => '2026-08-07'],
        2 => ['kwh' => 250, 'date' => '2026-08-14'],
        3 => ['kwh' => 220, 'date' => '2026-08-21'],
        4 => ['kwh' => 230, 'date' => '2026-08-31'],
    ];

    foreach ($weeksData as $wNum => $wVal) {
        $this->actingAs($admin)
            ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
                'meter_id' => $meter->id,
                'year' => 2026,
                'month' => 8,
                'week_number' => $wNum,
                'reading_date' => $wVal['date'],
                'actual_kwh' => $wVal['kwh'],
                'rate_per_kwh' => 12.00,
            ]);
    }

    // Now all 4 weeks exist, monthly record should be created/synchronized
    $monthlyRecord = EnergyRecord::where('facility_id', $facility->id)
        ->where('meter_id', $meter->id)
        ->where('year', 2026)
        ->where('month', 8)
        ->first();

    expect($monthlyRecord)->not->toBeNull()
        ->and((float) $monthlyRecord->actual_kwh)->toEqual(900.0)
        ->and((float) $monthlyRecord->energy_cost)->toEqual(10800.0)
        ->and($monthlyRecord->input_source)->toBe('weekly_aggregate')
        ->and($monthlyRecord->review_status)->toBe('approved');

    // Verify monthly records page displays the 4/4 Complete status and weekly tiles
    $pageResponse = $this->actingAs($admin)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
            'timeframe' => 'weekly',
        ]))
        ->assertOk()
        ->assertSee('4/4 Weeks Complete')
        ->assertSee('900.00 kWh')
        ->assertSee('Manual Reading');
});

test('duplicate week entry for same month and meter is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['source' => 'local']);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter Dup Test',
        'meter_type' => 'main',
        'approved_at' => now(),
    ]);

    // Store Week 1
    $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 9,
            'week_number' => 1,
            'actual_kwh' => 150,
            'rate_per_kwh' => 12.00,
        ])
        ->assertRedirect();

    // Try to store Week 1 again
    $duplicateResponse = $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 9,
            'week_number' => 1,
            'actual_kwh' => 200,
            'rate_per_kwh' => 12.00,
        ]);

    $duplicateResponse->assertSessionHasErrors('duplicate_week');
    expect(FacilityMeterWeeklyReading::where('facility_id', $facility->id)->count())->toBe(1);
});

test('deleting a weekly reading removes it and adjusts or removes monthly aggregate', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['source' => 'local']);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter Delete Test',
        'meter_type' => 'main',
        'approved_at' => now(),
    ]);

    // Add 4 weeks
    $readingIds = [];
    foreach ([1 => 100, 2 => 100, 3 => 100, 4 => 100] as $wNum => $kwh) {
        $this->actingAs($admin)
            ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
                'meter_id' => $meter->id,
                'year' => 2026,
                'month' => 5,
                'week_number' => $wNum,
                'actual_kwh' => $kwh,
                'rate_per_kwh' => 12.00,
            ]);
    }

    $lastReading = FacilityMeterWeeklyReading::where('facility_id', $facility->id)
        ->where('week_number', 4)
        ->firstOrFail();

    // Verify monthly aggregate was created (400 kWh)
    $aggregate = EnergyRecord::where('facility_id', $facility->id)
        ->where('month', 5)
        ->where('year', 2026)
        ->first();
    expect($aggregate)->not->toBeNull()
        ->and((float) $aggregate->actual_kwh)->toEqual(400.0);

    // Delete Week 4 reading
    $deleteResponse = $this->actingAs($admin)
        ->delete(route('facility-meter-weekly-readings.destroy', [
            'facility' => $facility->id,
            'reading' => $lastReading->id,
        ]));

    $deleteResponse->assertRedirect();
    $deleteResponse->assertSessionHas('success');

    // Week 4 reading is deleted
    $this->assertDatabaseMissing('facility_meter_weekly_readings', ['id' => $lastReading->id]);

    // Aggregate record should be deleted/removed because only 3 weeks remain
    $aggregateAfterDelete = EnergyRecord::where('facility_id', $facility->id)
        ->where('month', 5)
        ->where('year', 2026)
        ->first();
    expect($aggregateAfterDelete)->toBeNull();
});

test('monthly records page supplies meter dial timeline and previous reading auto calculations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['source' => 'local']);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Dial Test Meter',
        'meter_type' => 'main',
        'approved_at' => now(),
    ]);

    // Store Week 1 reading starting at dial 1000.00 to 1250.00 (250 kWh)
    $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 8,
            'week_number' => 1,
            'reading_date' => '2026-08-07',
            'previous_reading_kwh' => 1000.00,
            'current_reading_kwh' => 1250.00,
            'actual_kwh' => 250.00,
            'rate_per_kwh' => 12.00,
        ])
        ->assertRedirect();

    $response = $this->actingAs($admin)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
            'timeframe' => 'weekly',
        ]))
        ->assertOk();

    // Verify view data contains meterDialTimeline and latestMeterDials
    $timeline = $response->viewData('meterDialTimeline');
    $latestDials = $response->viewData('latestMeterDials');

    expect($timeline)->not->toBeEmpty()
        ->and($latestDials[(int) $meter->id])->toEqual(1250.00);
});

test('storing an elevated weekly reading exceeding baseline triggers an in-app alert notification', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Weekly Spike Facility',
        'source' => 'local',
        'baseline_kwh' => 2000,
    ]);

    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Spike Meter',
        'meter_type' => 'main',
        'baseline_kwh' => 2000,
        'approved_at' => now(),
    ]);

    // August has 31 days. Week 1 baseline = (7 / 31) * 2000 = 451.61 kWh.
    // Entering 700 kWh is +55% above baseline (Critical spike).
    $this->actingAs($admin)
        ->post(route('facility-meter-weekly-readings.store', ['facility' => $facility->id]), [
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 8,
            'week_number' => 1,
            'reading_date' => '2026-08-07',
            'actual_kwh' => 700.00,
            'rate_per_kwh' => 12.00,
        ])
        ->assertRedirect();

    // Assert notification was created for admin
    $this->assertDatabaseHas('notifications', [
        'user_id' => $admin->id,
        'type' => 'critical',
    ]);
});


