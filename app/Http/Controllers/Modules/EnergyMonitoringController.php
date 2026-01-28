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
        // Get the total number of facilities dynamically
        $totalFacilities = Facility::count();
        // Get all facilities for the table
        $facilities = Facility::all();
        // Count facilities with a 'High' alert in the current month
        $currentMonth = date('n');
        $currentYear = date('Y');
        $highAlertCount = 0;
        foreach ($facilities as $facility) {
            $record = $facility->energyRecords()
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('alert', 'High')
                ->first();
            if ($record) {
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
            if ($facility->energyRecords->count() > 0) {
                $facility->energyRecords->first()->last_maintenance = $lastMaint ? $lastMaint->completed_date : null;
                $facility->energyRecords->first()->next_maintenance = $nextMaint ? $nextMaint->scheduled_date : null;
            }
        }
        return view('modules.energy-monitoring.index', compact('totalFacilities', 'facilities', 'highAlertCount'));
    }
}
