# Data Integration API

This read-only API allows another authorized system to retrieve LGU energy data. It does not expose users, passwords, uploaded bills, or other private account data.

## Authentication

Generate a long random token, place it in `.env`, and run `php artisan config:clear`:

```env
INTEGRATION_API_TOKEN=your-long-random-secret
```

Send it on every request:

```http
Authorization: Bearer your-long-random-secret
Accept: application/json
```

Never commit or send the token in a URL. Give it to the other group through a secure channel. The API returns `503` until a token is configured and `401` for an invalid token.

## Endpoints

All endpoints are `GET` requests and are limited to 60 requests per minute per client.

| Endpoint | Purpose | Optional filters |
| --- | --- | --- |
| `/api/v1/summary` | Record counts and total recorded energy | None |
| `/api/v1/facilities` | Active facility catalog | `status`, `search`, `page`, `per_page` |
| `/api/v1/meters` | Main and sub-meter catalog | `facility_id`, `type`, `status`, `page`, `per_page` |
| `/api/v1/energy-records` | Energy consumption records | `facility_id`, `meter_id`, `year`, `month`, `page`, `per_page` |
| `/api/v1/incidents` | Energy incidents | `facility_id`, `status`, `page`, `per_page` |
| `/api/v1/maintenance` | Active maintenance (Pending/Ongoing/Completed still in the working table) | `facility_id`, `status`, `scheduled_from`, `scheduled_to`, `updated_since`, `page`, `per_page` |

`per_page` defaults to 25 and has a maximum of 100. List responses contain Laravel pagination fields, including `data`, `current_page`, `last_page`, `per_page`, and `total`.

Example:

```bash
curl "https://your-domain.example/api/v1/energy-records?year=2026&month=7&per_page=50" \
  -H "Authorization: Bearer your-long-random-secret" \
  -H "Accept: application/json"
```

Treat IDs as stable identifiers when importing data. Use `updated_at` to detect records that changed since a previous import.

## CIMM maintenance sync

A second, self-contained integration backs the CIMM ↔ Energy maintenance sync
(the "Facilities Needing Maintenance" page ↔ CIMM's `sched.php`). It lives
under its own prefix and its own token — deliberately **not** the
`INTEGRATION_API_TOKEN` bearer token above, since that token already gates
several unrelated read endpoints and may have a real secret configured for
another consumer. This one is scoped to just this integration and defaults
to a shared dev key so it works out of the box on local dev:

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `/api/v1/cimm-maintenance-sync/maintenance` | GET | Same data/filters as `/api/v1/maintenance` above |
| `/api/v1/cimm-maintenance-sync/maintenance-history` | GET | Archived (Completed) maintenance history — `facility_id`, `status`, `updated_since`, `page`, `per_page` |
| `/api/v1/cimm-maintenance-sync/maintenance/{id}/sync` | POST | Write-back: apply a CIMM-side status/schedule change |

CIMM's `sched.php` imports every row from the two GET endpoints as
`maintenance_schedule` entries (see
`lgu-portal/includes/api/cimm_energy_maintenance.php`), tagged with an
"⚡ Energy" badge. When a CIMM admin edits one of those rows (status,
scheduled date, assigned team), CIMM POSTs the change to the `/sync`
endpoint so both systems agree on where the work stands:

```http
POST /api/v1/cimm-maintenance-sync/maintenance/{id}/sync
Authorization: Bearer <CIMM_MAINTENANCE_SYNC_TOKEN>
Content-Type: application/json

{
  "status": "Ongoing",
  "scheduled_date": "2026-08-01",
  "assigned_to": "Engr. Cruz",
  "completed_date": null
}
```

`{id}` is the Energy `maintenance.id` (the active-table record — once a
record is archived to `maintenance_history` it's terminal and there's
nothing left to sync). `status` must be one of `Pending`, `Ongoing`,
`Completed` (`completed_date` is required when `status` is `Completed`).
Applying the update runs through the exact same logic as this app's own
"Facilities Needing Maintenance" form (`App\Traits\MaintenanceSyncHelpers`):
completing a record archives it to history, deletes the active row, flips
the facility's status, resolves the linked energy incident, and notifies
the relevant users — a status change looks identical regardless of which
side made it.

Set `CIMM_MAINTENANCE_SYNC_TOKEN` in `.env` to a long random secret shared
with the CIMM install (see `config/services.php`); it falls back to a shared
dev default if left unset, matching how CIMM's own CPRF/RGMAP integrations
default to a shared key on local dev.

## CPRF (Facilities Reservation) Integration

CPRF pushes manual facility meter readings in and pulls facilities and
engineer-approved recommendations out. Auth: `Authorization: Bearer
{CPRF_INTEGRATION_TOKEN}` (defaults to the shared dev key
`CPRF_ENERGY_SHARED_KEY_2026`; override in production). Rate limit: 60
requests/minute.

### POST /api/v1/cprf/facility-readings

Upserts the facility-level monthly `energy_records` row (one per facility +
year + month, `meter_id` NULL, `input_source=cprf`). Baseline, deviation, and
alert are computed exactly as for manually encoded records.

Request body:

| Field | Type | Required | Notes |
|---|---|---|---|
| facility_id | integer | yes | must exist in `facilities` |
| year | integer | yes | 2000–2100 |
| month | integer | yes | 1–12 |
| previous_reading_kwh | number | yes | >= 0 |
| current_reading_kwh | number | yes | >= previous_reading_kwh |
| reading_date | date | yes | e.g. `2026-07-21` |
| energy_cost | number | no | |
| rate_per_kwh | number | no | |
| notes | string | no | logged only, not stored |
| external_ref | string | no | CPRF's local reading id, logged only |
| recorded_by_name | string | no | logged only |

Responses: `201` created / `200` updated with `{message, record{id, facility_id,
period{year,month}, actual_kwh, baseline_kwh, deviation_percent, alert,
input_source}}`; `422` validation error; `401`/`503` auth.

### GET /api/v1/cprf/facilities

Same response shape and filters as `GET /api/v1/facilities` (status, search,
page, per_page) — only the auth token differs.

### GET /api/v1/cprf/recommendations

Rows from `energy_saving_recommendations`. Filters: `facility_id`, `year`,
`month`, `status` (default `approved`; pass `all` to lift), `updated_since`,
`page`, `per_page` (max 100). Row shape: `id, facility{id,name}, year, month,
generated_message, engineer_recommendation, status, expected_savings_kwh,
target_date, reviewed_at, updated_at`.
