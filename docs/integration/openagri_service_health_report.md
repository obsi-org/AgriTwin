# OpenAgri Service Health Report

Date: 2026-07-06

AgriTwin now reports OpenAgri health in three groups. The first group is the core OpenAgri service set and is checked before extensions or optional services.

## Core OpenAgri Services

| Service | Local endpoint | Current result | Operational interpretation |
| --- | --- | --- | --- |
| Gatekeeper / Access Control | `http://127.0.0.1:18091/` | Available | Core access-control path is reachable locally. |
| Weather Data Service | `http://127.0.0.1:18092/docs` | Service reachable; observations may require external provider credentials | Core weather service is reachable, but live observations still require provider configuration when the data endpoint returns authentication/provider errors. |
| Irrigation Management | `http://127.0.0.1:18093/docs` | Available | Core irrigation advisory integration path is reachable locally. |
| Pest & Disease Management | `http://127.0.0.1:18094/docs` | Available | Core pest/disease integration path is reachable locally. |

## Operational Extensions

| Service | Local endpoint | Current result |
| --- | --- | --- |
| FarmCalendar | `http://127.0.0.1:18095/api/v1/` | Available |
| Reporting Service | `http://127.0.0.1:18096/docs` | Available |

## Optional Services

| Service | Current result | Operational interpretation |
| --- | --- | --- |
| Geodatabase / OCSM | Optional service - not configured | Not required for the core service check. |

## Commands

```bash
scripts/openagri/check_openagri_services.sh --core-only
scripts/openagri/check_openagri_services.sh
curl -fsS http://localhost:8083/agritwin/api/openagri/core-services
curl -fsS http://localhost:8083/agritwin/api/openagri/services
```

The full health script returns non-zero when a core or additional operational service is missing, unavailable or requires credentials. Missing optional Geodatabase / OCSM configuration is reported but does not fail the local service check.
