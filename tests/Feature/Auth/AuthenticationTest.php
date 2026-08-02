<?php

use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Notification;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    config(['otp.enabled' => false]);

    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users with otp enabled are redirected to the verification page before authentication', function () {
    Notification::fake();
    config(['otp.enabled' => true]);

    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('verify.otp.form', absolute: false));
    $response->assertSessionHas('otp_user_id', $user->id);
    Notification::assertSentTo($user, SendOtpNotification::class);
});

test('otp resend stays blocked for the full server cooldown', function () {
    Notification::fake();
    config([
        'otp.enabled' => true,
        'otp.resend_cooldown_seconds' => 30,
    ]);

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('verify.otp.form', absolute: false));

    $response = $this->postJson(route('verify.otp.resend', absolute: false));

    $response
        ->assertStatus(429)
        ->assertJsonStructure(['message', 'retry_after', 'resend_available_at']);

    expect($response->json('retry_after'))->toBeGreaterThan(0)
        ->and(session('otp_resend_available_at'))->toBeGreaterThan(now()->timestamp);

    Notification::assertSentToTimes($user, SendOtpNotification::class, 1);
});

test('otp email uses the recipient name and correct singular expiration wording', function () {
    config(['otp.expire_minutes' => 1]);

    $user = User::factory()->make([
        'full_name' => 'Juan Dela Cruz',
    ]);

    $mail = (new SendOtpNotification('415874'))->toMail($user);
    $html = $mail->render();

    expect($html)
        ->toContain('Hello Juan Dela Cruz,')
        ->toContain('415874')
        ->toContain('1 minute')
        ->not->toContain('1 minutes')
        ->toContain('never share this code with anyone')
        ->toContain('cid:energy-system-logo@energy-system');
});

test('a pending user can verify otp and complete authentication', function () {
    Notification::fake();
    config(['otp.enabled' => true]);

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $otp = Otp::query()->where('user_id', $user->id)->latest()->firstOrFail();

    $response = $this->post('/verify-otp', [
        'otp_code' => $otp->code,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
    $response->assertSessionMissing('otp_user_id');
    expect($otp->fresh()->used)->toBeTruthy();
});

test('otp verification cannot select another user from request input', function () {
    config(['otp.enabled' => true]);

    $pendingUser = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherOtp = Otp::create([
        'user_id' => $otherUser->id,
        'code' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this
        ->withSession(['otp_user_id' => $pendingUser->id])
        ->post('/verify-otp', [
            'user_id' => $otherUser->id,
            'otp_code' => $otherOtp->code,
        ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('otp_code');
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
