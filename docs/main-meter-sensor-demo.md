# Main Meter Sensor Demo

Use this to show that the Main Meter can accept future IoT/sensor input.

Endpoint:

```text
POST /api/main-meter/sensor-readings
Authorization: Bearer demo-main-meter-sensor-token
Content-Type: application/json
```

Sample payload:

```json
{
  "facility_id": 1,
  "device_id": "MAIN-METER-SENSOR-001",
  "reading_month": "2026-05",
  "reading_start_kwh": 12000,
  "reading_end_kwh": 12850,
  "peak_demand_kw": 42.5,
  "power_factor": 0.95
}
```

After the request is accepted, the row is saved in `main_meter_readings` with `input_source = iot`.
It will appear on the Main Meter Monitoring table with the `IOT` source badge.
