# Docker Local Deployment

## Active Repository

The active AgriTwin repository is:

```text
/media/pedrobranco/AikoHD/Documents/Dev/agritwin
```

The application no longer requires the source tree to live under `/var/www/html`. The Docker web container mounts this repository and serves it under the `/agritwin3/` base path.

## Services

Docker Compose defines:

- `agritwin-web`: PHP 8.3 with Apache, GD, PDO MySQL, mysqli, intl, mbstring, curl, zip, XML and opcache.
- `agritwin-db`: MySQL 8 with a persistent named Docker volume.
- `openagri-fixture`: optional fixture service for connector tests, only started with the `fixture` profile.

Generated reports and runtime files are written under ignored `var/` paths in the repository. Database state is stored in the `agritwin3_agritwin_mysql_data` Docker volume.

## Start

```bash
scripts/start_local_stack.sh
```

The script creates or updates ignored local runtime files:

- `.env`
- `config/database.local.env`

It generates local database passwords when needed and does not print them.

By default the script starts only the AgriTwin web and database containers. To also ask the local OpenAgri helper scripts to start upstream services from the ignored `external/openagri/` checkout, use:

```bash
AGRITWIN_START_OPENAGRI=1 scripts/start_local_stack.sh
```

## URL

Open:

```text
http://localhost:8083/agritwin3/
```

Important routes:

```text
http://localhost:8083/agritwin3/agritwin_dashboard/index.php
http://localhost:8083/agritwin3/agritwin_dashboard/field1.php
http://localhost:8083/agritwin3/agritwin_dashboard/reports.php
http://localhost:8083/agritwin3/agritwin_dashboard/api_swagger.php
http://localhost:8083/agritwin3/api/openapi.json
```

## Stop

```bash
scripts/stop_local_stack.sh
```

This stops containers without deleting volumes.

## Check

```bash
scripts/check_local_stack.sh
```

The check validates Docker services, database connectivity from the web container, rendered pages, Reports/PDF generation, and OpenAgri core service health when local services are available.

## OpenAgri Connectivity

Inside Docker, `localhost` means the web container. The Compose configuration uses `host.docker.internal` with `host-gateway` so the app can reach host services:

- Gatekeeper: `http://host.docker.internal:18091`
- Weather: `http://host.docker.internal:18092`
- Irrigation: `http://host.docker.internal:18093`
- Pest and Disease: `http://host.docker.internal:18094`
- FarmCalendar: `http://host.docker.internal:18095`
- Reporting: `http://host.docker.internal:18096`

Real OpenAgri credentials remain in ignored local environment files or service-specific configuration outside Git.

## Cesium and Copernicus

Cesium and Copernicus values continue to resolve through `app_settings` or ignored environment variables. If the Docker database is fresh, import the existing database or set the values locally without committing them.

## Optional Host Apache Reverse Proxy

The Docker URL is the supported local URL. If a host Apache reverse proxy is desired, configure it manually outside this repository to forward `/agritwin3/` to `http://localhost:8083/agritwin3/`. Do not enable host Apache configuration as part of the repository setup.
