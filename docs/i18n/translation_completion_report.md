# Translation Completion Report

Date: 2026-05-13

## Translation Architecture

AgriTwin uses central PHP translation dictionaries in `agritwin_dashboard/lang/` for English (`en`), Portuguese from Portugal (`pt-PT`) and Spanish from Spain (`es-ES`). The shared helper `agritwin_t()` renders translated labels, while `agritwin_current_language()` accepts `?lang=`, then persists the selection in session and cookie state under `/agritwin3/`.

The shared HMI layout now exposes a small JavaScript-safe translation object for dynamic browser labels such as connection state, queued operations and advisory-report titles.

## Pages Audited

- Login and profile
- User Management
- Dashboard / Field Overview
- Analytics / Statistics
- Digital Twin and Cesium field view
- Sensor Stations / Telemetry
- Irrigation
- Pest & Disease
- Farm Calendar
- Reports
- KPIs
- OpenAgri Services
- API / Swagger surrounding page
- Settings, Weather configuration and database diagnostics
- Sidebar, header and footer

## Fixes Completed

- Added an automated translation audit at `scripts/i18n/audit_translations.php`.
- Moved shared station, crop, block, weather-source, role and JavaScript labels into translation files.
- Replaced hardcoded labels in Digital Twin, Energy Management, User Profile, User Management and the Cesium field view.
- Added translated role names and operational field-view labels.
- Confirmed all three dictionaries contain the same number of keys, with no missing or empty values.
- Confirmed active translation strings do not contain blocked project/prototype wording.

## Terminology Decisions

Portuguese uses Portugal terminology such as `utilizador`, `Definições`, `Rega`, `Estações de Sensores` and `Modo local de contingência`.

Spanish uses Spain terminology such as `Configuración`, `Riego`, `Estaciones de sensores` and `Modo local de contingencia`.

The following product and technical terms intentionally remain unchanged: AgriTwin, OpenAgri, Fotovoltaica Macotera SL, OBSIDIAN, FarmCalendar, Swagger, OpenAPI, API, JSON, OAuth2, JWT, Docker, PHP, SQL, KPI, NDVI, NDRE, LAI_SAVI, Sentinel Hub, ET0, Kc, Cesium and Chart.js.

## Verification

Primary command:

```bash
php scripts/i18n/audit_translations.php
```

Expected result:

```text
translation-audit: locales=en,pt-PT,es-ES keys=399 hard_failures=0
```

The generated detailed reports are:

- `docs/i18n/translation_audit_report.md`
- `docs/i18n/translation_audit_report.json`
