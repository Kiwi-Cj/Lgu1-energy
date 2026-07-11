<?php

namespace App\Console\Commands;

use App\Models\Submeter;
use App\Models\SubmeterReading;
use App\Services\SubmeterBaselineAlertService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Repositories\MemoryRepository;
use Throwable;

class ListenForSubmeterMqttReadings extends Command
{
    protected $signature = 'mqtt:submeter-listen {--topic= : Override the MQTT topic filter}';

    protected $description = 'Listen for submeter telemetry on MQTT and store readings in the database';

    public function __construct(private readonly SubmeterBaselineAlertService $baselineService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $host = (string) config('services.mqtt.host', '127.0.0.1');
        $port = (int) config('services.mqtt.port', 1883);
        $clientId = (string) config('services.mqtt.client_id', 'lgu-energy-laravel-subscriber');
        $topic = trim((string) ($this->option('topic') ?: config('services.mqtt.topic', 'lgu/submeters/+/telemetry')));
        $qos = (int) config('services.mqtt.qos', 0);

        $mqtt = new MqttClient(
            $host,
            $port,
            $clientId,
            MqttClient::MQTT_3_1,
            new MemoryRepository()
        );

        $settings = (new ConnectionSettings())
            ->setConnectTimeout((int) config('services.mqtt.connect_timeout', 60))
            ->setSocketTimeout((int) config('services.mqtt.socket_timeout', 5))
            ->setKeepAliveInterval((int) config('services.mqtt.keep_alive', 10));

        $username = trim((string) config('services.mqtt.username', ''));
        if ($username !== '') {
            $settings = $settings->setUsername($username);
        }

        $password = (string) config('services.mqtt.password', '');
        if ($password !== '') {
            $settings = $settings->setPassword($password);
        }

        $this->info(sprintf('Connecting to MQTT broker %s:%d...', $host, $port));
        $mqtt->connect($settings, true);
        $this->info('Connected. Listening on topic filter: ' . $topic);

        $mqtt->subscribe($topic, function (string $receivedTopic, string $message) {
            try {
                $payload = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
                $result = $this->storeReading($receivedTopic, $payload);

                $this->line(sprintf(
                    '[%s] stored reading id=%d submeter=%d device=%s kwh=%.2f',
                    now()->format('Y-m-d H:i:s'),
                    $result['reading']->id,
                    $result['reading']->submeter_id,
                    (string) $result['reading']->device_id,
                    (float) $result['reading']->kwh_used
                ));
            } catch (Throwable $e) {
                Log::warning('MQTT submeter listener failed to process message', [
                    'topic' => $receivedTopic,
                    'message' => $message,
                    'error' => $e->getMessage(),
                ]);

                $this->error('Failed to process MQTT payload: ' . $e->getMessage());
            }
        }, $qos);

        while (true) {
            $mqtt->loop(true, true);
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        // Unreachable under normal operation, but kept for completeness.
        $mqtt->disconnect();

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{reading: SubmeterReading}
     */
    private function storeReading(string $topic, array $payload): array
    {
        $submeterId = (int) ($payload['submeter_id'] ?? 0);
        if ($submeterId <= 0) {
            throw new \InvalidArgumentException('submeter_id is required.');
        }

        $submeter = Submeter::with('facility')->findOrFail($submeterId);
        if (! $submeter->facility) {
            throw new \InvalidArgumentException('Selected submeter belongs to an archived facility.');
        }

        if (strtolower((string) ($submeter->status ?? '')) !== 'active') {
            throw new \InvalidArgumentException('Selected submeter is inactive.');
        }

        $periodType = strtolower((string) ($payload['period_type'] ?? 'monthly'));
        if (! in_array($periodType, ['daily', 'weekly', 'monthly'], true)) {
            $periodType = 'monthly';
        }

        $periodStartDate = (string) ($payload['period_start_date'] ?? '');
        $periodEndDate = (string) ($payload['period_end_date'] ?? '');

        if ($periodStartDate === '' && ! empty($payload['reading_month'])) {
            $month = Carbon::createFromFormat('Y-m', (string) $payload['reading_month'])->startOfMonth();
            $periodStartDate = $month->toDateString();
            $periodEndDate = $month->copy()->endOfMonth()->toDateString();
            $periodType = 'monthly';
        }

        if ($periodStartDate === '' || $periodEndDate === '') {
            throw new \InvalidArgumentException('period_start_date and period_end_date are required.');
        }

        $readingStart = (float) ($payload['reading_start_kwh'] ?? 0);
        $readingEnd = (float) ($payload['reading_end_kwh'] ?? 0);
        if ($readingEnd < $readingStart) {
            throw new \InvalidArgumentException('reading_end_kwh must be greater than or equal to reading_start_kwh.');
        }

        $duplicate = SubmeterReading::query()
            ->where('submeter_id', $submeter->id)
            ->where('period_type', $periodType)
            ->whereDate('period_start_date', $periodStartDate)
            ->whereDate('period_end_date', $periodEndDate)
            ->exists();

        if ($duplicate) {
            throw new \InvalidArgumentException('A reading for the same submeter period already exists.');
        }

        $reading = SubmeterReading::create([
            'submeter_id' => $submeter->id,
            'period_type' => $periodType,
            'period_start_date' => $periodStartDate,
            'period_end_date' => $periodEndDate,
            'reading_start_kwh' => $readingStart,
            'reading_end_kwh' => $readingEnd,
            'operating_days' => isset($payload['operating_days']) ? (int) $payload['operating_days'] : null,
            'input_source' => 'iot',
            'device_id' => (string) ($payload['device_id'] ?? 'mqtt-device'),
            'received_at' => now(),
            'approved_at' => now(),
        ]);

        $this->baselineService->processReading($reading->fresh(['submeter.facility']));

        return ['reading' => $reading];
    }
}
