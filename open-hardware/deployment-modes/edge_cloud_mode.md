# Edge + Cloud ADS Mode

Edge + cloud mode is suitable for rural deployments with intermittent connectivity, local operational needs or a requirement to preserve reduced-confidence guidance during outages.

## Architecture

```text
sensor node -> local gateway -> local queue/cache -> cloud sync -> DSS/dashboard
weather station -> local gateway -> OpenAgri/weather cache -> local checks -> cloud sync
```

## Edge Gateway Responsibilities

- Receive MQTT or HTTP telemetry from sensor nodes and weather stations.
- Validate payload structure and numeric ranges.
- Persist measurements in a local queue/cache.
- Cache recent weather/OpenAgri inputs for local continuity.
- Run lightweight rule checks or inference where configured.
- Synchronise queued data to cloud services when connectivity returns.
- Mark stale or locally inferred outputs as reduced confidence.

## Offline Behaviour

- Measurements remain queued locally with timestamps and source identifiers.
- Local dashboards or maintenance endpoints can show last known status.
- DSS outputs that depend on stale external inputs should be labelled reduced confidence.
- Sync worker backfills cloud storage after recovery.

## Recovery

1. Detect restored network connectivity.
2. Send queued payloads in timestamp order.
3. Reconcile duplicates with idempotent packet identifiers.
4. Refresh OpenAgri/weather cache.
5. Return status from reduced confidence to normal when data freshness is restored.

## Suitable Conditions

- Intermittent cellular coverage.
- High-value irrigation or crop-risk decisions requiring local continuity.
- Sites with many low-power local nodes.
- Deployments where local buffering reduces data loss.
