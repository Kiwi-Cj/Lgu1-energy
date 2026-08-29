<?php

use App\Models\User;
use App\Notifications\SendOtpNotification;

uses(Tests\TestCase::class);

test('OTP email contains no image or embedded content references', function () {
    $user = new User([
        'full_name' => 'Test User',
        'email' => 'test@example.test',
    ]);

    $message = (new SendOtpNotification('123456'))->toMail($user);
    $html = view('emails.otp', $message->viewData)->render();

    expect($html)
        ->not->toContain('<img')
        ->not->toContain('cid:')
        ->not->toContain('data:image');

    expect($message->subject)->toMatch('/^Your OTP Code - .+ \[[A-Z0-9]{6}\]$/');
});
