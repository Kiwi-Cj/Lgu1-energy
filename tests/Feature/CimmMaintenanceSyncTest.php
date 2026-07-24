<?php

use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\User;

test('cimm completed maintenance is archived to maintenance history', function () {
    config(['services.cimm_maintenance_sync.token' => 'test-cimm-token']);

    $facility = Facility::query()->create([
        'name' => 'CIMM Test Facility',
        'type' => 'Office',
        'location' => 'Test Location',
        'status' => 'active',
    ]);
    $maintenance = Maintenance::query()->create([
        'facility_id' => $facility->id,
        'issue_type' => 'General - Preventive Check',
        'trigger_month' => 'Jul 2026',
        'trend' => 'Stable',
        'maintenance_type' => 'Corrective',
        'maintenance_status' => 'Pending',
        'remarks' => 'Created for CIMM sync test.',
    ]);

    $response = $this->withToken('test-cimm-token')->postJson(
        "/api/v1/cimm-maintenance-sync/maintenance/{$maintenance->id}/sync",
        [
            'status' => 'Completed',
            'completed_date' => '2026-07-23',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('archived', true);

    $this->assertDatabaseMissing('maintenance', ['id' => $maintenance->id]);
    $this->assertDatabaseHas('maintenance_history', [
        'facility_id' => $facility->id,
        'maintenance_status' => 'Completed',
        'completed_date' => '2026-07-23 00:00:00',
    ]);
});

test('maintenance page reconciles stale completed rows into history', function () {
    $user = User::factory()->create(['role' => 'super admin']);
    $facility = Facility::query()->create([
        'name' => 'Stale Completed Test Facility',
        'type' => 'Office',
        'location' => 'Test Location',
        'status' => 'active',
    ]);
    $maintenance = Maintenance::query()->create([
        'facility_id' => $facility->id,
        'issue_type' => 'General - Preventive Check',
        'trigger_month' => 'Jul 2026',
        'trend' => 'Stable',
        'maintenance_type' => 'Corrective',
        'maintenance_status' => 'completed',
        'completed_date' => '2026-07-23 00:00:00',
        'remarks' => 'Completed outside the current sync endpoint.',
    ]);

    $this->actingAs($user)
        ->get('/modules/maintenance/index')
        ->assertOk();

    $this->assertDatabaseMissing('maintenance', ['id' => $maintenance->id]);
    $this->assertDatabaseHas('maintenance_history', [
        'facility_id' => $facility->id,
        'maintenance_status' => 'Completed',
        'completed_date' => '2026-07-23 00:00:00',
    ]);
});
