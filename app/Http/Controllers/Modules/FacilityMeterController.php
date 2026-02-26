<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class FacilityMeterController extends Controller
{
    private function canManage(): bool
    {
        return RoleAccess::can(auth()->user(), 'manage_facility_master');
    }

    private function canForceDelete(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin']);
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

        if (($validated['meter_type'] ?? 'sub') === 'main') {
            $validated['parent_meter_id'] = null;
        } elseif (! empty($validated['parent_meter_id'])) {
            $parentId = (int) $validated['parent_meter_id'];
            if ($meterId !== null && $parentId === $meterId) {
                abort(422, 'A meter cannot be its own parent.');
            }

            $parentExists = FacilityMeter::where('facility_id', $facilityId)
                ->where('id', $parentId)
                ->exists();

            if (! $parentExists) {
                abort(422, 'Selected parent meter does not belong to this facility.');
            }
        } else {
            $validated['parent_meter_id'] = null;
        }

        return $validated;
    }

    public function index(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'meter_type' => trim((string) $request->query('meter_type', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $query = FacilityMeter::with(['parentMeter'])
            ->where('facility_id', $facility->id);

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($builder) use ($q) {
                $builder->where('meter_name', 'like', "%{$q}%")
                    ->orWhere('meter_number', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });
        }

        if ($filters['meter_type'] !== '') {
            $query->where('meter_type', $filters['meter_type']);
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $meters = $query
            ->orderByRaw("CASE WHEN meter_type = 'main' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->paginate(12)
            ->withQueryString();

        $parentMeterOptions = FacilityMeter::where('facility_id', $facility->id)
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN meter_type = 'main' THEN 0 ELSE 1 END")
            ->orderBy('meter_name')
            ->get(['id', 'meter_name', 'meter_type']);

        $countsBase = FacilityMeter::where('facility_id', $facility->id);
        $activeCount = (clone $countsBase)->count();
        $mainCount = (clone $countsBase)->where('meter_type', 'main')->count();
        $subCount = (clone $countsBase)->where('meter_type', 'sub')->count();
        $archivedCount = FacilityMeter::onlyTrashed()->where('facility_id', $facility->id)->count();

        return view('modules.facilities.meters.index', [
            'facility' => $facility,
            'meters' => $meters,
            'filters' => $filters,
            'parentMeterOptions' => $parentMeterOptions,
            'activeCount' => $activeCount,
            'mainCount' => $mainCount,
            'subCount' => $subCount,
            'archivedCount' => $archivedCount,
            'canManageMeters' => $this->canManage(),
        ]);
    }

    public function store(Request $request, $facilityId)
    {
        if (! $this->canManage()) {
            return redirect()->back()->with('error', 'You do not have permission to manage meters.');
        }

        $facility = Facility::findOrFail($facilityId);
        $validated = $this->validateMeter($request, (int) $facility->id);
        $validated['facility_id'] = $facility->id;

        FacilityMeter::create($validated);

        return redirect()->route('modules.facilities.meters.index', $facility->id)
            ->with('success', 'Meter added successfully.');
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

        return redirect()->route('modules.facilities.meters.index', $facility->id)
            ->with('success', 'Meter updated successfully.');
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

        return redirect()->route('modules.facilities.meters.index', $facility->id)
            ->with('success', 'Meter moved to archive.');
    }

    public function archive(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'meter_type' => trim((string) $request->query('meter_type', '')),
        ];

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
            'canManageMeters' => $this->canManage(),
            'canForceDeleteMeters' => $this->canForceDelete(),
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
