<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\Submeter;
use App\Models\SubmeterEquipment;
use App\Support\EnergyCost;
use App\Support\RoleAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoadTrackingController extends Controller
{
    /**
     * Standard appliance presets with average power ratings and operating profiles.
     */
    public const APPLIANCE_PRESETS = [
        [
            'name' => 'Inverter Split AC (1.0 HP / 0.75 kW)',
            'category' => 'HVAC / Cooling',
            'rated_watts' => 750,
            'hours_per_day' => 8,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Inverter Split AC (1.5 HP / 1.1 kW)',
            'category' => 'HVAC / Cooling',
            'rated_watts' => 1150,
            'hours_per_day' => 8,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Inverter Split AC (2.0 HP / 1.5 kW)',
            'category' => 'HVAC / Cooling',
            'rated_watts' => 1500,
            'hours_per_day' => 9,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Window Type AC (Non-Inverter 1.5 HP)',
            'category' => 'HVAC / Cooling',
            'rated_watts' => 1400,
            'hours_per_day' => 8,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Floor Standing AC (3.0 - 5.0 HP)',
            'category' => 'HVAC / Cooling',
            'rated_watts' => 3800,
            'hours_per_day' => 8,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Desktop Computer with Monitor',
            'category' => 'IT & Office Equipment',
            'rated_watts' => 200,
            'hours_per_day' => 8,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Office Laptop & Docking Station',
            'category' => 'IT & Office Equipment',
            'rated_watts' => 65,
            'hours_per_day' => 8,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Server Rack / Network Equipment',
            'category' => 'IT & Office Equipment',
            'rated_watts' => 600,
            'hours_per_day' => 24,
            'days_per_month' => 30,
        ],
        [
            'name' => 'Heavy Duty Multi-Function Network Printer/Copier',
            'category' => 'IT & Office Equipment',
            'rated_watts' => 350,
            'hours_per_day' => 6,
            'days_per_month' => 22,
        ],
        [
            'name' => 'LED T8 Tube Light Fixture (2x18W)',
            'category' => 'Lighting',
            'rated_watts' => 36,
            'hours_per_day' => 10,
            'days_per_month' => 22,
        ],
        [
            'name' => 'LED Downlight / Ceiling Panel (18W)',
            'category' => 'Lighting',
            'rated_watts' => 18,
            'hours_per_day' => 10,
            'days_per_month' => 22,
        ],
        [
            'name' => 'High Bay / Flood Light Fixture (100W)',
            'category' => 'Lighting',
            'rated_watts' => 100,
            'hours_per_day' => 12,
            'days_per_month' => 30,
        ],
        [
            'name' => 'Water Dispenser (Hot & Cold)',
            'category' => 'Appliances & Pantry',
            'rated_watts' => 500,
            'hours_per_day' => 10,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Two-Door Inverter Refrigerator',
            'category' => 'Appliances & Pantry',
            'rated_watts' => 180,
            'hours_per_day' => 24,
            'days_per_month' => 30,
        ],
        [
            'name' => 'Microwave Oven (Commercial/Pantry)',
            'category' => 'Appliances & Pantry',
            'rated_watts' => 1200,
            'hours_per_day' => 1.5,
            'days_per_month' => 22,
        ],
        [
            'name' => 'Water Booster Pump (1.5 HP)',
            'category' => 'Pumps & Motors',
            'rated_watts' => 1100,
            'hours_per_day' => 4,
            'days_per_month' => 30,
        ],
        [
            'name' => 'Elevator Motor / Traction Unit',
            'category' => 'Pumps & Motors',
            'rated_watts' => 7500,
            'hours_per_day' => 6,
            'days_per_month' => 30,
        ],
        [
            'name' => 'Exhaust Fan / Ventilation Blower',
            'category' => 'Pumps & Motors',
            'rated_watts' => 120,
            'hours_per_day' => 12,
            'days_per_month' => 22,
        ],
    ];

    public const CATEGORIES = [
        'HVAC / Cooling',
        'Lighting',
        'IT & Office Equipment',
        'Appliances & Pantry',
        'Pumps & Motors',
        'Medical & Specialized',
        'Other',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        if (! RoleAccess::can($user, 'view_load_tracking')) {
            abort(403, 'Unauthorized to view Load Tracking.');
        }

        $isStaff = RoleAccess::is($user, 'staff');
        $facilitiesQuery = Facility::query()->orderBy('name');

        if ($isStaff) {
            $staffFacilityIds = $user->facilities()->pluck('facilities.id')->toArray();
            $facilitiesQuery->whereIn('id', $staffFacilityIds);
        }

        $facilities = $facilitiesQuery->get();

        if ($facilities->isEmpty()) {
            return view('modules.load-tracking.index', [
                'facilities' => collect(),
                'selectedFacility' => null,
                'equipments' => collect(),
                'summary' => $this->emptySummary(),
                'categoryBreakdown' => [],
                'topConsumers' => [],
                'mainMeters' => collect(),
                'subMeters' => collect(),
                'categories' => self::CATEGORIES,
                'presets' => self::APPLIANCE_PRESETS,
                'canManage' => RoleAccess::can($user, 'manage_load_tracking'),
            ]);
        }

        $requestedFacilityId = (int) $request->get('facility_id', 0);
        $selectedFacility = $requestedFacilityId > 0
            ? $facilities->firstWhere('id', $requestedFacilityId)
            : $facilities->first();

        if (! $selectedFacility) {
            $selectedFacility = $facilities->first();
        }

        // Equipment query for selected facility
        $equipmentQuery = SubmeterEquipment::query()
            ->with(['mainMeter', 'submeter'])
            ->where(function ($q) use ($selectedFacility) {
                $q->where('facility_id', $selectedFacility->id)
                    ->orWhereHas('mainMeter', fn ($m) => $m->where('facility_id', $selectedFacility->id))
                    ->orWhereHas('submeter', fn ($s) => $s->where('facility_id', $selectedFacility->id));
            });

        // Filter by category if specified
        $categoryFilter = trim((string) $request->get('category', 'all'));
        if ($categoryFilter !== '' && $categoryFilter !== 'all') {
            $equipmentQuery->where('category', $categoryFilter);
        }

        // Filter by search keyword
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $equipmentQuery->where(function ($q) use ($search) {
                $q->where('equipment_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $equipments = $equipmentQuery->orderByDesc('id')->get();

        // Rate per kWh resolution
        $latestRecord = EnergyRecord::query()
            ->where('facility_id', $selectedFacility->id)
            ->whereNotNull('rate_per_kwh')
            ->where('rate_per_kwh', '>', 0)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        $ratePerKwh = $latestRecord && is_numeric($latestRecord->rate_per_kwh)
            ? (float) $latestRecord->rate_per_kwh
            : EnergyCost::DEFAULT_RATE_PER_KWH;

        // Baseline resolution
        $baselineKwh = is_numeric($selectedFacility->baseline_kwh) && (float) $selectedFacility->baseline_kwh > 0
            ? (float) $selectedFacility->baseline_kwh
            : null;

        // Calculate summary metrics
        $totalItems = $equipments->count();
        $totalUnits = (int) $equipments->sum('quantity');
        $totalConnectedWatts = (float) $equipments->sum(fn ($eq) => $eq->total_watts);
        $totalConnectedKw = round($totalConnectedWatts / 1000, 2);
        $totalDailyKwh = (float) $equipments->sum(fn ($eq) => $eq->daily_kwh);
        $totalMonthlyKwh = (float) $equipments->sum(fn ($eq) => $eq->monthly_kwh);
        $totalMonthlyCost = round($totalMonthlyKwh * $ratePerKwh, 2);

        // Baseline comparison & percentage
        $baselineVariance = null;
        $baselineVariancePercent = null;
        $baselineStatus = 'No Baseline';
        if ($baselineKwh && $baselineKwh > 0) {
            $baselineVariance = round($totalMonthlyKwh - $baselineKwh, 2);
            $baselineVariancePercent = round((($totalMonthlyKwh - $baselineKwh) / $baselineKwh) * 100, 1);
            if ($totalMonthlyKwh <= $baselineKwh) {
                $baselineStatus = 'Within Baseline';
            } elseif ($baselineVariancePercent <= 15) {
                $baselineStatus = 'Slight Exceedance';
            } else {
                $baselineStatus = 'Exceeding Baseline';
            }
        }

        $summary = [
            'total_items' => $totalItems,
            'total_units' => $totalUnits,
            'total_connected_watts' => $totalConnectedWatts,
            'total_connected_kw' => $totalConnectedKw,
            'total_daily_kwh' => round($totalDailyKwh, 2),
            'total_monthly_kwh' => round($totalMonthlyKwh, 2),
            'rate_per_kwh' => $ratePerKwh,
            'total_monthly_cost' => $totalMonthlyCost,
            'baseline_kwh' => $baselineKwh,
            'baseline_variance' => $baselineVariance,
            'baseline_variance_percent' => $baselineVariancePercent,
            'baseline_status' => $baselineStatus,
        ];

        // Group equipment by category for Donut chart
        $categoryBreakdown = [];
        $categoriesGrouped = $equipments->groupBy(function ($eq) {
            return $eq->category ?: 'Other';
        });

        foreach ($categoriesGrouped as $catName => $items) {
            $catMonthlyKwh = round((float) $items->sum(fn ($eq) => $eq->monthly_kwh), 2);
            $catKw = round((float) $items->sum(fn ($eq) => $eq->total_watts) / 1000, 2);
            $catUnits = (int) $items->sum('quantity');
            $percentage = $totalMonthlyKwh > 0 ? round(($catMonthlyKwh / $totalMonthlyKwh) * 100, 1) : 0;

            $categoryBreakdown[] = [
                'category' => $catName,
                'monthly_kwh' => $catMonthlyKwh,
                'connected_kw' => $catKw,
                'units' => $catUnits,
                'percentage' => $percentage,
                'monthly_cost' => round($catMonthlyKwh * $ratePerKwh, 2),
            ];
        }

        // Sort categories by highest kWh
        usort($categoryBreakdown, fn ($a, $b) => $b['monthly_kwh'] <=> $a['monthly_kwh']);

        // Top 5 consuming equipment for Bar chart
        $topConsumers = $equipments->sortByDesc(fn ($eq) => $eq->monthly_kwh)->take(5)->map(function ($eq) use ($totalMonthlyKwh, $ratePerKwh) {
            $monthlyKwh = round($eq->monthly_kwh, 2);
            $pct = $totalMonthlyKwh > 0 ? round(($monthlyKwh / $totalMonthlyKwh) * 100, 1) : 0;
            return [
                'name' => $eq->equipment_name,
                'category' => $eq->category ?: 'Other',
                'monthly_kwh' => $monthlyKwh,
                'monthly_cost' => round($monthlyKwh * $ratePerKwh, 2),
                'percentage' => $pct,
            ];
        })->values()->all();

        // Facility meters for optional assignment
        $mainMeters = FacilityMeter::where('facility_id', $selectedFacility->id)
            ->where('meter_type', 'main')
            ->orderBy('meter_name')
            ->get(['id', 'meter_name', 'meter_number']);

        $subMeters = Submeter::where('facility_id', $selectedFacility->id)
            ->orderBy('submeter_name')
            ->get(['id', 'submeter_name']);

        $canManage = RoleAccess::can($user, 'manage_load_tracking');

        return view('modules.load-tracking.index', [
            'facilities' => $facilities,
            'selectedFacility' => $selectedFacility,
            'equipments' => $equipments,
            'summary' => $summary,
            'categoryBreakdown' => $categoryBreakdown,
            'topConsumers' => $topConsumers,
            'mainMeters' => $mainMeters,
            'subMeters' => $subMeters,
            'categories' => self::CATEGORIES,
            'presets' => self::APPLIANCE_PRESETS,
            'categoryFilter' => $categoryFilter,
            'search' => $search,
            'canManage' => $canManage,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (! RoleAccess::can($user, 'manage_load_tracking')) {
            abort(403, 'Unauthorized to add equipment.');
        }

        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'equipment_name' => 'required|string|max:191',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:191',
            'meter_scope' => 'nullable|in:facility,main,sub',
            'facility_meter_id' => 'nullable|exists:facility_meters,id',
            'submeter_id' => 'nullable|exists:submeters,id',
            'quantity' => 'required|integer|min:1|max:9999',
            'rated_watts' => 'required|numeric|min:0.1|max:1000000',
            'operating_hours_per_day' => 'required|numeric|min:0.1|max:24',
            'operating_days_per_month' => 'required|integer|min:1|max:31',
            'notes' => 'nullable|string|max:1000',
        ]);

        $meterScope = $validated['meter_scope'] ?? 'facility';
        if ($meterScope === 'main' && ! empty($validated['facility_meter_id'])) {
            $validated['meter_scope'] = 'main';
            $validated['submeter_id'] = null;
        } elseif ($meterScope === 'sub' && ! empty($validated['submeter_id'])) {
            $validated['meter_scope'] = 'sub';
            $validated['facility_meter_id'] = null;
        } else {
            $validated['meter_scope'] = 'main'; // default scope
            $validated['facility_meter_id'] = null;
            $validated['submeter_id'] = null;
        }

        SubmeterEquipment::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Equipment load entry created successfully.',
            ]);
        }

        return redirect()
            ->route('modules.load-tracking.index', ['facility_id' => $validated['facility_id']])
            ->with('success', "Equipment '{$validated['equipment_name']}' added successfully!");
    }

    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        if (! RoleAccess::can($user, 'manage_load_tracking')) {
            abort(403, 'Unauthorized to update equipment.');
        }

        $equipment = SubmeterEquipment::findOrFail($id);

        $validated = $request->validate([
            'equipment_name' => 'required|string|max:191',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:191',
            'meter_scope' => 'nullable|in:facility,main,sub',
            'facility_meter_id' => 'nullable|exists:facility_meters,id',
            'submeter_id' => 'nullable|exists:submeters,id',
            'quantity' => 'required|integer|min:1|max:9999',
            'rated_watts' => 'required|numeric|min:0.1|max:1000000',
            'operating_hours_per_day' => 'required|numeric|min:0.1|max:24',
            'operating_days_per_month' => 'required|integer|min:1|max:31',
            'notes' => 'nullable|string|max:1000',
        ]);

        $meterScope = $validated['meter_scope'] ?? 'facility';
        if ($meterScope === 'main' && ! empty($validated['facility_meter_id'])) {
            $validated['meter_scope'] = 'main';
            $validated['submeter_id'] = null;
        } elseif ($meterScope === 'sub' && ! empty($validated['submeter_id'])) {
            $validated['meter_scope'] = 'sub';
            $validated['facility_meter_id'] = null;
        } else {
            $validated['meter_scope'] = 'main';
            $validated['facility_meter_id'] = null;
            $validated['submeter_id'] = null;
        }

        $equipment->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Equipment updated successfully.',
            ]);
        }

        return redirect()
            ->route('modules.load-tracking.index', ['facility_id' => $equipment->facility_id])
            ->with('success', "Equipment '{$equipment->equipment_name}' updated successfully!");
    }

    public function destroy(Request $request, int $id)
    {
        $user = auth()->user();
        if (! RoleAccess::can($user, 'manage_load_tracking')) {
            abort(403, 'Unauthorized to delete equipment.');
        }

        $equipment = SubmeterEquipment::findOrFail($id);
        $facilityId = $equipment->facility_id;
        $name = $equipment->equipment_name;

        $equipment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Equipment deleted successfully.',
            ]);
        }

        return redirect()
            ->route('modules.load-tracking.index', ['facility_id' => $facilityId])
            ->with('success', "Equipment '{$name}' deleted successfully!");
    }

    public function export(Request $request, int $facilityId): StreamedResponse
    {
        $user = auth()->user();
        if (! RoleAccess::can($user, 'view_load_tracking')) {
            abort(403, 'Unauthorized.');
        }

        $facility = Facility::findOrFail($facilityId);
        $equipments = SubmeterEquipment::query()
            ->with(['mainMeter', 'submeter'])
            ->where('facility_id', $facility->id)
            ->get();

        $latestRecord = EnergyRecord::query()
            ->where('facility_id', $facility->id)
            ->whereNotNull('rate_per_kwh')
            ->where('rate_per_kwh', '>', 0)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        $ratePerKwh = $latestRecord && is_numeric($latestRecord->rate_per_kwh)
            ? (float) $latestRecord->rate_per_kwh
            : EnergyCost::DEFAULT_RATE_PER_KWH;

        $filename = 'Load_Schedule_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $facility->name) . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($facility, $equipments, $ratePerKwh) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['LGU-1 Energy - Facility Load Schedule & Energy Consumption Report']);
            fputcsv($handle, ['Facility Name:', $facility->name]);
            fputcsv($handle, ['Address:', $facility->address ?? 'N/A']);
            fputcsv($handle, ['Baseline kWh:', $facility->baseline_kwh ?? 'N/A']);
            fputcsv($handle, ['Applied Electricity Rate (PHP/kWh):', number_format($ratePerKwh, 2)]);
            fputcsv($handle, ['Export Date:', date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, [
                'ID',
                'Equipment / Appliance Name',
                'Category',
                'Location / Area',
                'Assigned Meter',
                'Quantity',
                'Rated Watts (W)',
                'Total Watts (W)',
                'Total kW',
                'Operating Hours / Day',
                'Operating Days / Month',
                'Daily Energy (kWh)',
                'Monthly Energy (kWh)',
                'Est. Monthly Cost (PHP)',
                'Notes',
            ]);

            $totalKw = 0;
            $totalDailyKwh = 0;
            $totalMonthlyKwh = 0;
            $totalCost = 0;

            foreach ($equipments as $eq) {
                $kw = round($eq->total_watts / 1000, 3);
                $daily = round($eq->daily_kwh, 2);
                $monthly = round($eq->monthly_kwh, 2);
                $cost = round($monthly * $ratePerKwh, 2);

                $totalKw += $kw;
                $totalDailyKwh += $daily;
                $totalMonthlyKwh += $monthly;
                $totalCost += $cost;

                fputcsv($handle, [
                    $eq->id,
                    $eq->equipment_name,
                    $eq->category ?: 'Other',
                    $eq->location ?: 'N/A',
                    $eq->meter_name,
                    $eq->quantity,
                    number_format($eq->rated_watts, 2),
                    number_format($eq->total_watts, 2),
                    number_format($kw, 3),
                    number_format($eq->operating_hours_per_day, 2),
                    $eq->operating_days_per_month,
                    number_format($daily, 2),
                    number_format($monthly, 2),
                    number_format($cost, 2),
                    $eq->notes ?? '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                '',
                '',
                '',
                '',
                $equipments->sum('quantity'),
                '',
                number_format($totalKw * 1000, 2),
                number_format($totalKw, 3),
                '',
                '',
                number_format($totalDailyKwh, 2),
                number_format($totalMonthlyKwh, 2),
                number_format($totalCost, 2),
                '',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function emptySummary(): array
    {
        return [
            'total_items' => 0,
            'total_units' => 0,
            'total_connected_watts' => 0,
            'total_connected_kw' => 0,
            'total_daily_kwh' => 0,
            'total_monthly_kwh' => 0,
            'rate_per_kwh' => EnergyCost::DEFAULT_RATE_PER_KWH,
            'total_monthly_cost' => 0,
            'baseline_kwh' => null,
            'baseline_variance' => null,
            'baseline_variance_percent' => null,
            'baseline_status' => 'No Data',
        ];
    }
}
