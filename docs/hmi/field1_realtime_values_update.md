# Field1 Current Operational Values Update

## Scope

`agritwin_dashboard/field1.php` keeps the recovered standalone Cesium implementation intact. The update replaces fixed weather, sensor, irrigation, pest/disease and task values with a central field data provider while preserving terrain loading, WMS layers, CSV trees, GLTF models, markers, legends and selection behaviour.

## Data Source Priority

The field provider is implemented in `agritwin_dashboard/includes/field_data_provider.php` and returns a structured payload with:

- `generated_at`
- `timezone`
- `location_label`
- `external_weather`
- `local_weather_station`
- `sensor_markers`
- `weather_stations`
- `irrigation_status`
- `pest_disease_status`
- `operational_insights`
- `task_queue`
- `data_source_status`

Weather priority is:

1. OpenAgri Weather Service through the existing AgriTwin weather source function when live observations are available.
2. Public weather source already available through the platform when reachable without secrets.
3. Local fallback generated for today in Europe/Madrid when external data is unavailable.

AEMET remains available through `meteo.php` when `AGRITWIN_AEMET_API_KEY` is configured outside Git. The field page handles AEMET forecast fetch failures without affecting Cesium initialisation.

## Local Fallback Method

The local fallback is time-aware for Nava de Sotrobal / Macotera / Salamanca:

- temperature follows a daily curve with lower night/morning values and higher afternoon values;
- humidity varies inversely with temperature;
- wind speed and direction vary by local hour and day;
- rainfall remains within plausible late-spring ranges;
- solar radiation follows daylight hours;
- sensor marker values are derived from the current weather payload and realistic block-level variation.

## Field Updates

Updated field content includes:

- right-side weather panel values and timestamps;
- weather source and last updated labels;
- sensor marker InfoBox values;
- weather station marker InfoBox values;
- operational insights;
- irrigation and pest/disease status;
- task queue dates and actions using today/upcoming days instead of old fixed dates.

## Cesium Preservation

The update does not alter:

- CesiumJS and widget CSS sources;
- `#cesiumContainer`;
- `Cesium.createWorldTerrain()`;
- farm bounding rectangle;
- Sentinel/Copernicus NDVI and LAI_SAVI configuration;
- NASA GIBS NDVI layer;
- `almond_trees.csv` and `pistachio.csv` loading;
- `result.gltf` pivot model;
- `solar_panel/solar.gltf` solar panel models;
- layer toggles;
- entity selection and InfoBox behaviour.

## Verification

Commands run:

- `php -l agritwin_dashboard/includes/field_data_provider.php`
- `php -l agritwin_dashboard/field1.php`
- `php scripts/i18n/audit_translations.php`
- `make test`
- `docker compose config`
- `scripts/check_local_stack.sh`
- `scripts/openagri/check_openagri_services.sh --core-only`
- authenticated curl checks for `field1.php`, dashboard, OpenAgri Services and Settings

Rendered HTML checks confirmed current weather fields, source labels, Cesium terrain code and all required asset references remain present. No old fixed 2025 task dates were found in rendered `field1.php`.

## Limitations

Live OpenAgri Weather observations require valid configured service access and provider credentials. AEMET forecast data requires `AGRITWIN_AEMET_API_KEY` configured locally outside Git. When these are not available, the page uses the local fallback described above.
