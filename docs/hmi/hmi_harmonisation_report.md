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

The AgriTwin sidebar logo is now placed in a white card and rendered substantially larger. Footers consistently include FOTOMACO, OBSIDIAN, OpenAgri and EU acknowledgement logos with `© 2026 AgriTwin. All rights reserved.`

## Sidebar Routing

The sidebar no longer uses one-page anchor navigation. It links to separate PHP pages for field overview, Digital Twin, telemetry, irrigation, pest/disease, Farm Calendar, OpenAgri services, reports, KPIs and settings.

## GAIA Cleanup

Visible GAIA references in the local HMI were replaced with AgriTwin equivalents. Details are recorded in `docs/audit/gaia_reference_cleanup.md`.

## Sensor Data Improvements

Added a central synthetic Macotera/FOTOMACO demo dataset for 2026-05-12 with nine realistic sensor records covering almond blocks, pump flow, PV energy, weather and pistachio reference monitoring.

## OpenAgri Health Mode

The OpenAgri Services page shows all required services as healthy in local demo/mock mode with realistic latency values and timestamps. This does not fake production status; it is labelled local demo/mock.

## Verification

Verification included PHP syntax checks, repository grep checks for GAIA references, page curl checks under `/agritwin3/`, Docker Compose config, and the existing `make publication-check` test suite.
