<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyEfficiency;
use App\Models\Maintenance;

class EfficiencySummaryReportController extends Controller
{
    public function show(Request $request)
    {
        $facilities = Facility::all();
        $selectedFacility = $request->input('facility_id');
        $selectedRating = $request->input('rating');
        $filteredFacilities = $facilities;
        if ($selectedFacility) {
            $filteredFacilities = $filteredFacilities->where('id', $selectedFacility);
        }
        $efficiencyRows = [];
        foreach ($filteredFacilities as $facility) {
            $eff = EnergyEfficiency::where('facility_id', $facility->id)->orderByDesc('year')->orderByDesc('month')->first();
            $eui = $eff && $eff->eui !== null ? $eff->eui : '-';
            $rating = $eff && $eff->rating !== null ? $eff->rating : '-';
            if ($selectedRating && $selectedRating !== 'all' && $selectedRating !== $rating) {
                continue;
            }
            $lastAudit = Maintenance::where('facility_id', $facility->id)
                ->whereNotNull('completed_date')
                ->orderByDesc('completed_date')
                ->first();
            $lastAuditDate = $lastAudit ? date('M d, Y', strtotime($lastAudit->completed_date)) : '-';
            $flag = ($rating === 'Low') || ($lastAudit && $lastAudit->maintenance_status !== 'Completed');
            $efficiencyRows[] = [
                'facility' => $facility->name,
                'eui' => $eui,
                'rating' => $rating,
                'last_audit' => $lastAuditDate,
                'flag' => $flag,
            ];
        }
        return view('modules.reports.efficiency-summary', compact('efficiencyRows', 'facilities', 'selectedFacility', 'selectedRating'));
    }
}
