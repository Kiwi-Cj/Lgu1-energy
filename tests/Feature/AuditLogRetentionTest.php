<?php

use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\AuditLogRetentionService;

test('audit retention removes only records older than the configured period', function () {
    Setting::setValue('retention_period', '3');
    Setting::setValue('audit_last_pruned_at', '');

    $expired = AuditLog::create([
        'action' => 'expired.test.event',
        'module' => 'test',
        'method' => 'POST',
    ]);
    AuditLog::whereKey($expired->id)->update(['created_at' => now()->subMonthsNoOverflow(4)]);
    $current = AuditLog::create([
        'action' => 'current.test.event',
        'module' => 'test',
        'method' => 'POST',
    ]);
    AuditLog::whereKey($current->id)->update(['created_at' => now()->subMonth()]);

    $result = app(AuditLogRetentionService::class)->prune(force: true);

    expect($result['deleted'])->toBe(1)
        ->and($result['retention_months'])->toBe(3)
        ->and(AuditLog::find($expired->id))->toBeNull()
        ->and(AuditLog::find($current->id))->not->toBeNull();
});

test('automatic audit cleanup runs at most once within 24 hours', function () {
    Setting::setValue('retention_period', '3');
    Setting::setValue('audit_last_pruned_at', now()->toDateTimeString());

    $result = app(AuditLogRetentionService::class)->prune();

    expect($result['skipped'])->toBeTrue()
        ->and($result['reason'])->toContain('last 24 hours');
});
