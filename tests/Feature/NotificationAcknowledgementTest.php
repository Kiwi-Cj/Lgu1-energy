<?php

use App\Models\Notification;
use App\Models\User;

test('a user can acknowledge their own notification', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'title' => 'Critical Energy Alert',
        'message' => 'Critical usage detected.',
        'type' => 'energy_record_alert',
    ]);

    $this->actingAs($user)
        ->postJson(route('notifications.acknowledge', $notification))
        ->assertOk()
        ->assertJsonPath('success', true);

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull()
        ->and($notification->acknowledged_at)->not->toBeNull();
});

test('a user cannot acknowledge another users notification', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $owner->id,
        'title' => 'Projected Cost Alert',
        'message' => 'Projected cost may exceed the previous month.',
        'type' => 'ai_cost_alert',
    ]);

    $this->actingAs($otherUser)
        ->postJson(route('notifications.acknowledge', $notification))
        ->assertForbidden();

    expect($notification->fresh()->acknowledged_at)->toBeNull();
});
