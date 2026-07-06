# Cloud-Based ADS Mode

Cloud mode is suitable when sensor nodes have reliable connectivity and power, and when low local autonomy is acceptable.

## Architecture

```text
sensor node -> MQTT/HTTPS -> cloud API -> time-series DB -> DSS -> dashboard/digital twin
weather station -> MQTT/HTTPS -> cloud API -> time-series DB -> DSS -> dashboard/digital twin
```

## Components

- Sensor nodes publish telemetry at configured intervals.
- Weather station publishes local observations.
- Cloud API authenticates, validates and stores payloads.
- Time-series storage supports analytics, reporting and Digital Twin overlays.
- DSS pipelines combine sensor, weather, EO and OpenAgri service inputs.
- Dashboard and Digital Twin present operational status.

## Advantages

- Simpler local infrastructure.
- Centralised updates and monitoring.
- Lower gateway maintenance.
- Easier integration with cloud analytics and OpenAgri adapters.

## Limitations

- Requires reliable node-to-cloud connectivity.
- Network outages can create data gaps unless nodes have local storage.
- Higher radio energy use may reduce battery life.
- Cloud latency depends on backhaul quality.

## Suitable Conditions

- Farms with stable Wi-Fi, cellular or LoRaWAN backhaul.
- Sensor nodes with adequate power budget.
- Deployments where cloud availability is more important than local autonomy.
