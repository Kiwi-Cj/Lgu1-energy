<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FacilitiesArchiveExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $rows,
        private readonly array $columns = []
    ) {
    }

    public function headings(): array
    {
        $labels = $this->columnLabels();

        return collect($this->resolvedColumns())
            ->map(fn (string $key) => $labels[$key] ?? $key)
            ->values()
            ->all();
    }

    public function array(): array
    {
        $columns = $this->resolvedColumns();

        return $this->rows->map(function ($facility) {
            $deletedBy = $facility->deletedByUser?->full_name
                ?? $facility->deletedByUser?->name
                ?? $facility->deletedByUser?->username
                ?? 'Unknown';

            $rowMap = [
                'facility' => (string) ($facility->name ?? ''),
                'type' => (string) ($facility->type ?? ''),
                'status' => (string) ($facility->status ?? ''),
                'barangay' => (string) ($facility->barangay ?? ''),
                'archive_reason' => (string) ($facility->archive_reason ?? ''),
                'deleted_by' => (string) $deletedBy,
                'archived_at' => $facility->deleted_at ? $facility->deleted_at->format('Y-m-d H:i:s') : '',
            ];

            return collect($columns)
                ->map(fn (string $key) => $rowMap[$key] ?? '')
                ->values()
                ->all();
        })->values()->all();
    }

    private function resolvedColumns(): array
    {
        $allowed = array_keys($this->columnLabels());
        $selected = array_values(array_intersect($allowed, $this->columns));

        return $selected !== [] ? $selected : $allowed;
    }

    private function columnLabels(): array
    {
        return [
            'facility' => 'Facility',
            'type' => 'Type',
            'status' => 'Status',
            'barangay' => 'Barangay',
            'archive_reason' => 'Archive Reason',
            'deleted_by' => 'Deleted By',
            'archived_at' => 'Archived At',
        ];
    }
}
