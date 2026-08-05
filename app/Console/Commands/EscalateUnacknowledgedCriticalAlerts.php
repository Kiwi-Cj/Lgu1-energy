<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use App\Support\RoleAccess;
use App\Support\SystemSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EscalateUnacknowledgedCriticalAlerts extends Command
{
    protected $signature = 'energy:escalate-critical-alerts {--minutes=30 : Acknowledgement deadline in minutes}';

    protected $description = 'Escalate critical energy alerts that remain unacknowledged';

    public function handle(): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $deadline = now()->subMinutes($minutes);
        $alerts = Notification::query()
            ->where('type', 'energy_record_alert')
            ->whereNotNull('target_url')
            ->whereNull('acknowledged_at')
            ->where('created_at', '>=', now()->subDay())
            ->where('created_at', '<=', $deadline)
            ->whereRaw('LOWER(message) LIKE ?', ['%[critical]%'])
            ->get()
            ->groupBy('message');

        $admins = User::query()->get()
            ->filter(fn (User $user) => RoleAccess::in($user, ['super_admin', 'admin']));
        $escalated = 0;

        foreach ($alerts as $message => $copies) {
            $alreadyAcknowledged = Notification::query()
                ->where('type', 'energy_record_alert')
                ->where('message', $message)
                ->whereNotNull('acknowledged_at')
                ->exists();
            if ($alreadyAcknowledged) {
                continue;
            }

            $source = $copies->first();
            $escalationMessage = "Escalation: No assigned responder acknowledged this Critical alert within {$minutes} minutes. {$message}";

            foreach ($admins as $admin) {
                $notification = $admin->notifications()->firstOrCreate(
                    ['type' => 'critical_alert_escalation', 'message' => $escalationMessage],
                    [
                        'title' => 'Unacknowledged Critical Alert',
                        'target_url' => $source?->target_url,
                    ]
                );

                if (! $notification->wasRecentlyCreated) {
                    continue;
                }

                $escalated++;
                if (SystemSettings::emailNotificationsEnabled()
                    && filter_var($admin->email, FILTER_VALIDATE_EMAIL)
                ) {
                    try {
                        Mail::raw(
                            "{$escalationMessage}\n\nOpen the alert: {$source?->target_url}",
                            fn ($mail) => $mail->to($admin->email)->subject('Escalation: Unacknowledged Critical Energy Alert')
                        );
                    } catch (\Throwable $e) {
                        // Bell escalation remains available if email delivery fails.
                    }
                }
            }
        }

        $this->info("Critical alert escalations created: {$escalated}");

        return self::SUCCESS;
    }
}
