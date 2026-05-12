# AgriTwin Swagger/OpenAPI

Date: 2026-05-12

## URLs

- Swagger UI: `/agritwin3/agritwin_dashboard/api_swagger.php`
- OpenAPI JSON: `/agritwin3/api/openapi.json`

## Endpoints Included

- `GET /agritwin3/api/health`
- `GET /agritwin3/api/openagri/services`
- `GET /agritwin3/api/weather/current`
- `GET /agritwin3/api/sensor-stations`
- `GET /agritwin3/api/sensors/readings`
- `GET /agritwin3/api/irrigation/recommendations`
- `GET /agritwin3/api/pest-disease/alerts`
- `GET /agritwin3/api/farm-calendar/operations`
- `GET /agritwin3/api/kpis`
- `GET /agritwin3/api/users`
- `POST /agritwin3/api/openagri/services/test`
- `POST /agritwin3/api/edge/sensor-packets.php`

## Verification

```bash
curl -fsS http://localhost/agritwin3/api/openapi.json
curl -fsS http://localhost/agritwin3/agritwin_dashboard/api_swagger.php
```

## Extension Notes

Add new API endpoints to `api/openapi.json` when adding backend routes. Keep examples synthetic, omit tokens and credentials, and document whether each endpoint returns live or fallback data.
