#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "${ROOT_DIR}"

backup_dir="${ROOT_DIR}/var/backups"
mkdir -p "${backup_dir}"
timestamp="$(date +%Y%m%d-%H%M%S)"
output="${backup_dir}/agritwin-db-${timestamp}.sql"

echo "[db] exporting Docker database to ignored runtime path"
docker compose exec -T agritwin-db sh -lc 'mysqldump --single-transaction --quick -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > "${output}"
chmod 600 "${output}" || true
echo "[db] export complete: ${output}"
