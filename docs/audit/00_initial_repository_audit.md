# Initial Repository Audit

Audit date: 2026-05-12

Authoritative source documents:

- `docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf`
- `docs/source/AgriTwin_D1_20260124.pdf`

## Current Directory Structure

```text
.
├── Dockerfile
├── Makefile
├── index.php
├── db_init.php
├── mysql.sql
├── docs/
│   ├── audit/
│   └── source/
└── agritwin_dashboard/
    ├── *.php dashboard pages
    ├── mobile.html
    ├── property_boundaries.js
    ├── crop CSVs and image assets
    ├── result.gltf/result.bin
    └── solar_panel/ 3D model and texture assets
```

Top-level static assets include AgriTwin/OpenAgri/EU/partner images and duplicate dashboard images.

## Detected Languages and Frameworks

| Area | Detected stack |
| --- | --- |
| Backend | PHP 8-style procedural pages using sessions and PDO |
| Database | MySQL/MariaDB schema and seed dump in `mysql.sql`; runtime connection in `agritwin_dashboard/db_config.php` |
| Frontend | Server-rendered HTML/CSS/JavaScript embedded in PHP |
| Geospatial/digital twin | CesiumJS loaded from external CDNs in `field1.php` and `mobile.html` |
| External browser assets | Font Awesome CDN, Cesium CDN, NASA GIBS WMS, Copernicus/Sentinel Hub WMS, Google map tile/icon URLs |
| Packaging | Minimal `Dockerfile` based on `php:8.3-apache`; no compose or Kubernetes manifests |
| Tests | No unit, integration, browser, or API test framework detected |

## Entry Points and Services

- `index.php`: root login page, starts session, loads farms, authenticates users, redirects to `agritwin_dashboard/index.php`.
- `db_init.php`: idempotent seed helper for minimal `app_settings`, `farms`, and `users` tables. It currently contains a SQL seed defect and a fallback include path that references `ceres_dashboard`.
- `agritwin_dashboard/index.php`: dashboard overview reading latest farm measurements.
- `agritwin_dashboard/field.php`: redirects to farm-specific field pages. It references `field2.php` and `field3.php`, which are absent.
- `agritwin_dashboard/field1.php`: Cesium-based field monitoring view with hard-coded farm coordinates, local CSV tree overlays, WMS NDVI/LAI/SAVI layers, weather fetch, and local 3D assets.
- `agritwin_dashboard/meteo.php`: proxy to AEMET municipality weather API.
- `agritwin_dashboard/ai_recommendations.php`: displays seeded recommendation records.
- `agritwin_dashboard/task_scheduling.php`: displays seeded farm task schedules.
- `agritwin_dashboard/energy.php`: displays seeded energy readings.
- `agritwin_dashboard/settings.php`: edits Cesium and Copernicus values in `app_settings`; displays static sensor/actuator setup fields.
- `agritwin_dashboard/user_profile.php`: mostly static example profile form.
- `agritwin_dashboard/change_farm.php` and `logout.php`: session utilities.

## Data Models

`mysql.sql` defines seeded tables for:

- `ai_recommendations`
- `energy_readings`
- `farms`
- `farm_actuators`
- `farm_measurements`
- `farm_sensors`
- `field_data`
- `server_performance`
- `task_schedules`
- `users`

The schema stores many sensor and KPI-like values as formatted strings rather than typed numeric/time-series measurements, which limits analytics and validation.

## API Clients and External Integrations

Current direct integrations visible in code:

- AEMET OpenData API in `agritwin_dashboard/meteo.php`.
- Cesium Ion token usage in `field1.php`/settings.
- Copernicus Dataspace/Sentinel Hub WMS URL construction in `field1.php`.
- NASA GIBS WMS NDVI imagery in `field1.php` and `mobile.html`.
- CesiumJS and Font Awesome CDNs.
- Google tile/icon URLs in `field1.php`.

Referenced but not implemented as real clients in this local codebase:

- OpenAgri Weather Data Service.
- OpenAgri Irrigation Service.
- OpenAgri Pest & Disease Management Service.
- OpenAgri Gatekeeper/access control.
- FarmCalendar.
- Reporting Service/PDF advisory export.

## How to Run Locally

Current pragmatic path:

1. Provision MySQL or MariaDB.
2. Create database/user matching `agritwin_dashboard/db_config.php` or update that file locally.
3. Import `mysql.sql`.
4. Run `make lint` to confirm PHP syntax.
5. Run `make run` and open `http://127.0.0.1:8080`.

The Dockerfile is incomplete for the current app: it only copies root `index.php`, not `agritwin_dashboard/`, assets, SQL, or database services.

## Missing Environment Variables

No environment-variable based configuration exists. The following should be externalized before any shared deployment:

- `AGRITWIN_DB_HOST`
- `AGRITWIN_DB_NAME`
- `AGRITWIN_DB_USER`
- `AGRITWIN_DB_PASSWORD`
- `AGRITWIN_CESIUM_TOKEN`
- `AGRITWIN_COPERNICUS_ID`
- `AGRITWIN_AEMET_API_KEY`
- `AGRITWIN_OPENAGRI_GATEKEEPER_URL`
- `AGRITWIN_OPENAGRI_GATEKEEPER_CLIENT_ID`
- `AGRITWIN_OPENAGRI_GATEKEEPER_CLIENT_SECRET`
- `AGRITWIN_OPENAGRI_WEATHER_URL`
- `AGRITWIN_OPENAGRI_IRRIGATION_URL`
- `AGRITWIN_OPENAGRI_PEST_DISEASE_URL`
- `AGRITWIN_FARMCALENDAR_URL`

## Current Security Risks

- Hard-coded database credentials in `agritwin_dashboard/db_config.php`.
- Hard-coded Cesium token and Copernicus identifier in `db_init.php`/settings data.
- Hard-coded AEMET API key in `agritwin_dashboard/meteo.php`.
- Seeded administrator password hash and placeholder user hash in source SQL.
- Database connection errors are echoed to users.
- Session cookies are not configured with secure defaults in app bootstrap.
- No CSRF protection on login, settings, farm switch, or profile forms.
- No role-based authorization beyond `$_SESSION['user_id']`.
- Settings page can expose and edit API credentials in the browser.
- External scripts/styles are loaded from CDNs without subresource integrity.
- `change_farm.php` redirects to raw `HTTP_REFERER`.
- Missing farm pages referenced by `field.php` can create broken navigation.
- Raw/private farm data segregation is not implemented beyond this `.gitignore` baseline.

## Current Documentation Gaps

- No README with setup, credentials, database import, or prototype scope.
- No architecture document explaining cloud-only vs mixed edge-cloud ADS.
- No OpenAPI/API client documentation.
- No data model dictionary or migration strategy.
- No deployment documentation for Docker, compose, or Kubernetes.
- No operational notes for tokens, secrets, backups, or GDPR controls.
- No test plan beyond this audit.
- No documentation connecting local files to proposal/D1 requirements before this audit.

## Current Test Coverage

- No automated test suite detected.
- Safe verification run during audit: `php -l` across all PHP files passed.
- See `docs/audit/02_test_gap_report.md` for recommended coverage.

## Alignment and Gaps Against AgriTwin Proposal and D1

The source documents define AgriTwin as an open-source ADS with an AI-powered DSS, Environmental Digital Twin, IoT/edge gateway ingestion, Copernicus/EO and weather ingestion, OpenAgri service integration, Gatekeeper access control, offline-first PWA operation, cloud-only and mixed edge-cloud variants, KPI capture, and GDPR-aware deployment.

Current local alignment:

- Provides a PHP dashboard prototype with login, farm switching, overview cards, task schedule cards, recommendation cards, energy cards, settings, and profile pages.
- Includes a Cesium-based field monitoring view with farm boundaries, NDVI/EO overlays, local tree CSV overlays, sensor/machine/weather markers, and 3D assets.
- Includes seeded data for farms, measurements, sensors, actuators, tasks, recommendations, energy readings, and server performance.
- Includes source PDFs under `docs/source/`.

Major gaps:

- No real AI model code, predictive DSS pipeline, model serving, training workflow, or validation metrics.
- No implemented OpenAgri Gatekeeper, Weather, Irrigation, Pest & Disease, FarmCalendar, or Reporting Service clients.
- No IoT ingestion service, edge gateway runtime, buffering, sync queue, or typed telemetry API.
- No offline-first PWA service worker, manifest, local cache, or sync behavior in the PHP app.
- No cloud-only vs mixed edge-cloud deployment manifests.
- No KPI capture for latency, forecast MAE, nitrogen error, uptime, network volume, CPU/GPU hours, energy, interoperability, or switchability.
- No GDPR/data protection design beyond session login and password hashes.

