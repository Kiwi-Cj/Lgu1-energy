<?php

use App\Models\Facility;
use App\Models\User;

test('exporting reports requires password authorization', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => 'secretPassword123',
    ]);
    $facility = Facility::factory()->create(['name' => 'City Hall']);

    // Direct access without password confirmation is redirected back with error
    $response = $this->actingAs($admin)
        ->get(route('modules.load-tracking.export', $facility->id));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Please confirm your password before downloading reports.');
});

test('submitting wrong password fails authorization with remaining attempts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => 'secretPassword123',
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('downloads.authorize'), [
            'download_password' => 'wrongPassword',
            'target' => '/modules/reports/energy-export',
        ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'remaining_attempts' => 2,
    ]);
});

test('submitting correct password authorizes export and returns download token', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => \Illuminate\Support\Facades\Hash::make('secretPassword123'),
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('downloads.authorize'), [
            'download_password' => 'secretPassword123',
            'target' => '/modules/reports/energy-export',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
    ]);
    expect($response->json('redirect_url'))->toContain('download_token=');
});

test('submitting correct password authorizes print action', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => \Illuminate\Support\Facades\Hash::make('secretPassword123'),
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('downloads.authorize'), [
            'download_password' => 'secretPassword123',
            'target' => 'print',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'action' => 'print',
        'redirect_url' => 'print',
    ]);
});

test('multiple failed password attempts triggers a lockout penalty', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => 'secretPassword123',
    ]);

    for ($i = 0; $i < 2; $i++) {
        $this->actingAs($admin)->postJson(route('downloads.authorize'), [
            'download_password' => 'wrong',
            'target' => 'print',
        ]);
    }

    // 3rd failed attempt triggers lockout
    $thirdAttempt = $this->actingAs($admin)->postJson(route('downloads.authorize'), [
        'download_password' => 'wrong',
        'target' => 'print',
    ]);

    $thirdAttempt->assertStatus(429);
    $thirdAttempt->assertJson([
        'success' => false,
        'remaining_attempts' => 0,
    ]);
});
