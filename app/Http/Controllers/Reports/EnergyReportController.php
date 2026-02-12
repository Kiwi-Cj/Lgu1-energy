<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnergyReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $query = \App\Models\EnergyRecord::with('facility');
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        $records = $query->orderBy('year')->orderBy('month')->get();

        $energyData = [];
        foreach ($records as $record) {
            $facility = $record->facility;
            $baseline = $record->baseline_kwh;
            $actualKwh = $record->actual_kwh;
            $variance = ($baseline !== null) ? ($actualKwh - $baseline) : null;
            $trend = 'Stable';
            if ($variance !== null && $baseline !== null && $baseline != 0) {
                if ($variance > ($baseline * 0.05)) {
                    $trend = 'Increasing';
                } elseif ($variance < -($baseline * 0.05)) {
                    $trend = 'Decreasing';
                }
            }
            $monthNum = (int)ltrim($record->month, '0');
            $monthName = date('M', mktime(0, 0, 0, $monthNum, 1));
            $monthYear = $monthName . ' ' . $record->year;
            $energyData[] = [
                'facility' => $facility ? $facility->name : 'N/A',
                'month' => $monthYear,
                'actual_kwh' => number_format($actualKwh, 2),
                'baseline_kwh' => $baseline !== null ? number_format($baseline, 2) : 'N/A',
                'variance' => $variance !== null ? number_format($variance, 2) : 'N/A',
                'trend' => $trend,
            ];
        }
        $columns = ['facility', 'month', 'actual_kwh', 'baseline_kwh', 'variance', 'trend'];
        $totalUsage = array_sum(array_map(fn($row) => floatval(str_replace(',', '', $row['actual_kwh'])), $energyData));
        $pdf = \PDF::loadView('admin.reports.energy-pdf', compact('energyData', 'totalUsage', 'columns'));
        return $pdf->download('energy_report.pdf');
    }

    public function index()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $facilityIds = ($role === 'staff') ? $user->facilities->pluck('id')->toArray() : null;
        $query = \DB::table('energy_usages')
            ->join('facilities', 'energy_usages.facility_id', '=', 'facilities.id')
            ->select('facilities.name as facility', 'energy_usages.usage', 'energy_usages.date');
        if ($facilityIds) {
            $query->whereIn('energy_usages.facility_id', $facilityIds);
        }
        $energyData = $query
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
