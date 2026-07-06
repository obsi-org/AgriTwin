# MQTT Topic Conventions

MQTT topics use generic deployment and node identifiers:

```text
agritwin/{deployment_id}/sensor/{node_id}/telemetry
agritwin/{deployment_id}/weather/{station_id}/telemetry
agritwin/{deployment_id}/gateway/{gateway_id}/status
agritwin/{deployment_id}/sensor/{node_id}/status
```

Examples:

```text
agritwin/example_deployment/sensor/sensor_station_A/telemetry
agritwin/example_deployment/weather/weather_station_A/telemetry
agritwin/example_deployment/gateway/edge_gateway_A/status
```

## Topic Rules

- Use lower-case letters, digits, hyphens and underscores.
- Do not encode exact coordinates, parcel identifiers or private infrastructure labels in topics.
- Keep authentication and broker addresses in local configuration outside git.
- Retain queued messages on the edge gateway during connectivity outages.

## Example Sensor Payload

```json
{
  "deployment_id": "example_deployment",
  "node_id": "sensor_station_A",
  "observed_at": "gateway_timestamp",
  "data_source_classification": "field_sensor_node",
  "readings": {
    "soil_moisture_vwc_pct_a": 24.1,
    "soil_temperature_c": 18.4,
    "air_temperature_c": 22.1,
    "relative_humidity_pct": 58.0,
    "battery_voltage_v": 3.86
  },
  "units": {
    "soil_moisture_vwc_pct_a": "% VWC",
    "soil_temperature_c": "C",
    "air_temperature_c": "C",
    "relative_humidity_pct": "%",
    "battery_voltage_v": "V"
  },
  "status": {
    "battery": "normal",
    "clock": "gateway_assigned",
    "quality": "normal"
  }
}
```
