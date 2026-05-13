# Mandatory OpenAgri Services

Date: 2026-05-13

AgriTwin treats four OpenAgri services as the core Proposal/D1 compliance set:

1. OpenAgri Gatekeeper / Access Control
2. OpenAgri Weather Data Service
3. OpenAgri Irrigation Management Service
4. OpenAgri Pest & Disease Management Service

FarmCalendar and Reporting Service remain operational extensions. Geodatabase / OCSM is optional for the local D1 readiness view and is not counted as a compliance failure when it is not configured.

## Proposal/D1 Traceability

| Mandatory service | AgriTwin role | Proposal/D1 evidence |
| --- | --- | --- |
| Gatekeeper / Access Control | Authentication, access control and trusted service boundary for OpenAgri calls. | Shows that AgriTwin can use the OpenAgri access-control pattern before invoking reusable services. |
| Weather Data Service | Weather forecasts and agrometeorological indicators for DSS inputs. | Supports irrigation, pest/disease and water-stress decision inputs. |
| Irrigation Management Service | ET0, soil-moisture analysis and irrigation advisory support. | Supports the AI-powered irrigation DSS and irrigation schedule evidence. |
| Pest & Disease Management Service | Disease-risk, growing-degree-day and pest-risk calculations. | Supports pest-management advisory requirements. |

## Environment Variables

| Service | Base URL | Health/docs URL |
| --- | --- | --- |
| Gatekeeper | `OPENAGRI_GATEKEEPER_URL` | `OPENAGRI_GATEKEEPER_HEALTH_URL`, `OPENAGRI_GATEKEEPER_DOCS_URL` |
| Weather | `OPENAGRI_WEATHER_URL` | `OPENAGRI_WEATHER_HEALTH_URL`, `OPENAGRI_WEATHER_DOCS_URL` |
| Irrigation | `OPENAGRI_IRRIGATION_URL` | `OPENAGRI_IRRIGATION_HEALTH_URL`, `OPENAGRI_IRRIGATION_DOCS_URL` |
| Pest & Disease | `OPENAGRI_PEST_DISEASE_URL` | `OPENAGRI_PEST_DISEASE_HEALTH_URL`, `OPENAGRI_PEST_DISEASE_DOCS_URL` |

Credentials remain outside git. The local template is `config/openagri.local.env.example`; the active local file is `config/openagri.local.env` and must stay untracked.

## Health Checks

Use:

```bash
scripts/openagri/check_openagri_services.sh --mandatory-only
```

The command checks mandatory services first and returns a non-zero exit code if any mandatory service is not available, unless `--warnings-only` is also supplied. FarmCalendar and Reporting are checked by the full command. Geodatabase / OCSM is reported as optional and does not fail the mandatory compliance check when it is not configured.

The AgriTwin API exposes the mandatory subset at:

```text
/agritwin3/api/openagri/mandatory-services
```

## Weather Observation Limitation

The local Weather Service container can be reachable while the operational weather observations endpoint still requires external provider credentials. In that state AgriTwin reports the Weather service reachability separately from weather observations and uses the public no-secret weather source or local fallback only with truthful source labels.
