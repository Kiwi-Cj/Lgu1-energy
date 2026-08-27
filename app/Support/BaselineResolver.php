<?php

namespace App\Support;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;

final class BaselineResolver
{
    public static function forRecord(EnergyRecord $record, ?Facility $facility = null): ?float
    {
        $recordBaseline = self::normalize($record->baseline_kwh);
        if ($recordBaseline !== null) {
            return $recordBaseline;
        }

        $meter = $record->relationLoaded('meter')
            ? $record->meter
            : $record->meter()->first();

        return self::forFacility($facility ?? $record->facility, $meter);
    }

    public static function forFacility(?Facility $facility, ?FacilityMeter $meter = null): ?float
    {
        $meterBaseline = self::normalize($meter?->baseline_kwh);
        if ($meterBaseline !== null) {
            return $meterBaseline;
        }

        if (! $facility) {
            return null;
        }

        $profile = $facility->relationLoaded('energyProfiles')
            ? $facility->energyProfiles->sortByDesc('id')->first()
            : $facility->energyProfiles()->latest()->first();

        $profileBaseline = self::normalize($profile?->baseline_kwh);
        if ($profileBaseline !== null) {
            return $profileBaseline;
        }

        return self::normalize($facility->baseline_kwh);
    }

    private static function normalize(mixed $value): ?float
    {
        if (! is_numeric($value) || (float) $value <= 0) {
            return null;
        }

        return round((float) $value, 2);
    }
}
