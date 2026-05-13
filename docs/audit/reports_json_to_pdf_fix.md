# Reports JSON-to-PDF Fix

## Root Cause

`agritwin_dashboard/reports.php` rendered a structured advisory payload directly inside a `<pre>` block. That made the user-facing Reports page look like a technical JSON export rather than an operational report centre.

## Fix

- Replaced raw JSON rendering with report type selection, status, metadata cards, readable previews and PDF actions.
- Added `agritwin_dashboard/includes/reporting.php` to format report sections and generate local PDF files.
- Added report API endpoints for report types, generation, listing and PDF download.
- Added `var/reports/` as an ignored runtime output directory.
- Documented local PDF generation and OpenAgri Reporting Service integration status.

## Report Types

- Field Operational Report
- Weather and Sensor Report
- Irrigation Report
- Pest and Disease Risk Report
- OpenAgri Services Status Report
- KPI / Analytics Report

## Verification

Verification should confirm:

- `reports.php` contains no visible raw JSON payload;
- report previews are human-readable;
- generated PDFs start with `%PDF`;
- PDFs include headings, tables, units, source labels and the OpenAgri funding acknowledgement;
- generated PDFs are not staged or committed.

## Limitations

The current PDF generator is local and dependency-free. Live OpenAgri Reporting Service PDF generation can be wired later when the service exposes a verified PDF workflow for this deployment.
