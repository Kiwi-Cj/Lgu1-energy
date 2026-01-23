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
        // Get all facilities
        $facilities = Facility::all();
        $efficiencyRows = [];
        foreach ($facilities as $facility) {
            // Get latest efficiency record by facility_id
            $eff = EnergyEfficiency::where('facility_id', $facility->id)->orderByDesc('year')->orderByDesc('month')->first();
            $eui = $eff && $eff->eui !== null ? $eff->eui : '-';
            $rating = $eff && $eff->rating !== null ? $eff->rating : '-';
            // Get last audit (maintenance completed)
            $lastAudit = Maintenance::where('facility_id', $facility->id)
                ->whereNotNull('completed_date')
                ->orderByDesc('completed_date')
                ->first();
            $lastAuditDate = $lastAudit ? date('M d, Y', strtotime($lastAudit->completed_date)) : '-';
            // Flag if last efficiency rating is Low or maintenance needed
            $flag = ($rating === 'Low') || ($lastAudit && $lastAudit->maintenance_status !== 'Completed');
            $efficiencyRows[] = [
                'facility' => $facility->name,
                'eui' => $eui,
                'rating' => $rating,
                'last_audit' => $lastAuditDate,
                'flag' => $flag,
            ];
        }
        return view('modules.reports.efficiency-summary', compact('efficiencyRows'));
    }
}
