# Local Database Connection Fix

## Root Cause

The local prototype is a plain PHP/PDO application. The active database configuration is `agritwin_dashboard/db_config.php`, which previously read process environment variables only and did not load the repository `.env` file. When Apache served the app at `/agritwin3/`, no database password was present in the PHP process, so PDO attempted:

```text
user: ceres_db_user
host: localhost
password_configured: no
```

That produced:

```text
SQLSTATE[HY000] [1045] Access denied for user 'ceres_db_user'@'localhost' (using password: NO)
```

Docker Compose also used a different demo DB user (`agritwin`) than the plain PHP default (`ceres_db_user`), which made host and container setup inconsistent.

## Files Changed

- `agritwin_dashboard/db_config.php`: loads `.env`, supports both `AGRITWIN_DB_*` and common `DB_*` names, sends a non-empty local-development password by default, includes the DB port, and logs diagnostics without logging the password.
- `.env.example`: documents complete DB variables with safe local development values.
- `docker-compose.yml`: aligns app and MySQL services on `agritwin_db` / `ceres_db_user` / `agritwin_local_dev_password`, adds DB healthcheck, and keeps configurable published ports.
- `docker/mysql/init/00-agritwin-user.sql`: idempotently creates/updates the local demo database user and grants for both `%` and `localhost`.
- `docs/operations/database_local_fix.md`: this operational note.

## Required Local Environment Variables

For host Apache or PHP built-in server:

```dotenv
AGRITWIN_DB_HOST=localhost
AGRITWIN_DB_PORT=3306
AGRITWIN_DB_NAME=agritwin_db
AGRITWIN_DB_USER=ceres_db_user
AGRITWIN_DB_PASSWORD=agritwin_local_dev_password
```

Equivalent legacy/common names are also supported:

```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=agritwin_db
DB_USERNAME=ceres_db_user
DB_PASSWORD=agritwin_local_dev_password
```

For Docker Compose, the app service uses:

```text
AGRITWIN_DB_HOST=db
AGRITWIN_DB_PORT=3306
AGRITWIN_DB_NAME=agritwin_db
AGRITWIN_DB_USER=ceres_db_user
AGRITWIN_DB_PASSWORD=agritwin_local_dev_password
```

## Restart the Stack

Host Apache:

```bash
sudo systemctl reload apache2
```

PHP built-in server:

```bash
make run
```

Docker mock stack:

```bash
AGRITWIN_APP_PORT=18081 docker compose --profile mock up --build -d
```

Use another `AGRITWIN_APP_PORT` if `8080` is already occupied.

## Verify DB Connectivity

From the app container:

```bash
docker compose --profile mock exec app php -r 'require "agritwin_dashboard/db_config.php"; echo $pdo instanceof PDO ? "app-db-ok\n" : "failed\n";'
```

From the DB container:

```bash
docker compose --profile mock exec db mysql -uceres_db_user -pagritwin_local_dev_password -e "SELECT DATABASE();" agritwin_db
```

HTTP check:

```bash
curl -i -L http://localhost/agritwin3/
curl -s -L http://localhost/agritwin3/ | grep -i "Access denied\|using password: NO\|SQLSTATE" && exit 1 || true
```

## Reset Only the Local DB User Safely

Do not delete database volumes or pilot data just to fix grants. As a privileged local MySQL admin, run:

```sql
CREATE DATABASE IF NOT EXISTS agritwin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'ceres_db_user'@'%' IDENTIFIED BY 'agritwin_local_dev_password';
ALTER USER 'ceres_db_user'@'%' IDENTIFIED BY 'agritwin_local_dev_password';
GRANT ALL PRIVILEGES ON agritwin_db.* TO 'ceres_db_user'@'%';
CREATE USER IF NOT EXISTS 'ceres_db_user'@'localhost' IDENTIFIED BY 'agritwin_local_dev_password';
ALTER USER 'ceres_db_user'@'localhost' IDENTIFIED BY 'agritwin_local_dev_password';
GRANT ALL PRIVILEGES ON agritwin_db.* TO 'ceres_db_user'@'localhost';
FLUSH PRIVILEGES;
```

This matches `docker/mysql/init/00-agritwin-user.sql`.

## Secret Handling

The password in this document is a deterministic local development password only. Do not reuse it for pilot, staging, production, or shared infrastructure. Never commit real database passwords, tokens, TLS material, or farm network credentials. Put real values in deployment secrets or local `.env` files that remain ignored by git.
