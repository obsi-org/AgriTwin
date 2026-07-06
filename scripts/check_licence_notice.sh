#!/usr/bin/env bash
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

failures=0

fail() {
  echo "[licence] $*" >&2
  failures=$((failures + 1))
}

require_file() {
  local file="$1"
  [[ -f "${file}" ]] || fail "${file} is missing"
}

require_contains() {
  local file="$1"
  local pattern="$2"
  local label="$3"
  if [[ ! -f "${file}" ]]; then
    fail "${file} is missing for ${label}"
    return
  fi
  LC_ALL=C grep -Fq "${pattern}" "${file}" || fail "${file} does not contain ${label}"
}

require_file LICENSE
require_file NOTICE
require_file README.md

require_contains LICENSE "Apache License" "Apache License heading"
require_contains LICENSE "Version 2.0, January 2004" "Apache License 2.0 version"
require_contains NOTICE "AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making" "AgriTwin notice title"
require_contains NOTICE "Copyright 2026 Obsidian Innovation Institute - Associação" "copyright owner"
require_contains NOTICE "OpenAgri has received funding from the European Union’s Horizon Europe research and innovation programme under Grant Agreement No. 101134083." "OpenAgri funding acknowledgement"
require_contains NOTICE "The reusable AgriTwin software components are released under the Apache License 2.0." "Apache-2.0 component statement"
require_contains NOTICE "Raw pilot-farm data, credentials, security secrets, site-specific configuration and non-anonymised operational logs are excluded from the public release." "excluded materials statement"
require_contains NOTICE "This output reflects only the author’s view. The European Commission cannot be held responsible for any use that may be made of the information contained therein." "European Commission disclaimer"
require_contains README.md "## Licence, funding and disclaimer" "README licence section"

if [[ -f agritwin_dashboard/includes/reporting.php ]]; then
  require_contains agritwin_dashboard/includes/reporting.php "agritwin_funding_acknowledgement" "report funding footer"
  require_contains agritwin_dashboard/includes/reporting.php "agritwin_ec_responsibility_disclaimer" "report European Commission disclaimer"
  require_contains agritwin_dashboard/includes/reporting.php "copyright_owner" "report copyright owner"
else
  fail "agritwin_dashboard/includes/reporting.php is missing"
fi

header_phrase="AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making"
mapfile -t source_files < <(
  git ls-files --cached --others --exclude-standard -- \
    '*.php' '*.js' '*.css' '*.sh' '*.sql' \
    'Dockerfile' 'docker-compose*.yml' |
  grep -Ev '^(vendor/|node_modules/|external/openagri/|generated/|storage/|var/|docs/reference/)' |
  grep -Ev '(^|/)(.*\\.min\\.js|.*\\.min\\.css)$' |
  sort
)

for file in "${source_files[@]}"; do
  if [[ ! -f "${file}" ]]; then
    continue
  fi
  if ! head -n 12 "${file}" | grep -Fq "${header_phrase}"; then
    fail "${file} is missing the AgriTwin source header"
  fi
done

if (( failures > 0 )); then
  echo "[licence] failed with ${failures} issue(s)" >&2
  exit 1
fi

echo "[licence] licence, notice, report disclaimer and source header checks passed"
