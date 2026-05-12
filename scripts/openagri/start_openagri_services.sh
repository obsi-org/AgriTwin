#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TARGET_DIR="${ROOT_DIR}/external/openagri"
RUN_CONTAINERS="${OPENAGRI_RUN_CONTAINERS:-0}"

if [[ ! -d "${TARGET_DIR}" ]]; then
  echo "[start] ${TARGET_DIR} does not exist; run setup_openagri_services.sh first"
  exit 0
fi

start_repo() {
  local repo_dir="$1"
  local name
  name="$(basename "${repo_dir}")"
  local compose_file
  compose_file="$(find "${repo_dir}" -maxdepth 3 \( -name 'docker-compose*.yml' -o -name 'compose*.yml' \) -print | head -n 1)"
  if [[ -z "${compose_file}" ]]; then
    echo "[start] ${name}: no Docker Compose file found"
    return 0
  fi

  echo "[start] ${name}: validating ${compose_file}"
  if ! docker compose -f "${compose_file}" config >/tmp/agritwin-openagri-${name}.config 2>/tmp/agritwin-openagri-${name}.err; then
    echo "[start] ${name}: compose config failed"
    sed -n '1,40p' /tmp/agritwin-openagri-${name}.err
    return 0
  fi

  if [[ "${RUN_CONTAINERS}" == "1" ]]; then
    echo "[start] ${name}: starting official compose stack"
    docker compose -f "${compose_file}" up -d --remove-orphans || true
  else
    echo "[start] ${name}: compose validated; set OPENAGRI_RUN_CONTAINERS=1 to start upstream containers"
  fi
}

for repo in "${TARGET_DIR}"/*; do
  [[ -d "${repo}" ]] || continue
  start_repo "${repo}"
done

echo "[start] AgriTwin compose overlay validation"
docker compose -f "${ROOT_DIR}/docker-compose.yml" -f "${ROOT_DIR}/docker-compose.openagri.yml" config >/tmp/agritwin-openagri-overlay.config
echo "[start] overlay config ok"
