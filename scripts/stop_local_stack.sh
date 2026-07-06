#!/usr/bin/env bash
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

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
