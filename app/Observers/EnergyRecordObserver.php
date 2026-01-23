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
    }
    public function saved(EnergyRecord $record)
    {
        $facility = $record->facility;
        $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
        $avg = $profile ? $profile->average_monthly_kwh : null;
        $variance = ($record->kwh_consumed && $avg !== null) ? $record->kwh_consumed - $avg : null;
        $eui = ($record->kwh_consumed && $facility && $facility->floor_area) ? round($record->kwh_consumed / $facility->floor_area, 2) : null;
        $percent = ($avg && $avg != 0) ? ($record->kwh_consumed / $avg) * 100 : 0;
        // Inverted: High: <60%, Medium: 60% to <80%, Low: >=80%
        if ($percent < 60) {
            $ratingVal = 'High';
        } elseif ($percent >= 60 && $percent < 80) {
            $ratingVal = 'Medium';
        } else {
            $ratingVal = 'Low';
        }

        // Check for trend: last 3 months increasing
        $trendIncreasing = false;
        if ($facility) {
            $recent = $facility->energyRecords()->orderByDesc('year')->orderByDesc('month')->take(3)->pluck('kwh_consumed');
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
            'actual_kwh' => $record->kwh_consumed,
            'avg_kwh' => $avg,
            'variance' => $variance,
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
    }
}
