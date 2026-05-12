# Pilot Deployment Runbook

This runbook converts the Proposal and D1 deployment intent into operational checklists for a FOTOMACO-style pilot. It is written for future deployment work; the current repository is not yet production-ready.

## Edge Gateway Deployment Checklist

- Confirm gateway hardware identity, asset ID, and physical location.
- Install supported OS image and security updates.
- Create non-root runtime user.
- Install edge gateway package and service unit.
- Configure farm ID, gateway ID, cloud DSS endpoint, and sync interval using environment or secret manager.
- Install scoped credentials; do not store tokens in source-controlled files.
- Enable local encrypted cache or filesystem encryption where supported.
- Confirm local time sync and timezone.
- Confirm sensor interface configuration.
- Confirm outbound queue persistence across reboot.
- Confirm reduced-confidence rule set and cache freshness limits.
- Confirm local logs exclude raw credentials and token values.
- Record initial gateway health metrics.

## Farm Network and 4G Checklist

- Verify farm Wi-Fi SSID and gateway access policy.
- Verify 4G signal strength at gateway location.
- Run connectivity test to Cloud DSS endpoint.
- Run DNS resolution test.
- Verify TLS certificate chain from gateway.
- Record expected outage windows and weak-signal areas.
- Confirm retry/backoff settings.
- Confirm gateway continues buffering with WAN disconnected.
- Confirm local dashboard access over farm Wi-Fi if enabled.
- Do not publish network names, passwords, SIM identifiers, or router administration details.

## Sensor Commissioning Checklist

- Assign each sensor a stable `asset_id`.
- Record sensor type, parcel association, installation date, and firmware version.
- Verify calibration status.
- Validate payload schema against `docs/architecture/data_model.md`.
- Confirm parameter units are canonical.
- Confirm timestamps are UTC or carry timezone offset.
- Confirm battery and signal quality are reported where available.
- Confirm sample reading reaches edge cache.
- Confirm sample reading syncs to Cloud DSS.
- Confirm sensor dropout alert fires when expected.

## Cloud DSS Checklist

- Provision application runtime and database.
- Provision time-series, geospatial, object, and model registry stores.
- Configure environment variables and secrets.
- Import initial non-sensitive farm, parcel, and asset metadata.
- Configure OpenAgri Gatekeeper and service endpoints.
- Configure EO/Copernicus ingestion credentials.
- Configure backup and retention policy.
- Configure role-based access for administrator, agronomist, technician, and viewer roles.
- Enable audit logging.
- Run health checks and smoke tests.
- Confirm report PDF storage is private by default.

## OpenAgri Service Connectivity Checklist

- Gatekeeper token request succeeds.
- Service discovery returns expected Weather, Irrigation, Pest & Disease, FarmCalendar, and Reporting endpoints.
- Weather test request returns normalized weather records.
- Irrigation test request returns normalized recommendation records.
- Pest & Disease test request returns normalized risk records.
- FarmCalendar test creates a synthetic non-pilot task and reads it back.
- Reporting test generates a synthetic PDF and stores private metadata.
- Cache write/read works for every service response.
- Simulated service outage triggers cached or reduced-confidence behavior.
- Connector logs contain correlation IDs but not tokens.

## Functional Acceptance Tests

- Login and role-restricted dashboard access works.
- Sensor batch is buffered at edge and synced to cloud.
- Cloud DSS stores typed sensor readings.
- Digital Twin renders farm overview, parcels, assets, sensor overlays, and EO layer metadata.
- Weather, irrigation, and pest service responses are visible in advisory provenance.
- Irrigation recommendation can be accepted and exported to FarmCalendar.
- Pest-risk alert can create a scouting activity.
- PDF advisory report can be generated from synthetic or approved data.
- Edge gateway continues operating during WAN outage.
- Reduced-confidence advisory appears when OpenAgri/cache data is stale.
- KPI telemetry records ingestion latency, recommendation latency, uptime, network volume, and compute/energy usage.

## Rollback Procedure

1. Stop new advisory publication in Cloud DSS.
2. Disable OpenAgri write operations and FarmCalendar task creation.
3. Keep edge gateway buffering enabled unless data corruption is suspected.
4. Revert Cloud DSS application to the previous tagged release.
5. Restore previous database schema or run down migrations if validated.
6. Reapply previous model artifact and edge configuration.
7. Restart services and run health checks.
8. Replay queued edge data only after idempotency checks pass.
9. Mark any recommendations generated during incident window as superseded or requiring agronomist review.
10. Record rollback cause, affected records, and follow-up action in audit log.

