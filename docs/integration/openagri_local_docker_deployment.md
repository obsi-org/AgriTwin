# Local OpenAgri Docker Deployment

AgriTwin provides non-interactive helper scripts under `scripts/openagri/`:

- `setup_openagri_services.sh`: clones or updates upstream OpenAgri repositories into `external/openagri/`.
- `generate_openagri_local_env.sh`: creates untracked local `.env` files plus `external/openagri/runtime/docker-compose.generated.yml` and `config/openagri.local.env`.
- `start_openagri_services.sh`: validates upstream compose files and starts the generated local runtime when `OPENAGRI_RUN_CONTAINERS=1` is supplied.
- `check_openagri_services.sh`: checks configured OpenAgri endpoint URLs.
- `stop_openagri_services.sh`: stops any upstream compose stacks that were started through their compose files.

The AgriTwin compose overlay is `docker-compose.openagri.yml`. It supplies stable local service URLs to the AgriTwin app:

| Service | Default local URL |
| --- | --- |
| Gatekeeper | `http://127.0.0.1:18091` |
| Weather | `http://127.0.0.1:18092` |
| Irrigation | `http://127.0.0.1:18093` |
| Pest and Disease | `http://127.0.0.1:18094` |
| FarmCalendar | `http://127.0.0.1:18095` |
| Reporting | `http://127.0.0.1:18096` |
| Geodatabase / OCSM | Configuration required |

Generate local service configuration:

```bash
scripts/openagri/setup_openagri_services.sh
scripts/openagri/generate_openagri_local_env.sh
```

Start the local OpenAgri runtime:

```bash
OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh
```

Check service availability:

```bash
scripts/openagri/check_openagri_services.sh
```

Stop the local runtime without deleting volumes:

```bash
scripts/openagri/stop_openagri_services.sh
```

Validate the combined AgriTwin configuration:

```bash
docker compose -f docker-compose.yml -f docker-compose.openagri.yml config
```

Do not commit generated service `.env` files, `config/openagri.local.env`, or generated runtime compose files. They contain local-only development credentials and are ignored by git.

Troubleshooting: if a service container is running but the health check fails, inspect it with `docker compose -f external/openagri/runtime/docker-compose.generated.yml logs --tail=120 <service>`. Weather data endpoints may require both OpenAgri authentication and an upstream weather-provider key even when the local Weather Service documentation endpoint is available.
