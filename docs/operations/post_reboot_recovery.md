# Post-Reboot Recovery

Date: 2026-05-13

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

```bash
scripts/start_local_stack.sh
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

## Remaining Limitations

- If the local `.env` is intentionally customised, rerun `scripts/start_local_stack.sh` after reboot so `config/database.local.env` is regenerated with the active published port.
- If live OpenAgri Weather observations are required, external provider credentials still must be provided outside git.
