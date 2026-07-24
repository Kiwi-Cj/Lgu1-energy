<?php

namespace App\Services;

use App\Models\Facility;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls the CPRF (Barangay Culiat Facilities Reservation) facilities feed
 * and mirrors it into this app's facilities table as source='cprf' rows.
 *
 * CPRF is the system of record for these facilities: identity fields
 * (name, address, details, status) are overwritten on every sync and are
 * read-only in this app's UI. Energy-side data (energy profiles, meters,
 * baselines, readings) is never touched by the sync.
 *
 * Rows previously mirrored but no longer present in the feed are marked
 * status='inactive' (never deleted) so their reading history is preserved.
 */
class CprfFacilitySyncService
{
    /** CPRF feed status -> this app's facility status. */
    private const STATUS_MAP = [
        'available' => 'active',
        'maintenance' => 'maintenance',
        'offline' => 'inactive',
    ];

    /**
     * @return array{success: bool, created: int, updated: int, deactivated: int, unchanged: int, error: ?string}
     */
    public function sync(): array
    {
        $summary = ['success' => false, 'created' => 0, 'updated' => 0, 'deactivated' => 0, 'unchanged' => 0, 'error' => null];

        $feedUrl = (string) config('services.cprf_integration.facilities_feed_url', '');
        $token = (string) config('services.cprf_integration.token', '');
        if ($feedUrl === '' || $token === '') {
            $summary['error'] = 'CPRF facilities feed is not configured (set CPRF_FACILITIES_FEED_URL and CPRF_INTEGRATION_TOKEN in .env).';
            return $summary;
        }

        try {
            $response = Http::withToken($token)->acceptJson()->timeout(20)->get($feedUrl);
        } catch (\Throwable $e) {
            $summary['error'] = 'CPRF feed request failed: ' . $e->getMessage();
            return $summary;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            $summary['error'] = 'CPRF feed returned HTTP ' . $response->status()
                . ($response->json('error') ? (': ' . $response->json('error')) : '');
            return $summary;
        }

        $rows = $response->json('data');
        if (! is_array($rows)) {
            $summary['error'] = 'CPRF feed payload has no data array.';
            return $summary;
        }

        $seenRefs = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['id'], $row['name'])) {
                continue;
            }
            $externalRef = (int) $row['id'];
            $seenRefs[] = $externalRef;

            /** @var Facility $facility */
            $facility = Facility::withTrashed()
                ->where('source', 'cprf')
                ->where('external_ref', $externalRef)
                ->first();

            $identity = [
                'name' => (string) $row['name'],
                'type' => 'Public Facility',
                'address' => isset($row['location']) && $row['location'] !== null ? (string) $row['location'] : '',
                'barangay' => isset($row['barangay']) && $row['barangay'] !== null ? (string) $row['barangay'] : 'Culiat',
                'operating_hours' => isset($row['operating_hours']) && $row['operating_hours'] !== null ? (string) $row['operating_hours'] : null,
                'status' => self::STATUS_MAP[strtolower((string) ($row['status'] ?? ''))] ?? 'inactive',
            ];

            if ($facility === null) {
                Facility::create($identity + [
                    'source' => 'cprf',
                    'external_ref' => $externalRef,
                ]);
                $summary['created']++;
                continue;
            }

            if ($facility->trashed()) {
                $facility->restore();
            }

            $facility->fill($identity);
            if ($facility->isDirty()) {
                $facility->save();
                $summary['updated']++;
            } else {
                $summary['unchanged']++;
            }
        }

        // Mirrored rows missing from the feed: deactivate, never delete.
        $missing = Facility::query()
            ->where('source', 'cprf')
            ->when($seenRefs !== [], fn ($q) => $q->whereNotIn('external_ref', $seenRefs))
            ->where('status', '!=', 'inactive')
            ->get();
        foreach ($missing as $facility) {
            $facility->update(['status' => 'inactive']);
            $summary['deactivated']++;
        }

        $summary['success'] = true;
        Log::info('CPRF facility sync completed', $summary);

        return $summary;
    }
}
