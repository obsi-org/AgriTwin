# AgriTwin Swagger/OpenAPI

Date: 2026-05-12

## URLs

- API / Swagger catalogue page: `/agritwin3/agritwin_dashboard/api_swagger.php`
- OpenAPI JSON: `/agritwin3/api/openapi.json`

## Endpoints Included

- `GET /agritwin3/api/health.php`
- `GET /agritwin3/api/openagri-health.php`
- `GET /agritwin3/api/sensors.php`
- `GET /agritwin3/api/weather-current.php`
- `POST /agritwin3/api/edge/sensor-packets.php`

## Extension Notes

Add new API endpoints to `api/openapi.json` when adding backend routes. Keep examples synthetic, omit tokens and credentials, and document whether each endpoint returns live, local demo or fallback data.
