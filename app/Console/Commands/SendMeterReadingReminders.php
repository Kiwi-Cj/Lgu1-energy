<?php

namespace App\Console\Commands;

use App\Services\MeterReadingReminderService;
use Illuminate\Console\Command;

class SendMeterReadingReminders extends Command
{
    protected $signature = 'energy:send-reading-reminders {--force : Send reminders regardless of schedule day}';

    protected $description = 'Send meter reading reminders to assigned staff and officers for missing scheduled logs';

    public function handle(MeterReadingReminderService $service): int
    {
        $force = (bool) $this->option('force');
        $result = $service->sendReminders($force);

        if (($result['status'] ?? '') === 'disabled') {
            $this->warn('Meter reading reminders are disabled in System Settings.');
            return self::SUCCESS;
        }

        if (($result['status'] ?? '') === 'skipped') {
            $this->line('Meter reading reminders skipped: ' . ($result['reason'] ?? 'Not scheduled today.'));
            return self::SUCCESS;
        }

        $sent = (int) ($result['sent'] ?? 0);
        $facilities = $result['facilities_reminded'] ?? [];
        $week = $result['week_number'] ?? 1;

        $this->info("Meter reading reminders sent: {$sent} notification(s) for Week {$week}.");
        if (! empty($facilities)) {
            $this->line('Facilities notified: ' . implode(', ', $facilities));
        }

        return self::SUCCESS;
    }
}
