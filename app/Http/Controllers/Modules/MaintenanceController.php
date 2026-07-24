<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;
use App\Support\RoleAccess;
use App\Traits\MaintenanceSyncHelpers;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    use MaintenanceSyncHelpers;

    public function destroyHistory($id)
    {
        if (($blocked = $this->ensureMaintenanceHistoryDeleteAccess()) !== null) {
            return $blocked;
        }

        $history = \App\Models\MaintenanceHistory::findOrFail($id);
        $history->delete();
        return redirect()->route('maintenance.history')->with('success', 'History record deleted successfully!');
    }
    public function history()
    {
        $query = \App\Models\MaintenanceHistory::with([
            'facility' => fn ($builder) => $builder->withTrashed()->select('id', 'name'),
        ])->whereIn('issue_type', $this->maintenanceIssueTypes());
        if (request()->filled('facility_id')) {
            $query->where('facility_id', request('facility_id'));
        }
        if (request()->filled('status')) {
            $query->where('maintenance_status', request('status'));
        }
        if (request()->filled('month')) {
            $query->whereMonth('scheduled_date', request('month'));
        }
        if (request()->filled('maintenance_type')) {
            $query->where('maintenance_type', request('maintenance_type'));
        }
        $history = $query->orderByDesc('completed_date')->get();
        $historyRows = [];
        foreach ($history as $row) {
            $resolvedRemarks = $this->resolveMaintenanceRemarks(
                $row->remarks,
                $row->issue_type,
                $row->trend,
                $row->maintenance_status
            );
            $historyRows[] = [
                'id' => $row->id,
                'facility' => $this->resolveFacilityName((int) $row->facility_id, $row->facility?->name),
                'issue_type' => $row->issue_type,
                'trigger_month' => $row->trigger_month,
                'trigger_date' => $this->formatHistoryDate($row->trigger_date ?? $row->created_at),
                'trend' => $row->trend,
                'maintenance_type' => $row->maintenance_type,
                'maintenance_status' => $row->maintenance_status,
                'scheduled_date' => $this->formatHistoryDate($row->scheduled_date),
                'assigned_to' => $row->assigned_to,
                'completed_date' => $this->formatHistoryDate($row->completed_date),
                'remarks' => $resolvedRemarks,
            ];
        }
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        return view('modules.maintenance.history', [
            'historyRows' => $historyRows,
            'role' => $role,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        if (($blocked = $this->ensureMaintenanceActionAccess($request)) !== null) {
            return $blocked;
        }

        if (($blocked = $this->ensureMaintenanceCompletionAccess($request)) !== null) {
            return $blocked;
        }

        if (
            strtolower((string) $request->input('maintenance_status')) === 'completed'
            && !$request->filled('completed_date')
        ) {
            $request->merge(['completed_date' => now()->toDateString()]);
        }

        // If maintenance_id is present, update. Otherwise, insert new.
        $isUpdate = $request->filled('maintenance_id');
        $rules = [
            'maintenance_type' => 'required|string',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|string',
            'remarks' => 'nullable|string',
            'maintenance_status' => 'required|string',
            'completed_date' => 'nullable|date',
        ];
        if ($isUpdate) {
            $rules['maintenance_id'] = 'required|integer|exists:maintenance,id';
            $rules['issue_type'] = ['required', 'string', Rule::in($this->maintenanceIssueTypes())];
        } else {
            $rules['facility_id'] = 'required|integer|exists:facilities,id';
            $rules['issue_type'] = ['required', 'string', Rule::in($this->maintenanceIssueTypes())];
            $rules['trigger_month'] = 'required|string';
        }
        if ($request->maintenance_status === 'Completed') {
            $rules['completed_date'] = 'required|date';
        }
        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }
            throw $e;
        }

        try {
            $previousStatus = null;
            if ($isUpdate) {
                $maintenance = \App\Models\Maintenance::findOrFail($validated['maintenance_id']);
                $previousStatus = $maintenance->maintenance_status;
                $this->applyMaintenanceStatusUpdate($maintenance, $validated);
            } else {
                $remarksInput = trim((string) ($validated['remarks'] ?? ''));
                $maintenance = \App\Models\Maintenance::create([
                    'facility_id' => $validated['facility_id'],
                    'issue_type' => $validated['issue_type'],
                    'trigger_month' => $validated['trigger_month'],
                    'maintenance_type' => $validated['maintenance_type'],
                    'scheduled_date' => $validated['scheduled_date'],
                    'assigned_to' => $validated['assigned_to'],
                    'remarks' => $remarksInput !== ''
                        ? $validated['remarks']
                        : $this->resolveMaintenanceRemarks(
                            null,
                            $validated['issue_type'],
                            null,
                            $validated['maintenance_status']
                        ),
                    'maintenance_status' => $validated['maintenance_status'],
                    'completed_date' => $validated['completed_date'],
                ]);
            }
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            throw $e;
        }

        try {
            $effects = $this->applyMaintenancePostSaveEffects($maintenance, $previousStatus);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to archive completed maintenance.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            throw $e;
        }

        if ($effects['archived']) {
            $archived = $effects['maintenance'];
            // Return the archived record for table update
            return response()->json(['success' => true, 'archived' => true, 'maintenance' => [
                'facility' => $this->resolveFacilityName((int) $archived->facility_id, $archived->facility?->name),
                'trigger_month' => $archived->trigger_month,
                'maintenance_status' => $archived->maintenance_status,
                'scheduled_date' => $archived->scheduled_date,
                'remarks' => $archived->remarks,
            ]]);
        }

        $maintenance = $effects['maintenance'];
        return response()->json(['success' => true, 'maintenance' => [
            'facility' => $this->resolveFacilityName((int) $maintenance->facility_id, $maintenance->facility?->name),
            'issue_type' => $maintenance->issue_type,
            'trigger_month' => $maintenance->trigger_month,
            'maintenance_status' => $maintenance->maintenance_status,
            'scheduled_date' => $maintenance->scheduled_date,
            'remarks' => $maintenance->remarks,
        ]]);
    }

public function index()
{
    $this->archiveCompletedMaintenanceRows();

    $user = auth()->user();
    $role = RoleAccess::normalize($user);
    $facilityIds = ($role === 'staff') ? $user->facilities->pluck('id')->toArray() : null;
    $query = \App\Models\Maintenance::with('facility:id,name,image_path')
        ->whereHas('facility')
        ->whereIn('issue_type', $this->maintenanceIssueTypes());
    if ($facilityIds) {
        $query->whereIn('facility_id', $facilityIds);
    }
    if (request()->filled('facility_id')) {
        $query->where('facility_id', request('facility_id'));
    }
    if (request()->filled('status')) {
        $query->where('maintenance_status', request('status'));
    }
    if (request()->filled('month')) {
        $query->whereMonth('scheduled_date', request('month'));
    }
    if (request()->filled('maintenance_type')) {
        $query->where('maintenance_type', request('maintenance_type'));
    }
    $maintenance = $query->orderByDesc('id')->get();
        $maintenanceRows = [];
    $needingCount = 0;
    $pendingCount = 0;
    $ongoingCount = 0;
    $completedCount = 0;
    $reflaggedCount = 0;

    foreach ($maintenance as $row) {
        // Only count if facility is assigned (for staff)
        if ($facilityIds && !in_array($row->facility_id, $facilityIds)) continue;
        if (in_array($row->maintenance_status, ['Pending','Ongoing'])) $needingCount++;
        if ($row->maintenance_status === 'Pending') $pendingCount++;
        if ($row->maintenance_status === 'Ongoing') $ongoingCount++;
        if ($row->maintenance_status === 'Completed') $completedCount++;
        $resolvedRemarks = $this->resolveMaintenanceRemarks(
            $row->remarks,
            $row->issue_type,
            $row->trend,
            $row->maintenance_status
        );

        $maintenanceRows[] = [
            'id' => $row->id,
            'facility_id' => $row->facility_id,
            'facility' => $row->facility?->name ?? '-',
            'facility_image_url' => $row->facility?->resolved_image_url,
            'issue_type' => $row->issue_type,
            'trigger_month' => $row->trigger_month,
            'trigger_date' => $row->created_at?->format('M d, Y') ?? $row->trigger_month,
            'maintenance_type' => $row->maintenance_type,
            'maintenance_status' => $row->maintenance_status,
            'scheduled_date' => $row->scheduled_date ?? '-',
            'assigned_to' => $row->assigned_to,
            'completed_date' => $row->completed_date,
            'remarks' => $resolvedRemarks,
            'action' => $row->maintenance_status === 'Pending'
                ? '<button class="btn btn-sm" style="background:#2563eb;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;" title="Schedule Maintenance"><i class="fa fa-calendar-plus"></i> Schedule</button>'
                : ($row->maintenance_status === 'Ongoing'
                    ? (
                        $role === 'energy_officer'
                        ? '<button class="btn btn-sm" style="background:#0ea5e9;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;" title="Update Maintenance"><i class="fa fa-pen"></i> Update</button>'
                        : '<button class="btn btn-sm" style="background:#22c55e;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;" title="Mark as Complete"><i class="fa fa-check-circle"></i> Complete</button>'
                    )
                    : '-')
        ];
    }

    // Optional: count re-flagged (facilities with completed + new pending)
    $completed = $maintenance->where('maintenance_status','Completed')->pluck('facility_id')->unique();
    $pending = $maintenance->where('maintenance_status','Pending')->pluck('facility_id')->unique();
    $reflaggedCount = $completed->intersect($pending)->count();

    $user = auth()->user();
    $role = RoleAccess::normalize($user);
    $facilities = ($role === 'staff') ? $user->facilities : Facility::orderBy('name')->get();
    return view('modules.maintenance.index', [
        'maintenanceRows' => $maintenanceRows,
        'needingCount' => $needingCount,
        'pendingCount' => $pendingCount,
        'ongoingCount' => $ongoingCount,
        'completedCount' => $completedCount,
        'reflaggedCount' => $reflaggedCount,
        'role' => $role,
        'user' => $user,
        'facilities' => $facilities,
    ]);
}


private function ensureMaintenanceActionAccess(?Request $request = null)
{
    if (RoleAccess::can(auth()->user(), 'maintenance_actions')) {
        return null;
    }

    $request = $request ?: request();

    if ($request && ($request->expectsJson() || $request->isJson() || $request->wantsJson())) {
        return response()->json([
            'success' => false,
            'message' => 'Staff accounts are not allowed to perform maintenance actions.',
        ], 403);
    }

    return redirect()->back()->with('error', 'Staff accounts are not allowed to perform maintenance actions.');
}

private function archiveCompletedMaintenanceRows(): void
{
    \App\Models\Maintenance::query()
        ->whereRaw('LOWER(TRIM(maintenance_status)) = ?', ['completed'])
        ->orderBy('id')
        ->get()
        ->each(function (\App\Models\Maintenance $maintenance) {
            $maintenance->maintenance_status = 'Completed';
            $maintenance->completed_date ??= $maintenance->updated_at?->toDateString() ?? now()->toDateString();
            $maintenance->save();

            $this->applyMaintenancePostSaveEffects($maintenance, 'Completed');
        });
}

private function ensureMaintenanceCompletionAccess(?Request $request = null)
{
    if (RoleAccess::can(auth()->user(), 'maintenance_complete')) {
        return null;
    }

    $request = $request ?: request();
    $targetStatus = strtolower((string) $request->input('maintenance_status'));
    if ($targetStatus !== 'completed') {
        return null;
    }

    $message = 'You do not have permission to mark maintenance as Completed.';

    if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    return redirect()->back()->with('error', $message);
}

private function ensureMaintenanceHistoryDeleteAccess(?Request $request = null)
{
    if (RoleAccess::can(auth()->user(), 'delete_maintenance_history')) {
        return null;
    }

    $request = $request ?: request();
    $message = 'You do not have permission to delete maintenance history records.';

    if ($request && ($request->expectsJson() || $request->isJson() || $request->wantsJson())) {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    return redirect()->back()->with('error', $message);
}

private function formatHistoryDate(mixed $value): string
{
    if ($value === null || trim((string) $value) === '') {
        return '-';
    }

    try {
        return Carbon::parse($value)->format('M d, Y');
    } catch (\Throwable) {
        return (string) $value;
    }
}

}
