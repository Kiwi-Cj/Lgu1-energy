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
        return redirect()->route('reports.energy');
    }
}
