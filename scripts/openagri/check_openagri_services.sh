#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"
LOCAL_OPENAGRI_ENV="${ROOT_DIR}/config/openagri.local.env"
MANDATORY_ONLY=0
WARNINGS_ONLY=0
MANDATORY_FAILURE=0
SERVICE_FAILURE=0

for arg in "$@"; do
  case "${arg}" in
    --core-only) MANDATORY_ONLY=1 ;;
    --mandatory-only) MANDATORY_ONLY=1 ;;
    --warnings-only) WARNINGS_ONLY=1 ;;
    -h|--help)
      cat <<'USAGE'
Usage: scripts/openagri/check_openagri_services.sh [--core-only] [--mandatory-only] [--warnings-only]

Checks core OpenAgri services first:
  gatekeeper, weather, irrigation, pest_disease

Additional services are checked unless --core-only or --mandatory-only is used:
  farmcalendar, reporting

Optional Geodatabase / OCSM is reported when configured but does not fail the
core service check.
USAGE
      exit 0
      ;;
    *)
      echo "Unknown option: ${arg}" >&2
      exit 2
      ;;
  esac
done

if [[ -f "${ENV_FILE}" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "${ENV_FILE}"
  set +a
fi
if [[ -f "${LOCAL_OPENAGRI_ENV}" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "${LOCAL_OPENAGRI_ENV}"
  set +a
fi

check_url() {
  local group="$1"
  local name="$2"
  local url="$3"
  local path="$4"
  if [[ -z "${url}" ]]; then
    echo "${group}|${name}|not_configured|Configuration required"
    if [[ "${group}" == "core" ]]; then
      MANDATORY_FAILURE=1
      SERVICE_FAILURE=1
    elif [[ "${group}" == "extension" && "${MANDATORY_ONLY}" -eq 0 ]]; then
      SERVICE_FAILURE=1
    fi
    return 0
  fi
  local target="${path}"
  if [[ "${path}" != http* ]]; then
    target="${url%/}/${path#/}"
  fi
  local started status latency body_file err_file
  body_file="$(mktemp /tmp/agritwin-openagri-body.XXXXXX)"
  err_file="$(mktemp /tmp/agritwin-openagri-error.XXXXXX)"
  started="$(date +%s%3N)"
  status="$(curl -k -sS -o "${body_file}" -w '%{http_code}' --max-time 5 "${target}" 2>"${err_file}" || echo 000)"
  latency="$(( $(date +%s%3N) - started ))"
  rm -f "${body_file}" "${err_file}"
  if [[ "${status}" =~ ^[23] ]]; then
    echo "${group}|${name}|available|${target}|${status}|${latency}ms"
  elif [[ "${status}" == "401" || "${status}" == "403" ]]; then
    echo "${group}|${name}|credentials_required|${target}|${status}|${latency}ms"
    if [[ "${group}" == "core" ]]; then
      MANDATORY_FAILURE=1
      SERVICE_FAILURE=1
    elif [[ "${group}" == "extension" && "${MANDATORY_ONLY}" -eq 0 ]]; then
      SERVICE_FAILURE=1
    fi
  else
    echo "${group}|${name}|unavailable|${target}|${status}|${latency}ms"
    if [[ "${group}" == "core" ]]; then
      MANDATORY_FAILURE=1
      SERVICE_FAILURE=1
    elif [[ "${group}" == "extension" && "${MANDATORY_ONLY}" -eq 0 ]]; then
      SERVICE_FAILURE=1
    fi
  fi
}

echo "Core OpenAgri services"
check_url "core" "gatekeeper" "${OPENAGRI_GATEKEEPER_URL:-}" "${OPENAGRI_GATEKEEPER_HEALTH_URL:-/health}"
check_url "core" "weather" "${OPENAGRI_WEATHER_URL:-}" "${OPENAGRI_WEATHER_HEALTH_URL:-/api/data/forecast5?lat=40.9492&lon=-5.6958}"
check_url "core" "irrigation" "${OPENAGRI_IRRIGATION_URL:-}" "${OPENAGRI_IRRIGATION_HEALTH_URL:-/api/irrigation/advisory}"
check_url "core" "pest_disease" "${OPENAGRI_PEST_DISEASE_URL:-}" "${OPENAGRI_PEST_DISEASE_HEALTH_URL:-/api/pest/risk}"

if [[ "${MANDATORY_ONLY}" -eq 0 ]]; then
  echo "Additional operational services"
  check_url "extension" "farmcalendar" "${OPENAGRI_FARMCALENDAR_URL:-}" "${OPENAGRI_FARMCALENDAR_HEALTH_URL:-/api/calendar/operations}"
  check_url "extension" "reporting" "${OPENAGRI_REPORTING_URL:-}" "${OPENAGRI_REPORTING_HEALTH_URL:-/api/reports}"

  echo "Optional services"
  if [[ -n "${OPENAGRI_GEODATABASE_URL:-}" ]]; then
    check_url "optional" "geodatabase" "${OPENAGRI_GEODATABASE_URL:-}" "${OPENAGRI_GEODATABASE_HEALTH_URL:-/health}"
  else
    echo "optional|geodatabase|optional_not_configured|Optional service"
  fi
fi

if [[ "${SERVICE_FAILURE}" -ne 0 && "${WARNINGS_ONLY}" -ne 1 ]]; then
  exit 1
fi
exit 0
