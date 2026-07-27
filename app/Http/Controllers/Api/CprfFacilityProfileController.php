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
     * a name-matching step. Facilities with no energy profile set yet are
     * omitted from the response, not returned as an error.
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
            ->whereHas('energyProfiles')
            ->when($request->filled('updated_since'), function (Builder $q) use ($request) {
                $q->whereHas('energyProfiles', function (Builder $pq) use ($request) {
                    $pq->where('updated_at', '>=', $request->date('updated_since'));
                });
            })
            // Prefer the approved profile when one exists, over "whichever
            // was created most recently" — facilities can accumulate
            // multiple profile rows (the Add-Profile form always inserts a
            // new row rather than updating), and approval is applied to one
            // specific row by id. Without this, a newer unapproved edit
            // could shadow an older approved profile, showing CPRF stale
            // "pending" status and mismatched fields even though the
            // engineer already approved a different, still-current row.
            ->with(['energyProfiles' => fn ($q) => $q->orderByDesc('engineer_approved')->latest()])
            ->orderBy('external_ref');

        $perPage = min(max($request->integer('per_page', 25), 1), 100);
        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->getCollection()->transform(function (Facility $facility) {
            $profile = $facility->energyProfiles->first();

            return [
                'facility_external_ref' => (int) $facility->external_ref,
                'energy_facility_id' => $facility->id,
                'electric_meter_no' => $profile->electric_meter_no ?? null,
                'utility_provider' => $profile->utility_provider ?? null,
                'contract_account_no' => $profile->contract_account_no ?? null,
                'main_energy_source' => $profile->main_energy_source ?? null,
                'backup_power' => $profile->backup_power ?? null,
                'transformer_capacity' => $profile->transformer_capacity ?? null,
                'number_of_meters' => $profile->number_of_meters ?? null,
                'baseline_kwh' => $profile && $profile->baseline_kwh !== null ? (float) $profile->baseline_kwh : null,
                'engineer_approved' => (bool) ($profile->engineer_approved ?? false),
                'baseline_locked' => (bool) ($profile->baseline_locked ?? false),
                'baseline_source' => $profile->baseline_source ?? null,
                'updated_at' => $profile?->updated_at?->toIso8601String(),
            ];
        });

        // JSON_PRESERVE_ZERO_FRACTION: without it, whole-number floats (e.g.
        // baseline_kwh = 7820.0) serialize as "7820" under this environment's
        // serialize_precision=-1 ini setting, which round-trips as an integer
        // and breaks the documented ?float contract for CPRF consumers.
        return response()->json($paginator, 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }
}
