<?php
namespace App\Observers;

use App\Models\EnergyRecord;
use App\Models\Facility;


class EnergyRecordObserver
{
    public function deleted(EnergyRecord $record)
    {
        $facility = $record->facility;
        $month = $record->month ? date('M', mktime(0,0,0,(int)$record->month,1)) : '-';
        $year = $record->year;
        if ($facility) {
            // Delete related energy efficiency (by facility_id)
                // EnergyEfficiency model deleted; nothing to clean up
            // Delete related maintenance records for this facility and trigger month
            $triggerMonth = $record->month ? date('M Y', mktime(0,0,0,(int)$record->month,1,$record->year)) : '-';
            \App\Models\Maintenance::where('facility_id', $facility->id)
                ->where('trigger_month', $triggerMonth)
                ->delete();
        }
        // Delete related incidents with this energy_record_id
        \App\Models\EnergyIncident::where('energy_record_id', $record->id)->delete();
    }
    public function saved(EnergyRecord $record)
    {
        $facility = $record->facility;
        // first3months_data table removed; fallback to baseline_kwh
        $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
        $avg = $profile ? $profile->baseline_kwh : null;
        $variance = ($record->actual_kwh && $avg !== null) ? $record->actual_kwh - $avg : 0;
        $eui = ($record->actual_kwh && $facility && $facility->floor_area) ? round($record->actual_kwh / $facility->floor_area, 2) : 0; // Never null
        $percent = ($avg && $avg != 0) ? ($record->actual_kwh / $avg) * 100 : 0;

        // ALERT LOGIC
        $size = 'small';
        if ($avg > 3000) {
            $size = 'extra_large';
        } elseif ($avg > 1500) {
            $size = 'large';
        } elseif ($avg > 500) {
            $size = 'medium';
        }
        $deviation = ($record->actual_kwh && $avg) ? (($record->actual_kwh - $avg) / $avg) * 100 : null;
        $alert = null;
        if ($deviation !== null) {
            if ($size === 'small') {
                $alert = $deviation > 30 ? 'High' : ($deviation > 15 ? 'Medium' : 'Low');
            } elseif ($size === 'medium') {
                $alert = $deviation > 20 ? 'High' : ($deviation > 10 ? 'Medium' : 'Low');
            } elseif ($size === 'large') {
                $alert = $deviation > 15 ? 'High' : ($deviation > 5 ? 'Medium' : 'Low');
            } else /* extra_large */ {
                $alert = $deviation > 10 ? 'High' : ($deviation > 3 ? 'Medium' : 'Low');
            }
        }

        // CORRECTED: High alert = Low efficiency, else use percent
        if ($alert === 'High') {
            $ratingVal = 'Low';
        } elseif ($percent < 60) {
            $ratingVal = 'Low';
        } elseif ($percent >= 60 && $percent < 80) {
            $ratingVal = 'Medium';
        } else {
            $ratingVal = 'High';
        }

        // Check for trend: last 3 months increasing
        $trendIncreasing = false;
        if ($facility) {
            $recent = $facility->energyRecords()->orderByDesc('year')->orderByDesc('month')->take(3)->pluck('actual_kwh');
            if ($recent->count() === 3 && $recent[0] > $recent[1] && $recent[1] > $recent[2]) {
                $trendIncreasing = true;
            }
        }

        // (Removed: auto-flagged maintenance for low efficiency or trend increasing. Now only auto-flag if auto-incident is triggered.)

        // --- INCIDENT LOGIC: keep this aligned with monthly-record 5-level thresholds ---
        $baselineForIncident = is_numeric($record->baseline_kwh)
            ? (float) $record->baseline_kwh
            : (is_numeric($avg) ? (float) $avg : null);
        $deviation = ($baselineForIncident && $baselineForIncident != 0)
            ? round((($record->actual_kwh - $baselineForIncident) / $baselineForIncident) * 100, 2)
            : null;

        $resolveIncidentSeverity = function (?float $deviationValue, ?float $baselineValue): array {
            if ($deviationValue === null) {
                return ['key' => 'normal', 'label' => 'Normal'];
            }

            $baselineValue = $baselineValue ?? 0.0;
            if ($baselineValue <= 1000) {
                $t = ['level5' => 80, 'level4' => 50, 'level3' => 30, 'level2' => 15];
            } elseif ($baselineValue <= 3000) {
                $t = ['level5' => 60, 'level4' => 40, 'level3' => 20, 'level2' => 10];
            } elseif ($baselineValue <= 10000) {
                $t = ['level5' => 30, 'level4' => 20, 'level3' => 12, 'level2' => 5];
            } else {
                $t = ['level5' => 20, 'level4' => 12, 'level3' => 7, 'level2' => 3];
            }

            if ($deviationValue > $t['level5']) {
                return ['key' => 'critical', 'label' => 'Critical'];
            }
            if ($deviationValue > $t['level4']) {
                return ['key' => 'very-high', 'label' => 'Very High'];
            }
            if ($deviationValue > $t['level3']) {
                return ['key' => 'high', 'label' => 'High'];
            }
            if ($deviationValue > $t['level2']) {
                return ['key' => 'warning', 'label' => 'Warning'];
            }

            return ['key' => 'normal', 'label' => 'Normal'];
        };

        $severity = $resolveIncidentSeverity($deviation, $baselineForIncident);
        $isIncidentLevel = in_array($severity['key'], ['critical', 'very-high'], true);
        $legacyIncidentDescriptions = [
            'High energy consumption detected for this billing period.',
            'System detected unusually high energy consumption for this period. Please review and validate.',
        ];
        $resolveIncidentStatusKey = function (?string $statusValue): string {
            $statusRaw = strtolower(trim((string) $statusValue));
            if (str_contains($statusRaw, 'resolved') || str_contains($statusRaw, 'closed')) {
                return 'resolved';
            }
            if (str_contains($statusRaw, 'open') || str_contains($statusRaw, 'ongoing')) {
                return 'open';
            }
            return 'pending';
        };
        $buildIncidentDescription = function (string $severityKey, string $statusKey): string {
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
        };

        if ($facility && $isIncidentLevel) {
            // Deduplicate by facility + billing month/year (not by today's date).
            $incident = \App\Models\EnergyIncident::where('facility_id', $facility->id)
                ->where('month', (int) $record->month)
                ->where('year', (int) $record->year)
                ->first();

            $statusKey = $resolveIncidentStatusKey($incident?->status);
            $generatedDescription = $buildIncidentDescription($severity['key'], $statusKey);

            $incidentPayload = [
                'energy_record_id' => $record->id,
                'month' => (int) $record->month,
                'year' => (int) $record->year,
                'deviation_percent' => $deviation,
            ];
            $currentDescription = trim((string) ($incident?->description ?? ''));
            $shouldAutoSetDescription = !$incident
                || $currentDescription === ''
                || in_array($currentDescription, $legacyIncidentDescriptions, true);
            if ($shouldAutoSetDescription) {
                $incidentPayload['description'] = $generatedDescription;
            }

            if (!$incident) {
                $incidentPayload['facility_id'] = $facility->id;
                $incidentPayload['status'] = 'Pending';
                $incidentPayload['date_detected'] = now()->toDateString();
                $incidentPayload['created_by'] = $record->recorded_by ?? null;
                $incident = \App\Models\EnergyIncident::create($incidentPayload);
            } else {
                $incident->fill($incidentPayload);
                if (!$incident->date_detected) {
                    $incident->date_detected = now()->toDateString();
                }
                if (!$incident->status) {
                    $incident->status = 'Pending';
                }
                $incident->save();
            }

            // --- AUTO-FLAG MAINTENANCE LOGIC: If auto-incident, also auto-flag maintenance ---
            $triggerMonth = $record->month ? date('M Y', mktime(0,0,0,(int)$record->month,1,$record->year)) : '-';
            $maintenanceIssueType = $severity['key'] === 'critical'
                ? 'Auto-flagged: Critical Consumption'
                : 'Auto-flagged: Very High Consumption';
            $maintenanceRemarks = match ($severity['key']) {
                'critical' => $trendIncreasing
                    ? 'Critical consumption spike detected with increasing trend. Perform immediate load isolation and root-cause inspection.'
                    : 'Critical consumption spike detected. Validate meter data and inspect major load equipment immediately.',
                default => $trendIncreasing
                    ? 'Very high consumption deviation detected with increasing trend. Schedule urgent corrective checks and monitor weekly.'
                    : 'Very high consumption deviation detected. Schedule corrective maintenance and review operating schedules.',
            };
            $legacyMaintenanceRemarks = [
                'Auto-flagged due to system-detected high energy consumption (incident auto-created).',
            ];
            $maintenance = \App\Models\Maintenance::where('facility_id', $facility->id)
                ->where('trigger_month', $triggerMonth)
                ->where(function ($query) {
                    $query->where('issue_type', 'Auto-flagged: High Consumption')
                        ->orWhere('issue_type', 'Auto-flagged: Critical Consumption')
                        ->orWhere('issue_type', 'Auto-flagged: Very High Consumption');
                })
                ->whereIn('maintenance_status', ['Pending','Ongoing'])
                ->first();
            if (!$maintenance) {
                \App\Models\Maintenance::create([
                    'facility_id' => $facility->id,
                    'issue_type' => $maintenanceIssueType,
                    'trigger_month' => $triggerMonth,
                    'trend' => $trendIncreasing ? 'Increasing' : 'Stable',
                    'maintenance_type' => 'Corrective',
                    'maintenance_status' => 'Pending',
                    'scheduled_date' => null,
                    'assigned_to' => null,
                    'completed_date' => null,
                    'remarks' => $maintenanceRemarks,
                ]);
            } else {
                $existingRemarks = trim((string) ($maintenance->remarks ?? ''));
                $maintenance->issue_type = $maintenanceIssueType;
                $maintenance->trend = $trendIncreasing ? 'Increasing' : 'Stable';
                if ($existingRemarks === '' || in_array($existingRemarks, $legacyMaintenanceRemarks, true)) {
                    $maintenance->remarks = $maintenanceRemarks;
                }
                $maintenance->save();
            }
        }
    }
}
