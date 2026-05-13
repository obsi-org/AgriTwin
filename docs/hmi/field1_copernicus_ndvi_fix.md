# Field View Copernicus NDVI Fix

## Root Cause

The AgriTwin3 field view had been restored from an older working page and only looked up `app_settings.copernicus_id`. The working AgriTwin2 deployment was inspected read-only and its working Copernicus configuration was found as an `app_settings` value named `copernicus_id`, with the same WMS URL construction used by the reference page. AgriTwin3 now contains the same local runtime value in its database, but no credential or instance identifier is committed to Git or documented here.

The field view also had no fallback lookup for equivalent Sentinel Hub setting names or a full configured WMS URL. When configuration or imagery requests failed, the page provided no safe status and the index controls could leave the operator with no visible change. The LAI/SAVI legend asset may also be absent locally, so the legend image can fail independently of the layer.

## Reference Used

The NDVI/Copernicus section in `docs/reference/field1_working_reference.php` was used as the functional reference. The active page keeps the same WMS 1.3.0 pattern:

- URL pattern: `https://sh.dataspace.copernicus.eu/ogc/wms/{COPERNICUS_ID}?`
- Layer names: `NDVI` and `LAI_SAVI`
- CRS: `EPSG:3857`
- Tiling scheme: `Cesium.WebMercatorTilingScheme`
- Farm bounding rectangle: west `-5.262770243294276`, south `40.883388135762168`, east `-5.255199073804725`, north `40.892776720220887`

The actual Copernicus ID remains loaded from local runtime configuration and is not documented here.

## Configuration Resolution

AgriTwin3 now resolves Copernicus configuration in this order:

1. `app_settings.sentinel_hub_wms_url`
2. `app_settings.copernicus_id`
3. `app_settings.sentinelhub_instance_id`
4. `app_settings.sentinel_hub_instance_id`
5. `COPERNICUS_ID`
6. `SENTINELHUB_INSTANCE_ID`
7. `SENTINEL_HUB_INSTANCE_ID`
8. `SENTINEL_HUB_WMS_URL`

If a full WMS URL is configured, it is used directly. Otherwise AgriTwin builds the Copernicus Data Space WMS URL from the configured instance identifier.

## Changes Made

- Trimmed the database-loaded Copernicus ID before use.
- Copied the working local Copernicus runtime value from the read-only AgriTwin2 configuration into the AgriTwin3 local `app_settings` table without committing the value.
- Added alias and environment lookup for Sentinel Hub/Copernicus configuration.
- Added `scripts/check_copernicus_wms.php` for secret-safe WMS availability checks.
- Added a safe configured/not-configured state for Copernicus controls.
- Disabled NDVI and LAI/SAVI controls when Copernicus configuration is missing.
- Kept Copernicus NDVI and LAI/SAVI layer creation after the Cesium viewer and bounding rectangle exist.
- Added safe imagery-provider error handling without exposing identifiers.
- Added a compact Copernicus layer status line.
- Added a legend failure fallback so a missing legend image does not break layer activation.
- Updated the field view labels to use Copernicus wording instead of Sentinel Hub wording for user-facing controls.

## Preserved Behaviour

The fix does not change Cesium terrain loading, the viewer container, the initial field camera, property boundary, sensors, machinery, weather stations, almond and pistachio trees, pivot model, solar panel models, weather panel, AEMET forecast panel, sidebar toggle, entity selection or InfoBox behaviour.

## Verification

Commands to run:

- `php -l agritwin_dashboard/field1.php`
- `php scripts/i18n/audit_translations.php`
- `make test`
- `docker compose config`
- `scripts/check_local_stack.sh`
- `php scripts/check_copernicus_wms.php`
- Authenticated curl check for `http://localhost/agritwin3/agritwin_dashboard/field1.php`

Rendered-page checks should confirm:

- Copernicus WMS URL construction is present.
- `NDVI` and `LAI_SAVI` layer names are present.
- `Cesium.WebMercatorTilingScheme` is used.
- The farm bounding rectangle is present.
- NDVI/LAI_SAVI controls are present.
- Legend references are present.
- Cesium terrain and `#cesiumContainer` remain present.
- No SQLSTATE, credentials or obsolete project wording appears.

## Manual Browser Check

If browser automation is unavailable, open `field1.php`, select `NDVI (Copernicus)`, then select `LAI/SAVI (Copernicus)`. The selected Copernicus layer should become visible above base imagery while terrain and farm entities remain visible. If the remote WMS rejects the request, the status line should show a professional unavailable state without breaking the field view.
