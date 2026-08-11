<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\EnergySavingRecommendation;
use App\Models\Notification;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Schema;

class RecommendationNotificationService
{
    public const TYPE = 'energy_recommendation';

    public function notifySystemRecommendation(EnergyRecord $record): void
    {
        if (! $this->isAvailable() || ! $this->isMainMeterRecord($record)) {
            return;
        }

        $record->loadMissing(['facility:id,name', 'meter:id,meter_type']);
        if (! $record->facility) {
            return;
        }

        User::query()
            ->where('status', 'active')
            ->whereHas('facilities', fn ($query) => $query->whereKey((int) $record->facility_id))
            ->get()
            ->filter(fn (User $user) => RoleAccess::is($user, 'staff'))
            ->each(fn (User $user) => $this->ensureForUser($user, $record));
    }

    public function ensureForUser(User $user, EnergyRecord $record): ?Notification
    {
        if (! $this->isAvailable() || ! RoleAccess::is($user, 'staff') || ! $this->isMainMeterRecord($record)) {
            return null;
        }

        $record->loadMissing(['facility:id,name', 'meter:id,meter_type']);
        if (! $record->facility) {
            return null;
        }

        $periodLabel = date('F Y', mktime(0, 0, 0, (int) $record->month, 1, (int) $record->year));
        $targetUrl = $this->targetUrl($record);

        return $user->notifications()->firstOrCreate(
            [
                'type' => self::TYPE,
                'target_url' => $targetUrl,
            ],
            [
                'title' => 'Energy Recommendation',
                'message' => "A system-generated recommendation is available for {$record->facility->name} ({$periodLabel}).",
                'read_at' => null,
            ]
        );
    }

    public function notifyManualRecommendation(EnergySavingRecommendation $recommendation): void
    {
        if (! $this->isAvailable() || $recommendation->status !== 'approved' || ! $recommendation->assigned_to) {
            return;
        }

        $recipient = User::query()->find($recommendation->assigned_to);
        if (! $recipient || ! RoleAccess::is($recipient, 'staff')) {
            return;
        }

        $records = EnergyRecord::query()
            ->with(['facility:id,name', 'meter:id,meter_type'])
            ->where('facility_id', $recommendation->facility_id)
            ->where('year', $recommendation->year)
            ->where('month', $recommendation->month)
            ->whereHas('meter', fn ($query) => $query->where('meter_type', 'main'))
            ->get();

        foreach ($records as $record) {
            $notification = $this->ensureForUser($recipient, $record);
            if (! $notification) {
                continue;
            }

            $periodLabel = date(
                'F Y',
                mktime(0, 0, 0, (int) $recommendation->month, 1, (int) $recommendation->year)
            );
            $notification->forceFill([
                'title' => 'New Action Recommendation',
                'message' => "A new action recommendation was assigned to you for {$record->facility->name} ({$periodLabel}).",
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->save();
        }
    }

    private function targetUrl(EnergyRecord $record): string
    {
        return route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => (int) $record->facility_id,
            'record_id' => (int) $record->id,
            'month' => sprintf('%04d-%02d', (int) $record->year, (int) $record->month),
        ]);
    }

    private function isMainMeterRecord(EnergyRecord $record): bool
    {
        $record->loadMissing('meter:id,meter_type');

        return strtolower((string) ($record->meter?->meter_type ?? '')) === 'main';
    }

    private function isAvailable(): bool
    {
        return Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'target_url');
    }
}
