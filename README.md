# AgriTwin Local Prototype

AgriTwin is the OpenAgri Agricultural Digital Twin for Intelligent Decision-Making. The target ADS combines an AI-powered DSS, Cesium-based Environmental Digital Twin, IoT/edge ingestion, Copernicus/EO and weather data, OpenAgri reusable services, offline-capable operation, and KPI reporting for irrigation, fertilisation/nitrogen, pest management, and resource efficiency.

This repository currently contains a local PHP/MySQL/Cesium prototype plus the engineering documentation needed to turn the Proposal and D1 into implementation tasks.

## Authoritative Sources

- `docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf`
- `docs/source/AgriTwin_D1_20260124.pdf`

## Local Run

Prerequisites: PHP CLI with PDO MySQL support, MySQL/MariaDB, and `make`.

```bash
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
http://localhost/agritwin3/api/openapi.json
```

The current prototype expects a local database imported from `mysql.sql` and configured through environment variables loaded by `agritwin_dashboard/db_config.php`. See `docs/operations/local_deployment.md` and `docs/operations/database_local_fix.md` for details.

## Reproducible Mock Demo

Docker Compose mock mode starts the PHP app, MariaDB seeded with synthetic demo data, and the local OpenAgri mock service:

```bash
make demo
```

Then open `http://127.0.0.1:8080`. If that port is occupied, run with `AGRITWIN_APP_PORT=18081 make demo`. Run all tests and publication safety checks with:

```bash
make publication-check
```

## Documentation Map

- `docs/audit/00_initial_repository_audit.md`: current codebase audit.
- `docs/audit/01_openagri_requirements_traceability.md`: implementation status against Proposal/D1 requirements.
- `docs/architecture/agritwin_system_architecture.md`: cloud-only and mixed edge-cloud ADS architecture.
- `docs/architecture/openagri_integration_architecture.md`: Gatekeeper, OpenAgri services, FarmCalendar, reporting, and fallback behavior.
- `docs/architecture/data_model.md`: canonical AgriTwin data records and JSON examples.
- `docs/integration/openagri_connectors.md`: OpenAgri connector configuration, APIs, cache/offline behavior, and tests.
- `docs/integration/openagri_services_configuration.md`: OpenAgri live/demo/auto service-health configuration and weather verification.
- `docs/edge/edge_gateway_runbook.md`: mixed edge-cloud gateway installation, storage, sync, and troubleshooting.
- `docs/edge/sensor_payload_spec.md`: canonical sensor packet schema and examples.
- `docs/evidence/kpi_methodology.md`: D1 KPI metric definitions, formulas, collection method, and comparison approach.
- `docs/evidence/standards_interoperability_switchability.md`: standards, portability, switchability, and hardware reuse evidence model.
- `docs/evidence/generated/`: generated sample KPI evidence for cloud-only vs mixed edge-cloud ADS comparison.
- `docs/hmi/hmi_design_notes.md`: farmer HMI journeys, screens, offline behavior, and limitations.
- `docs/hmi/pilot_user_quick_guide.md`: concise operator guide for the Salamanca pilot.
- `docs/hmi/hmi_harmonisation_report.md`: final HMI harmonisation, routing, logo/footer, service health, and sensor data notes.
- `docs/hmi/final_hmi_corrections_report.md`: final dashboard, footer, sidebar, weather, Swagger and settings corrections.
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

The OpenAgri integration layer uses local demo fixtures by default and does not claim live service health unless `OPENAGRI_MODE=live` or `OPENAGRI_MODE=auto` and a real endpoint request succeeds:

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
