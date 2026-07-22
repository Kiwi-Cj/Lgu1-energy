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
| `/api/v1/maintenance` | Maintenance schedules and history | `facility_id`, `status`, `scheduled_from`, `scheduled_to`, `updated_since`, `page`, `per_page` |

`per_page` defaults to 25 and has a maximum of 100. List responses contain Laravel pagination fields, including `data`, `current_page`, `last_page`, `per_page`, and `total`.

Example:

```bash
curl "https://your-domain.example/api/v1/energy-records?year=2026&month=7&per_page=50" \
  -H "Authorization: Bearer your-long-random-secret" \
  -H "Accept: application/json"
```

Treat IDs as stable identifiers when importing data. Use `updated_at` to detect records that changed since a previous import.
