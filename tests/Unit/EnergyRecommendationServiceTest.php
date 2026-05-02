<?php

namespace Tests\Unit;

use App\Services\EnergyRecommendationService;
use Tests\TestCase;

class EnergyRecommendationServiceTest extends TestCase
{
    public function test_it_returns_no_data_message_when_trend_is_missing(): void
    {
        config([
            'services.ai_recommendations.enabled' => false,
        ]);

        $service = app(EnergyRecommendationService::class);

        $message = $service->generateFacilityRecommendation([
            'alert_level' => 'No Data',
            'trend_percent' => null,
        ]);

        $this->assertStringContainsString('Not enough historical data', $message);
    }

    public function test_it_includes_baseline_delta_for_high_alerts(): void
    {
        config([
            'services.ai_recommendations.enabled' => false,
        ]);

        $service = app(EnergyRecommendationService::class);

        $message = $service->generateFacilityRecommendation([
            'alert_level' => 'High',
            'trend_percent' => 18.45,
            'actual_kwh' => 1800,
            'baseline_kwh' => 1500,
        ]);

        $this->assertStringContainsString('above baseline', $message);
        $this->assertStringContainsString('Recent trend is +18.45%', $message);
    }

    public function test_it_can_force_rules_only_even_when_ai_is_enabled(): void
    {
        config([
            'services.ai_recommendations.enabled' => true,
            'services.ai_recommendations.provider' => 'openai',
            'services.ai_recommendations.openai.key' => 'test-key',
        ]);

        $service = app(EnergyRecommendationService::class);

        $message = $service->generateFacilityRecommendation([
            'alert_level' => 'Normal',
            'trend_percent' => 1.25,
        ], false);

        $this->assertStringContainsString('within the expected range', $message);
    }

    public function test_it_returns_alert_and_recommendation_for_rules_fallback(): void
    {
        config([
            'services.ai_recommendations.enabled' => false,
        ]);

        $service = app(EnergyRecommendationService::class);

        $insight = $service->generateFacilityInsight([
            'alert_level' => 'moderate',
            'trend_percent' => 9.25,
        ], false);

        $this->assertSame('Warning', $insight['alert_level']);
        $this->assertSame('rules', $insight['source']);
        $this->assertStringContainsString('Energy use is rising', $insight['recommendation']);
    }

    public function test_it_generates_facility_specific_rule_based_recommendation(): void
    {
        config([
            'services.ai_recommendations.enabled' => false,
        ]);

        $service = app(EnergyRecommendationService::class);

        $message = $service->generateFacilityRecommendation([
            'facility_type' => 'Police Station',
            'alert_level' => 'Critical',
            'trend_percent' => 37.10,
            'actual_kwh' => 2500,
            'baseline_kwh' => 1600,
            'last_maintenance' => '2026-01-15',
        ], false);

        $this->assertStringContainsString('dispatch-room cooling', $message);
        $this->assertStringContainsString('Last maintenance was logged on Jan 15, 2026', $message);
    }
}
