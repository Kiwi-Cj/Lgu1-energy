<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('energy users manually encode records for a CPRF-managed facility', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'source' => 'cprf',
        'external_ref' => 91001,
    ]);
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Main Meter',
        'meter_number' => 'CPRF-MAIN-91001',
        'meter_type' => 'main',
        'status' => 'active',
        'baseline_kwh' => 450,
        'approved_by_user_id' => $admin->id,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->postJson(route('energy-records.store', ['facility' => $facility->id]), [
            'date' => '2026-08-01',
            'meter_id' => $meter->id,
            'actual_kwh' => 500,
            'rate_per_kwh' => 12,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('energy_records', [
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'input_source' => 'manual',
        'actual_kwh' => 500,
    ]);
});

test('CPRF-managed monthly records page provides Energy-owned entry controls', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'source' => 'cprf',
        'external_ref' => 91002,
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records', ['facility' => $facility->id]))
        ->assertOk()
        ->assertDontSee('Consumption data is encoded in CPRF')
        ->assertSee('Add Monthly Record');
});

test('monthly records header displays the current UMAN integration badge', function () {
    config()->set('services.uman_monthly_records.url', 'https://uman.test/api/monthly-energy-records.php');
    config()->set('services.uman_monthly_records.key', 'test-key');
    Cache::put('integrations.uman_monthly_records', [
        'state' => 'connected',
        'message' => 'Monthly records synchronized.',
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'source' => 'cprf',
        'external_ref' => 91004,
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records', ['facility' => $facility->id]))
        ->assertOk()
        ->assertSee('UMAN Connected')
        ->assertSee('UMAN integration status: UMAN Connected', false);
});

test('energy users cannot archive a CPRF-supplied monthly record', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'source' => 'cprf',
        'external_ref' => 91003,
    ]);
    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 500,
        'input_source' => 'cprf',
    ]);

    $this->actingAs($admin)
        ->deleteJson(route('energy-records.delete', [
            'facility' => $facility->id,
            'record' => $record->id,
        ]), ['archive_reason' => 'Should be rejected'])
        ->assertForbidden()
        ->assertJsonPath('message', 'Legacy CPRF-supplied monthly records are read-only in this system.');

    expect($record->fresh())->not->toBeNull();
});

test('local facilities retain manual monthly record controls', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['source' => 'local']);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records', ['facility' => $facility->id]))
        ->assertOk()
        ->assertSee('Add Monthly Record');
});
