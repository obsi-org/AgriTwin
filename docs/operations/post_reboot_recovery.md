# Post-Reboot Recovery

Date: 2026-05-13

## Purpose

Use this procedure after a workstation reboot or Docker restart to bring the local AgriTwin Docker web runtime and database back to a verified state. The procedure is non-destructive and must not delete database volumes.

## Root Cause Found

The active repository now runs directly from `/media/pedrobranco/AikoHD/Documents/Dev/agritwin` through Docker Compose. The old host Apache folder is no longer required for local operation.

## Fix Applied

- Added `restart: unless-stopped` to the AgriTwin web container, database and optional OpenAgri fixture service.
- Added a named `agritwin_mysql_data` volume for persistent MySQL data.
- Published the AgriTwin web service on `http://localhost:8083/agritwin3/`.
- Published the AgriTwin MySQL service on host port `33063` by default to avoid `3306` conflicts.
- Added `scripts/start_local_stack.sh`, `scripts/check_local_stack.sh` and `scripts/stop_local_stack.sh`.
- Added ignored `.env` and `config/database.local.env` generation for local runtime configuration.
- Added database diagnostics on the settings page without exposing passwords or SQL stack traces.

## Recovery Commands

Start the local stack:

```bash
scripts/start_local_stack.sh
```

To also ask the local OpenAgri helper scripts to start upstream services from the ignored `external/openagri/` checkout, run:

```bash
AGRITWIN_START_OPENAGRI=1 scripts/start_local_stack.sh
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

- Docker Compose web and database service status.
- Database connectivity inside the MySQL container.
- PHP application database connectivity from inside the web container.
- Local OpenAgri service checks when the services are configured and running.
- Rendered `http://localhost:8083/agritwin3/` pages for SQLSTATE, access-denied and prohibited terms.
- Reports/PDF generation inside the web container.

Expected successful result:

- Database container `agritwin3-agritwin-db-1` is healthy.
- Web container `agritwin3-agritwin-web-1` is running.
- Container PHP DB check prints `container-db-ok`.
- `Database status: Unavailable` no longer appears when the stack is running.
- Configured local OpenAgri services respond to real HTTP checks.

Additional verification commands:

```bash
scripts/openagri/check_openagri_services.sh
make test
find . -path './external' -prune -o -path './vendor' -prune -o -name '*.php' -print | xargs -n1 php -l
curl -s -L http://localhost:8083/agritwin3/index.php
curl -s -L http://localhost:8083/agritwin3/agritwin_dashboard/index.php
curl -s -L http://localhost:8083/agritwin3/agritwin_dashboard/settings.php
curl -s -L http://localhost:8083/agritwin3/agritwin_dashboard/openagri_services.php
```

## Troubleshooting

- Docker not running: start Docker, then rerun `scripts/start_local_stack.sh`.
- Database container not healthy: run `docker compose ps` and `docker compose logs agritwin-db --tail=120`; do not delete volumes as a routine recovery action.
- Web container not healthy: run `docker compose logs agritwin-web --tail=120` and verify `http://localhost:8083/agritwin3/index.php`.
- DB credentials/config not loaded: rerun `scripts/start_local_stack.sh` to regenerate ignored local `.env` and `config/database.local.env`, then rerun `scripts/check_local_stack.sh`.
- OpenAgri local services not started: run `AGRITWIN_START_OPENAGRI=1 scripts/start_local_stack.sh` or `OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh`, then run `scripts/openagri/check_openagri_services.sh`.
- Weather service running but live weather credentials missing: this is expected until real OpenAgri Weather authentication and upstream weather-provider credentials are supplied outside git.

## Safety Warnings

- Do not delete Docker volumes as part of normal post-reboot recovery.
- Do not commit `.env`, `config/database.local.env`, `config/openagri.local.env`, service `.env` files, credentials, tokens, certificates, or private pilot-farm data.

## Remaining Limitations

- If the local `.env` is intentionally customised, rerun `scripts/start_local_stack.sh` after reboot so `config/database.local.env` is regenerated with the active published port.
- If live OpenAgri Weather observations are required, external provider credentials still must be provided outside git.
