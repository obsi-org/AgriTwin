# OpenAgri Services Configuration

Date: 2026-05-12

## Environment Variables

Use environment variables or deployment secret stores. Do not commit real credentials.

| Variable | Purpose |
| --- | --- |
| `OPENAGRI_MODE` | `demo`, `live` or `auto`. |
| `OPENAGRI_GATEKEEPER_URL` | Gatekeeper/access-control endpoint. |
| `OPENAGRI_GATEKEEPER_CLIENT_ID` | Client identifier, if required. |
| `OPENAGRI_GATEKEEPER_CLIENT_SECRET` | Client secret, stored outside git. |
| `OPENAGRI_WEATHER_URL` | OpenAgri Weather Data Service endpoint. |
| `OPENAGRI_IRRIGATION_URL` | OpenAgri Irrigation Management endpoint. |
| `OPENAGRI_PEST_DISEASE_URL` | OpenAgri Pest & Disease Management endpoint. |
| `OPENAGRI_FARMCALENDAR_URL` | OpenAgri FarmCalendar endpoint. |
| `OPENAGRI_REPORTING_URL` | OpenAgri Reporting Service endpoint. |
| `OPENAGRI_GEODATABASE_URL` | Optional OpenAgri Geodatabase/OCSM endpoint. |

Legacy `AGRITWIN_OPENAGRI_*` variables remain supported for compatibility.

## Modes

- `demo`: do not claim live status; show configured demo service health.
- `live`: mark a service `live healthy` only after a real HTTP request succeeds.
- `auto`: attempt live checks and explicitly show fallback/demo status if live checks fail.

## Health Determination

Each service reports configured state, mode, status, endpoint, HTTP status, latency, last live success timestamp, last demo/fallback generation timestamp and a secret-free error summary.

## Weather Verification

The weather source is live OpenAgri only when the OpenAgri Weather endpoint is configured in live mode and returns a valid response. Otherwise AgriTwin attempts Open-Meteo public forecast as a no-secret live fallback and labels it as fallback. If both live paths fail, local station demo data is used and labelled as local demo fallback.
