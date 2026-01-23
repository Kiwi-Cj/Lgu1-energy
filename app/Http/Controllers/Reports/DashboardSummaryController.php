<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyEfficiency;
use App\Models\EnergyRecord;
use App\Models\Bill;

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

        $billQuery = Bill::query();
        if ($request->filled('date_start')) {
            $billQuery->whereRaw("CONCAT(year, '-', LPAD(month,2,'0')) >= ?", [$request->date_start]);
        }
        if ($request->filled('date_end')) {
            $billQuery->whereRaw("CONCAT(year, '-', LPAD(month,2,'0')) <= ?", [$request->date_end]);
        }
        $totalCost = $billQuery->sum('total_bill');

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
