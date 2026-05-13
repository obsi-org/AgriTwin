# Field1 Cesium Regression Fix

## Root Cause

The known-working reference was supplied locally as `docs/reference/field1_working_reference.php`. The current `agritwin_dashboard/field1.php` had been simplified during HMI integration and no longer carried all of the working Cesium logic from the reference. The missing pieces were world-terrain initialisation, Copernicus/Sentinel WMS layers, NASA GIBS NDVI, CSV-driven tree loading, GLTF pivot/solar models, selection highlighting and the complete layer-control set.

The earlier standalone page also redirected unauthenticated users to a non-existent local `login.php` path. The repaired page keeps the current AgriTwin shared layout and redirects unauthenticated users through the repository-root login page.

## Files Changed

- `agritwin_dashboard/field1.php`
- `agritwin_dashboard/assets/js/field1_cesium.js`
- `agritwin_dashboard/assets/hmi.css`
- `agritwin_dashboard/lang/en.php`
- `agritwin_dashboard/lang/pt-PT.php`
- `agritwin_dashboard/lang/es-ES.php`
- `.gitignore`

The local reference file remains ignored by Git and was used only for comparison.

## Cesium Loading Strategy

The field view keeps the CesiumJS 1.106 CDN import used by the working reference. The page renders through the shared AgriTwin HMI shell, uses an explicit `#cesiumContainer` with stable height, and initialises Cesium from `assets/js/field1_cesium.js` after the DOM is ready.

The Cesium Ion token is read from `app_settings.cesium_token` and injected into the browser using JSON encoding because Cesium terrain must be requested client-side. The token is not logged or documented. If the token is missing or terrain initialisation fails, the page shows a professional configuration/terrain notice and falls back to ellipsoid terrain while still rendering farm entities.

## Reference Comparison

Features restored from the working reference:

- Cesium viewer and terrain initialisation.
- Property boundary and camera rectangle.
- Copernicus/Sentinel WMS NDVI and LAI/SAVI overlays.
- NASA GIBS `MODIS_Terra_NDVI_8Day` overlay.
- Sensor markers A, B, C, D, E, F, G, H, I, J, L and M.
- Machinery markers for Tractor 1 and Harvester 1.
- Weather Station 1 and Weather Station 2 markers.
- Almond tree loading from `almond_trees.csv`.
- Pistachio tree loading from `pistachio.csv`.
- Pivot model loading from `result.gltf`.
- Solar panel model loading from `solar_panel/solar.gltf`.
- NDVI/LAI/SAVI legend handling with a fallback when the LAI/SAVI legend image is absent.
- Entity selection highlighting.
- Visible click/tap controls for all major layers.

## Farm Objects Restored

The repaired view renders local Cesium entities for:

- Almond trees from `almond_trees.csv`, with procedural fallback if the CSV or image cannot load.
- Pistachio trees from `pistachio.csv`, with procedural fallback if the CSV or image cannot load.
- PV panel arrays from `solar_panel/solar.gltf`, with box fallback if the model cannot load.
- Sensor markers from the working reference.
- Machinery markers from the working reference.
- Weather-station markers from the working reference.
- Field boundary polygon and outline.
- Pivot model from `result.gltf`, with a simple geometry fallback if the model cannot load.

## Controls Restored

The field view includes translated controls for:

- Reset view.
- Sensors.
- Machinery.
- Weather stations.
- Almond trees.
- Pistachio trees.
- Solar panels.
- Irrigation pivot.
- Field boundaries.
- NDVI.
- LAI/SAVI.
- NASA NDVI.
- Labels.
- Field information panel.

## Asset Verification

Available assets:

- `almond_trees.csv`
- `pistachio.csv`
- `almond_tree.png`
- `pistachio.png`
- `result.gltf`
- `solar_panel/solar.gltf`
- `ndvi_legend_custom.png`
- AgriTwin, OBSIDIAN, Fotovoltaica Macotera SL and OpenAgri logos

Missing asset:

- `lai_savi_legend_custom.png`

The LAI/SAVI overlay still works when Copernicus is configured; the legend panel shows an asset-unavailable message if the legend image cannot load.

## Verification

Planned verification commands:

- `php -l agritwin_dashboard/field1.php`
- `php -l agritwin_dashboard/includes/hmi_layout.php`
- `php scripts/i18n/audit_translations.php`
- `make test`
- `docker compose config`
- `docker compose -f docker-compose.openagri.yml config`
- `scripts/check_local_stack.sh`
- `scripts/openagri/check_openagri_services.sh --core-only`
- Curl checks for `field1.php`, dashboard, analytics and OpenAgri Services.

Verification completed:

- PHP syntax checks passed for modified PHP files.
- `node --check agritwin_dashboard/assets/js/field1_cesium.js` passed.
- `php scripts/i18n/audit_translations.php` passed with 452 keys per locale and no hard failures.
- `make test` passed.
- `docker compose config` passed for the main AgriTwin compose file.
- `docker compose -f docker-compose.openagri.yml config` still reports the existing OpenAgri compose limitation: service `app` has neither an image nor a build context. This fix did not modify that orchestration file.
- `scripts/check_local_stack.sh` passed.
- `scripts/openagri/check_openagri_services.sh --core-only` passed for Gatekeeper, Weather, Irrigation and Pest & Disease.
- Authenticated curl checks returned HTTP 200 for `field1.php`, dashboard, analytics and OpenAgri Services.
- Authenticated multilingual curl checks returned HTTP 200 for `field1.php?lang=en`, `field1.php?lang=pt-PT` and `field1.php?lang=es-ES`.
- Rendered `field1.php` contains the Cesium JS/CSS references, `#cesiumContainer`, `assets/js/field1_cesium.js`, farm bounds, CSV/model/legend asset references and the field configuration payload.

## Remaining Limitations

The Cesium viewer still loads CesiumJS from the existing CDN source. A future hardening step could vendor Cesium locally if offline 3D rendering is required. Full canvas-level confirmation should still be performed in a browser because the automated checks validate rendered markup, scripts and static configuration rather than visual pixels.
