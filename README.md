# AgriTwin Platform

AgriTwin is an operational OpenAgri agricultural digital twin for intelligent decision-making. The platform combines an AI-powered DSS, Cesium-based Environmental Digital Twin, IoT/edge ingestion, Copernicus/EO and weather data, OpenAgri reusable services, offline-capable operation, and KPI reporting for irrigation, fertilisation/nitrogen, pest management, and resource efficiency.

This repository contains the local PHP/MySQL/Cesium platform implementation, OpenAgri connector layer, multilingual HMI, analytics module, user management, deployment scripts and auditable engineering documentation.

## Source Material

Project source PDFs are stored under `docs/source/` for local audit and engineering traceability.

## Local Run

The active local repository is:

```text
/media/pedrobranco/AikoHD/Documents/Dev/agritwin
```

Prerequisites: Docker Engine with Docker Compose, PHP CLI for local lint/tests, and `make`.

```bash
scripts/start_local_stack.sh
scripts/check_local_stack.sh
make lint
make test
make run
```

Then open:

```text
http://localhost:8083/agritwin/
```

Dashboard URLs under the Docker web runtime are:

```text
http://localhost:8083/agritwin/
http://localhost:8083/agritwin/agritwin_dashboard/index.php
http://localhost:8083/agritwin/agritwin_dashboard/api_swagger.php
http://localhost:8083/agritwin/agritwin_dashboard/analytics.php
http://localhost:8083/agritwin/api/openapi.json
```

The current platform runs from this repository through Docker Compose. Apache and PHP run in the `agritwin-web` container, MySQL runs in `agritwin-db`, and the application is served under `/agritwin/` without requiring a host checkout under `/var/www/html`. See `docs/operations/docker_local_deployment.md` and `docs/operations/database_migration_to_docker.md` for details.

Local temporary credentials are stored in the ignored `LOCAL_CREDENTIALS.md` file generated during local setup.

## Post-Reboot Startup

After a machine reboot, restore the Docker-backed web and database runtime with:

```bash
scripts/start_local_stack.sh
```

Verify the recovery with:

```bash
scripts/check_local_stack.sh
```

The script publishes the AgriTwin web container on `http://localhost:8083/agritwin/`, the MySQL container on host port `33063`, and local OpenAgri-compatible service endpoints on ports `18091` through `18096` by default. It writes only ignored local runtime config under `.env`, `config/database.local.env`, and `config/openagri.local.env`.

To skip the local OpenAgri-compatible service containers:

```bash
AGRITWIN_START_OPENAGRI=0 scripts/start_local_stack.sh
```

Optional deeper checks:

```bash
scripts/openagri/check_openagri_services.sh
scripts/openagri/check_openagri_services.sh --core-only
make test
find . -path './external' -prune -o -path './vendor' -prune -o -name '*.php' -print | xargs -n1 php -l
curl -s -L http://localhost:8083/agritwin/index.php
curl -s -L http://localhost:8083/agritwin/agritwin_dashboard/index.php
curl -s -L http://localhost:8083/agritwin/agritwin_dashboard/settings.php
curl -s -L http://localhost:8083/agritwin/agritwin_dashboard/openagri_services.php
```

Live OpenAgri Weather observations require real external credentials and provider configuration outside git.

## Core OpenAgri Services

AgriTwin operational readiness is assessed first against four core OpenAgri services:

- Gatekeeper / Access Control
- Weather Data Service
- Irrigation Management Service
- Pest & Disease Management Service

FarmCalendar and Reporting Service are additional operational services. Geodatabase / OCSM is optional unless a real endpoint is configured.

Use the core-only check when validating the core service set:

```bash
scripts/openagri/check_openagri_services.sh --core-only
curl -fsS http://localhost:8083/agritwin/api/openagri/core-services
```

## Reproducible Local Run

Docker Compose local mode starts the PHP/Apache app and MySQL database from this repository:

```bash
make run
```

Then open `http://localhost:8083/agritwin/`. Run all tests and publication safety checks with:

```bash
make publication-check
```

## Languages and OpenAgri Local Services

The operational HMI supports English, Portuguese from Portugal and Spanish from Spain through the language selector or `?lang=en`, `?lang=pt-PT`, `?lang=es-ES`.

## Licence, funding and disclaimer

AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making

Copyright 2026 Obsidian Innovation Institute - Associação

This product includes software developed by Obsidian Innovation Institute - Associação within the AgriTwin Sustainable Innovation Pilot, implemented in the context of the OpenAgri project.

OpenAgri has received funding from the European Union’s Horizon Europe research and innovation programme under Grant Agreement No. 101134083.

The reusable AgriTwin software components are released under the Apache License 2.0. Please see the LICENSE file for the applicable licence terms.

The AgriTwin software components include Decision-Support System pipelines, Environmental Digital Twin components, OpenAgri service integration adapters, deployment templates and related documentation. Raw pilot-farm data, credentials, security secrets, site-specific configuration and non-anonymised operational logs are excluded from the public release.

This output reflects only the author’s view. The European Commission cannot be held responsible for any use that may be made of the information contained therein.

OpenAgri upstream services can be cloned and inspected without committing third-party source:

```bash
scripts/openagri/setup_openagri_services.sh
scripts/openagri/start_openagri_services.sh
scripts/openagri/check_openagri_services.sh
```

The default local run uses the lightweight OpenAgri-compatible containers defined in `docker-compose.yml`. Upstream checkouts live under ignored path `external/openagri/`; set `AGRITWIN_START_EXTERNAL_OPENAGRI=1` only when those service repositories and their local `.env` files are ready outside git.

## Documentation Map

- `docs/audit/00_initial_repository_audit.md`: current codebase audit.
- `docs/audit/01_openagri_requirements_traceability.md`: implementation status against source requirements.
- `docs/architecture/agritwin_system_architecture.md`: cloud-only and mixed edge-cloud ADS architecture.
- `docs/architecture/openagri_integration_architecture.md`: Gatekeeper, OpenAgri services, FarmCalendar, reporting, and fallback behavior.
- `docs/architecture/data_model.md`: canonical AgriTwin data records and JSON examples.
- `docs/analytics/statistics_engine.md`: analytics calculations, graphical page and API endpoints.
- `docs/integration/openagri_connectors.md`: OpenAgri connector configuration, APIs, cache/offline behavior, and tests.
- `docs/integration/openagri_services_configuration.md`: OpenAgri live/auto service-health configuration and weather verification.
- `docs/integration/openagri_endpoint_configuration.md`: OpenAgri endpoint, documentation URL and credential configuration.
- `docs/integration/openagri_core_services.md`: core OpenAgri service set and health-check method.
- `docs/edge/edge_gateway_runbook.md`: mixed edge-cloud gateway installation, storage, sync, and troubleshooting.
- `docs/edge/sensor_payload_spec.md`: canonical sensor packet schema and examples.
- `docs/evidence/kpi_methodology.md`: KPI metric definitions, formulas, collection method, and comparison approach.
- `docs/evidence/standards_interoperability_switchability.md`: standards, portability, switchability, and hardware reuse evidence model.
- `docs/evidence/generated/`: generated sample KPI evidence for cloud-only vs mixed edge-cloud ADS comparison.
- `docs/hmi/hmi_design_notes.md`: farmer HMI journeys, screens, offline behavior, and limitations.
- `docs/hmi/pilot_user_quick_guide.md`: concise operator guide for the Salamanca pilot.
- `docs/hmi/hmi_harmonisation_report.md`: final HMI harmonisation, routing, logo/footer, service health, and sensor data notes.
- `docs/hmi/final_hmi_corrections_report.md`: final dashboard, footer, sidebar, weather, Swagger and settings corrections.
- `docs/i18n/multilingual_platform.md`: English, Portuguese and Spanish translation structure.
- `docs/i18n/central_pages_translation_completion.md`: central page translation completion audit.
- `docs/integration/openagri_services_installation_report.md`: upstream OpenAgri clone/install analysis.
- `docs/integration/openagri_local_docker_deployment.md`: local OpenAgri helper scripts and compose overlay.
- `docs/integration/openagri_service_health_report.md`: latest local OpenAgri health status.
- `docs/security/user_management.md`: roles, permissions and account lifecycle.
- `docs/api/swagger_openapi.md`: local API / Swagger catalogue and OpenAPI JSON notes.
- `docs/audit/openagri_health_truthfulness_report.md`: live-vs-local OpenAgri connector health truthfulness notes.
- `docs/audit/fotovoltaica_macotera_display_name_cleanup.md`: visible partner display-name cleanup audit.
- `docs/data/sensor_stations_macotera.md`: five-station Macotera telemetry model and weather source policy.
- `docs/operations/local_deployment.md`: developer setup.
- `docs/operations/pilot_deployment_runbook.md`: pilot deployment checklist and rollback.
- `docs/security/gdpr_and_security.md`: GDPR/security engineering baseline.
- `docs/publication/open_source_publication_checklist.md`: release safety checklist for public publication.
- `docs/publication/openagri_catalogue_submission.md`: OpenAgri OS Catalogue submission notes.
- `docs/publication/catalogue_metadata.yml`: draft catalogue metadata.
- `docs/backlog/implementation_backlog.md`: prioritized implementation backlog.

## Licence

Original AgriTwin code and documentation in this repository are prepared under Apache-2.0. Third-party services, libraries, source documents, images, and 3D assets remain under their own licences and must be reviewed before public release. See `LICENSE`, `NOTICE`, and `THIRD_PARTY_LICENSES.md`.

## Security Warning

Do not commit secrets or private pilot-farm data. This includes credentials, tokens, TLS certificates, farm network secrets, operational logs, raw telemetry, private parcel boundaries, asset coordinates, site-specific thresholds, consent records, or identifiable report PDFs.

Run the local publication scan before sharing:

```bash
make scan-secrets
```

## OpenAgri Connector Tests

The OpenAgri integration layer uses local fallback fixtures by default and does not claim live service health unless `OPENAGRI_MODE=live` or `OPENAGRI_MODE=auto` and a real endpoint request succeeds:

```bash
make test-openagri
```

For configuration details, see `docs/integration/openagri_connectors.md`.

## KPI Evidence

The KPI tooling uses publication-safe sample events unless a local approved telemetry store is configured:

```bash
make test-kpi
php scripts/agritwin-kpi.php compare-ads cloud mixed
```

Runtime KPI events are stored in `var/kpi/events.jsonl` and are ignored by git. Generated sample evidence committed under `docs/evidence/generated/` contains no raw pilot-farm data.

## Edge Gateway Tests

The mixed edge-cloud gateway path uses local durable JSON queues and cache files:

```bash
make test-edge
```

The tests cover normal connectivity, 4G outage, service timeout, duplicate packet idempotency, reconnect/backfill, and stale OpenAgri cache confidence handling.
