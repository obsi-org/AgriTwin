# AgriTwin Data Model

Source basis: `docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf` and `docs/source/AgriTwin_D1_20260124.pdf`.

This document defines the canonical records that should replace ad hoc page-specific fields in the current PHP prototype. JSON examples use synthetic identifiers and coordinates.

## Common Envelope

All event-like records should carry provenance, tenant/farm scope, timestamps, and sync state.

```json
{
  "record_id": "rec_01HZX7SYNTHETIC",
  "record_type": "sensor_reading",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "source": {
    "system": "edge_gateway",
    "external_id": "gw-demo-001:00001234",
    "connector_version": "0.1.0"
  },
  "observed_at": "2026-05-12T08:00:00Z",
  "received_at": "2026-05-12T08:00:05Z",
  "sync": {
    "status": "synced",
    "attempts": 1,
    "idempotency_key": "gw-demo-001-00001234"
  }
}
```

## Canonical Entities

| Entity | Purpose | Key fields |
| --- | --- | --- |
| `farm` | Farm tenant and operating context | `farm_id`, `display_name`, `country`, `timezone`, `pilot_phase` |
| `parcel` | Field/block unit for DSS and Digital Twin | `parcel_id`, `farm_id`, `crop`, `area_ha`, `geometry_ref`, `privacy_class` |
| `asset` | Sensor, actuator, pump, gateway, drone, energy asset | `asset_id`, `farm_id`, `asset_type`, `status`, `location_ref` |
| `sensor_reading` | Typed sensor telemetry | `asset_id`, `parameter`, `value`, `unit`, `quality` |
| `eo_observation` | EO/Copernicus derived metadata | `index`, `raster_ref`, `cloud_cover`, `processing_level` |
| `weather_record` | Weather observation/forecast | `provider`, `forecast_horizon_hours`, `variables` |
| `irrigation_event` | Planned, recommended, or completed irrigation | `event_status`, `water_volume_l`, `duration_minutes`, `method` |
| `farm_activity` | Farm operation or observation | `activity_type`, `actor_role`, `status`, `evidence_refs` |
| `recommendation` | DSS/OpenAgri advisory output | `domain`, `action`, `confidence`, `explanation`, `inputs` |
| `alert` | Time-sensitive state requiring attention | `severity`, `category`, `trigger`, `resolution_status` |
| `kpi_telemetry` | Operational and agronomic KPI evidence | `metric_name`, `metric_value`, `unit`, `window` |

## OpenAgri Response Mapping

All OpenAgri connector responses are normalized before entering DSS logic.

```json
{
  "openagri_response": {
    "service": "weather",
    "service_version": "unknown",
    "gatekeeper_subject": "agritwin-cloud-dss",
    "request": {
      "farm_id": "farm_demo_001",
      "lat": 40.8834,
      "lon": -5.2627,
      "horizon_hours": 48
    },
    "cache": {
      "class": "live",
      "stored_at": "2026-05-12T08:01:00Z",
      "expires_at": "2026-05-12T10:01:00Z"
    },
    "normalized_records": [
      {
        "record_type": "weather_record",
        "provider": "openagri_weather",
        "variables": {
          "precipitation_probability": { "value": 0.35, "unit": "ratio" },
          "temperature_air": { "value": 18.2, "unit": "degC" }
        }
      }
    ]
  }
}
```

Mapping rules:

- Preserve raw response only in private object storage if needed for audit.
- Store normalized values in canonical typed tables.
- Record provider, request parameters, response time, cache class, and connector version.
- Mark downstream recommendations with the OpenAgri service records they used.

## Sensor Payload Format

```json
{
  "gateway_id": "gw-demo-001",
  "batch_id": "batch-20260512-0800",
  "farm_id": "farm_demo_001",
  "readings": [
    {
      "asset_id": "sensor_soil_001",
      "parcel_id": "parcel_demo_almond_01",
      "parameter": "soil_moisture",
      "value": 0.31,
      "unit": "m3_m3",
      "observed_at": "2026-05-12T08:00:00Z",
      "quality": {
        "flag": "ok",
        "battery_voltage": 3.7,
        "rssi_dbm": -78
      }
    },
    {
      "asset_id": "sensor_air_001",
      "parcel_id": "parcel_demo_almond_01",
      "parameter": "temperature_air",
      "value": 18.2,
      "unit": "degC",
      "observed_at": "2026-05-12T08:00:00Z",
      "quality": { "flag": "ok" }
    }
  ]
}
```

## EO/Copernicus/NDVI/NDRE Metadata Format

```json
{
  "record_type": "eo_observation",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "provider": "copernicus",
  "platform": "sentinel-2",
  "product": "L2A",
  "index": "NDVI",
  "observed_at": "2026-05-11T10:47:00Z",
  "processing": {
    "algorithm_version": "agritwin-eo-0.1.0",
    "cloud_cover_ratio": 0.08,
    "spatial_resolution_m": 10
  },
  "summary": {
    "mean": 0.72,
    "min": 0.51,
    "max": 0.84,
    "p10": 0.60,
    "p90": 0.81
  },
  "raster_ref": {
    "object_uri": "s3://private-agritwin-demo/eo/ndvi-demo.tif",
    "checksum_sha256": "synthetic"
  }
}
```

NDRE records use the same structure with `"index": "NDRE"`.

## Irrigation Event Format

```json
{
  "record_type": "irrigation_event",
  "event_id": "irr_demo_20260512_01",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "event_status": "recommended",
  "method": "drip",
  "scheduled_start": "2026-05-12T18:00:00Z",
  "duration_minutes": 35,
  "estimated_water_volume_l": 4200,
  "source_recommendation_id": "rec_irrigation_001",
  "constraints": {
    "pump_asset_id": "pump_demo_001",
    "avoid_rain_window_hours": 24
  }
}
```

## Farm Activity Format

```json
{
  "record_type": "farm_activity",
  "activity_id": "act_demo_001",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "activity_type": "pest_scouting",
  "status": "planned",
  "planned_start": "2026-05-13T07:30:00Z",
  "actor_role": "technician",
  "source": {
    "system": "agritwin",
    "external_system": "farmcalendar",
    "external_id": "fc_demo_123"
  },
  "notes": "Inspect high-risk row segments from pest alert.",
  "evidence_refs": []
}
```

## Alert and Recommendation Format

```json
{
  "record_type": "recommendation",
  "recommendation_id": "rec_irrigation_001",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "domain": "irrigation",
  "action": {
    "type": "reduce_irrigation",
    "value": 15,
    "unit": "percent",
    "valid_until": "2026-05-13T08:00:00Z"
  },
  "confidence": {
    "score": 0.76,
    "mode": "normal",
    "reasons": ["recent_soil_moisture", "openagri_weather_forecast"]
  },
  "explanation": "Rain probability and current soil moisture allow a reduced irrigation cycle.",
  "inputs": [
    { "record_id": "weather_demo_001", "role": "forecast" },
    { "record_id": "sensor_demo_001", "role": "soil_moisture" }
  ],
  "status": "pending_acknowledgement"
}
```

```json
{
  "record_type": "alert",
  "alert_id": "alert_pest_001",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "severity": "warning",
  "category": "pest_risk",
  "trigger": {
    "source": "openagri_pest_disease",
    "risk_index": 0.68,
    "threshold": "site_private_not_published"
  },
  "recommended_activity_id": "act_demo_001",
  "resolution_status": "open"
}
```

## KPI Telemetry Format

```json
{
  "record_type": "kpi_telemetry",
  "metric_id": "kpi_demo_001",
  "farm_id": "farm_demo_001",
  "metric_name": "irrigation_schedule_latency_seconds",
  "metric_value": 42.5,
  "unit": "s",
  "window": {
    "start": "2026-05-12T08:00:00Z",
    "end": "2026-05-12T08:05:00Z"
  },
  "dimensions": {
    "ads_mode": "mixed_edge_cloud",
    "component": "advisory_service"
  },
  "provenance": {
    "source_events": ["rec_irrigation_001", "irr_demo_20260512_01"]
  }
}
```

Required KPI names:

- `irrigation_schedule_latency_seconds`
- `water_stress_forecast_mae`
- `nitrogen_recommendation_error`
- `data_continuity_ratio`
- `service_uptime_ratio`
- `network_volume_mb`
- `cpu_hours`
- `gpu_hours`
- `energy_consumption_wh`
- `interoperability_check_status`
- `switchability_evidence_status`

