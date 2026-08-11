<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\EnergyPerformanceSummaryService;
use Illuminate\Http\Request;

class EnergyPerformanceSummaryController extends Controller
{
    public function show(Request $request, EnergyPerformanceSummaryService $summaryService)
    {
        $user = $request->user();
        $facilities = strtolower((string) ($user->role ?? '')) === 'staff'
            ? $user->facilities
            : Facility::query()->orderBy('name')->get();
        $selectedFacility = $request->input('facility_id');
        $selectedStatus = $request->input('status', 'all');

        $filteredFacilities = $selectedFacility
            ? $facilities->where('id', (int) $selectedFacility)
            : $facilities;
        $summary = $summaryService->build($filteredFacilities, $selectedStatus);

        return view('modules.reports.energy-performance-summary', [
            'performanceRows' => $summary['rows'],
            'summary' => $summary,
            'facilities' => $facilities,
            'selectedFacility' => $selectedFacility,
            'selectedStatus' => $selectedStatus,
        ]);
    }
}
