<?php

namespace App\Observers;

use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\User;
use App\Services\RecommendationNotificationService;
use App\Support\BaselineResolver;
use App\Support\EnergyCost;
use App\Support\EnergyAlertRouting;
use App\Support\RoleAccess;
use App\Support\SystemSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class EnergyRecordObserver
{
    public function created(EnergyRecord $record): void
    {
        $this->notifyReviewersOfSubmission($record);
    }

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
        if (! $record->wasRecentlyCreated
            && strtolower(trim((string) $record->input_source)) === 'cprf'
            && strtolower(trim((string) $record->external_source)) !== 'uman_cprf'
            && $record->wasChanged([
                'actual_kwh',
                'year',
                'month',
                'day',
                'energy_cost',
                'rate_per_kwh',
                'recorded_by',
                'recorded_by_name',
            ])
        ) {
            $this->notifyReviewersOfSubmission($record, true);
        }

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

        try {
            app(RecommendationNotificationService::class)->notifySystemRecommendation($record);
        } catch (\Throwable $e) {
            // Recommendation notifications must not block monthly record persistence.
        }

        $this->notifyRecipientsOfAlert($record, $facility, $deviation, $alert);
        $this->notifyRecipientsOfProjectedCost($record, $facility);

        // Legacy incident/maintenance automation is only for non-submeter streams.
        if ($this->isSubMeterRecord($record)) {
            return;
        }

        if (! SystemSettings::enabled('auto_log_incident', true)) {
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
            $targetUrl = route('modules.ai-alerts.index', ['month' => sprintf('%04d-%02d', $year, $month)]);

            $this->alertRecipients($facility)
                ->each(function (User $recipient) use ($message, $title, $targetUrl, $alertKey, $facilityName, $periodLabel) {
                    $notification = $recipient->notifications()->firstOrCreate(
                        ['type' => 'energy_record_alert', 'message' => $message],
                        ['title' => $title, 'target_url' => $targetUrl]
                    );

                    if ($notification->wasRecentlyCreated
                        && $alertKey === 'critical'
                        && in_array(RoleAccess::normalize($recipient), ['energy_officer', 'engineer'], true)
                        && SystemSettings::emailNotificationsEnabled()
                        && filter_var($recipient->email, FILTER_VALIDATE_EMAIL)
                    ) {
                        try {
                            Mail::raw(
                                "A critical energy alert was detected for {$facilityName} ({$periodLabel}).\n\n{$message}\n\nOpen AI Alerts: {$targetUrl}",
                                fn ($mail) => $mail->to($recipient->email)->subject("Critical Energy Alert - {$facilityName}")
                            );
                        } catch (\Throwable $e) {
                            // The bell alert remains available if email delivery fails.
                        }
                    }
                });
        } catch (\Throwable $e) {
            // Notification failure must not block monthly record persistence.
        }
    }

    private function notifyRecipientsOfProjectedCost(EnergyRecord $record, Facility $facility): void
    {
        try {
            if ($this->isSubMeterRecord($record) || ! Schema::hasTable('notifications')) {
                return;
            }

            $month = (int) $record->month;
            $year = (int) $record->year;
            if ($month < 1 || $month > 12 || $year < 1) {
                return;
            }

            $period = Carbon::create($year, $month, 1);
            $previousPeriod = $period->copy()->subMonth();
            $scope = static function ($query): void {
                $query->where(function ($meterScope) {
                    $meterScope->whereNull('meter_id')
                        ->orWhereHas('meter', fn ($meter) => $meter->where('meter_type', 'main'));
                });
            };

            $currentRecords = EnergyRecord::query()
                ->where('facility_id', $facility->id)
                ->where('year', $year)->where('month', $month)
                ->tap($scope)->get();
            $previousRecords = EnergyRecord::query()
                ->where('facility_id', $facility->id)
                ->where('year', $previousPeriod->year)->where('month', $previousPeriod->month)
                ->tap($scope)->get();

            $currentCost = (float) $currentRecords->sum(fn ($item) => EnergyCost::cost($item));
            $previousCost = (float) $previousRecords->sum(fn ($item) => EnergyCost::cost($item));
            if ($currentCost <= 0 || $previousCost <= 0) {
                return;
            }

            $latestDay = (int) $currentRecords->max('day');
            $projectedCost = $period->isSameMonth(now()) && $latestDay > 0
                ? ($currentCost / max(1, $latestDay)) * $period->daysInMonth
                : $currentCost;
            if ($projectedCost <= $previousCost) {
                return;
            }

            $increase = (($projectedCost - $previousCost) / $previousCost) * 100;
            $periodLabel = $period->format('M Y');
            $facilityName = trim((string) ($facility->name ?: 'Unknown Facility'));
            $message = sprintf(
                'Projected cost alert: %s (%s) may reach ₱%s, %.1f%% above the previous month budget of ₱%s.',
                $facilityName,
                $periodLabel,
                number_format($projectedCost, 2),
                $increase,
                number_format($previousCost, 2)
            );
            $targetUrl = route('modules.ai-alerts.index', ['month' => $period->format('Y-m')]);

            $this->alertRecipients($facility)->each(function (User $recipient) use ($message, $targetUrl) {
                $recipient->notifications()->firstOrCreate(
                    ['type' => 'ai_cost_alert', 'message' => $message],
                    ['title' => 'Projected Cost Alert', 'target_url' => $targetUrl]
                );
            });
        } catch (\Throwable $e) {
            // Cost analysis must not block saving the energy reading.
        }
    }

    private function alertRecipients(Facility $facility)
    {
        return User::query()
            ->with('facilities:id')
            ->get()
            ->filter(function (User $user) use ($facility) {
                $role = RoleAccess::normalize($user);
                if (in_array($role, ['super_admin', 'admin', 'energy_officer', 'engineer'], true)) {
                    return true;
                }

                return $role === 'staff'
                    && $user->facilities->contains('id', (int) $facility->id);
            });
    }

    private function notifyReviewersOfSubmission(EnergyRecord $record, bool $isUpdate = false): void
    {
        try {
            // UMAN imports are already approved by the source system and do
            // not belong in the manual monthly-record review queue.
            if (strtolower(trim((string) $record->external_source)) === 'uman_cprf') {
                return;
            }

            if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
                return;
            }

            $record->loadMissing(['facility:id,name', 'recordedBy:id,full_name,name,username']);
            $facilityName = trim((string) ($record->facility?->name ?? 'Unknown Facility'));
            $source = strtolower(trim((string) ($record->input_source ?? 'manual')));
            $isIntegrated = $source === 'cprf';
            $encoderName = trim((string) ($record->recorded_by_name ?? ''));

            if ($encoderName === '') {
                $encoderName = trim((string) (
                    $record->recordedBy?->full_name
                    ?? $record->recordedBy?->name
                    ?? $record->recordedBy?->username
                    ?? ''
                ));
            }
            if ($encoderName === '') {
                $encoderName = $isIntegrated ? 'CPRF Integration' : 'Unknown user';
            }

            $month = max(1, min(12, (int) ($record->month ?? now()->month)));
            $year = (int) ($record->year ?? now()->year);
            $periodLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
            $sourceLabel = $isIntegrated ? 'CPRF Integration' : 'Manual Entry';
            $action = $isUpdate ? 'updated' : 'submitted';
            $message = "{$encoderName} {$action} the {$periodLabel} monthly record for {$facilityName} via {$sourceLabel}.";
            $targetUrl = route('monthly-record-activity.index');

            User::query()
                ->get()
                ->filter(fn (User $recipient) => RoleAccess::in($recipient, ['super_admin', 'admin', 'engineer']))
                ->each(function (User $recipient) use ($record, $message, $targetUrl, $isUpdate) {
                    if ((int) $recipient->id === (int) $record->recorded_by) {
                        return;
                    }

                    $recipient->notifications()->firstOrCreate(
                        [
                            'type' => 'monthly_record_submission',
                            'message' => $message,
                        ],
                        [
                            'title' => $isUpdate ? 'Monthly Record Updated' : 'New Monthly Record',
                            'target_url' => $targetUrl,
                        ]
                    );
                });
        } catch (\Throwable $e) {
            // Activity notifications must not block monthly record persistence.
        }
    }

    private function resolveBaseline(EnergyRecord $record, Facility $facility): ?float
    {
        return BaselineResolver::forRecord($record, $facility);
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

        if (! EnergyAlertRouting::requiresIncident($alert)) {
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
            'Critical energy spike detected for this billing period and queued for urgent review.',
            'Very high energy deviation detected for this billing period and queued for validation.',
            'High energy deviation detected for this billing period and queued for validation.',
        ];

        $payload = [
            'energy_record_id' => $record->id,
            'category' => 'energy_anomaly',
            'source' => strtolower(trim((string) ($record->input_source ?? ''))) === 'cprf' ? 'cprf' : 'auto',
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
            $payload['status'] = 'Open';
            $payload['date_detected'] = now()->toDateString();
            $payload['created_by'] = $record->recorded_by ?? null;
            $incident = EnergyIncident::create($payload);
        } else {
            $incident->fill($payload);
            if (! $incident->date_detected) {
                $incident->date_detected = now()->toDateString();
            }
            if (! $incident->status) {
                $incident->status = 'Open';
            }
            $incident->save();
        }

        $this->upsertMaintenanceFromIncidentSeverity($facility, $record, $incident, $severityKey);
    }

    private function upsertMaintenanceFromIncidentSeverity(
        Facility $facility,
        EnergyRecord $record,
        EnergyIncident $incident,
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
        $hasEnergyIncidentId = Schema::hasColumn('maintenance', 'energy_incident_id');

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

            if ($hasEnergyIncidentId) {
                $payload['energy_incident_id'] = $incident->id;
            }

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
        if ($hasEnergyIncidentId && ! $maintenance->energy_incident_id) {
            $maintenance->energy_incident_id = $incident->id;
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

        return 'open';
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
                'critical' => 'Critical energy spike detected and forwarded to CIMM for urgent maintenance action.',
                'very-high' => 'Very high energy deviation detected and forwarded to CIMM for maintenance action.',
                default => 'High energy deviation detected and forwarded to CIMM for maintenance assessment.',
            };
        }

        return $severityKey === 'critical'
            ? 'Critical energy spike detected and forwarded to CIMM for urgent maintenance action.'
            : ($severityKey === 'very-high'
                ? 'Very high energy deviation detected and forwarded to CIMM for maintenance action.'
                : 'High energy deviation detected and forwarded to CIMM for maintenance assessment.');
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
