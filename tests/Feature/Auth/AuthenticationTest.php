<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionMissing('session_ended_modal');
    $response->assertSessionHas('status', 'You have been logged out successfully.');
});

test('authenticated activity can renew the session', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('session.keep-alive', absolute: false));

    $response
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('expires_in_seconds', (int) config('session.lifetime') * 60);
    $response->assertSessionHas('last_user_activity_at');
    $this->assertAuthenticated();
});

test('idle timeout logs the user out and returns a login redirect', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/logout', ['reason' => 'idle']);

    $this->assertGuest();
    $response
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('reason', 'idle')
        ->assertJsonPath('redirect', route('login'));
    $response->assertSessionHas('session_ended_modal', true);
    $response->assertSessionHas('status', 'Your session expired due to inactivity. Please sign in again.');
});
