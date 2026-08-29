# UMAN monthly energy record sync

LGU1 Energy pulls CPRF-originated monthly consumption records from UMAN's
read-only API and stores them in `energy_records`. Imported rows use
`input_source=cprf`, are auto-approved, and immediately participate in the
existing Energy Consumption dashboard, reports, baseline comparison, alerts,
incidents, and recommendations.

## Configuration

Add these values to LGU1 Energy's `.env`:

```dotenv
UMAN_MONTHLY_RECORDS_URL=https://your-uman-host/api/monthly-energy-records.php
UMAN_INTEGRATION_API_KEY=use-the-same-value-as-uman
```

UMAN must configure the matching `UMAN_INTEGRATION_API_KEY` value.
Only UMAN rows originally received from CPRF are imported. Each row's
`cprf_facility_id` is matched against an existing Energy facility where
`source=cprf` and `external_ref` contains the same CPRF ID. Run
`energy:sync-cprf-facilities` before the monthly-record sync so those facility
identities exist. Local/manual facilities are never selected as targets.

## Run

```shell
php artisan migrate
php artisan energy:sync-uman-monthly-records
```

Optional period filters are available:

```shell
php artisan energy:sync-uman-monthly-records --year=2026 --month=8
```

The Laravel scheduler runs the sync hourly. The server must therefore run the
normal Laravel scheduler (`schedule:run` every minute or `schedule:work`).

The import is idempotent by UMAN `source_record_id`: later syncs update the
same record rather than creating duplicates. CPRF-supplied records are
read-only in the LGU1 Energy monthly-record page.
