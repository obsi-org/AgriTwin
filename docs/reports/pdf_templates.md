# PDF Report Templates

## Template Structure

AgriTwin PDF reports are generated locally using a lightweight PHP PDF writer. Each report includes:

- top header logo row with AgriTwin first and Fotovoltaica Macotera SL second;
- secondary branding row with OpenAgri and Obsidian Innovation Institute - Associação;
- report title and type;
- generation timestamp;
- Nava de Sotrobal / Macotera / Salamanca location;
- data-source summary;
- formatted human-readable sections;
- tables with labels, values and units;
- the OpenAgri funding acknowledgement.
- `© 2026 AgriTwin`.

## Main Sections

The template builder maps structured platform data into readable sections:

- weather and sensor observations;
- irrigation recommendation and water-balance indicators;
- pest and disease risk interpretation;
- OpenAgri service status;
- KPI / analytics table;
- operational interpretation.

## Technical Notes

Generated reports are stored in `var/reports/`, which is ignored by Git. Report downloads require an authenticated AgriTwin session. The PDF generator intentionally does not include raw JSON payloads, credentials or secret configuration values.

The current local generator uses a dependency-free PDF writer. Existing PNG logo assets are converted to JPEG streams through PHP GD and embedded as PDF image objects. This avoids external rendering tools while producing valid `application/pdf` output for operational review and download.
