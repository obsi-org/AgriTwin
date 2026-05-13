# Field View Translation Fix

## Purpose

This note records the language-handling correction for `agritwin_dashboard/field1.php`.

## Root Cause

The Field View page was using its own language fallback based on `$_GET['lang']` and `$_SESSION['preferred_language']`. That bypassed the shared AgriTwin language resolver, so a user navigating from the Spanish or Portuguese interface without an explicit `?lang=` parameter could still see the page in English.

## Language Mechanism

`field1.php` now uses the shared `agritwin_current_language()` helper from `agritwin_dashboard/includes/hmi_data.php`. This keeps the page aligned with:

- direct `?lang=en`, `?lang=pt-PT` and `?lang=es-ES` URLs;
- the `agritwin_lang` session value;
- the `agritwin_lang` cookie;
- the platform language selected on other pages.

Links from `digital_twin.php` and the farm redirect helper preserve the selected language when opening the Field View.

## Translated Content

The page continues to use its field-specific translation map for Cesium-safe PHP and JavaScript strings. The fix covers:

- page title and HTML language attribute;
- right-side weather, irrigation, pest/disease and task panels;
- AEMET forecast status messages;
- Copernicus index controls and legend messages;
- Cesium InfoBox labels for sensors, weather stations, machinery and coordinates;
- user-visible entity names for the property boundary, irrigation pivot and solar panels;
- navigation back to the AgriTwin Dashboard.

## Cesium Preservation

No Cesium terrain, Copernicus NDVI/LAI_SAVI, NASA GIBS, CSV tree, GLTF model, sensor, weather-station, pivot, solar-panel or field-boundary loading logic was intentionally changed. The update is limited to language selection, language-preserving links and user-visible translated labels.

## Verification

Run:

```bash
php -l agritwin_dashboard/field1.php
php -l agritwin_dashboard/digital_twin.php
php -l agritwin_dashboard/field.php
scripts/i18n/audit_translations.php
make test
docker compose config
scripts/check_local_stack.sh
```

Authenticated curl checks should cover:

- `/agritwin_dashboard/field1.php?lang=en`
- `/agritwin_dashboard/field1.php?lang=pt-PT`
- `/agritwin_dashboard/field1.php?lang=es-ES`
- `/agritwin_dashboard/digital_twin.php?lang=en`
- `/agritwin_dashboard/digital_twin.php?lang=pt-PT`
- `/agritwin_dashboard/digital_twin.php?lang=es-ES`

Manual browser verification, if browser automation is unavailable: open the Field View in Spanish and Portuguese, select a sensor and a weather station, and confirm that the InfoBox labels use the active language while Cesium terrain and Copernicus layers remain available.
