# Reports Module

## Overview

The Reports module is a professional report centre for AgriTwin operational outputs. The user-facing page renders report cards, metadata, status badges, human-readable previews and PDF actions. It does not display raw JSON as the main report view.

## Report Types

- Field Operational Report
- Weather and Sensor Report
- Irrigation Report
- Pest and Disease Risk Report
- OpenAgri Services Status Report
- KPI / Analytics Report

## Data Sources

Reports are assembled from the existing AgriTwin HMI providers:

- weather source and local station values;
- sensor stations and telemetry summaries;
- irrigation recommendations and water-balance indicators;
- pest and disease risk indicators;
- OpenAgri service health and latency;
- KPI and analytics summaries.

## Generation Flow

1. `agritwin_dashboard/reports.php` builds a human-readable preview using `agritwin_dashboard/includes/reporting.php`.
2. The preview uses the same report model as the PDF and includes a branded header with AgriTwin and Fotovoltaica Macotera SL first, followed by OpenAgri and Obsidian Innovation Institute - Associação.
3. Preview sections use clear operational icons and the generated PDF uses compact section markers with human-readable tables.
4. When the operator generates a report, the local report service writes a valid PDF file under `var/reports/`.
5. The PDF embeds the existing logo assets in the header/footer and includes `© 2026 AgriTwin` plus the required OpenAgri funding acknowledgement on each page.
6. The page exposes Open PDF and Download PDF actions.
7. API endpoints return metadata or the generated PDF. Report listing, generation and download require an authenticated session and do not expose secrets.

## Storage

Generated PDFs are stored in:

```text
var/reports/
```

The directory is ignored by Git except for `.gitkeep`.

## Multilingual Support

The Reports page and PDF labels use the active AgriTwin language: English, Portuguese from Portugal or Spanish from Spain. Product and service names remain unchanged.

## Branding Rules

- Header primary row: AgriTwin logo, then Fotovoltaica Macotera SL logo.
- Header secondary row: OpenAgri logo and Obsidian Innovation Institute - Associação branding.
- Footer: European Union funding logo, `© 2026 AgriTwin`, and the exact OpenAgri funding acknowledgement.

## Verification

Run:

```bash
php -l agritwin_dashboard/reports.php
php -l agritwin_dashboard/includes/reporting.php
php -l api/reports/types/index.php
php -l api/reports/index.php
php -l api/reports/generate/index.php
php -l api/reports/download/index.php
php scripts/i18n/audit_translations.php
make test
docker compose config
scripts/check_local_stack.sh
```

Then curl the Reports page in `en`, `pt-PT` and `es-ES`, generate a sample PDF, and verify that the PDF starts with `%PDF` and contains human-readable report content rather than raw JSON.
