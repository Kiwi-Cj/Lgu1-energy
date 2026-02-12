<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;

class EnergyMonitoringController extends Controller
{
    /**
     * Display the Energy Monitoring Dashboard with dynamic total facilities card and facility table.
     */
    public function index()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        if ($role === 'staff') {
            $facilities = $user->facilities;
            $totalFacilities = $facilities->count();
        } else {
            $facilities = Facility::all();
            $totalFacilities = $facilities->count();
        }
        $currentMonth = date('n');
        $currentYear = date('Y');
        $highAlertCount = 0;
        $facilityIds = ($role === 'staff') ? $facilities->pluck('id')->toArray() : null;
        $totalEnergyCost = \App\Models\EnergyRecord::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
            ->sum('energy_cost');

        // Total actual kWh consumption for current month
        $totalActualKwh = \App\Models\EnergyRecord::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
            ->sum('actual_kwh');

        // Total baseline kWh for current month (sum of each facility's baseline_kwh)
        $totalAverageKwh = $facilityIds ? \App\Models\Facility::whereIn('id', $facilityIds)->sum('baseline_kwh') : \App\Models\Facility::sum('baseline_kwh');
        foreach ($facilities as $facility) {
            // Attach current month's record
            $facility->currentMonthRecord = $facility->energyRecords()
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();
            // Count high alerts (if you have an alert field/logic)
            if ($facility->currentMonthRecord && ($facility->currentMonthRecord->alert ?? null) === 'High') {
                $highAlertCount++;
            }
            // Attach last maintenance from MaintenanceHistory
            $lastMaint = \App\Models\MaintenanceHistory::where('facility_id', $facility->id)
                ->whereNotNull('completed_date')
                ->orderByDesc('completed_date')
                ->first();
            // Attach next maintenance from Maintenance (Ongoing)
            $nextMaint = \App\Models\Maintenance::where('facility_id', $facility->id)
                ->where('maintenance_status', 'Ongoing')
                ->orderBy('scheduled_date', 'asc')
                ->first();
            if ($facility->currentMonthRecord) {
                $facility->currentMonthRecord->last_maintenance = $lastMaint ? $lastMaint->completed_date : null;
                $facility->currentMonthRecord->next_maintenance = $nextMaint ? $nextMaint->scheduled_date : null;
            }
        }
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy-monitoring.index', compact('totalFacilities', 'facilities', 'highAlertCount', 'totalEnergyCost', 'notifications', 'unreadNotifCount') + ['role' => $role, 'user' => $user]);
    }
}
