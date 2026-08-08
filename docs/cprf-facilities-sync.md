# CPRF Public Facilities Sync

The Energy system mirrors the public facilities of the **Barangay Culiat
Facilities Reservation System (CPRF)** instead of encoding them by hand.
CPRF is the system of record for facility identity (name, address, details,
status); the Energy system owns everything energy-related (energy profiles,
meters, baselines, readings, recommendations).

## How it works

1. `energy:sync-cprf-facilities` (hourly via the scheduler, or the
   **Sync from CPRF now** button on the Facilities page's *Public
   Facilities — Brgy. Culiat* tab) pulls CPRF's facilities feed:
   `GET {CPRF_FACILITIES_FEED_URL}` with `Authorization: Bearer
   {CPRF_INTEGRATION_TOKEN}`.
2. Rows are upserted into `facilities` with `source='cprf'` and
   `external_ref=<CPRF facility id>` (unique per source). Identity fields
   are overwritten on every run; facilities that disappear from the feed
   are set to `status='inactive'` — never deleted, so reading history
   survives.
3. `GET /api/v1/cprf/facility-profiles` keys each profile by CPRF's
   `external_ref`, so CPRF can map Energy-managed profile data without fuzzy
   name matching.

## Rules for cprf-sourced facilities

- Identity fields are **read-only** in the UI and server-side
  (`FacilityController@update` allows only a photo change;
  `@destroy` refuses to archive them).
- Each facility has one LGU-managed energy profile. Meters, submeters, and
  readings work exactly like on local facilities. Approved Main Meters are
  authoritative for meter number, active meter count, and available baseline;
  the profile owns billing and power-setup fields.
- Locally created facilities keep `source='local'` and are unaffected.

## Configuration (.env)

```
CPRF_INTEGRATION_TOKEN=<shared secret, same value CPRF has as ENERGY_API_TOKEN>
CPRF_FACILITIES_FEED_URL=https://cprf.infragovservices.com/public/api/energy-facilities-feed.php
```

## One-time reconciliation after first deploy

Facilities that were previously encoded manually for Barangay Culiat will
be duplicated by their cprf-sourced mirrors after the first sync. Archive
the manual duplicates (their history is preserved in the archive); CPRF's
auto-mapping targets the mirrored rows.

## Status mapping (CPRF → Energy)

| CPRF status | Energy status |
|---|---|
| available | active |
| maintenance | maintenance |
| offline / missing from feed | inactive |
