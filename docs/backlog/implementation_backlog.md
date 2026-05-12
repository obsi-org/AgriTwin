# AgriTwin Implementation Backlog

Source basis: Proposal, D1, and current repository audit. Priority groups are `MUST`, `SHOULD`, and `COULD`.

## MUST

### M01 - Externalize Configuration and Secrets

Objective: Remove hard-coded database credentials and API tokens from PHP/seed files and load configuration from environment variables.

Files likely affected: `agritwin_dashboard/db_config.php`, `agritwin_dashboard/meteo.php`, `db_init.php`, `.env.example`, `README.md`, `docs/security/gdpr_and_security.md`.

Acceptance criteria:

- Database config reads from `AGRITWIN_DB_*` variables with safe local defaults only when explicitly enabled.
- AEMET, Cesium, Copernicus, and OpenAgri credentials are not stored in source.
- `.env.example` documents required variables without real values.
- App fails clearly when required configuration is missing.

Tests required: PHP syntax checks, config loading unit tests, manual local login smoke test.

Documentation required: Update local deployment and security docs.

Expected git commit message: `chore: externalize AgriTwin configuration and secrets`

### M02 - Create Reproducible Local Stack

Objective: Add a complete developer stack for PHP, MySQL/MariaDB, and seeded data.

Files likely affected: `Dockerfile`, `docker-compose.yml`, `mysql.sql`, `Makefile`, `README.md`, `docs/operations/local_deployment.md`.

Acceptance criteria:

- `docker compose up` starts the web app and database.
- Full dashboard assets are available in the container.
- Schema import is repeatable.
- `make run` and `make test` document whether they use local PHP or compose.

Tests required: Container build, compose smoke test, schema import test, PHP lint.

Documentation required: Update local deployment.

Expected git commit message: `chore: add reproducible local AgriTwin stack`

### M03 - Fix Prototype Runtime Defects

Objective: Resolve local PHP navigation and seed defects that block reliable local operation.

Files likely affected: `db_init.php`, `agritwin_dashboard/field.php`, dashboard PHP pages, `mysql.sql`.

Acceptance criteria:

- `db_init.php` references the correct dashboard path.
- Minimal seed SQL is syntactically valid.
- Farm routing does not reference missing pages without graceful fallback.
- Login redirect paths are consistent.

Tests required: PHP lint, database import test, login/dashboard manual smoke test.

Documentation required: Update known limitations in local deployment.

Expected git commit message: `fix: stabilize AgriTwin local prototype runtime`

### M04 - Define Canonical Schema and Migrations

Objective: Convert current demo tables toward the canonical data model in `docs/architecture/data_model.md`.

Files likely affected: `database/migrations/*`, `mysql.sql`, PHP data access code, `docs/architecture/data_model.md`.

Acceptance criteria:

- Canonical tables exist for farms, parcels, assets, sensor readings, EO observations, weather records, recommendations, activities, alerts, and KPI telemetry.
- Numeric values are stored as typed fields, not formatted strings.
- Provenance and sync metadata are present.

Tests required: Migration up/down test, fixture import test, data model validation tests.

Documentation required: Update data model and local deployment.

Expected git commit message: `feat: add canonical AgriTwin data schema`

### M05 - Implement Sensor Batch Ingestion API

Objective: Add a server-side API for edge/direct sensor payloads.

Files likely affected: `api/sensor_batches.php` or new backend service, routing config, database migrations, tests, `docs/architecture/data_model.md`.

Acceptance criteria:

- API accepts the documented sensor payload format.
- Payloads are validated, normalized, and persisted.
- Idempotency keys prevent duplicate inserts.
- Invalid records return structured errors.

Tests required: API request tests, schema validation tests, duplicate replay tests.

Documentation required: Add API usage examples.

Expected git commit message: `feat: add sensor batch ingestion API`

### M06 - Implement Edge Gateway Prototype

Objective: Add an edge gateway service that buffers sensor data, queues sync, caches responses, and supports reduced-confidence mode.

Files likely affected: `edge/`, `docs/operations/pilot_deployment_runbook.md`, `docs/architecture/agritwin_system_architecture.md`, tests.

Acceptance criteria:

- Gateway writes sensor batches to local cache before sync.
- Outbound queue persists across restart.
- Sync uses idempotency keys.
- Cached weather/irrigation/pest records can be read locally.
- Reduced-confidence status is emitted when cache is stale.

Tests required: Unit tests, offline/online sync tests, restart persistence test.

Documentation required: Edge gateway deployment notes.

Expected git commit message: `feat: add edge gateway buffering and sync prototype`

### M07 - Implement OpenAgri Gatekeeper Connector Foundation

Objective: Add shared connector code for Gatekeeper authentication, token refresh, service discovery, retries, and normalized errors.

Files likely affected: `src/OpenAgri/*` or backend equivalent, `.env.example`, tests, `docs/architecture/openagri_integration_architecture.md`.

Acceptance criteria:

- Connector obtains token from Gatekeeper.
- Tokens are not logged.
- Service discovery results are cached.
- Errors are categorized as retryable, unauthorized, rate-limited, unavailable, or invalid.

Tests required: Mock Gatekeeper tests, token expiry test, logging redaction test.

Documentation required: Connector configuration docs.

Expected git commit message: `feat: add OpenAgri Gatekeeper connector foundation`

### M08 - Implement OpenAgri Weather/Irrigation/Pest Connectors

Objective: Integrate the OpenAgri services required by the Proposal and D1.

Files likely affected: connector service files, database schema, advisory service, tests, docs.

Acceptance criteria:

- Weather response normalizes into `weather_record`.
- Irrigation response normalizes into `irrigation_recommendation`.
- Pest & Disease response normalizes into `pest_disease_risk` and alerts.
- Cache class and source freshness are stored.

Tests required: Mock service contract tests, cache fallback tests, error path tests.

Documentation required: Update OpenAgri integration architecture.

Expected git commit message: `feat: integrate OpenAgri weather irrigation and pest services`

### M09 - Implement Advisory Engine

Objective: Combine local DSS outputs, OpenAgri responses, EO/weather/sensor data, and farm constraints into traceable recommendations.

Files likely affected: advisory service files, `ai_recommendations.php`, schema, tests, docs.

Acceptance criteria:

- Recommendations include domain, action, confidence, explanation, inputs, status, and validity window.
- Irrigation, nitrogen/fertilisation, and pest domains are represented.
- Reduced-confidence recommendations are clearly marked.
- Acknowledgement/rejection feedback is stored.

Tests required: Unit tests, fixture-based recommendation tests, feedback API tests.

Documentation required: Update data model and UI notes.

Expected git commit message: `feat: add traceable AgriTwin advisory engine`

### M10 - Implement KPI Telemetry

Objective: Capture Proposal/D1 KPI evidence.

Files likely affected: KPI service, schema, dashboard pages, tests, docs.

Acceptance criteria:

- Captures irrigation schedule latency, water-stress forecast MAE, nitrogen recommendation error, data continuity, uptime, network volume, CPU/GPU hours, energy use, interoperability, and switchability.
- KPI records include ADS mode and component dimensions.
- Dashboard or report can display KPI summaries.

Tests required: Metric calculation tests, ingestion tests, dashboard/report smoke test.

Documentation required: Update data model and architecture docs.

Expected git commit message: `feat: add AgriTwin KPI telemetry capture`

### M11 - Implement GDPR and Access-Control Baseline

Objective: Add role-based access, CSRF protection, secure sessions, and audit logging.

Files likely affected: PHP auth/session bootstrap, forms, database schema, tests, `docs/security/gdpr_and_security.md`.

Acceptance criteria:

- Session cookies use secure defaults appropriate to environment.
- State-changing forms require CSRF tokens.
- Roles protect settings, reports, connector configuration, and recommendations.
- Audit logs capture sensitive actions without leaking secrets.

Tests required: Auth tests, CSRF tests, role tests, audit log tests.

Documentation required: Security docs update.

Expected git commit message: `feat: add AgriTwin access control and audit baseline`

## SHOULD

### S01 - Add FarmCalendar Integration

Objective: Sync accepted recommendations and completed farm operations with FarmCalendar.

Files likely affected: FarmCalendar connector, activity schema, task scheduling UI, tests, docs.

Acceptance criteria:

- Accepted irrigation/pest/fertilisation recommendations create proposed FarmCalendar activities.
- Completed activities sync back to AgriTwin.
- Offline writes queue and replay safely.

Tests required: Mock FarmCalendar tests, conflict tests, queue replay tests.

Documentation required: Update OpenAgri integration docs.

Expected git commit message: `feat: integrate FarmCalendar operations sync`

### S02 - Add Reporting/PDF Advisory Export

Objective: Generate advisory PDFs from canonical recommendations, sensor summaries, EO/weather context, and KPI data.

Files likely affected: reporting service files, object storage config, UI, tests, docs.

Acceptance criteria:

- PDF report generated from synthetic data.
- Report metadata and checksum are stored.
- Private pilot reports are not public.
- Fallback local generation is available if OpenAgri Reporting Service is unavailable.

Tests required: PDF generation test, metadata test, permissions test.

Documentation required: Update OpenAgri integration and operations docs.

Expected git commit message: `feat: add advisory PDF reporting`

### S03 - Add Offline-First PWA Behavior

Objective: Convert the dashboard into a low-connectivity PWA.

Files likely affected: `manifest.webmanifest`, service worker, frontend assets, dashboard pages, tests, docs.

Acceptance criteria:

- App shell caches for offline access.
- Farm state and queued user actions persist locally.
- Sync resumes automatically when connectivity returns.
- UI indicates stale data and reduced-confidence mode.

Tests required: Playwright offline tests, service worker tests, sync tests.

Documentation required: Update local and pilot runbooks.

Expected git commit message: `feat: add offline-first AgriTwin PWA shell`

### S04 - Add EO/Copernicus Ingestion Pipeline

Objective: Move EO/NDVI/NDRE handling from browser-only WMS demo to tracked canonical metadata and object references.

Files likely affected: EO service, schema, Cesium UI, tests, docs.

Acceptance criteria:

- EO observations are stored with metadata, cloud cover, index summary, and raster reference.
- Digital Twin can render indexed layers from canonical records.
- EO availability feeds advisory confidence.

Tests required: Metadata validation tests, fixture ingestion tests, UI smoke test.

Documentation required: Update data model.

Expected git commit message: `feat: add EO metadata ingestion for AgriTwin`

### S05 - Add Model Training and Registry Workflow

Objective: Implement documented training/retraining and artifact publication for cloud DSS and edge models.

Files likely affected: `models/`, `training/`, registry schema, edge sync, tests, docs.

Acceptance criteria:

- Training job records dataset version, metrics, artifact URI, and approval status.
- Edge-compatible artifact can be published to gateway.
- Model rollback is supported.

Tests required: Synthetic training smoke test, registry tests, artifact checksum tests.

Documentation required: Architecture and runbook updates.

Expected git commit message: `feat: add DSS model registry workflow`

### S06 - Add Browser and API Test Suite

Objective: Add automated tests for local prototype and new APIs.

Files likely affected: test config, Playwright/PHPUnit/Pest files, Makefile, docs.

Acceptance criteria:

- `make test` runs API/unit tests and browser smoke tests where dependencies exist.
- Login, dashboard navigation, settings, and Digital Twin load are covered.
- CI-ready commands are documented.

Tests required: The new suite itself.

Documentation required: Update test gap report.

Expected git commit message: `test: add AgriTwin API and browser smoke tests`

## COULD

### C01 - Add Kubernetes Manifests

Objective: Provide optional deployment templates for cloud DSS services.

Files likely affected: `deploy/k8s/*`, docs.

Acceptance criteria:

- Manifests avoid hard-coded secrets.
- Services, ingress, config maps, and secret references are documented.
- Local dry-run validation passes where tools are available.

Tests required: `kubectl --dry-run` or schema validation if available.

Documentation required: Operations docs update.

Expected git commit message: `chore: add optional Kubernetes deployment templates`

### C02 - Add Synthetic Demo Dataset

Objective: Provide safe public fixtures that exercise the full data model.

Files likely affected: `fixtures/synthetic/*`, tests, docs.

Acceptance criteria:

- Dataset includes farms, parcels, sensors, EO, weather, activities, recommendations, and KPI telemetry.
- No private pilot identifiers or exact site geometry.
- Fixtures power automated tests and demo screenshots.

Tests required: Fixture load and validation tests.

Documentation required: README and data model updates.

Expected git commit message: `test: add synthetic AgriTwin demo dataset`

### C03 - Add Architecture Diagrams

Objective: Add Mermaid diagrams for cloud-only, mixed ADS, and OpenAgri connector flows.

Files likely affected: architecture docs.

Acceptance criteria:

- Diagrams render in GitHub Markdown.
- Diagrams do not include private endpoint names.

Tests required: Markdown lint or diagram rendering check if available.

Documentation required: Existing architecture docs update.

Expected git commit message: `docs: add AgriTwin architecture diagrams`

