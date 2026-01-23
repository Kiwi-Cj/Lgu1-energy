<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsSummaryExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        // Format numbers (usage columns)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('B2:B'.$highestRow)
            ->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('C2:C'.$highestRow)
            ->getNumberFormat()->setFormatCode('#,##0.00');
        return [];
    }
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function array(): array
    {
        $rows = [];
        foreach ($this->data['summaryData'] as $row) {
            $rows[] = [
                $row['facility'],
                $row['total_usage'],
                $row['average_usage']
            ];
        }
        // Add summary row at the end
        $rows[] = [
            'TOTAL',
            $this->data['totalUsage'],
            $this->data['averageUsage']
        ];
        return $rows;
    }
    public function headings(): array
    {
        return [
            'Facility',
            'Total Usage (kWh)',
            'Average Usage (kWh)'
        ];
    }
    public function title(): string
    {
        return 'Analytics Summary';
    }
}
