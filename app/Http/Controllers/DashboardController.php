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
        $normalizeAlertLabel = function ($label) {
            return match ($label) {
                'Extreme / level 5' => 'Critical',
                'Extreme / level 4' => 'Very High',
                'High / level 3' => 'High',
                'Warning / level 2' => 'Warning',
                'Normal / Low' => 'Normal',
                default => $label ?: 'Unknown',
            };
        };
        $computeConsumptionStatus = function (float $deviation, float $baselineKwh): string {
            if ($baselineKwh <= 1000) {
                $size = 'Small';
            } elseif ($baselineKwh <= 3000) {
                $size = 'Medium';
            } elseif ($baselineKwh <= 10000) {
                $size = 'Large';
            } else {
                $size = 'Extra Large';
            }

            $thresholds = [
                'Small' => ['level5' => 80, 'level4' => 50, 'level3' => 30, 'level2' => 15],
                'Medium' => ['level5' => 60, 'level4' => 40, 'level3' => 20, 'level2' => 10],
                'Large' => ['level5' => 30, 'level4' => 20, 'level3' => 12, 'level2' => 5],
                'Extra Large' => ['level5' => 20, 'level4' => 12, 'level3' => 7, 'level2' => 3],
            ];
            $t = $thresholds[$size];

            if ($deviation > $t['level5']) {
                return 'Critical';
            }
            if ($deviation > $t['level4']) {
                return 'Very High';
            }
            if ($deviation > $t['level3']) {
                return 'High';
            }
            if ($deviation > $t['level2']) {
                return 'Warning';
            }
            return 'Normal';
        };

        // Alerts for dashboard/notifications (structured with severity for sorting and UI)
        $alerts = [];
        $severityRank = [
            'Critical' => 5,
            'Very High' => 4,
            'High' => 3,
            'Warning' => 2,
            'Normal' => 1,
        ];
        $normalizeSeverity = function (?string $label) {
            $label = trim((string) $label);
            return match ($label) {
                'Critical', 'Very High', 'High', 'Warning', 'Normal' => $label,
                'Medium' => 'Warning',
                'Low' => 'Normal',
                default => 'Warning',
            };
        };
        $pushAlert = function (string $message, string $level = 'Warning', string $type = 'system', $detectedAt = null) use (&$alerts, $severityRank, $normalizeSeverity) {
            $normalizedLevel = $normalizeSeverity($level);
            $timestamp = now()->timestamp;
            if ($detectedAt instanceof \DateTimeInterface) {
                $timestamp = $detectedAt->getTimestamp();
            } elseif (!empty($detectedAt)) {
                $timestamp = strtotime((string) $detectedAt) ?: $timestamp;
            }

            $alerts[] = [
                'message' => $message,
                'level' => $normalizedLevel,
                'type' => $type,
                'priority' => $severityRank[$normalizedLevel] ?? 1,
                'timestamp' => $timestamp,
            ];
        };

        // Check user role and facility assignment
        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');
        $facilityIds = ($userRole === 'staff') ? $user->facilities->pluck('id')->toArray() : null;

        // 4. Add alert for any unresolved energy incidents
        $unresolvedIncidents = \App\Models\EnergyIncident::with([
                'facility:id,name,baseline_kwh',
                'energyRecord:id,facility_id,baseline_kwh',
            ])
            ->where(function ($q) {
                $q->whereNull('resolved_at')
                    ->orWhere('status', '!=', 'Resolved');
            })
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('month', now()->month)
                        ->where('year', now()->year);
                })->orWhere(function ($sub) {
                    $sub->whereNull('month')
                        ->whereNull('year')
                        ->whereMonth('date_detected', now()->month)
                        ->whereYear('date_detected', now()->year);
                });
            })
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('facility_id', $facilityIds);
            })
            ->get();
        foreach ($unresolvedIncidents as $incident) {
            $facilityName = $incident->facility->name ?? 'Unknown Facility';
            $desc = $incident->description ?? 'No description';
            $status = $incident->status ?? 'Unresolved';
            $incidentLevel = 'Warning';
            $incidentDeviation = $incident->deviation_percent !== null ? (float) $incident->deviation_percent : null;
            $incidentBaseline = $incident->energyRecord?->baseline_kwh
                ?? $incident->facility?->baseline_kwh
                ?? null;
            if ($incidentDeviation !== null && $incidentBaseline !== null && $incidentBaseline > 0) {
                $incidentLevel = $computeConsumptionStatus($incidentDeviation, (float) $incidentBaseline);
            } elseif ($incidentDeviation !== null) {
                if ($incidentDeviation > 60) {
                    $incidentLevel = 'Critical';
                } elseif ($incidentDeviation > 40) {
                    $incidentLevel = 'Very High';
                } elseif ($incidentDeviation > 20) {
                    $incidentLevel = 'High';
                }
            }
            $pushAlert(
                "Incident: {$facilityName} - {$desc} [Status: {$status}]",
                $incidentLevel,
                'incident',
                $incident->date_detected ?? $incident->created_at
            );
        }

        // 1. Add alert for any facility with status 'High' in High Consumption Hubs
        $sixMonthsAgo = now()->subMonths(6);
        $criticalFacilities = Facility::with(['energyRecords' => function($q) use ($sixMonthsAgo) {
            $q->whereDate('created_at', '>=', $sixMonthsAgo);
        }])
        ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('id', $facilityIds); })
        ->get()
        ->map(function($facility) use ($computeConsumptionStatus) {
            $records = $facility->energyRecords;
            $totalKwh = $records->sum('actual_kwh');
            $totalBaseline = $records->sum('baseline_kwh');
            $deviation = ($totalBaseline > 0 && $totalKwh > 0) ? (($totalKwh - $totalBaseline) / $totalBaseline) * 100 : 0;
            $status = 'Normal';
            if ($totalBaseline > 0) {
                $baselineForSize = $records->count() > 0 ? ($totalBaseline / $records->count()) : $totalBaseline;
                $status = $computeConsumptionStatus($deviation, $baselineForSize);
            }
            return [
                'name' => $facility->name,
                'status' => $status,
                'deviation' => round($deviation, 2),
            ];
        })
        ->filter(function($f) { return in_array($f['status'], ['Critical', 'Very High', 'High'], true); });
        foreach ($criticalFacilities as $f) {
            $pushAlert(
                "{$f['status']}: {$f['name']} is above baseline by {$f['deviation']}%.",
                $f['status'],
                'consumption'
            );
        }

        // 2. Add alert for any active EnergyRecord alerts (Warning and above)
        $activeAlertLevels = ['Critical', 'Very High', 'High', 'Warning', 'Extreme / level 5', 'Extreme / level 4', 'High / level 3', 'Warning / level 2', 'Medium'];
        $activeAlertRecords = \App\Models\EnergyRecord::whereIn('alert', $activeAlertLevels)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
            ->get();
        foreach ($activeAlertRecords as $rec) {
            $facilityName = $rec->facility->name ?? 'Unknown Facility';
            $alertLabel = $normalizeAlertLabel($rec->alert);
            $pushAlert(
                "Alert: {$facilityName} has a {$alertLabel} alert this month.",
                $alertLabel,
                'record',
                $rec->created_at
            );
        }

        // 3. Add alert for any ongoing maintenance
        $ongoingMaint = \App\Models\Maintenance::where('maintenance_status', 'Ongoing')
            ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
            ->get();
        foreach ($ongoingMaint as $m) {
            $facilityName = $m->facility->name ?? 'Unknown Facility';
            $pushAlert(
                "Maintenance: {$facilityName} has ongoing maintenance.",
                'Warning',
                'maintenance',
                $m->created_at
            );
        }
        $alerts = collect($alerts)
            ->sort(function ($a, $b) {
                return ($b['priority'] <=> $a['priority']) ?: ($b['timestamp'] <=> $a['timestamp']);
            })
            ->values();
        $criticalAlerts = $alerts
            ->filter(function ($alert) {
                return in_array($alert['level'] ?? null, ['Critical', 'Very High', 'High'], true);
            })
            ->values();

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
        $activeAlertsQuery = EnergyRecord::whereIn('alert', $activeAlertLevels)
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
        $unresolvedIncidentCount = $unresolvedIncidents->count();
        $facilityStatusCounts = Facility::query()
            ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('id', $facilityIds); })
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_count")
            ->selectRaw("SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count")
            ->first();


        // 2. Charts
        $energyChartLabels = [];
        $energyChartData = [];
        $baselineChartData = [];
        $costChartLabels = [];
        $costChartData = [];
        $fallbackFacilityBaseline = $facilityIds
            ? Facility::whereIn('id', $facilityIds)->sum('baseline_kwh')
            : Facility::sum('baseline_kwh');
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
            $monthlyBaseline = EnergyRecord::where('year', $m['year'])->where('month', $m['month'])
                ->when($facilityIds, function($q) use ($facilityIds) { return $q->whereIn('facility_id', $facilityIds); })
                ->sum('baseline_kwh');
            $baselineChartData[] = $monthlyBaseline > 0 ? $monthlyBaseline : ($fallbackFacilityBaseline ?: 0);
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
        ->map(function($facility) use ($computeConsumptionStatus) {
            $records = $facility->energyRecords;
            $totalKwh = $records->sum('actual_kwh');
            // Sum baseline_kwh from each monthly record (last 6 months)
            $totalBaseline = $records->sum('baseline_kwh');
            $deviation = ($totalBaseline > 0 && $totalKwh > 0) ? (($totalKwh - $totalBaseline) / $totalBaseline) * 100 : 0;
            $status = 'Normal';
            if ($totalBaseline > 0) {
                $baselineForSize = $records->count() > 0 ? ($totalBaseline / $records->count()) : $totalBaseline;
                $status = $computeConsumptionStatus($deviation, $baselineForSize);
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
        foreach ($alerts as $alertItem) {
            $alertMsg = $alertItem['message'] ?? null;
            if (!$alertMsg) {
                continue;
            }
            $alertType = (string) ($alertItem['type'] ?? 'alert');
            $alertTitle = match ($alertType) {
                'incident' => 'Incident Alert',
                'maintenance' => 'Maintenance Alert',
                'consumption' => 'Consumption Alert',
                'record' => 'Energy Alert',
                default => 'System Alert',
            };
            // Avoid duplicates for the same month even if previously marked as read.
            // This keeps "Mark all read" stable and prevents the badge from reappearing on reload.
            $existingNotification = $user->notifications()
                ->where('message', $alertMsg)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->first();
            if ($existingNotification) {
                $currentTitle = strtolower(trim((string) ($existingNotification->title ?? '')));
                $currentType = strtolower(trim((string) ($existingNotification->type ?? '')));
                if ($currentTitle === '' || $currentTitle === 'system alert' || $currentType === '' || $currentType === 'alert') {
                    $existingNotification->update([
                        'title' => $alertTitle,
                        'type' => $alertType,
                    ]);
                }
            } else {
                $user->notifications()->create([
                    'title' => $alertTitle,
                    'message' => $alertMsg,
                    'type' => $alertType,
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
            'unresolvedIncidentCount' => $unresolvedIncidentCount,
            'facilityStatusCounts' => $facilityStatusCounts,
            'complianceStatus' => $complianceStatus,
            'energyChartLabels' => $energyChartLabels,
            'energyChartData' => $energyChartData,
            'baselineChartData' => $baselineChartData,
            'costChartLabels' => $costChartLabels,
            'costChartData' => $costChartData,
            'recentLogs' => $recentLogs,
            'alerts' => $alerts,
            'criticalAlerts' => $criticalAlerts,
            'topFacilities' => $topFacilities,
            'kwhTrend' => $kwhTrend,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadCount,
            'role' => $role,
            'user' => $user,
        ]);
    }
}
