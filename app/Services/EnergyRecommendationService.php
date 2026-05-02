<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnergyRecommendationService
{
    public function generateFacilityRecommendation(array $context, bool $allowExternalAi = true): string
    {
        return (string) ($this->generateFacilityInsight($context, $allowExternalAi)['recommendation'] ?? '');
    }

    public function generateFacilityInsight(array $context, bool $allowExternalAi = true): array
    {
        $fallbackAlertLevel = $this->normalizeAlertLevel((string) ($context['alert_level'] ?? 'No Data'));
        $context['alert_level'] = $fallbackAlertLevel;
        $fallbackRecommendation = $this->buildRuleBasedRecommendation($context);
        $fallback = [
            'alert_level' => $fallbackAlertLevel,
            'recommendation' => $fallbackRecommendation,
            'source' => 'rules',
        ];

        if (! $allowExternalAi || ! $this->isEnabled() || $this->provider() !== 'openai') {
            return $fallback;
        }

        $apiKey = trim((string) config('services.ai_recommendations.openai.key'));
        if ($apiKey === '') {
            return $fallback;
        }

        try {
            $baseUrl = rtrim((string) config('services.ai_recommendations.openai.base_url', 'https://api.openai.com/v1'), '/');
            $model = trim((string) config('services.ai_recommendations.openai.model', 'gpt-4o-mini'));

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout($this->timeoutSeconds())
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'temperature' => (float) config('services.ai_recommendations.openai.temperature', 0.2),
                    'max_tokens' => (int) config('services.ai_recommendations.openai.max_tokens', 180),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an energy efficiency analyst for local government facilities. Return valid JSON only.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildInsightPrompt($context, $fallbackAlertLevel, $fallbackRecommendation),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('AI recommendation request failed', [
                    'status' => $response->status(),
                ]);
                return $fallback;
            }

            $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            $parsed = $this->parseInsightResponse($content);
            if ($parsed === null) {
                return $fallback;
            }

            return [
                'alert_level' => $parsed['alert_level'],
                'recommendation' => $parsed['recommendation'],
                'source' => 'ai',
            ];
        } catch (\Throwable $e) {
            Log::warning('AI recommendation request exception', [
                'message' => $e->getMessage(),
            ]);
        }

        return $fallback;
    }

    private function buildRuleBasedRecommendation(array $context): string
    {
        $alertLevel = strtolower(trim((string) ($context['alert_level'] ?? 'No Data')));
        $facilityType = trim((string) ($context['facility_type'] ?? ''));
        $trendPercent = $this->toFloat($context['trend_percent'] ?? null);
        $actualKwh = $this->toFloat($context['actual_kwh'] ?? null);
        $baselineKwh = $this->toFloat($context['baseline_kwh'] ?? null);
        $lastMaintenance = trim((string) ($context['last_maintenance'] ?? ''));
        $nextMaintenance = trim((string) ($context['next_maintenance'] ?? ''));

        $primary = match ($alertLevel) {
            'critical' => 'Critical overuse detected. Treat this as an immediate operating issue and validate the highest-load circuits today.',
            'very high' => 'Very high energy use detected. Run a same-day load audit on the largest end uses before the next occupied shift.',
            'high' => 'Energy use is materially above expected levels. Confirm that operating schedules and major equipment runtime still match actual demand.',
            'warning' => 'Energy use is rising above normal variation. Tighten daily monitoring before the increase becomes a sustained cost issue.',
            'normal' => 'Energy performance is currently within the expected range. Keep the present controls in place and watch for early drift.',
            default => 'Not enough historical data for a full trend recommendation. Add at least 3 consecutive monthly records for the same main meter.',
        };

        $deltaLine = null;
        if ($actualKwh !== null && $baselineKwh !== null && $baselineKwh > 0) {
            $delta = $actualKwh - $baselineKwh;
            $direction = $delta >= 0 ? 'above' : 'below';
            $deltaAbs = abs($delta);
            $deltaPct = abs(($delta / $baselineKwh) * 100);
            $deltaLine = 'Current month is '
                . number_format($deltaAbs, 2)
                . ' kWh ('
                . number_format($deltaPct, 2)
                . '%) '
                . $direction
                . ' baseline.';
        }

        $trendLine = null;
        if ($trendPercent !== null) {
            $trendLine = 'Recent trend is ' . ($trendPercent >= 0 ? '+' : '') . number_format($trendPercent, 2) . '%.';
        }

        $facilityFocusLine = $this->buildFacilityFocusLine($facilityType, $alertLevel);
        $maintenanceLine = $this->buildMaintenanceLine($lastMaintenance, $nextMaintenance, $alertLevel);

        return implode(' ', array_filter([
            $primary,
            $facilityFocusLine,
            $deltaLine,
            $trendLine,
            $maintenanceLine,
        ]));
    }

    private function buildInsightPrompt(array $context, string $fallbackAlertLevel, string $fallbackRecommendation): string
    {
        $sanitized = [
            'facility_name' => (string) ($context['facility_name'] ?? ''),
            'facility_type' => (string) ($context['facility_type'] ?? ''),
            'alert_level' => (string) ($context['alert_level'] ?? ''),
            'trend_percent' => $this->toFloat($context['trend_percent'] ?? null),
            'actual_kwh' => $this->toFloat($context['actual_kwh'] ?? null),
            'baseline_kwh' => $this->toFloat($context['baseline_kwh'] ?? null),
            'floor_area' => $this->toFloat($context['floor_area'] ?? null),
            'last_maintenance' => (string) ($context['last_maintenance'] ?? ''),
            'next_maintenance' => (string) ($context['next_maintenance'] ?? ''),
        ];

        return 'Analyze this facility context and return JSON only with keys "alert_level" and "recommendation": '
            . json_encode($sanitized, JSON_UNESCAPED_SLASHES)
            . '. Allowed alert levels: Critical, Very High, High, Warning, Normal, No Data.'
            . ' Use this baseline only as a safety fallback, not as wording to copy: '
            . json_encode([
                'alert_level' => $fallbackAlertLevel,
                'recommendation' => $fallbackRecommendation,
            ], JSON_UNESCAPED_SLASHES)
            . '. Write 2 or 3 concise sentences.'
            . ' Sentence 1 should explain the issue using the supplied numbers when available.'
            . ' Sentence 2 should give facility-type-specific checks or load controls.'
            . ' Sentence 3 may mention maintenance timing or schedule follow-up.'
            . ' Avoid generic filler and do not repeat the baseline wording verbatim.';
    }

    private function buildFacilityFocusLine(string $facilityType, string $alertLevel): ?string
    {
        if ($alertLevel === 'no data') {
            return null;
        }

        $type = strtolower($facilityType);

        return match (true) {
            str_contains($type, 'police') => 'For police operations, check dispatch-room cooling, perimeter and security lighting, workstation clusters, and radio or battery charging loads.',
            str_contains($type, 'health'), str_contains($type, 'hospital'), str_contains($type, 'clinic') => 'For health facilities, review cooling for treatment rooms, refrigeration loads, sterilization equipment, and after-hours lighting.',
            str_contains($type, 'school'), str_contains($type, 'campus') => 'For school facilities, verify classroom HVAC, computer labs, pumps, and exterior lighting schedules against actual occupancy.',
            str_contains($type, 'office'), str_contains($type, 'municipal'), str_contains($type, 'city hall'), str_contains($type, 'barangay') => 'For office facilities, focus on air-conditioning zones, printer and server rooms, pantry equipment, and lighting left on after office hours.',
            str_contains($type, 'gym'), str_contains($type, 'sports') => 'For sports facilities, inspect court lighting, ventilation fans, sound systems, and event-related loads outside booked hours.',
            str_contains($type, 'water'), str_contains($type, 'pump') => 'For pump and water facilities, compare pump runtime, pressure settings, and leakage-related cycling against normal operation.',
            default => 'Check HVAC runtime, lighting schedules, and any continuously energized equipment in the highest-load areas.',
        };
    }

    private function buildMaintenanceLine(string $lastMaintenance, string $nextMaintenance, string $alertLevel): ?string
    {
        if ($alertLevel === 'no data') {
            return null;
        }

        $formattedNext = $this->formatDateLabel($nextMaintenance);
        if ($formattedNext !== null) {
            return 'Coordinate the inspection with the maintenance schedule on ' . $formattedNext . ' and confirm controls are reset after service.';
        }

        $formattedLast = $this->formatDateLabel($lastMaintenance);
        if ($formattedLast === null) {
            return 'No recent maintenance date is logged, so include thermostat settings, filters, timers, and lighting controls in the field check.';
        }

        return 'Last maintenance was logged on ' . $formattedLast . '; compare current runtime and setpoints against post-maintenance conditions.';
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.ai_recommendations.enabled', false);
    }

    private function provider(): string
    {
        return strtolower(trim((string) config('services.ai_recommendations.provider', 'rules')));
    }

    private function timeoutSeconds(): int
    {
        $timeout = (int) config('services.ai_recommendations.openai.timeout', 10);
        return $timeout > 0 ? $timeout : 10;
    }

    private function parseInsightResponse(string $content): ?array
    {
        if ($content === '') {
            return null;
        }

        $json = trim($content);
        if (str_starts_with($json, '```')) {
            $json = preg_replace('/^```(?:json)?\s*/i', '', $json) ?? $json;
            $json = preg_replace('/\s*```$/', '', $json) ?? $json;
            $json = trim($json);
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        $recommendation = trim((string) ($decoded['recommendation'] ?? ''));
        if ($recommendation === '') {
            return null;
        }

        return [
            'alert_level' => $this->normalizeAlertLevel((string) ($decoded['alert_level'] ?? 'No Data')),
            'recommendation' => $recommendation,
        ];
    }

    private function toFloat($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function formatDateLabel(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        $timestamp = strtotime($trimmed);
        if ($timestamp === false) {
            return $trimmed;
        }

        return date('M j, Y', $timestamp);
    }

    private function normalizeAlertLevel(?string $level): string
    {
        return match (strtolower(trim((string) $level))) {
            'critical' => 'Critical',
            'very high', 'very_high' => 'Very High',
            'high' => 'High',
            'warning', 'moderate' => 'Warning',
            'normal', 'low' => 'Normal',
            default => 'No Data',
        };
    }
}
