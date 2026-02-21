<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use Carbon\Carbon;

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

        $facilityIds = $filteredFacilities->pluck('id')->values();
        $recordsByFacility = EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereNotNull('actual_kwh')
            ->where('actual_kwh', '>', 0)
            ->get(['facility_id', 'year', 'month', 'actual_kwh'])
            ->groupBy('facility_id');

        $hasOpenMaintenance = Maintenance::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereIn('maintenance_status', ['Pending', 'Ongoing'])
            ->select('facility_id')
            ->distinct()
            ->pluck('facility_id')
            ->flip();

        $lastAuditByFacility = MaintenanceHistory::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereNotNull('completed_date')
            ->orderByDesc('completed_date')
            ->get(['facility_id', 'completed_date'])
            ->groupBy('facility_id')
            ->map(fn ($rows) => $rows->first());

        $efficiencyRows = [];
        foreach ($filteredFacilities as $facility) {
            $records = $recordsByFacility->get($facility->id, collect());
            $monthlyTotals = $records
                ->groupBy(function ($record) {
                    return sprintf('%04d-%02d', (int) $record->year, (int) $record->month);
                })
                ->map(function ($rows) {
                    return (float) $rows->sum('actual_kwh');
                });

            $avgMonthlyKwh = $monthlyTotals->count() > 0 ? (float) $monthlyTotals->avg() : null;
            $floorArea = (float) ($facility->floor_area ?? 0);
            $euiValue = ($avgMonthlyKwh !== null && $floorArea > 0)
                ? round($avgMonthlyKwh / $floorArea, 2)
                : null;
            $eui = $euiValue !== null ? $euiValue : '-';

            // Lower EUI means better efficiency.
            if ($euiValue !== null) {
                if ($euiValue < 5) {
                    $rating = 'High';
                } elseif ($euiValue < 10) {
                    $rating = 'Medium';
                } else {
                    $rating = 'Low';
                }
            } else {
                $rating = '-';
            }

            if ($selectedRating && $selectedRating !== 'all' && $selectedRating !== $rating) {
                continue;
            }

            $lastAudit = $lastAuditByFacility->get($facility->id);
            $lastAuditDate = $lastAudit
                ? Carbon::parse($lastAudit->completed_date)->format('M d, Y')
                : '-';

            $flag = ($rating === 'Low') || $hasOpenMaintenance->has($facility->id);

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
