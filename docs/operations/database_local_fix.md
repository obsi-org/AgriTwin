# Local Database Connection Fix

## Root Cause

The local platform is a PHP/PDO application. The active database configuration is `agritwin_dashboard/db_config.php`, which previously read process environment variables only and did not load the repository `.env` file. When the host Apache deployment served the app under the old path, no database password was present in the PHP process, so PDO attempted a passwordless connection.

Docker Compose also used a different database user than the plain PHP default, which made host and container setup inconsistent.

## Files Changed

- `agritwin_dashboard/db_config.php`: loads `.env`, supports both `AGRITWIN_DB_*` and common `DB_*` names, sends a non-empty local-development password by default, includes the DB port, and logs diagnostics without logging the password.
- `.env.example`: documents complete DB variables with safe placeholders.
- `docker-compose.yml`: aligns the app and MySQL services on the Docker database service, adds DB healthcheck, and keeps configurable published ports.
- `mysql.sql`: initialises the local database schema when the Docker volume is first created.
- `docs/operations/database_local_fix.md`: this operational note.

## Required Local Environment Variables

For host Apache or PHP built-in server:

```dotenv
AGRITWIN_DB_HOST=localhost
AGRITWIN_DB_PORT=3306
AGRITWIN_DB_NAME=agritwin_db
AGRITWIN_DB_USER=replace-with-local-db-user
AGRITWIN_DB_PASSWORD=replace-with-generated-local-password
```

Equivalent legacy/common names are also supported:

```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=agritwin_db
DB_USERNAME=replace-with-local-db-user
DB_PASSWORD=replace-with-generated-local-password
```

For Docker Compose, the app service uses:

```text
AGRITWIN_DB_HOST=agritwin-db
AGRITWIN_DB_PORT=3306
AGRITWIN_DB_NAME=agritwin_db
AGRITWIN_DB_USER=replace-with-local-db-user
AGRITWIN_DB_PASSWORD=replace-with-generated-local-password
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

Docker stack:

```bash
scripts/start_local_stack.sh
```

Use another `AGRITWIN_APP_PORT` if `8083` is already occupied.

## Verify DB Connectivity

From the app container:

```bash
docker compose exec -T agritwin-web php -r 'require "agritwin_dashboard/db_config.php"; echo $pdo instanceof PDO ? "container-db-ok\n" : "failed\n";'
```

From the DB container:

```bash
docker compose exec -T agritwin-db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT DATABASE();" "$MYSQL_DATABASE"'
```

HTTP check:

```bash
curl -i -L http://localhost:8083/agritwin/
curl -s -L http://localhost:8083/agritwin/ | grep -i "Access denied\|using password: NO\|SQLSTATE" && exit 1 || true
```

## Reset Only the Local DB User Safely

Do not delete database volumes or operational data just to fix grants. Use generated local credentials from ignored `.env` files. As a privileged local MySQL admin, run equivalent commands with local values:

```sql
CREATE DATABASE IF NOT EXISTS agritwin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '<local_db_user>'@'%' IDENTIFIED BY '<generated_local_password>';
ALTER USER '<local_db_user>'@'%' IDENTIFIED BY '<generated_local_password>';
GRANT ALL PRIVILEGES ON agritwin_db.* TO '<local_db_user>'@'%';
CREATE USER IF NOT EXISTS '<local_db_user>'@'localhost' IDENTIFIED BY '<generated_local_password>';
ALTER USER '<local_db_user>'@'localhost' IDENTIFIED BY '<generated_local_password>';
GRANT ALL PRIVILEGES ON agritwin_db.* TO '<local_db_user>'@'localhost';
FLUSH PRIVILEGES;
```

The Docker path uses the `agritwin-db` service and the persistent Docker volume instead of host MySQL.

## Secret Handling

Never commit real database passwords, tokens, TLS material, or farm network credentials. Put real values in deployment secrets or local `.env` files that remain ignored by git.
