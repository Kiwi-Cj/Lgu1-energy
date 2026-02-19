<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;

use App\Models\Maintenance;

class EfficiencySummaryReportController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $facilities = ($role === 'staff') ? $user->facilities : Facility::all();
        $selectedFacility = $request->input('facility_id');
        $selectedRating = $request->input('rating');
        $filteredFacilities = $facilities;
        if ($selectedFacility) {
            $filteredFacilities = $filteredFacilities->where('id', $selectedFacility);
        }
        $efficiencyRows = [];
        foreach ($filteredFacilities as $facility) {
            // Compute EUI and rating from EnergyRecord
            $records = $facility->energyRecords()->whereNotNull('actual_kwh')->where('actual_kwh', '>', 0)->get();
            $totalKwh = $records->sum('actual_kwh');
            $months = $records->count();
            $floorArea = $facility->floor_area ?? 0;
            $eui = ($months > 0 && $floorArea > 0) ? round(($totalKwh / $months) / $floorArea, 2) : '-';
            // Corrected: Low EUI = High efficiency, High EUI = Low efficiency
            if ($eui !== '-') {
                if ($eui < 5) $rating = 'High';
                elseif ($eui < 10) $rating = 'Medium';
                else $rating = 'Low';
            } else {
                $rating = '-';
            }
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
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.reports.efficiency-summary', compact('efficiencyRows', 'facilities', 'selectedFacility', 'selectedRating', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }
}
