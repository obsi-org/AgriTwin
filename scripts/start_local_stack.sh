#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

ensure_local_db_env() {
  local env_file="${ROOT_DIR}/.env"
  local db_env_file="${ROOT_DIR}/config/database.local.env"
  local port="${AGRITWIN_DB_PUBLISHED_PORT:-3308}"
  [[ -f "${env_file}" ]] || cp .env.example "${env_file}"
  local tmp
  tmp="$(mktemp)"
  awk -v port="${port}" '
    BEGIN { seen_host=seen_port=seen_user=seen_pass=seen_dbhost=seen_dbport=seen_dbuser=seen_dbpass=seen_pub=0 }
    /^AGRITWIN_DB_HOST=/ { print "AGRITWIN_DB_HOST=127.0.0.1"; seen_host=1; next }
    /^AGRITWIN_DB_PORT=/ { print "AGRITWIN_DB_PORT=" port; seen_port=1; next }
    /^AGRITWIN_DB_USER=/ { print "AGRITWIN_DB_USER=ceres_db_user"; seen_user=1; next }
    /^AGRITWIN_DB_PASSWORD=/ { print "AGRITWIN_DB_PASSWORD=agritwin_local_dev_password"; seen_pass=1; next }
    /^DB_HOST=/ { print "DB_HOST=127.0.0.1"; seen_dbhost=1; next }
    /^DB_PORT=/ { print "DB_PORT=" port; seen_dbport=1; next }
    /^DB_USERNAME=/ { print "DB_USERNAME=ceres_db_user"; seen_dbuser=1; next }
    /^DB_PASSWORD=/ { print "DB_PASSWORD=agritwin_local_dev_password"; seen_dbpass=1; next }
    /^AGRITWIN_DB_PUBLISHED_PORT=/ { print "AGRITWIN_DB_PUBLISHED_PORT=" port; seen_pub=1; next }
    { print }
    END {
      if (!seen_host) print "AGRITWIN_DB_HOST=127.0.0.1"
      if (!seen_port) print "AGRITWIN_DB_PORT=" port
      if (!seen_user) print "AGRITWIN_DB_USER=ceres_db_user"
      if (!seen_pass) print "AGRITWIN_DB_PASSWORD=agritwin_local_dev_password"
      if (!seen_dbhost) print "DB_HOST=127.0.0.1"
      if (!seen_dbport) print "DB_PORT=" port
      if (!seen_dbuser) print "DB_USERNAME=ceres_db_user"
      if (!seen_dbpass) print "DB_PASSWORD=agritwin_local_dev_password"
      if (!seen_pub) print "AGRITWIN_DB_PUBLISHED_PORT=" port
    }
  ' "${env_file}" > "${tmp}"
  mv "${tmp}" "${env_file}"
  chmod 600 "${env_file}" || true
  mkdir -p "${ROOT_DIR}/config"
  cat > "${db_env_file}" <<ENV
AGRITWIN_DB_HOST=127.0.0.1
AGRITWIN_DB_PORT=${port}
AGRITWIN_DB_NAME=agritwin_db
AGRITWIN_DB_USER=ceres_db_user
AGRITWIN_DB_PASSWORD=agritwin_local_dev_password
DB_HOST=127.0.0.1
DB_PORT=${port}
DB_DATABASE=agritwin_db
DB_USERNAME=ceres_db_user
DB_PASSWORD=agritwin_local_dev_password
AGRITWIN_DB_PUBLISHED_PORT=${port}
ENV
  chmod 644 "${db_env_file}" || true
}

ensure_local_db_env

echo "[stack] starting AgriTwin database and local support services"
docker compose --profile mock up -d db openagri-mock

echo "[stack] ensuring local database user grants"
for _ in {1..30}; do
  if docker compose exec -T db mysqladmin ping -h localhost -uroot -pagritwin_demo_root_password --silent >/dev/null 2>&1; then
    docker compose exec -T db mysql -uroot -pagritwin_demo_root_password >/dev/null <<'SQL'
CREATE DATABASE IF NOT EXISTS agritwin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'ceres_db_user'@'%' IDENTIFIED BY 'agritwin_local_dev_password';
ALTER USER 'ceres_db_user'@'%' IDENTIFIED BY 'agritwin_local_dev_password';
GRANT ALL PRIVILEGES ON agritwin_db.* TO 'ceres_db_user'@'%';
CREATE USER IF NOT EXISTS 'ceres_db_user'@'localhost' IDENTIFIED BY 'agritwin_local_dev_password';
ALTER USER 'ceres_db_user'@'localhost' IDENTIFIED BY 'agritwin_local_dev_password';
GRANT ALL PRIVILEGES ON agritwin_db.* TO 'ceres_db_user'@'localhost';
FLUSH PRIVILEGES;
USE agritwin_db;
UPDATE farms SET farm_name='Fotovoltaica Macotera SL', farm_code='MACOTERA' WHERE farm_name LIKE '%Demo%' OR farm_code LIKE 'DEMO%';
SQL
    break
  fi
  sleep 2
done

if [[ "${AGRITWIN_START_COMPOSE_APP:-0}" == "1" ]]; then
  echo "[stack] starting optional compose web app on AGRITWIN_APP_PORT"
  docker compose --profile mock up -d app
else
  echo "[stack] Apache/local web root is used for http://localhost/agritwin3/; compose app start skipped"
fi

if [[ -x scripts/openagri/setup_openagri_services.sh ]]; then
  scripts/openagri/setup_openagri_services.sh
fi
if [[ -x scripts/openagri/start_openagri_services.sh ]]; then
  OPENAGRI_VALIDATE_UPSTREAM_COMPOSE="${OPENAGRI_VALIDATE_UPSTREAM_COMPOSE:-0}" \
  OPENAGRI_RUN_CONTAINERS="${OPENAGRI_RUN_CONTAINERS:-1}" \
    scripts/openagri/start_openagri_services.sh
fi

echo "[stack] local stack start complete"
