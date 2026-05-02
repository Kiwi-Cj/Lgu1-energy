<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Exports\FacilitiesArchiveExport;
use App\Models\BaselineResetLog;
use App\Models\EnergyIncident;
use App\Models\EnergyIncidentHistory;
use App\Models\EnergyProfile;
use App\Models\EnergyRecord;
use App\Models\FacilityMeter;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\FacilityAuditLog;
use App\Models\Maintenance;
use App\Models\MaintenanceHistory;
use App\Models\Submeter;
use App\Models\SubmeterEquipment;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;


class FacilityController extends Controller
{
    private function resolveWebPublicRoot(): string
    {
        // Optional override for shared hosting / cPanel setups.
        $configured = (string) env('PUBLIC_UPLOAD_ROOT', '');
        if ($configured !== '' && is_dir($configured)) {
            return rtrim($configured, DIRECTORY_SEPARATOR);
        }

        // Common cPanel layout: project in ".../lgu1_energy", live web root in sibling ".../public_html".
        $cpanelPublicHtml = dirname(base_path()) . DIRECTORY_SEPARATOR . 'public_html';
        if (is_dir($cpanelPublicHtml)) {
            return rtrim($cpanelPublicHtml, DIRECTORY_SEPARATOR);
        }

        return public_path();
    }

    private function storeFacilityImageToPublic(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $image = $request->file('image');
        $directory = $this->resolveWebPublicRoot() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'facility_images';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('facility_', true) . '.' . $image->getClientOriginalExtension();
        $image->move($directory, $filename);

        return 'uploads/facility_images/' . $filename;
    }

    /**
     * Update the specified facility in storage.
     */
    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'floor_area' => 'nullable|numeric|min:0',
            'floors' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'operating_hours' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        // Handle image upload if present
        if ($path = $this->storeFacilityImageToPublic($request)) {
            $validated['image_path'] = $path;
        }

        // Remove 'image' from validated to avoid mass assignment error
        unset($validated['image']);

        $facility->update($validated);

        return redirect()->route('facilities.show', $facility->id)->with('success', 'Facility updated successfully.');
    }
    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'floor_area' => 'nullable|numeric|min:0',
            'floors' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'operating_hours' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        // Handle image upload if present
        if ($path = $this->storeFacilityImageToPublic($request)) {
            $validated['image_path'] = $path;
        }

        // Remove 'image' from validated to avoid mass assignment error
        unset($validated['image']);

        $facility = Facility::create($validated);

        return redirect()->route('facilities.index')->with('success', 'Facility added successfully.');
    }
    /**
     * Show the form for editing the specified facility.
     */
    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        return redirect()->route('modules.facilities.show', ['id' => $facility->id]);
    }
    public function show($id)
    {
        $facility = Facility::findOrFail($id);

        // Fallback: use last 3 energyRecords instead of deleted energyReadings
        $avgKwh = null;
        $showAvg = false;
        $records = method_exists($facility, 'energyRecords') ? $facility->energyRecords()->orderBy('year')->orderBy('month')->take(3)->pluck('actual_kwh') : collect();
        if ($records->count() === 3) {
            $avgKwh = $records->avg();
            $showAvg = true;
        }

        return view('modules.facilities.show', compact('facility', 'avgKwh', 'showAvg'));
    }
    private function isSuperAdmin()
    {
        return RoleAccess::is(auth()->user(), 'super_admin');
    }

    private function isStaff()
    {
        return RoleAccess::is(auth()->user(), 'staff');
    }

    private function isEngineer()
    {
        return RoleAccess::is(auth()->user(), 'engineer');
    }

    private function isArchiveAdmin(): bool
    {
        return RoleAccess::in(auth()->user(), ['super_admin', 'admin']);
    }

    private function logFacilityAudit(Facility $facility, string $action, ?string $reason = null): void
    {
        try {
            FacilityAuditLog::create([
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'action' => $action,
                'reason' => $reason ? trim($reason) : null,
                'performed_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /* =========================
        FACILITY LIST
    ========================== */
    public function index()
    {
        $user = auth()->user();
        $facilityRelations = [
            'meters:id,facility_id,meter_name,meter_type,status,approved_at,baseline_kwh',
            'submeters:id,facility_id,submeter_name',
        ];

        if ($this->isStaff()) {
            // Get all facilities assigned to this staff via facility_user pivot
            $facilities = $user->facilities ?? collect();
            if (method_exists($facilities, 'load')) {
                $facilities->load($facilityRelations);
            }
        } else {
            $facilities = Facility::with($facilityRelations)->get();
        }

        // Compute dynamic facility size from total approved MAIN meter baseline.
        $facilitiesWithAvg = $facilities->map(function($facility) {
            $baselineKwh = null;
            $facility->resolvedBaselineSource = 'manual';
            $facility->resolvedBaselineSourceLabel = 'No main meter baseline';

            $mainBaselineSum = (float) $facility->meters
                ->filter(function ($meter) {
                    return strtolower((string) $meter->meter_type) === 'main'
                        && ! is_null($meter->approved_at)
                        && strtolower((string) $meter->status) === 'active';
                })
                ->sum(function ($meter) {
                    return (is_numeric($meter->baseline_kwh) && (float) $meter->baseline_kwh > 0)
                        ? (float) $meter->baseline_kwh
                        : 0;
                });

            if ($mainBaselineSum > 0) {
                $baselineKwh = $mainBaselineSum;
                $facility->facilitySizeSource = 'main_meter_total';
                $facility->resolvedBaselineSource = 'main_meter_total';
                $facility->resolvedBaselineSourceLabel = 'Total Main Meter Baseline';
            } else {
                $facility->facilitySizeSource = 'manual';
            }

            $facility->resolvedBaselineKwh = $baselineKwh;

            if ($baselineKwh !== null) {
                $facility->dynamicSize = Facility::resolveSizeLabelFromBaseline($baselineKwh) ?? ($facility->size ?? 'N/A');
            } else {
                $facility->dynamicSize = $facility->size ?? 'N/A';
            }

            $facility->searchMeterNames = $facility->meters
                ->pluck('meter_name')
                ->filter(fn ($name) => trim((string) $name) !== '')
                ->implode(' ');

            $facility->searchSubmeterNames = $facility->submeters
                ->pluck('submeter_name')
                ->filter(fn ($name) => trim((string) $name) !== '')
                ->implode(' ');

            return $facility;
        });

        return view('modules.facilities.index', [
            'facilities' => $facilitiesWithAvg,
            'totalFacilities' => $facilities->count(),
            'activeFacilities' => $facilities->where('status', 'active')->count(),
            'inactiveFacilities' => $facilities->where('status', 'inactive')->count(),
            'maintenanceFacilities' => $facilities->where('status', 'maintenance')->count(),
            'archivedFacilitiesCount' => Facility::onlyTrashed()->count(),
        ]);
    }

    public function equipmentInventory(Request $request, Facility $facility)
    {
        $user = $request->user();
        $canViewInventory = RoleAccess::can($user, 'view_load_tracking')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer']);
        $canManageInventory = RoleAccess::can($user, 'manage_load_tracking')
            || RoleAccess::in($user, ['super_admin', 'admin', 'energy_officer', 'staff']);

        if (! $canViewInventory) {
            return redirect()->route('modules.facilities.index')
                ->with('error', 'You do not have permission to view equipment inventory.');
        }

        if ($this->isStaff()) {
            $assignedFacilityIds = $user->facilities()
                ->pluck('facilities.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! in_array((int) $facility->id, $assignedFacilityIds, true)) {
                return redirect()->route('modules.facilities.index')
                    ->with('error', 'You can only view equipment inventory for your assigned facility.');
            }
        }

        $selectedMeterScope = strtolower(trim((string) $request->query('meter_scope', 'all')));
        $selectedSubmeter = $request->filled('submeter_id') ? (int) $request->query('submeter_id') : null;
        $selectedMainMeter = $request->filled('main_meter_id') ? (int) $request->query('main_meter_id') : null;

        if (! in_array($selectedMeterScope, ['all', 'sub', 'main'], true)) {
            $selectedMeterScope = 'all';
        }

        if ($selectedMeterScope === 'sub') {
            $selectedMainMeter = null;
        } elseif ($selectedMeterScope === 'main') {
            $selectedSubmeter = null;
        } elseif ($selectedSubmeter && $selectedMainMeter) {
            // Avoid conflicting filters when scope is "all".
            $selectedMainMeter = null;
        }

        $submeters = Submeter::query()
            ->where('facility_id', (int) $facility->id)
            ->where('status', 'active')
            ->orderBy('submeter_name')
            ->get(['id', 'facility_id', 'submeter_name', 'status']);

        $mainMeters = FacilityMeter::query()
            ->where('facility_id', (int) $facility->id)
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

        $equipmentQuery = SubmeterEquipment::query()
            ->with([
                'submeter:id,facility_id,submeter_name,status',
                'mainMeter:id,facility_id,meter_name,meter_number,meter_type,status,approved_at',
            ])
            ->where(function ($query) use ($facility) {
                $query->where(function ($subQuery) use ($facility) {
                    $subQuery->where('meter_scope', 'sub')
                        ->whereHas('submeter', function ($meterQuery) use ($facility) {
                            $meterQuery->where('facility_id', (int) $facility->id)
                                ->where('status', 'active');
                        });
                })->orWhere(function ($mainQuery) use ($facility) {
                    $mainQuery->where('meter_scope', 'main')
                        ->whereHas('mainMeter', function ($meterQuery) use ($facility) {
                            $meterQuery->where('facility_id', (int) $facility->id)
                                ->where('meter_type', 'main')
                                ->where('status', 'active')
                                ->whereNotNull('approved_at');
                        });
                });
            });

        if ($selectedMeterScope === 'sub') {
            $equipmentQuery->where('meter_scope', 'sub');
            if ($selectedSubmeter) {
                $equipmentQuery->where('submeter_id', $selectedSubmeter);
            }
        } elseif ($selectedMeterScope === 'main') {
            $equipmentQuery->where('meter_scope', 'main');
            if ($selectedMainMeter) {
                $equipmentQuery->where('facility_meter_id', $selectedMainMeter);
            }
        } else {
            if ($selectedSubmeter) {
                $equipmentQuery->where('meter_scope', 'sub')
                    ->where('submeter_id', $selectedSubmeter);
            } elseif ($selectedMainMeter) {
                $equipmentQuery->where('meter_scope', 'main')
                    ->where('facility_meter_id', $selectedMainMeter);
            }
        }

        $summaryRows = (clone $equipmentQuery)
            ->setEagerLoads([])
            ->get(['id', 'quantity', 'rated_watts', 'estimated_kwh']);
        $totals = [
            'items' => (int) $summaryRows->count(),
            'total_watts' => (float) $summaryRows->sum(function ($equipment) {
                $quantity = (int) ($equipment->quantity ?? 0);
                $watts = (float) ($equipment->rated_watts ?? 0);
                return $quantity * $watts;
            }),
            'estimated_kwh' => (float) $summaryRows->sum(fn ($equipment) => (float) ($equipment->estimated_kwh ?? 0)),
        ];

        $equipmentRows = $equipmentQuery
            ->orderByDesc('estimated_kwh')
            ->orderBy('equipment_name')
            ->paginate(25)
            ->withQueryString();

        return view('modules.facilities.equipment-inventory', [
            'facility' => $facility,
            'submeters' => $submeters,
            'mainMeters' => $mainMeters,
            'selectedMeterScope' => $selectedMeterScope,
            'selectedSubmeter' => $selectedSubmeter,
            'selectedMainMeter' => $selectedMainMeter,
            'equipmentRows' => $equipmentRows,
            'totals' => $totals,
            'canManageInventory' => $canManageInventory,
        ]);
    }

    /* =========================
        FACILITY MODAL DETAILS
    ========================== */
    public function modalDetail($id)
    {
        $facility = Facility::findOrFail($id);
        $profile = $facility->energyProfiles()->latest()->first();
        $energyRecords = $facility->energyRecords()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Debug: Log when modalDetail is called
        \Log::info('modalDetail called', ['facility_id' => $id]);

        // BASELINE
        // Use profile baseline_kwh if locked, else use last 3 energyRecords
        $avgKwh = null;
        if ($profile && $profile->baseline_kwh && $profile->baseline_locked) {
            $avgKwh = $profile->baseline_kwh;
        } elseif ($energyRecords->count() >= 3) {
            $avgKwh = round($energyRecords->take(3)->avg('actual_kwh'), 2);
            if ($profile) {
                $profile->update([
                    'baseline_kwh' => $avgKwh,
                    'baseline_locked' => true,
                    'baseline_source' => 'computed',
                ]);
            } else {
                try {
                    $created = $facility->energyProfiles()->create([
                        'baseline_kwh' => $avgKwh,
                        'baseline_locked' => true,
                        'baseline_source' => 'computed',
                        'electric_meter_no' => 'N/A',
                        'utility_provider' => 'N/A',
                        'contract_account_no' => 'N/A',
                        'main_energy_source' => 'Grid',
                        'backup_power' => 'None',
                        'transformer_capacity' => 0,
                        'number_of_meters' => 1,
                    ]);
                    \Log::info('EnergyProfile created', ['profile' => $created]);
                } catch (\Exception $e) {
                    \Log::error('EnergyProfile creation failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // TREND
        $trendRecords = $energyRecords->take(3)->reverse();
        $trendData = $trendRecords->pluck('actual_kwh');
        $trendLabels = $trendRecords->map(function ($r) {
            return $r->month ? date('M', mktime(0, 0, 0, $r->month, 1)) . ' ' . $r->year : '- ' . $r->year;
        });

        // TREND ANALYSIS
        $trendAnalysis = null;
        if ($trendData->count() === 3) {
            $vals = $trendData->values();
            if ($vals[2] > $vals[1] && $vals[1] > $vals[0]) $trendAnalysis = 'Increasing';
            elseif ($vals[2] < $vals[1] && $vals[1] < $vals[0]) $trendAnalysis = 'Decreasing';
            else $trendAnalysis = 'Stable';
        }

        // SUDDEN SPIKE
        $suddenSpike = false;
        if ($avgKwh && $trendData->isNotEmpty()) {
            $suddenSpike = $trendData->last() > ($avgKwh * 1.30);
        }

        // USAGE ROWS
        $usageRows = [];
        foreach ($trendRecords as $rec) {
            $variance = ($avgKwh !== null) ? $rec->actual_kwh - $avgKwh : null;
            $percent = ($avgKwh && $avgKwh > 0) ? ($rec->actual_kwh / $avgKwh) * 100 : null;

            if ($percent !== null && $percent > 120) $alert = 'High';
            elseif ($percent !== null && $percent >= 90) $alert = 'Medium';
            else $alert = 'Normal';

            $usageRows[] = [
                'month' => $rec->month ? date('M', mktime(0, 0, 0, $rec->month, 1)) . ' ' . $rec->year : '- ' . $rec->year,
                'actual_kwh' => $rec->actual_kwh,
                'baseline_kwh' => $avgKwh,
                'variance' => $variance,
                'alert_level' => $alert,
            ];
        }

        // RECOMMENDATIONS
        $recommendations = [];
        if ($trendAnalysis === 'Increasing') $recommendations[] = 'Energy consumption trend is increasing. Review operating schedules and equipment condition.';
        if ($suddenSpike) $recommendations[] = 'Sudden increase detected. Possible abnormal usage or equipment issue. Field validation recommended.';
        $highKwhRec = $this->getHighKwhRecommendation($avgKwh, $trendData);
        if ($highKwhRec) $recommendations[] = $highKwhRec;
        if (empty($recommendations)) $recommendations[] = 'Energy consumption within acceptable range. Continue regular monitoring.';

        // EUI
        $monthlyEui = $annualEui = null;
        if ($facility->floor_area && $energyRecords->count()) {
            $latestKwh = $energyRecords->first()->actual_kwh;
            $monthlyEui = round($latestKwh / $facility->floor_area, 2);
            $annualEui = round(($latestKwh * 12) / $facility->floor_area, 2);
        }

        // MAINTENANCE
        $lastMaint = Maintenance::where('facility_id', $facility->id)
            ->whereNotNull('completed_date')
            ->orderBy('completed_date', 'desc')->first();

        $nextMaint = Maintenance::where('facility_id', $facility->id)
            ->where('maintenance_status', 'Scheduled')
            ->orderBy('scheduled_date', 'asc')->first();

        return response()->json([
                'facility' => $facility->name,
                'type' => $facility->type,
                'address' => $facility->address,
                'barangay' => $facility->barangay,
                'status' => $facility->status,
                'baseline_kwh' => $avgKwh,
                'baseline_status' => $profile && $profile->baseline_locked ? 'Approved Baseline' : 'Temporary Baseline',
                'engineer_approved' => $profile ? (bool)$profile->engineer_approved : false,
                'trend_labels' => $trendLabels,
                'trend_data' => $trendData,
                'trend_analysis' => $trendAnalysis,
                'usage' => $usageRows,
                'monthly_eui' => $monthlyEui,
                'annual_eui' => $annualEui,
                'last_maintenance' => $lastMaint ? $lastMaint->completed_date : null,
                'next_maintenance' => $nextMaint ? $nextMaint->scheduled_date : null,
                'recommendations' => $recommendations,
                'disclaimer' => 'System-generated analysis. Subject to validation by assigned LGU personnel.',
                'image_url' => $facility->resolved_image_url,
            ]);
    }

    /* =========================
        HIGH KWH THRESHOLD
    ========================== */
    private function getHighKwhRecommendation($avgKwh, $trendData)
    {
        if (!$avgKwh || $trendData->isEmpty()) return null;

        if ($avgKwh > 3000) $percent = 0.10;
        elseif ($avgKwh > 1500) $percent = 0.15;
        elseif ($avgKwh > 500) $percent = 0.20;
        else $percent = 0.30;

        $threshold = $avgKwh * (1 + $percent);

        if ($trendData->last() >= $threshold) {
            return 'Consumption exceeded allowable threshold for facility size. Engineering review recommended.';
        }

        return null;
    }

    /* =========================
        BASELINE RESET W/ LOG
    ========================== */
    public function resetBaseline(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$this->isSuperAdmin()) {
            return redirect()->route('facilities.index')
                ->with('error', 'Only Super Admin can reset baseline.');
        }

        $facility = Facility::findOrFail($id);
        $profile = $facility->energyProfiles()->latest()->first();

        if ($profile) {
            $profile->update([
                'baseline_kwh' => null,
                'baseline_locked' => false,
                'baseline_source' => null,
            ]);
        }

        // LOGGING
        BaselineResetLog::create([
            'facility_id' => $facility->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'created_at' => now(),
        ]);

        return redirect()->route('facilities.show', $id)
            ->with('success', 'Baseline reset successfully and logged.');
    }

    /* =========================
        ENGINEER APPROVAL TOGGLE (FACILITY)
    ========================== */
    public function toggleEngineerApproval($id)
    {
        $facility = Facility::findOrFail($id);
        if (!$this->isEngineer() && !$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only engineers or super admins can approve.'], 403);
        }
        $facility->engineer_approved = !$facility->engineer_approved;
        $facility->save();
        return response()->json([
            'success' => true,
            'message' => 'Engineer approval status toggled.',
            'engineer_approved' => $facility->engineer_approved
        ]);
    }

    /* =========================
        MONTHLY COA REPORT EXPORT
    ========================== */
    public function exportMonthlyReport(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $facilities = Facility::all();

        $report = $facilities->map(function($f) use ($month, $year) {
            $record = $f->energyRecords()->where('month', $month)->where('year', $year)->first();
            $avg = $f->energyProfiles()->latest()->first()?->baseline_kwh;

            return [
                'facility' => $f->name,
                'barangay' => $f->barangay,
                'actual_kwh' => $record?->actual_kwh ?? 0,
                'baseline_kwh' => $avg ?? 0,
                'variance' => ($record?->actual_kwh ?? 0) - ($avg ?? 0),
            ];
        });

        $filename = "COA_Report_{$year}_{$month}.csv";
        $headers = ['Content-Type' => 'text/csv'];

        $callback = function() use ($report) {
            $file = fopen('php://output', 'w');
            if ($report->count()) fputcsv($file, array_keys($report->first()));
            foreach ($report as $row) fputcsv($file, $row);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /* =========================
        DELETE FACILITY
    ========================== */
    public function destroy(Request $request, $id)
    {
        $archiveReason = trim((string) $request->input('archive_reason', ''));
        if ($archiveReason === '') {
            return redirect()->back()->with('error', 'Archive reason is required.');
        }
        if (mb_strlen($archiveReason) > 500) {
            return redirect()->back()->with('error', 'Archive reason must be 500 characters or fewer.');
        }

        $facility = Facility::findOrFail($id);
        $facility->deleted_by = auth()->id();
        $facility->archive_reason = $archiveReason;
        $facility->saveQuietly();
        $this->logFacilityAudit($facility, 'archived', $archiveReason);
        $facility->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Facility moved to archive successfully.']);
        }
        return redirect()->route('facilities.index')->with('success', 'Facility moved to archive.');
    }

    public function archive(Request $request)
    {
        $exportColumnOptions = [
            'facility' => 'Facility',
            'type' => 'Type',
            'status' => 'Status',
            'barangay' => 'Barangay',
            'archive_reason' => 'Archive Reason',
            'deleted_by' => 'Deleted By',
            'archived_at' => 'Archived At',
        ];

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => trim((string) $request->query('type', '')),
            'status' => trim((string) $request->query('status', '')),
            'archived_from' => trim((string) $request->query('archived_from', '')),
            'archived_to' => trim((string) $request->query('archived_to', '')),
        ];

        $requestedExportColumns = $request->query('export_columns', []);
        $requestedExportColumns = is_array($requestedExportColumns) ? $requestedExportColumns : [];
        $selectedExportColumns = array_values(array_intersect(array_keys($exportColumnOptions), $requestedExportColumns));
        if (empty($selectedExportColumns)) {
            $selectedExportColumns = array_keys($exportColumnOptions);
        }

        if ($filters['archived_from'] !== '' && $filters['archived_to'] !== '' && $filters['archived_from'] > $filters['archived_to']) {
            [$filters['archived_from'], $filters['archived_to']] = [$filters['archived_to'], $filters['archived_from']];
        }

        $query = Facility::onlyTrashed()->with('deletedByUser');

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhere('barangay', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        if ($filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['archived_from'] !== '') {
            $query->whereDate('deleted_at', '>=', $filters['archived_from']);
        }

        if ($filters['archived_to'] !== '') {
            $query->whereDate('deleted_at', '<=', $filters['archived_to']);
        }

        $exportFormat = strtolower(trim((string) $request->query('export', '')));
        if (in_array($exportFormat, ['csv', 'xlsx'], true)) {
            $exportRows = (clone $query)
                ->orderByDesc('deleted_at')
                ->get();

            $dateStamp = now()->format('Ymd_His');
            $extension = $exportFormat === 'xlsx' ? 'xlsx' : 'csv';
            $filename = "facilities_archive_{$dateStamp}.{$extension}";
            $writerType = $exportFormat === 'xlsx' ? ExcelWriter::XLSX : ExcelWriter::CSV;

            return Excel::download(new FacilitiesArchiveExport($exportRows, $selectedExportColumns), $filename, $writerType);
        }

        $archivedFacilities = $query
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        $typeOptions = Facility::onlyTrashed()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $statusOptions = Facility::onlyTrashed()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('modules.facilities.archive', [
            'archivedFacilities' => $archivedFacilities,
            'filters' => $filters,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
            'exportColumnOptions' => $exportColumnOptions,
            'selectedExportColumns' => $selectedExportColumns,
            'canForceDelete' => $this->isArchiveAdmin(),
        ]);
    }

    public function restore($id)
    {
        $facility = Facility::onlyTrashed()->findOrFail($id);
        $restoreLogReason = $facility->archive_reason
            ? 'Restored from archive. Original archive reason: ' . $facility->archive_reason
            : 'Restored from archive.';
        $this->logFacilityAudit($facility, 'restored', $restoreLogReason);
        $facility->restore();

        return redirect()->route('modules.facilities.archive')
            ->with('success', 'Facility restored successfully.');
    }

    public function forceDelete($id)
    {
        if (! $this->isArchiveAdmin()) {
            return redirect()->route('modules.facilities.archive')
                ->with('error', 'Only admins can permanently delete archived facilities.');
        }

        $facility = Facility::onlyTrashed()->findOrFail($id);

        try {
            DB::transaction(function () use ($facility) {
                $facilityId = $facility->id;
                $facilityName = $facility->name;
                $archiveReason = $facility->archive_reason;

                $this->logFacilityAudit(
                    $facility,
                    'permanently_deleted',
                    $archiveReason
                        ? 'Permanent delete from archive. Original archive reason: ' . $archiveReason
                        : 'Permanent delete from archive.'
                );

                $energyRecordIds = EnergyRecord::withTrashed()
                    ->where('facility_id', $facilityId)
                    ->pluck('id');

                if ($energyRecordIds->isNotEmpty()) {
                    EnergyIncidentHistory::whereIn('energy_record_id', $energyRecordIds)->delete();
                }

                // Force delete monthly records to avoid orphan rows and trigger cleanup observer logic.
                EnergyRecord::withTrashed()
                    ->where('facility_id', $facilityId)
                    ->get()
                    ->each(function (EnergyRecord $record) {
                        $record->forceDelete();
                    });

                EnergyIncident::where('facility_id', $facilityId)->delete();
                Maintenance::where('facility_id', $facilityId)->delete();
                MaintenanceHistory::where('facility_id', $facilityId)->delete();
                EnergyProfile::where('facility_id', $facilityId)->delete();
                BaselineResetLog::where('facility_id', $facilityId)->delete();

                $facility->users()->detach();
                $facility->forceDelete();

                // Keep facility audit logs viewable historically even after force delete.
                FacilityAuditLog::where('facility_id', $facilityId)
                    ->update(['facility_name' => $facilityName]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('modules.facilities.archive')
                ->with('error', 'Permanent delete failed. Please check related records or try again.');
        }

        return redirect()->route('modules.facilities.archive')
            ->with('success', 'Facility permanently deleted.');
    }
}
