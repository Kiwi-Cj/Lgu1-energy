<?php

use App\Models\Facility;
use App\Models\User;

test('conservation overview presents one consolidated workflow', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.index'))
        ->assertOk()
        ->assertSee('Detect')
        ->assertSee('Assign')
        ->assertSee('Execute')
        ->assertSee('Verify')
        ->assertSee('Report')
        ->assertSee('Guide only; use the workspaces below to update work')
        ->assertSee('Start from AI Alerts')
        ->assertSee('Energy Recommendations')
        ->assertSee('Assign &amp; verify', escape: false)
        ->assertSee('Daily routine')
        ->assertSee('Measure targets')
        ->assertDontSee('Suggestions Box')
        ->assertDontSee('Estimated Savings');
});

test('duplicate conservation feature urls redirect to their owning workspaces', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', ['feature' => 'ai-recommendations']))
        ->assertRedirect(route('modules.ai-alerts.index'));

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', ['feature' => 'suggestions-box']))
        ->assertRedirect(route('landing.contact'));

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', ['feature' => 'conservation-goals']))
        ->assertOk();
});

test('daily checklist uses a compact task board with modal task creation and automatic saving', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Bernardo Court']);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'daily-checklist',
            'facility_id' => $facility->id,
            'date' => '2026-08-08',
        ]))
        ->assertOk()
        ->assertSee('Daily Task Board')
        ->assertSee('Daily checklist summary', escape: false)
        ->assertSee('checklistTaskModal', escape: false)
        ->assertSee('Assigned Routine')
        ->assertSee('Add First Task')
        ->assertDontSee('Save Checklist')
        ->assertDontSee('<select id="checklist_task_period"', escape: false);
});
