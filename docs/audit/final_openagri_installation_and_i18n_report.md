# Final OpenAgri Installation and I18N Report

Date: 2026-05-12

## Central Pages Translated

The central PHP HMI pages now use `agritwin_t()` with translation files for English, Portuguese from Portugal and Spanish from Spain. Pages checked:

- Dashboard / Field Overview
- Digital Twin Map
- Sensor Stations
- Irrigation Schedule
- Pest & Disease Alerts
- Farm Calendar
- Reports
- KPI Dashboard
- OpenAgri Services
- API / Swagger
- Settings
- Login
- User Profile
- User Management

The language selector persists `?lang=` choices through session and cookie state.

## OpenAgri Services Installed Locally

`scripts/openagri/setup_openagri_services.sh` cloned the upstream repositories into ignored path `external/openagri/`.

| Service | Commit | Running state |
| --- | --- | --- |
| Bootstrap Deployment | `34fc4a6` | Not running; compose file empty in checkout. |
| Gatekeeper | `49f4bc3` | Not running; official compose requires local database `.env`. |
| Weather Service | `340aace` | Not running; official compose requires registry, MongoDB, Gatekeeper and external weather-provider settings. |
| Irrigation Management | `01e9eb1` | Not running; Dockerfile found, no compose file. |
| Pest and Disease Management | `3aa67a9` | Not running; Dockerfile found, no compose file. |
| FarmCalendar | `bfebfea` | Not running; official compose requires PostgreSQL, JWT and Django settings. |
| Reporting Service | `2ef1b0d` | Not running; Dockerfile found, no compose file. |

## Endpoint URLs

AgriTwin supports local OpenAgri URLs through `.env.example` and `docker-compose.openagri.yml`, using ports `18091` to `18097`. The active `.env` does not currently configure these URLs, so the runtime service-health script reports `Configuration required`.

## Swagger/API URLs

- Swagger UI: `/agritwin3/agritwin_dashboard/api_swagger.php`
- OpenAPI JSON: `/agritwin3/api/openapi.json`

The OpenAPI document includes OpenAgri service-status, check-all, weather, sensor-station, irrigation, pest/disease, farm-calendar, KPI and user endpoints.

## Weather Source Status

The dashboard first checks configured OpenAgri Weather. If unavailable, it attempts the public Open-Meteo no-secret source. If that is unavailable, it uses local fallback station values and labels that state truthfully.

## Verification Summary

Commands run during this phase:

```bash
scripts/openagri/setup_openagri_services.sh
scripts/openagri/start_openagri_services.sh
scripts/openagri/check_openagri_services.sh
```

Further syntax, rendered-page, Docker and API checks are recorded in the final assistant response for this commit.

## Remaining Manual Actions

- Provide real OpenAgri service `.env` files and credentials outside git.
- Start upstream services with `OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh` once their required settings are available.
- Set the corresponding `OPENAGRI_*_URL`, health path and docs URL variables in AgriTwin.
- Re-run `scripts/openagri/check_openagri_services.sh` and verify the OpenAgri Services HMI page.
