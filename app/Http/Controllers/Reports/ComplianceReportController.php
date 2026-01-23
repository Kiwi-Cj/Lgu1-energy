<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplianceReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $facility = $request->input('facility');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = [
            ['facility' => 'Main Building', 'status' => 'Compliant', 'remarks' => 'All good', 'date' => '2026-01-01'],
            ['facility' => 'Annex', 'status' => 'Non-Compliant', 'remarks' => 'Missing documents', 'date' => '2026-01-02'],
            ['facility' => 'Warehouse', 'status' => 'Compliant', 'remarks' => 'Passed inspection', 'date' => '2026-01-03'],
        ];

        $complianceData = array_filter($query, function($row) use ($facility, $status, $dateFrom, $dateTo) {
            $pass = true;
            if ($facility && stripos($row['facility'], $facility) === false) $pass = false;
            if ($status && $row['status'] !== $status) $pass = false;
            if ($dateFrom && $row['date'] < $dateFrom) $pass = false;
            if ($dateTo && $row['date'] > $dateTo) $pass = false;
            return $pass;
        });

        $compliantCount = count(array_filter($complianceData, fn($row) => $row['status'] === 'Compliant'));
        $nonCompliantCount = count(array_filter($complianceData, fn($row) => $row['status'] === 'Non-Compliant'));

        $pdf = \PDF::loadView('admin.reports.compliance-pdf', compact('complianceData', 'compliantCount', 'nonCompliantCount'));
        return $pdf->download('compliance_report.pdf');
    }

    public function index(Request $request)
    {
        // Sample data, replace with actual DB queries
        $facility = $request->input('facility');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = [
            ['facility' => 'Main Building', 'status' => 'Compliant', 'remarks' => 'All good', 'date' => '2026-01-01'],
            ['facility' => 'Annex', 'status' => 'Non-Compliant', 'remarks' => 'Missing documents', 'date' => '2026-01-02'],
            ['facility' => 'Warehouse', 'status' => 'Compliant', 'remarks' => 'Passed inspection', 'date' => '2026-01-03'],
        ];

        // Filter logic (for demo, use array_filter)
        $complianceData = array_filter($query, function($row) use ($facility, $status, $dateFrom, $dateTo) {
            $pass = true;
            if ($facility && stripos($row['facility'], $facility) === false) $pass = false;
            if ($status && $row['status'] !== $status) $pass = false;
            if ($dateFrom && $row['date'] < $dateFrom) $pass = false;
            if ($dateTo && $row['date'] > $dateTo) $pass = false;
            return $pass;
        });

        $compliantCount = count(array_filter($complianceData, fn($row) => $row['status'] === 'Compliant'));
        $nonCompliantCount = count(array_filter($complianceData, fn($row) => $row['status'] === 'Non-Compliant'));

        return view('admin.reports.compliance', compact('complianceData', 'compliantCount', 'nonCompliantCount'));
    }
}
