#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TARGET_DIR="${ROOT_DIR}/external/openagri"
RUNTIME_DIR="${TARGET_DIR}/runtime"
CONFIG_DIR="${ROOT_DIR}/config"
LOCAL_CONFIG="${CONFIG_DIR}/openagri.local.env"
mkdir -p "${RUNTIME_DIR}" "${CONFIG_DIR}"

token() {
  local name="$1"
  printf 'agritwin-local-%s-%s' "${name}" "$(date +%Y%m%d)"
}

write_if_missing() {
  local path="$1"
  shift
  if [[ -f "${path}" ]]; then
    echo "[env] exists ${path}"
    return 0
  fi
  mkdir -p "$(dirname "${path}")"
  printf '%s\n' "$@" > "${path}"
  chmod 600 "${path}" || true
  echo "[env] generated ${path}"
}

set_env_value() {
  local path="$1"
  local key="$2"
  local value="$3"
  [[ -f "${path}" ]] || return 0
  local tmp
  tmp="$(mktemp)"
  if grep -q "^${key}=" "${path}"; then
    awk -v key="${key}" -v value="${value}" 'BEGIN { prefix=key "=" } index($0, prefix)==1 { print key "=" value; next } { print }' "${path}" > "${tmp}"
  else
    cat "${path}" > "${tmp}"
    printf '%s=%s\n' "${key}" "${value}" >> "${tmp}"
  fi
  mv "${tmp}" "${path}"
  chmod 600 "${path}" || true
}

patch_irrigation_runtime() {
  local main_py="${TARGET_DIR}/irrigation-management/app/main.py"
  [[ -f "${main_py}" ]] || return 0
  if ! grep -q '^import logging$' "${main_py}"; then
    tmp="$(mktemp)"
    {
      printf 'import logging\nimport time\n'
      cat "${main_py}"
    } > "${tmp}"
    mv "${tmp}" "${main_py}"
    echo "[env] patched irrigation runtime imports for local startup"
  elif ! grep -q '^import time$' "${main_py}"; then
    tmp="$(mktemp)"
    awk 'NR==1 { print; print "import time"; next } { print }' "${main_py}" > "${tmp}"
    mv "${tmp}" "${main_py}"
    echo "[env] patched irrigation runtime time import for local startup"
  fi
}

patch_irrigation_runtime

write_if_missing "${TARGET_DIR}/bootstrap-deployment/.env" \
  "JWT_SIGNING_KEY=$(token bootstrap-jwt)" \
  "JWT_ALG=HS256" \
  "JWT_COOKIE_NAME=OpenAgriAuth" \
  "GATEKEEPER_APP_PORT=18091" \
  "FARM_CALENDAR_APP_PORT=18095" \
  "WEATHER_SRV_PORT=18092" \
  "PDM_SERVICE_PORT=18094" \
  "IRR_SERVICE_PORT=18093" \
  "REPORTING_SERVICE_PORT=18096" \
  "WEATHER_SRV_OPENWEATHERMAP_API_KEY=external-provider-configuration-required"

write_if_missing "${TARGET_DIR}/gatekeeper/.env" \
  "TAG=latest" \
  "DOCKER_REGISTRY=openagri-eu" \
  "SOURCE_REPO=https://github.com/openagri-eu/openagri-gatekeeper" \
  "DB_USER=gatekeeper_admin" \
  "DB_NAME=gatekeeper" \
  "DB_PASS=$(token gatekeeper-db)" \
  "DB_HOST=gatekeeper-db" \
  "DB_PORT=3306" \
  "APP_HOST=0.0.0.0" \
  "APP_PORT=8001" \
  "DJANGO_PORT=8001" \
  "SUPERUSER_USERNAME=local_admin" \
  "SUPERUSER_EMAIL=local-admin@example.invalid" \
  "SUPERUSER_PASSWORD=$(token gatekeeper-admin)" \
  "DJANGO_SECRET_KEY=$(token gatekeeper-django)" \
  "JWT_SIGNING_KEY=$(token gatekeeper-jwt)" \
  "ACCESS_TOKEN_SECRET=$(token gatekeeper-access)" \
  "REFRESH_TOKEN_SECRET=$(token gatekeeper-refresh)" \
  "JWT_SECRET=$(token gatekeeper-jwt-secret)" \
  "DJANGO_DEBUG=True" \
  "MAINTENANCE_MODE=OFF" \
  "EXTRA_ALLOWED_HOSTS=localhost,127.0.0.1,gatekeeper" \
  "FARM_CALENDAR_API=http://farmcalendar:8002/api/v1/" \
  "FARM_CALENDAR_POST_AUTH=http://localhost:18095/post_auth/"
set_env_value "${TARGET_DIR}/gatekeeper/.env" "DB_PASS" "agritwin-local-gatekeeper-user"
set_env_value "${TARGET_DIR}/gatekeeper/.env" "DB_HOST" "gatekeeper-db"
set_env_value "${TARGET_DIR}/gatekeeper/.env" "DB_PORT" "3306"
set_env_value "${TARGET_DIR}/gatekeeper/.env" "APP_PORT" "8001"

write_if_missing "${TARGET_DIR}/gatekeeper/mysql_docker/.env" \
  "MYSQL_DATABASE=gatekeeper" \
  "MYSQL_ROOT_PASSWORD=$(token gatekeeper-mysql-root)" \
  "MYSQL_USER=gatekeeper_admin" \
  "MYSQL_PASSWORD=$(token gatekeeper-mysql-user)" \
  "PMA_HOST=mysql" \
  "PMA_USER=gatekeeper_admin" \
  "PMA_PASSWORD=$(token gatekeeper-mysql-user)"

write_if_missing "${TARGET_DIR}/weather-service/.env" \
  "TAG=latest" \
  "DOCKER_REGISTRY=openagri-eu" \
  "SOURCE_REPO=https://github.com/openagri-eu/openagri-weatherservice" \
  "LOGGING_LEVEL=INFO" \
  "WEATHER_SRV_PORT=8000" \
  "WEATHER_SRV_DATABASE_URI=mongodb://root:$(token weather-mongo)@weather-mongo:27017" \
  "WEATHER_SRV_DATABASE_NAME=openagridb" \
  "WEATHER_SRV_OPENWEATHERMAP_API_KEY=external-provider-configuration-required" \
  "WEATHER_SRV_MONGO_INITDB_ROOT_USERNAME=root" \
  "WEATHER_SRV_MONGO_INITDB_ROOT_PASSWORD=$(token weather-mongo)" \
  "WEATHER_SRV_MONGO_INITDB_DATABASE=openagridb" \
  "WEATHER_SRV_EXTRA_ALLOWED_HOSTS=*" \
  "JWT_SIGNING_KEY=$(token weather-jwt)" \
  "JWT_ALG=HS256" \
  "INTERNAL_GK_URL=http://gatekeeper:8001" \
  "GATEKEEPER_SUPERUSER_USERNAME=local_admin" \
  "GATEKEEPER_SUPERUSER_PASSWORD=$(token gatekeeper-admin)" \
  "PUSH_THI_TO_FARMCALENDAR=False" \
  "PUSH_THI_ALERTS_TO_FARMCALENDAR=False" \
  "GATEKEEPER_FARM_CALENDAR_API=http://farmcalendar:8002/api/v1/" \
  "INTERVAL_THI_TO_FARMCALENDAR=8"
set_env_value "${TARGET_DIR}/weather-service/.env" "WEATHER_SRV_DATABASE_URI" "mongodb://weather-mongo:27017"
set_env_value "${TARGET_DIR}/weather-service/.env" "WEATHER_SRV_DATABASE_NAME" "openagridb"
set_env_value "${TARGET_DIR}/weather-service/.env" "WEATHER_SRV_MONGO_INITDB_ROOT_USERNAME" "root"
set_env_value "${TARGET_DIR}/weather-service/.env" "WEATHER_SRV_MONGO_INITDB_ROOT_PASSWORD" "$(token weather-mongo)"

write_if_missing "${TARGET_DIR}/irrigation-management/.env" \
  "TAG=latest" \
  "DOCKER_REGISTRY=openagri-eu" \
  "SOURCE_REPO=https://github.com/openagri-eu/openagri-irrigationmanagement" \
  "POSTGRES_USER=irrigation_user" \
  "POSTGRES_PASSWORD=$(token irrigation-db)" \
  "POSTGRES_HOST=irrigation-db" \
  "POSTGRES_PORT=5432" \
  "POSTGRES_DB=irrigation" \
  "JWT_KEY=$(token irrigation-jwt)" \
  "SERVICE_PORT=8005" \
  "CORS_ORIGINS=[\"*\"]" \
  "JWT_ALGORITHM=HS256" \
  "ACCESS_TOKEN_EXPIRATION_TIME=240" \
  "REFRESH_TOKEN_EXPIRATION_TIME=10080" \
  "USING_GATEKEEPER=False" \
  "GATEKEEPER_BASE_URL=http://gatekeeper:8001" \
  "GATEKEEPER_USERNAME=local_admin" \
  "GATEKEEPER_PASSWORD=$(token gatekeeper-admin)" \
  "SERVICE_NAME=irrigation" \
  "USING_FRONTEND=False"
set_env_value "${TARGET_DIR}/irrigation-management/.env" "ACCESS_TOKEN_EXPIRATION_TIME" "240"
set_env_value "${TARGET_DIR}/irrigation-management/.env" "REFRESH_TOKEN_EXPIRATION_TIME" "10080"

write_if_missing "${TARGET_DIR}/pest-disease-management/.env" \
  "POSTGRES_USER=pest_user" \
  "POSTGRES_PASSWORD=$(token pest-db)" \
  "POSTGRES_HOST=pest-db" \
  "POSTGRES_PORT=5432" \
  "POSTGRES_DB=pest" \
  "JWT_KEY=$(token pest-jwt)" \
  "SERVICE_PORT=8003" \
  "CORS_ORIGINS=[\"*\"]" \
  "JWT_ALGORITHM=HS256" \
  "ACCESS_TOKEN_EXPIRATION_TIME=240" \
  "REFRESH_TOKEN_EXPIRATION_TIME=10080" \
  "USING_GATEKEEPER=False" \
  "GATEKEEPER_BASE_URL=http://gatekeeper:8001" \
  "GATEKEEPER_USERNAME=local_admin" \
  "GATEKEEPER_PASSWORD=$(token gatekeeper-admin)" \
  "SERVICE_NAME=pdm" \
  "LOGGING=INFO"
set_env_value "${TARGET_DIR}/pest-disease-management/.env" "ACCESS_TOKEN_EXPIRATION_TIME" "240"
set_env_value "${TARGET_DIR}/pest-disease-management/.env" "REFRESH_TOKEN_EXPIRATION_TIME" "10080"

write_if_missing "${TARGET_DIR}/farmcalendar/.env" \
  "TAG=latest" \
  "DOCKER_REGISTRY=openagri-eu" \
  "SOURCE_REPO=https://github.com/openagri-eu/openagri-farmcalendar" \
  "POSTGRES_HOST=farmcalendar-db" \
  "POSTGRES_DB=farm_calendar" \
  "POSTGRES_USER=farmcalendar_user" \
  "POSTGRES_PASSWORD=$(token farmcalendar-db)" \
  "DJANGO_SECRET_KEY=$(token farmcalendar-django)" \
  "GATEKEEPER_LOGIN_URL=" \
  "JWT_SIGNING_KEY=$(token farmcalendar-jwt)" \
  "JWT_COOKIE_NAME=OpenAgriAuth" \
  "AUTO_CREATE_AUTH_USER=True" \
  "EXTRA_ALLOWED_HOSTS=localhost,127.0.0.1,farmcalendar" \
  "APP_HOST=0.0.0.0" \
  "APP_PORT=8002" \
  "LOGGING_LEVEL=INFO"

write_if_missing "${TARGET_DIR}/reporting-service/.env" \
  "REPORTING_POSTGRES_USER=reporting_user" \
  "REPORTING_POSTGRES_PASSWORD=$(token reporting-db)" \
  "REPORTING_POSTGRES_DB=reporting" \
  "REPORTING_POSTGRES_HOST=reporting-db" \
  "REPORTING_POSTGRES_PORT=5432" \
  "JWT_SIGNING_KEY=$(token reporting-jwt)" \
  "JWT_ACCESS_TOKEN_EXPIRATION_TIME=240" \
  "REPORTING_SERVICE_NAME=reporting" \
  "REPORTING_SERVICE_PORT=8009" \
  "REPORTING_USING_GATEKEEPER=False" \
  "REPORTING_GATEKEEPER_BASE_URL=http://gatekeeper:8001/" \
  "REPORTING_GATEKEEPER_USERNAME=local_admin" \
  "REPORTING_GATEKEEPER_PASSWORD=$(token gatekeeper-admin)" \
  "REPORTING_BACKEND_CORS_ORIGINS=[\"*\"]"

cat > "${LOCAL_CONFIG}" <<'ENV'
OPENAGRI_MODE=auto
OPENAGRI_GATEKEEPER_URL=http://127.0.0.1:18091
OPENAGRI_GATEKEEPER_HEALTH_URL=/
OPENAGRI_GATEKEEPER_DOCS_URL=http://127.0.0.1:18091/
OPENAGRI_WEATHER_URL=http://127.0.0.1:18092
OPENAGRI_WEATHER_HEALTH_URL=/docs
OPENAGRI_WEATHER_DOCS_URL=http://127.0.0.1:18092/docs
OPENAGRI_WEATHER_DATA_URL=/api/data/weather/?lat=40.9492&lon=-5.6958
OPENAGRI_IRRIGATION_URL=http://127.0.0.1:18093
OPENAGRI_IRRIGATION_HEALTH_URL=/docs
OPENAGRI_IRRIGATION_DOCS_URL=http://127.0.0.1:18093/docs
OPENAGRI_PEST_DISEASE_URL=http://127.0.0.1:18094
OPENAGRI_PEST_DISEASE_HEALTH_URL=/docs
OPENAGRI_PEST_DISEASE_DOCS_URL=http://127.0.0.1:18094/docs
OPENAGRI_FARMCALENDAR_URL=http://127.0.0.1:18095
OPENAGRI_FARMCALENDAR_HEALTH_URL=/api/v1/
OPENAGRI_FARMCALENDAR_DOCS_URL=http://127.0.0.1:18095/api/v1/
OPENAGRI_REPORTING_URL=http://127.0.0.1:18096
OPENAGRI_REPORTING_HEALTH_URL=/docs
OPENAGRI_REPORTING_DOCS_URL=http://127.0.0.1:18096/docs
OPENAGRI_GEODATABASE_URL=
OPENAGRI_GEODATABASE_HEALTH_URL=/health
OPENAGRI_GEODATABASE_DOCS_URL=
AGRITWIN_WEATHER_SOURCE_PREFERENCE=auto
ENV
chmod 644 "${LOCAL_CONFIG}" || true

cat > "${RUNTIME_DIR}/docker-compose.generated.yml" <<'YAML'
services:
  gatekeeper-db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: gatekeeper
      MYSQL_ROOT_PASSWORD: agritwin-local-gatekeeper-root
      MYSQL_USER: gatekeeper_admin
      MYSQL_PASSWORD: agritwin-local-gatekeeper-user
    volumes:
      - gatekeeper_db:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 10

  gatekeeper:
    build:
      context: ../gatekeeper
    env_file:
      - ../gatekeeper/.env
    environment:
      DB_HOST: gatekeeper-db
      DB_PORT: 3306
      APP_PORT: 8001
      DJANGO_PORT: 8001
    depends_on:
      gatekeeper-db:
        condition: service_healthy
    restart: on-failure
    ports:
      - "18091:8001"

  weather-mongo:
    image: mongo:4
    volumes:
      - weather_mongo:/data/db
    healthcheck:
      test: ["CMD", "mongo", "--quiet", "--eval", "db.adminCommand('ping').ok"]
      interval: 10s
      timeout: 5s
      retries: 12

  weather:
    build:
      context: ../weather-service
      dockerfile: Dockerfile.x86_64
    env_file:
      - ../weather-service/.env
    environment:
      WEATHER_SRV_PORT: 8000
      WEATHER_SRV_DATABASE_URI: mongodb://weather-mongo:27017
      INTERNAL_GK_URL: http://gatekeeper:8001
    depends_on:
      weather-mongo:
        condition: service_healthy
    restart: on-failure
    ports:
      - "18092:8000"

  irrigation-db:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: irrigation
      POSTGRES_USER: irrigation_user
      POSTGRES_PASSWORD: agritwin-local-irrigation-db-20260512
    volumes:
      - irrigation_db:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U irrigation_user -d irrigation"]
      interval: 10s
      timeout: 5s
      retries: 12

  irrigation:
    build:
      context: ../irrigation-management
    env_file:
      - ../irrigation-management/.env
    depends_on:
      irrigation-db:
        condition: service_healthy
    restart: on-failure
    entrypoint: ["/usr/local/bin/entrypoint.sh"]
    command: ["8005"]
    ports:
      - "18093:8005"

  pest-db:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: pest
      POSTGRES_USER: pest_user
      POSTGRES_PASSWORD: agritwin-local-pest-db-20260512
    volumes:
      - pest_db:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U pest_user -d pest"]
      interval: 10s
      timeout: 5s
      retries: 12

  pest-disease:
    build:
      context: ../pest-disease-management
    env_file:
      - ../pest-disease-management/.env
    depends_on:
      pest-db:
        condition: service_healthy
    restart: on-failure
    entrypoint: ["/usr/local/bin/entrypoint.sh"]
    command: ["8003"]
    ports:
      - "18094:8003"

  farmcalendar-db:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: farm_calendar
      POSTGRES_USER: farmcalendar_user
      POSTGRES_PASSWORD: agritwin-local-farmcalendar-db-20260512
    volumes:
      - farmcalendar_db:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U farmcalendar_user -d farm_calendar"]
      interval: 10s
      timeout: 5s
      retries: 12

  farmcalendar:
    build:
      context: ../farmcalendar
    env_file:
      - ../farmcalendar/.env
    environment:
      POSTGRES_HOST: farmcalendar-db
      APP_PORT: 8002
    depends_on:
      farmcalendar-db:
        condition: service_healthy
    restart: on-failure
    command: /usr/local/bin/entrypoint.sh
    ports:
      - "18095:8002"

  reporting-db:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: reporting
      POSTGRES_USER: reporting_user
      POSTGRES_PASSWORD: agritwin-local-reporting-db-20260512
    volumes:
      - reporting_db:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U reporting_user -d reporting"]
      interval: 10s
      timeout: 5s
      retries: 12

  reporting:
    build:
      context: ../reporting-service
    env_file:
      - ../reporting-service/.env
    depends_on:
      reporting-db:
        condition: service_healthy
    restart: on-failure
    entrypoint: ["/usr/local/bin/entrypoint.sh"]
    command: ["8009"]
    ports:
      - "18096:8009"

volumes:
  gatekeeper_db:
  weather_mongo:
  irrigation_db:
  pest_db:
  farmcalendar_db:
  reporting_db:
YAML

if [[ -f "${ROOT_DIR}/.env" ]]; then
  tmp_env="$(mktemp)"
  awk '
    BEGIN { skip=0 }
    /^# BEGIN AGRITWIN LOCAL OPENAGRI$/ { skip=1; next }
    /^# END AGRITWIN LOCAL OPENAGRI$/ { skip=0; next }
    skip==0 { print }
  ' "${ROOT_DIR}/.env" > "${tmp_env}"
  {
    cat "${tmp_env}"
    printf '\n# BEGIN AGRITWIN LOCAL OPENAGRI\n'
    cat "${LOCAL_CONFIG}"
    printf '# END AGRITWIN LOCAL OPENAGRI\n'
  } > "${ROOT_DIR}/.env"
  rm -f "${tmp_env}"
  chmod 600 "${ROOT_DIR}/.env" || true
fi

echo "[env] generated local runtime compose ${RUNTIME_DIR}/docker-compose.generated.yml"
echo "[env] generated local AgriTwin config ${LOCAL_CONFIG}"
