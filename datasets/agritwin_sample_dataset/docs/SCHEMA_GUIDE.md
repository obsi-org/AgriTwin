# Schema Guide

The schema package uses AgriTwin-compatible names such as `observed_at`, `generated_at`, `parcel_group_id`, `station_id`, `soil_moisture_vwc_pct`, `irrigation_flow_m3_h`, `pump_energy_kwh`, `water_stress_index`, `pest_disease_risk_index` and `nitrogen_recommendation_kg_ha`.

Timestamps are ISO-8601. Aggregated files use `bucket_date` and may use `bucket_start` / `bucket_end` in downstream extensions. Synthetic records use generated ISO-8601 timestamps and are marked with `synthetic_developer_sample`.

Units are documented in `schema_only/units.csv`.
