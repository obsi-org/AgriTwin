# AgriTwin Swagger/OpenAPI

Date: 2026-05-12

## URLs

- Swagger UI: `/agritwin3/agritwin_dashboard/api_swagger.php`
- OpenAPI JSON: `/agritwin3/api/openapi.json`

## Endpoints Included

- `GET /agritwin3/api/health`
- `GET /agritwin3/api/openagri/services`
- `GET /agritwin3/api/openagri/core-services`
- `GET /agritwin3/api/weather/current`
- `GET /agritwin3/api/weather/configuration/status`
- `GET /agritwin3/api/weather/diagnostics`
- `POST /agritwin3/api/weather/api-key`
- `POST /agritwin3/api/weather/api-key/validate`
- `POST /agritwin3/api/weather/api-key/remove`
- `GET /agritwin3/api/analytics/summary`
- `GET /agritwin3/api/analytics/weather`
- `GET /agritwin3/api/analytics/sensor-stations`
- `GET /agritwin3/api/analytics/irrigation`
- `GET /agritwin3/api/analytics/pest-disease`
- `GET /agritwin3/api/analytics/kpis`
- `GET /agritwin3/api/analytics/service-freshness`
- `GET /agritwin3/api/sensor-stations`
- `GET /agritwin3/api/sensors/readings`
- `GET /agritwin3/api/irrigation/recommendations`
- `GET /agritwin3/api/pest-disease/alerts`
- `GET /agritwin3/api/farm-calendar/operations`
- `GET /agritwin3/api/kpis`
- `GET /agritwin3/api/reports/types`
- `GET /agritwin3/api/reports`
- `POST /agritwin3/api/reports/generate`
- `GET /agritwin3/api/reports/download`
- `GET /agritwin3/api/users`
- `POST /agritwin3/api/openagri/services/test`
- `POST /agritwin3/api/openagri/services/check-all`
- `POST /agritwin3/api/edge/sensor-packets.php`

## Verification

```bash
curl -fsS http://localhost/agritwin3/api/openapi.json
curl -fsS http://localhost/agritwin3/agritwin_dashboard/api_swagger.php
```

## Extension Notes

Add new API endpoints to `api/openapi.json` when adding backend routes. Keep examples synthetic, omit tokens and credentials, and document whether each endpoint returns live or fallback data.

Weather API key write endpoints require a settings-authorized session and CSRF token. The OpenAPI schema marks the submitted key as `writeOnly`; status and diagnostics responses never return the raw value.

Report endpoints generate and list professional PDF reports. JSON responses are metadata only; the report deliverable is served as `application/pdf`.
