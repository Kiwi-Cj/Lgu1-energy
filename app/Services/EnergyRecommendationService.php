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
        $trendPercent = $this->toFloat($context['trend_percent'] ?? null);
        $actualKwh = $this->toFloat($context['actual_kwh'] ?? null);
        $baselineKwh = $this->toFloat($context['baseline_kwh'] ?? null);

        $primary = match ($alertLevel) {
            'critical' => 'Energy use is at a critical level. Validate meter readings, inspect HVAC and lighting runtime today, and enforce immediate load reduction in off-hours.',
            'very high' => 'Energy use is very high. Prioritize a rapid load audit for major equipment and tighten operating schedules for high-consumption zones.',
            'high' => 'Energy use is above expected trend. Compare occupancy against operating hours and optimize HVAC setpoints and lighting controls.',
            'warning' => 'Energy use is rising. Monitor daily usage, remove avoidable standby loads, and check recent operational changes.',
            'normal' => 'Energy trend is stable. Keep current controls and continue routine monitoring to sustain performance.',
            default => 'Not enough historical data for a full trend recommendation. Add at least 3 consecutive monthly records for the same main meter.',
        };

        $deltaLine = '';
        if ($actualKwh !== null && $baselineKwh !== null && $baselineKwh > 0) {
            $delta = $actualKwh - $baselineKwh;
            $direction = $delta >= 0 ? 'above' : 'below';
            $deltaAbs = abs($delta);
            $deltaPct = abs(($delta / $baselineKwh) * 100);
            $deltaLine = ' Current month is '
                . number_format($deltaAbs, 2)
                . ' kWh ('
                . number_format($deltaPct, 2)
                . '%) '
                . $direction
                . ' baseline.';
        }

        $trendLine = '';
        if ($trendPercent !== null) {
            $trendLine = ' Trend change is ' . ($trendPercent >= 0 ? '+' : '') . number_format($trendPercent, 2) . '%.';
        }

        return trim($primary . $deltaLine . $trendLine);
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
            . ' Start from this baseline alert/recommendation and improve only when strongly justified: '
            . json_encode([
                'alert_level' => $fallbackAlertLevel,
                'recommendation' => $fallbackRecommendation,
            ], JSON_UNESCAPED_SLASHES)
            . '. Keep recommendation to 2 concise sentences.';
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
