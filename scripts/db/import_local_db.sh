#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

if [[ $# -ne 1 || ! -f "$1" ]]; then
  echo "Usage: scripts/db/import_local_db.sh /path/to/agritwin.sql" >&2
  exit 2
fi

dump_path="$1"

echo "[db] importing SQL dump into Docker database without deleting volumes"
docker compose exec -T agritwin-db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < "${dump_path}"
echo "[db] import complete"
