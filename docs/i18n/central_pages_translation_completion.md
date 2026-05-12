# Central Pages Translation Completion

Date: 2026-05-12

AgriTwin now uses the central PHP translation files under `agritwin_dashboard/lang/` for the central HMI pages:

- `en.php`
- `pt-PT.php`
- `es-ES.php`

## Pages Checked

- Dashboard / Field Overview
- Digital Twin Map
- Sensor Stations / Telemetry
- Irrigation Schedule
- Pest & Disease Alerts
- Farm Calendar
- Reports
- KPI Dashboard
- OpenAgri Services
- API / Swagger
- Settings
- Login
- User Profile
- User Management

## Keys Added

The update added keys for page headings, dashboard cards, service-health tables, settings forms, Swagger text, user-management messages, sensor-reading labels, recommendations, operations, KPI labels, weather-source explanations and status badges.

## Language Persistence

The selected language is accepted through `?lang=en`, `?lang=pt-PT` or `?lang=es-ES`, then persisted in the session and an `agritwin_lang` cookie scoped to `/agritwin3/`.

## Limitations

Operational identifiers such as station IDs, crop block names, API paths, repository URLs and product names remain unchanged. These are treated as domain data or technical identifiers rather than translatable interface copy.
