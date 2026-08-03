<?php

use App\Mail\UserWelcome;

uses(Tests\TestCase::class);

test('welcome email renders secure responsive content without image attachments', function () {
    $mailable = new UserWelcome(
        recipientName: 'Test User',
        recipientEmail: 'test@example.test',
        role: 'energy_officer',
        temporaryPassword: 'Temporary!123',
        loginUrl: 'https://example.test/login',
    );

    $html = $mailable->render();

    expect($html)
        ->toContain('Your account is ready')
        ->toContain('Energy Officer')
        ->toContain('Temporary!123')
        ->toContain('https://example.test/login')
        ->not->toContain('<img')
        ->not->toContain('cid:');

    expect($mailable->attachments)->toBe([])
        ->and($mailable->rawAttachments)->toBe([]);
});
