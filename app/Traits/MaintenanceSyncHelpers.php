<?php
namespace App\Traits;

use App\Models\Facility;
use App\Models\User;
use App\Support\RoleAccess;
use Carbon\Carbon;

/**
 * Shared maintenance status-transition logic used by both the web
 * MaintenanceController (Facilities Needing Maintenance page) and the
 * CIMM integration sync endpoint, so a status change behaves identically
 * regardless of which side initiated it.
 */
trait MaintenanceSyncHelpers
{
    private function maintenanceIssueTypes(): array
    {
        return [
            'Auto-flagged: High Consumption',
            'Auto-flagged: Very High Consumption',
            'Auto-flagged: Critical Consumption',
            'Electrical - Power Outage',
            'Electrical - Circuit Overload',
            'Lighting - Bulb Replacement',
            'Lighting - Fixture Repair',
            'Aircon - Not Cooling',
            'Aircon - Cleaning Needed',
            'Plumbing - Leak',
            'Plumbing - Clogged Drain',
            'Roof - Leak',
            'Roof - Gutter Cleaning',
            'Pest Control',
            'General - Preventive Check',
            'General - Other',
        ];
    }

    private function parseTriggerMonth(?string $triggerMonth): array
    {
        $raw = trim((string) $triggerMonth);
        if ($raw === '') {
            return [null, null];
        }

        foreach (['F Y', 'M Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date instanceof Carbon) {
                    return [(int) $date->month, (int) $date->year];
                }
            } catch (\Throwable $e) {
                // Keep trying the next format.
            }
        }

        return [null, null];
    }

    private function resolveFacilityName(int $facilityId, ?string $facilityName): string
    {
        $name = trim((string) ($facilityName ?? ''));
        if ($name !== '') {
            return $name;
        }

        if ($facilityId > 0) {
            $archivedName = trim((string) (Facility::withTrashed()->where('id', $facilityId)->value('name') ?? ''));
            if ($archivedName !== '') {
                return $archivedName . ' (Archived)';
            }
        }

        return '-';
    }

    private function resolveMaintenanceRemarks(?string $remarks, ?string $issueType, ?string $trend, ?string $status): string
    {
        $normalizedRemarks = trim((string) $remarks);
        $legacyRemarks = [
            '',
            '-',
            'Auto-flagged due to system-detected high energy consumption (incident auto-created).',
        ];
        if (!in_array($normalizedRemarks, $legacyRemarks, true)) {
            return $normalizedRemarks;
        }

        $issueText = strtolower((string) $issueType);
        $statusText = strtolower((string) $status);
        $trendText = strtolower((string) $trend);

        $severityKey = str_contains($issueText, 'critical')
            ? 'critical'
            : (str_contains($issueText, 'very high') ? 'very-high' : 'high');
        $statusKey = str_contains($statusText, 'completed')
            ? 'completed'
            : (str_contains($statusText, 'ongoing') ? 'ongoing' : 'pending');

        $base = match ($severityKey . ':' . $statusKey) {
            'critical:completed' => 'Critical maintenance action completed. Validate consumption stabilization for the next billing cycles.',
            'critical:ongoing' => 'Critical maintenance action is in progress. Keep temporary controls active while root-cause checks continue.',
            'critical:pending' => 'Critical consumption anomaly queued for urgent corrective maintenance and immediate technical inspection.',
            'very-high:completed' => 'Very high consumption issue completed. Continue scheduled checks to confirm sustained improvement.',
            'very-high:ongoing' => 'Very high consumption issue under corrective maintenance. Continue close monitoring during this period.',
            'very-high:pending' => 'Very high consumption anomaly queued for corrective maintenance and operating schedule validation.',
            'high:completed' => 'Maintenance task completed. Keep regular monitoring to prevent repeat deviation.',
            'high:ongoing' => 'Maintenance task is ongoing. Continue monitoring and equipment checks.',
            default => 'Maintenance task queued for review and corrective action.',
        };

        if (str_contains($trendText, 'increasing')) {
            return $base . ' Trend is increasing; prioritize root-cause analysis.';
        }

        return $base;
    }

    private function resolveEfficiencyRating(?string $issueType, ?string $maintenanceType, ?string $trend): string
    {
        $issue = strtolower((string) $issueType);
        $type = strtolower((string) $maintenanceType);
        $trendText = strtolower((string) $trend);

        if (
            str_contains($issue, 'critical')
            || str_contains($issue, 'circuit overload')
            || str_contains($issue, 'power outage')
        ) {
            return 'Low';
        }

        if (
            str_contains($issue, 'very high')
            || str_contains($issue, 'high consumption')
            || str_contains($trendText, 'increasing')
            || str_contains($type, 'corrective')
        ) {
            return 'Medium';
        }

        return 'High';
    }

    /**
     * Apply a status/schedule change to an existing Maintenance record. Used
     * by both the web "Facilities Needing Maintenance" form and the CIMM
     * integration sync endpoint, so a field is only touched when the caller
     * actually supplies it — the web form always supplies every key (so
     * behaves exactly as before), while CIMM only sends the subset of
     * fields it owns (status, scheduled_date, assigned_to, completed_date),
     * leaving issue_type/remarks/maintenance_type as Energy already has them.
     *
     * @param array{maintenance_status:string,maintenance_type?:?string,scheduled_date?:?string,assigned_to?:?string,issue_type?:?string,remarks?:?string,completed_date?:?string} $fields
     */
    private function applyMaintenanceStatusUpdate(\App\Models\Maintenance $maintenance, array $fields): void
    {
        $newStatus = trim((string) ($fields['maintenance_status'] ?? ''));
        $newIssueType = trim((string) ($fields['issue_type'] ?? ''));

        if (array_key_exists('maintenance_type', $fields)) {
            $maintenance->maintenance_type = $fields['maintenance_type'];
        }
        if (array_key_exists('scheduled_date', $fields)) {
            $maintenance->scheduled_date = $fields['scheduled_date'];
        }
        if (array_key_exists('assigned_to', $fields)) {
            $maintenance->assigned_to = $fields['assigned_to'];
        }
        if (array_key_exists('issue_type', $fields) && $newStatus !== 'Completed' && $newIssueType !== '') {
            $maintenance->issue_type = $newIssueType;
        }
        if (array_key_exists('remarks', $fields)) {
            $remarksInput = trim((string) ($fields['remarks'] ?? ''));
            $maintenance->remarks = $remarksInput !== ''
                ? $fields['remarks']
                : $this->resolveMaintenanceRemarks(null, $maintenance->issue_type, $maintenance->trend, $newStatus);
        }
        $maintenance->maintenance_status = $newStatus;
        if (array_key_exists('completed_date', $fields)) {
            $maintenance->completed_date = $fields['completed_date'];
        }
        $maintenance->save();
    }

    /**
     * Shared "after save" tail: notifications, facility/incident status sync,
     * and archiving to history when the status lands on Completed. Shared by
     * both the create and update paths in MaintenanceController::store(), and
     * by the CIMM integration sync endpoint.
     *
     * @return array{maintenance:\App\Models\Maintenance|\App\Models\MaintenanceHistory,archived:bool}
     */
    private function applyMaintenancePostSaveEffects(\App\Models\Maintenance $maintenance, ?string $previousStatus): array
    {
        $this->notifyMaintenanceStatusTransition($maintenance, $previousStatus);
        $this->syncFacilityStatusFromMaintenance($maintenance, $previousStatus);

        if (in_array($maintenance->maintenance_status, ['Ongoing', 'Completed'], true)) {
            $this->syncIncidentStatusFromMaintenance($maintenance);
        }

        if ($maintenance->maintenance_status !== 'Completed') {
            return ['maintenance' => $maintenance, 'archived' => false];
        }

        $archivedRecord = null;
        \Illuminate\Support\Facades\DB::transaction(function () use ($maintenance, &$archivedRecord) {
            $resolvedTrend = trim((string) $maintenance->trend) !== '' ? $maintenance->trend : 'Stable';
            $archivedRecord = \App\Models\MaintenanceHistory::create([
                'facility_id' => $maintenance->facility_id,
                'issue_type' => $maintenance->issue_type,
                'trigger_month' => $maintenance->trigger_month,
                'trend' => $resolvedTrend,
                'efficiency_rating' => $this->resolveEfficiencyRating(
                    $maintenance->issue_type,
                    $maintenance->maintenance_type,
                    $resolvedTrend
                ),
                'maintenance_type' => $maintenance->maintenance_type,
                'maintenance_status' => $maintenance->maintenance_status,
                'scheduled_date' => $maintenance->scheduled_date,
                'assigned_to' => $maintenance->assigned_to,
                'completed_date' => $maintenance->completed_date,
                'remarks' => $maintenance->remarks,
            ]);
            $maintenance->delete();
        });

        return ['maintenance' => $archivedRecord, 'archived' => true];
    }

    private function syncIncidentStatusFromMaintenance(\App\Models\Maintenance $maintenance): void
    {
        $statusText = strtolower((string) $maintenance->maintenance_status);
        if (!in_array($statusText, ['ongoing', 'completed'], true)) {
            return;
        }

        [$triggerMonthNum, $triggerYearNum] = $this->parseTriggerMonth($maintenance->trigger_month);
        if ($triggerMonthNum === null || $triggerYearNum === null) {
            return;
        }

        $baseQuery = \App\Models\EnergyIncident::query()
            ->where('facility_id', $maintenance->facility_id)
            ->where('month', $triggerMonthNum)
            ->where('year', $triggerYearNum);

        if ($statusText === 'ongoing') {
            $incident = (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('status', 'like', '%pending%')
                        ->orWhere('status', 'like', '%open%')
                        ->orWhere('status', 'like', '%ongoing%');
                })
                ->orderByDesc('id')
                ->first();

            if (!$incident) {
                $incident = (clone $baseQuery)->orderByDesc('id')->first();
            }

            if ($incident) {
                $incident->status = 'Ongoing';
                $incident->resolved_at = null;
                $incident->save();
            }
            return;
        }

        $incident = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('status', 'not like', '%resolved%')
                    ->where('status', 'not like', '%closed%');
            })
            ->orderByDesc('id')
            ->first();

        if (!$incident) {
            $incident = (clone $baseQuery)->orderByDesc('id')->first();
        }

        if ($incident) {
            $incident->status = 'Resolved';
            $incident->resolved_at = $maintenance->completed_date
                ? Carbon::parse($maintenance->completed_date)
                : now();
            $incident->save();
        }
    }

    private function syncFacilityStatusFromMaintenance(\App\Models\Maintenance $maintenance, ?string $previousStatus = null): void
    {
        $newStatus = strtolower(trim((string) ($maintenance->maintenance_status ?? '')));
        $oldStatus = strtolower(trim((string) ($previousStatus ?? '')));

        if ($newStatus === $oldStatus) {
            return;
        }

        if (!$maintenance->facility_id) {
            return;
        }

        $facility = Facility::find($maintenance->facility_id);
        if (!$facility) {
            return;
        }

        if ($newStatus === 'ongoing') {
            if (strtolower(trim((string) ($facility->status ?? ''))) !== 'maintenance') {
                $facility->status = 'maintenance';
                $facility->save();
            }
            return;
        }

        if ($newStatus !== 'completed') {
            return;
        }

        $hasOtherOngoing = \App\Models\Maintenance::query()
            ->where('facility_id', $maintenance->facility_id)
            ->where('maintenance_status', 'Ongoing')
            ->exists();

        if ($hasOtherOngoing) {
            return;
        }

        // Only auto-revert when the facility is currently in maintenance status.
        if (strtolower(trim((string) ($facility->status ?? ''))) === 'maintenance') {
            $facility->status = 'active';
            $facility->save();
        }
    }

    private function notifyMaintenanceStatusTransition(\App\Models\Maintenance $maintenance, ?string $previousStatus = null): void
    {
        $newStatus = strtolower(trim((string) ($maintenance->maintenance_status ?? '')));
        $oldStatus = strtolower(trim((string) ($previousStatus ?? '')));

        if (!in_array($newStatus, ['ongoing', 'completed'], true)) {
            return;
        }

        if ($newStatus === $oldStatus) {
            return;
        }

        $maintenance->loadMissing('facility:id,name');

        $facilityName = trim((string) ($maintenance->facility?->name ?? 'Unknown Facility'));
        $period = trim((string) ($maintenance->trigger_month ?? 'Unknown Period'));
        $statusLabel = $newStatus === 'ongoing' ? 'Ongoing' : 'Completed';
        $title = $newStatus === 'ongoing' ? 'Maintenance In Progress' : 'Maintenance Completed';
        $message = "Maintenance: {$facilityName} ({$period}) status updated to {$statusLabel}.";

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
                ]);
            });
    }
}
