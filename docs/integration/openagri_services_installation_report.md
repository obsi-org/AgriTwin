# OpenAgri Services Installation Report

Date: 2026-05-12

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
