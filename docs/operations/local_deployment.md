# Local Deployment

This document describes the current local developer setup.

## Current Local Stack

- PHP/Apache web runtime in Docker service `agritwin-web`.
- MySQL schema and data in Docker service `agritwin-db`.
- Static image, CSV, Cesium, and 3D model assets in the repository.
- Persistent Docker volume for database state.
- Ignored local `var/` paths for generated reports and runtime files.

## Prerequisites

- Docker Engine with Docker Compose.
- PHP CLI for lint/test commands.
- `make`.

## Source Documents

The authoritative documents must remain available:

```text
docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf
docs/source/AgriTwin_D1_20260124.pdf
```

## Database Setup

Run `scripts/start_local_stack.sh`. It creates ignored local runtime settings in `.env` and `config/database.local.env`, starts `agritwin-db`, and imports `mysql.sql` on first volume initialisation.

The Docker database publishes host port `33063` by default to avoid collisions with host MySQL on `3306`. Do not delete the Docker volume during routine recovery.

For migration from an existing database, see `docs/operations/database_migration_to_docker.md`.

## Run Checks

```bash
scripts/start_local_stack.sh
scripts/check_local_stack.sh
make lint
make test
```

`make test` runs PHP syntax checks and the available connector, HMI, KPI, edge and analytics tests.

## Run the App

```bash
make run
```

Open:

```text
http://localhost:8083/agritwin/
```

The Docker URL is the supported local URL. The app expects a working Docker database. Login credentials depend on imported seed data or ignored `LOCAL_CREDENTIALS.md`.

## After Reboot

Run this startup command:

```bash
scripts/start_local_stack.sh
```

Run this validation command:

```bash
scripts/check_local_stack.sh
```

Check local OpenAgri service endpoints directly:

```bash
scripts/openagri/check_openagri_services.sh
```

Expected URL checks:

```bash
curl -s -L http://localhost:8083/agritwin/index.php
curl -s -L http://localhost:8083/agritwin/agritwin_dashboard/index.php
curl -s -L http://localhost:8083/agritwin/agritwin_dashboard/settings.php
curl -s -L http://localhost:8083/agritwin/agritwin_dashboard/openagri_services.php
```

Successful recovery means `agritwin3-agritwin-db-1` is healthy, `agritwin3-agritwin-web-1` is running, the container PHP DB check reports `container-db-ok`, pages render without database-unavailable status, and configured local OpenAgri services respond. The scripts do not delete Docker volumes.

## Known Local Limitations

- Live OpenAgri credentials and operational endpoints are not committed; local fallback remains available where a service is not configured.
- PDF generation is local to AgriTwin and writes generated files under ignored `var/reports/`.
- `field.php` references missing `field2.php` and `field3.php`.
- Production secret management, backups, and hardening remain deployment tasks.

## Recommended Developer Workflow

1. Run `git status --short` before changes.
2. Keep source PDFs under `docs/source/`.
3. Use `.env` for local credentials, but do not commit it.
4. Run `make lint` and `make test` before every commit.
5. Update relevant docs in `docs/architecture`, `docs/operations`, `docs/security`, or `docs/backlog` when changing behavior.
6. Commit small, auditable changes with messages listed in `docs/backlog/implementation_backlog.md` where applicable.
