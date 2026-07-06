# Database Migration to Docker

## Goal

Move AgriTwin database state into the Docker `agritwin-db` service without deleting existing volumes or committing database dumps.

## Runtime Storage

- Docker volume: `agritwin3_agritwin_mysql_data`
- Ignored dump directory: `var/backups/`

## Export Current Docker Database

```bash
scripts/db/export_current_db.sh
```

The dump is written to `var/backups/` and ignored by Git.

## Import a Local Dump

```bash
scripts/db/import_local_db.sh /path/to/agritwin.sql
```

This imports into the running Docker database without deleting volumes.

## Migrating From the Previous Apache/MySQL Deployment

Treat previous deployments under `/var/www/html/agritwin2` and `/var/www/html/agritwin3` as read-only references. If the old MySQL database is still available, create a dump outside Git, then import it with `scripts/db/import_local_db.sh`.

Do not commit:

- database dumps;
- `.env`;
- `config/database.local.env`;
- Copernicus IDs;
- Cesium tokens;
- OpenAgri credentials;
- `LOCAL_CREDENTIALS.md`.

## Fresh Database

On a fresh Docker volume, MySQL imports `mysql.sql` during first startup and the application runs the idempotent schema/seed logic in `db_init.php`. Cesium and Copernicus values may still need to be copied into `app_settings` or provided through ignored local environment variables.
