# Footer Funding Acknowledgement

## Required Text

The following acknowledgement was added to the AgriTwin platform footer and public entry/login page:

This software prototype is part of a sub-project that has received funding from the European Union's Horizon Europe research and innovation programme under project OpenAgri (grant agreement No 101134083)

## Layouts Updated

- Shared authenticated dashboard footer in `agritwin_dashboard/includes/hmi_layout.php`.
- Root public entry/login page in `index.php`.
- Standalone Cesium Field View in `agritwin_dashboard/field1.php`.
- Compatibility login route in `agritwin_dashboard/login.php`, which redirects to the root login page.

## Styling Notes

The acknowledgement is placed below the footer logo strip. It uses smaller centred text, constrained line length and adequate contrast so that it remains readable without dominating the footer. The Cesium Field View uses a compact acknowledgement under the existing logo overlay to avoid covering terrain controls, farm objects or the right-side operational panel.

## Verification Commands

```bash
php -l index.php
php -l agritwin_dashboard/includes/hmi_data.php
php -l agritwin_dashboard/includes/hmi_layout.php
php -l agritwin_dashboard/field1.php
php -l agritwin_dashboard/login.php
php scripts/i18n/audit_translations.php
make test
docker compose config
scripts/check_local_stack.sh
```

Rendered-page checks should confirm the acknowledgement appears on:

- `/agritwin3/index.php`
- `/agritwin3/agritwin_dashboard/login.php`
- `/agritwin3/agritwin_dashboard/index.php`
- `/agritwin3/agritwin_dashboard/field1.php`
- central dashboard pages that use the shared layout.
