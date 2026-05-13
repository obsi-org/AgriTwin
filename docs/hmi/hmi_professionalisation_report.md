# AgriTwin HMI Professionalisation Report

## Scope

This update reviewed the shared AgriTwin dashboard shell and the central operational pages that inherit it: dashboard, analytics, Digital Twin, sensor stations, irrigation, pest and disease, Farm Calendar, reports, KPIs, OpenAgri Services, API / Swagger, settings, users and help.

## Sidebar Branding

- Increased the AgriTwin logo size in the left sidebar while keeping it inside the light brand container.
- Added the existing local `OpenAgri.png` asset below the AgriTwin logo.
- Added a subtle divider between the two logos for visual balance.
- Added translated and accessible logo alternative text in English, Portuguese from Portugal and Spanish from Spain.
- Preserved the `/agritwin3/` base path and existing navigation links.

## Interface Improvements

- Widened the sidebar slightly to support clearer branding and menu spacing.
- Strengthened active navigation states with a left accent and consistent icon alignment.
- Improved the header with a more deliberate boundary and depth.
- Harmonised card radii, section spacing, chart containers, controls and table hover states.
- Improved help-section readability with clearer separation between sections.
- Kept all changes in the shared HMI stylesheet so the dashboard, analytics, settings, OpenAgri Services, help, user management and API pages remain visually consistent.

## Responsive and Accessibility Notes

- The sidebar brand block spans the mobile navigation grid.
- Logo dimensions are reduced at narrower widths to avoid overflow.
- Both AgriTwin and OpenAgri logos include translated `alt` text.
- Navigation icons retain visible text labels, so status and navigation do not depend on colour alone.

## Translation Updates

Added translation keys in:

- `agritwin_dashboard/lang/en.php`
- `agritwin_dashboard/lang/pt-PT.php`
- `agritwin_dashboard/lang/es-ES.php`

New keys:

- `platform_branding`
- `agritwin_logo_alt`
- `openagri_logo_alt`

## Verification

Commands run:

- `php -l agritwin_dashboard/includes/hmi_layout.php`
- `php -l agritwin_dashboard/lang/en.php`
- `php -l agritwin_dashboard/lang/pt-PT.php`
- `php -l agritwin_dashboard/lang/es-ES.php`
- `php scripts/i18n/audit_translations.php`

Additional runtime checks are recorded in the final task summary for the commit.

Runtime verification completed:

- `make test` passed.
- `docker compose config` passed for the main AgriTwin compose file.
- `docker compose -f docker-compose.openagri.yml config` reported the existing OpenAgri compose limitation: service `app` has neither an image nor a build context. This change did not modify OpenAgri orchestration.
- `scripts/check_local_stack.sh` passed; database container was healthy and rendered page checks returned HTTP 200.
- `scripts/openagri/check_openagri_services.sh --core-only` passed for Gatekeeper, Weather, Irrigation and Pest & Disease local endpoints.
- Curl checks for dashboard, analytics, OpenAgri Services, settings, help and API / Swagger returned HTTP 200.
- Dashboard sidebar HTML includes both `brand-logo-primary` and `brand-logo-secondary`.
- English, Portuguese and Spanish checks for the login page, dashboard and help page returned HTTP 200 and did not render prohibited project/prototype wording.
