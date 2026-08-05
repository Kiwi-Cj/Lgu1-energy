<?php

namespace App\Support;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;

final class BaselineResolver
{
    public static function forRecord(EnergyRecord $record, ?Facility $facility = null): ?float
    {
        if (is_numeric($record->baseline_kwh)) {
            return self::normalize($record->baseline_kwh);
        }

        $meter = $record->relationLoaded('meter')
            ? $record->meter
            : $record->meter()->first();

        return self::forFacility($facility ?? $record->facility, $meter);
    }

    public static function forFacility(?Facility $facility, ?FacilityMeter $meter = null): ?float
    {
        if ($meter && is_numeric($meter->baseline_kwh)) {
            return self::normalize($meter->baseline_kwh);
        }

        if (! $facility) {
            return null;
        }

        $profile = $facility->relationLoaded('energyProfiles')
            ? $facility->energyProfiles->sortByDesc('id')->first()
            : $facility->energyProfiles()->latest()->first();

        if ($profile && is_numeric($profile->baseline_kwh)) {
            return self::normalize($profile->baseline_kwh);
        }

        return is_numeric($facility->baseline_kwh)
            ? self::normalize($facility->baseline_kwh)
            : null;
    }

    private static function normalize(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
