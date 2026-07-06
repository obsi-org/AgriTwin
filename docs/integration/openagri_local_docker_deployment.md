# Local OpenAgri Docker Deployment

AgriTwin provides local OpenAgri-compatible Docker services in the main `docker-compose.yml`. They are started by `scripts/start_local_stack.sh` and expose the standard local ports used by the AgriTwin web container and host-side checks.

The upstream OpenAgri helper scripts remain available under `scripts/openagri/` for installations that need to run the cloned upstream repositories:

- `setup_openagri_services.sh`: clones or updates upstream OpenAgri repositories into `external/openagri/`.
- `generate_openagri_local_env.sh`: creates untracked local `.env` files plus `external/openagri/runtime/docker-compose.generated.yml` and `config/openagri.local.env`.
- `start_openagri_services.sh`: validates upstream compose files and starts the generated local runtime when `OPENAGRI_RUN_CONTAINERS=1` is supplied.
- `check_openagri_services.sh`: checks configured OpenAgri endpoint URLs.
- `stop_openagri_services.sh`: stops any upstream compose stacks that were started through their compose files.

The default local runtime supplies stable service URLs:

| Service | Default local URL |
| --- | --- |
| Gatekeeper | `http://127.0.0.1:18091` |
| Weather | `http://127.0.0.1:18092` |
| Irrigation | `http://127.0.0.1:18093` |
| Pest and Disease | `http://127.0.0.1:18094` |
| FarmCalendar | `http://127.0.0.1:18095` |
| Reporting | `http://127.0.0.1:18096` |
| Geodatabase / OCSM | Configuration required |

Start AgriTwin plus the local OpenAgri-compatible services:

```bash
scripts/start_local_stack.sh
```

Skip local OpenAgri-compatible service containers only when another local OpenAgri runtime already provides the required ports:

```bash
AGRITWIN_START_OPENAGRI=0 scripts/start_local_stack.sh
```

Use cloned upstream OpenAgri repositories only after their ignored service `.env` files are configured outside git:

```bash
AGRITWIN_START_EXTERNAL_OPENAGRI=1 scripts/start_local_stack.sh
```

Check service availability:

```bash
scripts/openagri/check_openagri_services.sh
```

This command verifies local OpenAgri service availability through configured health or documentation endpoints. Local service availability is separate from live upstream weather observations: the Weather Service can be running locally while live external observations still require OpenAgri authentication and an upstream weather-provider key.

Stop the local runtime without deleting volumes:

```bash
scripts/openagri/stop_openagri_services.sh
```

Validate the combined AgriTwin configuration:

```bash
docker compose -f docker-compose.yml -f docker-compose.openagri.yml config
```

Do not commit generated service `.env` files, `config/openagri.local.env`, or generated runtime compose files. They contain local-only operational configuration and are ignored by git.

Troubleshooting: if one of the default local services fails, inspect it with `docker compose logs --tail=120 openagri-weather` or the relevant service name. If an upstream service container is running but the health check fails, inspect it with `docker compose -f external/openagri/runtime/docker-compose.generated.yml logs --tail=120 <service>`. Live OpenAgri Weather observations require real external credentials and provider configuration outside git even when the local Weather Service documentation endpoint is available.
