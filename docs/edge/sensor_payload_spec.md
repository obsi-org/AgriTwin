# AgriTwin Edge Sensor Payload Specification

This specification defines the mixed edge-cloud ADS sensor packet accepted by the local edge gateway module. Examples are synthetic and contain no private pilot-farm data.

## Packet Envelope

Required fields:

- `gateway_id`: stable edge gateway id.
- `packet_id`: gateway-local unique packet id.
- `farm_id`: pseudonymised farm id.
- `observed_at`: ISO-8601 timestamp for the sampling window.
- `readings`: non-empty array of sensor readings.

Optional fields:

- `parcel_id`: pseudonymised parcel/block id.
- `sampling_interval_minutes`: defaults to `15`.

The edge gateway creates an idempotency key as `gateway_id:packet_id` and queues the packet locally before synchronising with the cloud DSS.

## Supported Parameters

| Parameter | Unit | Notes |
| --- | --- | --- |
| `soil_moisture` | `m3_m3` | Volumetric water content from probe. |
| `soil_temperature` | `degC` | Soil temperature near probe depth. |
| `air_temperature` | `degC` | Local air temperature. |
| `humidity` | `percent` | Relative humidity. |
| `leaf_wetness` | `percent` | Leaf wetness index or percent where available. |
| `pump_flow_l_min` | `l_min` | Pump flow if actuator telemetry exists. |
| `pump_power_kw` | `kW` | Pump power if measured. |
| `pump_energy_kwh` | `kWh` | Energy consumed for operation or sampling window. |

Unsupported parameter names are rejected so cloud-only and mixed ADS modes share the same canonical data model.

## Example Input Payload

```json
{
  "gateway_id": "edge-demo-001",
  "packet_id": "20260512T080000Z-001",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "observed_at": "2026-05-12T08:00:00Z",
  "sampling_interval_minutes": 15,
  "readings": [
    {
      "asset_id": "soil-probe-demo-01",
      "parameter": "soil_moisture",
      "value": 0.19,
      "quality": {
        "flag": "ok",
        "battery_voltage": 3.7,
        "rssi_dbm": -74
      }
    },
    {
      "asset_id": "soil-probe-demo-01",
      "parameter": "soil_temperature",
      "value": 19.6,
      "quality": {
        "flag": "ok"
      }
    },
    {
      "asset_id": "meteo-demo-01",
      "parameter": "air_temperature",
      "value": 31.0,
      "quality": {
        "flag": "ok"
      }
    },
    {
      "asset_id": "meteo-demo-01",
      "parameter": "humidity",
      "value": 78.0,
      "quality": {
        "flag": "ok"
      }
    },
    {
      "asset_id": "leaf-demo-01",
      "parameter": "leaf_wetness",
      "value": 65.0,
      "quality": {
        "flag": "ok"
      }
    },
    {
      "asset_id": "pump-demo-01",
      "parameter": "pump_energy_kwh",
      "value": 0.42,
      "quality": {
        "flag": "estimated"
      }
    }
  ]
}
```

## Canonical Normalised Packet

```json
{
  "record_type": "sensor_packet",
  "gateway_id": "edge-demo-001",
  "packet_id": "20260512T080000Z-001",
  "farm_id": "farm_demo_001",
  "parcel_id": "parcel_demo_almond_01",
  "architecture_mode": "mixed",
  "observed_at": "2026-05-12T08:00:00+00:00",
  "sampling_interval_minutes": 15,
  "sync": {
    "status": "queued",
    "attempts": 0,
    "idempotency_key": "edge-demo-001:20260512T080000Z-001"
  },
  "source": {
    "system": "edge_gateway",
    "external_id": "edge-demo-001:20260512T080000Z-001",
    "connector_version": "edge-gateway-0.1.0"
  }
}
```

Each item in `readings` is normalised to `record_type = sensor_reading` with canonical parameter names, unit, observed timestamp, quality metadata, and optional parcel/asset identifiers.

## Reduced-Confidence Recommendation Example

When OpenAgri weather, irrigation, or pest/disease cache entries are missing or stale, the edge gateway produces a local fallback recommendation:

```json
{
  "record_type": "recommendation",
  "domain": "mixed_edge_cloud_advisory",
  "architecture_mode": "mixed",
  "action": {
    "type": "irrigate",
    "value": 11,
    "unit": "minutes"
  },
  "water_balance": {
    "water_balance_mm": -3.75,
    "action": "irrigate",
    "reason": "soil_moisture_below_local_threshold"
  },
  "pest_risk": {
    "risk_index": 78.5,
    "risk_class": "high",
    "method": "degree_day_leaf_wetness_rule"
  },
  "confidence": {
    "mode": "reduced",
    "score": 0.52,
    "reasons": [
      "local_sensor_rule",
      "stale_or_missing_openagri_indicator"
    ]
  }
}
```

The fallback is intended to keep farm operations moving during 4G or OpenAgri outages. It does not replace the cloud DSS or agronomist-reviewed recommendations.
