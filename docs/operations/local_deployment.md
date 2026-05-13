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

Create a local database and user matching `agritwin_dashboard/db_config.php`, or set local environment values in `.env`. Do not edit tracked PHP files for credentials.

Current default values in the prototype:

```text
host: localhost
port: 3306
database: agritwin_db
user: ceres_db_user
password: agritwin_local_dev_password
```

For the Docker-backed local stack, the database container listens on host port `3308` by default so it does not collide with an existing host MySQL/MariaDB on `3306`. `scripts/start_local_stack.sh` writes ignored local runtime settings to `.env` and `config/database.local.env`.

Import:

```bash
mysql -u ceres_db_user -p agritwin_db < mysql.sql
```

If the local user is missing or has a different password, apply `docker/mysql/init/00-agritwin-user.sql` as a privileged local MySQL administrator. See `docs/operations/database_local_fix.md`.

## Run Checks

```bash
scripts/start_local_stack.sh
scripts/check_local_stack.sh
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

## After Reboot

Run:

```bash
scripts/start_local_stack.sh
scripts/check_local_stack.sh
```

This starts the AgriTwin MySQL container, checks host and container DB connectivity, starts configured local OpenAgri services, and verifies the key `/agritwin3/` pages. It does not delete Docker volumes.

## Known Local Limitations

- Live OpenAgri credentials and pilot endpoints are not committed; mock mode is the default local path.
- PDF generation still depends on a live or mocked Reporting Service.
- `field.php` references missing `field2.php` and `field3.php`.
- Production secret management, backups, and hardening remain deployment tasks.

## Recommended Developer Workflow

1. Run `git status --short` before changes.
2. Keep source PDFs under `docs/source/`.
3. Use `.env` for local credentials, but do not commit it.
4. Run `make lint` and `make test` before every commit.
5. Update relevant docs in `docs/architecture`, `docs/operations`, `docs/security`, or `docs/backlog` when changing behavior.
6. Commit small, auditable changes with messages listed in `docs/backlog/implementation_backlog.md` where applicable.
