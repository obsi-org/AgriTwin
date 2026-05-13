#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

echo "[check] docker compose services"
docker compose --profile mock ps

echo "[check] database container connectivity"
docker compose exec -T db mysql \
  -uceres_db_user \
  -pagritwin_local_dev_password \
  -e 'SELECT DATABASE() AS db, COUNT(*) AS farms FROM farms;' agritwin_db >/tmp/agritwin-db-check.out
cat /tmp/agritwin-db-check.out

echo "[check] host database connectivity"
php -r 'require "agritwin_dashboard/db_config.php"; if (!isset($pdo) || !($pdo instanceof PDO)) { fwrite(STDERR, "host-db-unavailable\n"); exit(1); } echo "host-db-ok\n";'

if [[ -x scripts/openagri/check_openagri_services.sh ]]; then
  echo "[check] OpenAgri local services"
  scripts/openagri/check_openagri_services.sh
fi

echo "[check] rendered pages"
for url in \
  http://localhost/agritwin3/index.php \
  http://localhost/agritwin3/agritwin_dashboard/index.php \
  http://localhost/agritwin3/agritwin_dashboard/settings.php \
  http://localhost/agritwin3/agritwin_dashboard/openagri_services.php
do
  code="$(curl -s -L -o /tmp/agritwin-page-check.out -w '%{http_code}' "$url")"
  echo "${code} ${url}"
  if grep -Eiq 'SQLSTATE|Access denied|Database status: Unavailable|demo|mock|dummy|fake|stub|GAIA|Pilot HMI' /tmp/agritwin-page-check.out; then
    echo "[check] unexpected rendered diagnostic in ${url}" >&2
    grep -Ein 'SQLSTATE|Access denied|Database status: Unavailable|demo|mock|dummy|fake|stub|GAIA|Pilot HMI' /tmp/agritwin-page-check.out | head -10 >&2
    exit 1
  fi
done

echo "[check] local stack ok"
