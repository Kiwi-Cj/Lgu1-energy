<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $user = User::factory()->create();
    $newPassword = 'StrongPass!123';

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue(Hash::check($newPassword, $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();
    $newPassword = 'StrongPass!123';

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');
});
