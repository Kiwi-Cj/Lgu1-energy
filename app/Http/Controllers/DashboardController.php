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
        if ($userRole === 'staff') {
            $totalFacilities = $user->facility_id ? 1 : 0;
        } else {
            $totalFacilities = Facility::count();
        }
        $currentYear = now()->year;
        $totalKwh = EnergyRecord::where('year', $currentYear)->sum('actual_kwh');
        $totalCost = EnergyRecord::where('year', $currentYear)->sum('energy_cost');
        // Count records with alert set to 'Medium' or 'High' for active alerts
        $activeAlerts = EnergyRecord::whereIn('alert', ['Medium', 'High'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $ongoingMaintenance = Maintenance::where('maintenance_status', 'Ongoing')->count();
        $complianceStatus = EnergyEfficiency::where('rating', 'Low')->count() > 0 ? 'Pending' : 'Compliant';


        // 2. Charts
        $energyChartLabels = [];
        $energyChartData = [];
        $baselineChartData = [];
        $costChartLabels = [];
        $costChartData = [];
        for ($i = 1; $i <= 6; $i++) {
            $month = now()->subMonths(6 - $i);
            $label = $month->format('M');
            $energyChartLabels[] = $label;
            $energyChartData[] = EnergyRecord::where('year', $currentYear)->where('month', $month->month)->sum('actual_kwh');
            // Dynamic baseline: sum of all facilities' baseline_kwh
            $baselineChartData[] = Facility::all()->sum('baseline_kwh');
            $costChartLabels[] = $label;
            $costChartData[] = EnergyRecord::where('year', $currentYear)->where('month', $month->month)->sum('energy_cost');
        }

        // 2b. Top Energy-Consuming Facilities (current month)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $topFacilities = EnergyRecord::with('facility')
            ->selectRaw('facility_id, SUM(actual_kwh) as monthly_kwh')
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->groupBy('facility_id')
            ->orderByDesc('monthly_kwh')
            ->take(5)
            ->get()
            ->map(function($rec) {
                $facility = $rec->facility;
                $avgKwh = $facility ? ($facility->baseline_kwh ?? 0) : 0;
                $status = '-';
                if ($avgKwh > 0) {
                    if ($rec->monthly_kwh > $avgKwh * 1.2) {
                        $status = 'High';
                    } elseif ($rec->monthly_kwh > $avgKwh) {
                        $status = 'Medium';
                    } else {
                        $status = 'Normal';
                    }
                }
                return (object) [
                    'name' => $facility ? $facility->name : 'Unknown',
                    'monthly_kwh' => $rec->monthly_kwh,
                    'status' => $status,
                ];
            });

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
        // High usage = alert is 'High' or 'Medium'
        $highUsageQuery = EnergyRecord::whereIn('alert', ['Medium', 'High'])->orderByDesc('created_at');
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

        // --- Dynamic kWh Trend Calculation (6 months) ---
        $monthsToCompare = 6;
        $currentMonths = collect();
        $previousMonths = collect();
        for ($i = 1; $i <= $monthsToCompare; $i++) {
            $currentMonths->push([
                'year' => now()->subMonths($monthsToCompare - $i)->year,
                'month' => now()->subMonths($monthsToCompare - $i)->month
            ]);
            $previousMonths->push([
                'year' => now()->subMonths($monthsToCompare * 2 - $i)->year,
                'month' => now()->subMonths($monthsToCompare * 2 - $i)->month
            ]);
        }
        $currentKwh = $currentMonths->sum(function($m) {
            return EnergyRecord::where('year', $m['year'])->where('month', $m['month'])->sum('actual_kwh');
        });
        $previousKwh = $previousMonths->sum(function($m) {
            return EnergyRecord::where('year', $m['year'])->where('month', $m['month'])->sum('actual_kwh');
        });
        if ($previousKwh > 0) {
            $kwhTrend = (($currentKwh - $previousKwh) / $previousKwh) * 100;
            $kwhTrend = ($kwhTrend >= 0 ? '+' : '') . number_format($kwhTrend, 1) . '%';
        } else {
            $kwhTrend = '';
        }

        // Insert alerts as notifications (if not already present)
        foreach ($alerts as $alertMsg) {
            // Check if this alert already exists for the user (avoid duplicates)
            $exists = $user->notifications()->where('message', $alertMsg)->whereNull('read_at')->exists();
            if (!$exists) {
                $user->notifications()->create([
                    'title' => 'System Alert',
                    'message' => $alertMsg,
                    'type' => 'alert',
                ]);
            }
        }
        $notifications = $user->notifications()->orderByDesc('created_at')->take(10)->get();
        $unreadCount = $user->notifications()->whereNull('read_at')->count();
        return view('modules.dashboard.index', [
            'totalFacilities' => $totalFacilities,
            'totalKwh' => $totalKwh,
            'totalCost' => $totalCost,
            'activeAlerts' => $activeAlerts,
            'ongoingMaintenance' => $ongoingMaintenance,
            'complianceStatus' => $complianceStatus,
            'energyChartLabels' => $energyChartLabels,
            'energyChartData' => $energyChartData,
            'baselineChartData' => $baselineChartData,
            'costChartLabels' => $costChartLabels,
            'costChartData' => $costChartData,
            'recentLogs' => $recentLogs,
            'alerts' => $alerts,
            'topFacilities' => $topFacilities,
            'kwhTrend' => $kwhTrend,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadCount,
        ]);
    }
}
