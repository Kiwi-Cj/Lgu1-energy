<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;

class EnergyEfficiencyAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = $request->query('facility_id');
        $month = $request->query('month');
        $rating = $request->query('rating');
        $facilities = Facility::all();
        $records = EnergyRecord::with('facility');
        if ($facilityId) {
            $records->where('facility_id', $facilityId);
        }
        if ($month) {
            $parts = explode('-', $month);
            if (count($parts) == 2) {
                $records->where('year', $parts[0]);
                $records->where('month', $parts[1]);
            }
        }
        $efficiencyRows = [];
        $highCount = $mediumCount = $lowCount = $flaggedCount = 0;
        foreach ($records->get() as $rec) {
            $facility = $rec->facility;
            $avgKwh = optional($facility?->energyProfiles()->latest()->first())->average_monthly_kwh;
            $variance = ($rec->kwh_consumed && $avgKwh !== null) ? $rec->kwh_consumed - $avgKwh : null;
            $eui = ($rec->kwh_consumed && $facility && $facility->floor_area) ? round($rec->kwh_consumed / $facility->floor_area, 2) : null;

            // Dynamic threshold logic (same as Energy Monitoring)
            $size = 'small';
            if ($avgKwh > 3000) {
                $size = 'extra_large';
            } elseif ($avgKwh > 1500) {
                $size = 'large';
            } elseif ($avgKwh > 500) {
                $size = 'medium';
            }
            $percent = 0.3; // default for small
            if ($size === 'medium') $percent = 0.2;
            if ($size === 'large') $percent = 0.15;
            if ($size === 'extra_large') $percent = 0.10;
            $dynamicThreshold = $avgKwh ? $avgKwh * (1 + $percent) : null;
            if ($dynamicThreshold !== null && $rec->kwh_consumed >= $dynamicThreshold) {
                $ratingVal = 'Low'; // Not efficient
                $lowCount++;
                $flaggedCount++;
            } elseif ($dynamicThreshold !== null && $rec->kwh_consumed >= $avgKwh) {
                $ratingVal = 'Medium';
                $mediumCount++;
            } else {
                $ratingVal = 'High'; // Efficient
                $highCount++;
            }
            if ($rating && $rating !== 'all' && $ratingVal !== $rating) continue;
            $efficiencyRows[] = [
                'facility_id' => $facility ? $facility->id : null,
                'facility' => $facility ? $facility->name : '-',
                'month' => ($rec->month ? date('M', mktime(0,0,0,(int)$rec->month,1)) : '-') . ' ' . $rec->year,
                'actual_kwh' => $rec->kwh_consumed,
                'avg_kwh' => $avgKwh,
                'variance' => $variance,
                'eui' => $eui,
                'rating' => $ratingVal,
                'status' => $ratingVal,
            ];
        }
        // Prepare modal variables from the first facility (if any)
        $modalFacilityName = $modalFacilityType = $modalFacilityLocation = $modalAvgKwh = $modalMainSource = $modalBackupPower = $modalNumMeters = $modalFloorArea = $modalEui = $modalLastMaint = $modalNextMaint = $modalMaintRemarks = $modalMaintLink = null;
        $modalUsageTable = $modalRecommendations = [];
        if (count($efficiencyRows)) {
            $firstFacilityId = $efficiencyRows[0]['facility_id'] ?? null;
            if ($firstFacilityId) {
                $facility = Facility::find($firstFacilityId);
                if ($facility) {
                    $modalFacilityName = $facility->name;
                    $modalFacilityType = $facility->type;
                    $modalFacilityLocation = $facility->barangay;
                    $profile = $facility->energyProfiles()->latest()->first();
                    $modalAvgKwh = $profile ? $profile->average_monthly_kwh : null;
                    $modalMainSource = $profile ? $profile->main_energy_source : null;
                    $modalBackupPower = $profile ? $profile->backup_power : null;
                    $modalNumMeters = $profile ? $profile->number_of_meters : null;
                    $modalFloorArea = $facility->floor_area;
                    $modalEui = ($modalFloorArea && isset($efficiencyRows[0]['actual_kwh'])) ? round($efficiencyRows[0]['actual_kwh'] / $modalFloorArea, 2) : null;
                    // Usage table
                    $records = $facility->energyRecords()->orderBy('year','desc')->orderBy('month','desc')->get();
                    foreach ($records as $rec) {
                        $avg = $profile ? $profile->average_monthly_kwh : null;
                        $variance = ($rec->kwh_consumed && $avg !== null) ? $rec->kwh_consumed - $avg : null;
                        // Dynamic threshold logic (Low/Medium/High)
                        $size = 'small';
                        if ($avg > 3000) {
                            $size = 'extra_large';
                        } elseif ($avg > 1500) {
                            $size = 'large';
                        } elseif ($avg > 500) {
                            $size = 'medium';
                        }
                        $percent = 0.3;
                        if ($size === 'medium') $percent = 0.2;
                        if ($size === 'large') $percent = 0.15;
                        if ($size === 'extra_large') $percent = 0.10;
                        $dynamicThreshold = $avg ? $avg * (1 + $percent) : null;
                        if ($dynamicThreshold !== null && $rec->kwh_consumed >= $dynamicThreshold) {
                            $ratingVal = 'Low';
                        } elseif ($dynamicThreshold !== null && $rec->kwh_consumed >= $avg) {
                            $ratingVal = 'Medium';
                        } else {
                            $ratingVal = 'High';
                        }
                        $modalUsageTable[] = [
                            'month' => ($rec->month ? date('M', mktime(0,0,0,(int)$rec->month,1)) : '-') . ' ' . $rec->year,
                            'actual_kwh' => $rec->kwh_consumed,
                            'avg_kwh' => $avg,
                            'variance' => $variance,
                            'rating' => $ratingVal,
                            'status' => $ratingVal,
                        ];
                    }
                    // Maintenance
                    $lastMaint = $facility->maintenance()->whereNotNull('completed_date')->orderBy('completed_date','desc')->first();
                    $nextMaint = $facility->maintenance()->where('maintenance_status','Scheduled')->orderBy('scheduled_date','asc')->first();
                    $modalLastMaint = $lastMaint ? $lastMaint->completed_date : null;
                    $modalNextMaint = $nextMaint ? $nextMaint->scheduled_date : null;
                    $modalMaintRemarks = $lastMaint ? $lastMaint->remarks : null;
                    $modalMaintLink = url('/modules/maintenance/index?facility_id='.$facility->id);
                    // Recommendations (simple example)
                    if (count($modalUsageTable) && collect($modalUsageTable)->contains('rating', 'Low')) {
                        $modalRecommendations[] = 'Schedule preventive maintenance';
                        $modalRecommendations[] = 'Inspect HVAC / lighting system';
                        $modalRecommendations[] = 'Optimize operating hours';
                    }
                    if (count($modalUsageTable) >= 3) {
                        $t = collect($modalUsageTable)->pluck('actual_kwh');
                        $n = $t->count();
                        if ($n >= 3 && $t[$n-3] < $t[$n-2] && $t[$n-2] < $t[$n-1]) {
                            $modalRecommendations[] = 'Investigate unusual load increase';
                            $modalRecommendations[] = 'Check electrical wiring / leaks';
                        }
                    }
                    if (empty($modalRecommendations)) $modalRecommendations[] = 'No special recommendations.';
                }
            }
        }
        return view('modules.energy-efficiency-analysis.index', compact(
            'facilities','efficiencyRows','highCount','mediumCount','lowCount','flaggedCount',
            'modalFacilityName','modalFacilityType','modalFacilityLocation','modalAvgKwh','modalMainSource','modalBackupPower','modalNumMeters','modalFloorArea','modalEui','modalUsageTable','modalLastMaint','modalNextMaint','modalMaintRemarks','modalMaintLink','modalRecommendations'
        ));
    }
}
