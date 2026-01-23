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
            $avg = $facility && $facility->energyProfiles()->latest()->first() ? $facility->energyProfiles()->latest()->first()->average_monthly_kwh : null;
            $variance = ($rec->kwh_consumed && $avg !== null) ? $rec->kwh_consumed - $avg : null;
            $eui = ($rec->kwh_consumed && $facility && $facility->floor_area) ? round($rec->kwh_consumed / $facility->floor_area, 2) : null;
            $percent = ($avg && $avg != 0) ? ($rec->kwh_consumed / $avg) * 100 : 0;
            // Inverted: High: <60%, Medium: 60% to <80%, Low: >=80%
            if ($percent < 60) {
                $ratingVal = 'High';
                $highCount++;
            } elseif ($percent >= 60 && $percent < 80) {
                $ratingVal = 'Medium';
                $mediumCount++;
            } else {
                $ratingVal = 'Low';
                $lowCount++;
                $flaggedCount++;
            }
            if ($rating && $rating !== 'all' && $ratingVal !== $rating) continue;
            $efficiencyRows[] = [
                'facility_id' => $facility ? $facility->id : null,
                'facility' => $facility ? $facility->name : '-',
                'month' => ($rec->month ? date('M', mktime(0,0,0,(int)$rec->month,1)) : '-') . ' ' . $rec->year,
                'actual_kwh' => $rec->kwh_consumed,
                'avg_kwh' => $avg,
                'variance' => $variance,
                'eui' => $eui,
                'rating' => $ratingVal,
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
                        $percent = ($avg && $avg != 0) ? ($rec->kwh_consumed / $avg) * 100 : 0;
                        if ($percent < 60) {
                            $ratingVal = 'High';
                        } elseif ($percent >= 60 && $percent < 80) {
                            $ratingVal = 'Medium';
                        } else {
                            $ratingVal = 'Low';
                        }
                        $modalUsageTable[] = [
                            'month' => ($rec->month ? date('M', mktime(0,0,0,(int)$rec->month,1)) : '-') . ' ' . $rec->year,
                            'actual_kwh' => $rec->kwh_consumed,
                            'avg_kwh' => $avg,
                            'variance' => $variance,
                            'rating' => $ratingVal,
                            'status' => $rec->status,
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
