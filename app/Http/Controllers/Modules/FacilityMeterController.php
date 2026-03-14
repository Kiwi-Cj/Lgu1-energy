<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\Submeter;
use App\Models\SubmeterEquipment;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class FacilityMeterController extends Controller
{
    private function redirectAfterMutation(Request $request, Facility $facility, string $message)
    {
        $redirectTo = trim((string) $request->input('_redirect_to', 'energy_profile'));
        if ($redirectTo === 'main_submeters') {
            $mainMeterId = (int) $request->input('main_meter_id');
            if ($mainMeterId > 0) {
                return redirect()
                    ->route('modules.facilities.meters.main-submeters', [$facility->id, $mainMeterId])
                    ->with('success', $message);
            }
        }

        $routeName = match ($redirectTo) {
            'meters_index' => 'modules.facilities.meters.index',
            'meters_unapproved' => 'modules.facilities.meters.unapproved',
            default => 'modules.facilities.energy-profile.index',
        };

        return redirect()->route($routeName, $facility->id)->with('success', $message);
    }

    private function canManage(): bool
    {
        return RoleAccess::can(auth()->user(), 'manage_facility_master');
    }

    private function canForceDelete(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin']);
    }

    private function canApprove(): bool
    {
        return RoleAccess::can(auth()->user(), 'approve_facility_meters');
    }

    private function canViewUnapproved(): bool
    {
        return $this->canApprove() || $this->canManage();
    }

    private function canManageEquipment(): bool
    {
        return $this->canManage() || RoleAccess::can(auth()->user(), 'manage_load_tracking');
    }

    private function validateMeter(Request $request, int $facilityId, ?int $meterId = null): array
    {
        $validated = $request->validate([
            'meter_name' => 'required|string|max:255',
            'meter_number' => 'nullable|string|max:255',
            'meter_type' => 'required|in:main,sub',
            'parent_meter_id' => 'nullable|integer',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'multiplier' => 'nullable|numeric|min:0.0001|max:999999',
            'baseline_kwh' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['multiplier'] = isset($validated['multiplier']) && $validated['multiplier'] !== null && $validated['multiplier'] !== ''
            ? (float) $validated['multiplier']
            : 1.0;
        $validated['baseline_kwh'] = isset($validated['baseline_kwh']) && $validated['baseline_kwh'] !== null && $validated['baseline_kwh'] !== ''
            ? (float) $validated['baseline_kwh']
            : null;

        $requestedMeterType = $validated['meter_type'] ?? 'sub';
        $currentMeterType = null;
        if ($meterId !== null) {
            $currentMeterType = (string) (FacilityMeter::query()
                ->where('facility_id', $facilityId)
                ->where('id', $meterId)
                ->value('meter_type') ?? '');
        }

        if ($requestedMeterType === 'sub' && ($meterId === null || $currentMeterType !== 'sub')) {
            $approvedMainMeterQuery = FacilityMeter::query()
                ->where('facility_id', $facilityId)
                ->where('meter_type', 'main')
                ->whereNotNull('approved_at');

            if ($meterId !== null) {
                $approvedMainMeterQuery->where('id', '!=', $meterId);
            }

            if (! $approvedMainMeterQuery->exists()) {
                abort(422, 'Cannot create or set a sub-meter without an approved main meter.');
            }
        }

        if ($requestedMeterType === 'main') {
            $validated['parent_meter_id'] = null;
        } else {
            $parentId = (int) ($validated['parent_meter_id'] ?? 0);
            if ($parentId <= 0) {
                abort(422, 'Sub-meter must be linked to an approved main meter.');
            }

            if ($meterId !== null && $parentId === $meterId) {
                abort(422, 'A meter cannot be its own parent.');
            }

            $parentExists = FacilityMeter::where('facility_id', $facilityId)
                ->where('id', $parentId)
                ->where('meter_type', 'main')
                ->whereNotNull('approved_at')
                ->exists();

            if (! $parentExists) {
                abort(422, 'Selected linked main meter is not approved or does not belong to this facility.');
            }

            $validated['parent_meter_id'] = $parentId;
        }

        return $validated;
    }

    public function index(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        return redirect()->route('modules.facilities.energy-profile.index', $facility->id);
    }

    public function mainSubmeters($facilityId, $meterId)
    {
        $facility = Facility::findOrFail($facilityId);
        $mainMeter = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'main')
            ->whereKey($meterId)
            ->firstOrFail();

        $subMeters = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'sub')
            ->where('parent_meter_id', $mainMeter->id)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->get();

        $linkedSubCount = $subMeters->count();
        $activeLinkedSubCount = $subMeters->where('status', 'active')->count();
        $approvedLinkedSubCount = $subMeters->whereNotNull('approved_at')->count();
        $archivedSubCount = FacilityMeter::onlyTrashed()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'sub')
            ->count();

        return view('modules.facilities.meters.submeters-by-main', [
            'facility' => $facility,
            'mainMeter' => $mainMeter,
            'subMeters' => $subMeters,
            'linkedSubCount' => $linkedSubCount,
            'activeLinkedSubCount' => $activeLinkedSubCount,
            'approvedLinkedSubCount' => $approvedLinkedSubCount,
            'archivedSubCount' => $archivedSubCount,
            'canManageMeters' => $this->canManage(),
            'canApproveMeters' => $this->canApprove(),
        ]);
    }

    public function submeterEquipment($facilityId, $meterId)
    {
        $facility = Facility::findOrFail($facilityId);
        $subMeter = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'sub')
            ->whereKey($meterId)
            ->firstOrFail();

        $mainMeter = null;
        if (! empty($subMeter->parent_meter_id)) {
            $mainMeter = FacilityMeter::query()
                ->where('facility_id', $facility->id)
                ->where('meter_type', 'main')
                ->whereKey((int) $subMeter->parent_meter_id)
                ->first();
        }

        $submeterEntity = Submeter::query()
            ->where('facility_id', $facility->id)
            ->whereRaw('LOWER(TRIM(submeter_name)) = ?', [strtolower(trim((string) $subMeter->meter_name))])
            ->first();

        $equipmentRows = collect();
        if ($submeterEntity) {
            $equipmentRows = SubmeterEquipment::query()
                ->where('meter_scope', 'sub')
                ->where('submeter_id', (int) $submeterEntity->id)
                ->orderByDesc('estimated_kwh')
                ->orderBy('equipment_name')
                ->get();
        }

        $totalWatts = (float) $equipmentRows->sum(function ($equipment) {
            $quantity = (int) ($equipment->quantity ?? 0);
            $ratedWatts = (float) ($equipment->rated_watts ?? 0);
            return $quantity * $ratedWatts;
        });
        $totalEstimatedKwh = (float) $equipmentRows->sum(fn ($equipment) => (float) ($equipment->estimated_kwh ?? 0));

        return view('modules.facilities.meters.equipment-by-submeter', [
            'facility' => $facility,
            'subMeter' => $subMeter,
            'mainMeter' => $mainMeter,
            'submeterEntity' => $submeterEntity,
            'equipmentRows' => $equipmentRows,
            'equipmentCount' => $equipmentRows->count(),
            'totalWatts' => $totalWatts,
            'totalEstimatedKwh' => $totalEstimatedKwh,
            'canManageEquipment' => $this->canManageEquipment(),
        ]);
    }

    public function storeSubmeterEquipment(Request $request, $facilityId, $meterId)
    {
        if (! $this->canManageEquipment()) {
            return redirect()->back()->with('error', 'You do not have permission to manage equipment inventory.');
        }

        $facility = Facility::findOrFail($facilityId);
        $subMeter = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'sub')
            ->whereKey($meterId)
            ->firstOrFail();

        $submeterEntity = Submeter::query()
            ->where('facility_id', $facility->id)
            ->whereRaw('LOWER(TRIM(submeter_name)) = ?', [strtolower(trim((string) $subMeter->meter_name))])
            ->first();

        if (! $submeterEntity) {
            return redirect()
                ->route('modules.facilities.meters.submeter-equipment', [$facility->id, $subMeter->id])
                ->with('error', 'No linked submeters record found for this sub-meter.');
        }

        $validated = $request->validate([
            'equipment_name' => 'required|string|max:120',
            'quantity' => 'required|integer|min:1|max:100000',
            'rated_watts' => 'required|numeric|min:0.01|max:99999999.99',
            'operating_hours_per_day' => 'required|numeric|min:0.01|max:24',
            'operating_days_per_month' => 'required|integer|min:1|max:31',
        ]);

        $equipmentName = trim((string) $validated['equipment_name']);
        $duplicateExists = SubmeterEquipment::query()
            ->where('meter_scope', 'sub')
            ->where('submeter_id', (int) $submeterEntity->id)
            ->whereRaw('LOWER(equipment_name) = ?', [strtolower($equipmentName)])
            ->exists();

        if ($duplicateExists) {
            return redirect()
                ->route('modules.facilities.meters.submeter-equipment', [$facility->id, $subMeter->id])
                ->withInput()
                ->with('error', 'This equipment already exists for the selected sub-meter.');
        }

        SubmeterEquipment::create([
            'meter_scope' => 'sub',
            'submeter_id' => (int) $submeterEntity->id,
            'facility_meter_id' => null,
            'equipment_name' => $equipmentName,
            'quantity' => (int) $validated['quantity'],
            'rated_watts' => $validated['rated_watts'],
            'operating_hours_per_day' => $validated['operating_hours_per_day'],
            'operating_days_per_month' => (int) $validated['operating_days_per_month'],
        ]);

        return redirect()
            ->route('modules.facilities.meters.submeter-equipment', [$facility->id, $subMeter->id])
            ->with('success', 'Equipment added successfully.');
    }

    public function store(Request $request, $facilityId)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $validated = $this->validateMeter($request, (int) $facility->id);
        $validated['facility_id'] = $facility->id;

        if ($this->canApprove()) {
            $validated['approved_by_user_id'] = auth()->id();
            $validated['approved_at'] = now();
        }

        FacilityMeter::create($validated);

        return $this->redirectAfterMutation($request, $facility, 'Meter added successfully.');
    }

    public function update(Request $request, $facilityId, $meterId)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $meter = FacilityMeter::where('facility_id', $facility->id)->findOrFail($meterId);

        $validated = $this->validateMeter($request, (int) $facility->id, (int) $meter->id);
        $meter->update($validated);

        return $this->redirectAfterMutation($request, $facility, 'Meter updated successfully.');
    }

    public function destroy(Request $request, $facilityId, $meterId)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage meters.');
        }

        $archiveReason = trim((string) $request->input('archive_reason', ''));
        if ($archiveReason === '') {
            return redirect()->back()->with('error', 'Archive reason is required.');
        }

        $facility = Facility::findOrFail($facilityId);
        $meter = FacilityMeter::where('facility_id', $facility->id)->findOrFail($meterId);

        $meter->deleted_by = auth()->id();
        $meter->archive_reason = $archiveReason;
        $meter->saveQuietly();
        $meter->delete();

        return $this->redirectAfterMutation($request, $facility, 'Meter moved to archive.');
    }

    public function toggleApproval(Request $request, $facilityId, $meterId)
    {
        if (! $this->canApprove()) {
            return redirect()->back()->with('error', 'Only super admin, admin, or engineer can approve meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $meter = FacilityMeter::where('facility_id', $facility->id)->findOrFail($meterId);

        if ($meter->approved_at) {
            $meter->approved_by_user_id = null;
            $meter->approved_at = null;
            $message = 'Meter marked as not approved.';
        } else {
            $meter->approved_by_user_id = auth()->id();
            $meter->approved_at = now();
            $message = 'Meter approved successfully.';
        }

        $meter->save();

        return $this->redirectAfterMutation($request, $facility, $message);
    }

    public function archive(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        $subOnlyMode = (string) $request->query('sub_only', '') === '1';
        $mainMeterId = (int) $request->query('main_meter_id', 0);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'meter_type' => trim((string) $request->query('meter_type', '')),
        ];

        if ($subOnlyMode) {
            $filters['meter_type'] = 'sub';
        }

        $query = FacilityMeter::onlyTrashed()
            ->with(['parentMeter', 'deletedByUser'])
            ->where('facility_id', $facility->id);

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($builder) use ($q) {
                $builder->where('meter_name', 'like', "%{$q}%")
                    ->orWhere('meter_number', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('archive_reason', 'like', "%{$q}%");
            });
        }

        if ($filters['meter_type'] !== '') {
            $query->where('meter_type', $filters['meter_type']);
        }

        $archivedMeters = $query->orderByDesc('deleted_at')->paginate(12)->withQueryString();

        return view('modules.facilities.meters.archive', [
            'facility' => $facility,
            'archivedMeters' => $archivedMeters,
            'filters' => $filters,
            'subOnlyMode' => $subOnlyMode,
            'mainMeterId' => $mainMeterId,
            'canManageMeters' => $this->canManage(),
            'canForceDeleteMeters' => $this->canForceDelete(),
        ]);
    }

    public function unapproved(Request $request, $facilityId)
    {
        if (! $this->canViewUnapproved()) {
            return redirect()->back()->with('error', 'You do not have permission to view unapproved meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
        ];
        $hasMainMeter = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'main')
            ->exists();

        $buildQuery = function (string $meterType) use ($facility, $filters) {
            $query = FacilityMeter::query()
                ->with('parentMeter')
                ->where('facility_id', $facility->id)
                ->whereNull('approved_at')
                ->where('meter_type', $meterType);

            if ($filters['q'] !== '') {
                $q = $filters['q'];
                $query->where(function ($builder) use ($q) {
                    $builder->where('meter_name', 'like', "%{$q}%")
                        ->orWhere('meter_number', 'like', "%{$q}%")
                        ->orWhere('location', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%");
                });
            }

            if ($filters['status'] !== '') {
                $query->where('status', $filters['status']);
            }

            return $query
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('meter_name');
        };

        $unapprovedMainMeters = $buildQuery('main')->get();
        $unapprovedSubMeters = $hasMainMeter ? $buildQuery('sub')->get() : collect();

        return view('modules.facilities.meters.unapproved', [
            'facility' => $facility,
            'unapprovedMainMeters' => $unapprovedMainMeters,
            'unapprovedSubMeters' => $unapprovedSubMeters,
            'hasMainMeter' => $hasMainMeter,
            'filters' => $filters,
            'canApproveMeters' => $this->canApprove(),
        ]);
    }

    public function restore($facilityId, $meterId)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $meter = FacilityMeter::onlyTrashed()
            ->where('facility_id', $facility->id)
            ->findOrFail($meterId);

        $meter->restore();

        return redirect()->route('modules.facilities.meters.archive', $facility->id)
            ->with('success', 'Meter restored successfully.');
    }

    public function forceDelete($facilityId, $meterId)
    {
        if (! $this->canForceDelete()) {
            return redirect()->back()->with('error', 'Only admins can permanently delete archived meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $meter = FacilityMeter::onlyTrashed()
            ->where('facility_id', $facility->id)
            ->findOrFail($meterId);

        $meter->forceDelete();

        return redirect()->route('modules.facilities.meters.archive', $facility->id)
            ->with('success', 'Meter permanently deleted.');
    }
}
