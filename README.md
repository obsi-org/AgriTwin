# AgriTwin Local Prototype

AgriTwin is the OpenAgri Agricultural Digital Twin for Intelligent Decision-Making. The target ADS combines an AI-powered DSS, Cesium-based Environmental Digital Twin, IoT/edge ingestion, Copernicus/EO and weather data, OpenAgri reusable services, offline-capable operation, and KPI reporting for irrigation, fertilisation/nitrogen, pest management, and resource efficiency.

This repository currently contains a local PHP/MySQL/Cesium prototype plus the engineering documentation needed to turn the Proposal and D1 into implementation tasks.

## Authoritative Sources

- `docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf`
- `docs/source/AgriTwin_D1_20260124.pdf`

## Local Run

Prerequisites: PHP CLI with PDO MySQL support, MySQL/MariaDB, and `make`.

```bash
scripts/start_local_stack.sh
scripts/check_local_stack.sh
make lint
make test
make run
```

Then open:

```text
http://127.0.0.1:8080
```

Dashboard URLs under the Apache deployment path are:

```text
http://localhost/agritwin3/
http://localhost/agritwin3/agritwin_dashboard/index.php
http://localhost/agritwin3/agritwin_dashboard/api_swagger.php
http://localhost/agritwin3/agritwin_dashboard/analytics.php
http://localhost/agritwin3/api/openapi.json
```

The current prototype expects a local database imported from `mysql.sql` and configured through environment variables loaded by `agritwin_dashboard/db_config.php`. See `docs/operations/local_deployment.md` and `docs/operations/database_local_fix.md` for details.

## Post-Reboot Startup

After a machine reboot, restore the local Docker-backed database and OpenAgri runtime with:

```bash
scripts/start_local_stack.sh
```

Verify the recovery with:

```bash
scripts/check_local_stack.sh
```

The script publishes the AgriTwin MySQL container on `127.0.0.1:3308` by default to avoid collisions with a host MySQL service on `3306`, and writes only ignored local runtime config under `.env` and `config/database.local.env`.

Optional deeper checks:

```bash
scripts/openagri/check_openagri_services.sh
scripts/openagri/check_openagri_services.sh --mandatory-only
make test
find . -path './external' -prune -o -path './vendor' -prune -o -name '*.php' -print | xargs -n1 php -l
curl -s -L http://localhost/agritwin3/index.php
curl -s -L http://localhost/agritwin3/agritwin_dashboard/index.php
curl -s -L http://localhost/agritwin3/agritwin_dashboard/settings.php
curl -s -L http://localhost/agritwin3/agritwin_dashboard/openagri_services.php
```

Live OpenAgri Weather observations require real external credentials and provider configuration outside git.

## Mandatory OpenAgri Services

Proposal/D1 compliance is assessed first against four mandatory OpenAgri services:

- Gatekeeper / Access Control
- Weather Data Service
- Irrigation Management Service
- Pest & Disease Management Service

FarmCalendar and Reporting Service are operational extensions. Geodatabase / OCSM is optional for the local D1 readiness check unless a real endpoint is configured.

Use the mandatory-only check when validating the core service set:

```bash
scripts/openagri/check_openagri_services.sh --mandatory-only
curl -fsS http://localhost/agritwin3/api/openagri/mandatory-services
```

## Reproducible Mock Demo

Docker Compose mock mode starts the PHP app, MariaDB seeded with synthetic demo data, and the local OpenAgri mock service:

```bash
make demo
```

Then open `http://127.0.0.1:8080`. If that port is occupied, run with `AGRITWIN_APP_PORT=18081 make demo`. Run all tests and publication safety checks with:

```bash
make publication-check
```

## Languages and OpenAgri Local Services

The operational HMI supports English, Portuguese from Portugal and Spanish from Spain through the language selector or `?lang=en`, `?lang=pt-PT`, `?lang=es-ES`.

OpenAgri upstream services can be cloned and inspected without committing third-party source:

```bash
scripts/openagri/setup_openagri_services.sh
scripts/openagri/start_openagri_services.sh
scripts/openagri/check_openagri_services.sh
```

The checkouts live under ignored path `external/openagri/`. Use `docker-compose.openagri.yml` to point AgriTwin at local OpenAgri service endpoints once each upstream service has its required local `.env` and credentials configured outside git.

## Documentation Map

- `docs/audit/00_initial_repository_audit.md`: current codebase audit.
- `docs/audit/01_openagri_requirements_traceability.md`: implementation status against Proposal/D1 requirements.
- `docs/architecture/agritwin_system_architecture.md`: cloud-only and mixed edge-cloud ADS architecture.
- `docs/architecture/openagri_integration_architecture.md`: Gatekeeper, OpenAgri services, FarmCalendar, reporting, and fallback behavior.
- `docs/architecture/data_model.md`: canonical AgriTwin data records and JSON examples.
- `docs/analytics/statistics_engine.md`: analytics calculations, graphical page and API endpoints.
- `docs/integration/openagri_connectors.md`: OpenAgri connector configuration, APIs, cache/offline behavior, and tests.
- `docs/integration/openagri_services_configuration.md`: OpenAgri live/demo/auto service-health configuration and weather verification.
- `docs/integration/openagri_endpoint_configuration.md`: OpenAgri endpoint, documentation URL and credential configuration.
- `docs/integration/openagri_mandatory_services.md`: mandatory Proposal/D1 OpenAgri service set and health-check method.
- `docs/edge/edge_gateway_runbook.md`: mixed edge-cloud gateway installation, storage, sync, and troubleshooting.
- `docs/edge/sensor_payload_spec.md`: canonical sensor packet schema and examples.
- `docs/evidence/kpi_methodology.md`: D1 KPI metric definitions, formulas, collection method, and comparison approach.
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
- `docs/audit/openagri_health_truthfulness_report.md`: live-vs-local-demo OpenAgri connector health truthfulness notes.
- `docs/audit/fotovoltaica_macotera_display_name_cleanup.md`: visible partner display-name cleanup audit.
- `docs/data/macetera_demo_sensor_dataset.md`: synthetic Fotovoltaica Macotera SL / Macotera sensor dataset used by the local HMI.
- `docs/data/sensor_stations_macotera.md`: five-station Macotera telemetry model and weather source policy.
- `docs/operations/local_deployment.md`: developer setup.
- `docs/operations/pilot_deployment_runbook.md`: pilot deployment checklist and rollback.
- `docs/security/gdpr_and_security.md`: GDPR/security engineering baseline.
- `docs/publication/open_source_publication_checklist.md`: release safety checklist for public publication.
- `docs/publication/openagri_catalogue_submission.md`: OpenAgri OS Catalogue submission notes.
- `docs/publication/catalogue_metadata.yml`: draft catalogue metadata.
- `docs/backlog/implementation_backlog.md`: prioritized implementation backlog.
- `docs/backlog/proposal_d1_gap_closure_plan.md`: gap-to-action closure plan.

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

## D1 KPI Evidence

The KPI tooling uses synthetic sample events unless a local approved telemetry store is configured:

```bash
make test-kpi
make generate-d1-evidence
php scripts/agritwin-kpi.php compare-ads cloud mixed
```

Runtime KPI events are stored in `var/kpi/events.jsonl` and are ignored by git. Generated sample evidence committed under `docs/evidence/generated/` contains no raw pilot-farm data.

## Edge Gateway Tests

The mixed edge-cloud gateway path uses local durable JSON queues and cache files:

```bash
make test-edge
```

The tests cover normal connectivity, 4G outage, service timeout, duplicate packet idempotency, reconnect/backfill, and stale OpenAgri cache confidence handling.
