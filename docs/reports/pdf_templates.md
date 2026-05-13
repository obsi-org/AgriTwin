# PDF Report Templates

## Template Structure

AgriTwin PDF reports are generated locally using a lightweight PHP PDF writer. Each report includes:

- AgriTwin title block;
- Fotovoltaica Macotera SL, OBSIDIAN and OpenAgri branding text;
- report title and type;
- generation timestamp;
- Nava de Sotrobal / Macotera / Salamanca location;
- data-source summary;
- formatted human-readable sections;
- tables with labels, values and units;
- the OpenAgri funding acknowledgement.

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

The current local generator uses text-based PDF layout. It avoids external dependencies and produces valid `application/pdf` output for operational review and download.
