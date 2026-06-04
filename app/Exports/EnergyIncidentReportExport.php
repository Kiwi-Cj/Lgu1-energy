<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EnergyIncidentReportExport implements FromView
{
    public function __construct(private $incidentRows)
    {
    }

    public function view(): View
    {
        return view('exports.energy_incident_report', [
            'incidentRows' => $this->incidentRows,
        ]);
    }
}
