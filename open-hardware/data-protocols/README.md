# Data Protocols

AgriTwin hardware nodes publish structured telemetry using generic identifiers and explicit units. The schemas in this folder define the minimum payload contracts for sensor nodes and weather stations.

Files:

- `sensor_payload_schema.json`: soil, crop and microclimate node telemetry.
- `weather_payload_schema.json`: weather station telemetry.
- `mqtt_topics.md`: MQTT topic conventions.
- `http_ingestion.md`: HTTP ingestion pattern.

Payloads should include:

- `deployment_id`: generic deployment identifier.
- `node_id` or `station_id`: generic node identifier.
- `observed_at`: ISO-8601 timestamp or `gateway_timestamp`.
- `readings`: numeric values.
- `units`: unit map.
- `status`: battery, clock and quality status.

Do not include exact private coordinates or credentials in telemetry examples committed to git.
