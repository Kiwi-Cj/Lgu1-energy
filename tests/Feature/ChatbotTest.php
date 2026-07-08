<?php

use App\Models\User;

it('returns account information when the user asks about their account', function () {
    $user = new User([
        'id' => 1,
        'full_name' => 'Juan Dela Cruz',
        'name' => 'Juan Dela Cruz',
        'username' => 'juandelacruz',
        'role' => 'staff',
        'department' => 'Engineering',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->postJson(route('modules.chatbot.respond'), [
        'message' => 'What is in my account?',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('type', 'account')
        ->assertJsonPath('data.user_name', 'Juan Dela Cruz');
});

it('returns main meter alert guidance when the user asks about alerts', function () {
    $user = new User([
        'id' => 2,
        'full_name' => 'Maria Santos',
        'name' => 'Maria Santos',
        'username' => 'mariasantos',
        'role' => 'admin',
        'department' => 'Energy',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->postJson(route('modules.chatbot.respond'), [
        'message' => 'What are the main meter alerts?',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('type', 'alerts');
});
