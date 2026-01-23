<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        // For demo, use static data. Replace with actual query logic as needed.
        $results = [
            ['Facility' => 'Main Building', 'Equipment' => 'Generator', 'Energy Usage' => 1200, 'Date' => '2026-01-01'],
            ['Facility' => 'Annex', 'Equipment' => 'AC Unit', 'Energy Usage' => 800, 'Date' => '2026-01-02'],
        ];
        $pdf = \PDF::loadView('admin.reports.custom-pdf', compact('results'));
        return $pdf->download('custom_report.pdf');
    }

    public function index(Request $request)
    {
        $results = null;
        if ($request->isMethod('post')) {
            $fields = $request->input('fields', []);
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $filters = $request->input('filters');

            // Example: Build a query based on selected fields and filters
            // This is a placeholder, you can customize per your schema
            $query = \DB::table('facilities');
            if (in_array('equipment', $fields)) {
                $query->join('equipment', 'facilities.id', '=', 'equipment.facility_id');
            }
            if (in_array('energy_usage', $fields)) {
                $query->join('energy_usages', 'facilities.id', '=', 'energy_usages.facility_id');
            }
            if ($dateFrom) {
                $query->whereDate('facilities.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('facilities.created_at', '<=', $dateTo);
            }
            // Simple filter parsing (e.g. Facility=Main, Status=Active)
            if ($filters) {
                $filterPairs = explode(',', $filters);
                foreach ($filterPairs as $pair) {
                    [$key, $value] = array_map('trim', explode('=', $pair));
                    if ($key && $value) {
                        $query->where($key, $value);
                    }
                }
            }
            $results = $query->select($fields ?: ['facilities.*'])->get();
        }
        return view('admin.reports.custom', compact('results'));
    }
}
