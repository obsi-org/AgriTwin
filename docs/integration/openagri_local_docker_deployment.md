# Local OpenAgri Docker Deployment

AgriTwin provides non-interactive helper scripts under `scripts/openagri/`:

- `setup_openagri_services.sh`: clones or updates upstream OpenAgri repositories into `external/openagri/`.
- `start_openagri_services.sh`: validates upstream compose files and starts them only when `OPENAGRI_RUN_CONTAINERS=1` is explicitly supplied.
- `check_openagri_services.sh`: checks configured OpenAgri endpoint URLs.
- `stop_openagri_services.sh`: stops any upstream compose stacks that were started through their compose files.

The AgriTwin compose overlay is `docker-compose.openagri.yml`. It supplies stable local service URLs to the AgriTwin app:

| Service | Default local URL |
| --- | --- |
| Gatekeeper | `http://host.docker.internal:18091` |
| Weather | `http://host.docker.internal:18092` |
| Irrigation | `http://host.docker.internal:18093` |
| Pest and Disease | `http://host.docker.internal:18094` |
| FarmCalendar | `http://host.docker.internal:18095` |
| Reporting | `http://host.docker.internal:18096` |
| Geodatabase / OCSM | `http://host.docker.internal:18097` |

Validate the combined AgriTwin configuration:

```bash
docker compose -f docker-compose.yml -f docker-compose.openagri.yml config
```

Start AgriTwin with the local OpenAgri endpoint overlay:

```bash
docker compose -f docker-compose.yml -f docker-compose.openagri.yml --profile mock up -d
```

To attempt upstream OpenAgri containers, first create each service's required local `.env` files from its own documentation, then run:

```bash
OPENAGRI_RUN_CONTAINERS=1 scripts/openagri/start_openagri_services.sh
```

Do not commit those service `.env` files or any credentials.
