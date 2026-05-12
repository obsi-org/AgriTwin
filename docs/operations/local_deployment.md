# Local Deployment

This document describes the current local prototype and the target developer setup implied by the architecture docs.

## Current Prototype Stack

- PHP pages under the repository root and `agritwin_dashboard/`.
- MySQL/MariaDB schema in `mysql.sql`.
- Static image, CSV, Cesium, and 3D model assets in the repository.
- Minimal Dockerfile that is not yet sufficient for the full app.

## Prerequisites

- PHP CLI with PDO MySQL support.
- MySQL or MariaDB.
- `make`.
- Optional: Docker for future container verification.

## Source Documents

The authoritative documents must remain available:

```text
docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf
docs/source/AgriTwin_D1_20260124.pdf
```

## Database Setup

Create a local database and user matching `agritwin_dashboard/db_config.php`, or update that local file with developer-only values.

Current default values in the prototype:

```text
host: localhost
database: agritwin_db
user: ceres_db_user
password: replace-with-local-password
```

Import:

```bash
mysql -u ceres_db_user -p agritwin_db < mysql.sql
```

Do not commit local credential changes. The backlog includes moving this configuration to environment variables.

## Run Checks

```bash
make lint
make test
```

`make test` currently runs PHP syntax checks and reports that no automated test suite exists.

## Run the App

```bash
make run
```

Open:

```text
http://127.0.0.1:8080
```

The app expects a working database. Login credentials depend on imported seed data.

## Known Local Limitations

- The Dockerfile only copies root `index.php`; it does not run the full dashboard.
- There is no `docker-compose.yml` for PHP plus MySQL.
- OpenAgri connectors are not implemented yet.
- Edge gateway, offline PWA, and reporting services are not implemented yet.
- `field.php` references missing `field2.php` and `field3.php`.
- Hard-coded credentials/tokens exist and must be removed before shared deployment.

## Recommended Developer Workflow

1. Run `git status --short` before changes.
2. Keep source PDFs under `docs/source/`.
3. Use `.env` for local credentials, but do not commit it.
4. Run `make lint` and `make test` before every commit.
5. Update relevant docs in `docs/architecture`, `docs/operations`, `docs/security`, or `docs/backlog` when changing behavior.
6. Commit small, auditable changes with messages listed in `docs/backlog/implementation_backlog.md` where applicable.
