# Final OpenAgri Runtime Report

Date: 2026-05-12

## Automated Actions

- Generated ignored local `.env` files for cloned OpenAgri services.
- Generated ignored `external/openagri/runtime/docker-compose.generated.yml`.
- Added committed template `config/openagri.local.env.example`.
- Appended local OpenAgri URL configuration to the ignored AgriTwin `.env`.
- Built and started the generated local OpenAgri Docker runtime with `OPENAGRI_RUN_CONTAINERS=1`.
- Added database health waits and restart behaviour to the generated runtime compose.
- Patched the ignored local Irrigation Management checkout during generation to add missing runtime imports required for startup.
- Rechecked AgriTwin pages and API endpoints against the locally running service URLs.
- Removed a rendered SQLSTATE failure path by making the login and settings pages degrade to a professional database-unavailable state when the host MySQL credentials are not accepted.

## Mandatory Services - Proposal/D1

| Service | Local URL | Result |
| --- | --- | --- |
| Gatekeeper | `http://127.0.0.1:18091` | Available |
| Weather Service | `http://127.0.0.1:18092` | Service available; weather data endpoint requires authentication/provider configuration |
| Irrigation Management | `http://127.0.0.1:18093` | Available |
| Pest and Disease Management | `http://127.0.0.1:18094` | Available |

These four services form the mandatory AgriTwin/OpenAgri integration evidence for Proposal/D1: access control, weather inputs, irrigation advisory support, and pest/disease risk support.

## Operational Extensions

| Service | Local URL | Result |
| --- | --- | --- |
| FarmCalendar | `http://127.0.0.1:18095` | Available through `/api/v1/` |
| Reporting Service | `http://127.0.0.1:18096` | Available |

## Optional Services

| Service | Local URL | Result |
| --- | --- | --- |
| Geodatabase / OCSM | Not configured | Configuration required |

Geodatabase / OCSM is optional for the current local D1 readiness check. Its missing configuration is not interpreted as a failure of the mandatory service set.

## Weather Source

The local OpenAgri Weather Service process and documentation endpoint are available. The operational weather data endpoint requires an authenticated OpenAgri request and/or upstream weather-provider configuration. AgriTwin therefore must continue to label dashboard weather observations truthfully and use a public no-secret source or local fallback when OpenAgri weather data cannot be retrieved.

## Commands Run

```bash
scripts/openagri/setup_openagri_services.sh
OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh
scripts/openagri/check_openagri_services.sh
scripts/openagri/check_openagri_services.sh --mandatory-only
docker compose -f external/openagri/runtime/docker-compose.generated.yml ps -a
docker compose -f external/openagri/runtime/docker-compose.generated.yml config
curl -fsS http://localhost/agritwin3/api/openagri/mandatory-services
```

## Remaining Requirements

- Provide real OpenAgri Weather authentication and upstream weather-provider credentials outside git if live OpenAgri weather observations are required.
- Provide a real Geodatabase / OCSM endpoint if that service is in scope for the local validation run.
- Keep generated local `.env` files untracked.
- Repair or expose the local AgriTwin MySQL user if login/user management should be exercised against the host database rather than the existing graceful unavailable state.

## 2026-05-13 Mandatory-Service Reprioritisation

The HMI, settings page, JSON API and health-check script now present Gatekeeper, Weather Data, Irrigation Management and Pest & Disease Management as the primary Proposal/D1 service group. FarmCalendar and Reporting are shown as operational extensions. Geodatabase / OCSM is shown as optional and not required for D1 when no endpoint is configured.

Verification commands run:

```bash
scripts/openagri/check_openagri_services.sh --mandatory-only
scripts/openagri/check_openagri_services.sh
scripts/check_local_stack.sh
make test
docker compose config
curl -fsS http://localhost/agritwin3/api/openagri/mandatory-services
curl -fsS http://localhost/agritwin3/api/openagri/services
```

Observed status:

- Mandatory services: Gatekeeper, Weather, Irrigation and Pest & Disease endpoints all returned HTTP 200 on their configured local health/docs paths.
- Extensions: FarmCalendar and Reporting endpoints returned HTTP 200.
- Optional services: Geodatabase / OCSM remains optional and not configured.
- Weather observations: the Weather service endpoint is reachable; live weather observations still require external provider credentials when using the OpenAgri data endpoint.

## 2026-05-13 Weather API Key Settings

AgriTwin Settings now supports controlled insertion, validation and removal of the Weather provider API key for live OpenAgri Weather observations.

- Storage: encrypted local file under ignored `storage/secrets/`.
- Access: Administrator or Technical Manager Settings permission.
- Protection: CSRF for browser POSTs; key is never rendered, returned by APIs or committed.
- Connector precedence: environment token first, Settings-stored key second.
- Safe endpoints:
  - `GET /agritwin3/api/weather/configuration/status`
  - `GET /agritwin3/api/weather/diagnostics`
  - `POST /agritwin3/api/weather/api-key`
  - `POST /agritwin3/api/weather/api-key/validate`
  - `POST /agritwin3/api/weather/api-key/remove`

Live weather observations still require a valid provider key and successful OpenAgri Weather observation response.

## 2026-05-13 Operational Users and Analytics

Operational user accounts were created/updated in the local database through `scripts/users/create_operational_users.php`. Passwords were generated at runtime, hashed with PHP `password_hash`, and not written to tracked files or documentation.

The analytics module adds:

- `/agritwin3/agritwin_dashboard/analytics.php`
- `/agritwin3/api/analytics/summary`
- `/agritwin3/api/analytics/weather`
- `/agritwin3/api/analytics/sensor-stations`
- `/agritwin3/api/analytics/irrigation`
- `/agritwin3/api/analytics/pest-disease`
- `/agritwin3/api/analytics/kpis`
- `/agritwin3/api/analytics/service-freshness`

The module uses the local operational dataset and current OpenAgri service-health records. It does not change the live-weather limitation: live OpenAgri Weather observations still require valid provider credentials.
