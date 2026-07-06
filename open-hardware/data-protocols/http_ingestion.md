# HTTP Ingestion

HTTP ingestion is useful when a sensor node or edge gateway cannot use MQTT. Use HTTPS in production or restrict plain HTTP to trusted local networks behind an edge gateway.

## Sensor Endpoint

```text
POST /api/edge/sensor-packets
Content-Type: application/json
Authorization: Bearer <replace-with-local-credential>
```

Example body:

```json
{
  "deployment_id": "example_deployment",
  "node_id": "sensor_station_A",
  "observed_at": "2026-06-01T10:15:00Z",
  "data_source_classification": "field_sensor_node",
  "readings": {
    "soil_moisture_vwc_pct_a": 23.5,
    "soil_temperature_c": 18.7,
    "air_temperature_c": 24.0,
    "relative_humidity_pct": 49.0
  },
  "units": {
    "soil_moisture_vwc_pct_a": "% VWC",
    "soil_temperature_c": "C",
    "air_temperature_c": "C",
    "relative_humidity_pct": "%"
  },
  "status": {
    "battery": "normal",
    "clock": "synchronised",
    "quality": "normal"
  }
}
```

## Weather Endpoint

```text
POST /api/edge/weather-packets
Content-Type: application/json
Authorization: Bearer <replace-with-local-credential>
```

Example body:

```json
{
  "deployment_id": "example_deployment",
  "station_id": "weather_station_A",
  "observed_at": "2026-06-01T10:15:00Z",
  "data_source_classification": "field_weather_station",
  "readings": {
    "air_temperature_c": 24.2,
    "relative_humidity_pct": 48.0,
    "rainfall_mm": 0.0,
    "wind_speed_kmh": 12.4,
    "wind_direction_deg": 315,
    "solar_radiation_w_m2": 740
  },
  "units": {
    "air_temperature_c": "C",
    "relative_humidity_pct": "%",
    "rainfall_mm": "mm",
    "wind_speed_kmh": "km h^-1",
    "wind_direction_deg": "degrees",
    "solar_radiation_w_m2": "W m^-2"
  },
  "status": {
    "battery": "normal",
    "clock": "synchronised",
    "quality": "normal"
  }
}
```

## Validation Rules

- Reject timestamps too far in the future.
- Mark payloads as reduced confidence when clocks are assigned by the gateway.
- Reject values outside documented physical ranges.
- Store authentication material outside repository files.
