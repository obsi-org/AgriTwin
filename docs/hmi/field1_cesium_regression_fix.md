# Field1 Cesium Regression Fix

## Root Cause

`agritwin_dashboard/field1.php` still used an older standalone full-screen Cesium implementation. It redirected unauthenticated users to a non-existent local `login.php` path and depended on fragile Cesium terrain, external marker icons and inline object loading. When the surrounding HMI was modernised, this page did not inherit the shared layout, sidebar branding, responsive rules or current sensor-station data.

## Files Changed

- `agritwin_dashboard/field1.php`
- `agritwin_dashboard/assets/js/field1_cesium.js`
- `agritwin_dashboard/assets/hmi.css`
- `agritwin_dashboard/includes/hmi_layout.php`
- `agritwin_dashboard/digital_twin.php`
- `agritwin_dashboard/lang/en.php`
- `agritwin_dashboard/lang/pt-PT.php`
- `agritwin_dashboard/lang/es-ES.php`

## Cesium Loading Strategy

The field view keeps the existing CesiumJS CDN import used by the original page. The page now renders through the shared AgriTwin HMI shell, uses an explicit `#cesiumContainer` with stable height, and initialises Cesium from `assets/js/field1_cesium.js` after the DOM is ready.

To avoid exposing private tokens in rendered HTML or JavaScript, the repaired page does not send the Cesium Ion token to the browser. The viewer uses Cesium ellipsoid terrain and renders the local farm entities without requiring private credentials. The page records whether a token is configured only as server-side state and does not display the value.

## Farm Objects Restored

The repaired view renders local Cesium entities for:

- Almond, pistachio and maize tree blocks.
- PV panel arrays.
- Sensor stations from the central AgriTwin station provider.
- Sensor stations are positioned inside the safe field-view geometry for this Cesium scene while preserving their central AgriTwin station identifiers and readings.
- Irrigation lines and pump house.
- Weather mast.
- Field boundary polygon and outline.
- Optional pivot model from `result.gltf` when the asset can be loaded.

## Controls Restored

The field view includes translated controls for:

- Reset view.
- Trees.
- PV panels.
- Sensor stations.
- Irrigation assets.
- Field boundaries.
- Weather layer.
- Labels.

## Verification

Planned verification commands:

- `php -l agritwin_dashboard/field1.php`
- `php -l agritwin_dashboard/includes/hmi_layout.php`
- `php scripts/i18n/audit_translations.php`
- `make test`
- `docker compose config`
- `scripts/check_local_stack.sh`
- `scripts/openagri/check_openagri_services.sh --core-only`
- Curl checks for `field1.php`, dashboard, analytics and OpenAgri Services.

Verification completed:

- PHP syntax checks passed for modified PHP files.
- `node --check agritwin_dashboard/assets/js/field1_cesium.js` passed.
- `php scripts/i18n/audit_translations.php` passed with 427 keys per locale and no hard failures.
- `make test` passed.
- `docker compose config` passed for the main AgriTwin compose file.
- `scripts/check_local_stack.sh` passed.
- `scripts/openagri/check_openagri_services.sh --core-only` passed for Gatekeeper, Weather, Irrigation and Pest & Disease.
- Authenticated curl checks returned HTTP 200 for `field1.php`, dashboard, analytics and OpenAgri Services.
- Authenticated multilingual curl checks returned HTTP 200 for `field1.php?lang=en`, `field1.php?lang=pt-PT` and `field1.php?lang=es-ES`.
- Rendered `field1.php` contains the Cesium JS/CSS references, `#cesiumContainer`, `assets/js/field1_cesium.js`, and the field configuration payload.
- `docker compose -f docker-compose.openagri.yml config` still reports the existing OpenAgri compose limitation: service `app` has neither an image nor a build context. This fix did not modify that orchestration file.

## Remaining Limitations

The Cesium viewer still loads CesiumJS from the existing CDN source. A future hardening step could vendor Cesium locally if offline 3D rendering is required.
