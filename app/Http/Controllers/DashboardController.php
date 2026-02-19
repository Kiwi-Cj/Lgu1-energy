<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;
// use App\Models\Bill; // removed
use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Alerts array for notifications and dashboard
        $alerts = [];

        // 4. Add alert for any unresolved energy incidents
        $unresolvedIncidents = \App\Models\EnergyIncident::whereNull('resolved_at')
            ->whereMonth('date_detected', now()->month)
            ->whereYear('date_detected', now()->year)
            ->orWhere(function($q) {
                $q->where('status', '!=', 'Resolved')
                  ->whereMonth('date_detected', now()->month)
                  ->whereYear('date_detected', now()->year);
            })
            ->get();
        foreach ($unresolvedIncidents as $incident) {
            $facilityName = $incident->facility->name ?? 'Unknown Facility';
            $desc = $incident->description ?? 'No description';
            $status = $incident->status ?? 'Unresolved';
            $alerts[] = "Incident: {$facilityName} - {$desc} [Status: {$status}]";
        }

        // 1. Add alert for any facility with status 'High' in High Consumption Hubs
        $sixMonthsAgo = now()->subMonths(6);
        $criticalFacilities = Facility::with(['energyRecords' => function($q) use ($sixMonthsAgo) {
            $q->whereDate('created_at', '>=', $sixMonthsAgo);
        }])
        ->get()
        ->map(function($facility) {
            $records = $facility->energyRecords;
            $totalKwh = $records->sum('actual_kwh');
            $totalBaseline = $records->sum('baseline_kwh');
            $deviation = ($totalBaseline > 0 && $totalKwh > 0) ? (($totalKwh - $totalBaseline) / $totalBaseline) * 100 : 0;
            $status = 'Normal';
            if ($totalBaseline > 0) {
                if ($deviation >= 20) {
                    $status = 'High';
                } elseif ($deviation >= 10) {
                    $status = 'Medium';
                }
            }
            return [
                'name' => $facility->name,
                'status' => $status,
                'deviation' => round($deviation, 2),
            ];
        })
        ->filter(function($f) { return $f['status'] === 'High'; });
        foreach ($criticalFacilities as $f) {
            $alerts[] = "Critical: {$f['name']} is above baseline by {$f['deviation']}%.";
        }

        // 2. Add alert for any active EnergyRecord alerts (Medium/High)
        $activeAlertRecords = \App\Models\EnergyRecord::whereIn('alert', ['Medium', 'High'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();
        foreach ($activeAlertRecords as $rec) {
            $facilityName = $rec->facility->name ?? 'Unknown Facility';
            $alerts[] = "Alert: {$facilityName} has a {$rec->alert} alert this month.";
        }

        // 3. Add alert for any ongoing maintenance
        $ongoingMaint = \App\Models\Maintenance::where('maintenance_status', 'Ongoing')->get();
        foreach ($ongoingMaint as $m) {
            $facilityName = $m->facility->name ?? 'Unknown Facility';
            $alerts[] = "Maintenance: {$facilityName} has ongoing maintenance.";
        }
        // Check user role and facility assignment
        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');
        $facilityIds = ($userRole === 'staff') ? $user->facilities->pluck('id')->toArray() : null;

        // 1. Summary Cards
        if ($userRole === 'staff') {
            $totalFacilities = $user->facilities->count();
        } else {
            $totalFacilities = Facility::count();
        }
        $monthsRange = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthsRange[] = [
                'year' => $date->year,
                'month' => $date->month
            ];
        }
        $totalKwhQuery = EnergyRecord::query();
        $totalCostQuery = EnergyRecord::query();
        $totalKwhQuery->where(function($q) use ($monthsRange) {
            foreach ($monthsRange as $m) {
                $q->orWhere(function($sub) use ($m) {
                    $sub->where('year', $m['year'])->where('month', $m['month']);
                });
            }
        });
        $totalCostQuery->where(function($q) use ($monthsRange) {
            foreach ($monthsRange as $m) {
                $q->orWhere(function($sub) use ($m) {
                    $sub->where('year', $m['year'])->where('month', $m['month']);
                });
            }
        });
        $activeAlertsQuery = EnergyRecord::whereIn('alert', ['Medium', 'High'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        $ongoingMaintenanceQuery = Maintenance::where('maintenance_status', 'Ongoing');
        if ($facilityIds) {
            $totalKwhQuery->whereIn('facility_id', $facilityIds);
            $totalCostQuery->whereIn('facility_id', $facilityIds);
            $activeAlertsQuery->whereIn('facility_id', $facilityIds);
            $ongoingMaintenanceQuery->whereIn('facility_id', $facilityIds);
        }
        $totalKwh = $totalKwhQuery->sum('actual_kwh');
        $totalCost = $totalCostQuery->sum('energy_cost');
        $activeAlerts = $activeAlertsQuery->count();
        $ongoingMaintenance = $ongoingMaintenanceQuery->count();
        $complianceStatus = 'N/A';


        // 2. Charts
        $energyChartLabels = [];
        $energyChartData = [];
        $baselineChartData = [];
        $costChartLabels = [];
        $costChartData = [];
        $months = [];
        for ($i = 1; $i <= 6; $i++) {
            $monthObj = now()->subMonths(6 - $i);
            $months[] = [
                'label' => $monthObj->format('M'),
                'year' => $monthObj->year,
                'month' => $monthObj->month
            ];
        }
        foreach ($months as $m) {
            $energyChartLabels[] = $m['label'];
            $actualKwh = EnergyRecord::where('year', $m['year'])->where('month', $m['month'])
                ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
                ->sum('actual_kwh');
            $energyChartData[] = $actualKwh ?: 0;
            $baselineChartData[] = $facilityIds ? Facility::whereIn('id', $facilityIds)->sum('baseline_kwh') : Facility::all()->sum('baseline_kwh');
            $costChartLabels[] = $m['label'];
            $cost = EnergyRecord::where('year', $m['year'])->where('month', $m['month'])
                ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
                ->sum('energy_cost');
            $costChartData[] = $cost ?: 0;
        }

        // 2b. High Consumption Hubs (last 6 months, average vs. baseline)
        $sixMonthsAgo = now()->subMonths(6);
        $topFacilities = Facility::with(['energyRecords' => function($q) use ($sixMonthsAgo) {
            $q->whereDate('created_at', '>=', $sixMonthsAgo);
        }])
        ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('id', $facilityIds); })
        ->get()
        ->map(function($facility) {
            $records = $facility->energyRecords;
            $totalKwh = $records->sum('actual_kwh');
            // Sum baseline_kwh from each monthly record (last 6 months)
            $totalBaseline = $records->sum('baseline_kwh');
            $deviation = ($totalBaseline > 0 && $totalKwh > 0) ? (($totalKwh - $totalBaseline) / $totalBaseline) * 100 : 0;
            $status = 'Normal';
            if ($totalBaseline > 0) {
                if ($deviation >= 20) {
                    $status = 'High';
                } elseif ($deviation >= 10) {
                    $status = 'Medium';
                }
            }
            return (object) [
                'name' => $facility->name,
                'total_kwh' => round($totalKwh, 2),
                'baseline_kwh' => round($totalBaseline, 2),
                'deviation' => round($deviation, 2),
                'status' => $status,
            ];
        })
        // Show all facilities with data, no filter
        ->sortByDesc('deviation')
        ->values();

        // 3. Recent Activity (last 8 actions) - Filter by facility for Staff
        $recentLogs = [];
        
        // Facility logs (Staff only see their facility, Admin/Energy Officer see all)
        $facilityQuery = Facility::orderByDesc('created_at');
        if ($facilityIds) {
            $facilityQuery->whereIn('id', $facilityIds);
        }
        $facilityLogs = $facilityQuery->take(2)->get()->map(function($f) {
            return 'Added new facility – ' . ($f->name ?? 'Unknown');
        });
        $recentLogs = $facilityLogs->toArray();

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
        $currentKwh = $currentMonths->sum(function($m) use ($facilityIds) {
            return EnergyRecord::where('year', $m['year'])->where('month', $m['month'])
                ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
                ->sum('actual_kwh');
        });
        $previousKwh = $previousMonths->sum(function($m) use ($facilityIds) {
            return EnergyRecord::where('year', $m['year'])->where('month', $m['month'])
                ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
                ->sum('actual_kwh');
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
        $role = $userRole;
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
            'role' => $role,
            'user' => $user,
        ]);
    }
}
