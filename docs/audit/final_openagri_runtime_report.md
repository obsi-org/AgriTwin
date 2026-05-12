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

## Runtime Result

| Service | Local URL | Result |
| --- | --- | --- |
| Gatekeeper | `http://127.0.0.1:18091` | Available |
| Weather Service | `http://127.0.0.1:18092` | Service available; weather data endpoint requires authentication/provider configuration |
| Irrigation Management | `http://127.0.0.1:18093` | Available |
| Pest and Disease Management | `http://127.0.0.1:18094` | Available |
| FarmCalendar | `http://127.0.0.1:18095` | Available through `/api/v1/` |
| Reporting Service | `http://127.0.0.1:18096` | Available |
| Geodatabase / OCSM | Not configured | Configuration required |

## Weather Source

The local OpenAgri Weather Service process and documentation endpoint are available. The operational weather data endpoint requires an authenticated OpenAgri request and/or upstream weather-provider configuration. AgriTwin therefore must continue to label dashboard weather observations truthfully and use a public no-secret source or local fallback when OpenAgri weather data cannot be retrieved.

## Commands Run

```bash
scripts/openagri/setup_openagri_services.sh
OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh
scripts/openagri/check_openagri_services.sh
docker compose -f external/openagri/runtime/docker-compose.generated.yml ps -a
docker compose -f external/openagri/runtime/docker-compose.generated.yml config
```

## Remaining Requirements

- Provide real OpenAgri Weather authentication and upstream weather-provider credentials outside git if live OpenAgri weather observations are required.
- Provide a real Geodatabase / OCSM endpoint if that service is in scope for the local validation run.
- Keep generated local `.env` files untracked.
- Repair or expose the local AgriTwin MySQL user if login/user management should be exercised against the host database rather than the existing graceful unavailable state.
