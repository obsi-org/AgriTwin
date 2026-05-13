# Field1 Final Text Cleanup

## Scope

The current `agritwin_dashboard/field1.php` was treated as the working Cesium baseline after manual recovery. The cleanup intentionally avoided refactoring the Cesium viewer, terrain provider, WMS layers, terrain sampling, CSV loading, GLTF model loading, keyboard toggles, entity selection and InfoBox behaviour.

## Wording Replacements

- `FOTOMACO`, `Fotomaco` and visible shorthand organisation labels were replaced with `Fotovoltaica Macotera SL`.
- `CERES Dashboard` and old dashboard wording were replaced with `AgriTwin Dashboard`.
- `GAIA` navigation wording was replaced with `AgriTwin` terminology.
- Field headings now use final platform wording: `AgriTwin Field Digital Twin`, `Field Monitoring`, `Operational Insights`, `Task Scheduling` and `Back to AgriTwin Dashboard`.
- Logo alt text now names `Fotovoltaica Macotera SL`, `AgriTwin`, `OBSIDIAN` and `OpenAgri` professionally.

The technical filename `fotomaco.png` remains unchanged to avoid breaking the working page asset path. It is not used as visible display text.

## Cesium Preservation

The following working Cesium elements were preserved in `field1.php`:

- CesiumJS 1.106 CDN script and widgets CSS.
- `Cesium.Ion.defaultAccessToken` assignment from the database value using JSON encoding.
- `Cesium.createWorldTerrain()` terrain provider initialisation.
- `#cesiumContainer` ID and full-viewport layout.
- Farm bounding rectangle and camera flight destination.
- Sentinel/Copernicus NDVI and LAI_SAVI WMS configuration.
- NASA GIBS `MODIS_Terra_NDVI_8Day` layer.
- Almond and pistachio CSV loading.
- `result.gltf` pivot model.
- `solar_panel/solar.gltf` solar panel models.
- NDVI and LAI/SAVI legend image paths.
- Entity selection and InfoBox behaviour.

## Multilingual Notes

A small page-local translation map was added for visible field page headings, buttons, panel labels and forecast messages in English, Portuguese from Portugal and Spanish from Spain. A wider platform-layout integration was deliberately not attempted because the recovered standalone page currently owns the working Cesium layout.

## Verification

Commands run:

- `php -l agritwin_dashboard/field1.php`
- `php scripts/i18n/audit_translations.php`
- `make test`
- `docker compose config`
- `scripts/check_local_stack.sh`
- `scripts/openagri/check_openagri_services.sh --core-only`
- Authenticated curl checks for `field1.php` in `en`, `pt-PT` and `es-ES`.
- Authenticated curl checks for dashboard, analytics and OpenAgri Services.

Rendered HTML verification confirmed the Cesium script/CSS references, `#cesiumContainer`, `Cesium.createWorldTerrain()`, farm bounds and all required asset references remain present. Forbidden wording and plaintext credential checks passed.

## Manual Browser Check

A final browser check is still recommended to visually confirm terrain, trees, panels, sensors, pivot and overlays because command-line checks verify markup and script presence, not rendered Cesium pixels.
