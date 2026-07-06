# Training Data Requirements

AgriTwin model training expects tabular time-series records aligned with the sample dataset schema.

## Required Fields

| Field | Unit | Notes |
| --- | --- | --- |
| `soil_moisture_vwc_pct` | % VWC | Numeric soil moisture value. |
| `soil_temperature_c` | °C | Numeric soil temperature value. |
| `air_temperature_c` | °C | Numeric air temperature value. |
| `relative_humidity_pct` | % | Numeric relative humidity value. |
| `leaf_wetness_pct` | % | Numeric leaf wetness indicator. |

Additional useful fields include rainfall, solar radiation, wind speed, irrigation flow, pump energy and PV power.

## Privacy Requirements

Training data committed to the repository must be anonymised, aggregated, synthetic or schema-only. Do not commit:

- Raw pilot-farm readings.
- Exact private coordinates.
- Private parcel or infrastructure identifiers.
- Credentials or service tokens.
- Non-anonymised operational logs.
- Site-specific thresholds.

## Distribution Workflow

Distribution builds must use `datasets/agritwin_sample_dataset/` by default. Local deployments may point training configs to authorised local data through ignored configuration only.
