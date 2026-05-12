# OpenAgri Integration Architecture

Source basis: `docs/source/3.-OpenAgri-Proposal-AgriTwin_20241202.pdf`, `docs/source/AgriTwin_D1_20260124.pdf`, and the OpenAgri references listed in `docs/audit/remote_access_notes.md`.

This document defines how AgriTwin should integrate OpenAgri services in local development and pilot deployment. Current local code does not yet implement these connectors; this is the target architecture and backlog basis.

## Integration Principles

- Gatekeeper is the primary authentication and service-discovery boundary.
- The Cloud DSS owns OpenAgri credentials and connector calls.
- The edge gateway receives cached and normalized outputs from the Cloud DSS by default.
- Direct edge-to-OpenAgri calls are allowed only as an explicit pilot configuration and must use scoped credentials.
- All OpenAgri responses are normalized into canonical AgriTwin records with source, timestamp, request parameters, cache status, and confidence.
- Cached responses must be visible in the UI with age and confidence mode.

## Gatekeeper Authentication and Service Discovery

Flow:

```text
Cloud DSS connector service
    -> Gatekeeper token request with client credentials
    -> service discovery / endpoint lookup
    -> scoped service call
    -> response normalization and cache write
```

Required configuration:

- `AGRITWIN_OPENAGRI_GATEKEEPER_URL`
- `AGRITWIN_OPENAGRI_GATEKEEPER_CLIENT_ID`
- `AGRITWIN_OPENAGRI_GATEKEEPER_CLIENT_SECRET`
- `AGRITWIN_OPENAGRI_GATEKEEPER_SCOPE`

Connector responsibilities:

- Request and refresh tokens server side.
- Store token expiry, not token bodies, in operational logs.
- Discover Weather, Irrigation, Pest & Disease, FarmCalendar, and Reporting endpoints.
- Validate TLS certificates.
- Add correlation IDs to all requests.
- Normalize errors into retryable, non-retryable, unauthorized, rate-limited, and degraded categories.

## Weather Service Integration

Purpose:

- Provide forecasts, precipitation accumulation, growing degree days, and weather context for irrigation and pest risk.

Inputs:

- Farm or parcel location.
- Crop type and growth stage when needed.
- Forecast horizon.
- Timezone.

Outputs to AgriTwin:

- `weather_observation`
- `weather_forecast`
- `weather_risk_context`

Usage:

- Advisory engine uses forecast precipitation and temperature to adjust irrigation and pest recommendations.
- KPI service records data availability and connector latency.
- Edge gateway receives cached weather summaries for reduced-confidence mode.

Fallback:

- Use most recent valid cache while `cache_age_hours <= configured_max_age`.
- If cache is stale, use local sensor weather readings and mark confidence as reduced.
- If no data exists, advisory engine must withhold weather-dependent recommendations or require agronomist confirmation.

## Irrigation Service Integration

Purpose:

- Generate optimized irrigation recommendations using soil moisture, precipitation, evapotranspiration, crop state, and parcel constraints.

Inputs:

- Canonical soil-moisture readings.
- Parcel and crop metadata.
- Weather forecast and precipitation.
- Satellite-derived evapotranspiration or vegetation indices where available.
- Pump/actuator constraints.

Outputs to AgriTwin:

- `irrigation_recommendation`
- `irrigation_schedule_candidate`
- `water_stress_context`

Usage:

- Advisory engine combines OpenAgri output with local DSS model output and farmer constraints.
- Digital Twin displays recommended irrigation zones and schedule state.
- FarmCalendar receives accepted irrigation schedules.

Fallback:

- Use cached irrigation guidance if still within freshness limits.
- Edge gateway uses local thresholds and last known schedule in reduced-confidence mode.
- Recommendations must expose `source_status = "cached"` or `source_status = "local_rule"` when not generated from live service data.

## Pest and Disease Management Integration

Purpose:

- Calculate pest and disease risk indices using weather, crop type, growth stage, location, and observations.

Inputs:

- Crop type and growth stage.
- Weather forecast and recent observations.
- Parcel location.
- Farmer/agronomist observations.
- EO vegetation stress indicators where relevant.

Outputs to AgriTwin:

- `pest_disease_risk`
- `pest_alert`
- `recommended_observation_task`

Usage:

- Advisory engine generates pest scouting, drone surveillance, or treatment advisories.
- FarmCalendar receives observation tasks.
- Digital Twin displays risk overlays by parcel.

Fallback:

- Use cached risk index and mark stale age.
- If local weather and observations indicate risk but service is unavailable, generate a reduced-confidence scouting alert rather than a treatment recommendation.

## FarmCalendar Integration

Purpose:

- Exchange farm operations, observations, parcels, and assets with the OpenAgri FarmCalendar ecosystem.

Record groups:

- Farm operations: irrigation, fertilisation, pest scouting, treatment, harvest, maintenance.
- Observations: soil, pest, disease, crop stage, weather, machinery notes.
- Parcels: canonical parcel identifiers, crop, area, geometry reference.
- Assets: sensors, actuators, pumps, drones, solar panels, gateways.

Integration behavior:

- Accepted recommendations create proposed FarmCalendar tasks.
- Completed FarmCalendar tasks sync back as activity evidence for DSS learning and KPI reporting.
- Parcel and asset records are referenced by stable IDs; private geometry is not published unless explicitly approved.
- Conflict handling uses `external_id`, `updated_at`, and `source_system`.

Fallback:

- Queue outbound FarmCalendar writes when unavailable.
- Continue local scheduling with `sync_status = "pending"`.
- Mark conflicts for human review if both AgriTwin and FarmCalendar update the same event offline.

## Reporting Service Integration

Purpose:

- Generate PDF advisory and validation reports for farmers, agronomists, and project evidence.

Inputs:

- Farm, parcel, date range.
- Recommendations and acknowledgements.
- Sensor/EO/weather summaries.
- KPI summaries.
- Advisory explanations and confidence.

Outputs:

- PDF advisory report.
- Machine-readable report metadata.
- Object storage pointer and checksum.

Fallback:

- If the OpenAgri Reporting Service is unavailable, AgriTwin may generate a local PDF using the same canonical data and mark `report.source = "agritwin-local"`.
- Report generation requests must be queued and retried if generated centrally.

## Cache and Connectivity Behavior

Cache classes:

- `live`: response generated during current request.
- `warm_cache`: response within normal freshness window.
- `stale_cache`: response outside freshness window but still useful for context.
- `local_rule`: edge or cloud fallback generated without external service response.
- `unavailable`: no usable data.

Connectivity rules:

- Cloud DSS is the cache authority for OpenAgri service data.
- Edge gateway stores only the subset needed for farm-local reduced-confidence recommendations.
- Every advisory must include source freshness and confidence mode.
- Queues must use idempotency keys to avoid duplicate farm activities, sensor readings, and reports after reconnect.

## Open-Source Publication Boundaries

Publishable:

- Connector interfaces and client code without credentials.
- Canonical schemas and synthetic examples.
- Docker/compose/Kubernetes templates without environment-specific values.
- Edge gateway source code and firmware templates without farm network secrets.
- Cesium widgets and Digital Twin UI code using synthetic/demo geometry.
- DSS pipeline code using synthetic or anonymized datasets.
- Test fixtures that cannot identify pilot farms or operational thresholds.

Must not be published:

- Raw pilot-farm data, operational logs, credentials, tokens, TLS certificates, farm network secrets.
- Private parcel boundaries or asset coordinates.
- Site-specific thresholds, irrigation strategy parameters, or commercially sensitive operational settings.
- Personal data, consent records, user identities, or identifiable report PDFs.
- Model artifacts trained on private data unless approved and privacy-reviewed.

