# AgriTwin Sample Dataset Package

This folder provides privacy-safe datasets for AgriTwin development, validation and distribution. It contains anonymised aggregated samples, synthetic developer data and schema-only examples compatible with the AgriTwin sensor, DSS, analytics, reporting and Digital Twin architecture.

The package does not include raw pilot-farm data, private coordinates, exact parcel boundaries, operational credentials, security secrets, site-specific configuration or non-anonymised operational logs.

## Packages

- `anonymised_aggregated/`: daily aggregated sample records with generalised parcel and infrastructure identifiers.
- `synthetic_developer/`: deterministic synthetic timeseries and event records for developer testing.
- `schema_only/`: field definitions, units, JSON Schema, validation rules and example records.
- `validation/`: local validator for privacy and schema checks.
- `scripts/`: deterministic generator used to rebuild the sample package.

Run:

```bash
python3 datasets/agritwin_sample_dataset/scripts/generate_sample_dataset.py
python3 datasets/agritwin_sample_dataset/validation/validate_dataset.py
```
