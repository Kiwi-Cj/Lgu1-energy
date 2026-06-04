<?php

namespace App\Services;

use App\Models\EnergyIncident;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Schema;

class IncidentNotificationService
{
    public function notify(EnergyIncident $incident): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
            return;
        }

        $incident->loadMissing('facility:id,name');

        $status = trim((string) ($incident->status ?? 'Pending'));
        if ($this->isResolvedStatus($status)) {
            return;
        }

        $facilityName = trim((string) ($incident->facility?->name ?? 'Unknown Facility'));
        $periodLabel = $this->periodLabel($incident);
        $severity = trim((string) ($incident->severity_label ?? $incident->severityLabel ?? 'Incident'));

        $title = 'Incident Report Pending';
        $message = "Incident report needs review for {$facilityName} ({$periodLabel}) - {$severity} [{$status}]";

        User::query()
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
            })
            ->each(function (User $recipient) use ($title, $message) {
                $exists = $recipient->notifications()
                    ->where('type', 'incident')
                    ->where('message', $message)
                    ->exists();

                if ($exists) {
                    return;
                }

                $recipient->notifications()->create([
                    'title' => $title,
                    'message' => $message,
                    'type' => 'incident',
                ]);
            });
    }

    private function isResolvedStatus(string $status): bool
    {
        $normalized = strtolower($status);

        return str_contains($normalized, 'resolved') || str_contains($normalized, 'closed');
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
