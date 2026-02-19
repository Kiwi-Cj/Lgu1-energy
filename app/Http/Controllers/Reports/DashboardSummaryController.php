<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;

use App\Models\EnergyRecord;
// use App\Models\Bill; // removed

class DashboardSummaryController extends Controller
{
    public function summary(Request $request)
    {
        $totalFacilities = Facility::where('status', 'Active')->count();

        $energyQuery = EnergyRecord::query();
        if ($request->filled('date_start')) {
            $energyQuery->whereRaw("CONCAT(year, '-', LPAD(month,2,'0')) >= ?", [$request->date_start]);
        }
        if ($request->filled('date_end')) {
            $energyQuery->whereRaw("CONCAT(year, '-', LPAD(month,2,'0')) <= ?", [$request->date_end]);
        }
        $totalKwh = $energyQuery->sum('kwh_consumed');

            // Bill query removed
            $totalCost = 0;

        $lowEfficiencyCount = EnergyEfficiency::where('rating', 'Low')
            ->whereIn('facility_id', Facility::where('status', 'Active')->pluck('id'))
            ->distinct('facility_id')
            ->count('facility_id');

        return response()->json([
            'totalFacilities' => $totalFacilities,
            'totalKwh' => $totalKwh,
            'totalCost' => $totalCost,
            'lowEfficiencyCount' => $lowEfficiencyCount,
        ]);
    }
}
