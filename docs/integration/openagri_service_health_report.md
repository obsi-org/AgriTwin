# OpenAgri Service Health Report

Date: 2026-05-13

AgriTwin now reports OpenAgri health in three groups. The first group is the Proposal/D1 mandatory set and is checked before extensions or optional services.

## Mandatory OpenAgri Services - Proposal/D1

| Service | Local endpoint | Current result | D1 interpretation |
| --- | --- | --- | --- |
| Gatekeeper / Access Control | `http://127.0.0.1:18091/` | Available | Mandatory access-control path is reachable locally. |
| Weather Data Service | `http://127.0.0.1:18092/docs` | Service reachable; observations may require external provider credentials | Mandatory weather service is reachable, but live observations still require provider configuration when the data endpoint returns authentication/provider errors. |
| Irrigation Management | `http://127.0.0.1:18093/docs` | Available | Mandatory irrigation advisory integration path is reachable locally. |
| Pest & Disease Management | `http://127.0.0.1:18094/docs` | Available | Mandatory pest/disease integration path is reachable locally. |

## Operational Extensions

| Service | Local endpoint | Current result |
| --- | --- | --- |
| FarmCalendar | `http://127.0.0.1:18095/api/v1/` | Available |
| Reporting Service | `http://127.0.0.1:18096/docs` | Available |

## Optional Services

| Service | Current result | Compliance interpretation |
| --- | --- | --- |
| Geodatabase / OCSM | Optional - not configured | Not required for the local D1 mandatory service check. |

## Commands

```bash
scripts/openagri/check_openagri_services.sh --mandatory-only
scripts/openagri/check_openagri_services.sh
curl -fsS http://localhost/agritwin3/api/openagri/mandatory-services
curl -fsS http://localhost/agritwin3/api/openagri/services
```

The full health script returns non-zero when a mandatory service is missing, unavailable or requires credentials. Missing optional Geodatabase / OCSM configuration is reported but does not fail the mandatory compliance check.
