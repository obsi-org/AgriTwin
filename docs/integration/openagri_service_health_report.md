# OpenAgri Service Health Report

Date: 2026-05-12

`scripts/openagri/check_openagri_services.sh` was executed against the active local environment. The current `.env` file does not define live OpenAgri endpoint URLs, so the script reported every required service as `not_configured`.

| Service | Current state | Reason |
| --- | --- | --- |
| Gatekeeper / Access Control | Configuration required | No active `OPENAGRI_GATEKEEPER_URL` in the local runtime environment. |
| Weather Data Service | Configuration required | No active `OPENAGRI_WEATHER_URL` in the local runtime environment. |
| Irrigation Management | Configuration required | No active `OPENAGRI_IRRIGATION_URL` in the local runtime environment. |
| Pest & Disease Management | Configuration required | No active `OPENAGRI_PEST_DISEASE_URL` in the local runtime environment. |
| FarmCalendar | Configuration required | No active `OPENAGRI_FARMCALENDAR_URL` in the local runtime environment. |
| Reporting Service | Configuration required | No active `OPENAGRI_REPORTING_URL` in the local runtime environment. |
| Geodatabase / OCSM | Configuration required | No active `OPENAGRI_GEODATABASE_URL` in the local runtime environment. |

The AgriTwin HMI uses the same connector health logic as the JSON API. A connector is marked as live only when a configured real endpoint returns a successful HTTP response. Local fallback states are labelled explicitly.
