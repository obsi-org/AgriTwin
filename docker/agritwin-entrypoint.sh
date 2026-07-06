#!/usr/bin/env sh
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

set -eu

APP_DIR="/var/www/html/agritwin"

mkdir -p \
  "${APP_DIR}/var/cache/openagri" \
  "${APP_DIR}/var/queue/openagri" \
  "${APP_DIR}/var/kpi" \
  "${APP_DIR}/var/edge/cache" \
  "${APP_DIR}/var/edge/queue" \
  "${APP_DIR}/var/cloud_dss" \
  "${APP_DIR}/var/reports" \
  "${APP_DIR}/var/runtime"

chown -R www-data:www-data "${APP_DIR}/var" 2>/dev/null || true
chmod -R ug+rwX "${APP_DIR}/var" 2>/dev/null || true

exec docker-php-entrypoint "$@"
