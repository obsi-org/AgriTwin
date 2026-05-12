# AgriTwin HMI Harmonisation Report

## Pages Reviewed

- `/agritwin3/`
- `/agritwin3/agritwin_dashboard/index.php`
- `/agritwin3/agritwin_dashboard/digital_twin.php`
- `/agritwin3/agritwin_dashboard/telemetry.php`
- `/agritwin3/agritwin_dashboard/irrigation.php`
- `/agritwin3/agritwin_dashboard/pest_disease.php`
- `/agritwin3/agritwin_dashboard/farm_calendar.php`
- `/agritwin3/agritwin_dashboard/openagri_services.php`
- `/agritwin3/agritwin_dashboard/reports.php`
- `/agritwin3/agritwin_dashboard/kpis.php`
- `/agritwin3/agritwin_dashboard/settings.php`

## Layout Changes

Added a shared plain-PHP HMI layout under `agritwin_dashboard/includes/`. It provides a consistent header, sidebar, card styling, footer, active navigation, responsive behaviour and status badge styling across the core platform pages.

## Logo and Footer Changes

The AgriTwin sidebar logo is now placed in a white card. Footers consistently include Fotovoltaica Macotera SL, OBSIDIAN, OpenAgri and EU acknowledgement logos with `© 2026 AgriTwin. All rights reserved.`

Follow-up professionalisation reduced the sidebar logo to a balanced size, removed the visible `Pilot HMI` label, and enlarged the Fotovoltaica Macotera SL and OBSIDIAN footer logos for better presentation in local demonstrations.

Final corrections removed duplicate visible sidebar logo text and added the AgriTwin logo to the shared footer.

## Sidebar Routing

The sidebar no longer uses one-page anchor navigation. It links to separate PHP pages for field overview, Digital Twin, Sensor Stations, irrigation, pest/disease, Farm Calendar, OpenAgri services, reports, KPIs, API / Swagger and settings.

## GAIA Cleanup

Visible GAIA references in the local HMI were replaced with AgriTwin equivalents. Details are recorded in `docs/audit/gaia_reference_cleanup.md`.

## Sensor Data Improvements

Added a central synthetic Fotovoltaica Macotera SL / Macotera demo dataset for 2026-05-12 with five sensor stations covering almond, pistachio and maize blocks plus soil, microclimate, irrigation flow and pump-energy readings.

## OpenAgri Health Mode

The OpenAgri Services page now distinguishes live, demo, auto fallback, unavailable, unauthenticated and not-configured connector states. Local demo services are no longer presented as live healthy integrations.

## Weather Source

The dashboard now separates External Weather Service values from On-farm Sensor Stations. OpenAgri Weather is preferred when live-configured and verified; otherwise AgriTwin uses Open-Meteo public forecast as a no-secret live fallback before falling back to local demo station data.

## Display Name Cleanup

Visible shorthand names such as `FOTOMACO` were replaced with `Fotovoltaica Macotera SL` in the active UI. Technical filenames and asset paths such as `fotomaco.png` were retained because changing them would break existing references.

## Verification

Verification included PHP syntax checks, repository grep checks for GAIA references, page curl checks under `/agritwin3/`, Docker Compose config, and the existing `make publication-check` test suite.
