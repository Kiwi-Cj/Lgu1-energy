<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;

test('energy users cannot manually encode a monthly record for a CPRF-managed facility', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'source' => 'cprf',
        'external_ref' => 91001,
    ]);

    $this->actingAs($admin)
        ->postJson(route('energy-records.store', ['facility' => $facility->id]), [
            'date' => '2026-08-01',
            'meter_id' => 1,
            'actual_kwh' => 500,
            'rate_per_kwh' => 12,
        ])
        ->assertForbidden()
        ->assertJsonPath(
            'message',
            'Monthly records for CPRF-managed facilities are received automatically from CPRF and cannot be encoded manually in this system.'
        );

    expect(EnergyRecord::query()->where('facility_id', $facility->id)->exists())->toBeFalse();
});

test('CPRF-managed monthly records page is view-only and explains the integration', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'source' => 'cprf',
        'external_ref' => 91002,
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records', ['facility' => $facility->id]))
        ->assertOk()
        ->assertSee('CPRF-managed monthly records')
        ->assertSee('Consumption data is encoded in CPRF')
        ->assertDontSee('Add Monthly Record');
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
        ->assertJsonPath('message', 'CPRF-supplied monthly records are read-only in this system.');

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
