<?php

namespace Database\Seeders;

use App\Models\FacilityMeter;
use App\Models\Submeter;
use App\Models\SubmeterEquipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SubmeterEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('submeter_equipments')) {
            return;
        }

        $hasMeterScope = Schema::hasColumn('submeter_equipments', 'meter_scope');
        $hasMainMeterLink = Schema::hasColumn('submeter_equipments', 'facility_meter_id');

        $this->seedSubmeterEquipment($hasMeterScope, $hasMainMeterLink);
        $this->seedMainMeterEquipment($hasMeterScope, $hasMainMeterLink);
    }

    private function seedSubmeterEquipment(bool $hasMeterScope, bool $hasMainMeterLink): void
    {
        $submeters = Submeter::query()
            ->with('facility:id,name')
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'facility_id', 'submeter_name']);

        if ($submeters->isEmpty()) {
            return;
        }

        $managedNames = collect($this->allSubmeterTemplatePool())
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        foreach ($submeters as $submeter) {
            $submeterId = (int) $submeter->id;
            $desiredTemplates = $this->resolveSubmeterTemplates(
                (string) $submeter->submeter_name,
                (string) ($submeter->facility?->name ?? '')
            );
            $desiredNames = collect($desiredTemplates)->pluck('name')->values()->all();

            // Keep seeder idempotent by syncing only known seeded equipment names for this meter.
            $cleanup = SubmeterEquipment::query()
                ->where('submeter_id', $submeterId)
                ->whereIn('equipment_name', $managedNames);

            if ($hasMeterScope) {
                $cleanup->where('meter_scope', 'sub');
            }

            if (! empty($desiredNames)) {
                $cleanup->whereNotIn('equipment_name', $desiredNames);
            }

            $cleanup->delete();

            foreach ($desiredTemplates as $i => $template) {
                $variation = (($submeterId + $i) % 5) * 0.03;
                $quantity = (int) max(1, round(((int) $template['quantity']) * (1 + ($variation / 2))));
                $watts = round(((float) $template['watts']) * (1 + $variation), 2);

                $values = [
                    'submeter_id' => $submeterId,
                    'equipment_name' => $template['name'],
                    'quantity' => $quantity,
                    'rated_watts' => $watts,
                    'operating_hours_per_day' => round((float) $template['hours'], 2),
                    'operating_days_per_month' => min(31, max(1, (int) $template['days'])),
                ];

                if ($hasMeterScope) {
                    $values['meter_scope'] = 'sub';
                }

                if ($hasMainMeterLink) {
                    $values['facility_meter_id'] = null;
                }

                $identity = [
                    'submeter_id' => $submeterId,
                    'equipment_name' => $template['name'],
                ];
                if ($hasMeterScope) {
                    $identity['meter_scope'] = 'sub';
                }

                SubmeterEquipment::query()->updateOrCreate($identity, $values);
            }
        }
    }

    private function seedMainMeterEquipment(bool $hasMeterScope, bool $hasMainMeterLink): void
    {
        if (! $hasMeterScope || ! $hasMainMeterLink) {
            return;
        }

        $mainMeters = FacilityMeter::query()
            ->with('facility:id,name')
            ->where('meter_type', 'main')
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->orderBy('id')
            ->get(['id', 'facility_id', 'meter_name']);

        if ($mainMeters->isEmpty()) {
            return;
        }

        $managedNames = collect($this->allMainMeterTemplatePool())
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        foreach ($mainMeters as $mainMeter) {
            $meterId = (int) $mainMeter->id;
            $desiredTemplates = $this->resolveMainMeterTemplates(
                (string) ($mainMeter->facility?->name ?? ''),
                (string) $mainMeter->meter_name
            );
            $desiredNames = collect($desiredTemplates)->pluck('name')->values()->all();

            SubmeterEquipment::query()
                ->where('meter_scope', 'main')
                ->where('facility_meter_id', $meterId)
                ->whereIn('equipment_name', $managedNames)
                ->when(! empty($desiredNames), fn ($query) => $query->whereNotIn('equipment_name', $desiredNames))
                ->delete();

            foreach ($desiredTemplates as $i => $template) {
                $variation = (($meterId + $i) % 4) * 0.04;

                SubmeterEquipment::query()->updateOrCreate(
                    [
                        'meter_scope' => 'main',
                        'facility_meter_id' => $meterId,
                        'equipment_name' => $template['name'],
                    ],
                    [
                        'meter_scope' => 'main',
                        'submeter_id' => null,
                        'facility_meter_id' => $meterId,
                        'equipment_name' => $template['name'],
                        'quantity' => (int) max(1, round(((int) $template['quantity']) * (1 + ($variation / 2)))),
                        'rated_watts' => round(((float) $template['watts']) * (1 + $variation), 2),
                        'operating_hours_per_day' => round((float) $template['hours'], 2),
                        'operating_days_per_month' => (int) $template['days'],
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array{name: string, quantity: int, watts: float, hours: float, days: int}>
     */
    private function resolveSubmeterTemplates(string $submeterName, string $facilityName): array
    {
        $haystack = strtolower(trim($submeterName . ' ' . $facilityName));

        if ($this->containsAny($haystack, ['aircon', 'hvac', 'plant', 'chiller', 'cooling'])) {
            return [
                ['name' => 'Air Conditioning Unit', 'quantity' => 6, 'watts' => 1380.0, 'hours' => 10.0, 'days' => 25],
                ['name' => 'Ventilation and Exhaust Fans', 'quantity' => 10, 'watts' => 120.0, 'hours' => 11.0, 'days' => 25],
                ['name' => 'Water Booster Pump', 'quantity' => 2, 'watts' => 1600.0, 'hours' => 4.0, 'days' => 24],
            ];
        }

        if ($this->containsAny($haystack, ['light', 'lighting', 'hall', 'arena', 'perimeter'])) {
            return [
                ['name' => 'LED Lighting Circuit', 'quantity' => 48, 'watts' => 18.0, 'hours' => 11.0, 'days' => 26],
                ['name' => 'Ventilation and Exhaust Fans', 'quantity' => 6, 'watts' => 90.0, 'hours' => 9.0, 'days' => 24],
                ['name' => 'Printer and Office Peripherals', 'quantity' => 3, 'watts' => 120.0, 'hours' => 4.0, 'days' => 22],
            ];
        }

        if ($this->containsAny($haystack, ['market', 'stall', 'cold storage'])) {
            return [
                ['name' => 'Refrigeration Compressor Set', 'quantity' => 3, 'watts' => 1850.0, 'hours' => 12.0, 'days' => 27],
                ['name' => 'Ventilation and Exhaust Fans', 'quantity' => 9, 'watts' => 125.0, 'hours' => 11.0, 'days' => 27],
                ['name' => 'LED Lighting Circuit', 'quantity' => 34, 'watts' => 20.0, 'hours' => 12.0, 'days' => 27],
            ];
        }

        if ($this->containsAny($haystack, ['laboratory', 'lab', 'pharmacy', 'health', 'clinic', 'outpatient'])) {
            return [
                ['name' => 'Laboratory Cold Storage Unit', 'quantity' => 2, 'watts' => 980.0, 'hours' => 24.0, 'days' => 30],
                ['name' => 'Air Conditioning Unit', 'quantity' => 4, 'watts' => 1280.0, 'hours' => 10.0, 'days' => 26],
                ['name' => 'Desktop and IT Cluster', 'quantity' => 8, 'watts' => 210.0, 'hours' => 8.0, 'days' => 24],
            ];
        }

        if ($this->containsAny($haystack, ['office', 'engineering', 'library', 'museum', 'admin'])) {
            return [
                ['name' => 'Desktop and IT Cluster', 'quantity' => 16, 'watts' => 230.0, 'hours' => 9.0, 'days' => 24],
                ['name' => 'Printer and Office Peripherals', 'quantity' => 8, 'watts' => 150.0, 'hours' => 5.0, 'days' => 23],
                ['name' => 'LED Lighting Circuit', 'quantity' => 26, 'watts' => 18.0, 'hours' => 10.0, 'days' => 25],
            ];
        }

        return [
            ['name' => 'LED Lighting Circuit', 'quantity' => 28, 'watts' => 18.0, 'hours' => 10.0, 'days' => 26],
            ['name' => 'Air Conditioning Unit', 'quantity' => 4, 'watts' => 1200.0, 'hours' => 9.0, 'days' => 24],
            ['name' => 'Desktop and IT Cluster', 'quantity' => 12, 'watts' => 220.0, 'hours' => 9.0, 'days' => 24],
        ];
    }

    /**
     * @return array<int, array{name: string, quantity: int, watts: float, hours: float, days: int}>
     */
    private function resolveMainMeterTemplates(string $facilityName, string $meterName): array
    {
        $haystack = strtolower(trim($facilityName . ' ' . $meterName));

        if ($this->containsAny($haystack, ['market', 'terminal', 'transport'])) {
            return [
                ['name' => 'Main Common Area Lighting', 'quantity' => 44, 'watts' => 28.0, 'hours' => 12.0, 'days' => 27],
                ['name' => 'Main Water Pump System', 'quantity' => 3, 'watts' => 2150.0, 'hours' => 5.0, 'days' => 26],
                ['name' => 'Main Cold Storage Support', 'quantity' => 2, 'watts' => 2600.0, 'hours' => 11.0, 'days' => 27],
            ];
        }

        if ($this->containsAny($haystack, ['sports', 'arena', 'gym'])) {
            return [
                ['name' => 'Main Common Area Lighting', 'quantity' => 50, 'watts' => 26.0, 'hours' => 11.0, 'days' => 26],
                ['name' => 'Main Water Pump System', 'quantity' => 3, 'watts' => 2050.0, 'hours' => 4.0, 'days' => 25],
                ['name' => 'Main Arena Auxiliary Loads', 'quantity' => 1, 'watts' => 3800.0, 'hours' => 6.0, 'days' => 22],
            ];
        }

        if ($this->containsAny($haystack, ['health', 'hospital', 'clinic', 'rural health'])) {
            return [
                ['name' => 'Main Common Area Lighting', 'quantity' => 30, 'watts' => 24.0, 'hours' => 12.0, 'days' => 30],
                ['name' => 'Main Water Pump System', 'quantity' => 2, 'watts' => 1900.0, 'hours' => 4.0, 'days' => 30],
                ['name' => 'Main Medical Support Loads', 'quantity' => 1, 'watts' => 3200.0, 'hours' => 9.0, 'days' => 30],
            ];
        }

        if ($this->containsAny($haystack, ['library', 'museum', 'college', 'education'])) {
            return [
                ['name' => 'Main Common Area Lighting', 'quantity' => 36, 'watts' => 22.0, 'hours' => 11.0, 'days' => 25],
                ['name' => 'Main Water Pump System', 'quantity' => 2, 'watts' => 1750.0, 'hours' => 3.0, 'days' => 24],
                ['name' => 'Main ICT and Server Backbone', 'quantity' => 1, 'watts' => 2100.0, 'hours' => 10.0, 'days' => 25],
            ];
        }

        return [
            ['name' => 'Main Common Area Lighting', 'quantity' => 32, 'watts' => 24.0, 'hours' => 11.0, 'days' => 26],
            ['name' => 'Main Water Pump System', 'quantity' => 2, 'watts' => 1850.0, 'hours' => 3.0, 'days' => 24],
            ['name' => 'Main Elevator and Lift Motor', 'quantity' => 1, 'watts' => 2800.0, 'hours' => 5.0, 'days' => 24],
        ];
    }

    /**
     * @return array<int, array{name: string, quantity: int, watts: float, hours: float, days: int}>
     */
    private function allSubmeterTemplatePool(): array
    {
        return [
            ['name' => 'LED Lighting Circuit', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Air Conditioning Unit', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Desktop and IT Cluster', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Ventilation and Exhaust Fans', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Printer and Office Peripherals', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Water Booster Pump', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Refrigeration Compressor Set', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Laboratory Cold Storage Unit', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
        ];
    }

    /**
     * @return array<int, array{name: string, quantity: int, watts: float, hours: float, days: int}>
     */
    private function allMainMeterTemplatePool(): array
    {
        return [
            ['name' => 'Main Common Area Lighting', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Main Water Pump System', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Main Elevator and Lift Motor', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Main Cold Storage Support', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Main Arena Auxiliary Loads', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Main Medical Support Loads', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
            ['name' => 'Main ICT and Server Backbone', 'quantity' => 0, 'watts' => 0.0, 'hours' => 0.0, 'days' => 0],
        ];
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, strtolower((string) $needle))) {
                return true;
            }
        }

        return false;
    }
}
