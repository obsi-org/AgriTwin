# Reports Branding and PDF Layout Upgrade

## Scope

This update improves the Reports module preview and generated PDFs with stronger branding, readable operational sections, tables and repeated footer notices.

## Header Branding

- AgriTwin logo appears first.
- Fotovoltaica Macotera SL logo appears second.
- OpenAgri and Obsidian Innovation Institute - Associação appear in the secondary branding area.

## Footer Branding

Generated reports include:

- European Union funding logo where the local asset is available;
- Obsidian Innovation Institute - Associação;
- `© 2026 AgriTwin`;
- the required OpenAgri funding acknowledgement.

## Layout Improvements

- The report preview uses section icons for executive summary, weather, sensors, irrigation, operational tasks, pest and disease risk, services and KPIs.
- The PDF generator embeds existing logo assets as image objects.
- Longer reports are split across multiple PDF pages with repeated header and footer areas.
- Report data is presented as human-readable tables and labelled sections, not raw JSON.

## Verification

Use the Reports page to generate a sample report, then verify that the PDF starts with `%PDF`, includes the required branding and acknowledgement, and does not contain raw JSON or secret configuration values.
