<?php

use App\Models\Facility;
use App\Models\User;

test('facilities page shows and applies the local source tab', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Facility::factory()->create([
        'name' => 'Local Source Facility',
        'source' => 'local',
    ]);
    Facility::factory()->create([
        'name' => 'CPRF Source Facility',
        'source' => 'cprf',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('facilities.index', ['source' => 'local']));

    $response->assertOk()
        ->assertSee('Local Facilities')
        ->assertSee('Local Source Facility')
        ->assertDontSee('CPRF Source Facility')
        ->assertViewHas('sourceTab', 'local')
        ->assertViewHas('allFacilitiesCount', 2)
        ->assertViewHas('localFacilitiesCount', 1)
        ->assertViewHas('publicFacilitiesCount', 1);
});
