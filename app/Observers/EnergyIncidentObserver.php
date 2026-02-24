<?php

namespace App\Observers;

use App\Models\EnergyIncident;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Schema;

class EnergyIncidentObserver
{
    public function created(EnergyIncident $incident): void
    {
        try {
            if (!Schema::hasTable('users') || !Schema::hasTable('notifications')) {
                return;
            }

            $incident->loadMissing('facility:id,name');

            $facilityName = trim((string) ($incident->facility?->name ?? 'Unknown Facility'));
            $periodLabel = $this->periodLabel($incident);
            $severity = $incident->severityLabel;
            $status = trim((string) ($incident->status ?? 'Pending'));

            $title = 'Incident Alert';
            $message = "New incident reported for {$facilityName} ({$periodLabel}) - {$severity} [{$status}]";

            $recipients = User::query()
                ->with('facilities:id')
                ->get()
                ->filter(function (User $user) use ($incident) {
                    $role = RoleAccess::normalize($user);

                    if (in_array($role, ['super_admin', 'admin', 'energy_officer'], true)) {
                        return true;
                    }

                    if ($role === 'staff' && $incident->facility_id) {
                        return $user->facilities->contains('id', (int) $incident->facility_id);
                    }

                    return false;
                });

            foreach ($recipients as $recipient) {
                $alreadyExists = $recipient->notifications()
                    ->where('type', 'incident')
                    ->where('message', $message)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $recipient->notifications()->create([
                    'title' => $title,
                    'message' => $message,
                    'type' => 'incident',
                ]);
            }
        } catch (\Throwable $e) {
            // Do not break incident creation flow if notification creation fails.
        }
    }

    private function periodLabel(EnergyIncident $incident): string
    {
        $month = (int) ($incident->month ?? 0);
        $year = (int) ($incident->year ?? 0);

        if ($month >= 1 && $month <= 12 && $year > 0) {
            return date('M Y', mktime(0, 0, 0, $month, 1, $year));
        }

        if ($incident->date_detected) {
            return $incident->date_detected->format('M d, Y');
        }

        return 'Unknown Period';
    }
}

