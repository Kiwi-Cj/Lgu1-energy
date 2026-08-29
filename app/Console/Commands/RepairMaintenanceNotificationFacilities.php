<?php

namespace App\Console\Commands;

use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use App\Models\Notification;
use Illuminate\Console\Command;

class RepairMaintenanceNotificationFacilities extends Command
{
    protected $signature = 'energy:repair-maintenance-notification-facilities';

    protected $description = 'Replace Unknown Facility in maintenance notifications when the matching maintenance record has a facility.';

    public function handle(): int
    {
        $updated = 0;
        $skipped = 0;

        Notification::query()
            ->where('type', 'maintenance')
            ->where('message', 'like', 'Maintenance: Unknown Facility (%')
            ->orderBy('id')
            ->chunkById(100, function ($notifications) use (&$updated, &$skipped) {
                foreach ($notifications as $notification) {
                    if (! preg_match('/^Maintenance: Unknown Facility \(([^)]+)\) status updated to (Ongoing|Completed)\.$/', (string) $notification->message, $matches)) {
                        $skipped++;
                        continue;
                    }

                    [, $period, $status] = $matches;
                    $candidates = Maintenance::query()
                        ->where('trigger_month', $period)
                        ->get()
                        ->concat(MaintenanceHistory::query()
                            ->where('trigger_month', $period)
                            ->get());

                    $names = $candidates
                        ->map(function ($maintenance) {
                            if (method_exists($maintenance, 'resolvedFacilityName')) {
                                return $maintenance->resolvedFacilityName();
                            }

                            $maintenance->loadMissing('facility:id,name');
                            return trim((string) ($maintenance->facility?->name ?? ''));
                        })
                        ->filter(fn ($name) => $name !== '' && $name !== 'Facility not linked')
                        ->unique()
                        ->values();

                    if ($names->count() !== 1) {
                        // A status can have changed again after its notification
                        // was sent. In that case, use only a uniquely nearest
                        // maintenance update from the same period (within 48h).
                        $nearest = $candidates
                            ->map(function ($maintenance) use ($notification) {
                                $name = method_exists($maintenance, 'resolvedFacilityName')
                                    ? $maintenance->resolvedFacilityName()
                                    : trim((string) ($maintenance->facility?->name ?? ''));

                                return [
                                    'name' => $name,
                                    'seconds' => $maintenance->updated_at
                                        ? abs($maintenance->updated_at->diffInSeconds($notification->created_at, false))
                                        : PHP_INT_MAX,
                                ];
                            })
                            ->filter(fn (array $candidate) => $candidate['name'] !== '' && $candidate['name'] !== 'Facility not linked')
                            ->sortBy('seconds')
                            ->values();

                        if ($nearest->isEmpty() || $nearest->first()['seconds'] > 172800
                            || ($nearest->count() > 1 && $nearest->first()['seconds'] === $nearest->get(1)['seconds'])) {
                            $skipped++;
                            continue;
                        }

                        $names = collect([$nearest->first()['name']]);
                    }

                    $notification->update([
                        'message' => "Maintenance: {$names->first()} ({$period}) status updated to {$status}.",
                    ]);
                    $updated++;
                }
            });

        $this->info("Updated {$updated} notification(s); skipped {$skipped} ambiguous or unlinked notification(s).");

        return self::SUCCESS;
    }
}
