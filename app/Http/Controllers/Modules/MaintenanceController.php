<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;

class MaintenanceController extends Controller
{
    public function destroyHistory($id)
    {
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
            $historyRows[] = [
                'id' => $row->id,
                'facility' => $row->facility ? $row->facility->name : '-',
                'issue_type' => $row->issue_type,
                'trigger_month' => $row->trigger_month,
                'efficiency_rating' => $row->efficiency_rating,
                'trend' => $row->trend,
                'maintenance_type' => $row->maintenance_type,
                'maintenance_status' => $row->maintenance_status,
                'scheduled_date' => $row->scheduled_date ?? '-',
                'assigned_to' => $row->assigned_to,
                'completed_date' => $row->completed_date,
                'remarks' => $row->remarks ?? '-',
            ];
        }
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
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
        } else {
            $rules['facility_id'] = 'required|integer|exists:facilities,id';
            $rules['issue_type'] = 'required|string';
            $rules['trigger_month'] = 'required|string';
        }
        if ($request->maintenance_status === 'Completed') {
            $rules['completed_date'] = 'required|date';
        }
        $validated = $request->validate($rules);

        if ($isUpdate) {
            $maintenance = \App\Models\Maintenance::findOrFail($validated['maintenance_id']);
            $maintenance->maintenance_type = $validated['maintenance_type'];
            $maintenance->scheduled_date = $validated['scheduled_date'];
            $maintenance->assigned_to = $validated['assigned_to'];
            $maintenance->remarks = $validated['remarks'];
            $maintenance->maintenance_status = $validated['maintenance_status'];
            $maintenance->completed_date = $validated['completed_date'];
            $maintenance->save();
        } else {
            $maintenance = \App\Models\Maintenance::create([
                'facility_id' => $validated['facility_id'],
                'issue_type' => $validated['issue_type'],
                'trigger_month' => $validated['trigger_month'],
                'maintenance_type' => $validated['maintenance_type'],
                'scheduled_date' => $validated['scheduled_date'],
                'assigned_to' => $validated['assigned_to'],
                'remarks' => $validated['remarks'],
                'maintenance_status' => $validated['maintenance_status'],
                'completed_date' => $validated['completed_date'],
            ]);
        }

        // If marked as Completed, move to history and delete from active
        if ($maintenance->maintenance_status === 'Completed') {
            $archived = \App\Models\MaintenanceHistory::create([
                'facility_id' => $maintenance->facility_id,
                'issue_type' => $maintenance->issue_type,
                'trigger_month' => $maintenance->trigger_month,
                'efficiency_rating' => $maintenance->efficiency_rating,
                'trend' => $maintenance->trend,
                'maintenance_type' => $maintenance->maintenance_type,
                'maintenance_status' => $maintenance->maintenance_status,
                'scheduled_date' => $maintenance->scheduled_date,
                'assigned_to' => $maintenance->assigned_to,
                'completed_date' => $maintenance->completed_date,
                'remarks' => $maintenance->remarks,
            ]);
            $maintenance->delete();
            // Return the archived record for table update
            return response()->json(['success' => true, 'archived' => true, 'maintenance' => [
                'facility' => $archived->facility ? $archived->facility->name : '-',
                'issue_type' => $archived->issue_type,
                'trigger_month' => $archived->trigger_month,
                'efficiency_rating' => $archived->efficiency_rating,
                'maintenance_status' => $archived->maintenance_status,
                'scheduled_date' => $archived->scheduled_date,
                'remarks' => $archived->remarks,
            ]]);
        }

        return response()->json(['success' => true, 'maintenance' => [
            'facility' => $maintenance->facility ? $maintenance->facility->name : '-',
            'issue_type' => $maintenance->issue_type,
            'trigger_month' => $maintenance->trigger_month,
            'efficiency_rating' => $maintenance->efficiency_rating,
            'maintenance_status' => $maintenance->maintenance_status,
            'scheduled_date' => $maintenance->scheduled_date,
            'remarks' => $maintenance->remarks,
        ]]);
}

public function index()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
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


            // Get last 3 efficiency history for this facility (ordered by completed_date ASC)
            $history = \App\Models\MaintenanceHistory::where('facility_id', $row->facility_id)
                ->orderBy('completed_date', 'asc')
                ->pluck('efficiency_rating')
                ->filter(function($v) { return $v !== null; })
                ->values();
            $count = $history->count();
            $trend = '';
            if ($count > 1) {
                // Use only the last 3 records (or 2 if only 2 exist)
                $slice = $history->slice(-3)->values();
                $isIncreasing = true;
                $isDecreasing = true;
                $isStable = true;
                for ($i = 1; $i < $slice->count(); $i++) {
                    if ($slice[$i] > $slice[$i-1]) {
                        $isDecreasing = false;
                        $isStable = false;
                    } elseif ($slice[$i] < $slice[$i-1]) {
                        $isIncreasing = false;
                        $isStable = false;
                    } else {
                        $isIncreasing = false;
                        $isDecreasing = false;
                    }
                }
                if ($isStable) {
                    $trend = 'Stable';
                } elseif ($isIncreasing) {
                    $trend = 'Increasing';
                } elseif ($isDecreasing) {
                    $trend = 'Decreasing';
                } else {
                    $trend = 'Fluctuating';
                }
            } elseif ($count === 1) {
                $trend = 'Stable';
            }

            $maintenanceRows[] = [
                'id' => $row->id,
                'facility' => $row->facility ? $row->facility->name : '-',
                'issue_type' => $row->issue_type,
                'trigger_month' => $row->trigger_month,
                'efficiency_rating' => $row->efficiency_rating,
                'maintenance_type' => $row->maintenance_type,
                'maintenance_status' => $row->maintenance_status,
                'scheduled_date' => $row->scheduled_date ?? '-',
                'assigned_to' => $row->assigned_to,
                'completed_date' => $row->completed_date,
                'remarks' => $row->remarks ?? '-',
                'action' => $row->maintenance_status === 'Pending'
                    ? '<button class="btn btn-sm" style="background:#2563eb;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;" title="Schedule Maintenance"><i class="fa fa-calendar-plus"></i> Schedule</button>'
                    : ($row->maintenance_status === 'Ongoing' ? '<button class="btn btn-sm" style="background:#22c55e;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;" title="Mark as Complete"><i class="fa fa-check-circle"></i> Complete</button>' : '-')
            ];
        }

        // Optional: count re-flagged (facilities with completed + new pending)
        $completed = $maintenance->where('maintenance_status','Completed')->pluck('facility_id')->unique();
        $pending = $maintenance->where('maintenance_status','Pending')->pluck('facility_id')->unique();
        $reflaggedCount = $completed->intersect($pending)->count();

        $user = auth()->user();
        $role = strtolower($user->role ?? '');
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
}
