<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    private ?array $trendPercentThresholdsBySize = null;

    public function destroy($id)
    {
        $usage = \App\Models\EnergyRecord::findOrFail($id);
        
        // Restrict Staff from deleting records (or only allow deletion of their assigned facility's records)
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId && $usage->facility_id != $userFacilityId) {
                return redirect()->route('modules.energy.index')
                    ->with('error', 'You do not have permission to delete this record.');
            }
        }
        
        // Get current filters from POST (form fields)
        $facilityId = request()->input('facility_id');
        $month = request()->input('month');
        $year = request()->input('year');
        $usage->delete();
        // Pass filters back to index so the view is preserved
        $params = [];
        if ($facilityId) $params['facility_id'] = $facilityId;
        if ($month) $params['month'] = $month;
        if ($year) $params['year'] = $year;
        return redirect()->route('modules.energy.index', $params)->with('success', 'Energy record deleted successfully!');
    }

    public function edit($id)
    {
        $usage = \App\Models\EnergyRecord::findOrFail($id);
        
        // Restrict Staff to only edit their assigned facility's records
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId && $usage->facility_id != $userFacilityId) {
                return redirect()->route('modules.energy.index')
                    ->with('error', 'You do not have permission to edit this record.');
            }
            $facilities = \App\Models\Facility::where('id', $userFacilityId)->get();
        } else {
            $facilities = \App\Models\Facility::all();
        }
        // Pass current filters to the view
        $filters = [
            'facility_id' => request('facility_id'),
            'month' => request('month'),
            'year' => request('year'),
        ];
        return view('modules.energy.edit', compact('usage', 'facilities') + $filters);
    }

    public function update(Request $request, $id)
    {
        $usage = \App\Models\EnergyRecord::findOrFail($id);
        
        // Restrict Staff to only update their assigned facility's records
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId && $usage->facility_id != $userFacilityId) {
                return redirect()->route('modules.energy.index')
                    ->with('error', 'You do not have permission to update this record.');
            }
        }
        
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
            'kwh_consumed' => 'required|numeric',
            'status' => 'nullable|string',
        ]);

        // Prevent Staff from changing facility_id to a different facility
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId && $validated['facility_id'] != $userFacilityId) {
                return redirect()->back()->withInput()->withErrors(['facility_id' => 'You can only update records for your assigned facility.']);
            }
        }
        // Compute kwh_vs_avg and percent_change
        $facility = \App\Models\Facility::find($validated['facility_id']);
        $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
        $avg = $profile ? $profile->baseline_kwh : null;
        $kwh_vs_avg = ($avg !== null) ? $validated['kwh_consumed'] - $avg : null;
        $percent_change = ($avg && $avg != 0) ? (($kwh_vs_avg / $avg) * 100) : null;
        $validated['kwh_vs_avg'] = $kwh_vs_avg;
        $validated['percent_change'] = $percent_change;
        $usage->update($validated);
        // Preserve filters after update (use filter fields from form if present)
        $params = [];
        if ($request->filled('facility_id_filter')) $params['facility_id'] = $request->input('facility_id_filter');
        if ($request->filled('month_filter')) $params['month'] = $request->input('month_filter');
        if ($request->filled('year_filter')) $params['year'] = $request->input('year_filter');
        return redirect()->route('modules.energy.index', $params)
            ->with('success', 'Energy record updated successfully!');
    }

    public function create()
    {
        // Restrict Staff to only create records for their assigned facility
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId) {
                $facilities = \App\Models\Facility::where('id', $userFacilityId)->get();
            } else {
                return redirect()->route('modules.energy.index')
                    ->with('error', 'You are not assigned to any facility. Please contact administrator.');
            }
        } else {
            $facilities = \App\Models\Facility::all();
        }
        return view('modules.energy.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
            'kwh_consumed' => 'required|numeric',
            'meralco_bill' => 'nullable|image|max:4096',
        ]);

        // Restrict Staff to only create records for their assigned facility
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId && $validated['facility_id'] != $userFacilityId) {
                return redirect()->back()->withInput()->withErrors(['facility_id' => 'You can only create records for your assigned facility.']);
            }
        }

        // Prevent duplicate entry for the same facility, month, and year
        $exists = \App\Models\EnergyRecord::where('facility_id', $validated['facility_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withInput()->withErrors(['duplicate' => 'An energy record for this facility and month/year already exists.']);
        }

        // Handle Meralco bill image upload if present
        if ($request->hasFile('meralco_bill')) {
            $validated['meralco_bill'] = $request->file('meralco_bill')->store('meralco_bills', 'public');
        }

        $validated['created_by'] = auth()->id();

        // Map kwh_consumed to actual_kwh for DB
        $validated['actual_kwh'] = $validated['kwh_consumed'];
        unset($validated['kwh_consumed']);

        // --- Compute baseline kWh ---
        $baselineInput = $request->input('baseline_kwh');
        $facility = \App\Models\Facility::find($validated['facility_id']);
        $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
        $avg = null;
        if ($baselineInput !== null && $baselineInput !== '') {
            $avg = floatval($baselineInput);
        } elseif ($profile && $profile->baseline_kwh !== null) {
            $avg = floatval($profile->baseline_kwh);
        } elseif ($facility && $facility->baseline_kwh !== null) {
            $avg = floatval($facility->baseline_kwh);
        }
        $validated['baseline_kwh'] = $avg;

        // --- Compute deviation ---
        $actualKwh = isset($validated['actual_kwh']) ? floatval($validated['actual_kwh']) : null;
        $deviation = ($avg && $avg != 0 && $actualKwh !== null) ? round((($actualKwh - $avg) / $avg) * 100, 2) : null;
        $validated['deviation'] = $deviation;

        // --- Compute alert based on deviation and facility size ---
        $sizeLabel = 'Medium';
        if ($avg !== null) {
            if ($avg <= 1000) {
                $sizeLabel = 'Small';
            } elseif ($avg <= 3000) {
                $sizeLabel = 'Medium';
            } elseif ($avg <= 10000) {
                $sizeLabel = 'Large';
            } else {
                $sizeLabel = 'Extra Large';
            }
        }
        $thresholds = [
            'Small' =>    [ 'level5' => 80,  'level4' => 50,  'level3' => 30,  'level2' => 15 ],
            'Medium' =>   [ 'level5' => 60,  'level4' => 40,  'level3' => 20,  'level2' => 10 ],
            'Large' =>    [ 'level5' => 30,  'level4' => 20,  'level3' => 12,  'level2' => 5  ],
            'Extra Large'=>[ 'level5' => 20,  'level4' => 12,  'level3' => 7,   'level2' => 3  ],
        ];
        $t = $thresholds[$sizeLabel];
        if ($deviation === null) {
            $validated['alert'] = '';
        } elseif ($deviation > $t['level5']) {
            $validated['alert'] = 'Critical';
        } elseif ($deviation > $t['level4']) {
            $validated['alert'] = 'Very High';
        } elseif ($deviation > $t['level3']) {
            $validated['alert'] = 'High';
        } elseif ($deviation > $t['level2']) {
            $validated['alert'] = 'Warning';
        } else {
            $validated['alert'] = 'Normal';
        }

        // --- Save to database ---
        \App\Models\EnergyRecord::create($validated);

    // Preserve filters after add (use filter fields from form if present)
    $params = [];
    if ($request->filled('facility_id')) $params['facility_id'] = $request->input('facility_id');
    if ($request->filled('month')) $params['month'] = $request->input('month');
    if ($request->filled('year')) $params['year'] = $request->input('year');
    return redirect()->route('modules.energy.index', $params)->with('success', 'Energy record added successfully!');
    }
    public function index(Request $request)
    {
        $facilityId = $request->query('facility_id');
        $month = $request->query('month');
        $year = $request->query('year');
        $query = EnergyRecord::with('facility');
        
        // Restrict Staff to only see their assigned facility's data
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId) {
                $query->where('facility_id', $userFacilityId);
                // Override filter if Staff tries to access other facilities
                $facilityId = $userFacilityId;
            } else {
                // Staff with no assigned facility sees nothing
                $query->whereRaw('1 = 0');
            }
        } elseif ($facilityId) {
            $query->where('facility_id', $facilityId);
        }
        if ($month && $month !== 'all') {
            $query->where('month', $month);
        }
        if ($year) {
            $query->where('year', $year);
        }
        // Kung walang filter, ipakita lahat ng data (walang limit)
        // Kung may kahit anong filter, ipakita lahat ng tugma (walang limit)
        $recentUsages = $query->orderByDesc('year')->orderByDesc('month')->get();
        // Attach average_monthly_kwh, kwh_vs_avg, percent_change, and status for each record
        foreach ($recentUsages as $usage) {
            $profile = $usage->facility ? $usage->facility->energyProfiles()->latest()->first() : null;
            $avg = $profile ? $profile->baseline_kwh : null;
            $usage->baseline_kwh = $avg;
            $usage->kwh_vs_avg = ($avg !== null)
                ? $usage->kwh_consumed - $avg
                : null;
            $usage->percent_change = ($avg && $avg != 0)
                ? (($usage->kwh_vs_avg / $avg) * 100)
                : null;
            if ($avg !== null) {
                $diff = $usage->kwh_consumed - $avg;
                $usage->status = $diff > 0 ? 'High' : 'Normal';
            } else {
                $usage->status = null;
            }
        }
        $totalKwh = $query->sum('kwh_consumed');
        $activeFacilities = Facility::where('status', 'active')->count();
        $facilities = Facility::all();

        // Prepare monthly kWh data for graph (group by month for the selected year/facility)
        $graphYear = $year ?: date('Y');
        $graphQuery = EnergyRecord::query();
        if ($facilityId) {
            $graphQuery->where('facility_id', $facilityId);
        }
        $graphQuery->where('year', $graphYear);
        $monthlyKwh = array_fill(1, 12, 0);
        $baselineKwh = array_fill(1, 12, 0);
        $baselineValue = null;
        if ($facilityId) {
            $facility = Facility::find($facilityId);
            $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
            $baselineValue = $profile ? $profile->average_monthly_kwh : null;
        }
        foreach ($graphQuery->get() as $rec) {
            $m = (int)ltrim($rec->month, '0');
            if ($m >= 1 && $m <= 12) {
                $monthlyKwh[$m] += $rec->kwh_consumed;
                if ($baselineValue !== null) {
                    $baselineKwh[$m] = $baselineValue;
                }
            }
        }

        // Get available months for selected facility and year
        $availableMonths = [];
        if ($facilityId && $year) {
            $availableMonths = EnergyRecord::where('facility_id', $facilityId)
                ->where('year', $year)
                ->pluck('month')
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy.index', [
            'recentUsages' => $recentUsages,
            'totalKwh' => $totalKwh,
            'activeFacilities' => $activeFacilities,
            'facilities' => $facilities,
            'filterFacilityId' => $facilityId,
            'filterMonth' => $month,
            'filterYear' => $year,
            'monthlyKwh' => $monthlyKwh,
            'baselineKwh' => $baselineKwh,
            'graphYear' => $graphYear,
            'availableMonths' => $availableMonths,
            'role' => $role,
            'user' => $user,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadNotifCount,
        ]);
    }

    public function show($id)
    {
        $usage = EnergyRecord::with('facility')->findOrFail($id);
        
        // Restrict Staff to only view their assigned facility's records
        if (RoleAccess::is(auth()->user(), 'staff')) {
            $userFacilityId = auth()->user()->facility_id;
            if ($userFacilityId && $usage->facility_id != $userFacilityId) {
                return redirect()->route('modules.energy.index')
                    ->with('error', 'You do not have permission to view this record.');
            }
        }
        
        // Pass current filters to the view
        $filters = [
            'facility_id' => request('facility_id'),
            'month' => request('month'),
            'year' => request('year'),
        ];
        return view('modules.energy.show', compact('usage') + $filters);
    }

    public function energyReport(Request $request)
    {
        // Get all energy records with facility relationships
        $facilityId = $request->input('facility_id');
        $year = $request->input('year');
        $month = $request->input('month');
        $query = EnergyRecord::with('facility');
        if ($facilityId) {
            $query->where('facility_id', $facilityId);
        }
        if ($year) {
            $query->where('year', $year);
        } else {
            $query->where('year', date('Y'));
        }
        if ($month) {
            $query->where('month', $month);
        }
        $records = $query
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $trendByRecordId = $this->buildTrendDirectionMap($records);
        $energyRows = [];

        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = $record->baseline_kwh;
            $actualKwh = $record->actual_kwh;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = $trendByRecordId[$record->id] ?? 'stable';

            // Format month display
            $monthNum = (int)ltrim($record->month, '0');
            $monthName = date('M', mktime(0, 0, 0, $monthNum, 1));
            $monthYear = $monthName . ' ' . $record->year;

            $energyRows[] = [
                'facility' => $facility ? $facility->name : 'N/A',
                'month' => $monthYear,
                'actual_kwh' => number_format($actualKwh, 2),
                'baseline_kwh' => $baseline !== null ? number_format($baseline, 2) : '',
                'variance' => $variance !== null ? number_format($variance, 2) : '',
                'trend' => $trend,
            ];
        }
        
        $facilities = Facility::all();
        $years = EnergyRecord::select('year')->distinct()->orderByDesc('year')->pluck('year');
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.reports.energy', compact('energyRows', 'facilities', 'years', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    private function buildTrendDirectionMap($records): array
    {
        $thresholds = $this->getTrendPercentThresholdsBySize();
        $trendByRecordId = [];

        $records
            ->groupBy('facility_id')
            ->each(function ($facilityRecords) use (&$trendByRecordId, $thresholds) {
                $history = [];

                $facilityRecords
                    ->sortBy(fn ($row) => sprintf('%04d-%02d-%06d', (int) $row->year, (int) $row->month, (int) $row->id))
                    ->each(function ($record) use (&$history, &$trendByRecordId, $thresholds) {
                        $baseline = is_numeric($record->baseline_kwh ?? null) ? (float) $record->baseline_kwh : null;
                        $facilityBaseline = is_numeric(optional($record->facility)->baseline_kwh ?? null) ? (float) optional($record->facility)->baseline_kwh : null;
                        $sizeLabel = Facility::resolveSizeLabelFromBaseline($baseline ?? $facilityBaseline) ?? 'Small';
                        $threshold = $this->resolveTrendPercentTriggerForSize($sizeLabel, $thresholds);

                        $reference = null;
                        $historyCount = count($history);
                        if ($historyCount >= 3) {
                            $reference = array_sum(array_slice($history, -3)) / 3;
                        } elseif ($historyCount >= 1) {
                            $reference = end($history);
                        }

                        $trend = 'stable';
                        $actual = is_numeric($record->actual_kwh ?? null) ? (float) $record->actual_kwh : 0.0;
                        if ($reference !== null && $reference > 0) {
                            $trendPercent = (($actual - $reference) / $reference) * 100;
                            if ($trendPercent > $threshold) {
                                $trend = 'up';
                            } elseif ($trendPercent < -$threshold) {
                                $trend = 'down';
                            }
                        }

                        $trendByRecordId[$record->id] = $trend;

                        if ($actual > 0) {
                            $history[] = $actual;
                        }
                    });
            });

        return $trendByRecordId;
    }

    private function resolveTrendPercentTriggerForSize(string $sizeLabel, ?array $thresholds = null): float
    {
        $sizeKey = match (strtolower(str_replace('_', '-', trim($sizeLabel)))) {
            'small' => 'small',
            'small-medium', 'small medium' => 'small', // legacy fallback
            'medium' => 'medium',
            'large' => 'large',
            'extra-large', 'extra large', 'xlarge' => 'xlarge',
            default => 'small',
        };

        $all = $thresholds ?? $this->getTrendPercentThresholdsBySize();

        return (float) ($all[$sizeKey] ?? $all['small'] ?? 0);
    }

    private function getTrendPercentThresholdsBySize(): array
    {
        if ($this->trendPercentThresholdsBySize !== null) {
            return $this->trendPercentThresholdsBySize;
        }

        return $this->trendPercentThresholdsBySize = [
            'small' => 10,
            'medium' => 7,
            'large' => 4,
            'xlarge' => 2,
        ];
    }
}
