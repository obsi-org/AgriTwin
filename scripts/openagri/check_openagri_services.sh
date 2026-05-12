#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"
if [[ -f "${ENV_FILE}" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "${ENV_FILE}"
  set +a
fi

check_url() {
  local name="$1"
  local url="$2"
  local path="$3"
  if [[ -z "${url}" ]]; then
    echo "${name}|not_configured|Configuration required"
    return 0
  fi
  local target="${path}"
  if [[ "${path}" != http* ]]; then
    target="${url%/}/${path#/}"
  fi
  local started status latency
  started="$(date +%s%3N)"
  status="$(curl -k -sS -o /tmp/agritwin-openagri-check.out -w '%{http_code}' --max-time 5 "${target}" 2>/tmp/agritwin-openagri-check.err || echo 000)"
  latency="$(( $(date +%s%3N) - started ))"
  if [[ "${status}" =~ ^2|3 ]]; then
    echo "${name}|available|${target}|${status}|${latency}ms"
  elif [[ "${status}" == "401" || "${status}" == "403" ]]; then
    echo "${name}|credentials_required|${target}|${status}|${latency}ms"
  else
    echo "${name}|unavailable|${target}|${status}|${latency}ms"
  fi
}

check_url "gatekeeper" "${OPENAGRI_GATEKEEPER_URL:-}" "${OPENAGRI_GATEKEEPER_HEALTH_URL:-/health}"
check_url "weather" "${OPENAGRI_WEATHER_URL:-}" "${OPENAGRI_WEATHER_HEALTH_URL:-/api/data/forecast5?lat=40.9492&lon=-5.6958}"
check_url "irrigation" "${OPENAGRI_IRRIGATION_URL:-}" "${OPENAGRI_IRRIGATION_HEALTH_URL:-/api/irrigation/advisory}"
check_url "pest_disease" "${OPENAGRI_PEST_DISEASE_URL:-}" "${OPENAGRI_PEST_DISEASE_HEALTH_URL:-/api/pest/risk}"
check_url "farmcalendar" "${OPENAGRI_FARMCALENDAR_URL:-}" "${OPENAGRI_FARMCALENDAR_HEALTH_URL:-/api/calendar/operations}"
check_url "reporting" "${OPENAGRI_REPORTING_URL:-}" "${OPENAGRI_REPORTING_HEALTH_URL:-/api/reports}"
check_url "geodatabase" "${OPENAGRI_GEODATABASE_URL:-}" "${OPENAGRI_GEODATABASE_HEALTH_URL:-/health}"
