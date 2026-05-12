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

The current prototype expects a local database imported from `mysql.sql` and configured through `agritwin_dashboard/db_config.php`. See `docs/operations/local_deployment.md` for details and current limitations.

## Documentation Map

- `docs/audit/00_initial_repository_audit.md`: current codebase audit.
- `docs/audit/01_openagri_requirements_traceability.md`: implementation status against Proposal/D1 requirements.
- `docs/architecture/agritwin_system_architecture.md`: cloud-only and mixed edge-cloud ADS architecture.
- `docs/architecture/openagri_integration_architecture.md`: Gatekeeper, OpenAgri services, FarmCalendar, reporting, and fallback behavior.
- `docs/architecture/data_model.md`: canonical AgriTwin data records and JSON examples.
- `docs/integration/openagri_connectors.md`: OpenAgri connector configuration, APIs, cache/offline behavior, and tests.
- `docs/operations/local_deployment.md`: developer setup.
- `docs/operations/pilot_deployment_runbook.md`: pilot deployment checklist and rollback.
- `docs/security/gdpr_and_security.md`: GDPR/security engineering baseline.
- `docs/backlog/implementation_backlog.md`: prioritized implementation backlog.
- `docs/backlog/proposal_d1_gap_closure_plan.md`: gap-to-action closure plan.

## Licence Note

No project licence file is currently present. Treat the repository as not yet licensed for redistribution until an explicit licence is added by the project owner.

## Security Warning

Do not commit secrets or private pilot-farm data. This includes credentials, tokens, TLS certificates, farm network secrets, operational logs, raw telemetry, private parcel boundaries, asset coordinates, site-specific thresholds, consent records, or identifiable report PDFs.

## OpenAgri Connector Tests

The OpenAgri integration layer uses mocked local fixtures by default:

```bash
make test-openagri
```

For configuration details, see `docs/integration/openagri_connectors.md`.
