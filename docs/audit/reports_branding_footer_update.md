# Reports Branding and Footer Update

## Scope

This update improves the Reports module preview and generated PDFs so they read as branded professional AgriTwin documents rather than technical exports.

## Branding Rules Applied

- AgriTwin and Fotovoltaica Macotera SL appear first in the report header.
- OpenAgri and Obsidian Innovation Institute - Associação appear in the secondary branding row.
- The footer includes the European Union funding logo, `© 2026 AgriTwin`, and the required funding acknowledgement.
- Generated PDFs embed logo assets using the local PHP GD conversion path and do not depend on external rendering services.
- Report preview sections use operational icons for weather, sensors, irrigation, tasks, service health and analytics.
- Generated PDFs repeat the header/footer on each page and use compact section markers with readable tables instead of raw JSON.

## Required Funding Text

This software prototype is part of a sub-project that has received funding from the European Union's Horizon Europe research and innovation programme under project OpenAgri (grant agreement No 101134083)

## Verification

Checks performed:

- Reports page rendered in English, Portuguese and Spanish.
- Report preview contains no raw JSON block.
- Generated PDF starts with `%PDF`.
- Generated PDF contains AgriTwin, Fotovoltaica Macotera SL, OpenAgri and Obsidian Innovation Institute branding text.
- Generated PDF contains `© 2026 AgriTwin` and the funding acknowledgement.
- Generated report files remain under ignored `var/reports/`.

## Remaining Notes

OpenAgri Reporting Service remains visible through service health, but AgriTwin currently generates the operator-facing PDF locally for reliable formatting and branding.
