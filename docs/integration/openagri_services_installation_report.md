# OpenAgri Services Installation Report

Date: 2026-05-12

## Runtime Automation Update

`scripts/openagri/generate_openagri_local_env.sh` now creates the safe local runtime configuration needed to start the cloned OpenAgri services without committing credentials. Generated files are intentionally untracked:

- `external/openagri/bootstrap-deployment/.env`
- `external/openagri/gatekeeper/.env`
- `external/openagri/gatekeeper/mysql_docker/.env`
- `external/openagri/weather-service/.env`
- `external/openagri/irrigation-management/.env`
- `external/openagri/pest-disease-management/.env`
- `external/openagri/farmcalendar/.env`
- `external/openagri/reporting-service/.env`
- `external/openagri/runtime/docker-compose.generated.yml`
- `config/openagri.local.env`

The generated runtime compose starts stable local service endpoints on ports `18091` through `18096` and does not remove Docker volumes.

Latest local health result:

| Service | Local URL | Health/docs URL | Result |
| --- | --- | --- | --- |
| Gatekeeper | `http://127.0.0.1:18091` | `http://127.0.0.1:18091/` | Available, HTTP 200 |
| Weather Service | `http://127.0.0.1:18092` | `http://127.0.0.1:18092/docs` | Available, HTTP 200; weather data endpoint still requires authentication/provider configuration |
| Irrigation Management | `http://127.0.0.1:18093` | `http://127.0.0.1:18093/docs` | Available, HTTP 200 after applying a local ignored import patch to the cloned upstream checkout |
| Pest and Disease Management | `http://127.0.0.1:18094` | `http://127.0.0.1:18094/docs` | Available, HTTP 200 |
| FarmCalendar | `http://127.0.0.1:18095` | `http://127.0.0.1:18095/api/v1/` | Available, HTTP 200 |
| Reporting Service | `http://127.0.0.1:18096` | `http://127.0.0.1:18096/docs` | Available, HTTP 200 |
| Geodatabase / OCSM | Not configured | Not configured | Configuration required |

Weather note: the OpenAgri Weather application is running locally, but `/api/data/weather/` returns authentication required without a valid token and upstream weather-provider credentials. AgriTwin therefore must not present OpenAgri weather observations as live data until those credentials are supplied; it can still show the service process as available.

Upstream OpenAgri repositories were cloned into `external/openagri/` by `scripts/openagri/setup_openagri_services.sh`. That folder is intentionally ignored by git so AgriTwin remains self-contained without vendoring third-party service source.

| Service | Repository | Commit | Docker assets found | Start status |
| --- | --- | --- | --- | --- |
| Bootstrap Deployment | `https://github.com/openagri-eu/OpenAgri-Bootstrap-Deployment.git` | `34fc4a6` | `docker-compose.yml` | Compose file was empty in this checkout, so no stack was started. |
| Gatekeeper | `https://github.com/agstack/OpenAgri-GateKeeper.git` | `49f4bc3` | `Dockerfile`, `docker-compose.yml`, `mysql_docker/docker-compose.yml` | Compose validation requires local `.env` database values. No containers were started. |
| Weather Service | `https://github.com/agstack/OpenAgri-WeatherService.git` | `340aace` | `Dockerfile.x86_64`, `Dockerfile.arm64`, architecture-specific compose files | Compose validation requires registry, port, MongoDB, Gatekeeper and weather-provider settings. No containers were started. |
| Irrigation Management | `https://github.com/agstack/OpenAgri-IrrigationManagement.git` | `01e9eb1` | `Dockerfile`, `.env.example` | No compose file found; Dockerfile-based startup requires service-specific environment. |
| Pest and Disease Management | `https://github.com/openagri-eu/pest-and-disease-management.git` | `3aa67a9` | `Dockerfile`, `.env.example` | No compose file found; Dockerfile-based startup requires service-specific environment. |
| FarmCalendar | `https://github.com/openagri-eu/farmcalendar.git` | `bfebfea` | `Dockerfile`, `docker-compose.yml` | Compose validation requires PostgreSQL, JWT and Django settings. No containers were started. |
| Reporting Service | `https://github.com/openagri-eu/reporting-service.git` | `2ef1b0d` | `Dockerfile`, `.env.example` | No compose file found; Dockerfile-based startup requires service-specific environment. |

## Commands Run

```bash
scripts/openagri/setup_openagri_services.sh
scripts/openagri/start_openagri_services.sh
scripts/openagri/check_openagri_services.sh
```

## Result

The repositories were downloaded and analysed successfully. The upstream service stacks were not started automatically because their official compose files require project-specific environment variables and credentials that are not safe to invent or commit. AgriTwin now exposes truthful endpoint status: if local services are not running or not configured, the UI reports configuration or connection requirements rather than claiming live availability.
