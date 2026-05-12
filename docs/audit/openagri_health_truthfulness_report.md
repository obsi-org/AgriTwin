# OpenAgri Health Truthfulness Report

Date: 2026-05-12

## Health-Check Method

The HMI health model reads the `OPENAGRI_*` variables first and the legacy `AGRITWIN_OPENAGRI_*` variables second. A service is shown as `live healthy` only when `OPENAGRI_MODE=live` or `OPENAGRI_MODE=auto`, the endpoint is not a local demo URL, and a real HTTP request returns a successful response.

If the configured mode is `demo`, or the endpoint points to `localhost`, `127.0.0.1` or `openagri-mock`, the UI marks the connector as demo healthy instead of live.

## Current Local Result

The default local development configuration uses Docker/local demo endpoints. Therefore the platform shows OpenAgri services as configured for demo data, not as live healthy integrations.

| Service | Current local mode | Live status claim |
| --- | --- | --- |
| Gatekeeper / Access Control | demo/local when default env is used | Not claimed live |
| Weather Data Service | demo/local or fallback unless a real endpoint succeeds | Not claimed live |
| Irrigation Management Service | demo/local unless live endpoint succeeds | Not claimed live |
| Pest & Disease Management Service | demo/local unless live endpoint succeeds | Not claimed live |
| FarmCalendar | demo/local unless live endpoint succeeds | Not claimed live |
| Reporting Service | demo/local unless live endpoint succeeds | Not claimed live |
| Geodatabase / OCSM | not configured unless endpoint supplied | Not claimed live |

## Weather Source

Weather-driven cards now prefer OpenAgri Weather in live mode, then use Open-Meteo public forecast as a no-secret live fallback, then use local station demo data if live weather cannot be reached.

## Required Environment Variables For Live Checks

- `OPENAGRI_MODE=live` or `OPENAGRI_MODE=auto`
- `OPENAGRI_GATEKEEPER_URL`
- `OPENAGRI_WEATHER_URL`
- `OPENAGRI_IRRIGATION_URL`
- `OPENAGRI_PEST_DISEASE_URL`
- `OPENAGRI_FARMCALENDAR_URL`
- `OPENAGRI_REPORTING_URL`
- `OPENAGRI_GEODATABASE_URL` if available
- Service tokens or Gatekeeper access token where required.

Real credentials must remain in local environment variables or a deployment secret store and must not be committed.

## Limitations

The generic health check uses conservative GET probes. Final production validation must align the probe paths with the current OpenAgri Swagger/OpenAPI contracts for each deployed service.
