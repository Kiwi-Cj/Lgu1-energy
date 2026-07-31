<?php

use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;

function makeWorkflowIncident(array $incidentAttributes = [], array $facilityAttributes = []): EnergyIncident
{
    $facility = Facility::factory()->create(array_merge([
        'name' => 'Workflow Test Facility',
        'source' => 'local',
        'baseline_kwh' => 1000,
    ], $facilityAttributes));

    $record = EnergyRecord::withoutEvents(fn () => EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 1500,
        'baseline_kwh' => 1000,
        'input_source' => ($facilityAttributes['source'] ?? 'local') === 'cprf' ? 'cprf' : 'manual',
    ]));

    return EnergyIncident::create(array_merge([
        'energy_record_id' => $record->id,
        'facility_id' => $facility->id,
        'month' => 7,
        'year' => 2026,
        'deviation_percent' => 50,
        'description' => 'Automated anomaly test incident.',
        'status' => 'Open',
        'date_detected' => '2026-07-20',
    ], $incidentAttributes));
}

test('incident list filters real CPRF incidents and labels their source', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    makeWorkflowIncident([], ['name' => 'Integrated Civic Hall', 'source' => 'cprf', 'external_ref' => 777]);
    makeWorkflowIncident([], ['name' => 'Local Civic Hall']);

    $this->actingAs($admin)
        ->get(route('energy-incidents.index', ['source' => 'cprf', 'year' => 2026, 'month' => 7]))
        ->assertOk()
        ->assertSee('Integrated Civic Hall')
        ->assertSee('CPRF Integrated')
        ->assertDontSee('Local Civic Hall');
});

test('incident status cannot be changed manually because CIMM owns the action workflow', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $incident = makeWorkflowIncident();

    $this->actingAs($admin)
        ->put(route('energy-incidents.update', $incident), ['status' => 'Ongoing'])
        ->assertRedirect(route('energy-incidents.index'))
        ->assertSessionHas('error');

    expect($incident->fresh()->status)->toBe('Open')
        ->and($incident->fresh()->resolved_at)->toBeNull();
});

test('staff also cannot change a CIMM-managed incident status', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $incident = makeWorkflowIncident();

    $this->actingAs($staff)
        ->put(route('energy-incidents.update', $incident), ['status' => 'Resolved'])
        ->assertForbidden();

    expect($incident->fresh()->status)->toBe('Open');
});

test('manual incident reporting creates an open incident and a linked CIMM maintenance request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Manual Report Facility']);
    $detectedAt = now()->subHour()->format('Y-m-d\TH:i');

    $this->actingAs($admin)
        ->post(route('energy-incidents.store'), [
            'facility_id' => $facility->id,
            'category' => 'equipment_overheating',
            'detected_at' => $detectedAt,
            'affected_asset' => 'AHU-02',
            'description' => 'AHU motor housing is unusually hot and emitting intermittent noise.',
        ])
        ->assertRedirect(route('energy-incidents.index'))
        ->assertSessionHas('success');

    $incident = EnergyIncident::query()->where('facility_id', $facility->id)->sole();
    expect($incident->source)->toBe('manual')
        ->and($incident->status)->toBe('Open')
        ->and($incident->category)->toBe('equipment_overheating')
        ->and($incident->affected_asset)->toBe('AHU-02');

    $this->assertDatabaseHas('maintenance', [
        'facility_id' => $facility->id,
        'energy_incident_id' => $incident->id,
        'issue_type' => 'General - Other',
        'maintenance_type' => 'Corrective',
        'maintenance_status' => 'Pending',
    ]);
});

test('manual reporting rejects a duplicate active category for the same facility', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $incident = makeWorkflowIncident([
        'category' => 'meter_issue',
        'source' => 'manual',
        'date_detected' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->from(route('energy-incidents.index'))
        ->post(route('energy-incidents.store'), [
            'facility_id' => $incident->facility_id,
            'category' => 'meter_issue',
            'detected_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'description' => 'A second report for the same active meter issue.',
        ])
        ->assertRedirect(route('energy-incidents.index'))
        ->assertSessionHasErrors('category');

    expect(EnergyIncident::query()->where('facility_id', $incident->facility_id)->count())->toBe(1);
});
