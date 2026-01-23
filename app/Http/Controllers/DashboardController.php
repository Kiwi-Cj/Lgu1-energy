<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;
use App\Models\Bill;
use App\Models\Maintenance;
use App\Models\EnergyEfficiency;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Check user role and facility assignment
        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');
        $userFacilityId = ($userRole === 'staff') ? $user->facility_id : null;

        // 1. Summary Cards
        $totalFacilities = Facility::count();
        $totalKwh = EnergyRecord::whereMonth('created_at', now()->month)->sum('kwh_consumed');
        $totalCost = Bill::whereMonth('created_at', now()->month)->sum('total_bill');
        $activeAlerts = EnergyRecord::where('alert_flag', 1)->count();
        $ongoingMaintenance = Maintenance::where('maintenance_status', 'Ongoing')->count();
        $complianceStatus = EnergyEfficiency::where('rating', 'Low')->count() > 0 ? 'Pending' : 'Compliant';

        // 2. Charts
        $energyChartLabels = [];
        $energyChartData = [];
        $costChartLabels = [];
        $costChartData = [];
        for ($i = 1; $i <= 6; $i++) {
            $month = now()->subMonths(6 - $i);
            $label = $month->format('M');
            $energyChartLabels[] = $label;
            $energyChartData[] = EnergyRecord::whereMonth('created_at', $month->month)->sum('kwh_consumed');
            $costChartLabels[] = $label;
            $costChartData[] = Bill::whereMonth('created_at', $month->month)->sum('total_bill');
        }

        // 3. Recent Activity (last 8 actions) - Filter by facility for Staff
        $recentLogs = [];
        
        // Facility logs (Staff only see their facility, Admin/Energy Officer see all)
        $facilityQuery = Facility::orderByDesc('created_at');
        if ($userFacilityId) {
            $facilityQuery->where('id', $userFacilityId);
        }
        $facilityLogs = $facilityQuery->take(2)->get()->map(function($f) {
            return 'Added new facility – ' . ($f->name ?? 'Unknown');
        });
        
        // Maintenance logs (Staff only see their facility, Admin/Energy Officer see all)
        $maintenanceQuery = Maintenance::orderByDesc('created_at');
        if ($userFacilityId) {
            $maintenanceQuery->where('facility_id', $userFacilityId);
        }
        $maintenanceLogs = $maintenanceQuery->take(2)->get()->map(function($m) {
            return 'Maintenance scheduled – ' . ($m->facility->name ?? 'Facility') . ' (' . ($m->maintenance_type ?? 'Type') . ')';
        });
        
        // Energy logs (Staff only see their facility, Admin/Energy Officer see all)
        $energyQuery = EnergyRecord::orderByDesc('created_at');
        if ($userFacilityId) {
            $energyQuery->where('facility_id', $userFacilityId);
        }
        $energyLogs = $energyQuery->take(2)->get()->map(function($e) {
            return 'Energy record added – ' . ($e->facility->name ?? 'Facility') . ' (' . ($e->month ?? '-') . '/' . ($e->year ?? '-') . ')';
        });
        
        // Bill logs (Staff only see their facility, Admin/Energy Officer see all)
        $billQuery = Bill::orderByDesc('created_at');
        if ($userFacilityId) {
            $billQuery->where('facility_id', $userFacilityId);
        }
        $billLogs = $billQuery->take(2)->get()->map(function($b) {
            return 'Bill generated – ' . ($b->facility->name ?? 'Facility') . ' (' . ($b->month ?? '-') . ')';
        });
        
        $recentLogs = $facilityLogs->merge($maintenanceLogs)->merge($energyLogs)->merge($billLogs)->take(8)->toArray();

        // 4. Alerts & Notifications (dynamic) - Filter by facility for Staff
        $alerts = [];
        
        // High energy usage alerts (Staff only see their facility, Admin/Energy Officer see all)
        $highUsageQuery = EnergyRecord::where('alert_flag', 1)->orderByDesc('created_at');
        if ($userFacilityId) {
            $highUsageQuery->where('facility_id', $userFacilityId);
        }
        $highUsage = $highUsageQuery->take(3)->get();
        foreach ($highUsage as $record) {
            $alerts[] = '⚠️ High energy usage detected – ' . ($record->facility->name ?? 'Facility') . ' (' . ($record->month ?? '-') . '/' . ($record->year ?? '-') . ')';
        }
        
        // Pending maintenance alerts (Staff only see their facility, Admin/Energy Officer see all)
        $pendingMaintenanceQuery = Maintenance::where('maintenance_status', 'Pending')->orderByDesc('created_at');
        if ($userFacilityId) {
            $pendingMaintenanceQuery->where('facility_id', $userFacilityId);
        }
        $pendingMaintenance = $pendingMaintenanceQuery->take(3)->get();
        foreach ($pendingMaintenance as $m) {
            $alerts[] = '🔴 Pending maintenance – ' . ($m->facility->name ?? 'Facility');
        }

        return view('modules.dashboard.index', [
            'totalFacilities' => $totalFacilities,
            'totalKwh' => $totalKwh,
            'totalCost' => $totalCost,
            'activeAlerts' => $activeAlerts,
            'ongoingMaintenance' => $ongoingMaintenance,
            'complianceStatus' => $complianceStatus,
            'energyChartLabels' => $energyChartLabels,
            'energyChartData' => $energyChartData,
            'costChartLabels' => $costChartLabels,
            'costChartData' => $costChartData,
            'recentLogs' => $recentLogs,
            'alerts' => $alerts,
        ]);
    }
}
