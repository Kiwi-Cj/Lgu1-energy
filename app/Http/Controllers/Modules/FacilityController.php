<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::all();
        $totalFacilities = $facilities->count();
        $activeFacilities = $facilities->where('status', 'active')->count();
        $inactiveFacilities = $facilities->where('status', 'inactive')->count();
        $maintenanceFacilities = $facilities->where('status', 'maintenance')->count();
        return view('modules.facilities.index', compact(
            'facilities',
            'totalFacilities',
            'activeFacilities',
            'inactiveFacilities',
            'maintenanceFacilities'
        ));
    }

    public function create()
    {
        // Block Staff from creating facilities
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('facilities.index')
                ->with('error', 'You do not have permission to create facilities.');
        }
        return view('modules.facilities.create');
    }

    public function store(Request $request)
    {
        // Block Staff from creating facilities
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('facilities.index')
                ->with('error', 'You do not have permission to create facilities.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'department' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'floor_area' => 'nullable|numeric',
            'floors' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'operating_hours' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $data = $request->only([
            'name', 'type', 'department', 'address', 'barangay', 'floor_area', 'floors', 'year_built', 'operating_hours', 'status'
        ]);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('facility_images', 'public');
        }
        Facility::create($data);
        return redirect()->route('facilities.index')->with('success', 'Facility created successfully!');
    }

    public function show($id)
    {
        $facility = Facility::findOrFail($id);
        return view('modules.facilities.show', compact('facility'));
    }

    public function edit($id)
    {
        // Block Staff from editing facilities
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('facilities.index')
                ->with('error', 'You do not have permission to edit facilities.');
        }
        
        $facility = Facility::findOrFail($id);
        return view('modules.facilities.edit', compact('facility'));
    }

    public function update(Request $request, $id)
    {
        // Block Staff from updating facilities
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('facilities.index')
                ->with('error', 'You do not have permission to update facilities.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'department' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'floor_area' => 'nullable|numeric',
            'floors' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'operating_hours' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $facility = Facility::findOrFail($id);
        $data = $request->only([
            'name', 'type', 'department', 'address', 'barangay', 'floor_area', 'floors', 'year_built', 'operating_hours', 'status'
        ]);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('facility_images', 'public');
        }
        $facility->update($data);
        return redirect()->route('facilities.index')->with('success', 'Facility updated successfully!');
    }

    public function destroy($id)
    {
        // Block Staff from deleting facilities
        if (auth()->check() && strtolower(auth()->user()->role ?? '') === 'staff') {
            return redirect()->route('facilities.index')
                ->with('error', 'You do not have permission to delete facilities.');
        }
        
        $facility = Facility::findOrFail($id);
        $facility->delete();
        return redirect()->route('facilities.index')->with('success', 'Facility deleted successfully!');
    }

    // AJAX endpoint for facility modal details
    public function modalDetail($id)
    {
        $facility = \App\Models\Facility::findOrFail($id);
        $profile = $facility->energyProfiles()->latest()->first();
        $energyRecords = $facility->energyRecords()->orderBy('year','desc')->orderBy('month','desc')->get();
        $floorArea = $facility->floor_area;
        $eui = null;
        if ($floorArea && $energyRecords->count()) {
            $eui = round($energyRecords->first()->kwh_consumed / $floorArea, 2);
        }
        $maintenance = \App\Models\Maintenance::where('facility_id', $facility->id)
            ->orderBy('scheduled_date','desc')->first();
        $lastMaint = \App\Models\Maintenance::where('facility_id', $facility->id)
            ->whereNotNull('completed_date')->orderBy('completed_date','desc')->first();
        $nextMaint = \App\Models\Maintenance::where('facility_id', $facility->id)
            ->where('maintenance_status','Scheduled')->orderBy('scheduled_date','asc')->first();
        $usageRows = [];
        foreach ($energyRecords as $rec) {
            $avg = $profile ? $profile->average_monthly_kwh : null;
            $variance = ($rec->kwh_consumed && $avg !== null) ? $rec->kwh_consumed - $avg : null;
            $percent = ($avg && $avg != 0) ? ($rec->kwh_consumed / $avg) * 100 : 0;
            // Inverted: High: <60%, Medium: 60% to <80%, Low: >=80%
            if ($percent < 60) {
                $ratingVal = 'High';
            } elseif ($percent >= 60 && $percent < 80) {
                $ratingVal = 'Medium';
            } else {
                $ratingVal = 'Low';
            }
            $usageRows[] = [
                'month' => ($rec->month ? date('M', mktime(0,0,0,(int)$rec->month,1)) : '-') . ' ' . $rec->year,
                'actual_kwh' => $rec->kwh_consumed,
                'avg_kwh' => $avg,
                'variance' => $variance,
                'rating' => $ratingVal,
                'status' => $rec->status,
            ];
        }
        // Recommendations logic
        $recommendations = [];
        $latestRating = isset($usageRows[0]['rating']) ? $usageRows[0]['rating'] : null;
        if ($latestRating === 'Low') {
            $recommendations[] = 'Immediate investigation required: Check for abnormal equipment operation, leaks, or unauthorized usage.';
            $recommendations[] = 'Review recent changes in facility operations or occupancy.';
            $recommendations[] = 'Consider energy audit for detailed assessment.';
        } else {
            if (!empty($usageRows) && collect($usageRows)->contains('rating', 'Low')) {
                $recommendations[] = 'Schedule preventive maintenance';
                $recommendations[] = 'Inspect HVAC / lighting system';
                $recommendations[] = 'Optimize operating hours';
            }
            if (count($usageRows) >= 3) {
                $t = collect($usageRows)->pluck('actual_kwh');
                $n = $t->count();
                if ($n >= 3 && $t[$n-3] < $t[$n-2] && $t[$n-2] < $t[$n-1]) {
                    $recommendations[] = 'Investigate unusual load increase';
                    $recommendations[] = 'Check electrical wiring / leaks';
                }
            }
            if (empty($recommendations)) $recommendations[] = 'No special recommendations.';
        }
        return response()->json([
            'name' => $facility->name,
            'type' => $facility->type,
            'barangay' => $facility->barangay,
            'avg_kwh' => $profile ? $profile->average_monthly_kwh : null,
            'main_source' => $profile ? $profile->main_energy_source : null,
            'backup_power' => $profile ? $profile->backup_power : null,
            'num_meters' => $profile ? $profile->number_of_meters : null,
            'floor_area' => $floorArea,
            'eui' => $eui,
            'usage' => $usageRows,
            'trend' => $energyRecords->pluck('kwh_consumed'),
            'trend_labels' => $energyRecords->map(function($r){ return ($r->month ? date('M', mktime(0,0,0,(int)$r->month,1)) : '-') . ' ' . $r->year; }),
            'last_maintenance' => $lastMaint ? $lastMaint->completed_date : null,
            'next_maintenance' => $nextMaint ? $nextMaint->scheduled_date : null,
            'maint_remarks' => $maintenance ? $maintenance->remarks : null,
            'maint_link' => url('/modules/maintenance/index?facility_id='.$facility->id),
            'recommendations' => $recommendations,
        ]);
    }
}
