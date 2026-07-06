#!/usr/bin/env python3
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

"""Generate deterministic, privacy-safe AgriTwin sample datasets.

This script does not read operational data, credentials, local databases or
external services. It writes anonymised/aggregated samples, synthetic developer
timeseries and schema-only examples compatible with the AgriTwin data model.
"""

from __future__ import annotations

import argparse
import csv
import json
import math
import random
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any, Iterable


DEFAULT_DAYS = 30
DEFAULT_SEED = 134083
BASE_DATE = datetime(2026, 5, 1, tzinfo=timezone.utc)
SITE_METADATA = {
    "region": "Castilla y León",
    "province": "Salamanca",
    "site_label": "anonymised_salamanca_agri_site",
    "location_precision": "regional_or_grid_cell",
    "coarse_grid_centroid": {"lat": 40.9, "lon": -5.7, "coordinate_precision": "1_decimal_degree"},
}
PARCELS = [
    {"parcel_group_id": "parcel_group_001", "crop_code": "almond", "area_ha": 4.8},
    {"parcel_group_id": "parcel_group_002", "crop_code": "pistachio", "area_ha": 3.6},
    {"parcel_group_id": "parcel_group_003", "crop_code": "maize", "area_ha": 5.1},
]
SENSOR_STATIONS = ["sensor_station_A", "sensor_station_B", "sensor_station_C", "sensor_station_D"]
PRIVACY_TRANSFORMATIONS = [
    "location_generalised",
    "parcel_identifiers_generalised",
    "infrastructure_identifiers_generalised",
    "timestamps_bucketed",
    "values_aggregated",
    "private_logs_excluded",
]
DATA_SOURCE_CLASSIFICATIONS = [
    "anonymised_aggregated_sample",
    "synthetic_developer_sample",
    "schema_only_example",
]


def clamp(value: float, lower: float, upper: float) -> float:
    return max(lower, min(upper, value))


def round1(value: float) -> float:
    return round(value, 1)


def write_csv(path: Path, rows: list[dict[str, Any]], fieldnames: list[str]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames, lineterminator="\n")
        writer.writeheader()
        writer.writerows(rows)


def write_json(path: Path, payload: Any) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def daily_weather(day: int, rng: random.Random) -> dict[str, Any]:
    date = (BASE_DATE + timedelta(days=day)).date().isoformat()
    seasonal = 18.5 + math.sin(day / 7.0) * 2.3
    rainfall = 0.0
    if day in {4, 5, 17, 23}:
        rainfall = round1(rng.uniform(3.0, 18.0))
    elif rng.random() < 0.18:
        rainfall = round1(rng.uniform(0.2, 3.5))
    max_temp = round1(clamp(seasonal + rng.uniform(5.5, 11.5), 14, 34))
    min_temp = round1(clamp(seasonal - rng.uniform(4.0, 8.5), 6, 22))
    avg_temp = round1((max_temp + min_temp) / 2)
    humidity = int(clamp(72 - (avg_temp - 14) * 2.2 + rainfall * 1.8 + rng.uniform(-8, 8), 25, 90))
    return {
        "bucket_date": date,
        **SITE_METADATA,
        "air_temperature_min_c": min_temp,
        "air_temperature_avg_c": avg_temp,
        "air_temperature_max_c": max_temp,
        "relative_humidity_avg_pct": humidity,
        "rainfall_mm": rainfall,
        "wind_speed_avg_kmh": round1(clamp(rng.uniform(4, 24) + (2 if rainfall > 5 else 0), 0, 45)),
        "solar_radiation_daily_mj_m2": round1(clamp(rng.uniform(13, 27) - rainfall * 0.25, 4, 30)),
        "data_source_classification": "anonymised_aggregated_sample",
    }


def generate_anonymised_aggregated(root: Path, days: int, seed: int) -> dict[str, int]:
    rng = random.Random(seed)
    out = root / "anonymised_aggregated"
    weather_rows: list[dict[str, Any]] = []
    sensor_rows: list[dict[str, Any]] = []
    irrigation_rows: list[dict[str, Any]] = []
    energy_rows: list[dict[str, Any]] = []
    pest_rows: list[dict[str, Any]] = []
    dss_rows: list[dict[str, Any]] = []
    kpi_rows: list[dict[str, Any]] = []

    for day in range(days):
        weather = daily_weather(day, rng)
        weather_rows.append(weather)
        rainfall = float(weather["rainfall_mm"])
        for idx, parcel in enumerate(PARCELS):
            crop_offset = [0.0, -1.5, 2.0][idx]
            station = SENSOR_STATIONS[idx % len(SENSOR_STATIONS)]
            soil_avg = round1(clamp(25.5 - day * 0.08 + rainfall * 0.45 + crop_offset + rng.uniform(-1.8, 1.8), 10, 38))
            stress = round(clamp((24 - soil_avg) / 18, 0, 1), 2)
            irrigation_required = soil_avg < (21.0 if parcel["crop_code"] != "maize" else 24.0)
            bucket_date = weather["bucket_date"]
            sensor_rows.append({
                "bucket_date": bucket_date,
                "parcel_group_id": parcel["parcel_group_id"],
                "crop_code": parcel["crop_code"],
                "sensor_station_group_id": station,
                "soil_moisture_vwc_pct_avg": soil_avg,
                "soil_moisture_vwc_pct_min": round1(max(10, soil_avg - rng.uniform(1.4, 3.8))),
                "soil_temperature_c_avg": round1(clamp(float(weather["air_temperature_avg_c"]) - rng.uniform(0.5, 3.0), 6, 28)),
                "air_temperature_c_avg": weather["air_temperature_avg_c"],
                "relative_humidity_pct_avg": weather["relative_humidity_avg_pct"],
                "leaf_wetness_pct_avg": int(clamp(12 + rainfall * 5 + rng.uniform(0, 25), 0, 100)),
                "water_stress_index_avg": stress,
                "record_count": 96,
                "data_source_classification": "anonymised_aggregated_sample",
            })
            irrigation_flow = round1(rng.uniform(12, 34) if irrigation_required and rainfall < 5 else 0.0)
            irrigation_rows.append({
                "bucket_date": bucket_date,
                "parcel_group_id": parcel["parcel_group_id"],
                "crop_code": parcel["crop_code"],
                "irrigation_infrastructure_group_id": "irrigation_pump_group_01",
                "irrigation_flow_m3_h_avg": irrigation_flow,
                "irrigation_runtime_minutes": int(rng.uniform(45, 150) if irrigation_flow > 0 else 0),
                "irrigation_volume_m3_estimated": round1(irrigation_flow * rng.uniform(0.7, 2.2)),
                "irrigation_recommendation": "review_irrigation_window" if irrigation_required else "maintain_monitoring",
                "data_source_classification": "anonymised_aggregated_sample",
            })
            pest_risk = round(clamp((float(weather["relative_humidity_avg_pct"]) - 45) / 70 + rainfall / 40 + rng.uniform(-0.08, 0.08), 0, 1), 2)
            pest_rows.append({
                "bucket_date": bucket_date,
                "parcel_group_id": parcel["parcel_group_id"],
                "crop_code": parcel["crop_code"],
                "leaf_wetness_pct_avg": sensor_rows[-1]["leaf_wetness_pct_avg"],
                "pest_disease_risk_index": pest_risk,
                "risk_level": "high" if pest_risk >= 0.7 else ("moderate" if pest_risk >= 0.4 else "low"),
                "monitoring_recommendation": "inspect_traps_within_48h" if pest_risk >= 0.4 else "routine_monitoring",
                "data_source_classification": "anonymised_aggregated_sample",
            })
            dss_rows.append({
                "recommendation_date": bucket_date,
                "parcel_group_id": parcel["parcel_group_id"],
                "crop_code": parcel["crop_code"],
                "recommendation_type": "irrigation" if irrigation_required else "monitoring",
                "water_stress_index": stress,
                "pest_disease_risk_index": pest_risk,
                "nitrogen_recommendation_kg_ha": round1(clamp(35 + idx * 9 + rng.uniform(-5, 7), 0, 120)),
                "confidence": "normal" if day % 9 != 0 else "reduced",
                "data_source_classification": "anonymised_aggregated_sample",
            })
            kpi_rows.append({
                "bucket_date": bucket_date,
                "parcel_group_id": parcel["parcel_group_id"],
                "crop_code": parcel["crop_code"],
                "kpi_name": "water_stress_index",
                "kpi_value": stress,
                "unit": "index_0_1",
                "data_source_classification": "anonymised_aggregated_sample",
            })

        energy_rows.append({
            "bucket_date": weather["bucket_date"],
            "energy_infrastructure_group_id": "pv_meter_group_01",
            "pump_group_id": "irrigation_pump_group_01",
            "pv_energy_kwh": round1(clamp(float(weather["solar_radiation_daily_mj_m2"]) * rng.uniform(6.5, 10.5), 0, 260)),
            "pump_energy_kwh": round1(sum(float(row["irrigation_flow_m3_h_avg"]) for row in irrigation_rows[-len(PARCELS):]) * rng.uniform(0.18, 0.36)),
            "pump_energy_anomaly_flag": "review" if day in {12, 13} else "normal",
            "data_source_classification": "anonymised_aggregated_sample",
        })

    write_csv(out / "aggregated_weather_daily.csv", weather_rows, list(weather_rows[0].keys()))
    write_csv(out / "aggregated_sensor_daily.csv", sensor_rows, list(sensor_rows[0].keys()))
    write_csv(out / "aggregated_irrigation_daily.csv", irrigation_rows, list(irrigation_rows[0].keys()))
    write_csv(out / "aggregated_energy_daily.csv", energy_rows, list(energy_rows[0].keys()))
    write_csv(out / "aggregated_pest_disease_daily.csv", pest_rows, list(pest_rows[0].keys()))
    write_csv(out / "aggregated_dss_recommendations.csv", dss_rows, list(dss_rows[0].keys()))
    write_csv(out / "aggregated_kpis.csv", kpi_rows, list(kpi_rows[0].keys()))

    manifest = {
        "dataset_name": "AgriTwin anonymised aggregated sample dataset",
        "version": "1.0.0",
        "seed": seed,
        "days": days,
        "site_metadata": SITE_METADATA,
        "data_source_classification": "anonymised_aggregated_sample",
        "privacy_transformations": PRIVACY_TRANSFORMATIONS,
        "excluded_materials": [
            "raw_pilot_farm_data",
            "exact_private_coordinates",
            "credentials",
            "site_specific_configuration",
            "non_anonymised_operational_logs",
        ],
        "files": {
            "aggregated_weather_daily.csv": len(weather_rows),
            "aggregated_sensor_daily.csv": len(sensor_rows),
            "aggregated_irrigation_daily.csv": len(irrigation_rows),
            "aggregated_energy_daily.csv": len(energy_rows),
            "aggregated_pest_disease_daily.csv": len(pest_rows),
            "aggregated_dss_recommendations.csv": len(dss_rows),
            "aggregated_kpis.csv": len(kpi_rows),
        },
    }
    write_json(out / "dataset_manifest.json", manifest)
    return manifest["files"]


def generate_synthetic(root: Path, days: int, seed: int) -> dict[str, int]:
    rng = random.Random(seed + 7)
    out = root / "synthetic_developer"
    intervals = days * 96
    start = datetime(2026, 6, 1, tzinfo=timezone.utc)
    weather_rows: list[dict[str, Any]] = []
    sensor_rows: list[dict[str, Any]] = []
    energy_rows: list[dict[str, Any]] = []
    irrigation_rows: list[dict[str, Any]] = []
    eo_rows: list[dict[str, Any]] = []
    activity_rows: list[dict[str, Any]] = []

    for i in range(intervals):
        ts = start + timedelta(minutes=15 * i)
        hour = ts.hour + ts.minute / 60
        daylight = max(0.0, math.sin((hour - 6) / 14 * math.pi))
        rainfall = 0.0
        if 120 <= i <= 128:
            rainfall = round1(rng.uniform(0.4, 2.6))
        temp = round1(clamp(17 + daylight * 12 + math.sin(i / 80) * 2 + rng.uniform(-0.9, 0.9), 8, 34))
        humidity = int(clamp(72 - daylight * 32 + rainfall * 8 + rng.uniform(-5, 5), 25, 90))
        wind = round1(clamp(6 + daylight * 12 + rng.uniform(-2, 6), 0, 45))
        solar = int(clamp(daylight * 900 + rng.uniform(-30, 30), 0, 950))
        weather_rows.append({
            "observed_at": ts.isoformat().replace("+00:00", "Z"),
            **SITE_METADATA,
            "air_temperature_c": temp,
            "relative_humidity_pct": humidity,
            "rainfall_mm": rainfall,
            "wind_speed_kmh": wind,
            "solar_radiation_w_m2": solar,
            "data_source_classification": "synthetic_developer_sample",
        })
        for sidx, station in enumerate(SENSOR_STATIONS):
            parcel = PARCELS[sidx % len(PARCELS)]
            event_drop = 5 if 170 <= i <= 210 and sidx == 1 else 0
            soil = round1(clamp(28 - daylight * 4 + rainfall * 1.2 - event_drop + rng.uniform(-1, 1), 10, 38))
            sensor_rows.append({
                "record_type": "sensor_reading",
                "observed_at": ts.isoformat().replace("+00:00", "Z"),
                "farm_id": "example_farm_001",
                "parcel_id": parcel["parcel_group_id"],
                "station_id": station,
                "asset_id": f"{station}_probe_01",
                "crop_code": parcel["crop_code"],
                "soil_moisture_vwc_pct": soil,
                "soil_temperature_c": round1(clamp(temp - rng.uniform(1.0, 4.0), 6, 28)),
                "air_temperature_c": temp,
                "relative_humidity_pct": humidity,
                "leaf_wetness_pct": int(clamp(8 + rainfall * 20 + (humidity - 60) * 0.8, 0, 100)),
                "water_stress_index": round(clamp((24 - soil) / 18, 0, 1), 2),
                "coordinate_type": "synthetic",
                "coordinate_precision": "coarse",
                "data_source_classification": "synthetic_developer_sample",
            })
        pump_flow = round1(28.0 if i in {86, 87, 88, 184, 185, 186, 187} else 0.0)
        if pump_flow > 0:
            irrigation_rows.append({
                "event_id": f"synthetic_irrigation_event_{len(irrigation_rows)+1:03d}",
                "started_at": ts.isoformat().replace("+00:00", "Z"),
                "ended_at": (ts + timedelta(minutes=45)).isoformat().replace("+00:00", "Z"),
                "parcel_group_id": PARCELS[len(irrigation_rows) % len(PARCELS)]["parcel_group_id"],
                "irrigation_infrastructure_group_id": "irrigation_pump_group_01",
                "irrigation_flow_m3_h": pump_flow,
                "estimated_volume_m3": round1(pump_flow * 0.75),
                "scenario_label": "normal_irrigation_day" if len(irrigation_rows) < 3 else "low_soil_moisture_event",
                "data_source_classification": "synthetic_developer_sample",
            })
        anomaly = 1.35 if 184 <= i <= 187 else 1.0
        energy_rows.append({
            "observed_at": ts.isoformat().replace("+00:00", "Z"),
            "pv_meter_group_id": "pv_meter_group_01",
            "pump_group_id": "irrigation_pump_group_01",
            "pv_power_kw": round1(daylight * rng.uniform(35, 82)),
            "pump_energy_kwh": round1((pump_flow / 40) * rng.uniform(4.5, 8.5) * anomaly),
            "pump_energy_anomaly_flag": "review" if anomaly > 1 else "normal",
            "data_source_classification": "synthetic_developer_sample",
        })

    for day in range(0, days, 5):
        observed = (start + timedelta(days=day)).date().isoformat()
        for parcel in PARCELS:
            eo_rows.append({
                "observed_date": observed,
                "parcel_group_id": parcel["parcel_group_id"],
                "crop_code": parcel["crop_code"],
                "ndvi_mean": round(clamp(rng.uniform(0.42, 0.82), 0, 1), 3),
                "lai_savi_mean": round(clamp(rng.uniform(0.28, 0.74), 0, 1), 3),
                "cloud_cover_pct": round1(rng.uniform(0, 35)),
                "data_source_classification": "synthetic_developer_sample",
            })

    activity_templates = [
        ("2026-06-01T07:15:00Z", "field_inspection", "Normal irrigation day pre-check"),
        ("2026-06-02T08:00:00Z", "sensor_check", "Low soil-moisture event review"),
        ("2026-06-02T17:30:00Z", "irrigation_pressure_check", "Pump pressure verified"),
        ("2026-06-03T08:10:00Z", "pest_monitoring", "Humidity and leaf wetness increased risk"),
        ("2026-06-04T09:00:00Z", "energy_review", "Pump energy anomaly review"),
        ("2026-06-05T11:00:00Z", "service_recovery", "OpenAgri synchronisation recovered"),
    ]
    for idx, (ts, activity, note) in enumerate(activity_templates, start=1):
        activity_rows.append({
            "activity_id": f"synthetic_activity_{idx:03d}",
            "activity_at": ts,
            "parcel_group_id": PARCELS[idx % len(PARCELS)]["parcel_group_id"],
            "activity_type": activity,
            "operator_role": "farm_manager" if idx % 2 else "technical_manager",
            "note": note,
            "data_source_classification": "synthetic_developer_sample",
        })

    recommendations = {
        "dataset_name": "Synthetic DSS recommendations",
        "generated_at": "2026-06-05T12:00:00Z",
        "data_source_classification": "synthetic_developer_sample",
        "recommendations": [
            {
                "recommendation_id": "synthetic_rec_001",
                "record_type": "recommendation",
                "parcel_group_id": "parcel_group_002",
                "recommendation_type": "irrigation",
                "water_stress_index": 0.68,
                "nitrogen_recommendation_kg_ha": 48.0,
                "confidence": "normal",
                "reason": "Soil moisture below preferred range after a warm windy interval.",
            },
            {
                "recommendation_id": "synthetic_rec_002",
                "record_type": "recommendation",
                "parcel_group_id": "parcel_group_001",
                "recommendation_type": "pest_monitoring",
                "pest_disease_risk_index": 0.62,
                "confidence": "reduced",
                "reason": "Rainfall, humidity and leaf wetness increased monitoring priority.",
            },
        ],
    }
    twin = {
        "record_type": "digital_twin_telemetry_summary",
        "generated_at": "2026-06-05T12:00:00Z",
        "site_label": "synthetic_developer_site",
        "coordinate_type": "synthetic",
        "coordinate_precision": "coarse",
        "entities": [
            {"entity_id": "parcel_group_001", "entity_type": "parcel", "status": "configured"},
            {"entity_id": "sensor_station_A", "entity_type": "sensor_station", "status": "active"},
            {"entity_id": "irrigation_pump_group_01", "entity_type": "irrigation_asset", "status": "review"},
            {"entity_id": "pv_meter_group_01", "entity_type": "energy_asset", "status": "active"},
        ],
        "telemetry_freshness_minutes": 15,
        "data_source_classification": "synthetic_developer_sample",
    }
    service_status = {
        "record_type": "openagri_service_status",
        "generated_at": "2026-06-05T12:00:00Z",
        "services": [
            {"service": "Gatekeeper", "status": "available", "latency_ms": 36},
            {"service": "Weather Data Service", "status": "available", "latency_ms": 54},
            {"service": "Irrigation Management Service", "status": "available", "latency_ms": 61},
            {"service": "Pest and Disease Management Service", "status": "unavailable", "latency_ms": None, "scenario_label": "OpenAgri service unavailable event"},
            {"service": "Reporting Service", "status": "available", "latency_ms": 72, "scenario_label": "recovery_synchronisation_event"},
        ],
        "data_source_classification": "synthetic_developer_sample",
    }

    write_csv(out / "synthetic_weather_timeseries.csv", weather_rows, list(weather_rows[0].keys()))
    write_csv(out / "synthetic_sensor_timeseries.csv", sensor_rows, list(sensor_rows[0].keys()))
    write_csv(out / "synthetic_irrigation_events.csv", irrigation_rows, list(irrigation_rows[0].keys()))
    write_csv(out / "synthetic_energy_timeseries.csv", energy_rows, list(energy_rows[0].keys()))
    write_csv(out / "synthetic_eo_indices.csv", eo_rows, list(eo_rows[0].keys()))
    write_csv(out / "synthetic_farm_activity_log.csv", activity_rows, list(activity_rows[0].keys()))
    write_json(out / "synthetic_dss_recommendations.json", recommendations)
    write_json(out / "synthetic_digital_twin_telemetry.json", twin)
    write_json(out / "synthetic_openagri_service_status.json", service_status)

    manifest_files = {
        "synthetic_weather_timeseries.csv": len(weather_rows),
        "synthetic_sensor_timeseries.csv": len(sensor_rows),
        "synthetic_irrigation_events.csv": len(irrigation_rows),
        "synthetic_energy_timeseries.csv": len(energy_rows),
        "synthetic_eo_indices.csv": len(eo_rows),
        "synthetic_farm_activity_log.csv": len(activity_rows),
        "synthetic_dss_recommendations.json": len(recommendations["recommendations"]),
        "synthetic_digital_twin_telemetry.json": len(twin["entities"]),
        "synthetic_openagri_service_status.json": len(service_status["services"]),
    }
    write_json(out / "dataset_manifest.json", {
        "dataset_name": "AgriTwin synthetic developer dataset",
        "version": "1.0.0",
        "seed": seed + 7,
        "days": days,
        "site_metadata": {**SITE_METADATA, "site_label": "synthetic_developer_site", "coordinate_type": "synthetic"},
        "scenarios": [
            "normal_irrigation_day",
            "low_soil_moisture_event",
            "rainfall_event",
            "pest_risk_increase_due_to_humidity_leaf_wetness",
            "pump_energy_anomaly",
            "OpenAgri_service_unavailable_event",
            "recovery_synchronisation_event",
        ],
        "data_source_classification": "synthetic_developer_sample",
        "files": manifest_files,
    })
    return manifest_files


FIELD_DEFINITIONS = [
    ("record_type", "string", "required", "", "Record family, e.g. sensor_reading or recommendation", ""),
    ("observed_at", "datetime", "required", "", "ISO-8601 observation timestamp", ""),
    ("generated_at", "datetime", "optional", "", "ISO-8601 generation timestamp", ""),
    ("bucket_date", "date", "required_for_aggregated", "", "Daily aggregation date", ""),
    ("bucket_start", "datetime", "optional", "", "Aggregation bucket start", ""),
    ("bucket_end", "datetime", "optional", "", "Aggregation bucket end", ""),
    ("parcel_group_id", "string", "required", "", "Generalised parcel group identifier", "parcel_group_001|parcel_group_002|parcel_group_003"),
    ("sensor_station_group_id", "string", "optional", "", "Generalised sensor station group", "sensor_station_A|sensor_station_B|sensor_station_C|sensor_station_D"),
    ("station_id", "string", "optional", "", "Synthetic station identifier", ""),
    ("crop_code", "enum", "required", "", "Crop code", "almond|pistachio|maize"),
    ("air_temperature_c", "number", "optional", "°C", "Air temperature", "8..34"),
    ("air_temperature_min_c", "number", "optional", "°C", "Daily minimum air temperature", "6..34"),
    ("air_temperature_avg_c", "number", "optional", "°C", "Daily average air temperature", "8..34"),
    ("air_temperature_c_avg", "number", "optional", "°C", "Daily average air temperature", "8..34"),
    ("air_temperature_max_c", "number", "optional", "°C", "Daily maximum air temperature", "8..34"),
    ("relative_humidity_pct", "number", "optional", "%", "Relative humidity", "25..90"),
    ("relative_humidity_avg_pct", "number", "optional", "%", "Daily average relative humidity", "25..90"),
    ("relative_humidity_pct_avg", "number", "optional", "%", "Daily average relative humidity", "25..90"),
    ("soil_moisture_vwc_pct", "number", "optional", "% VWC", "Volumetric water content", "10..38"),
    ("soil_moisture_vwc_pct_avg", "number", "optional", "% VWC", "Average volumetric water content", "10..38"),
    ("soil_moisture_vwc_pct_min", "number", "optional", "% VWC", "Minimum volumetric water content", "10..38"),
    ("soil_temperature_c", "number", "optional", "°C", "Soil temperature", "6..28"),
    ("soil_temperature_c_avg", "number", "optional", "°C", "Daily average soil temperature", "6..28"),
    ("leaf_wetness_pct", "number", "optional", "%", "Leaf wetness indicator", "0..100"),
    ("leaf_wetness_pct_avg", "number", "optional", "%", "Average leaf wetness indicator", "0..100"),
    ("rainfall_mm", "number", "optional", "mm", "Rainfall depth", "0..25 daily"),
    ("wind_speed_kmh", "number", "optional", "km/h", "Wind speed", "0..45"),
    ("wind_speed_avg_kmh", "number", "optional", "km/h", "Average wind speed", "0..45"),
    ("solar_radiation_w_m2", "number", "optional", "W/m²", "Solar radiation", "0..950"),
    ("solar_radiation_daily_mj_m2", "number", "optional", "MJ/m²", "Daily solar radiation", "0..30"),
    ("irrigation_flow_m3_h", "number", "optional", "m³/h", "Irrigation flow", "0..40"),
    ("irrigation_flow_m3_h_avg", "number", "optional", "m³/h", "Average irrigation flow", "0..40"),
    ("pump_energy_kwh", "number", "optional", "kWh", "Pump energy", "0..20 per 15 min synthetic interval"),
    ("pv_power_kw", "number", "optional", "kW", "PV power", "0..90"),
    ("water_stress_index", "number", "optional", "index_0_1", "Water stress index", "0..1"),
    ("water_stress_index_avg", "number", "optional", "index_0_1", "Average water stress index", "0..1"),
    ("pest_disease_risk_index", "number", "optional", "index_0_1", "Pest and disease risk index", "0..1"),
    ("nitrogen_recommendation_kg_ha", "number", "optional", "kg/ha", "DSS nitrogen recommendation", "0..120"),
    ("data_source_classification", "enum", "required", "", "Sample data class", "|".join(DATA_SOURCE_CLASSIFICATIONS)),
]


def generate_schema_only(root: Path) -> dict[str, int]:
    out = root / "schema_only"
    units = [
        ("air_temperature_c", "°C", "Air temperature"),
        ("relative_humidity_pct", "%", "Relative humidity"),
        ("soil_moisture_vwc_pct", "% VWC", "Volumetric water content"),
        ("soil_temperature_c", "°C", "Soil temperature"),
        ("leaf_wetness_pct", "%", "Leaf wetness"),
        ("rainfall_mm", "mm", "Rainfall"),
        ("wind_speed_kmh", "km/h", "Wind speed"),
        ("solar_radiation_w_m2", "W/m²", "Solar radiation"),
        ("irrigation_flow_m3_h", "m³/h", "Irrigation flow"),
        ("pump_energy_kwh", "kWh", "Pump energy"),
        ("pv_power_kw", "kW", "PV power"),
        ("water_stress_index", "index_0_1", "Water stress"),
        ("pest_disease_risk_index", "index_0_1", "Pest and disease risk"),
        ("nitrogen_recommendation_kg_ha", "kg/ha", "Nitrogen recommendation"),
    ]
    write_csv(out / "field_definitions.csv", [
        {
            "field_name": name,
            "type": dtype,
            "required": required,
            "unit": unit,
            "description": description,
            "allowed_values_or_range": allowed,
        }
        for name, dtype, required, unit, description, allowed in FIELD_DEFINITIONS
    ], ["field_name", "type", "required", "unit", "description", "allowed_values_or_range"])
    write_csv(out / "units.csv", [
        {"field_name": field, "unit": unit, "description": desc} for field, unit, desc in units
    ], ["field_name", "unit", "description"])

    schema = {
        "$schema": "https://json-schema.org/draft/2020-12/schema",
        "$id": "https://example.invalid/agritwin/schema/agritwin-sample-record.schema.json",
        "title": "AgriTwin Sample Dataset Record",
        "type": "object",
        "required": ["data_source_classification"],
        "properties": {
            "record_type": {"type": "string"},
            "observed_at": {"type": "string", "format": "date-time"},
            "generated_at": {"type": "string", "format": "date-time"},
            "bucket_date": {"type": "string", "format": "date"},
            "parcel_group_id": {"type": "string", "enum": [p["parcel_group_id"] for p in PARCELS]},
            "crop_code": {"type": "string", "enum": ["almond", "pistachio", "maize"]},
            "air_temperature_c": {"type": "number", "minimum": 8, "maximum": 34},
            "relative_humidity_pct": {"type": "number", "minimum": 25, "maximum": 90},
            "soil_moisture_vwc_pct": {"type": "number", "minimum": 10, "maximum": 38},
            "soil_temperature_c": {"type": "number", "minimum": 6, "maximum": 28},
            "leaf_wetness_pct": {"type": "number", "minimum": 0, "maximum": 100},
            "rainfall_mm": {"type": "number", "minimum": 0, "maximum": 25},
            "wind_speed_kmh": {"type": "number", "minimum": 0, "maximum": 45},
            "solar_radiation_w_m2": {"type": "number", "minimum": 0, "maximum": 950},
            "irrigation_flow_m3_h": {"type": "number", "minimum": 0, "maximum": 40},
            "pump_energy_kwh": {"type": "number", "minimum": 0, "maximum": 260},
            "pv_power_kw": {"type": "number", "minimum": 0, "maximum": 90},
            "water_stress_index": {"type": "number", "minimum": 0, "maximum": 1},
            "pest_disease_risk_index": {"type": "number", "minimum": 0, "maximum": 1},
            "nitrogen_recommendation_kg_ha": {"type": "number", "minimum": 0, "maximum": 120},
            "data_source_classification": {"type": "string", "enum": DATA_SOURCE_CLASSIFICATIONS},
        },
        "additionalProperties": True,
    }
    validation_rules = {
        "timestamp_conventions": {
            "timezone": "UTC for generated examples; operational deployments may store local timezone with explicit offset.",
            "iso_8601_required_for": ["observed_at", "generated_at", "started_at", "ended_at"],
            "aggregated_bucket_fields": ["bucket_date", "bucket_start", "bucket_end"],
        },
        "allowed_crop_codes": ["almond", "pistachio", "maize"],
        "allowed_location_precision_values": ["regional_or_grid_cell", "coarse", "none"],
        "distribution_constraints": [
            "No exact private coordinates",
            "No private parcel identifiers",
            "No private infrastructure identifiers",
            "No credentials or API keys",
            "No non-anonymised operational logs",
        ],
        "numeric_ranges": {k: v for k, v in schema["properties"].items() if "minimum" in v},
    }
    examples = {
        "example_weather_record.json": {
            "record_type": "weather_observation",
            "observed_at": "2026-06-01T12:00:00Z",
            **SITE_METADATA,
            "air_temperature_c": 25.4,
            "relative_humidity_pct": 44,
            "rainfall_mm": 0.0,
            "wind_speed_kmh": 14.2,
            "solar_radiation_w_m2": 780,
            "data_source_classification": "schema_only_example",
        },
        "example_sensor_record.json": {
            "record_type": "sensor_reading",
            "observed_at": "2026-06-01T12:15:00Z",
            "farm_id": "example_farm_001",
            "parcel_id": "parcel_group_001",
            "station_id": "sensor_station_A",
            "soil_moisture_vwc_pct": 24.6,
            "soil_temperature_c": 20.4,
            "air_temperature_c": 25.1,
            "relative_humidity_pct": 46,
            "leaf_wetness_pct": 12,
            "data_source_classification": "schema_only_example",
        },
        "example_irrigation_record.json": {
            "record_type": "irrigation_event",
            "started_at": "2026-06-01T18:00:00Z",
            "ended_at": "2026-06-01T18:45:00Z",
            "parcel_group_id": "parcel_group_001",
            "irrigation_flow_m3_h": 28.0,
            "data_source_classification": "schema_only_example",
        },
        "example_energy_record.json": {
            "record_type": "energy_reading",
            "observed_at": "2026-06-01T12:00:00Z",
            "pv_meter_group_id": "pv_meter_group_01",
            "pv_power_kw": 62.0,
            "pump_energy_kwh": 0.0,
            "data_source_classification": "schema_only_example",
        },
        "example_eo_record.json": {
            "record_type": "eo_vegetation_index_summary",
            "observed_date": "2026-06-01",
            "parcel_group_id": "parcel_group_001",
            "ndvi_mean": 0.66,
            "lai_savi_mean": 0.51,
            "cloud_cover_pct": 9.0,
            "data_source_classification": "schema_only_example",
        },
        "example_activity_record.json": {
            "record_type": "farmer_activity",
            "activity_at": "2026-06-01T07:15:00Z",
            "activity_type": "field_inspection",
            "operator_role": "farm_manager",
            "data_source_classification": "schema_only_example",
        },
        "example_dss_recommendation.json": {
            "record_type": "recommendation",
            "generated_at": "2026-06-01T13:00:00Z",
            "parcel_group_id": "parcel_group_001",
            "recommendation_type": "irrigation",
            "water_stress_index": 0.42,
            "nitrogen_recommendation_kg_ha": 52.0,
            "confidence": "normal",
            "data_source_classification": "schema_only_example",
        },
        "example_digital_twin_telemetry.json": {
            "record_type": "digital_twin_telemetry_summary",
            "generated_at": "2026-06-01T13:00:00Z",
            "entity_id": "sensor_station_A",
            "entity_type": "sensor_station",
            "status": "active",
            "coordinate_type": "synthetic",
            "coordinate_precision": "coarse",
            "data_source_classification": "schema_only_example",
        },
        "example_openagri_service_status.json": {
            "record_type": "openagri_service_status",
            "generated_at": "2026-06-01T13:00:00Z",
            "service": "Weather Data Service",
            "status": "available",
            "latency_ms": 54,
            "data_source_classification": "schema_only_example",
        },
        "example_kpi_record.json": {
            "record_type": "kpi_evaluation",
            "generated_at": "2026-06-01T13:00:00Z",
            "kpi_name": "irrigation_schedule_latency",
            "kpi_value": 42.0,
            "unit": "seconds",
            "data_source_classification": "schema_only_example",
        },
    }
    write_json(out / "schema.json", schema)
    write_json(out / "validation_rules.json", validation_rules)
    for filename, payload in examples.items():
        write_json(out / filename, payload)
    example_rows = []
    for filename, payload in examples.items():
        row = {"example_file": filename, "record_type": payload["record_type"], "data_source_classification": payload["data_source_classification"]}
        example_rows.append(row)
    write_csv(out / "example_records.csv", example_rows, ["example_file", "record_type", "data_source_classification"])
    return {
        "field_definitions.csv": len(FIELD_DEFINITIONS),
        "units.csv": len(units),
        "example_records.csv": len(example_rows),
        "json_schema_files": 12,
    }


DOCS: dict[str, str] = {
    "README.md": """# AgriTwin Sample Dataset Package

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
""",
    "DATASET_CARD.md": """# Dataset Card

## Name

AgriTwin safe sample dataset package.

## Purpose

Support AgriTwin DSS, sensor ingestion, analytics, reporting and Digital Twin development without publishing private operational data.

## Geographic Context

The sample is compatible with a Salamanca / Castilla y León agricultural context using only regional labels and coarse or synthetic location metadata.

## Privacy Level

The anonymised package uses daily buckets, generalised identifiers, aggregated values and no exact private coordinates. The synthetic package is generated from deterministic formulas and does not derive from operational logs.

## Licence

Reusable schema, documentation and generation scripts are released under Apache License 2.0. See the repository `LICENSE` and `NOTICE` files.
""",
    "PRIVACY_AND_ANONYMISATION.md": """# Privacy And Anonymisation

The committed dataset package applies:

- location generalisation to regional or coarse grid-cell metadata;
- parcel identifier generalisation to `parcel_group_001` style IDs;
- infrastructure identifier generalisation to generic station, pump and meter groups;
- timestamp bucketing for aggregated records;
- deterministic synthetic generation for developer records;
- exclusion of credentials, raw logs, exact private parcel geometry and site-specific thresholds.

The package must not be used to infer any real deployment layout or operational history.
""",
    "SCHEMA_GUIDE.md": """# Schema Guide

The schema package uses AgriTwin-compatible names such as `observed_at`, `generated_at`, `parcel_group_id`, `station_id`, `soil_moisture_vwc_pct`, `irrigation_flow_m3_h`, `pump_energy_kwh`, `water_stress_index`, `pest_disease_risk_index` and `nitrogen_recommendation_kg_ha`.

Timestamps are ISO-8601. Aggregated files use `bucket_date` and may use `bucket_start` / `bucket_end` in downstream extensions. Synthetic records use generated ISO-8601 timestamps and are marked with `synthetic_developer_sample`.

Units are documented in `schema_only/units.csv`.
""",
    "DEVELOPER_USAGE.md": """# Developer Usage

Use `synthetic_developer/` for repeatable tests of sensor ingestion, DSS recommendations, reports and OpenAgri adapter handling. Use `anonymised_aggregated/` for dashboard, analytics and KPI tests that need daily or parcel-level summaries.

The data is deterministic. Change `--seed` only when intentionally creating a new sample fixture version.

No external API calls or secrets are required.
""",
    "VALIDATION_GUIDE.md": """# Validation Guide

Run the validator from repository root:

```bash
python3 datasets/agritwin_sample_dataset/validation/validate_dataset.py
```

The validator checks required files, JSON syntax, CSV headers, numeric ranges, timestamp formats, forbidden private strings and common credential patterns. It exits non-zero if unsafe content is detected.
""",
    "DISTRIBUTION_NOTES.md": """# Distribution Notes

This dataset package is safe for the `distribution-clean` branch. It intentionally excludes private farm names, raw telemetry, exact coordinates, private parcel boundaries, generated reports, credentials and operational logs.

Distribution deployments should replace these samples with their own authorised data sources through local configuration, API integrations or database imports that remain outside Git.
""",
}


def generate_docs(root: Path) -> dict[str, int]:
    docs_dir = root / "docs"
    for filename, content in DOCS.items():
        (docs_dir / filename).parent.mkdir(parents=True, exist_ok=True)
        (docs_dir / filename).write_text(content, encoding="utf-8")
    return {filename: 1 for filename in DOCS}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--days", type=int, default=DEFAULT_DAYS, help="Number of days to generate")
    parser.add_argument("--seed", type=int, default=DEFAULT_SEED, help="Deterministic random seed")
    parser.add_argument("--output-dir", default=str(Path(__file__).resolve().parents[1]), help="Dataset package root")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    root = Path(args.output_dir).resolve()
    counts = {
        "anonymised_aggregated": generate_anonymised_aggregated(root, args.days, args.seed),
        "synthetic_developer": generate_synthetic(root, args.days, args.seed),
        "schema_only": generate_schema_only(root),
        "docs": generate_docs(root),
    }
    write_json(root / "dataset_package_manifest.json", {
        "dataset_name": "AgriTwin safe sample dataset package",
        "version": "1.0.0",
        "generated_by": "scripts/generate_sample_dataset.py",
        "seed": args.seed,
        "days": args.days,
        "packages": counts,
        "privacy_statement": "No raw pilot-farm data, credentials, exact private coordinates or non-anonymised operational logs are included.",
    })
    print(json.dumps(counts, indent=2, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
