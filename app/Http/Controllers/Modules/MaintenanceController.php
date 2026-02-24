<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
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
        $query = \App\Models\MaintenanceHistory::with('facility');
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
                'facility' => $row->facility ? $row->facility->name : '-',
                'issue_type' => $row->issue_type,
                'trigger_month' => $row->trigger_month,
                'trend' => $row->trend,
                'maintenance_type' => $row->maintenance_type,
                'maintenance_status' => $row->maintenance_status,
                'scheduled_date' => $row->scheduled_date ?? '-',
                'assigned_to' => $row->assigned_to,
                'completed_date' => $row->completed_date,
                'remarks' => $resolvedRemarks,
            ];
        }
        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.maintenance.history', [
            'historyRows' => $historyRows,
            'role' => $role,
            'user' => $user,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadNotifCount,
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
            $rules['issue_type'] = 'nullable|string|max:255';
        } else {
            $rules['facility_id'] = 'required|integer|exists:facilities,id';
            $rules['issue_type'] = 'required|string';
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
                $remarksInput = trim((string) ($validated['remarks'] ?? ''));
                $newStatus = trim((string) ($validated['maintenance_status'] ?? ''));
                $newIssueType = trim((string) ($validated['issue_type'] ?? ''));
                $maintenance->maintenance_type = $validated['maintenance_type'];
                $maintenance->scheduled_date = $validated['scheduled_date'];
                $maintenance->assigned_to = $validated['assigned_to'];
                if ($newStatus !== 'Completed' && $newIssueType !== '') {
                    $maintenance->issue_type = $newIssueType;
                }
                $maintenance->remarks = $remarksInput !== ''
                    ? $validated['remarks']
                    : $this->resolveMaintenanceRemarks(
                        null,
                        $maintenance->issue_type,
                        $maintenance->trend,
                        $validated['maintenance_status']
                    );
                $maintenance->maintenance_status = $validated['maintenance_status'];
                $maintenance->completed_date = $validated['completed_date'];
                $maintenance->save();
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

        $this->notifyMaintenanceStatusTransition($maintenance, $previousStatus ?? null);
        $this->syncFacilityStatusFromMaintenance($maintenance, $previousStatus ?? null);

        if (in_array($maintenance->maintenance_status, ['Ongoing', 'Completed'], true)) {
            $this->syncIncidentStatusFromMaintenance($maintenance);
        }

        // If marked as Completed, move to history and delete from active.
        if ($maintenance->maintenance_status === 'Completed') {
            $archived = null;
            try {
                DB::transaction(function () use (&$archived, $maintenance) {
                    $resolvedTrend = trim((string) $maintenance->trend) !== '' ? $maintenance->trend : 'Stable';
                    $archived = \App\Models\MaintenanceHistory::create([
                        'facility_id' => $maintenance->facility_id,
                        'issue_type' => $maintenance->issue_type,
                        'trigger_month' => $maintenance->trigger_month,
                        'trend' => $resolvedTrend,
                        'efficiency_rating' => $this->resolveEfficiencyRating(
                            $maintenance->issue_type,
                            $maintenance->maintenance_type,
                            $resolvedTrend
                        ),
                        'maintenance_type' => $maintenance->maintenance_type,
                        'maintenance_status' => $maintenance->maintenance_status,
                        'scheduled_date' => $maintenance->scheduled_date,
                        'assigned_to' => $maintenance->assigned_to,
                        'completed_date' => $maintenance->completed_date,
                        'remarks' => $maintenance->remarks,
                    ]);
                    $maintenance->delete();
                });
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
            // Return the archived record for table update
            return response()->json(['success' => true, 'archived' => true, 'maintenance' => [
                'facility' => $archived->facility ? $archived->facility->name : '-',
                'trigger_month' => $archived->trigger_month,
                'maintenance_status' => $archived->maintenance_status,
                'scheduled_date' => $archived->scheduled_date,
                'remarks' => $archived->remarks,
            ]]);
        }

        return response()->json(['success' => true, 'maintenance' => [
            'facility' => $maintenance->facility ? $maintenance->facility->name : '-',
            'issue_type' => $maintenance->issue_type,
            'trigger_month' => $maintenance->trigger_month,
            'maintenance_status' => $maintenance->maintenance_status,
            'scheduled_date' => $maintenance->scheduled_date,
            'remarks' => $maintenance->remarks,
        ]]);
    }

public function index()
{
    $user = auth()->user();
    $role = RoleAccess::normalize($user);
    $facilityIds = ($role === 'staff') ? $user->facilities->pluck('id')->toArray() : null;
    $query = \App\Models\Maintenance::with('facility');
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
    $maintenance = $query->get();
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
            'facility' => $row->facility ? $row->facility->name : '-',
            'issue_type' => $row->issue_type,
            'trigger_month' => $row->trigger_month,
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
    // Filter notifications for staff: only those related to assigned facilities
    if ($role === 'staff') {
        // Only filter by user, not by facility_id (column does not exist in notifications)
        $notifications = $user->notifications()->orderByDesc('created_at')->take(10)->get();
        $unreadNotifCount = $user->notifications()->whereNull('read_at')->count();
    } else {
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
    }
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
        'notifications' => $notifications,
        'unreadNotifCount' => $unreadNotifCount,
    ]);
}

private function parseTriggerMonth(?string $triggerMonth): array
{
    $raw = trim((string) $triggerMonth);
    if ($raw === '') {
        return [null, null];
    }

    foreach (['F Y', 'M Y'] as $format) {
        try {
            $date = Carbon::createFromFormat($format, $raw);
            if ($date instanceof Carbon) {
                return [(int) $date->month, (int) $date->year];
            }
        } catch (\Throwable $e) {
            // Keep trying the next format.
        }
    }

    return [null, null];
}

private function resolveMaintenanceRemarks(?string $remarks, ?string $issueType, ?string $trend, ?string $status): string
{
    $normalizedRemarks = trim((string) $remarks);
    $legacyRemarks = [
        '',
        '-',
        'Auto-flagged due to system-detected high energy consumption (incident auto-created).',
    ];
    if (!in_array($normalizedRemarks, $legacyRemarks, true)) {
        return $normalizedRemarks;
    }

    $issueText = strtolower((string) $issueType);
    $statusText = strtolower((string) $status);
    $trendText = strtolower((string) $trend);

    $severityKey = str_contains($issueText, 'critical')
        ? 'critical'
        : (str_contains($issueText, 'very high') ? 'very-high' : 'high');
    $statusKey = str_contains($statusText, 'completed')
        ? 'completed'
        : (str_contains($statusText, 'ongoing') ? 'ongoing' : 'pending');

    $base = match ($severityKey . ':' . $statusKey) {
        'critical:completed' => 'Critical maintenance action completed. Validate consumption stabilization for the next billing cycles.',
        'critical:ongoing' => 'Critical maintenance action is in progress. Keep temporary controls active while root-cause checks continue.',
        'critical:pending' => 'Critical consumption anomaly queued for urgent corrective maintenance and immediate technical inspection.',
        'very-high:completed' => 'Very high consumption issue completed. Continue scheduled checks to confirm sustained improvement.',
        'very-high:ongoing' => 'Very high consumption issue under corrective maintenance. Continue close monitoring during this period.',
        'very-high:pending' => 'Very high consumption anomaly queued for corrective maintenance and operating schedule validation.',
        'high:completed' => 'Maintenance task completed. Keep regular monitoring to prevent repeat deviation.',
        'high:ongoing' => 'Maintenance task is ongoing. Continue monitoring and equipment checks.',
        default => 'Maintenance task queued for review and corrective action.',
    };

    if (str_contains($trendText, 'increasing')) {
        return $base . ' Trend is increasing; prioritize root-cause analysis.';
    }

    return $base;
}

private function resolveEfficiencyRating(?string $issueType, ?string $maintenanceType, ?string $trend): string
{
    $issue = strtolower((string) $issueType);
    $type = strtolower((string) $maintenanceType);
    $trendText = strtolower((string) $trend);

    if (
        str_contains($issue, 'critical')
        || str_contains($issue, 'circuit overload')
        || str_contains($issue, 'power outage')
    ) {
        return 'Low';
    }

    if (
        str_contains($issue, 'very high')
        || str_contains($issue, 'high consumption')
        || str_contains($trendText, 'increasing')
        || str_contains($type, 'corrective')
    ) {
        return 'Medium';
    }

    return 'High';
}

private function syncIncidentStatusFromMaintenance(\App\Models\Maintenance $maintenance): void
{
    $statusText = strtolower((string) $maintenance->maintenance_status);
    if (!in_array($statusText, ['ongoing', 'completed'], true)) {
        return;
    }

    [$triggerMonthNum, $triggerYearNum] = $this->parseTriggerMonth($maintenance->trigger_month);
    if ($triggerMonthNum === null || $triggerYearNum === null) {
        return;
    }

    $baseQuery = \App\Models\EnergyIncident::query()
        ->where('facility_id', $maintenance->facility_id)
        ->where('month', $triggerMonthNum)
        ->where('year', $triggerYearNum);

    if ($statusText === 'ongoing') {
        $incident = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('status', 'like', '%pending%')
                    ->orWhere('status', 'like', '%open%')
                    ->orWhere('status', 'like', '%ongoing%');
            })
            ->orderByDesc('id')
            ->first();

        if (!$incident) {
            $incident = (clone $baseQuery)->orderByDesc('id')->first();
        }

        if ($incident) {
            $incident->status = 'Ongoing';
            $incident->resolved_at = null;
            $incident->save();
        }
        return;
    }

    $incident = (clone $baseQuery)
        ->where(function ($query) {
            $query->where('status', 'not like', '%resolved%')
                ->where('status', 'not like', '%closed%');
        })
        ->orderByDesc('id')
        ->first();

    if (!$incident) {
        $incident = (clone $baseQuery)->orderByDesc('id')->first();
    }

    if ($incident) {
        $incident->status = 'Resolved';
        $incident->resolved_at = $maintenance->completed_date
            ? Carbon::parse($maintenance->completed_date)
            : now();
        $incident->save();
    }
}

private function syncFacilityStatusFromMaintenance(\App\Models\Maintenance $maintenance, ?string $previousStatus = null): void
{
    $newStatus = strtolower(trim((string) ($maintenance->maintenance_status ?? '')));
    $oldStatus = strtolower(trim((string) ($previousStatus ?? '')));

    if ($newStatus === $oldStatus) {
        return;
    }

    if (!$maintenance->facility_id) {
        return;
    }

    $facility = Facility::find($maintenance->facility_id);
    if (!$facility) {
        return;
    }

    if ($newStatus === 'ongoing') {
        if (strtolower(trim((string) ($facility->status ?? ''))) !== 'maintenance') {
            $facility->status = 'maintenance';
            $facility->save();
        }
        return;
    }

    if ($newStatus !== 'completed') {
        return;
    }

    $hasOtherOngoing = \App\Models\Maintenance::query()
        ->where('facility_id', $maintenance->facility_id)
        ->where('maintenance_status', 'Ongoing')
        ->exists();

    if ($hasOtherOngoing) {
        return;
    }

    // Only auto-revert when the facility is currently in maintenance status.
    if (strtolower(trim((string) ($facility->status ?? ''))) === 'maintenance') {
        $facility->status = 'active';
        $facility->save();
    }
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

private function notifyMaintenanceStatusTransition(\App\Models\Maintenance $maintenance, ?string $previousStatus = null): void
{
    $newStatus = strtolower(trim((string) ($maintenance->maintenance_status ?? '')));
    $oldStatus = strtolower(trim((string) ($previousStatus ?? '')));

    if (!in_array($newStatus, ['ongoing', 'completed'], true)) {
        return;
    }

    if ($newStatus === $oldStatus) {
        return;
    }

    $maintenance->loadMissing('facility:id,name');

    $facilityName = trim((string) ($maintenance->facility?->name ?? 'Unknown Facility'));
    $period = trim((string) ($maintenance->trigger_month ?? 'Unknown Period'));
    $statusLabel = $newStatus === 'ongoing' ? 'Ongoing' : 'Completed';
    $title = $newStatus === 'ongoing' ? 'Maintenance In Progress' : 'Maintenance Completed';
    $message = "Maintenance: {$facilityName} ({$period}) status updated to {$statusLabel}.";

    User::query()
        ->with('facilities:id')
        ->get()
        ->filter(function (User $user) use ($maintenance) {
            $role = RoleAccess::normalize($user);

            if (in_array($role, ['super_admin', 'admin', 'energy_officer'], true)) {
                return true;
            }

            if ($role === 'staff' && $maintenance->facility_id) {
                return $user->facilities->contains('id', (int) $maintenance->facility_id);
            }

            return false;
        })
        ->each(function (User $user) use ($title, $message) {
            $exists = $user->notifications()
                ->where('type', 'maintenance')
                ->where('message', $message)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($exists) {
                return;
            }

            $user->notifications()->create([
                'title' => $title,
                'message' => $message,
                'type' => 'maintenance',
            ]);
        });
}
}
