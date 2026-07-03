<?php

namespace App\Observers;

use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Schema;

class EnergyRecordObserver
{
    public function deleted(EnergyRecord $record): void
    {
        // For soft deletes, keep related records so monthly entries can be restored from archive.
        if (method_exists($record, 'isForceDeleting') && ! $record->isForceDeleting()) {
            return;
        }

        if ($this->isSubMeterRecord($record)) {
            return;
        }

        EnergyIncident::where('energy_record_id', $record->id)->delete();
    }

    public function saved(EnergyRecord $record): void
    {
        $record->loadMissing(['facility.energyProfiles', 'meter']);
        $facility = $record->facility;
        if (! $facility) {
            return;
        }

        $baseline = $this->resolveBaseline($record, $facility);
        $actualKwh = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
        $deviation = EnergyRecord::calculateDeviation($actualKwh, $baseline);
        $alert = EnergyRecord::resolveAlertLevel($deviation, $baseline);

        $updates = [];
        if ($this->shouldUpdateNumeric($record->baseline_kwh, $baseline)) {
            $updates['baseline_kwh'] = $baseline;
        }
        if ($this->shouldUpdateNumeric($record->deviation, $deviation)) {
            $updates['deviation'] = $deviation;
        }
        if ($this->shouldUpdateString($record->alert, $alert)) {
            $updates['alert'] = $alert;
        }

        if ($updates !== []) {
            $record->forceFill($updates)->saveQuietly();
        }

        $this->notifyRecipientsOfAlert($record, $facility, $deviation, $alert);

        // Legacy incident/maintenance automation is only for non-submeter streams.
        if ($this->isSubMeterRecord($record)) {
            return;
        }

        $this->syncIncidentAndMaintenance($record, $facility, $deviation, $alert);
    }

    private function notifyRecipientsOfAlert(
        EnergyRecord $record,
        Facility $facility,
        ?float $deviation,
        string $alert
    ): void {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
                return;
            }

            $alertKey = strtolower(trim($alert));
            if (! in_array($alertKey, ['high', 'very high', 'critical'], true)) {
                return;
            }

            $month = (int) ($record->month ?? 0);
            $year = (int) ($record->year ?? 0);
            if ($month <= 0 || $year <= 0) {
                return;
            }

            $facilityName = trim((string) ($facility->name ?? 'Unknown Facility'));
            $periodLabel = date('M Y', mktime(0, 0, 0, $month, 1, $year));
            $scopeLabel = $this->isSubMeterRecord($record) ? 'Submeter' : 'Main meter';
            $meterName = trim((string) ($record->meter?->meter_name ?? $scopeLabel));
            $level = ucwords($alertKey);
            $deviationLabel = $deviation !== null ? ' by ' . number_format($deviation, 2) . '%' : '';

            $title = 'Energy Alert';
            $message = "Alert: {$scopeLabel} {$meterName} at {$facilityName} ({$periodLabel}) increased{$deviationLabel} [{$level}]";

            User::query()
                ->with('facilities:id')
                ->get()
                ->filter(function (User $user) use ($facility) {
                    $role = RoleAccess::normalize($user);

                    if (in_array($role, ['super_admin', 'admin', 'energy_officer', 'engineer'], true)) {
                        return true;
                    }

                    if ($role === 'staff') {
                        return $user->facilities->contains('id', (int) $facility->id);
                    }

                    return false;
                })
                ->each(function (User $recipient) use ($message, $title) {
                    $exists = $recipient->notifications()
                        ->where('type', 'energy_record_alert')
                        ->where('message', $message)
                        ->exists();

                    if ($exists) {
                        return;
                    }

                    $recipient->notifications()->create([
                        'title' => $title,
                        'message' => $message,
                        'type' => 'energy_record_alert',
                    ]);
                });
        } catch (\Throwable $e) {
            // Notification failure must not block monthly record persistence.
        }
    }

    private function resolveBaseline(EnergyRecord $record, Facility $facility): ?float
    {
        if (is_numeric($record->baseline_kwh)) {
            return round((float) $record->baseline_kwh, 2);
        }

        if ($record->meter && is_numeric($record->meter->baseline_kwh)) {
            return round((float) $record->meter->baseline_kwh, 2);
        }

        $profile = $facility->energyProfiles()->latest()->first();
        if ($profile && is_numeric($profile->baseline_kwh)) {
            return round((float) $profile->baseline_kwh, 2);
        }

        if (is_numeric($facility->baseline_kwh)) {
            return round((float) $facility->baseline_kwh, 2);
        }

        return null;
    }

    private function syncIncidentAndMaintenance(
        EnergyRecord $record,
        Facility $facility,
        ?float $deviation,
        string $alert
    ): void {
        $alertKey = strtolower(trim($alert));
        $severityKey = match ($alertKey) {
            'critical' => 'critical',
            'very high' => 'very-high',
            'high' => 'high',
            default => 'normal',
        };

        if (! in_array($severityKey, ['critical', 'very-high', 'high'], true)) {
            return;
        }

        $month = (int) ($record->month ?? 0);
        $year = (int) ($record->year ?? 0);
        if ($month <= 0 || $year <= 0) {
            return;
        }

        $incident = EnergyIncident::query()
            ->where('facility_id', $facility->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $statusKey = $this->resolveIncidentStatusKey($incident?->status);
        $description = $this->buildIncidentDescription($severityKey, $statusKey);
        $legacyDescriptions = [
            'High energy consumption detected for this billing period.',
            'System detected unusually high energy consumption for this period. Please review and validate.',
        ];

        $payload = [
            'energy_record_id' => $record->id,
            'month' => $month,
            'year' => $year,
            'deviation_percent' => $deviation,
        ];

        $currentDescription = trim((string) ($incident?->description ?? ''));
        $shouldAutofillDescription = ! $incident
            || $currentDescription === ''
            || in_array($currentDescription, $legacyDescriptions, true);
        if ($shouldAutofillDescription) {
            $payload['description'] = $description;
        }

        if (! $incident) {
            $payload['facility_id'] = $facility->id;
            $payload['status'] = 'Pending';
            $payload['date_detected'] = now()->toDateString();
            $payload['created_by'] = $record->recorded_by ?? null;
            EnergyIncident::create($payload);
        } else {
            $incident->fill($payload);
            if (! $incident->date_detected) {
                $incident->date_detected = now()->toDateString();
            }
            if (! $incident->status) {
                $incident->status = 'Pending';
            }
            $incident->save();
        }

        $this->upsertMaintenanceFromIncidentSeverity($facility, $record, $severityKey);
    }

    private function upsertMaintenanceFromIncidentSeverity(
        Facility $facility,
        EnergyRecord $record,
        string $severityKey
    ): void {
        if (! Schema::hasTable('maintenance')) {
            return;
        }

        $hasTriggerMonth = Schema::hasColumn('maintenance', 'trigger_month');
        $hasIssueType = Schema::hasColumn('maintenance', 'issue_type');
        $hasTrend = Schema::hasColumn('maintenance', 'trend');
        $hasMaintenanceType = Schema::hasColumn('maintenance', 'maintenance_type');
        $hasMaintenanceStatus = Schema::hasColumn('maintenance', 'maintenance_status');
        $hasScheduledDate = Schema::hasColumn('maintenance', 'scheduled_date');
        $hasAssignedTo = Schema::hasColumn('maintenance', 'assigned_to');
        $hasCompletedDate = Schema::hasColumn('maintenance', 'completed_date');
        $hasRemarks = Schema::hasColumn('maintenance', 'remarks');
        $hasDescription = Schema::hasColumn('maintenance', 'description');

        $triggerMonth = date('M Y', mktime(0, 0, 0, (int) $record->month, 1, (int) $record->year));

        $recentUsage = $facility->energyRecords()
            ->where(function ($query) {
                $query->whereNull('meter_id')
                    ->orWhereHas('meter', fn ($m) => $m->where('meter_type', 'main'));
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(3)
            ->pluck('actual_kwh')
            ->values();

        $trendIncreasing = $recentUsage->count() === 3
            && $recentUsage[0] > $recentUsage[1]
            && $recentUsage[1] > $recentUsage[2];

        $issueType = match ($severityKey) {
            'critical' => 'Auto-flagged: Critical Consumption',
            'very-high' => 'Auto-flagged: Very High Consumption',
            default => 'Auto-flagged: High Consumption',
        };

        $remarks = match ($severityKey) {
            'critical' => $trendIncreasing
                ? 'Critical consumption spike detected with increasing trend. Perform immediate load isolation and root-cause inspection.'
                : 'Critical consumption spike detected. Validate meter data and inspect major load equipment immediately.',
            'very-high' => $trendIncreasing
                ? 'Very high consumption deviation detected with increasing trend. Schedule urgent corrective checks and monitor weekly.'
                : 'Very high consumption deviation detected. Schedule corrective maintenance and review operating schedules.',
            default => $trendIncreasing
                ? 'High consumption deviation detected with increasing trend. Schedule corrective inspection and monitor closely.'
                : 'High consumption deviation detected. Schedule corrective maintenance and validate operating schedules.',
        };

        $maintenanceQuery = Maintenance::query()
            ->where('facility_id', $facility->id);

        if ($hasTriggerMonth) {
            $maintenanceQuery->where('trigger_month', $triggerMonth);
        }

        if ($hasIssueType) {
            $maintenanceQuery->where(function ($query) {
                $query->where('issue_type', 'Auto-flagged: High Consumption')
                    ->orWhere('issue_type', 'Auto-flagged: Critical Consumption')
                    ->orWhere('issue_type', 'Auto-flagged: Very High Consumption');
            });
        }

        if ($hasMaintenanceStatus) {
            $maintenanceQuery->whereIn('maintenance_status', ['Pending', 'Ongoing']);
        }

        $maintenance = $maintenanceQuery->first();

        if (! $maintenance) {
            $payload = [
                'facility_id' => $facility->id,
            ];

            if ($hasIssueType) {
                $payload['issue_type'] = $issueType;
            }
            if ($hasTriggerMonth) {
                $payload['trigger_month'] = $triggerMonth;
            }
            if ($hasTrend) {
                $payload['trend'] = $trendIncreasing ? 'Increasing' : 'Stable';
            }
            if ($hasMaintenanceType) {
                $payload['maintenance_type'] = 'Corrective';
            }
            if ($hasMaintenanceStatus) {
                $payload['maintenance_status'] = 'Pending';
            }
            if ($hasScheduledDate) {
                $payload['scheduled_date'] = null;
            }
            if ($hasAssignedTo) {
                $payload['assigned_to'] = null;
            }
            if ($hasCompletedDate) {
                $payload['completed_date'] = null;
            }
            if ($hasRemarks) {
                $payload['remarks'] = $remarks;
            } elseif ($hasDescription) {
                $payload['description'] = $remarks;
            }

            Maintenance::create($payload);

            return;
        }

        $legacyRemarks = [
            '',
            'Auto-flagged due to system-detected high energy consumption (incident auto-created).',
        ];

        $existingRemarks = trim((string) (($hasRemarks ? $maintenance->remarks : ($hasDescription ? $maintenance->description : '')) ?? ''));
        if ($hasIssueType) {
            $maintenance->issue_type = $issueType;
        }
        if ($hasTrend) {
            $maintenance->trend = $trendIncreasing ? 'Increasing' : 'Stable';
        }
        if (in_array($existingRemarks, $legacyRemarks, true)) {
            if ($hasRemarks) {
                $maintenance->remarks = $remarks;
            } elseif ($hasDescription) {
                $maintenance->description = $remarks;
            }
        }
        $maintenance->save();
    }

    private function resolveIncidentStatusKey(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));
        if (str_contains($normalized, 'resolved') || str_contains($normalized, 'closed')) {
            return 'resolved';
        }
        if (str_contains($normalized, 'open') || str_contains($normalized, 'ongoing')) {
            return 'open';
        }

        return 'pending';
    }

    private function buildIncidentDescription(string $severityKey, string $statusKey): string
    {
        if ($statusKey === 'resolved') {
            return match ($severityKey) {
                'critical' => 'Critical energy spike for this billing period was resolved after corrective action.',
                'very-high' => 'Very high energy deviation for this billing period has been resolved and stabilized.',
                default => 'High energy deviation for this billing period has been resolved and stabilized.',
            };
        }

        if ($statusKey === 'open') {
            return match ($severityKey) {
                'critical' => 'Critical energy spike is active and requires immediate intervention.',
                'very-high' => 'Very high energy deviation is active and under close monitoring.',
                default => 'High energy deviation is active and under monitoring.',
            };
        }

        return $severityKey === 'critical'
            ? 'Critical energy spike detected for this billing period and queued for urgent review.'
            : ($severityKey === 'very-high'
                ? 'Very high energy deviation detected for this billing period and queued for validation.'
                : 'High energy deviation detected for this billing period and queued for validation.');
    }

    private function isSubMeterRecord(EnergyRecord $record): bool
    {
        if (! $record->meter) {
            return false;
        }

        return strtolower((string) ($record->meter->meter_type ?? '')) === 'sub';
    }

    private function shouldUpdateNumeric(mixed $current, ?float $target): bool
    {
        $currentNumeric = is_numeric($current) ? round((float) $current, 2) : null;
        $targetNumeric = $target !== null ? round((float) $target, 2) : null;
        return $currentNumeric !== $targetNumeric;
    }

    private function shouldUpdateString(mixed $current, string $target): bool
    {
        return trim((string) $current) !== trim($target);
    }
}
