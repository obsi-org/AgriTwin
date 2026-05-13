# Field View Current Values and Translation

## Data Source Priority

`field1.php` now consumes the central field data provider at `agritwin_dashboard/includes/field_data_provider.php`. The provider prepares a single structured payload for the Cesium field view with current Europe/Madrid timestamps, local weather, sensor markers, weather stations, irrigation state, pest and disease state, operational insights and task scheduling.

Weather values follow the existing AgriTwin priority:

1. OpenAgri Weather Service when live observations are configured and return usable values.
2. Public weather source already available through the platform weather source logic.
3. Local operational fallback for Nava de Sotrobal / Macotera when live values are unavailable.

AEMET remains available through the field forecast panel via `meteo.php` when the local AEMET key is configured outside Git. Forecast fetch failures do not affect Cesium terrain, Copernicus layers or field asset loading.

## Fallback Generation

The local fallback is generated for the current local date and time in `Europe/Madrid`. It keeps values plausible for Macotera / Salamanca:

- temperature follows a daily curve;
- humidity varies inversely with temperature;
- wind remains within operational late-spring ranges;
- rainfall stays low unless humidity indicates light precipitation;
- solar radiation follows daylight hours;
- soil moisture varies by sensor block and remains in realistic VWC ranges.

## Translation Implementation

The field page keeps its standalone Cesium structure but now includes a compact translation map for:

- English (`en`);
- Portuguese from Portugal (`pt-PT`);
- Spanish from Spain (`es-ES`).

PHP-rendered panels, layer controls, button labels, forecast messages and JavaScript-generated InfoBox descriptions all use translated labels. Product, organisation and technical names such as AgriTwin, OpenAgri, Fotovoltaica Macotera SL, Cesium, Copernicus, NDVI and LAI/SAVI remain unchanged.

## Preserved Field Functionality

The update does not change:

- CesiumJS or widget CSS loading;
- `Cesium.createWorldTerrain()`;
- the initial field camera centre;
- Copernicus NDVI and LAI/SAVI WMS configuration;
- NASA GIBS NDVI;
- property boundary;
- almond and pistachio CSV loading;
- pivot model;
- solar panel models;
- entity selection and InfoBox behaviour;
- layer toggles and legends.

## Verification

Commands:

- `php -l agritwin_dashboard/field1.php`
- `php -l agritwin_dashboard/includes/field_data_provider.php`
- `php scripts/i18n/audit_translations.php`
- `make test`
- `docker compose config`
- `scripts/check_local_stack.sh`
- `scripts/openagri/check_openagri_services.sh --core-only`
- authenticated curl checks for `field1.php?lang=en`, `field1.php?lang=pt-PT` and `field1.php?lang=es-ES`

Manual browser verification remains useful for confirming the Cesium canvas and remote Copernicus raster layers visually because headless browser tooling is not installed locally.

## Limitations

Live OpenAgri Weather observations and AEMET forecasts still require valid external credentials configured outside Git. When those credentials are unavailable, the field view uses the local operational fallback and labels the source truthfully.
