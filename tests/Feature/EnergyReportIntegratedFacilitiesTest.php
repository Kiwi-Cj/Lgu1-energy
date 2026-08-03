<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;

test('energy report shows CPRF facilities that are still awaiting their first reading', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Facility::factory()->create([
        'name' => 'Integrated Community Hall',
        'source' => 'cprf',
        'external_ref' => 501,
        'baseline_kwh' => 2500,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.reports.energy', ['year' => 2026, 'month' => 7]))
        ->assertOk()
        ->assertSee('Integrated Community Hall')
        ->assertSee('CPRF Integrated')
        ->assertSee('Awaiting Reading')
        ->assertSee('2,500.00');
});

test('a CPRF reading replaces its awaiting placeholder in the energy report', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Integrated Sports Center',
        'source' => 'cprf',
        'external_ref' => 502,
        'baseline_kwh' => 3000,
    ]);

    EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 3200,
        'baseline_kwh' => 3000,
        'input_source' => 'cprf',
    ]));

    $this->actingAs($admin)
        ->get(route('modules.reports.energy', ['facility_id' => $facility->id, 'year' => 2026, 'month' => 7]))
        ->assertOk()
        ->assertSee('Integrated Sports Center')
        ->assertSee('CPRF Integrated')
        ->assertSee('3,200.00')
        ->assertDontSee('Awaiting Reading');
});

test('energy report also shows local facilities awaiting a reading', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Facility::factory()->create([
        'name' => 'Local Civic Center',
        'source' => 'local',
        'baseline_kwh' => 1750,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.reports.energy', ['year' => 2026, 'month' => 7]))
        ->assertOk()
        ->assertSee('Local Civic Center')
        ->assertSee('Awaiting Reading')
        ->assertSee('1,750.00')
        ->assertDontSee('CPRF Integrated');
});
