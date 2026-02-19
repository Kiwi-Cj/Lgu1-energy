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

        // --- INCIDENT LOGIC: Create EnergyIncident if High alert ---
        // Compute deviation and alert level (same as in Blade)
        $deviation = ($avg && $avg != 0) ? round((($record->actual_kwh - $avg) / $avg) * 100, 2) : null;
        $size = 'Medium';
        if ($avg <= 1000) {
            $size = 'Small';
        } elseif ($avg <= 3000) {
            $size = 'Medium';
        } elseif ($avg <= 10000) {
            $size = 'Large';
        } else {
            $size = 'Extra Large';
        }
        $alert = null;
        $alertLevel = 1;
        if ($deviation !== null) {
            if ($size === 'Small') {
                if ($deviation > 40) { $alert = 'High'; $alertLevel = 5; }
                elseif ($deviation > 35) { $alert = 'High'; $alertLevel = 4; }
                elseif ($deviation > 30) { $alert = 'Medium'; $alertLevel = 3; }
                elseif ($deviation > 15) { $alert = 'Medium'; $alertLevel = 2; }
                else { $alert = 'Low'; $alertLevel = 1; }
            } elseif ($size === 'Medium') {
                if ($deviation > 30) { $alert = 'High'; $alertLevel = 5; }
                elseif ($deviation > 25) { $alert = 'High'; $alertLevel = 4; }
                elseif ($deviation > 20) { $alert = 'Medium'; $alertLevel = 3; }
                elseif ($deviation > 10) { $alert = 'Medium'; $alertLevel = 2; }
                else { $alert = 'Low'; $alertLevel = 1; }
            } elseif ($size === 'Large') {
                if ($deviation > 25) { $alert = 'High'; $alertLevel = 5; }
                elseif ($deviation > 20) { $alert = 'High'; $alertLevel = 4; }
                elseif ($deviation > 15) { $alert = 'Medium'; $alertLevel = 3; }
                elseif ($deviation > 5) { $alert = 'Medium'; $alertLevel = 2; }
                else { $alert = 'Low'; $alertLevel = 1; }
            } else /* Extra Large */ {
                if ($deviation > 20) { $alert = 'High'; $alertLevel = 5; }
                elseif ($deviation > 15) { $alert = 'High'; $alertLevel = 4; }
                elseif ($deviation > 10) { $alert = 'Medium'; $alertLevel = 3; }
                elseif ($deviation > 3) { $alert = 'Medium'; $alertLevel = 2; }
                else { $alert = 'Low'; $alertLevel = 1; }
            }
        }
        // Only create incident if alertLevel is 4 or 5
        if ($facility && $alertLevel >= 4) {
            // Update if exists, else create, always set energy_record_id, month, year, size, and deviation_percent
            $incident = \App\Models\EnergyIncident::where('facility_id', $facility->id)
                ->where('description', 'like', '%High energy consumption detected for this billing period.%')
                ->where('date_detected', now()->toDateString())
                ->where('month', $record->month)
                ->where('year', $record->year)
                ->first();
            if (!$incident) {
                $incident = \App\Models\EnergyIncident::create([
                    'facility_id' => $facility->id,
                    'energy_record_id' => $record->id,
                    'month' => $record->month,
                    'year' => $record->year,
                    'size' => $size,
                    'deviation_percent' => $deviation,
                    'description' => 'High energy consumption detected for this billing period.',
                    'status' => 'Pending',
                    'date_detected' => now()->toDateString(),
                ]);
            } else {
                $incident->energy_record_id = $record->id;
                $incident->month = $record->month;
                $incident->year = $record->year;
                $incident->size = $size;
                $incident->deviation_percent = $deviation;
                $incident->save();
            }

            // --- AUTO-FLAG MAINTENANCE LOGIC: If auto-incident, also auto-flag maintenance ---
            $triggerMonth = $record->month ? date('M Y', mktime(0,0,0,(int)$record->month,1,$record->year)) : '-';
            $maintenance = \App\Models\Maintenance::where('facility_id', $facility->id)
                ->where('trigger_month', $triggerMonth)
                ->where('issue_type', 'Auto-flagged: High Consumption')
                ->whereIn('maintenance_status', ['Pending','Ongoing'])
                ->first();
            if (!$maintenance) {
                \App\Models\Maintenance::create([
                    'facility_id' => $facility->id,
                    'issue_type' => 'Auto-flagged: High Consumption',
                    'trigger_month' => $triggerMonth,
                    'trend' => $trendIncreasing ? 'Increasing' : 'Stable',
                    'maintenance_type' => 'Corrective',
                    'maintenance_status' => 'Pending',
                    'scheduled_date' => null,
                    'assigned_to' => null,
                    'completed_date' => null,
                    'remarks' => 'Auto-flagged due to system-detected high energy consumption (incident auto-created).',
                ]);
            }
        }
    }
}
