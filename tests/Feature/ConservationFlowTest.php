<?php

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
        ->assertSee('Action Recommendations')
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
