<?php

use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\User;

test('high usage creates a conservation-owned alert without incident or maintenance duplication', function () {
    $facility = Facility::factory()->create(['baseline_kwh' => 1000]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 6,
        'actual_kwh' => 1200,
        'baseline_kwh' => 1000,
        'input_source' => 'manual',
    ]);

    expect(EnergyIncident::query()->where('facility_id', $facility->id)->exists())->toBeFalse()
        ->and(Maintenance::query()->where('facility_id', $facility->id)->exists())->toBeFalse();
});

test('critical usage is routed to one linked incident and maintenance workflow', function () {
    $facility = Facility::factory()->create(['baseline_kwh' => 1000]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 1400,
        'baseline_kwh' => 1000,
        'input_source' => 'manual',
    ]);

    $incident = EnergyIncident::query()->where('facility_id', $facility->id)->sole();
    $maintenance = Maintenance::query()->where('facility_id', $facility->id)->sole();

    expect((int) $incident->month)->toBe(7)
        ->and((int) $incident->year)->toBe(2026)
        ->and((int) $maintenance->energy_incident_id)->toBe((int) $incident->id);
});

test('AI alerts expose only the action owned by each severity workflow', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $highFacility = Facility::factory()->create(['name' => 'High Usage Office', 'baseline_kwh' => 1000]);
    $criticalFacility = Facility::factory()->create(['name' => 'Critical Usage Office', 'baseline_kwh' => 1000]);

    EnergyRecord::withoutEvents(function () use ($highFacility, $criticalFacility) {
        EnergyRecord::create([
            'facility_id' => $highFacility->id,
            'year' => 2026,
            'month' => 8,
            'actual_kwh' => 1200,
            'baseline_kwh' => 1000,
            'input_source' => 'manual',
        ]);
        EnergyRecord::create([
            'facility_id' => $criticalFacility->id,
            'year' => 2026,
            'month' => 8,
            'actual_kwh' => 1400,
            'baseline_kwh' => 1000,
            'input_source' => 'manual',
        ]);
    });

    $this->actingAs($admin)
        ->get(route('modules.ai-alerts.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('Review &amp; assign action', escape: false)
        ->assertSee('Open incident &amp; maintenance', escape: false);
});
