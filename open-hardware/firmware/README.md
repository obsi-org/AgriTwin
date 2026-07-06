# Firmware Examples

This folder contains Arduino-compatible example sketches for AgriTwin sensor and weather telemetry. They are intentionally simple and use placeholders only.

Examples:

- `standard_sensor_node/agritwin_sensor_node_example.ino`
- `weather_station/agritwin_weather_station_example.ino`

## Expected Libraries

The examples use common Arduino patterns. Depending on the selected board and sensors, developers may use:

- WiFi or board-specific network library.
- ArduinoJson or equivalent JSON serializer.
- OneWire and DallasTemperature for DS18B20 probes.
- Wire plus SHT31/SHT35 sensor library.
- PubSubClient or HTTP client library for transport.

The sketches do not include real network names, passwords, endpoint secrets, farm identifiers or private coordinates.

## Timestamp Modes

The examples can publish either:

- a gateway timestamp placeholder, allowing the edge gateway/cloud to assign `observed_at`; or
- an ISO-8601 timestamp from an RTC/network time source when available.

Production deployments should use a reliable time source and record clock status in payload metadata.
