<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EnergyReportExport implements FromView
{
    protected $energyRows;

    public function __construct($energyRows)
    {
        $this->energyRows = $energyRows;
    }

    public function view(): View
    {
        return view('exports.energy_report', [
            'energyRows' => $this->energyRows
        ]);
    }
}
