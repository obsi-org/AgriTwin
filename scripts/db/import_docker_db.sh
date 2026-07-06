#!/usr/bin/env bash
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
exec "${ROOT_DIR}/scripts/db/import_local_db.sh" "$@"
