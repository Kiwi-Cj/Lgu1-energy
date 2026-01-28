<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Models\BaselineResetLog;


class FacilityController extends Controller
{

    /**
     * Update the specified facility in storage.
     */
    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'floor_area' => 'nullable|numeric|min:0',
            'floors' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'operating_hours' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/facility_images', $imageName);
            $validated['image'] = 'facility_images/' . $imageName;
        }

        $facility->update($validated);

        return redirect()->route('facilities.show', $facility->id)->with('success', 'Facility updated successfully.');
    }
    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'floor_area' => 'nullable|numeric|min:0',
            'floors' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'operating_hours' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/facility_images', $imageName);
            $validated['image'] = 'facility_images/' . $imageName;
        }

        $facility = Facility::create($validated);

        return redirect()->route('facilities.index')->with('success', 'Facility added successfully.');
    }
    /**
     * Show the form for editing the specified facility.
     */
    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        return view('modules.facilities.edit', compact('facility'));
    }
    public function show($id)
    {
        $facility = Facility::findOrFail($id);

        // Try to get first3months_data
        $first3mo = \DB::table('first3months_data')->where('facility_id', $facility->id)->first();
        $avgKwh = null;
        $showAvg = false;
        if ($first3mo && is_numeric($first3mo->month1) && is_numeric($first3mo->month2) && is_numeric($first3mo->month3)
            && $first3mo->month1 > 0 && $first3mo->month2 > 0 && $first3mo->month3 > 0) {
            $avgKwh = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
            $showAvg = true;
        } else {
            // Fallback: use last 3 energyReadings
            $readings = method_exists($facility, 'energyReadings') ? $facility->energyReadings()->orderBy('year')->orderBy('month')->take(3)->pluck('kwh') : collect();
            if ($readings->count() === 3) {
                $avgKwh = $readings->avg();
                $showAvg = true;
            }
        }

        return view('modules.facilities.show', compact('facility', 'avgKwh', 'showAvg'));
    }
    private function isSuperAdmin()
    {
        return auth()->check() && strtolower(auth()->user()->role) === 'super admin';
    }

    private function isStaff()
    {
        return auth()->check() && strtolower(auth()->user()->role) === 'staff';
    }

    private function isEngineer()
    {
        return auth()->check() && strtolower(auth()->user()->role) === 'engineer';
    }

    /* =========================
        FACILITY LIST
    ========================== */
    public function index()
    {
        $user = auth()->user();

        if ($this->isStaff()) {
            $facilities = Facility::where('id', $user->facility_id)->get();
        } else {
            $facilities = Facility::all();
        }

        // Compute 3-month average kWh for each facility, prefer first3months_data if available
        $facilitiesWithAvg = $facilities->map(function($facility) {
            $first3mo = \DB::table('first3months_data')->where('facility_id', $facility->id)->first();
            if ($first3mo) {
                $avg3MoKwh = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
                $facility->avg3MoKwh = $avg3MoKwh;
                $facility->has3MoData = true;
                // Compute dynamic size based on average
                if ($avg3MoKwh < 1500) {
                    $facility->dynamicSize = 'Small';
                } elseif ($avg3MoKwh < 3000) {
                    $facility->dynamicSize = 'Medium';
                } elseif ($avg3MoKwh < 6000) {
                    $facility->dynamicSize = 'Large';
                } else {
                    $facility->dynamicSize = 'Extra Large';
                }
            } else {
                $records = $facility->energyRecords()
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->take(3)
                    ->get();
                $has3MoData = $records->count() === 3;
                $avg3MoKwh = $has3MoData ? $records->avg('kwh_consumed') : null;
                $facility->avg3MoKwh = $avg3MoKwh;
                $facility->has3MoData = $has3MoData;
                // Compute dynamic size based on average
                if ($has3MoData && $avg3MoKwh) {
                    if ($avg3MoKwh < 1500) {
                        $facility->dynamicSize = 'Small';
                    } elseif ($avg3MoKwh < 3000) {
                        $facility->dynamicSize = 'Medium';
                    } elseif ($avg3MoKwh < 6000) {
                        $facility->dynamicSize = 'Large';
                    } else {
                        $facility->dynamicSize = 'Extra Large';
                    }
                } else {
                    $facility->dynamicSize = $facility->size ?? 'N/A';
                }
            }
            return $facility;
        });

        return view('modules.facilities.index', [
            'facilities' => $facilitiesWithAvg,
            'totalFacilities' => $facilities->count(),
            'activeFacilities' => $facilities->where('status', 'active')->count(),
            'inactiveFacilities' => $facilities->where('status', 'inactive')->count(),
            'maintenanceFacilities' => $facilities->where('status', 'maintenance')->count(),
        ]);
    }

    /* =========================
        FACILITY MODAL DETAILS
    ========================== */
    public function modalDetail($id)
    {
        $facility = Facility::findOrFail($id);
        $profile = $facility->energyProfiles()->latest()->first();
        $energyRecords = $facility->energyRecords()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Debug: Log when modalDetail is called
        \Log::info('modalDetail called', ['facility_id' => $id]);

        // BASELINE
        $avgKwh = null;
        if ($profile && $profile->average_monthly_kwh && $profile->baseline_locked) {
            $avgKwh = $profile->average_monthly_kwh;
        } elseif ($energyRecords->count() >= 3) {
            $avgKwh = round($energyRecords->take(3)->avg('kwh_consumed'), 2);

            if ($profile) {
                $profile->update([
                    'average_monthly_kwh' => $avgKwh,
                    'baseline_locked' => true,
                    'baseline_source' => 'computed',
                ]);
            } else {
                try {
                    $created = $facility->energyProfiles()->create([
                        'average_monthly_kwh' => $avgKwh,
                        'baseline_locked' => true,
                        'baseline_source' => 'computed',
                        'electric_meter_no' => 'N/A',
                        'utility_provider' => 'N/A',
                        'contract_account_no' => 'N/A',
                        'main_energy_source' => 'Grid',
                        'backup_power' => 'None',
                        'transformer_capacity' => 0,
                        'number_of_meters' => 1,
                    ]);
                    \Log::info('EnergyProfile created', ['profile' => $created]);
                } catch (\Exception $e) {
                    \Log::error('EnergyProfile creation failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // TREND
        $trendRecords = $energyRecords->take(3)->reverse();
        $trendData = $trendRecords->pluck('kwh_consumed');
        $trendLabels = $trendRecords->map(function ($r) {
            return $r->month ? date('M', mktime(0, 0, 0, $r->month, 1)) . ' ' . $r->year : '- ' . $r->year;
        });

        // TREND ANALYSIS
        $trendAnalysis = null;
        if ($trendData->count() === 3) {
            $vals = $trendData->values();
            if ($vals[2] > $vals[1] && $vals[1] > $vals[0]) $trendAnalysis = 'Increasing';
            elseif ($vals[2] < $vals[1] && $vals[1] < $vals[0]) $trendAnalysis = 'Decreasing';
            else $trendAnalysis = 'Stable';
        }

        // SUDDEN SPIKE
        $suddenSpike = false;
        if ($avgKwh && $trendData->isNotEmpty()) {
            $suddenSpike = $trendData->last() > ($avgKwh * 1.30);
        }

        // USAGE ROWS
        $usageRows = [];
        foreach ($trendRecords as $rec) {
            $variance = ($avgKwh !== null) ? $rec->kwh_consumed - $avgKwh : null;
            $percent = ($avgKwh && $avgKwh > 0) ? ($rec->kwh_consumed / $avgKwh) * 100 : null;

            if ($percent !== null && $percent > 120) $alert = 'High';
            elseif ($percent !== null && $percent >= 90) $alert = 'Medium';
            else $alert = 'Normal';

            $usageRows[] = [
                'month' => $rec->month ? date('M', mktime(0, 0, 0, $rec->month, 1)) . ' ' . $rec->year : '- ' . $rec->year,
                'actual_kwh' => $rec->kwh_consumed,
                'baseline_kwh' => $avgKwh,
                'variance' => $variance,
                'alert_level' => $alert,
            ];
        }

        // RECOMMENDATIONS
        $recommendations = [];
        if ($trendAnalysis === 'Increasing') $recommendations[] = 'Energy consumption trend is increasing. Review operating schedules and equipment condition.';
        if ($suddenSpike) $recommendations[] = 'Sudden increase detected. Possible abnormal usage or equipment issue. Field validation recommended.';
        $highKwhRec = $this->getHighKwhRecommendation($avgKwh, $trendData);
        if ($highKwhRec) $recommendations[] = $highKwhRec;
        if (empty($recommendations)) $recommendations[] = 'Energy consumption within acceptable range. Continue regular monitoring.';

        // EUI
        $monthlyEui = $annualEui = null;
        if ($facility->floor_area && $energyRecords->count()) {
            $latestKwh = $energyRecords->first()->kwh_consumed;
            $monthlyEui = round($latestKwh / $facility->floor_area, 2);
            $annualEui = round(($latestKwh * 12) / $facility->floor_area, 2);
        }

        // MAINTENANCE
        $lastMaint = Maintenance::where('facility_id', $facility->id)
            ->whereNotNull('completed_date')
            ->orderBy('completed_date', 'desc')->first();

        $nextMaint = Maintenance::where('facility_id', $facility->id)
            ->where('maintenance_status', 'Scheduled')
            ->orderBy('scheduled_date', 'asc')->first();

        return response()->json([
            'facility' => $facility->name,
            'barangay' => $facility->barangay,
            'baseline_kwh' => $avgKwh,
            'baseline_status' => $profile && $profile->baseline_locked ? 'Approved Baseline' : 'Temporary Baseline',
            'engineer_approved' => $profile ? (bool)$profile->engineer_approved : false,
            'trend_labels' => $trendLabels,
            'trend_data' => $trendData,
            'trend_analysis' => $trendAnalysis,
            'usage' => $usageRows,
            'monthly_eui' => $monthlyEui,
            'annual_eui' => $annualEui,
            'last_maintenance' => $lastMaint ? $lastMaint->completed_date : null,
            'next_maintenance' => $nextMaint ? $nextMaint->scheduled_date : null,
            'recommendations' => $recommendations,
            'disclaimer' => 'System-generated analysis. Subject to validation by assigned LGU personnel.',
        ]);
    }

    /* =========================
        HIGH KWH THRESHOLD
    ========================== */
    private function getHighKwhRecommendation($avgKwh, $trendData)
    {
        if (!$avgKwh || $trendData->isEmpty()) return null;

        if ($avgKwh > 3000) $percent = 0.10;
        elseif ($avgKwh > 1500) $percent = 0.15;
        elseif ($avgKwh > 500) $percent = 0.20;
        else $percent = 0.30;

        $threshold = $avgKwh * (1 + $percent);

        if ($trendData->last() >= $threshold) {
            return 'Consumption exceeded allowable threshold for facility size. Engineering review recommended.';
        }

        return null;
    }

    /* =========================
        BASELINE RESET W/ LOG
    ========================== */
    public function resetBaseline(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$this->isSuperAdmin()) {
            return redirect()->route('facilities.index')
                ->with('error', 'Only Super Admin can reset baseline.');
        }

        $facility = Facility::findOrFail($id);
        $profile = $facility->energyProfiles()->latest()->first();

        if ($profile) {
            $profile->update([
                'average_monthly_kwh' => null,
                'baseline_locked' => false,
                'baseline_source' => null,
            ]);
        }

        // LOGGING
        BaselineResetLog::create([
            'facility_id' => $facility->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'created_at' => now(),
        ]);

        return redirect()->route('facilities.show', $id)
            ->with('success', 'Baseline reset successfully and logged.');
    }

    /* =========================
        ENGINEER APPROVAL TOGGLE
    ========================== */
    public function toggleEngineerApproval($id)
    {
        $profile = Facility::findOrFail($id)->energyProfiles()->latest()->first();

        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'No energy profile found.'], 404);
        }

        if (!$this->isEngineer() && !$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only engineers or super admins can approve.'], 403);
        }

        $profile->engineer_approved = !$profile->engineer_approved;
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Engineer approval status toggled.',
            'engineer_approved' => $profile->engineer_approved
        ]);
    }

    /* =========================
        MONTHLY COA REPORT EXPORT
    ========================== */
    public function exportMonthlyReport(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $facilities = Facility::all();

        $report = $facilities->map(function($f) use ($month, $year) {
            $record = $f->energyRecords()->where('month', $month)->where('year', $year)->first();
            $avg = $f->energyProfiles()->latest()->first()?->average_monthly_kwh;

            return [
                'facility' => $f->name,
                'barangay' => $f->barangay,
                'actual_kwh' => $record?->kwh_consumed ?? 0,
                'baseline_kwh' => $avg ?? 0,
                'variance' => ($record?->kwh_consumed ?? 0) - ($avg ?? 0),
            ];
        });

        $filename = "COA_Report_{$year}_{$month}.csv";
        $headers = ['Content-Type' => 'text/csv'];

        $callback = function() use ($report) {
            $file = fopen('php://output', 'w');
            if ($report->count()) fputcsv($file, array_keys($report->first()));
            foreach ($report as $row) fputcsv($file, $row);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /* =========================
        DELETE FACILITY
    ========================== */
    public function destroy($id)
    {
        $facility = Facility::findOrFail($id);
        $facility->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Facility deleted successfully.']);
        }
        return redirect()->route('facilities.index')->with('success', 'Facility deleted successfully.');
    }
}
