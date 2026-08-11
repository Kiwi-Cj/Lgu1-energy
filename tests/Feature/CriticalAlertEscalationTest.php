<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('an unacknowledged critical alert is escalated to administrators after the deadline', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $responder = User::factory()->create(['role' => 'engineer']);
    $source = Notification::create([
        'user_id' => $responder->id,
        'title' => 'Energy Alert',
        'message' => 'Alert: Main meter at City Hall (Aug 2026) increased by 42.00% [Critical]',
        'type' => 'energy_record_alert',
        'target_url' => '/modules/ai-alerts?month=2026-08',
    ]);
    $source->forceFill(['created_at' => now()->subMinutes(31)])->saveQuietly();

    $this->artisan('energy:escalate-critical-alerts', ['--minutes' => 30])->assertSuccessful();

    expect($admin->notifications()->where('type', 'critical_alert_escalation')->count())->toBe(1);

    $this->artisan('energy:escalate-critical-alerts', ['--minutes' => 30])->assertSuccessful();
    expect($admin->notifications()->where('type', 'critical_alert_escalation')->count())->toBe(1);
});

test('an acknowledged critical event is not escalated', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'super_admin']);
    $responder = User::factory()->create(['role' => 'staff']);
    $source = Notification::create([
        'user_id' => $responder->id,
        'title' => 'Energy Alert',
        'message' => 'Alert: Main meter at Health Office (Aug 2026) increased by 40.00% [Critical]',
        'type' => 'energy_record_alert',
        'target_url' => '/modules/ai-alerts?month=2026-08',
        'read_at' => now()->subMinutes(20),
        'acknowledged_at' => now()->subMinutes(20),
    ]);
    $source->forceFill(['created_at' => now()->subMinutes(31)])->saveQuietly();

    $this->artisan('energy:escalate-critical-alerts', ['--minutes' => 30])->assertSuccessful();

    expect($admin->notifications()->where('type', 'critical_alert_escalation')->count())->toBe(0);
});
