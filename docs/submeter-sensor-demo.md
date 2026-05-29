# Submeter Sensor Demo

Submeter readings are sensor-only. Manual encoding in the Submeter Monitoring module is disabled.

Endpoint:

```text
POST /api/submeter/sensor-readings
Authorization: Bearer demo-submeter-sensor-token
Content-Type: application/json
```

Sample payload:

```json
{
  "submeter_id": 1,
  "device_id": "SUBMETER-SENSOR-001",
  "period_type": "monthly",
  "reading_month": "2026-05",
  "reading_start_kwh": 500,
  "reading_end_kwh": 620
}
```

Set `SUBMETER_SENSOR_TOKEN` in `.env`. If it is not set, the app falls back to `MAIN_METER_SENSOR_TOKEN`.

After the request is accepted, the row is saved in `submeter_readings` with `input_source = iot`.
It appears on the Submeter Monitoring table and sensor graph.
