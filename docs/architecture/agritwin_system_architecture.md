# AgriTwin System Architecture

Source basis: `docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf` and `docs/source/AgriTwin_D1_20260124.pdf`.

This document converts the Proposal and D1 target architecture into the local engineering architecture for AgriTwin. The current repository is a PHP/Cesium dashboard prototype; the target system is an open-source Agricultural Digital Solution (ADS) with two deployment modes: cloud-only and mixed edge-cloud.

## Architecture Goals

- Provide an AI-powered DSS for irrigation, fertilisation/nitrogen, pest and disease, water stress, and farm operations.
- Provide a Cesium-based Environmental Digital Twin for 2D/3D farm monitoring, EO overlays, assets, sensors, alerts, and advisory state.
- Integrate OpenAgri reusable services through Gatekeeper where available.
- Remain usable under weak 4G connectivity through edge buffering, local cache, and reduced-confidence recommendations.
- Keep the repository auditable, self-contained, and free from raw private pilot-farm data and secrets.

## Cloud-Only ADS

The cloud-only ADS places ingestion, persistence, analytics, OpenAgri integration, advisory generation, and UI serving in a cloud DSS environment.

```text
Sensors / manual uploads / EO jobs / OpenAgri services
        |
        v
Cloud ingestion APIs and scheduled import workers
        |
        v
Canonical records and persistent stores
        |
        v
DSS analytics, advisory engine, and model workers
        |
        v
Digital Twin UI, reports, dashboards, and API clients
```

Responsibilities:

- Accept sensor batches, manual observations, farm activities, EO metadata, weather observations, OpenAgri responses, and model outputs.
- Persist canonical records in typed stores.
- Execute heavy analytics, model training/retraining, model evaluation, and advisory generation.
- Serve the dashboard, Cesium Digital Twin, APIs, and reporting/export functions.
- Capture KPI telemetry across ingestion, inference, recommendation publishing, uptime, network volume, energy, and compute usage.

Use this mode when stable internet access is available or when a developer is running the prototype locally.

## Mixed Edge-Cloud ADS

The mixed ADS splits critical low-connectivity functions to an on-farm edge gateway while keeping durable persistence, heavy analytics, reporting, and governance in the cloud DSS.

```text
Arduino-class sensors and local farm assets
        |
        v
Edge gateway: buffer, validate, infer, queue, cache
        |
        | intermittent 4G / farm Wi-Fi sync
        v
Cloud DSS: persist, train, integrate, visualize, report
```

Use this mode for the FOTOMACO pilot and any farm where 4G availability is variable. The edge gateway must be able to keep collecting data and issuing reduced-confidence local advisories when cloud or OpenAgri connectivity is unavailable.

## Edge Gateway Responsibilities

The edge gateway is the farm-local reliability layer. It must run on low-cost open hardware where possible, with enough storage to buffer sensor data and enough compute for lightweight inference.

- Sensor buffering: receive sensor batches, validate payload schema, timestamp at receipt, assign gateway sequence numbers, and persist to local cache before attempting sync.
- Lightweight inference: run local rule-based or compressed-model inference for water stress, irrigation urgency, and basic anomaly detection.
- Queueing: keep outbound queues for sensor batches, activities, KPI telemetry, and model summaries. Use idempotency keys and retry metadata.
- Synchronisation: push queued data to cloud DSS when connectivity returns; pull cloud model/config updates, parcel metadata, and advisory acknowledgements.
- Cached OpenAgri responses: store recent weather, irrigation, and pest/disease responses from cloud DSS or direct OpenAgri calls if direct access is explicitly enabled.
- Reduced-confidence mode: when OpenAgri, EO, or cloud data is stale, mark recommendations with `confidence.mode = "reduced"` and expose the stale data horizon.
- Local observability: record queue depth, last successful sync, local storage use, sensor dropouts, network availability, CPU load, and energy draw where measurable.
- Farm-local UI support: optionally expose a minimal read-only dashboard on farm Wi-Fi for latest sensor state and reduced-confidence recommendations.

The edge gateway must not store unencrypted credentials, raw logs containing secrets, or private data outside configured retention windows.

## Cloud DSS Responsibilities

The cloud DSS is the authoritative decision and audit environment.

- Ingestion: receive edge batches, direct sensor feeds, manual uploads, FarmCalendar events, EO jobs, weather records, OpenAgri responses, and report requests.
- Persistent storage: enforce canonical schemas, immutable event history, source provenance, and audit metadata.
- Heavy analytics: compute water-stress forecasts, nitrogen demand, pest risk, irrigation schedules, and resource optimization.
- Model training/retraining: train TensorFlow/CUDA-capable models where appropriate, version datasets, record model metrics, and publish edge-compatible model artifacts.
- OpenAgri connectors: authenticate via Gatekeeper, discover services, normalize responses, cache results, and expose connector health.
- Digital Twin UI: serve Cesium 2D/3D views, parcel layers, sensors, EO overlays, assets, pumps, alerts, and recommendation status.
- Advisory engine: combine model outputs, OpenAgri service responses, farmer constraints, and agronomist rules into actionable recommendations.
- Reporting: produce PDF advisory exports and pilot validation reports.
- KPI capture: record latency, forecast error, nitrogen error, uptime, network volume, compute hours, energy consumption, interoperability status, and switchability evidence.

## Data Stores

| Store | Purpose | Candidate technology | Retention and privacy note |
| --- | --- | --- | --- |
| Relational application store | Users, farms, assets, settings, recommendations, activities, reports | MySQL/MariaDB initially; PostgreSQL later | No secrets in rows; site-specific values must be protected |
| Time-series store | Sensor readings, gateway metrics, KPI telemetry | TimescaleDB/PostgreSQL or InfluxDB | Keep raw pilot telemetry private; publish only anonymized samples |
| Geospatial store | Parcels, boundaries, asset locations, EO footprints | PostGIS | Parcel boundaries are private unless explicitly approved for publication |
| Object storage | EO rasters, report PDFs, model artifacts, uploaded files | S3-compatible storage or local MinIO | Separate public demo artifacts from private pilot objects |
| Local edge cache | Sensor buffer, outbound queue, cached responses, config | SQLite plus filesystem object cache | Encrypt at rest where supported; bounded retention |
| Model registry | Model versions, metrics, training metadata | MLflow or simple registry tables initially | Training datasets cannot include raw private data in public artifacts |

## API Surfaces

External API surfaces:

- `POST /api/v1/sensor-batches`: edge and direct sensor ingestion.
- `POST /api/v1/farm-activities`: farm operations, observations, and manual events.
- `GET /api/v1/farms/{farm_id}/digital-twin`: farm state for UI.
- `GET /api/v1/farms/{farm_id}/recommendations`: current advisories.
- `POST /api/v1/recommendations/{id}/acknowledgements`: farmer/agronomist feedback.
- `POST /api/v1/reports/advisory`: request PDF advisory generation.
- `GET /api/v1/kpi`: KPI telemetry and validation status.
- `GET /api/v1/health`: service health and connector state.

Internal services:

- `ingestion-service`: validates and normalizes incoming records.
- `edge-sync-service`: handles idempotent gateway sync and config publication.
- `openagri-connector-service`: Gatekeeper auth, service discovery, weather, irrigation, pest/disease, and reporting calls.
- `eo-ingestion-service`: Copernicus/EO metadata, NDVI/NDRE/LAI/SAVI layer registration, and raster/object references.
- `dss-model-service`: model inference, training, evaluation, and artifact publishing.
- `advisory-service`: recommendation assembly, confidence scoring, and explanation text.
- `digital-twin-api`: Cesium-ready farm state, layers, alerts, assets, and telemetry.
- `reporting-service`: PDF advisory and validation report generation.
- `kpi-service`: operational and agronomic KPI collection.

## Security Boundaries

Trust zones:

- Public repository: source code, synthetic data, documentation, schema examples, and non-sensitive assets.
- Developer local environment: local database, local tokens, generated logs, and test outputs.
- Cloud DSS private environment: production/pilot data, model artifacts, reports, API credentials, audit logs.
- Farm edge environment: sensor buffers, local credentials, network details, farm Wi-Fi/4G configuration, and site-specific thresholds.
- External OpenAgri services: accessed through Gatekeeper with scoped credentials.

Boundary rules:

- Never commit credentials, API tokens, TLS certificates, farm network secrets, raw operational logs, raw pilot telemetry, parcel boundaries, or site-specific thresholds.
- All service credentials must be supplied through environment variables or a secret manager.
- Edge-to-cloud communication must use TLS and authenticated requests.
- Gatekeeper tokens must be short-lived and refreshed server side.
- User-facing roles must separate administrator, agronomist, technician, and viewer permissions.
- Audit logs must record who changed settings, acknowledged advisories, exported reports, or altered model/config versions.

## Secret Handling

Local development must use `.env` or shell environment variables ignored by git. Target variables are documented in `docs/audit/00_initial_repository_audit.md` and should be expanded as implementation proceeds.

Secrets are owned by deployment environments, not source files. The current hard-coded credentials in the prototype are documented security debt and must be removed before any shared deployment.

