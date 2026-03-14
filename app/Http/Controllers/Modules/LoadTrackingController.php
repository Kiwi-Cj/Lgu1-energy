<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\Submeter;
use App\Models\SubmeterEquipment;
use App\Models\SubmeterEquipmentFile;
use App\Services\LoadTrackingService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoadTrackingController extends Controller
{
    public function __construct(private readonly LoadTrackingService $loadTrackingService)
    {
    }

    public function index(Request $request)
    {
        if (! $this->canView()) {
            return redirect()->route('dashboard.index')->with('error', 'You do not have permission to view load tracking.');
        }

        [$year, $month, $safeMonth] = $this->resolveMonth($request->query('month'));
        $selectedFacility = $request->filled('facility_id') ? (int) $request->query('facility_id') : null;
        $selectedSubmeter = $request->filled('submeter_id') ? (int) $request->query('submeter_id') : null;
        $selectedMainMeter = $request->filled('main_meter_id') ? (int) $request->query('main_meter_id') : null;
        $selectedMeterScope = strtolower(trim((string) $request->query('meter_scope', 'all')));
        $selectedConsumptionFilter = strtolower(trim((string) $request->query('consumption_filter', 'warning_high')));
        if (! in_array($selectedMeterScope, ['all', 'sub', 'main'], true)) {
            $selectedMeterScope = 'all';
        }
        if (! in_array($selectedConsumptionFilter, ['warning_high', 'all'], true)) {
            $selectedConsumptionFilter = 'warning_high';
        }

        if ($selectedMeterScope === 'sub') {
            $selectedMainMeter = null;
        } elseif ($selectedMeterScope === 'main') {
            $selectedSubmeter = null;
        } elseif ($selectedSubmeter && $selectedMainMeter) {
            // Avoid conflicting filters when scope is "all".
            $selectedMainMeter = null;
        }

        $facilityScope = $this->staffFacilityIds($request);

        $facilities = Facility::query()
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('id', $facilityScope))
            ->orderBy('name')
            ->get(['id', 'name']);

        $submeters = Submeter::query()
            ->with('facility:id,name')
            ->whereHas('facility')
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('facility_id', $facilityScope))
            ->when($selectedFacility, fn ($q) => $q->where('facility_id', $selectedFacility))
            ->where('status', 'active')
            ->orderBy('submeter_name')
            ->get(['id', 'facility_id', 'submeter_name', 'status']);

        $mainMeters = FacilityMeter::query()
            ->with('facility:id,name')
            ->whereHas('facility')
            ->when($facilityScope !== null, fn ($q) => $q->whereIn('facility_id', $facilityScope))
            ->when($selectedFacility, fn ($q) => $q->where('facility_id', $selectedFacility))
            ->where('meter_type', 'main')
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->orderBy('meter_name')
            ->get(['id', 'facility_id', 'meter_name', 'meter_number', 'status', 'approved_at']);

        if ($selectedSubmeter && ! $submeters->contains(fn (Submeter $submeter) => (int) $submeter->id === $selectedSubmeter)) {
            $selectedSubmeter = null;
        }
        if ($selectedMainMeter && ! $mainMeters->contains(fn (FacilityMeter $meter) => (int) $meter->id === $selectedMainMeter)) {
            $selectedMainMeter = null;
        }

        $snapshot = $this->loadTrackingService->buildMonthlySnapshot(
            $year,
            $month,
            $selectedFacility,
            $selectedSubmeter,
            $facilityScope,
            $selectedMeterScope === 'all' ? null : $selectedMeterScope,
            $selectedMeterScope === 'all' && $selectedSubmeter ? null : $selectedMainMeter
        );
        $rows = collect($snapshot['rows']);
        if ($selectedConsumptionFilter === 'warning_high') {
            $rows = $rows
                ->filter(fn (array $row) => in_array((string) ($row['consumption_level'] ?? 'normal'), ['warning', 'high'], true))
                ->values();
        }

        $comparisonLabels = $rows->map(function (array $row) {
            return $row['meter_name'] . ' [' . $row['meter_scope_label'] . '] (' . $row['facility_name'] . ')';
        })->all();
        $comparisonEstimated = $rows->pluck('estimated_kwh')->map(fn ($value) => (float) $value)->all();
        $comparisonActual = $rows->pluck('actual_kwh')->map(fn ($value) => (float) $value)->all();
        $totalEstimated = round((float) $rows->sum('estimated_kwh'), 2);
        $totalActual = round((float) $rows->sum('actual_kwh'), 2);
        $totals = [
            'estimated_kwh' => $totalEstimated,
            'actual_kwh' => $totalActual,
            'variance_percent' => $this->loadTrackingService->variancePercent($totalEstimated, $totalActual),
            'flagged_submeters' => $rows->where('consumption_level', 'high')->count(),
        ];

        $equipmentRows = SubmeterEquipment::query()
            ->with([
                'submeter:id,facility_id,submeter_name,status',
                'submeter.facility:id,name',
                'mainMeter:id,facility_id,meter_name,meter_number,meter_type,status,approved_at',
                'mainMeter.facility:id,name',
                'files:id,submeter_equipment_id,original_name,storage_path,file_size,created_at',
            ])
            ->withCount('files')
            ->where(function ($query) use ($facilityScope, $selectedFacility) {
                $query->where(function ($subQuery) use ($facilityScope, $selectedFacility) {
                    $subQuery->where('meter_scope', 'sub')
                        ->whereHas('submeter', function ($meterQuery) use ($facilityScope, $selectedFacility) {
                            $meterQuery->where('status', 'active');
                            if ($facilityScope !== null) {
                                $meterQuery->whereIn('facility_id', $facilityScope);
                            }
                            if ($selectedFacility) {
                                $meterQuery->where('facility_id', $selectedFacility);
                            }
                        });
                })->orWhere(function ($mainQuery) use ($facilityScope, $selectedFacility) {
                    $mainQuery->where('meter_scope', 'main')
                        ->whereHas('mainMeter', function ($meterQuery) use ($facilityScope, $selectedFacility) {
                            $meterQuery->where('meter_type', 'main')
                                ->where('status', 'active')
                                ->whereNotNull('approved_at');
                            if ($facilityScope !== null) {
                                $meterQuery->whereIn('facility_id', $facilityScope);
                            }
                            if ($selectedFacility) {
                                $meterQuery->where('facility_id', $selectedFacility);
                            }
                        });
                });
            });

        if ($selectedMeterScope === 'sub') {
            $equipmentRows->where('meter_scope', 'sub');
            if ($selectedSubmeter) {
                $equipmentRows->where('submeter_id', $selectedSubmeter);
            }
        } elseif ($selectedMeterScope === 'main') {
            $equipmentRows->where('meter_scope', 'main');
            if ($selectedMainMeter) {
                $equipmentRows->where('facility_meter_id', $selectedMainMeter);
            }
        } else {
            if ($selectedSubmeter) {
                $equipmentRows->where('meter_scope', 'sub')->where('submeter_id', $selectedSubmeter);
            } elseif ($selectedMainMeter) {
                $equipmentRows->where('meter_scope', 'main')->where('facility_meter_id', $selectedMainMeter);
            }
        }

        $equipmentRows = $equipmentRows
            ->orderByDesc('estimated_kwh')
            ->orderBy('equipment_name')
            ->paginate(25)
            ->withQueryString();

        return view('modules.load-tracking.index', [
            'selectedMonth' => $safeMonth,
            'selectedFacility' => $selectedFacility,
            'selectedSubmeter' => $selectedSubmeter,
            'selectedMainMeter' => $selectedMainMeter,
            'selectedMeterScope' => $selectedMeterScope,
            'selectedConsumptionFilter' => $selectedConsumptionFilter,
            'facilities' => $facilities,
            'submeters' => $submeters,
            'mainMeters' => $mainMeters,
            'rows' => $rows,
            'topEquipment' => $snapshot['top_equipment'],
            'pieLabels' => $snapshot['pie_labels'],
            'pieValues' => $snapshot['pie_values'],
            'comparisonLabels' => $comparisonLabels,
            'comparisonEstimated' => $comparisonEstimated,
            'comparisonActual' => $comparisonActual,
            'totals' => $totals,
            'equipmentRows' => $equipmentRows,
            'warningThreshold' => LoadTrackingService::VARIANCE_WARNING_PERCENT,
            'varianceThreshold' => LoadTrackingService::VARIANCE_FLAG_PERCENT,
            'canManage' => $this->canManage(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage equipment inventory.');
        }

        $validated = $request->validate([
            'meter_scope' => 'nullable|in:sub,main',
            'submeter_id' => 'nullable|integer|exists:submeters,id',
            'main_meter_id' => 'nullable|integer|exists:facility_meters,id',
            'equipment_name' => 'required|string|max:120',
            'quantity' => 'required|integer|min:1|max:100000',
            'rated_watts' => 'required|numeric|min:0.01|max:99999999.99',
            'operating_hours_per_day' => 'required|numeric|min:0.01|max:24',
            'operating_days_per_month' => 'required|integer|min:1|max:31',
            'return_to' => 'nullable|in:load_tracking,energy_profile,facility_inventory',
            'facility_context_id' => 'nullable|integer|exists:facilities,id',
            'month' => 'nullable|string',
            'facility_id' => 'nullable|integer',
            'meter_scope_filter' => 'nullable|in:all,sub,main',
            'consumption_filter' => 'nullable|in:all,warning_high',
            'submeter_id_filter' => 'nullable|integer',
            'main_meter_id_filter' => 'nullable|integer',
        ]);

        $meterScope = strtolower((string) ($validated['meter_scope'] ?? 'sub'));
        $facilityScope = $this->staffFacilityIds($request);
        $submeter = null;
        $mainMeter = null;

        if ($meterScope === 'main') {
            if (empty($validated['main_meter_id'])) {
                throw ValidationException::withMessages(['main_meter_id' => 'Main Meter is required.']);
            }

            $mainMeter = FacilityMeter::query()
                ->where('id', (int) $validated['main_meter_id'])
                ->where('meter_type', 'main')
                ->where('status', 'active')
                ->whereNotNull('approved_at')
                ->firstOrFail();

            if ($facilityScope !== null && ! in_array((int) $mainMeter->facility_id, $facilityScope, true)) {
                return redirect()->back()->withInput()->with('error', 'You can only manage inventory for your assigned facility.');
            }
        } else {
            if (empty($validated['submeter_id'])) {
                throw ValidationException::withMessages(['submeter_id' => 'Submeter is required.']);
            }

            $submeter = Submeter::query()
                ->where('id', (int) $validated['submeter_id'])
                ->whereHas('facility')
                ->where('status', 'active')
                ->firstOrFail();

            if ($facilityScope !== null && ! in_array((int) $submeter->facility_id, $facilityScope, true)) {
                return redirect()->back()->withInput()->with('error', 'You can only manage inventory for your assigned facility.');
            }
        }

        $duplicateQuery = SubmeterEquipment::query()
            ->where('meter_scope', $meterScope)
            ->whereRaw('LOWER(equipment_name) = ?', [strtolower(trim((string) $validated['equipment_name']))]);

        if ($meterScope === 'main') {
            $duplicateQuery->where('facility_meter_id', $mainMeter->id);
        } else {
            $duplicateQuery->where('submeter_id', $submeter->id);
        }

        if ($duplicateQuery->exists()) {
            $targetLabel = $meterScope === 'main' ? 'selected main meter' : 'selected submeter';
            return redirect()->back()->withInput()->with('error', 'This equipment already exists for the ' . $targetLabel . '.');
        }

        SubmeterEquipment::create([
            'meter_scope' => $meterScope,
            'submeter_id' => $meterScope === 'sub' ? $submeter->id : null,
            'facility_meter_id' => $meterScope === 'main' ? $mainMeter->id : null,
            'equipment_name' => trim((string) $validated['equipment_name']),
            'quantity' => (int) $validated['quantity'],
            'rated_watts' => $validated['rated_watts'],
            'operating_hours_per_day' => $validated['operating_hours_per_day'],
            'operating_days_per_month' => (int) $validated['operating_days_per_month'],
        ]);

        if (($validated['return_to'] ?? null) === 'facility_inventory' && ! empty($validated['facility_context_id'])) {
            $routeParams = [
                'facility' => (int) $validated['facility_context_id'],
                'meter_scope' => $meterScope,
            ];

            if ($meterScope === 'main') {
                $routeParams['main_meter_id'] = (int) $mainMeter->id;
            } else {
                $routeParams['submeter_id'] = (int) $submeter->id;
            }

            return redirect()
                ->route('modules.facilities.equipment-inventory', $routeParams)
                ->with('success', 'Equipment added successfully.');
        }

        if (($validated['return_to'] ?? null) === 'energy_profile' && ! empty($validated['facility_context_id'])) {
            return redirect()
                ->route('modules.facilities.energy-profile.index', (int) $validated['facility_context_id'])
                ->with('success', 'Equipment added to selected meter.');
        }

        return redirect()->route('modules.load-tracking.index', [
            'month' => $validated['month'] ?? now()->format('Y-m'),
            'facility_id' => $validated['facility_id'] ?? null,
            'meter_scope' => $validated['meter_scope_filter'] ?? 'all',
            'consumption_filter' => $validated['consumption_filter'] ?? 'warning_high',
            'submeter_id' => $validated['submeter_id_filter'] ?? null,
            'main_meter_id' => $validated['main_meter_id_filter'] ?? null,
        ])->with('success', 'Equipment added to load tracking inventory.');
    }

    public function update(Request $request, SubmeterEquipment $equipment)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage equipment inventory.');
        }

        $validated = $request->validate([
            'equipment_name' => 'required|string|max:120',
            'quantity' => 'required|integer|min:1|max:100000',
            'rated_watts' => 'required|numeric|min:0.01|max:99999999.99',
            'operating_hours_per_day' => 'required|numeric|min:0.01|max:24',
            'operating_days_per_month' => 'required|integer|min:1|max:31',
            'return_to' => 'nullable|in:load_tracking,energy_profile',
            'facility_context_id' => 'nullable|integer|exists:facilities,id',
            'month' => 'nullable|string',
            'facility_id' => 'nullable|integer',
            'meter_scope_filter' => 'nullable|in:all,sub,main',
            'consumption_filter' => 'nullable|in:all,warning_high',
            'submeter_id_filter' => 'nullable|integer',
            'main_meter_id_filter' => 'nullable|integer',
        ]);

        $facilityScope = $this->staffFacilityIds($request);
        $equipmentScope = strtolower((string) ($equipment->meter_scope ?? 'sub'));
        $facilityId = $equipmentScope === 'main'
            ? (int) ($equipment->mainMeter?->facility_id ?? 0)
            : (int) ($equipment->submeter?->facility_id ?? 0);

        if ($facilityScope !== null && ! in_array($facilityId, $facilityScope, true)) {
            return redirect()->back()->with('error', 'You can only manage inventory for your assigned facility.');
        }

        $duplicateQuery = SubmeterEquipment::query()
            ->where('meter_scope', $equipmentScope)
            ->where('id', '!=', $equipment->id)
            ->whereRaw('LOWER(equipment_name) = ?', [strtolower(trim((string) $validated['equipment_name']))]);

        if ($equipmentScope === 'main') {
            $duplicateQuery->where('facility_meter_id', $equipment->facility_meter_id);
        } else {
            $duplicateQuery->where('submeter_id', $equipment->submeter_id);
        }

        if ($duplicateQuery->exists()) {
            $targetLabel = $equipmentScope === 'main' ? 'selected main meter' : 'selected submeter';
            return redirect()->back()->withInput()->with('error', 'This equipment name is already used in the ' . $targetLabel . '.');
        }

        $equipment->update([
            'equipment_name' => trim((string) $validated['equipment_name']),
            'quantity' => (int) $validated['quantity'],
            'rated_watts' => $validated['rated_watts'],
            'operating_hours_per_day' => $validated['operating_hours_per_day'],
            'operating_days_per_month' => (int) $validated['operating_days_per_month'],
        ]);

        if (($validated['return_to'] ?? null) === 'energy_profile' && ! empty($validated['facility_context_id'])) {
            return redirect()
                ->route('modules.facilities.energy-profile.index', (int) $validated['facility_context_id'])
                ->with('success', 'Equipment inventory updated.');
        }

        return redirect()->route('modules.load-tracking.index', [
            'month' => $validated['month'] ?? now()->format('Y-m'),
            'facility_id' => $validated['facility_id'] ?? null,
            'meter_scope' => $validated['meter_scope_filter'] ?? 'all',
            'consumption_filter' => $validated['consumption_filter'] ?? 'warning_high',
            'submeter_id' => $validated['submeter_id_filter'] ?? null,
            'main_meter_id' => $validated['main_meter_id_filter'] ?? null,
        ])->with('success', 'Equipment inventory updated.');
    }

    public function destroy(Request $request, SubmeterEquipment $equipment)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage equipment inventory.');
        }

        $facilityScope = $this->staffFacilityIds($request);
        $facilityId = $this->resolveEquipmentFacilityId($equipment);

        if ($facilityScope !== null && ! in_array($facilityId, $facilityScope, true)) {
            return redirect()->back()->with('error', 'You can only manage inventory for your assigned facility.');
        }

        $equipment->delete();

        $returnTo = strtolower(trim((string) $request->input('return_to', '')));
        $facilityContextId = (int) $request->input('facility_context_id', 0);

        if ($returnTo === 'energy_profile' && $facilityContextId > 0) {
            return redirect()
                ->route('modules.facilities.energy-profile.index', $facilityContextId)
                ->with('success', 'Equipment removed from inventory.');
        }

        return redirect()->route('modules.load-tracking.index', [
            'month' => $request->query('month', now()->format('Y-m')),
            'facility_id' => $request->query('facility_id'),
            'meter_scope' => $request->query('meter_scope', 'all'),
            'consumption_filter' => $request->query('consumption_filter', 'warning_high'),
            'submeter_id' => $request->query('submeter_id'),
            'main_meter_id' => $request->query('main_meter_id'),
        ])->with('success', 'Equipment removed from inventory.');
    }

    public function uploadFile(Request $request, SubmeterEquipment $equipment): RedirectResponse
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage equipment files.');
        }

        $facilityScope = $this->staffFacilityIds($request);
        $facilityId = $this->resolveEquipmentFacilityId($equipment);
        if ($facilityScope !== null && ! in_array($facilityId, $facilityScope, true)) {
            return redirect()->back()->with('error', 'You can only manage files for your assigned facility.');
        }

        $validated = $request->validate([
            'attachment' => 'required|file|max:20480|mimes:pdf,csv,xlsx,xls,doc,docx,jpg,jpeg,png',
            'month' => 'nullable|string',
            'facility_id' => 'nullable|integer',
            'meter_scope' => 'nullable|in:all,sub,main',
            'consumption_filter' => 'nullable|in:all,warning_high',
            'submeter_id' => 'nullable|integer',
            'main_meter_id' => 'nullable|integer',
        ]);

        $file = $validated['attachment'];
        $scope = strtolower((string) ($equipment->meter_scope ?? 'sub'));
        $folder = $scope === 'main'
            ? 'equipment-files/main-' . (int) ($equipment->facility_meter_id ?? 0)
            : 'equipment-files/sub-' . (int) ($equipment->submeter_id ?? 0);

        $originalName = (string) $file->getClientOriginalName();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $storedName = ($base !== '' ? $base : 'file')
            . '-'
            . now()->format('YmdHis')
            . '-'
            . Str::lower(Str::random(6))
            . ($ext !== '' ? '.' . $ext : '');
        $storagePath = $file->storeAs($folder, $storedName, 'public');

        SubmeterEquipmentFile::create([
            'submeter_equipment_id' => $equipment->id,
            'meter_scope' => $scope === 'main' ? 'main' : 'sub',
            'submeter_id' => $scope === 'sub' ? (int) $equipment->submeter_id : null,
            'facility_meter_id' => $scope === 'main' ? (int) $equipment->facility_meter_id : null,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'storage_path' => (string) $storagePath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => (int) $file->getSize(),
            'uploaded_by' => optional($request->user())->id,
        ]);

        return redirect()->route('modules.load-tracking.index', [
            'month' => $request->input('month', now()->format('Y-m')),
            'facility_id' => $request->input('facility_id'),
            'meter_scope' => $request->input('meter_scope', 'all'),
            'consumption_filter' => $request->input('consumption_filter', 'warning_high'),
            'submeter_id' => $request->input('submeter_id'),
            'main_meter_id' => $request->input('main_meter_id'),
        ])->with('success', 'File uploaded for selected equipment.');
    }

    public function downloadFile(Request $request, SubmeterEquipment $equipment, SubmeterEquipmentFile $file)
    {
        if (! $this->canView()) {
            abort(403);
        }

        if ((int) $file->submeter_equipment_id !== (int) $equipment->id) {
            abort(404);
        }

        $facilityScope = $this->staffFacilityIds($request);
        $facilityId = $this->resolveEquipmentFacilityId($equipment);
        if ($facilityScope !== null && ! in_array($facilityId, $facilityScope, true)) {
            abort(403);
        }

        if (! Storage::disk('public')->exists((string) $file->storage_path)) {
            return redirect()->back()->with('error', 'File is missing from storage.');
        }

        return Storage::disk('public')->download((string) $file->storage_path, (string) $file->original_name);
    }

    public function destroyFile(Request $request, SubmeterEquipment $equipment, SubmeterEquipmentFile $file): RedirectResponse
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage equipment files.');
        }

        if ((int) $file->submeter_equipment_id !== (int) $equipment->id) {
            return redirect()->back()->with('error', 'Invalid equipment file reference.');
        }

        $facilityScope = $this->staffFacilityIds($request);
        $facilityId = $this->resolveEquipmentFacilityId($equipment);
        if ($facilityScope !== null && ! in_array($facilityId, $facilityScope, true)) {
            return redirect()->back()->with('error', 'You can only manage files for your assigned facility.');
        }

        if (Storage::disk('public')->exists((string) $file->storage_path)) {
            Storage::disk('public')->delete((string) $file->storage_path);
        }
        $file->delete();

        return redirect()->route('modules.load-tracking.index', [
            'month' => $request->input('month', now()->format('Y-m')),
            'facility_id' => $request->input('facility_id'),
            'meter_scope' => $request->input('meter_scope', 'all'),
            'consumption_filter' => $request->input('consumption_filter', 'warning_high'),
            'submeter_id' => $request->input('submeter_id'),
            'main_meter_id' => $request->input('main_meter_id'),
        ])->with('success', 'File removed from equipment.');
    }

    private function resolveMonth(mixed $month): array
    {
        try {
            $anchor = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth();
        } catch (\Throwable $e) {
            $anchor = now()->startOfMonth();
        }

        return [(int) $anchor->year, (int) $anchor->month, $anchor->format('Y-m')];
    }

    private function canView(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'view_load_tracking')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
    }

    private function canManage(): bool
    {
        $user = auth()->user();
        return RoleAccess::can($user, 'manage_load_tracking')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff']);
    }

    private function staffFacilityIds(Request $request): ?array
    {
        $user = $request->user();
        if (! $user || ! RoleAccess::is($user, 'staff')) {
            return null;
        }

        return $user->facilities->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function resolveEquipmentFacilityId(SubmeterEquipment $equipment): int
    {
        $scope = strtolower((string) ($equipment->meter_scope ?? 'sub'));

        return $scope === 'main'
            ? (int) ($equipment->mainMeter?->facility_id ?? 0)
            : (int) ($equipment->submeter?->facility_id ?? 0);
    }
}
