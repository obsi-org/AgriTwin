#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TARGET_DIR="${ROOT_DIR}/external/openagri"
mkdir -p "${TARGET_DIR}"

clone_or_update() {
  local name="$1"
  local primary="$2"
  local alternate="${3:-}"
  local dest="${TARGET_DIR}/${name}"
  local url="${primary}"

  if [[ ! -d "${dest}/.git" ]]; then
    echo "[setup] cloning ${name} from ${primary}"
    if ! git clone --depth 1 "${primary}" "${dest}"; then
      if [[ -n "${alternate}" ]]; then
        echo "[setup] primary failed for ${name}; trying ${alternate}"
        rm -rf "${dest}"
        git clone --depth 1 "${alternate}" "${dest}" || {
          echo "[setup] failed to clone ${name}"
          rm -rf "${dest}"
          return 0
        }
        url="${alternate}"
      else
        echo "[setup] failed to clone ${name}"
        rm -rf "${dest}"
        return 0
      fi
    fi
  else
    echo "[setup] updating ${name}"
    git -C "${dest}" pull --ff-only || true
  fi

  local commit="unknown"
  commit="$(git -C "${dest}" rev-parse --short HEAD 2>/dev/null || echo unknown)"
  local compose_files
  compose_files="$(find "${dest}" -maxdepth 3 \( -name 'docker-compose*.yml' -o -name 'compose*.yml' \) -print | sed "s#${dest}/##" | tr '\n' ' ')"
  local dockerfiles
  dockerfiles="$(find "${dest}" -maxdepth 3 -name 'Dockerfile*' -print | sed "s#${dest}/##" | tr '\n' ' ')"
  printf '%s|%s|%s|%s|%s\n' "${name}" "${url}" "${commit}" "${compose_files:-none}" "${dockerfiles:-none}" >> "${TARGET_DIR}/openagri_checkout_inventory.txt"
}

: > "${TARGET_DIR}/openagri_checkout_inventory.txt"
clone_or_update "bootstrap-deployment" "https://github.com/openagri-eu/OpenAgri-Bootstrap-Deployment.git"
clone_or_update "gatekeeper" "https://github.com/agstack/OpenAgri-GateKeeper.git" "https://github.com/openagri-eu/OpenAgri-GateKeeper.git"
clone_or_update "weather-service" "https://github.com/agstack/OpenAgri-WeatherService.git" "https://github.com/openagri-eu/OpenAgri-WeatherService.git"
clone_or_update "irrigation-management" "https://github.com/agstack/OpenAgri-IrrigationManagement.git" "https://github.com/openagri-eu/OpenAgri-IrrigationManagement.git"
clone_or_update "pest-disease-management" "https://github.com/openagri-eu/pest-and-disease-management.git"
clone_or_update "farmcalendar" "https://github.com/openagri-eu/farmcalendar.git"
clone_or_update "reporting-service" "https://github.com/openagri-eu/reporting-service.git"

echo "[setup] checkout inventory: ${TARGET_DIR}/openagri_checkout_inventory.txt"
cat "${TARGET_DIR}/openagri_checkout_inventory.txt"

"${ROOT_DIR}/scripts/openagri/generate_openagri_local_env.sh"
