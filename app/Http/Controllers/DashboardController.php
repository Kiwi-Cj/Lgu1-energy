<?php

namespace App\Http\Controllers;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\Maintenance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
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

        // Date range (month-based): defaults to latest 6 months.
        $defaultStartMonth = now()->subMonths(5)->startOfMonth();
        $defaultEndMonth = now()->startOfMonth();
        $parseMonthInput = function (?string $value, Carbon $fallback): Carbon {
            $value = trim((string) $value);
            if (!preg_match('/^\d{4}-\d{2}$/', $value)) {
                return $fallback->copy();
            }

            try {
                return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            } catch (\Throwable $e) {
                return $fallback->copy();
            }
        };

        $selectedStartMonth = $parseMonthInput($request->query('start_month'), $defaultStartMonth);
        $selectedEndMonth = $parseMonthInput($request->query('end_month'), $defaultEndMonth);
        if ($selectedStartMonth->gt($selectedEndMonth)) {
            [$selectedStartMonth, $selectedEndMonth] = [$selectedEndMonth, $selectedStartMonth];
        }

        $periodStartDate = $selectedStartMonth->copy()->startOfMonth();
        $periodEndDate = $selectedEndMonth->copy()->endOfMonth();
        $periodMonthCount = (int) $selectedStartMonth->diffInMonths($selectedEndMonth) + 1;
        $periodStartYm = (int) $selectedStartMonth->format('Ym');
        $periodEndYm = (int) $selectedEndMonth->format('Ym');
        $periodStartInput = $selectedStartMonth->format('Y-m');
        $periodEndInput = $selectedEndMonth->format('Y-m');
        $periodStartLabel = $selectedStartMonth->year === $selectedEndMonth->year
            ? $selectedStartMonth->format('F')
            : $selectedStartMonth->format('F Y');
        $periodEndLabel = $selectedEndMonth->format('F Y');
        $isDefaultRange = $periodStartInput === $defaultStartMonth->format('Y-m')
            && $periodEndInput === $defaultEndMonth->format('Y-m');

        $applyEnergyRecordRange = function ($query) use ($periodStartYm, $periodEndYm) {
            return $query->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$periodStartYm, $periodEndYm]);
        };

        // 4. Add alert for unresolved energy incidents in selected period.
        $unresolvedIncidents = \App\Models\EnergyIncident::with([
            'facility:id,name,baseline_kwh',
            'energyRecord:id,facility_id,baseline_kwh',
        ])
            ->where(function ($q) {
                $q->whereNull('resolved_at')
                    ->orWhere('status', '!=', 'Resolved');
            })
            ->where(function ($q) use ($periodStartYm, $periodEndYm, $periodStartDate, $periodEndDate) {
                $q->where(function ($sub) use ($periodStartYm, $periodEndYm) {
                    $sub->whereNotNull('month')
                        ->whereNotNull('year')
                        ->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$periodStartYm, $periodEndYm]);
                })->orWhere(function ($sub) use ($periodStartDate, $periodEndDate) {
                    $sub->where(function ($fallbackMonthYear) {
                        $fallbackMonthYear->whereNull('month')
                            ->orWhereNull('year');
                    })
                        ->whereDate('date_detected', '>=', $periodStartDate->toDateString())
                        ->whereDate('date_detected', '<=', $periodEndDate->toDateString());
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

        // 1. Add alert for any facility with high consumption in selected period.
        $criticalFacilities = Facility::with(['energyRecords' => function ($q) use ($periodStartDate, $periodEndDate) {
            $q->whereDate('created_at', '>=', $periodStartDate->toDateString())
                ->whereDate('created_at', '<=', $periodEndDate->toDateString());
        }])
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('id', $facilityIds);
            })
            ->get()
            ->map(function ($facility) use ($computeConsumptionStatus) {
                $records = $facility->energyRecords;
                $totalKwh = $records->sum('actual_kwh');
                $totalBaseline = $records->sum('baseline_kwh');
                $deviation = ($totalBaseline > 0 && $totalKwh > 0)
                    ? (($totalKwh - $totalBaseline) / $totalBaseline) * 100
                    : 0;
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
            ->filter(function ($f) {
                return in_array($f['status'], ['Critical', 'Very High', 'High'], true);
            });
        foreach ($criticalFacilities as $f) {
            $pushAlert(
                "{$f['status']}: {$f['name']} is above baseline by {$f['deviation']}%.",
                $f['status'],
                'consumption'
            );
        }

        // 2. Add alert for active EnergyRecord alerts in selected period.
        $activeAlertLevels = ['Critical', 'Very High', 'High', 'Warning', 'Extreme / level 5', 'Extreme / level 4', 'High / level 3', 'Warning / level 2', 'Medium'];
        $activeAlertRecords = EnergyRecord::whereIn('alert', $activeAlertLevels)
            ->whereDate('created_at', '>=', $periodStartDate->toDateString())
            ->whereDate('created_at', '<=', $periodEndDate->toDateString())
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('facility_id', $facilityIds);
            })
            ->get();
        foreach ($activeAlertRecords as $rec) {
            $facilityName = $rec->facility->name ?? 'Unknown Facility';
            $alertLabel = $normalizeAlertLabel($rec->alert);
            $pushAlert(
                "Alert: {$facilityName} has a {$alertLabel} alert in the selected period.",
                $alertLabel,
                'record',
                $rec->created_at
            );
        }

        // 3. Add alert for ongoing maintenance in selected period.
        $ongoingMaint = Maintenance::where('maintenance_status', 'Ongoing')
            ->whereDate('created_at', '>=', $periodStartDate->toDateString())
            ->whereDate('created_at', '<=', $periodEndDate->toDateString())
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('facility_id', $facilityIds);
            })
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

        $totalKwhQuery = EnergyRecord::query();
        $totalCostQuery = EnergyRecord::query();
        $applyEnergyRecordRange($totalKwhQuery);
        $applyEnergyRecordRange($totalCostQuery);

        $activeAlertsQuery = EnergyRecord::whereIn('alert', $activeAlertLevels)
            ->whereDate('created_at', '>=', $periodStartDate->toDateString())
            ->whereDate('created_at', '<=', $periodEndDate->toDateString());
        $ongoingMaintenanceQuery = Maintenance::where('maintenance_status', 'Ongoing')
            ->whereDate('created_at', '>=', $periodStartDate->toDateString())
            ->whereDate('created_at', '<=', $periodEndDate->toDateString());

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
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('id', $facilityIds);
            })
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
        $monthCursor = $selectedStartMonth->copy();
        while ($monthCursor->lte($selectedEndMonth)) {
            $months[] = [
                'label' => $monthCursor->format('M Y'),
                'year' => $monthCursor->year,
                'month' => $monthCursor->month,
            ];
            $monthCursor->addMonth();
        }

        foreach ($months as $m) {
            $energyChartLabels[] = $m['label'];
            $actualKwh = EnergyRecord::where('year', $m['year'])
                ->where('month', $m['month'])
                ->when($facilityIds, function ($q) use ($facilityIds) {
                    return $q->whereIn('facility_id', $facilityIds);
                })
                ->sum('actual_kwh');
            $energyChartData[] = $actualKwh ?: 0;

            $monthlyBaseline = EnergyRecord::where('year', $m['year'])
                ->where('month', $m['month'])
                ->when($facilityIds, function ($q) use ($facilityIds) {
                    return $q->whereIn('facility_id', $facilityIds);
                })
                ->sum('baseline_kwh');
            $baselineChartData[] = $monthlyBaseline > 0 ? $monthlyBaseline : ($fallbackFacilityBaseline ?: 0);

            $costChartLabels[] = $m['label'];
            $cost = EnergyRecord::where('year', $m['year'])
                ->where('month', $m['month'])
                ->when($facilityIds, function ($q) use ($facilityIds) {
                    return $q->whereIn('facility_id', $facilityIds);
                })
                ->sum('energy_cost');
            $costChartData[] = $cost ?: 0;
        }

        // 2b. High Consumption Hubs (selected period, average vs. baseline)
        $topFacilities = Facility::with(['energyRecords' => function ($q) use ($periodStartDate, $periodEndDate) {
            $q->whereDate('created_at', '>=', $periodStartDate->toDateString())
                ->whereDate('created_at', '<=', $periodEndDate->toDateString());
        }])
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('id', $facilityIds);
            })
            ->get()
            ->map(function ($facility) use ($computeConsumptionStatus) {
                $records = $facility->energyRecords;
                $totalKwh = $records->sum('actual_kwh');
                $totalBaseline = $records->sum('baseline_kwh');
                $deviation = ($totalBaseline > 0 && $totalKwh > 0)
                    ? (($totalKwh - $totalBaseline) / $totalBaseline) * 100
                    : 0;
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
            ->sortByDesc('deviation')
            ->values();

        // 3. Recent Activity (last 8 actions) - Filter by facility for Staff
        $recentLogs = [];
        $facilityQuery = Facility::orderByDesc('created_at');
        if ($facilityIds) {
            $facilityQuery->whereIn('id', $facilityIds);
        }
        $facilityLogs = $facilityQuery->take(2)->get()->map(function ($f) {
            return 'Added new facility - ' . ($f->name ?? 'Unknown');
        });
        $recentLogs = $facilityLogs->toArray();

        // --- Dynamic kWh Trend Calculation (selected window vs previous window) ---
        $previousStartYm = (int) $selectedStartMonth->copy()->subMonths($periodMonthCount)->format('Ym');
        $previousEndYm = (int) $selectedStartMonth->copy()->subMonth()->format('Ym');

        $currentKwh = EnergyRecord::query()
            ->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$periodStartYm, $periodEndYm])
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('facility_id', $facilityIds);
            })
            ->sum('actual_kwh');

        $previousKwh = EnergyRecord::query()
            ->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$previousStartYm, $previousEndYm])
            ->when($facilityIds, function ($q) use ($facilityIds) {
                return $q->whereIn('facility_id', $facilityIds);
            })
            ->sum('actual_kwh');

        if ($previousKwh > 0) {
            $kwhTrend = (($currentKwh - $previousKwh) / $previousKwh) * 100;
            $kwhTrend = ($kwhTrend >= 0 ? '+' : '') . number_format($kwhTrend, 1) . '%';
        } else {
            $kwhTrend = '';
        }

        // Insert alerts as notifications only for default (latest 6 months) view.
        if ($isDefaultRange) {
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
        }

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
            'role' => $role,
            'user' => $user,
            'periodStartLabel' => $periodStartLabel,
            'periodEndLabel' => $periodEndLabel,
            'periodStartInput' => $periodStartInput,
            'periodEndInput' => $periodEndInput,
            'periodMonthCount' => $periodMonthCount,
        ]);
    }
}
