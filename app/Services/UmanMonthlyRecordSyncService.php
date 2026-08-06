<?php

namespace App\Services;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UmanMonthlyRecordSyncService
{
    /**
     * @return array{success: bool, created: int, updated: int, skipped: int, errors: array<int, string>, error: ?string}
     */
    public function sync(?int $year = null, ?int $month = null): array
    {
        $result = [
            'success' => false,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'error' => null,
        ];

        $url = trim((string) config('services.uman_monthly_records.url', ''));
        $key = trim((string) config('services.uman_monthly_records.key', ''));
        if ($url === '' || $key === '') {
            $result['error'] = 'UMAN monthly-record sync is not configured.';
            $this->rememberStatus('not_configured', $result);

            return $result;
        }

        $page = 1;
        do {
            $query = ['page' => $page, 'per_page' => 200];
            if ($year !== null) {
                $query['year'] = $year;
            }
            if ($month !== null) {
                $query['month'] = $month;
            }

            try {
                $response = Http::acceptJson()
                    ->withHeaders(['X-API-Key' => $key])
                    ->timeout(30)
                    ->retry(2, 250)
                    ->get($url, $query);
            } catch (\Throwable $e) {
                $result['error'] = 'UMAN feed request failed: '.$e->getMessage();
                $this->rememberStatus('error', $result);

                return $result;
            }

            if (! $response->successful() || $response->json('success') !== true) {
                $result['error'] = 'UMAN feed returned HTTP '.$response->status()
                    .($response->json('error') ? ': '.$response->json('error') : '');
                $this->rememberStatus('error', $result);

                return $result;
            }

            $rows = $response->json('data');
            if (! is_array($rows)) {
                $result['error'] = 'UMAN feed payload has no data array.';
                $this->rememberStatus('error', $result);

                return $result;
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    $result['skipped']++;
                    continue;
                }

                try {
                    $status = DB::transaction(fn () => $this->importRow($row));
                    $result[$status]++;
                } catch (\Throwable $e) {
                    $sourceId = (string) ($row['source_record_id'] ?? 'unknown');
                    $result['errors'][] = "{$sourceId}: {$e->getMessage()}";
                    $result['skipped']++;
                    Log::warning('Unable to import UMAN monthly energy record', [
                        'source_record_id' => $sourceId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $lastPage = max(1, (int) $response->json('meta.last_page', 1));
            $page++;
        } while ($page <= $lastPage);

        $result['success'] = true;
        $this->rememberStatus($result['errors'] === [] ? 'connected' : 'partial', $result);
        Log::info('UMAN monthly energy sync completed', $result);

        return $result;
    }

    private function rememberStatus(string $state, array $result): void
    {
        $previous = Cache::get('integrations.uman_monthly_records', []);

        Cache::forever('integrations.uman_monthly_records', [
            'state' => $state,
            'last_attempt_at' => now()->toIso8601String(),
            'last_success_at' => $state === 'connected'
                ? now()->toIso8601String()
                : ($previous['last_success_at'] ?? null),
            'created' => (int) ($result['created'] ?? 0),
            'updated' => (int) ($result['updated'] ?? 0),
            'skipped' => (int) ($result['skipped'] ?? 0),
            'message' => $result['error']
                ?? (($result['errors'] ?? []) !== [] ? implode(' | ', $result['errors']) : 'UMAN records synchronized successfully.'),
        ]);
    }

    private function importRow(array $row): string
    {
        $sourceId = trim((string) ($row['source_record_id'] ?? ''));
        $facilityKey = trim((string) ($row['facility_key'] ?? ''));
        $facilityName = trim((string) ($row['facility_name'] ?? ''));
        $cprfFacilityId = (int) ($row['cprf_facility_id'] ?? 0);
        $year = (int) ($row['year'] ?? 0);
        $month = (int) ($row['month'] ?? 0);

        if ($sourceId === '' || $facilityKey === '' || $cprfFacilityId <= 0 || $year < 2000 || $month < 1 || $month > 12) {
            throw new \InvalidArgumentException('Invalid source identity, facility, or billing period.');
        }
        if (! is_numeric($row['consumption_kwh'] ?? null) || (float) $row['consumption_kwh'] < 0) {
            throw new \InvalidArgumentException('consumption_kwh must be a non-negative number.');
        }

        $facility = Facility::query()
            ->where('source', 'cprf')
            ->where('external_ref', $cprfFacilityId)
            ->first();
        if (! $facility) {
            throw new \RuntimeException(
                "No CPRF-mirrored Energy facility matches CPRF facility {$cprfFacilityId}. Run energy:sync-cprf-facilities first."
            );
        }

        $meter = FacilityMeter::withTrashed()->firstOrNew([
            'facility_id' => $facility->id,
            'meter_number' => 'UMAN-'.strtoupper(substr(hash('sha256', $facilityKey), 0, 16)),
        ]);
        $meter->fill([
            'meter_name' => 'CPRF Integrated Main Meter',
            'meter_type' => 'main',
            'location' => trim((string) ($row['location'] ?? '')) ?: $facilityName,
            'status' => 'active',
            'multiplier' => 1,
            'notes' => 'Automatically managed by the UMAN monthly-record integration.',
            'approved_at' => $meter->approved_at ?? now(),
        ]);
        $meter->save();
        if ($meter->trashed()) {
            $meter->restore();
        }

        $record = EnergyRecord::withTrashed()
            ->where('external_source', 'uman_cprf')
            ->where('external_record_id', $sourceId)
            ->first();
        $status = $record ? 'updated' : 'created';
        $record ??= new EnergyRecord;

        $actualKwh = (float) $row['consumption_kwh'];
        $cost = is_numeric($row['cost'] ?? null) ? (float) $row['cost'] : null;
        $rate = is_numeric($row['rate_per_kwh'] ?? null)
            ? (float) $row['rate_per_kwh']
            : ($cost !== null && $actualKwh > 0 ? round($cost / $actualKwh, 4) : null);
        $recordedAt = ! empty($row['recorded_at'])
            ? Carbon::parse((string) $row['recorded_at'])
            : Carbon::create($year, $month, 1);

        $baseline = EnergyRecord::query()
            ->where('meter_id', $meter->id)
            ->where(function ($query) use ($year, $month) {
                $query->where('year', '<', $year)
                    ->orWhere(fn ($period) => $period->where('year', $year)->where('month', '<', $month));
            })
            ->latest('year')
            ->latest('month')
            ->value('actual_kwh');
        $baseline = is_numeric($baseline) ? (float) $baseline : null;

        $record->fill([
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => $year,
            'month' => $month,
            'day' => $recordedAt->day,
            'actual_kwh' => $actualKwh,
            'energy_cost' => $cost,
            'rate_per_kwh' => $rate,
            'recorded_by' => null,
            'recorded_by_name' => 'CPRF via UMAN',
            'input_source' => 'cprf',
            'external_source' => 'uman_cprf',
            'external_record_id' => $sourceId,
            'review_status' => 'approved',
            'reviewed_at' => now(),
            'baseline_kwh' => $baseline,
            'deviation' => EnergyRecord::calculateDeviation($actualKwh, $baseline),
            'alert' => EnergyRecord::resolveAlertLevel(
                EnergyRecord::calculateDeviation($actualKwh, $baseline),
                $baseline
            ),
        ]);
        $record->save();
        if ($record->trashed()) {
            $record->restore();
        }

        return $status;
    }
}
