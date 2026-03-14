<?php

namespace App\Observers;

use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;

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

        // Legacy incident/maintenance automation is only for non-submeter streams.
        if ($this->isSubMeterRecord($record)) {
            return;
        }

        $this->syncIncidentAndMaintenance($record, $facility, $deviation, $alert);
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
            default => 'normal',
        };

        if (! in_array($severityKey, ['critical', 'very-high'], true)) {
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

        $issueType = $severityKey === 'critical'
            ? 'Auto-flagged: Critical Consumption'
            : 'Auto-flagged: Very High Consumption';

        $remarks = match ($severityKey) {
            'critical' => $trendIncreasing
                ? 'Critical consumption spike detected with increasing trend. Perform immediate load isolation and root-cause inspection.'
                : 'Critical consumption spike detected. Validate meter data and inspect major load equipment immediately.',
            default => $trendIncreasing
                ? 'Very high consumption deviation detected with increasing trend. Schedule urgent corrective checks and monitor weekly.'
                : 'Very high consumption deviation detected. Schedule corrective maintenance and review operating schedules.',
        };

        $maintenance = Maintenance::query()
            ->where('facility_id', $facility->id)
            ->where('trigger_month', $triggerMonth)
            ->where(function ($query) {
                $query->where('issue_type', 'Auto-flagged: High Consumption')
                    ->orWhere('issue_type', 'Auto-flagged: Critical Consumption')
                    ->orWhere('issue_type', 'Auto-flagged: Very High Consumption');
            })
            ->whereIn('maintenance_status', ['Pending', 'Ongoing'])
            ->first();

        if (! $maintenance) {
            Maintenance::create([
                'facility_id' => $facility->id,
                'issue_type' => $issueType,
                'trigger_month' => $triggerMonth,
                'trend' => $trendIncreasing ? 'Increasing' : 'Stable',
                'maintenance_type' => 'Corrective',
                'maintenance_status' => 'Pending',
                'scheduled_date' => null,
                'assigned_to' => null,
                'completed_date' => null,
                'remarks' => $remarks,
            ]);

            return;
        }

        $legacyRemarks = [
            '',
            'Auto-flagged due to system-detected high energy consumption (incident auto-created).',
        ];

        $existingRemarks = trim((string) ($maintenance->remarks ?? ''));
        $maintenance->issue_type = $issueType;
        $maintenance->trend = $trendIncreasing ? 'Increasing' : 'Stable';
        if (in_array($existingRemarks, $legacyRemarks, true)) {
            $maintenance->remarks = $remarks;
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
            return $severityKey === 'critical'
                ? 'Critical energy spike for this billing period was resolved after corrective action.'
                : 'Very high energy deviation for this billing period has been resolved and stabilized.';
        }

        if ($statusKey === 'open') {
            return $severityKey === 'critical'
                ? 'Critical energy spike is active and requires immediate intervention.'
                : 'Very high energy deviation is active and under close monitoring.';
        }

        return $severityKey === 'critical'
            ? 'Critical energy spike detected for this billing period and queued for urgent review.'
            : 'Very high energy deviation detected for this billing period and queued for validation.';
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
