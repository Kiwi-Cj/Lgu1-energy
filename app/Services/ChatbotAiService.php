<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotAiService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.ai_recommendations.enabled', false)
            && strtolower(trim((string) config('services.ai_recommendations.provider', 'rules'))) === 'openai'
            && trim((string) config('services.ai_recommendations.openai.key')) !== '';
    }

    public function generateReply(string $message, array $context = []): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        $apiKey = trim((string) config('services.ai_recommendations.openai.key'));
        $baseUrl = rtrim((string) config('services.ai_recommendations.openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = trim((string) config('services.ai_recommendations.openai.model', 'gpt-4o-mini'));

        try {
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
                            'content' => "You are an AI Assistant for the Energy Monitoring, Efficiency, and Conservation System. Your role is to help users understand the system's features, explain energy-related concepts, interpret monitoring data, provide energy-saving recommendations, and assist with using the system. Answer questions about energy consumption, energy efficiency, conservation practices, reports, alerts, meters, facilities, recommendations, user account management, exports, and dashboard features. If a user asks something unrelated to the system or energy management, politely explain that you are designed to assist only with this system and energy-related topics.",
                        ],
                        [
                            'role' => 'system',
                            'content' => "This system includes modules for dashboard, reports, alerts, meters, facilities, recommendations, user account, and exports. Users may ask about workflows such as generating a report, viewing history, registering a device, comparing consumption, and analyzing alerts.\nExample questions your chatbot should answer:\n- How do I use the dashboard?\n- How can I register a new device?\n- How do I generate an energy report?\n- How can I export my reports?\n- How do I view energy usage history?\n- How do I compare consumption across months?\n- What is energy monitoring?\n- What does kWh mean?\n- How is energy consumption calculated?\n- What is real-time monitoring?\n- Why is today's energy consumption higher than yesterday?\n- What is energy efficiency?\n- How can I improve my energy efficiency?\n- What does the Energy Efficiency Score mean?\n- Why is my efficiency score low?\n- How can I reduce electricity consumption?\n- What are the best energy-saving practices?\n- How can I lower my electricity bill?\n- Which appliances consume the most electricity?\n- Why did I receive a High Energy Usage alert?\n- What should I do when energy consumption exceeds the limit?\n- What does a Critical Energy Alert mean?\n- How do I interpret my energy usage report?\n- What is peak energy usage?\n- How can I compare monthly consumption?\n- What does the estimated electricity cost represent?",
                        ],
                        [
                            'role' => 'system',
                            'content' => 'Current system context: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->sanitizeMessage($message),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('AI chatbot request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return '';
            }

            return trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        } catch (\Throwable $e) {
            Log::warning('AI chatbot request exception', [
                'message' => $e->getMessage(),
            ]);
        }

        return '';
    }

    private function timeoutSeconds(): int
    {
        $timeout = (int) config('services.ai_recommendations.openai.timeout', 10);
        return $timeout > 0 ? $timeout : 10;
    }

    private function sanitizeMessage(string $message): string
    {
        return trim(preg_replace('/\s+/', ' ', $message));
    }
}
