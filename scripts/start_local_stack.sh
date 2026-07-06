#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

random_secret() {
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -hex 24
  else
    date +%s%N | sha256sum | awk '{print $1}'
  fi
}

env_value() {
  local key="$1"
  [[ -f .env ]] || return 1
  awk -F= -v key="${key}" '$1 == key { sub(/^[^=]*=/, ""); print; found=1; exit } END { exit found ? 0 : 1 }' .env
}

set_env_value() {
  local key="$1"
  local value="$2"
  local file="${3:-.env}"
  local tmp
  mkdir -p "$(dirname "${file}")"
  touch "${file}"
  tmp="$(mktemp)"
  if grep -q "^${key}=" "${file}"; then
    awk -v key="${key}" -v value="${value}" 'BEGIN { done=0 } $0 ~ "^" key "=" { print key "=" value; done=1; next } { print } END { if (!done) print key "=" value }' "${file}" > "${tmp}"
  else
    cat "${file}" > "${tmp}"
    printf '%s=%s\n' "${key}" "${value}" >> "${tmp}"
  fi
  mv "${tmp}" "${file}"
}

needs_generated_secret() {
  local value="${1:-}"
  [[ -z "${value}" ]] && return 0
  [[ "${value}" == *"replace-with"* ]] && return 0
  [[ "${value}" == *"change-me"* ]] && return 0
  [[ "${value}" == *"GENERATED_BY_START_SCRIPT"* ]] && return 0
  return 1
}

ensure_local_env() {
  [[ -f .env ]] || cp .env.example .env
  chmod 600 .env || true

  local app_port="${AGRITWIN_APP_PORT:-$(env_value AGRITWIN_APP_PORT || echo 8083)}"
  local db_port="${AGRITWIN_DB_PUBLISHED_PORT:-$(env_value AGRITWIN_DB_PUBLISHED_PORT || echo 33063)}"
  local db_name="${AGRITWIN_DB_NAME:-$(env_value AGRITWIN_DB_NAME || echo agritwin_db)}"
  local db_user="${AGRITWIN_DB_USER:-$(env_value AGRITWIN_DB_USER || echo agritwin_app)}"
  local db_password
  local db_root_password

  db_password="$(env_value AGRITWIN_DB_PASSWORD || true)"
  if needs_generated_secret "${db_password}"; then
    db_password="$(random_secret)"
  fi
  db_root_password="$(env_value AGRITWIN_DB_ROOT_PASSWORD || true)"
  if needs_generated_secret "${db_root_password}"; then
    db_root_password="$(random_secret)"
  fi

  set_env_value AGRITWIN_APP_PORT "${app_port}"
  set_env_value AGRITWIN_BASE_PATH "/agritwin"
  set_env_value APP_BASE_PATH "/agritwin"
  set_env_value AGRITWIN_DB_PUBLISHED_PORT "${db_port}"
  set_env_value AGRITWIN_DB_NAME "${db_name}"
  set_env_value AGRITWIN_DB_USER "${db_user}"
  set_env_value AGRITWIN_DB_PASSWORD "${db_password}"
  set_env_value AGRITWIN_DB_ROOT_PASSWORD "${db_root_password}"
  set_env_value DB_HOST "127.0.0.1"
  set_env_value DB_PORT "${db_port}"
  set_env_value DB_DATABASE "${db_name}"
  set_env_value DB_USERNAME "${db_user}"
  set_env_value DB_PASSWORD "${db_password}"

  set_env_value OPENAGRI_MODE "${OPENAGRI_MODE:-auto}"
  set_env_value OPENAGRI_GATEKEEPER_URL "${OPENAGRI_GATEKEEPER_URL:-http://host.docker.internal:18091}"
  set_env_value OPENAGRI_WEATHER_URL "${OPENAGRI_WEATHER_URL:-http://host.docker.internal:18092}"
  set_env_value OPENAGRI_IRRIGATION_URL "${OPENAGRI_IRRIGATION_URL:-http://host.docker.internal:18093}"
  set_env_value OPENAGRI_PEST_DISEASE_URL "${OPENAGRI_PEST_DISEASE_URL:-http://host.docker.internal:18094}"
  set_env_value OPENAGRI_FARMCALENDAR_URL "${OPENAGRI_FARMCALENDAR_URL:-http://host.docker.internal:18095}"
  set_env_value OPENAGRI_REPORTING_URL "${OPENAGRI_REPORTING_URL:-http://host.docker.internal:18096}"
  set_env_value AGRITWIN_OPENAGRI_MODE "${AGRITWIN_OPENAGRI_MODE:-auto}"
  set_env_value AGRITWIN_OPENAGRI_GATEKEEPER_URL "${AGRITWIN_OPENAGRI_GATEKEEPER_URL:-http://host.docker.internal:18091}"
  set_env_value AGRITWIN_OPENAGRI_WEATHER_URL "${AGRITWIN_OPENAGRI_WEATHER_URL:-http://host.docker.internal:18092}"
  set_env_value AGRITWIN_OPENAGRI_IRRIGATION_URL "${AGRITWIN_OPENAGRI_IRRIGATION_URL:-http://host.docker.internal:18093}"
  set_env_value AGRITWIN_OPENAGRI_PEST_DISEASE_URL "${AGRITWIN_OPENAGRI_PEST_DISEASE_URL:-http://host.docker.internal:18094}"
  set_env_value AGRITWIN_OPENAGRI_FARMCALENDAR_URL "${AGRITWIN_OPENAGRI_FARMCALENDAR_URL:-http://host.docker.internal:18095}"
  set_env_value AGRITWIN_OPENAGRI_REPORTING_URL "${AGRITWIN_OPENAGRI_REPORTING_URL:-http://host.docker.internal:18096}"

  mkdir -p config
  cat > config/database.local.env <<ENV
AGRITWIN_DB_HOST=127.0.0.1
AGRITWIN_DB_PORT=${db_port}
AGRITWIN_DB_NAME=${db_name}
AGRITWIN_DB_USER=${db_user}
AGRITWIN_DB_PASSWORD=${db_password}
DB_HOST=127.0.0.1
DB_PORT=${db_port}
DB_DATABASE=${db_name}
DB_USERNAME=${db_user}
DB_PASSWORD=${db_password}
AGRITWIN_DB_PUBLISHED_PORT=${db_port}
ENV
  chmod 600 config/database.local.env || true
}

ensure_local_env

if [[ "${AGRITWIN_START_OPENAGRI:-0}" == "1" ]]; then
  if [[ -x scripts/openagri/setup_openagri_services.sh ]]; then
    scripts/openagri/setup_openagri_services.sh || true
  fi
  if [[ -x scripts/openagri/start_openagri_services.sh ]]; then
    OPENAGRI_VALIDATE_UPSTREAM_COMPOSE="${OPENAGRI_VALIDATE_UPSTREAM_COMPOSE:-0}" \
    OPENAGRI_RUN_CONTAINERS="${OPENAGRI_RUN_CONTAINERS:-1}" \
      scripts/openagri/start_openagri_services.sh || true
  fi
fi

echo "[stack] starting AgriTwin Docker web and database services"
docker compose up -d --build --remove-orphans agritwin-db agritwin-web

echo "[stack] waiting for database health"
for _ in $(seq 1 60); do
  status="$(docker inspect -f '{{.State.Health.Status}}' "$(docker compose ps -q agritwin-db)" 2>/dev/null || true)"
  [[ "${status}" == "healthy" ]] && break
  sleep 2
done

echo "[stack] waiting for web health"
for _ in $(seq 1 60); do
  if curl -fsS "http://localhost:${AGRITWIN_APP_PORT:-8083}/agritwin/index.php" >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

echo "[stack] AgriTwin local URL: http://localhost:${AGRITWIN_APP_PORT:-8083}/agritwin/"
echo "[stack] local stack start complete"
