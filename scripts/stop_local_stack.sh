#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

if [[ -x scripts/openagri/stop_openagri_services.sh ]]; then
  scripts/openagri/stop_openagri_services.sh
fi

docker compose stop \
  agritwin-web \
  openagri-gatekeeper \
  openagri-weather \
  openagri-irrigation \
  openagri-pest-disease \
  openagri-farmcalendar \
  openagri-reporting \
  openagri-fixture \
  agritwin-db
echo "[stack] stopped local services without deleting volumes"
