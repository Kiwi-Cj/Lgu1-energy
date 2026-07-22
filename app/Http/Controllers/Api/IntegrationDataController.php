<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnergyIncident;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use App\Models\Submeter;
use App\Models\SubmeterReading;
use App\Traits\MaintenanceSyncHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IntegrationDataController extends Controller
{
    use MaintenanceSyncHelpers;


    public function summary(): JsonResponse
    {
        return response()->json([
            'data' => [
                'facilities' => Facility::query()->count(),
                'meters' => FacilityMeter::query()->count(),
                'submeters' => Submeter::query()->count(),
                'energy_records' => EnergyRecord::query()->count(),
                'submeter_readings' => SubmeterReading::query()->count(),
                'maintenance_records' => Maintenance::query()->count(),
                'open_maintenance' => Maintenance::query()
                    ->whereIn('maintenance_status', ['Pending', 'Ongoing'])
                    ->count(),
                'open_incidents' => EnergyIncident::query()
                    ->whereNotIn('status', ['Resolved', 'Closed'])
                    ->count(),
                'total_energy_kwh' => round((float) EnergyRecord::query()->sum('actual_kwh'), 2),
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function facilities(Request $request): JsonResponse
    {
        $query = Facility::query()
            ->withCount(['meters', 'submeters', 'energyRecords'])
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = '%'.addcslashes($request->string('search')->toString(), '%_').'%' ;
                $q->where(fn (Builder $nested) => $nested
                    ->where('name', 'like', $search)
                    ->orWhere('department', 'like', $search)
                    ->orWhere('barangay', 'like', $search));
            })
            ->orderBy('name');

        return $this->paginated($query, $request, fn (Facility $facility) => [
            'id' => $facility->id,
            'name' => $facility->name,
            'type' => $facility->type,
            'department' => $facility->department,
            'address' => $facility->address,
            'barangay' => $facility->barangay,
            'floor_area_sqm' => $facility->floor_area_sqm ?? $facility->floor_area,
            'floors' => $facility->floors,
            'operating_hours' => $facility->operating_hours,
            'status' => $facility->status,
            'baseline_kwh' => $this->number($facility->baseline_kwh),
            'meters_count' => $facility->meters_count,
            'submeters_count' => $facility->submeters_count,
            'energy_records_count' => $facility->energy_records_count,
            'updated_at' => $facility->updated_at?->toIso8601String(),
        ]);
    }

    public function meters(Request $request): JsonResponse
    {
        $query = FacilityMeter::query()
            ->with('facility:id,name')
            ->when($request->filled('facility_id'), fn (Builder $q) => $q->where('facility_id', $request->integer('facility_id')))
            ->when($request->filled('type'), fn (Builder $q) => $q->where('meter_type', $request->string('type')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->orderBy('facility_id')->orderBy('meter_name');

        return $this->paginated($query, $request, fn (FacilityMeter $meter) => [
            'id' => $meter->id,
            'facility' => ['id' => $meter->facility_id, 'name' => $meter->facility?->name],
            'name' => $meter->meter_name,
            'number' => $meter->meter_number,
            'type' => $meter->meter_type,
            'parent_meter_id' => $meter->parent_meter_id,
            'location' => $meter->location,
            'status' => $meter->status,
            'multiplier' => $this->number($meter->multiplier),
            'baseline_kwh' => $this->number($meter->baseline_kwh),
            'updated_at' => $meter->updated_at?->toIso8601String(),
        ]);
    }

    public function energyRecords(Request $request): JsonResponse
    {
        $query = EnergyRecord::query()
            ->with(['facility:id,name', 'meter:id,meter_name,meter_number'])
            ->when($request->filled('facility_id'), fn (Builder $q) => $q->where('facility_id', $request->integer('facility_id')))
            ->when($request->filled('meter_id'), fn (Builder $q) => $q->where('meter_id', $request->integer('meter_id')))
            ->when($request->filled('year'), fn (Builder $q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('month'), fn (Builder $q) => $q->where('month', $request->integer('month')))
            ->orderByDesc('year')->orderByDesc('month')->orderByDesc('day')->orderByDesc('id');

        return $this->paginated($query, $request, fn (EnergyRecord $record) => [
            'id' => $record->id,
            'facility' => ['id' => $record->facility_id, 'name' => $record->facility?->name],
            'meter' => ['id' => $record->meter_id, 'name' => $record->meter?->meter_name, 'number' => $record->meter?->meter_number],
            'period' => ['year' => $record->year, 'month' => $record->month, 'day' => $record->day],
            'actual_kwh' => $this->number($record->actual_kwh),
            'energy_cost' => $this->number($record->energy_cost),
            'rate_per_kwh' => $this->number($record->rate_per_kwh),
            'baseline_kwh' => $this->number($record->baseline_kwh),
            'deviation_percent' => $record->deviation,
            'alert' => $record->alert,
            'created_at' => $record->created_at?->toIso8601String(),
            'updated_at' => $record->updated_at?->toIso8601String(),
        ]);
    }

    public function incidents(Request $request): JsonResponse
    {
        $query = EnergyIncident::query()
            ->with('facility:id,name')
            ->when($request->filled('facility_id'), fn (Builder $q) => $q->where('facility_id', $request->integer('facility_id')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->orderByDesc('date_detected')->orderByDesc('id');

        return $this->paginated($query, $request, fn (EnergyIncident $incident) => [
            'id' => $incident->id,
            'facility' => ['id' => $incident->facility_id, 'name' => $incident->facility?->name],
            'energy_record_id' => $incident->energy_record_id,
            'period' => ['year' => $incident->year, 'month' => $incident->month],
            'deviation_percent' => $this->number($incident->deviation_percent),
            'severity' => $incident->severity_label,
            'description' => $incident->description,
            'status' => $incident->status,
            'date_detected' => $incident->date_detected?->toDateString(),
            'resolved_at' => $incident->resolved_at?->toIso8601String(),
            'updated_at' => $incident->updated_at?->toIso8601String(),
        ]);
    }

    public function maintenance(Request $request): JsonResponse
    {
        $request->validate([
            'facility_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'max:50'],
            'scheduled_from' => ['nullable', 'date_format:Y-m-d'],
            'scheduled_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:scheduled_from'],
            'updated_since' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Maintenance::query()
            ->with('facility:id,name')
            ->when($request->filled('facility_id'), fn (Builder $q) => $q->where('facility_id', $request->integer('facility_id')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('maintenance_status', $request->string('status')))
            ->when($request->filled('scheduled_from'), fn (Builder $q) => $q->whereDate('scheduled_date', '>=', $request->date('scheduled_from')))
            ->when($request->filled('scheduled_to'), fn (Builder $q) => $q->whereDate('scheduled_date', '<=', $request->date('scheduled_to')))
            ->when($request->filled('updated_since'), fn (Builder $q) => $q->where('updated_at', '>=', $request->date('updated_since')))
            ->orderByDesc('scheduled_date')
            ->orderByDesc('id');

        return $this->paginated($query, $request, fn (Maintenance $maintenance) => [
            'id' => $maintenance->id,
            'source' => 'active',
            'facility' => [
                'id' => $maintenance->facility_id,
                'name' => $maintenance->facility?->name,
            ],
            'issue_type' => $maintenance->issue_type,
            'trigger_month' => $maintenance->trigger_month,
            'trend' => $maintenance->trend,
            'maintenance_type' => $maintenance->maintenance_type,
            'status' => $maintenance->maintenance_status,
            'scheduled_date' => $maintenance->scheduled_date,
            'assigned_to' => $maintenance->assigned_to,
            'completed_date' => $maintenance->completed_date,
            'remarks' => $maintenance->remarks,
            'created_at' => $maintenance->created_at?->toIso8601String(),
            'updated_at' => $maintenance->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Completed maintenance history — the counterpart to maintenance() above.
     * Read-only: once a record lands here it's terminal on the Energy side,
     * so unlike /maintenance there is no matching sync-back endpoint for it.
     */
    public function maintenanceHistory(Request $request): JsonResponse
    {
        $request->validate([
            'facility_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'max:50'],
            'updated_since' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = MaintenanceHistory::query()
            ->with('facility:id,name')
            ->when($request->filled('facility_id'), fn (Builder $q) => $q->where('facility_id', $request->integer('facility_id')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('maintenance_status', $request->string('status')))
            ->when($request->filled('updated_since'), fn (Builder $q) => $q->where('updated_at', '>=', $request->date('updated_since')))
            ->orderByDesc('completed_date')
            ->orderByDesc('id');

        return $this->paginated($query, $request, fn (MaintenanceHistory $history) => [
            'id' => $history->id,
            'source' => 'history',
            'facility' => [
                'id' => $history->facility_id,
                'name' => $history->facility?->name,
            ],
            'issue_type' => $history->issue_type,
            'trigger_month' => $history->trigger_month,
            'trend' => $history->trend,
            'maintenance_type' => $history->maintenance_type,
            'status' => $history->maintenance_status,
            'scheduled_date' => $history->scheduled_date,
            'assigned_to' => $history->assigned_to,
            'completed_date' => $history->completed_date,
            'remarks' => $history->remarks,
            'created_at' => $history->created_at?->toIso8601String(),
            'updated_at' => $history->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Sync-back target for CIMM: applies a status/schedule change made in
     * CIMM's maintenance_schedule to the originating Energy `maintenance`
     * row, via the same trait both this and MaintenanceController::store()
     * use — so a CIMM-initiated "Completed" archives to history, flips
     * facility status, resolves the linked incident, and notifies users
     * exactly like a change made on this app's own Maintenance page would.
     *
     * Only targets active (still-open) records: once a record is archived
     * to history it's terminal here, so there's nothing left to sync.
     */
    public function updateMaintenance(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['Pending', 'Ongoing', 'Completed'])],
            'scheduled_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'completed_date' => ['nullable', 'date', 'required_if:status,Completed'],
        ]);

        $maintenance = Maintenance::find($id);
        if (!$maintenance) {
            return response()->json(['success' => false, 'message' => 'Maintenance record not found or already archived.'], 404);
        }

        $previousStatus = $maintenance->maintenance_status;

        $this->applyMaintenanceStatusUpdate($maintenance, [
            'maintenance_status' => $validated['status'],
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'completed_date' => $validated['completed_date'] ?? null,
        ]);

        $effects = $this->applyMaintenancePostSaveEffects($maintenance, $previousStatus);
        $record = $effects['maintenance'];

        return response()->json([
            'success' => true,
            'archived' => $effects['archived'],
            'maintenance' => [
                'id' => $effects['archived'] ? null : $record->id,
                'facility_id' => $record->facility_id,
                'status' => $record->maintenance_status,
                'scheduled_date' => $record->scheduled_date,
                'assigned_to' => $record->assigned_to,
                'completed_date' => $record->completed_date,
            ],
        ]);
    }

    private function paginated(Builder $query, Request $request, callable $transform): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 25), 1), 100);
        $paginator = $query->paginate($perPage)->withQueryString();
        $paginator->getCollection()->transform($transform);

        return response()->json($paginator);
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
