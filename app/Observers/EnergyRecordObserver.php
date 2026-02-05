<?php
namespace App\Observers;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\EnergyEfficiency;

class EnergyRecordObserver
{
    public function deleted(EnergyRecord $record)
    {
        $facility = $record->facility;
        $month = $record->month ? date('M', mktime(0,0,0,(int)$record->month,1)) : '-';
        $year = $record->year;
        if ($facility) {
            // Delete related energy efficiency (by facility_id)
            \App\Models\EnergyEfficiency::where('facility_id', $facility->id)
                ->where('month', $month)
                ->where('year', $year)
                ->delete();
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
        // Always use first3months_data for avg_kwh if available
        $first3mo = $facility ? \DB::table('first3months_data')->where('facility_id', $facility->id)->first() : null;
        if ($first3mo) {
            $avg = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
        } else {
            $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
            $avg = $profile ? $profile->baseline_kwh : null;
        }
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

        $autoFlagged = ($ratingVal === 'Low' || $trendIncreasing) ? 1 : 0;

        EnergyEfficiency::updateOrCreate([
            'facility_id' => $facility ? $facility->id : null,
            'month' => $record->month ? date('M', mktime(0,0,0,(int)$record->month,1)) : '-',
            'year' => $record->year,
        ], [
            'actual_kwh' => $record->actual_kwh,
            'avg_kwh' => $avg !== null ? $avg : 0,
            'variance' => $variance !== null ? $variance : 0,
            'eui' => $eui,
            'rating' => $ratingVal,
            'auto_flagged' => $autoFlagged,
        ]);

        // If Low Efficiency or auto-flagged, create or update a maintenance record for this month
        if ($facility) {
            $issueType = $ratingVal === 'Low' ? 'High Consumption / Inefficient' : 'Trend Increasing';
            $triggerMonth = $record->month ? date('M Y', mktime(0,0,0,(int)$record->month,1,$record->year)) : '-';
            $maintenance = \App\Models\Maintenance::where('facility_id', $facility->id)
                ->where('trigger_month', $triggerMonth)
                ->where('issue_type', $issueType)
                ->whereIn('maintenance_status', ['Pending','Ongoing'])
                ->first();
            if ($autoFlagged) {
                if (!$maintenance) {
                    \App\Models\Maintenance::create([
                        'facility_id' => $facility->id,
                        'issue_type' => $issueType,
                        'trigger_month' => $triggerMonth,
                        'efficiency_rating' => $ratingVal,
                        'trend' => $trendIncreasing ? 'Increasing' : 'Stable',
                        'maintenance_type' => 'Preventive',
                        'maintenance_status' => 'Pending',
                        'scheduled_date' => null,
                        'assigned_to' => null,
                        'completed_date' => null,
                        'remarks' => 'Auto-flagged due to ' . ($ratingVal === 'Low' ? 'low efficiency rating' : 'increasing consumption trend'),
                    ]);
                } else {
                    // Update efficiency_rating and trend if changed
                    $maintenance->efficiency_rating = $ratingVal;
                    $maintenance->trend = $trendIncreasing ? 'Increasing' : 'Stable';
                    $maintenance->save();
                }
            } else if ($maintenance) {
                // If no longer auto-flagged, update efficiency_rating and trend
                $maintenance->efficiency_rating = $ratingVal;
                $maintenance->trend = $trendIncreasing ? 'Increasing' : 'Stable';
                $maintenance->save();
            }
        }

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
        if ($deviation !== null) {
            if ($size === 'Small') {
                $alert = $deviation > 30 ? 'High' : ($deviation > 15 ? 'Medium' : 'Low');
            } elseif ($size === 'Medium') {
                $alert = $deviation > 20 ? 'High' : ($deviation > 10 ? 'Medium' : 'Low');
            } elseif ($size === 'Large') {
                $alert = $deviation > 15 ? 'High' : ($deviation > 5 ? 'Medium' : 'Low');
            } else /* Extra Large */ {
                $alert = $deviation > 10 ? 'High' : ($deviation > 3 ? 'Medium' : 'Low');
            }
        }
        if ($alert === 'High' && $facility) {
            // Update if exists, else create, always set energy_record_id, month, year, size, and deviation_percent
            $incident = \App\Models\EnergyIncident::where('facility_id', $facility->id)
                ->where('description', 'like', '%High energy consumption detected for this billing period.%')
                ->where('date_detected', now()->toDateString())
                ->where('month', $record->month)
                ->where('year', $record->year)
                ->first();
            if (!$incident) {
                \App\Models\EnergyIncident::create([
                    'facility_id' => $facility->id,
                    'energy_record_id' => $record->id,
                    'month' => $record->month,
                    'year' => $record->year,
                    'size' => $size,
                    'deviation_percent' => $deviation,
                    'description' => 'High energy consumption detected for this billing period.',
                    'status' => 'Open',
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
        }
    }
}
