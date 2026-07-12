<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EfficiencySummaryReportExport implements FromView
{
    public function __construct(
        protected array $efficiencyRows
    ) {
    }

    public function view(): View
    {
        return view('exports.efficiency_summary_report', [
            'efficiencyRows' => $this->efficiencyRows,
        ]);
    }
}
