<?php

use App\Models\Facility;
use App\Models\EnergySavingRecommendation;
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

test('adding a daily task also creates its linked facility recommendation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Daily Board Recommendation Facility']);

    $this->actingAs($admin)
        ->post(route('modules.energy-conservation.daily-checklist.tasks.store'), [
            'facility_id' => $facility->id,
            'task_label' => 'Turn off meeting-room lighting after closing.',
            'period' => 'closing',
            'return_date' => '2026-08-08',
        ])
        ->assertRedirect(route('modules.energy-conservation.feature', [
            'feature' => 'daily-checklist',
            'facility_id' => $facility->id,
            'date' => '2026-08-08',
        ]));

    $task = \App\Models\DailyEnergyChecklistTask::query()
        ->where('facility_id', $facility->id)
        ->firstOrFail();

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'facility_id' => $facility->id,
        'daily_checklist_task_id' => $task->id,
        'year' => 2026,
        'month' => 8,
        'engineer_recommendation' => 'Turn off meeting-room lighting after closing.',
        'status' => 'approved',
    ]);

    expect(EnergySavingRecommendation::query()
        ->where('daily_checklist_task_id', $task->id)
        ->firstOrFail()
        ->target_date
        ->toDateString())
        ->toBe('2026-08-08');
});

test('populating default checklist tasks creates standard opening and closing routine tasks and recommendations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Amoranto Sports Complex']);

    $this->actingAs($admin)
        ->post(route('modules.energy-conservation.daily-checklist.populate-default'), [
            'facility_id' => $facility->id,
            'return_date' => '2026-09-02',
        ])
        ->assertRedirect(route('modules.energy-conservation.feature', [
            'feature' => 'daily-checklist',
            'facility_id' => $facility->id,
            'date' => '2026-09-02',
        ]));

    $tasks = \App\Models\DailyEnergyChecklistTask::where('facility_id', $facility->id)->get();
    expect($tasks->count())->toBe(10);
    expect($tasks->where('period', 'opening')->count())->toBe(5);
    expect($tasks->where('period', 'closing')->count())->toBe(5);

    $recs = EnergySavingRecommendation::where('facility_id', $facility->id)->get();
    expect($recs->count())->toBe(0);
});
