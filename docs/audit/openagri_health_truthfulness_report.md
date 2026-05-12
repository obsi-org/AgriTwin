# OpenAgri Health Truthfulness Report

Date: 2026-05-12

## Health-Check Method

The HMI health model reads the `AGRITWIN_OPENAGRI_*_URL`, token and mode environment variables. A service is shown as `live healthy` only when `AGRITWIN_OPENAGRI_MODE=live`, the endpoint is not a local mock URL, and a real HTTP request returns a successful response.

If the configured mode is `mock`, or the endpoint points to `localhost`, `127.0.0.1` or `openagri-mock`, the UI marks the connector as local demo/mock instead of live.

## Current Local Result

The default local development configuration uses Docker/local mock endpoints. Therefore the platform shows OpenAgri services as configured for local demo data, not as live healthy integrations.

| Service | Current local mode | Live status claim |
| --- | --- | --- |
| Gatekeeper / Access Control | mock/local demo when default env is used | Not claimed live |
| Weather Data Service | mock/local demo or fallback unless a real endpoint succeeds | Not claimed live |
| Irrigation Management Service | mock/local demo unless live endpoint succeeds | Not claimed live |
| Pest & Disease Management Service | mock/local demo unless live endpoint succeeds | Not claimed live |
| FarmCalendar | mock/local demo unless live endpoint succeeds | Not claimed live |
| Reporting Service | mock/local demo unless live endpoint succeeds | Not claimed live |

## Weather Source

Weather-driven cards now display `Weather source: local demo data` unless the OpenAgri Weather Data Service is configured in live mode and a real request succeeds. Local fallback values are generated from the synthetic Macotera dataset for 2026-05-12.

## Required Environment Variables For Live Checks

- `AGRITWIN_OPENAGRI_MODE=live`
- `AGRITWIN_OPENAGRI_GATEKEEPER_URL`
- `AGRITWIN_OPENAGRI_WEATHER_URL`
- `AGRITWIN_OPENAGRI_IRRIGATION_URL`
- `AGRITWIN_OPENAGRI_PEST_DISEASE_URL`
- `AGRITWIN_OPENAGRI_FARMCALENDAR_URL`
- `AGRITWIN_OPENAGRI_REPORTING_URL`
- Service tokens or Gatekeeper access token where required.

Real credentials must remain in local environment variables or a deployment secret store and must not be committed.

## Limitations

The generic health check uses conservative GET probes. Final production validation must align the probe paths with the current OpenAgri Swagger/OpenAPI contracts for each deployed service.
