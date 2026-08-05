<?php

namespace App\Support;

final class EnergyAlertRouting
{
    public const INCIDENT = 'incident';
    public const CONSERVATION = 'conservation';
    public const MONITOR = 'monitor';

    public static function owner(?string $usageLevel, bool $hasCostRisk = false): string
    {
        $level = strtolower(trim((string) $usageLevel));

        if (in_array($level, ['critical', 'very high', 'very-high'], true)) {
            return self::INCIDENT;
        }

        if ($level === 'high' || $hasCostRisk) {
            return self::CONSERVATION;
        }

        return self::MONITOR;
    }

    public static function requiresIncident(?string $usageLevel): bool
    {
        return self::owner($usageLevel) === self::INCIDENT;
    }
}
