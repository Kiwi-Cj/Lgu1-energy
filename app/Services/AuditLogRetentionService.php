<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AuditLogRetentionService
{
    public function prune(bool $force = false): array
    {
        if (! Schema::hasTable('audit_logs')) {
            return ['deleted' => 0, 'skipped' => true, 'reason' => 'audit_logs table is missing'];
        }

        $settings = Schema::hasTable('settings')
            ? Setting::getMany(['retention_period', 'audit_last_pruned_at'], [
                'retention_period' => '3',
                'audit_last_pruned_at' => null,
            ])
            : ['retention_period' => '3', 'audit_last_pruned_at' => null];

        $retentionMonths = max(1, min(120, (int) ($settings['retention_period'] ?? 3)));
        $lastPrunedAt = trim((string) ($settings['audit_last_pruned_at'] ?? ''));

        if (! $force && $lastPrunedAt !== '') {
            try {
                if (Carbon::parse($lastPrunedAt)->greaterThan(now()->subDay())) {
                    return [
                        'deleted' => 0,
                        'skipped' => true,
                        'reason' => 'already pruned within the last 24 hours',
                        'retention_months' => $retentionMonths,
                    ];
                }
            } catch (\Throwable) {
                // An invalid timestamp should not prevent retention cleanup.
            }
        }

        $cutoff = now()->subMonthsNoOverflow($retentionMonths)->startOfDay();
        $deleted = AuditLog::query()->where('created_at', '<', $cutoff)->delete();

        if (Schema::hasTable('settings')) {
            Setting::setValue('audit_last_pruned_at', now()->toDateTimeString());
        }

        return [
            'deleted' => $deleted,
            'skipped' => false,
            'cutoff' => $cutoff,
            'retention_months' => $retentionMonths,
        ];
    }
}
