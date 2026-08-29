<?php

use App\Models\Facility;
use App\Models\User;

test('energy monitoring filters facilities by CPRF or local source', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Facility::factory()->create([
        'name' => 'CPRF Monitor Facility',
        'source' => 'cprf',
    ]);
    Facility::factory()->create([
        'name' => 'Local Monitor Facility',
        'source' => 'local',
    ]);

    $cprfResponse = $this->actingAs($admin)
        ->get(route('modules.energy-monitoring.index', ['source' => 'cprf']));

    $cprfResponse->assertOk()
        ->assertSee('CPRF Monitor Facility')
        ->assertDontSee('Local Monitor Facility')
        ->assertViewHas('sourceFilter', 'cprf')
        ->assertViewHas('totalFacilities', 1);

    $localResponse = $this->actingAs($admin)
        ->get(route('modules.energy-monitoring.index', ['source' => 'local']));

    $localResponse->assertOk()
        ->assertSee('Local Monitor Facility')
        ->assertDontSee('CPRF Monitor Facility')
        ->assertViewHas('sourceFilter', 'local')
        ->assertViewHas('totalFacilities', 1)
        ->assertSee('id="monitorTableSort"', false)
        ->assertSee('value="consumption-desc"', false)
        ->assertSee('value="condition"', false);
});
