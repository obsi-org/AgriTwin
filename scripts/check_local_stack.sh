#!/usr/bin/env bash
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

APP_PORT="${AGRITWIN_APP_PORT:-$(awk -F= '$1=="AGRITWIN_APP_PORT"{print $2; found=1; exit} END{if(!found) print "8083"}' .env 2>/dev/null || echo 8083)}"
BASE_URL="http://localhost:${APP_PORT}/agritwin"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "${TMP_DIR}"' EXIT

echo "[check] docker compose services"
docker compose ps

wait_for_service_health() {
  local service="$1"
  local container_id
  for _ in $(seq 1 60); do
    container_id="$(docker compose ps -q "${service}" 2>/dev/null || true)"
    if [[ -n "${container_id}" ]]; then
      status="$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "${container_id}" 2>/dev/null || true)"
      if [[ "${status}" == "healthy" || "${status}" == "running" ]]; then
        echo "[check] ${service} status: ${status}"
        return 0
      fi
    fi
    sleep 2
  done
  echo "[check] ${service} did not become healthy in time" >&2
  return 1
}

wait_for_service_health agritwin-db
wait_for_service_health agritwin-web

echo "[check] database container connectivity"
docker compose exec -T agritwin-db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT DATABASE() AS db, COUNT(*) AS farms FROM farms;" "$MYSQL_DATABASE"' >"${TMP_DIR}/db.out"
cat "${TMP_DIR}/db.out"

echo "[check] application database connectivity inside container"
docker compose exec -T agritwin-web php -r 'require "agritwin_dashboard/db_config.php"; if (!isset($pdo) || !($pdo instanceof PDO)) { fwrite(STDERR, "container-db-unavailable\n"); exit(1); } echo "container-db-ok\n";'

if [[ -x scripts/openagri/check_openagri_services.sh ]]; then
  echo "[check] OpenAgri local services"
  if scripts/openagri/check_openagri_services.sh >/tmp/agritwin-openagri-check.out 2>&1; then
    tr -d '\000' </tmp/agritwin-openagri-check.out
  else
    tr -d '\000' </tmp/agritwin-openagri-check.out
    echo "[check] required OpenAgri local services are not fully available" >&2
    exit 1
  fi
fi

cookie_file="${TMP_DIR}/cookie.txt"
auth_available=0
if [[ -f LOCAL_CREDENTIALS.md ]]; then
  username="$(awk '/Username: pedro.branco/{print $2; exit}' LOCAL_CREDENTIALS.md 2>/dev/null || true)"
  password="$(awk -F': ' '/Temporary password:/{print $2; exit}' LOCAL_CREDENTIALS.md 2>/dev/null || true)"
  if [[ -n "${username}" && -n "${password}" ]]; then
    curl -sS -c "${cookie_file}" -b "${cookie_file}" -L \
      --data-urlencode "username=${username}" \
      --data-urlencode "password=${password}" \
      --data-urlencode "farm_id=1" \
      "${BASE_URL}/index.php" >"${TMP_DIR}/login.out" || true
    if ! grep -qi 'login-container' "${TMP_DIR}/login.out"; then
      auth_available=1
    fi
  fi
fi

check_page() {
  local url="$1"
  local label="$2"
  local output="${TMP_DIR}/${label}.html"
  local code
  if [[ "${auth_available}" == "1" ]]; then
    code="$(curl -sS -b "${cookie_file}" -L -o "${output}" -w '%{http_code}' "${url}")"
  else
    code="$(curl -sS -L -o "${output}" -w '%{http_code}' "${url}")"
  fi
  echo "${code} ${url}"
  if [[ "${code}" != "200" ]]; then
    echo "[check] unexpected HTTP status for ${url}" >&2
    exit 1
  fi
  if grep -Eiq 'SQLSTATE|Access denied|LOCAL_CREDENTIALS|GAIA|Pilot HMI|proposal|deliverable|WP1|WP2' "${output}"; then
    echo "[check] unexpected rendered diagnostic in ${url}" >&2
    grep -Ein 'SQLSTATE|Access denied|LOCAL_CREDENTIALS|GAIA|Pilot HMI|proposal|deliverable|WP1|WP2' "${output}" | head -10 >&2
    exit 1
  fi
}

echo "[check] rendered pages"
check_page "${BASE_URL}/" "entry"
check_page "${BASE_URL}/index.php" "index"
check_page "${BASE_URL}/agritwin_dashboard/index.php" "dashboard"
check_page "${BASE_URL}/agritwin_dashboard/field1.php" "field1"
check_page "${BASE_URL}/agritwin_dashboard/reports.php" "reports"
check_page "${BASE_URL}/agritwin_dashboard/openagri_services.php" "openagri"
check_page "${BASE_URL}/agritwin_dashboard/settings.php" "settings"
check_page "${BASE_URL}/agritwin_dashboard/api_swagger.php" "swagger"
check_page "${BASE_URL}/api/health" "api_health"
check_page "${BASE_URL}/api/ai/status" "api_ai_status"

if [[ "${auth_available}" == "1" ]]; then
  check_page "${BASE_URL}/agritwin_dashboard/ai_models.php" "ai_models"
  grep -q 'cesiumContainer' "${TMP_DIR}/field1.html" || { echo "[check] field1 Cesium container not found" >&2; exit 1; }
  grep -q 'report-branding-panel' "${TMP_DIR}/reports.html" || { echo "[check] reports page did not render report centre" >&2; exit 1; }
fi

if grep -Eq '<pre|\\{&quot;|\\{"' "${TMP_DIR}/reports.html"; then
  echo "[check] reports page appears to expose raw JSON" >&2
  exit 1
fi

if ! grep -q "OpenAgri has received funding from the European Union" "${TMP_DIR}/index.html"; then
  echo "[check] funding acknowledgement not found on entry page" >&2
  exit 1
fi

echo "[check] report PDF generation inside container"
docker compose exec -T agritwin-web php -r 'require "agritwin_dashboard/includes/reporting.php"; $r = agritwin_report_generate("field-operational", "es-ES"); $bytes = file_get_contents($r["path"]); if (substr($bytes, 0, 4) !== "%PDF") { exit(1); } echo "pdf-ok\n";'

echo "[check] local stack ok"
