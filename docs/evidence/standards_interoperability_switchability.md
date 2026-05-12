# Standards, Interoperability and Switchability Evidence

This document records the current evidence model for D1 standards, interoperability, and switchability assessment. It is intentionally conservative: it describes what the local prototype can demonstrate and what must be validated before pilot claims are made.

## Data Standards and File Formats

- JSON Lines: local KPI event store at `var/kpi/events.jsonl`.
- JSON: KPI summaries, OpenAgri connector payloads, dashboard state cache, and sample fixtures.
- CSV: KPI tabular exports and existing crop/tree prototype data.
- Markdown: auditable generated evidence reports and engineering documentation.
- JSON-LD-compatible advisory payloads: used as the target shape for reporting and OpenAgri-compatible advisory exports.
- GeoJSON/OGC-aligned target: recommended target for parcel boundaries and geospatial overlays; private pilot parcel boundaries must not be committed.

## Network and IoT Protocols

- HTTP/HTTPS REST: primary OpenAgri connector integration pattern.
- Gatekeeper token-based access: expected service boundary for authenticated OpenAgri calls.
- Browser PWA cache: offline dashboard availability and last-known state.
- Edge gateway queue: local outbound queue for sensor-first synchronisation when connectivity is unavailable.
- MQTT/LoRaWAN/Modbus adapters: not implemented in the local prototype, but documented as expected edge ingestion adapters for hardware-specific deployments.

## Containerisation and Deployment Portability

- Dockerfile: local PHP application container path.
- Docker Compose: local development composition and OpenAgri real/mock profile extension point.
- Environment variables: service URLs, tokens, cache paths, KPI store path, and local deployment parameters are configured outside source code.
- No committed credentials: `.env.example` contains placeholders only.

## Software Module Switchability

- OpenAgri connectors are isolated under `src/openagri_connectors/`.
- KPI instrumentation is isolated under `src/kpi/`.
- The HMI remains in `agritwin_dashboard/` and reads local/static state without embedding OpenAgri tokens.
- CLI commands expose KPI export and ADS comparison without binding to a specific database vendor.
- Generated evidence can be recreated from event data, reducing dependence on manually edited reports.

## Farm Data Collection Reuse

- Internal canonical records are documented in `docs/architecture/data_model.md`.
- KPI events carry `architecture_mode`, `cycle_id`, source metadata, and privacy metadata.
- Sample fixtures are synthetic. Pilot farm collections must be pseudonymised and stored outside git unless explicitly approved and sanitised.
- Export formats support downstream reuse by spreadsheet tools, notebooks, dashboards, and audit packages.

## Hardware Module Reuse Assumptions

- Soil-moisture probes are treated as replaceable sources if they provide timestamped readings and stable sensor identifiers.
- Edge gateways are treated as replaceable if they can buffer packets, run lightweight inference, queue sync work, and expose resource/network counters.
- Pump or equipment energy telemetry is optional; when present it should provide timestamped kWh or power-duration measurements.
- Hardware-specific credentials, radio keys, farm network secrets, and site thresholds must stay out of this repository.

## Evidence Gaps

- No real pilot hardware counters are committed.
- No verified MQTT/LoRaWAN/Modbus adapter is present in the local prototype.
- No production identity provider configuration is committed.
- Final switchability evidence needs deployment records showing that at least one service or hardware module can be replaced without changing the canonical AgriTwin records.
