# Analytics Professionalisation Report

Date: 2026-05-13

## Changes

- Reworked `/agritwin3/agritwin_dashboard/analytics.php` into a full operational analytics workspace.
- Added professional filters for time range, station and crop/block.
- Replaced basic single-value charts with richer local SVG visualisations:
  - weather multi-line trend;
  - rainfall and temperature combination chart;
  - soil moisture and soil temperature station trends;
  - station irrigation-need ranking;
  - water-balance and pest-risk gauges;
  - irrigation recommendation timeline;
  - OpenAgri service freshness table.
- Expanded `agritwin_dashboard/includes/statistics_engine.php` with richer summaries, deterministic time-series, rankings, freshness scores, water-balance score and operational insights.
- Updated analytics API payloads to include richer chart metadata and summaries.
- Added multilingual labels in English, Portuguese from Portugal and Spanish from Spain.

## Data Treatment

The module uses the existing Macotera station and OpenAgri status providers. Longer historic storage is not yet connected, so the time-series layer derives deterministic operational sequences from current station values. The UI identifies this as a local operational dataset rather than claiming live historical telemetry.

## Verification

Planned verification:

```bash
php -l agritwin_dashboard/includes/statistics_engine.php
php -l agritwin_dashboard/analytics.php
make test
docker compose config
scripts/check_local_stack.sh
scripts/openagri/check_openagri_services.sh --mandatory-only
curl -s -L http://localhost/agritwin3/agritwin_dashboard/analytics.php
curl -s -L http://localhost/agritwin3/api/analytics/summary
```

## Remaining Limitations

- Live historical sensor persistence is still required for production-grade rolling 7-day and 30-day analytics.
- Live OpenAgri Weather observations still require valid upstream provider credentials.
