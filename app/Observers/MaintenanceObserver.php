<?php

namespace App\Observers;

use App\Models\Maintenance;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Schema;

class MaintenanceObserver
{
    public function created(Maintenance $maintenance): void
    {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
                return;
            }

            $status = strtolower(trim((string) ($maintenance->maintenance_status ?? 'pending')));
            if (in_array($status, ['completed', 'resolved', 'closed'], true)) {
                return;
            }

            $maintenance->loadMissing('facility:id,name');

            $facilityName = trim((string) ($maintenance->facility?->name ?? 'Unknown Facility'));
            $period = trim((string) ($maintenance->trigger_month ?? 'Unknown Period'));
            $issue = trim((string) ($maintenance->issue_type ?? 'Maintenance Required'));
            $statusLabel = $status !== '' ? ucfirst($status) : 'Pending';

            $title = 'Maintenance Required';
            $message = "Maintenance needed for {$facilityName} ({$period}) - {$issue} [{$statusLabel}]";

            User::query()
                ->with('facilities:id')
                ->get()
                ->filter(function (User $user) use ($maintenance) {
                    $role = RoleAccess::normalize($user);

                    if (in_array($role, ['super_admin', 'admin', 'energy_officer'], true)) {
                        return true;
                    }

                    if ($role === 'staff' && $maintenance->facility_id) {
                        return $user->facilities->contains('id', (int) $maintenance->facility_id);
                    }

                    return false;
                })
                ->each(function (User $user) use ($title, $message) {
                    $exists = $user->notifications()
                        ->where('type', 'maintenance')
                        ->where('message', $message)
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();

                    if ($exists) {
                        return;
                    }

                    $user->notifications()->create([
                        'title' => $title,
                        'message' => $message,
                        'type' => 'maintenance',
                        'target_url' => route('modules.maintenance.index'),
                    ]);
                });
        } catch (\Throwable) {
            // Keep maintenance creation available even if notification delivery fails.
        }
    }
}
