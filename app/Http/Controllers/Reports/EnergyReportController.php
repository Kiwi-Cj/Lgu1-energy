<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnergyReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        // Sample data, replace with actual DB queries
        $energyData = [
            ['facility' => 'Main Building', 'usage' => 1200, 'date' => '2026-01-01'],
            ['facility' => 'Annex', 'usage' => 800, 'date' => '2026-01-02'],
            ['facility' => 'Warehouse', 'usage' => 950, 'date' => '2026-01-03'],
        ];
        $totalUsage = array_sum(array_column($energyData, 'usage'));
        $pdf = \PDF::loadView('admin.reports.energy-pdf', compact('energyData', 'totalUsage'));
        return $pdf->download('energy_report.pdf');
    }

    public function index()
    {
        // Fetch energy usage data per facility
        $energyData = \DB::table('energy_usages')
            ->join('facilities', 'energy_usages.facility_id', '=', 'facilities.id')
            ->select('facilities.name as facility', 'energy_usages.usage', 'energy_usages.date')
            ->orderBy('energy_usages.date', 'desc')
            ->get()
            ->map(function($row) {
                return [
                    'facility' => $row->facility,
                    'usage' => $row->usage,
                    'date' => $row->date,
                ];
            })->toArray();

        $totalUsage = collect($energyData)->sum('usage');

        return view('admin.reports.energy', compact('energyData', 'totalUsage'));
    }
}
