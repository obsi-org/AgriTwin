# Post-Reboot Recovery

Date: 2026-05-13

## Purpose

Use this procedure after a workstation reboot or Docker restart to bring the local AgriTwin database, local OpenAgri services, and Apache-served `/agritwin3/` pages back to a verified state. The procedure is non-destructive and must not delete database volumes.

## Root Cause Found

After reboot, the AgriTwin MySQL container existed but was stopped and had no restart policy. The host-served PHP application reads database settings from local environment files and expects a reachable MySQL endpoint from the host runtime. The compose database was not publishing a host port, while an unrelated host MySQL service occupied `3306`, so AgriTwin could not reach the intended local database and displayed a database-unavailable state.

## Fix Applied

- Added `restart: unless-stopped` to the AgriTwin database, optional compose app, OpenAgri fixture service and real-env service.
- Added a named `agritwin_mysql_data` volume for persistent MySQL data.
- Published the AgriTwin MySQL service on host port `3308` by default to avoid `3306` conflicts.
- Added `scripts/start_local_stack.sh`, `scripts/check_local_stack.sh` and `scripts/stop_local_stack.sh`.
- Added ignored `config/database.local.env` generation so Apache/PHP can read the non-secret local DB endpoint without requiring broad access to `.env`.
- Added database diagnostics on the settings page without exposing passwords or SQL stack traces.

## Recovery Commands

Start the local stack:

```bash
scripts/start_local_stack.sh
```

Validate the local stack:

```bash
scripts/check_local_stack.sh
```

To stop without deleting volumes:

```bash
scripts/stop_local_stack.sh
```

## Verification

The check script verifies:

- Docker Compose database and OpenAgri fixture service status.
- Database connectivity inside the MySQL container as `ceres_db_user`.
- Host PHP connectivity using the same configuration read by AgriTwin.
- Local OpenAgri service checks.
- Rendered `/agritwin3/` pages for SQLSTATE, access-denied and prohibited prototype terms.

Expected successful result:

- Database container `agritwin3-db-1` is healthy.
- Host PHP DB check prints `host-db-ok`.
- `Database status: Unavailable` no longer appears when the stack is running.
- Configured local OpenAgri services respond to real HTTP checks.

Additional verification commands:

```bash
scripts/openagri/check_openagri_services.sh
make test
find . -path './external' -prune -o -path './vendor' -prune -o -name '*.php' -print | xargs -n1 php -l
curl -s -L http://localhost/agritwin3/index.php
curl -s -L http://localhost/agritwin3/agritwin_dashboard/index.php
curl -s -L http://localhost/agritwin3/agritwin_dashboard/settings.php
curl -s -L http://localhost/agritwin3/agritwin_dashboard/openagri_services.php
```

## Troubleshooting

- Docker not running: start Docker, then rerun `scripts/start_local_stack.sh`.
- Database container not healthy: run `docker compose --profile mock ps` and `docker compose --profile mock logs db --tail=120`; do not delete volumes as a routine recovery action.
- DB credentials/config not loaded: rerun `scripts/start_local_stack.sh` to regenerate ignored local `.env` and `config/database.local.env`, then rerun `scripts/check_local_stack.sh`.
- OpenAgri local services not started: run `OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh` and then `scripts/openagri/check_openagri_services.sh`.
- Weather service running but live weather credentials missing: this is expected until real OpenAgri Weather authentication and upstream weather-provider credentials are supplied outside git.

## Safety Warnings

- Do not delete Docker volumes as part of normal post-reboot recovery.
- Do not commit `.env`, `config/database.local.env`, `config/openagri.local.env`, service `.env` files, credentials, tokens, certificates, or private pilot-farm data.

## Remaining Limitations

- If the local `.env` is intentionally customised, rerun `scripts/start_local_stack.sh` after reboot so `config/database.local.env` is regenerated with the active published port.
- If live OpenAgri Weather observations are required, external provider credentials still must be provided outside git.
