# OpenAgri Endpoint Configuration

## URL Types

- Service endpoint: base URL used for live API calls.
- Health endpoint: service-specific health URL or path.
- API documentation: Swagger/OpenAPI documentation URL.
- Source repository: GitHub/source-code reference only; it is not treated as a live API endpoint.

## Configuration

Use environment variables such as `OPENAGRI_WEATHER_URL`, `OPENAGRI_WEATHER_HEALTH_URL` and `OPENAGRI_WEATHER_DOCS_URL`. Credentials such as `OPENAGRI_GATEKEEPER_CLIENT_SECRET` must remain outside git.

For local development, `scripts/openagri/generate_openagri_local_env.sh` writes `config/openagri.local.env` and appends the same OpenAgri URL block to the ignored local `.env` file. A safe committed template is available at `config/openagri.local.env.example`.

Discovered local URLs:

| Service | Base URL | Health URL/path | Docs URL |
| --- | --- | --- | --- |
| Gatekeeper | `http://127.0.0.1:18091` | `/` | `http://127.0.0.1:18091/` |
| Weather Service | `http://127.0.0.1:18092` | `/docs` | `http://127.0.0.1:18092/docs` |
| Irrigation Management | `http://127.0.0.1:18093` | `/docs` | `http://127.0.0.1:18093/docs` |
| Pest and Disease Management | `http://127.0.0.1:18094` | `/docs` | `http://127.0.0.1:18094/docs` |
| FarmCalendar | `http://127.0.0.1:18095` | `/api/v1/` | `http://127.0.0.1:18095/api/v1/` |
| Reporting Service | `http://127.0.0.1:18096` | `/docs` | `http://127.0.0.1:18096/docs` |
| Geodatabase / OCSM | Configuration required | Configuration required | Configuration required |

The Weather Service data URL is configured separately as `OPENAGRI_WEATHER_DATA_URL=/api/data/weather/?lat=40.9492&lon=-5.6958`. That endpoint can require authentication and upstream provider credentials; do not treat documentation availability as proof of live weather data.

Local upstream checkouts are created under ignored path `external/openagri/` by:

```bash
scripts/openagri/setup_openagri_services.sh
```

The local AgriTwin compose overlay `docker-compose.openagri.yml` maps AgriTwin to stable local OpenAgri URLs such as `http://host.docker.internal:18092` for the Weather Service. Use the overlay with:

```bash
docker compose -f docker-compose.yml -f docker-compose.openagri.yml config
```

`scripts/openagri/start_openagri_services.sh` validates upstream compose files by default. It starts the generated local runtime when `OPENAGRI_RUN_CONTAINERS=1` is set. External weather-provider credentials still must be supplied outside git when live upstream weather observations are required.

## Connection States

AgriTwin reports professional states: Live healthy, Local fallback active, Configuration required, Endpoint unreachable, Authentication failed and Connection unavailable. A service is marked live only after a real HTTP request succeeds.

## Verification

Use the OpenAgri Services page or:

```bash
curl -fsS http://localhost/agritwin3/api/openagri/services
curl -fsS -X POST 'http://localhost/agritwin3/api/openagri/services/test?service=weather'
```
