# Field View Copernicus NDVI Fix

## Root Cause

The field view used the same Copernicus WMS shape as the working reference, but the layer setup was not guarded against missing configuration or WMS request failures. When Copernicus configuration or imagery requests failed, the page provided no safe status and the index controls could leave the operator with no visible change. The LAI/SAVI legend asset is also not present locally, so the legend image could fail independently of the layer.

## Reference Used

The NDVI/Copernicus section in `docs/reference/field1_working_reference.php` was used as the functional reference. The active page keeps the same WMS 1.3.0 pattern:

- URL pattern: `https://sh.dataspace.copernicus.eu/ogc/wms/{COPERNICUS_ID}?`
- Layer names: `NDVI` and `LAI_SAVI`
- CRS: `EPSG:3857`
- Tiling scheme: `Cesium.WebMercatorTilingScheme`
- Farm bounding rectangle: west `-5.262770243294276`, south `40.883388135762168`, east `-5.255199073804725`, north `40.892776720220887`

The actual Copernicus ID remains loaded from `app_settings.copernicus_id` and is not documented here.

## Changes Made

- Trimmed the database-loaded Copernicus ID before use.
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
