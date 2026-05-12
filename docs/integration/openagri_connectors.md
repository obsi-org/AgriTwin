# OpenAgri Connectors

This document describes the AgriTwin OpenAgri connector layer implemented under `src/openagri_connectors/`.

The implementation was based on inspection of:

- `https://github.com/openagri-eu/OpenAgri-GateKeeper`
- `https://github.com/openagri-eu/OpenAgri-WeatherService`
- `https://github.com/openagri-eu/OpenAgri-IrrigationManagement`
- `https://github.com/openagri-eu/OpenAgri-PestAndDiseaseManagement`
- `https://github.com/openagri-eu/OpenAgri-FarmCalendar`
- `https://github.com/openagri-eu/OpenAgri-ReportingService`
- `https://github.com/openagri-eu/OpenAgri-UserDashboard`
- `https://github.com/openagri-eu/OCSM`

No third-party OpenAgri service code is vendored into this repository. The local code is an adapter layer around documented service endpoints and JSON/JSON-LD response shapes.

## Module Layout

```text
src/openagri_connectors/
├── Adapters/Normalizers.php
├── Clients/
│   ├── BaseOpenAgriClient.php
│   ├── FarmCalendarClient.php
│   ├── GatekeeperClient.php
│   ├── IrrigationServiceClient.php
│   ├── PestDiseaseServiceClient.php
│   ├── ReportingServiceClient.php
│   └── WeatherServiceClient.php
├── Config/OpenAgriConfig.php
├── Edge/EdgeModeCoordinator.php
├── Http/HttpClient.php
├── Support/
└── OpenAgriConnectorFactory.php
```

## Configuration

Use `.env.example` as the placeholder list. Real credentials must be supplied by the runtime environment and must not be committed.

Core variables:

- `AGRITWIN_OPENAGRI_MODE`: `mock` or `real`.
- `AGRITWIN_OPENAGRI_CACHE_ENABLED`: enables last-known-good fallback.
- `AGRITWIN_OPENAGRI_OFFLINE_MODE`: forces cache-only mode.
- `AGRITWIN_OPENAGRI_TIMEOUT_SECONDS`: HTTP timeout.
- `AGRITWIN_OPENAGRI_RETRIES`: retry count.
- `AGRITWIN_OPENAGRI_BACKOFF_MS`: exponential backoff base.
- `AGRITWIN_OPENAGRI_CACHE_DIR`: response cache directory.
- `AGRITWIN_OPENAGRI_QUEUE_DIR`: edge queue directory.
- `AGRITWIN_OPENAGRI_RESTORE_WINDOW_SECONDS`: default `259200` for 72 hours.

Service variables:

- `AGRITWIN_OPENAGRI_GATEKEEPER_URL`
- `AGRITWIN_OPENAGRI_GATEKEEPER_USERNAME`
- `AGRITWIN_OPENAGRI_GATEKEEPER_PASSWORD`
- `AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN`
- `AGRITWIN_OPENAGRI_WEATHER_URL`
- `AGRITWIN_OPENAGRI_WEATHER_TOKEN`
- `AGRITWIN_OPENAGRI_IRRIGATION_URL`
- `AGRITWIN_OPENAGRI_IRRIGATION_TOKEN`
- `AGRITWIN_OPENAGRI_PEST_DISEASE_URL`
- `AGRITWIN_OPENAGRI_PEST_DISEASE_TOKEN`
- `AGRITWIN_OPENAGRI_FARMCALENDAR_URL`
- `AGRITWIN_OPENAGRI_FARMCALENDAR_TOKEN`
- `AGRITWIN_OPENAGRI_REPORTING_URL`
- `AGRITWIN_OPENAGRI_REPORTING_TOKEN`

Default TTLs:

- Weather: 6 hours.
- Irrigation: 24 hours.
- Pest/Disease: 24 hours.
- FarmCalendar: 15 minutes.
- Reporting: on demand.

## API Calls

GateKeeper:

- `GatekeeperClient::login()`: `POST /api/login/`
- `GatekeeperClient::refresh()`: `POST /api/token/refresh/`
- `GatekeeperClient::me()`: `GET /api/me/`
- `GatekeeperClient::serviceDirectory()`: `GET /api/service_directory/`

Weather:

- `WeatherServiceClient::forecast($lat, $lon)`: `GET /api/data/forecast5`
- `WeatherServiceClient::current($lat, $lon)`: `GET /api/data/weather`

Irrigation:

- `IrrigationServiceClient::etoCalculations($locationId, $fromDate, $toDate)`: `GET /api/v1/eto/get-calculations/{location_id}/from/{from}/to/{to}/?formatting=JSON`
- `IrrigationServiceClient::createParcelLocation($wktPolygon)`: `POST /api/v1/location/parcel-wkt/`

Pest and Disease:

- `PestDiseaseServiceClient::fuzzyRiskForecast(...)`: `POST /api/v1/fuzzy-risk/forecast/`
- `PestDiseaseServiceClient::gdd(...)`: `GET /api/v1/tool/calculate-gdd/parcel/{parcel_id}/model/{model_ids}/verbose/{from}/from/{to}/to/`

FarmCalendar:

- `FarmCalendarClient::listOperations($endpoint, $query)`: `GET /api/v1/{endpoint}/`
- `FarmCalendarClient::createOperation($endpoint, $jsonLdPayload)`: `POST /api/v1/{endpoint}/`
- Convenience methods exist for parcels and agricultural machines.

Reporting:

- `ReportingServiceClient::requestReport($type, $jsonLdPayload)`: `POST /api/v1/openagri-report/{type}-report/`
- `ReportingServiceClient::download($reportId)`: `GET /api/v1/openagri-report/{report_id}/`

## Data Normalisation

Normalizers convert external service responses to AgriTwin internal record types:

- Weather responses become `weather_forecast` records with temperature, rainfall, wind, GDD, and ET0 where available.
- Irrigation ETo and advisory payloads become `irrigation_advisory` records with ET0 series, Kc, water balance, and suggested schedule fields.
- Pest/Disease responses become `pest_disease_risk` records with crop, growth stage, risk index, confidence, and thresholds where available.
- FarmCalendar JSON-LD responses become `farm_calendar_sync` records with operation, observation, parcel, asset, and timestamp fields.
- Reporting responses become `reporting_request` records with report ID, status, report type, and download metadata.

Each normalized record includes source/cache metadata and confidence mode.

## Caching and Fallback

`FileCache` stores the last successful response per service request in `AGRITWIN_OPENAGRI_CACHE_DIR`.

Fallback behavior:

- Live request success writes cache.
- Timeout, HTTP 5xx/429, invalid schema, missing base URL, or offline mode tries warm cache first.
- If warm cache is expired but any last-known-good response exists, the connector returns stale cache and sets confidence mode to `reduced`.
- If no cache exists, the response is `ok=false` with a structured error.

The restore/backfill helper lists cache keys modified within `AGRITWIN_OPENAGRI_RESTORE_WINDOW_SECONDS`, defaulting to 72 hours. This gives edge or cloud sync code a bounded window for catch-up after outage.

## Edge Mode

`EdgeModeCoordinator` wraps the file-backed `SyncQueue`:

- Sensor measurements are queued with `sensor` priority.
- OpenAgri refresh jobs are queued with `openagri` priority.
- Backlog events are queued with `backlog` priority.

Pending work is sorted as:

```text
sensor -> openagri -> backlog
```

This follows the AgriTwin D1 edge-mode behavior: local sensor measurements first, then OpenAgri refresh, then backlog catch-up.

## Structured Logging

`StructuredLogger` emits JSON records to STDERR. Keys containing token, secret, password, authorization, or API key are redacted.

## Local Mock Mode

Run the mock service with either:

```bash
make test-openagri
```

or:

```bash
docker compose --profile mock up openagri-mock
```

The mock service serves fixture responses from `tests/fixtures/openagri/` on `http://127.0.0.1:18080`.

## Real Mode

Set `AGRITWIN_OPENAGRI_MODE=real` and configure the service base URLs and credentials in the runtime environment. Use:

```bash
docker compose --profile real up openagri-real-env
```

This profile only validates that runtime variables are present; it does not start the upstream OpenAgri services.

## Tests

Run:

```bash
make test-openagri
make test
```

The mocked tests cover:

- Successful service calls and normalization.
- Timeout fallback to cache.
- Invalid schema fallback to cache.
- Stale cache and reduced confidence.
- Offline mode.
- Missing GateKeeper credentials.
- Restore/backfill cache key discovery.
- Edge queue priority.

## Known Limitations

- Service APIs are still evolving; endpoint names and payload fields may differ between OpenAgri branches.
- GateKeeper proxy-based service calls are not yet automatically wired into downstream clients.
- ReportingService file-upload behavior is represented by JSON payload submission in this baseline; multipart upload can be added when wired to a live service.
- Schema validation is intentionally lightweight and should be replaced with OpenAPI/JSON Schema validation as stable specs are published.
- The current PHP prototype has not yet migrated its dashboard pages to use these connectors.

