# Developer Usage

Use `synthetic_developer/` for repeatable tests of sensor ingestion, DSS recommendations, reports and OpenAgri adapter handling. Use `anonymised_aggregated/` for dashboard, analytics and KPI tests that need daily or parcel-level summaries.

The data is deterministic. Change `--seed` only when intentionally creating a new sample fixture version.

No external API calls or secrets are required.
