#!/usr/bin/env bash
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TARGET_DIR="${ROOT_DIR}/external/openagri"
GENERATED_COMPOSE="${TARGET_DIR}/runtime/docker-compose.generated.yml"
RUN_CONTAINERS="${OPENAGRI_RUN_CONTAINERS:-0}"

if [[ ! -d "${TARGET_DIR}" ]]; then
  echo "[start] ${TARGET_DIR} does not exist; run setup_openagri_services.sh first"
  exit 0
fi

"${ROOT_DIR}/scripts/openagri/generate_openagri_local_env.sh"

start_repo() {
  local repo_dir="$1"
  local name
  name="$(basename "${repo_dir}")"
  local compose_file
  if [[ "${name}" == "weather-service" && "$(uname -m)" != "aarch64" ]]; then
    compose_file="$(find "${repo_dir}" -maxdepth 3 -name 'docker-compose-x86_64.yml' -print | head -n 1)"
  else
    compose_file="$(find "${repo_dir}" -maxdepth 3 \( -name 'docker-compose*.yml' -o -name 'compose*.yml' \) -print | head -n 1)"
  fi
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

if [[ "${OPENAGRI_VALIDATE_UPSTREAM_COMPOSE:-1}" == "1" ]]; then
  for repo in "${TARGET_DIR}"/*; do
    [[ -d "${repo}" ]] || continue
    [[ "$(basename "${repo}")" == "runtime" ]] && continue
    start_repo "${repo}"
  done
fi

if [[ -f "${GENERATED_COMPOSE}" ]]; then
  echo "[start] validating generated local runtime compose"
  docker compose -f "${GENERATED_COMPOSE}" config >/tmp/agritwin-openagri-generated.config
  if [[ "${RUN_CONTAINERS}" == "1" ]]; then
    echo "[start] starting generated local OpenAgri runtime"
    docker compose -f "${GENERATED_COMPOSE}" up -d --build || true
  else
    echo "[start] generated runtime validated; set OPENAGRI_RUN_CONTAINERS=1 to start containers"
  fi
fi

echo "[start] AgriTwin compose overlay validation"
docker compose -f "${ROOT_DIR}/docker-compose.yml" -f "${ROOT_DIR}/docker-compose.openagri.yml" config >/tmp/agritwin-openagri-overlay.config
echo "[start] overlay config ok"
