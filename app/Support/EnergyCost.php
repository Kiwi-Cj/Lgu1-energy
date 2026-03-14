<?php

namespace App\Support;

final class EnergyCost
{
    public const DEFAULT_RATE_PER_KWH = 12.0;

    public static function ratePerKwh(mixed $record, float $defaultRate = self::DEFAULT_RATE_PER_KWH): float
    {
        $rawRate = null;

        if (is_array($record)) {
            $rawRate = $record['rate_per_kwh'] ?? null;
        } elseif (is_object($record)) {
            $rawRate = $record->rate_per_kwh ?? null;
        }

        return (is_numeric($rawRate) && $rawRate !== '')
            ? (float) $rawRate
            : $defaultRate;
    }

    public static function actualKwh(mixed $record): float
    {
        $rawKwh = null;

        if (is_array($record)) {
            $rawKwh = $record['actual_kwh'] ?? null;
        } elseif (is_object($record)) {
            $rawKwh = $record->actual_kwh ?? null;
        }

        return (is_numeric($rawKwh) && $rawKwh !== '')
            ? (float) $rawKwh
            : 0.0;
    }

    public static function cost(
        mixed $record,
        ?float $resolvedRate = null,
        float $defaultRate = self::DEFAULT_RATE_PER_KWH
    ): float {
        $rate = $resolvedRate ?? self::ratePerKwh($record, $defaultRate);

        return self::actualKwh($record) * $rate;
    }
}
