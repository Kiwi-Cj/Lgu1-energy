<?php

use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('password reset otp can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'username' => $user->username,
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, SendOtpNotification::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'username' => $user->username,
        'email' => $user->email,
    ]);

    $otp = Otp::query()->where('user_id', $user->id)->latest()->firstOrFail();
    $this->post('/forgot-password', [
        'submit_action' => 'verify_otp',
        'username' => $user->username,
        'email' => $user->email,
        'otp' => $otp->code,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();
    $newPassword = 'StrongPass!123';

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'username' => $user->username,
        'email' => $user->email,
    ]);

    $otp = Otp::query()->where('user_id', $user->id)->latest()->firstOrFail();
    $this->post('/forgot-password', [
        'submit_action' => 'verify_otp',
        'username' => $user->username,
        'email' => $user->email,
        'otp' => $otp->code,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $newPassword) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('password_reset_success', true)
            ->assertRedirect(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]));

        return true;
    });
});
