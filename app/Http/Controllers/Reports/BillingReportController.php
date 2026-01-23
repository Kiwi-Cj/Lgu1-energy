<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Facility;

class BillingReportController extends Controller
{
    public function show(Request $request)
    {
        // Get all bills with facility relationship
        $bills = Bill::with('facility')->orderByDesc('month')->orderBy('facility_id')->get();

        $billingRows = $bills->map(function($bill) {
            $monthName = $bill->month ? date('M Y', strtotime($bill->month.'-01')) : '-';
            return [
                'facility' => $bill->facility->name ?? '-',
                'month' => $monthName,
                'kwh' => $bill->kwh_consumed,
                'total_bill' => $bill->total_bill,
                'status' => $bill->status,
            ];
        });

        // Summary cards
        $highest = $bills->sortByDesc('total_bill')->first();
        $lowest = $bills->sortBy('total_bill')->first();
        $highestCostFacility = $highest && $highest->facility ? $highest->facility->name : '-';
        $lowestCostFacility = $lowest && $lowest->facility ? $lowest->facility->name : '-';
        $unpaidBillsCount = $bills->where('status', 'Unpaid')->count();

        return view('modules.reports.billing', [
            'billingRows' => $billingRows,
            'highestCostFacility' => $highestCostFacility,
            'lowestCostFacility' => $lowestCostFacility,
            'unpaidBillsCount' => $unpaidBillsCount,
        ]);
    }
}
