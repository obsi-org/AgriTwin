#!/usr/bin/env python3
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

"""Validate the privacy-safe AgriTwin sample dataset package."""

from __future__ import annotations

import csv
import json
import re
import sys
from datetime import datetime
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
PRIVATE_NAME_PATTERNS = [
    "Fotovoltaica " + "Maco" + "tera",
    "FOTO" + "MACO",
    "Foto" + "maco",
    "Maco" + "tera",
    "Nava de " + "Sotrobal",
    "Gregorio " + "Sanchez",
    "gregorio" + r"\.sanchez",
]
FORBIDDEN_PATTERNS = PRIVATE_NAME_PATTERNS + [
    r"40\.883" + r"642",
    r"-5\.259" + r"723",
    r"40\.883388" + r"135762168",
    r"-5\.262770" + r"243294276",
    "cesium" + r"_token",
    "copernicus" + r"_id",
    r"AEMET[_ -]?KEY",
    r"AKIA[0-9A-Z]{16}",
    r"-----BEGIN (RSA |EC |OPENSSH |)PRIVATE KEY-----",
    r"(?i)(api[_-]?key|client_secret|password|token)\s*[:=]\s*[A-Za-z0-9_\-]{12,}",
]
EXPECTED_FILES = [
    "anonymised_aggregated/aggregated_weather_daily.csv",
    "anonymised_aggregated/aggregated_sensor_daily.csv",
    "anonymised_aggregated/aggregated_irrigation_daily.csv",
    "anonymised_aggregated/aggregated_energy_daily.csv",
    "anonymised_aggregated/aggregated_pest_disease_daily.csv",
    "anonymised_aggregated/aggregated_dss_recommendations.csv",
    "anonymised_aggregated/aggregated_kpis.csv",
    "anonymised_aggregated/dataset_manifest.json",
    "synthetic_developer/synthetic_weather_timeseries.csv",
    "synthetic_developer/synthetic_sensor_timeseries.csv",
    "synthetic_developer/synthetic_irrigation_events.csv",
    "synthetic_developer/synthetic_energy_timeseries.csv",
    "synthetic_developer/synthetic_eo_indices.csv",
    "synthetic_developer/synthetic_farm_activity_log.csv",
    "synthetic_developer/synthetic_dss_recommendations.json",
    "synthetic_developer/synthetic_digital_twin_telemetry.json",
    "synthetic_developer/synthetic_openagri_service_status.json",
    "synthetic_developer/dataset_manifest.json",
    "schema_only/schema.json",
    "schema_only/field_definitions.csv",
    "schema_only/units.csv",
    "schema_only/validation_rules.json",
    "schema_only/example_weather_record.json",
    "schema_only/example_sensor_record.json",
    "schema_only/example_irrigation_record.json",
    "schema_only/example_energy_record.json",
    "schema_only/example_eo_record.json",
    "schema_only/example_activity_record.json",
    "schema_only/example_dss_recommendation.json",
    "schema_only/example_digital_twin_telemetry.json",
    "schema_only/example_openagri_service_status.json",
    "schema_only/example_kpi_record.json",
    "schema_only/example_records.csv",
    "dataset_package_manifest.json",
]
RANGES = {
    "air_temperature_c": (8, 34),
    "air_temperature_c_avg": (8, 34),
    "air_temperature_avg_c": (8, 34),
    "air_temperature_min_c": (6, 34),
    "air_temperature_max_c": (8, 34),
    "relative_humidity_pct": (25, 90),
    "relative_humidity_pct_avg": (25, 90),
    "relative_humidity_avg_pct": (25, 90),
    "soil_moisture_vwc_pct": (10, 38),
    "soil_moisture_vwc_pct_avg": (10, 38),
    "soil_moisture_vwc_pct_min": (10, 38),
    "soil_temperature_c": (6, 28),
    "soil_temperature_c_avg": (6, 28),
    "leaf_wetness_pct": (0, 100),
    "leaf_wetness_pct_avg": (0, 100),
    "rainfall_mm": (0, 25),
    "wind_speed_kmh": (0, 45),
    "wind_speed_avg_kmh": (0, 45),
    "solar_radiation_w_m2": (0, 950),
    "irrigation_flow_m3_h": (0, 40),
    "irrigation_flow_m3_h_avg": (0, 40),
    "pump_energy_kwh": (0, 260),
    "pv_power_kw": (0, 90),
    "water_stress_index": (0, 1),
    "water_stress_index_avg": (0, 1),
    "pest_disease_risk_index": (0, 1),
    "nitrogen_recommendation_kg_ha": (0, 120),
}
ALLOWED_CLASSIFICATIONS = {"anonymised_aggregated_sample", "synthetic_developer_sample", "schema_only_example"}


def fail(message: str, failures: list[str]) -> None:
    failures.append(message)
    print(f"[dataset-validation] {message}", file=sys.stderr)


def load_json(path: Path, failures: list[str]) -> Any:
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except Exception as exc:  # noqa: BLE001 - validation script reports all failures.
        fail(f"invalid JSON {path.relative_to(ROOT)}: {exc}", failures)
        return None


def is_iso_datetime(value: str) -> bool:
    try:
        datetime.fromisoformat(value.replace("Z", "+00:00"))
        return True
    except ValueError:
        return False


def validate_csv(path: Path, field_defs: set[str], failures: list[str]) -> int:
    with path.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        if not reader.fieldnames:
            fail(f"{path.relative_to(ROOT)} has no CSV header", failures)
            return 0
        if path.name != "field_definitions.csv":
            unknown = set(reader.fieldnames) - field_defs - {
                "region", "province", "site_label", "location_precision", "coarse_grid_centroid",
                "lat", "lon", "coordinate_precision", "coordinate_type", "record_count",
                "crop_code", "sensor_station_group_id", "irrigation_infrastructure_group_id",
                "energy_infrastructure_group_id", "pump_group_id", "pv_energy_kwh",
                "pump_energy_anomaly_flag", "irrigation_runtime_minutes",
                "irrigation_volume_m3_estimated", "irrigation_recommendation",
                "risk_level", "monitoring_recommendation", "recommendation_date",
                "recommendation_type", "confidence", "kpi_name", "kpi_value", "unit",
                "farm_id", "asset_id", "event_id", "started_at", "ended_at",
                "estimated_volume_m3", "scenario_label", "pv_meter_group_id",
                "observed_date", "ndvi_mean", "lai_savi_mean", "cloud_cover_pct",
                "activity_id", "activity_at", "activity_type", "operator_role", "note",
                "example_file", "field_name", "type", "required", "description",
                "allowed_values_or_range", "parcel_id",
            }
            if unknown:
                fail(f"{path.relative_to(ROOT)} has undocumented fields: {sorted(unknown)}", failures)
        rows = 0
        for row in reader:
            rows += 1
            classification = row.get("data_source_classification")
            if classification and classification not in ALLOWED_CLASSIFICATIONS:
                fail(f"{path.relative_to(ROOT)} row {rows} invalid classification {classification}", failures)
            for field, (lower, upper) in RANGES.items():
                if field in row and row[field] not in ("", None):
                    value = float(row[field])
                    if value < lower or value > upper:
                        fail(f"{path.relative_to(ROOT)} row {rows} {field}={value} outside {lower}..{upper}", failures)
            for field in ("observed_at", "generated_at", "started_at", "ended_at", "activity_at"):
                if row.get(field) and not is_iso_datetime(row[field]):
                    fail(f"{path.relative_to(ROOT)} row {rows} invalid ISO timestamp {field}={row[field]}", failures)
            if row.get("bucket_date"):
                try:
                    datetime.strptime(row["bucket_date"], "%Y-%m-%d")
                except ValueError:
                    fail(f"{path.relative_to(ROOT)} row {rows} invalid bucket_date {row['bucket_date']}", failures)
        if rows == 0:
            fail(f"{path.relative_to(ROOT)} has no data rows", failures)
        return rows


def scan_forbidden(failures: list[str]) -> None:
    patterns = [re.compile(pattern) for pattern in FORBIDDEN_PATTERNS]
    for path in ROOT.rglob("*"):
        if "__pycache__" in path.parts or path.suffix in {".pyc", ".pyo"}:
            continue
        if not path.is_file() or path.suffix.lower() in {".png", ".jpg", ".jpeg", ".gif", ".pdf"}:
            continue
        if path.relative_to(ROOT).as_posix() == "validation/validate_dataset.py":
            continue
        text = path.read_text(encoding="utf-8", errors="ignore")
        for pattern in patterns:
            if pattern.search(text):
                fail(f"forbidden or credential-like content in {path.relative_to(ROOT)}: {pattern.pattern}", failures)


def main() -> int:
    failures: list[str] = []
    for rel in EXPECTED_FILES:
        if not (ROOT / rel).is_file():
            fail(f"missing required file {rel}", failures)
    scan_forbidden(failures)
    schema = load_json(ROOT / "schema_only/schema.json", failures)
    validation = load_json(ROOT / "schema_only/validation_rules.json", failures)
    if not isinstance(schema, dict) or schema.get("title") != "AgriTwin Sample Dataset Record":
        fail("schema_only/schema.json does not contain the expected schema title", failures)
    if not isinstance(validation, dict) or "numeric_ranges" not in validation:
        fail("schema_only/validation_rules.json missing numeric_ranges", failures)
    field_defs = set()
    field_file = ROOT / "schema_only/field_definitions.csv"
    if field_file.exists():
        with field_file.open(newline="", encoding="utf-8") as handle:
            for row in csv.DictReader(handle):
                field_defs.add(row["field_name"])
    row_counts = {}
    for path in sorted(ROOT.rglob("*.csv")):
        row_counts[str(path.relative_to(ROOT))] = validate_csv(path, field_defs, failures)
    for path in sorted(ROOT.rglob("*.json")):
        load_json(path, failures)
    if failures:
        print(f"[dataset-validation] failed with {len(failures)} issue(s)", file=sys.stderr)
        return 1
    print("[dataset-validation] passed")
    print(json.dumps({"csv_row_counts": row_counts}, indent=2, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
