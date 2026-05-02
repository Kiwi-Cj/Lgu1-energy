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
        $usage = EnergyRecord::findOrFail($id);
        
        if ($response = $this->denyStaffCrossFacilityAccess($usage->facility_id, 'delete')) {
            return $response;
        }
        
        $usage->delete();

        $params = $this->buildIndexParams(request());

        return redirect()->route('modules.energy-monitoring.index', $params)->with('success', 'Energy record deleted successfully!');
    }

    public function edit($id)
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function update(Request $request, $id)
    {
        $usage = EnergyRecord::findOrFail($id);
        
        if ($response = $this->denyStaffCrossFacilityAccess($usage->facility_id, 'update')) {
            return $response;
        }
        
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
            'actual_kwh' => 'nullable|numeric|required_without:kwh_consumed',
            'kwh_consumed' => 'nullable|numeric|required_without:actual_kwh',
            'energy_cost' => 'nullable|numeric',
            'rate_per_kwh' => 'nullable|numeric',
            'baseline_kwh' => 'nullable|numeric',
            'alert' => 'nullable|string',
        ]);

        if ($response = $this->denyStaffCrossFacilityAccess((int) $validated['facility_id'], 'update', true)) {
            return $response;
        }

        $facility = Facility::find($validated['facility_id']);
        $usage->update($this->buildEnergyRecordPayload($request, $validated, $facility, false));

        $params = $this->buildIndexParams($request);

        return redirect()->route('modules.energy-monitoring.index', $params)
            ->with('success', 'Energy record updated successfully!');
    }

    public function create()
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
            'actual_kwh' => 'nullable|numeric|required_without:kwh_consumed',
            'kwh_consumed' => 'nullable|numeric|required_without:actual_kwh',
            'energy_cost' => 'nullable|numeric',
            'rate_per_kwh' => 'nullable|numeric',
            'baseline_kwh' => 'nullable|numeric',
            'alert' => 'nullable|string',
            'bill_image' => 'nullable|image|max:4096',
            'meralco_bill' => 'nullable|image|max:4096',
        ]);

        if ($response = $this->denyStaffCrossFacilityAccess((int) $validated['facility_id'], 'create', true)) {
            return $response;
        }

        $exists = EnergyRecord::where('facility_id', $validated['facility_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withInput()->withErrors(['duplicate' => 'An energy record for this facility and month/year already exists.']);
        }

        $facility = Facility::find($validated['facility_id']);
        EnergyRecord::create($this->buildEnergyRecordPayload($request, $validated, $facility, true));

        $params = $this->buildIndexParams($request);

        return redirect()->route('modules.energy-monitoring.index', $params)->with('success', 'Energy record added successfully!');
    }

    public function index(Request $request)
    {
        return redirect()->route('modules.energy-monitoring.index');
    }

    public function show($id)
    {
        return redirect()->route('modules.energy-monitoring.index');
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
        return view('modules.reports.energy', compact('energyRows', 'facilities', 'years', 'role', 'user'));
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

    private function buildEnergyRecordPayload(Request $request, array $validated, ?Facility $facility, bool $creating): array
    {
        $actualKwh = $this->resolveActualKwh($validated);
        $baseline = $this->resolveBaselineKwh($request, $facility, $validated);
        $deviation = ($baseline && $baseline != 0 && $actualKwh !== null)
            ? round((($actualKwh - $baseline) / $baseline) * 100, 2)
            : null;

        $payload = [
            'facility_id' => $validated['facility_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'actual_kwh' => $actualKwh,
            'baseline_kwh' => $baseline,
            'deviation' => $deviation,
            'energy_cost' => $validated['energy_cost'] ?? null,
            'rate_per_kwh' => $validated['rate_per_kwh'] ?? null,
            'alert' => $validated['alert'] ?? $this->resolveAlertLevel($baseline, $deviation),
        ];

        if ($creating) {
            $payload['recorded_by'] = auth()->id();
        }

        if ($request->hasFile('bill_image')) {
            $payload['bill_image'] = $request->file('bill_image')->store('meralco_bills', 'public');
        } elseif ($request->hasFile('meralco_bill')) {
            $payload['bill_image'] = $request->file('meralco_bill')->store('meralco_bills', 'public');
        }

        return $payload;
    }

    private function resolveActualKwh(array $validated): ?float
    {
        $value = $validated['actual_kwh'] ?? $validated['kwh_consumed'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    private function resolveBaselineKwh(Request $request, ?Facility $facility, array $validated): ?float
    {
        if (array_key_exists('baseline_kwh', $validated) && $validated['baseline_kwh'] !== null && $validated['baseline_kwh'] !== '') {
            return (float) $validated['baseline_kwh'];
        }

        $baselineInput = $request->input('baseline_kwh');
        if ($baselineInput !== null && $baselineInput !== '') {
            return (float) $baselineInput;
        }

        $profile = $facility?->energyProfiles()->latest()->first();
        if ($profile && $profile->baseline_kwh !== null) {
            return (float) $profile->baseline_kwh;
        }

        if ($facility && isset($facility->baseline_kwh) && $facility->baseline_kwh !== null) {
            return (float) $facility->baseline_kwh;
        }

        return null;
    }

    private function resolveAlertLevel(?float $baseline, ?float $deviation): string
    {
        if ($deviation === null) {
            return '';
        }

        $sizeLabel = match (true) {
            $baseline === null => 'Medium',
            $baseline <= 1000 => 'Small',
            $baseline <= 3000 => 'Medium',
            $baseline <= 10000 => 'Large',
            default => 'Extra Large',
        };

        $thresholds = [
            'Small' => ['level5' => 80, 'level4' => 50, 'level3' => 30, 'level2' => 15],
            'Medium' => ['level5' => 60, 'level4' => 40, 'level3' => 20, 'level2' => 10],
            'Large' => ['level5' => 30, 'level4' => 20, 'level3' => 12, 'level2' => 5],
            'Extra Large' => ['level5' => 20, 'level4' => 12, 'level3' => 7, 'level2' => 3],
        ];

        $t = $thresholds[$sizeLabel];

        return match (true) {
            $deviation > $t['level5'] => 'Critical',
            $deviation > $t['level4'] => 'Very High',
            $deviation > $t['level3'] => 'High',
            $deviation > $t['level2'] => 'Warning',
            default => 'Normal',
        };
    }

    private function buildIndexParams(Request $request): array
    {
        $params = [];

        foreach ([
            'facility_id_filter' => 'facility_id',
            'facility_id' => 'facility_id',
            'month_filter' => 'month',
            'month' => 'month',
            'year_filter' => 'year',
            'year' => 'year',
        ] as $source => $target) {
            if ($request->filled($source) && ! isset($params[$target])) {
                $params[$target] = $request->input($source);
            }
        }

        return $params;
    }

    private function denyStaffCrossFacilityAccess(int|string|null $facilityId, string $action, bool $redirectBack = false)
    {
        if (! RoleAccess::is(auth()->user(), 'staff')) {
            return null;
        }

        $userFacilityId = auth()->user()?->facility_id;
        if (! $userFacilityId || (string) $facilityId === (string) $userFacilityId) {
            return null;
        }

        $message = "You do not have permission to {$action} records for another facility.";

        return $redirectBack
            ? redirect()->back()->withInput()->withErrors(['facility_id' => $message])
            : redirect()->route('modules.energy-monitoring.index')->with('error', $message);
    }
}
