# AgriTwin Open Hardware Reference Package

This folder provides a generic, distribution-safe Open Hardware reference package for AgriTwin deployments. It documents hardware patterns that can be adapted to a deployment site without bundling private field data, credentials, exact coordinates, site-specific thresholds or non-anonymised logs.

The package covers:

- Standard Arduino-compatible sensor node for soil, crop and microclimate monitoring.
- Open weather station configuration for local observations.
- Edge gateway for mixed edge + cloud Agricultural Digital Solution operation.
- Cloud-based ADS mode where nodes send telemetry directly to cloud ingestion services.
- Edge + cloud ADS mode where local buffering, quality checks and synchronisation improve rural resilience.

The material is intended for engineering, training, procurement and deployment planning. It uses generic identifiers such as `deployment_id`, `sensor_station_A`, `weather_station_A` and `edge_gateway_A`.

Raw pilot-farm data, credentials, exact site coordinates, private parcel identifiers, site-specific infrastructure identifiers, site-specific thresholds and non-anonymised operational logs are excluded.

## Folder Map

- `bom/`: generic bills of materials for the sensor node, weather station and edge gateway.
- `schematics/`: wiring tables and installation notes.
- `firmware/`: Arduino-compatible example sketches with placeholders only.
- `data-protocols/`: JSON Schemas, MQTT topic conventions and HTTP ingestion examples.
- `deployment-modes/`: cloud mode, edge + cloud mode and comparison matrix.
- `calibration/`: soil moisture and weather station calibration guidance.
- `enclosures-power/`: enclosure, solar, battery and field installation guidance.
- `testing/`: bench, field acceptance and data quality checks.
- `safety-privacy/`: identifier, privacy, electrical and field safety notes.
- `distribution/`: release checklist for sharing the hardware reference package.

## Licensing Overview

| Material | Licence |
| --- | --- |
| Hardware documentation and example hardware design guidance | CERN-OHL-S-2.0 |
| Firmware examples | Apache-2.0 |
| Software snippets and scripts | Apache-2.0 |
| Third-party hardware components and libraries | Respective upstream terms |

See `LICENSE-HARDWARE.md` for the hardware licence notice. The package does not include proprietary sensor designs.

## What Is Excluded

- Real farm coordinates, parcel boundaries and private sensor locations.
- Real sensor readings, operational logs and private infrastructure identifiers.
- Passwords, service credentials, API secrets, radio keys and certificates.
- Site-specific calibration coefficients unless they are generic examples.
- Generated reports, database dumps and runtime artefacts.

## Basic Implementation Flow

1. Select cloud mode or edge + cloud mode based on connectivity and maintenance requirements.
2. Assemble a sensor node using the BOM and wiring guide.
3. Flash firmware with site-specific credentials supplied outside the repository.
4. Calibrate sensors using the calibration guide.
5. Register generic node identifiers in the target AgriTwin deployment.
6. Validate payloads against the JSON Schemas in `data-protocols/`.
7. Complete bench and field acceptance testing before operational use.
