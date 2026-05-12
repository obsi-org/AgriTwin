#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TARGET_DIR="${ROOT_DIR}/external/openagri"
GENERATED_COMPOSE="${TARGET_DIR}/runtime/docker-compose.generated.yml"

if [[ -f "${GENERATED_COMPOSE}" ]]; then
  echo "[stop] generated local OpenAgri runtime"
  docker compose -f "${GENERATED_COMPOSE}" down --remove-orphans || true
fi

if [[ -d "${TARGET_DIR}" ]]; then
  for repo in "${TARGET_DIR}"/*; do
    [[ -d "${repo}" ]] || continue
    compose_file="$(find "${repo}" -maxdepth 3 \( -name 'docker-compose*.yml' -o -name 'compose*.yml' \) -print | head -n 1)"
    if [[ -n "${compose_file}" ]]; then
      echo "[stop] $(basename "${repo}")"
      docker compose -f "${compose_file}" down --remove-orphans || true
    fi
  done
fi

docker compose -f "${ROOT_DIR}/docker-compose.yml" -f "${ROOT_DIR}/docker-compose.openagri.yml" config >/tmp/agritwin-openagri-overlay.config
echo "[stop] overlay config ok"
