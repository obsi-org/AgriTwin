# Final HMI Corrections Report

Date: 2026-05-12

## Corrections Completed

- Removed the dashboard `Branding and pilot context` section.
- Added the AgriTwin logo to the shared footer alongside Fotovoltaica Macotera SL, OBSIDIAN, OpenAgri and EU acknowledgement assets.
- Removed duplicate visible text from the sidebar logo card; the card now displays only the AgriTwin logo.
- Expanded telemetry from individual demo sensors to five sensor stations: MAC-ALM-A, MAC-ALM-B, MAC-ALM-C, MAC-PIST-A and MAC-MAIZE-A.
- Updated the OpenAgri Services menu icon and retained consistent sidebar icon alignment.
- Reworked OpenAgri health states to distinguish live, demo, auto fallback, unavailable, unauthenticated and not configured.
- Added a no-secret Open-Meteo public weather fallback when OpenAgri Weather is not live-verified.
- Replaced the static API catalogue with Swagger UI loading `/agritwin3/api/openapi.json`.
- Replaced the settings safe-notes block with read-only environment-managed configuration fields.

## Weather Data Path

AgriTwin now reports dashboard weather through a provider abstraction:

1. OpenAgri Weather Data Service if live mode is configured and a real request succeeds.
2. Open-Meteo public forecast as a live fallback requiring no committed secrets.
3. Local station demo fallback if network/live weather is unavailable.

The dashboard separates `External weather service` from `On-farm sensor stations`.

## Verification

Verification included PHP syntax checks, OpenAPI JSON parsing, Docker Compose config, existing test suites, curl checks for HMI/API pages, rendered-text checks for removed labels, and Docker mock runtime smoke testing.
