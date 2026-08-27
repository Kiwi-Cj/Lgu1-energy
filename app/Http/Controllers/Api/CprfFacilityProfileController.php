<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CprfFacilityProfileController extends Controller
{
    /**
     * Energy profiles for CPRF-mapped facilities, keyed by CPRF's own
     * facility id (external_ref) so CPRF can resolve rows directly without
     * a name-matching step. Facilities with neither a profile nor a main
     * meter are omitted from the response, not returned as an error.
     *
     * "engineer_approved" and "baseline_kwh" are sourced from the
     * facility's main meter, NOT the EnergyProfile record. Main Meter
     * approval is the authoritative engineer sign-off workflow, while the
     * Energy Profile holds the facility's LGU-managed billing and power
     * setup. The remaining
     * fields (utility provider, contract account, energy source, etc.)
     * have no meter equivalent and still come from EnergyProfile as-is.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'updated_since' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Facility::query()
            ->where('source', 'cprf')
            ->whereNotNull('external_ref')
            ->where(function (Builder $q) {
                $q->whereHas('meters', fn (Builder $mq) => $mq->where('meter_type', 'main'))
                    ->orWhereHas('energyProfiles');
            })
            ->when($request->filled('updated_since'), function (Builder $q) use ($request) {
                $since = $request->date('updated_since');
                $q->where(function (Builder $inner) use ($since) {
                    $inner->whereHas('meters', function (Builder $mq) use ($since) {
                        $mq->where('meter_type', 'main')->where('updated_at', '>=', $since);
                    })->orWhereHas('energyProfiles', function (Builder $pq) use ($since) {
                        $pq->where('updated_at', '>=', $since);
                    });
                });
            })
            ->with([
                'meters' => fn ($q) => $q->where('meter_type', 'main'),
                'energyProfiles' => fn ($q) => $q->latest(),
            ])
            ->orderBy('external_ref');

        $perPage = min(max($request->integer('per_page', 25), 1), 100);
        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->getCollection()->transform(function (Facility $facility) {
            $profile = $facility->energyProfiles->first();
            $mainMeters = $facility->meters;

            // Prefer the profile's designated primary meter if it's an
            // approved main meter; otherwise the first approved main
            // meter; otherwise just the first main meter (so baseline
            // still shows something even before any approval happens).
            $primaryMeter = $profile && $profile->primary_meter_id
                ? $mainMeters->firstWhere('id', $profile->primary_meter_id)
                : null;
            $meter = ($primaryMeter && $primaryMeter->approved_at !== null)
                ? $primaryMeter
                : ($mainMeters->first(fn ($m) => $m->approved_at !== null) ?? $mainMeters->first());

            $meterUpdatedAt = $mainMeters->max('updated_at');
            $latestUpdatedAt = $profile?->updated_at && $meterUpdatedAt
                ? ($profile->updated_at->greaterThan($meterUpdatedAt) ? $profile->updated_at : $meterUpdatedAt)
                : ($profile?->updated_at ?? $meterUpdatedAt);
            $registeredMeterNumber = trim((string) ($meter?->meter_number ?? ''));
            $profileMeterNumber = trim((string) ($profile?->electric_meter_no ?? ''));
            $electricMeterNumber = $registeredMeterNumber !== ''
                ? $registeredMeterNumber
                : (! in_array(strtoupper($profileMeterNumber), ['', 'N/A', 'NA', '-'], true)
                    ? $profileMeterNumber
                    : null);

            return [
                'facility_external_ref' => (int) $facility->external_ref,
                'energy_facility_id' => $facility->id,
                'electric_meter_no' => $electricMeterNumber,
                'utility_provider' => $profile->utility_provider ?? null,
                'contract_account_no' => $profile->contract_account_no ?? null,
                'main_energy_source' => $profile->main_energy_source ?? null,
                'backup_power' => $profile->backup_power ?? null,
                'transformer_capacity' => $profile->transformer_capacity ?? null,
                'number_of_meters' => $profile->number_of_meters ?? null,
                'main_meter_name' => $meter?->meter_name,
                'baseline_kwh' => $meter && $meter->baseline_kwh !== null ? (float) $meter->baseline_kwh : null,
                'engineer_approved' => $meter !== null && $meter->approved_at !== null,
                'baseline_locked' => (bool) ($profile->baseline_locked ?? false),
                'baseline_source' => $profile->baseline_source ?? null,
                'updated_at' => $latestUpdatedAt?->toIso8601String(),
            ];
        });

        // JSON_PRESERVE_ZERO_FRACTION: without it, whole-number floats (e.g.
        // baseline_kwh = 7820.0) serialize as "7820" under this environment's
        // serialize_precision=-1 ini setting, which round-trips as an integer
        // and breaks the documented ?float contract for CPRF consumers.
        return response()->json($paginator, 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }
}
