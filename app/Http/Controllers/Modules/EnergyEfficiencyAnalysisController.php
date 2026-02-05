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
        $effQuery = \App\Models\EnergyEfficiency::query();
        if ($facilityId) {
            $effQuery->where('facility_id', $facilityId);
        }
        if ($month) {
            $parts = explode('-', $month);
            if (count($parts) == 2) {
                $effQuery->where('year', $parts[0]);
                $effQuery->where('month', date('M', mktime(0,0,0,(int)$parts[1],1)));
            }
        }
        if ($rating && $rating !== 'all') {
            $effQuery->where('rating', $rating);
        }
        $efficiencyRows = [];
        $highCount = $mediumCount = $lowCount = $flaggedCount = 0;
        $effRecords = $effQuery->with('facility')->get();
        $currentMonth = date('M');
        $currentYear = date('Y');
        $highCount = $mediumCount = $lowCount = $flaggedCount = 0;
        foreach ($effRecords as $rec) {
            $facility = $rec->facility;
            $rowMonth = ($rec->month ?? '-') . ' ' . $rec->year;
            $efficiencyRows[] = [
                'facility_id' => $facility ? $facility->id : null,
                'facility' => $facility ? $facility->name : '-',
                'month' => $rowMonth,
                'actual_kwh' => $rec->actual_kwh,
                'avg_kwh' => $rec->avg_kwh,
                'variance' => $rec->variance,
                'eui' => $rec->eui,
                'rating' => $rec->rating,
                'status' => $rec->rating,
                'alert' => null,
                'deviation' => null,
            ];
            // Count only if current month/year
            if ($rec->month === $currentMonth && $rec->year == $currentYear) {
                if ($rec->rating === 'High') $highCount++;
                elseif ($rec->rating === 'Medium') $mediumCount++;
                elseif ($rec->rating === 'Low') {
                    $lowCount++;
                    $flaggedCount++;
                }
            }
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
                    $modalAvgKwh = $profile ? $profile->baseline_kwh : null;
                    $modalMainSource = $profile ? $profile->main_energy_source : null;
                    $modalBackupPower = $profile ? $profile->backup_power : null;
                    $modalNumMeters = $profile ? $profile->number_of_meters : null;
                    $modalFloorArea = $facility->floor_area;
                    $modalEui = ($modalFloorArea && isset($efficiencyRows[0]['actual_kwh'])) ? round($efficiencyRows[0]['actual_kwh'] / $modalFloorArea, 2) : null;
                    // Usage table: use EnergyEfficiency records for this facility
                    $usageRecords = \App\Models\EnergyEfficiency::where('facility_id', $facility->id)->orderBy('year','desc')->orderBy('month','desc')->get();
                    foreach ($usageRecords as $rec) {
                        $modalUsageTable[] = [
                            'month' => ($rec->month ?? '-') . ' ' . $rec->year,
                            'actual_kwh' => $rec->actual_kwh,
                            'avg_kwh' => $rec->avg_kwh,
                            'variance' => $rec->variance,
                            'rating' => $rec->rating,
                            'status' => $rec->rating,
                            'alert' => null,
                            'deviation' => null,
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
