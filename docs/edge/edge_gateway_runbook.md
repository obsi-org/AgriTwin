# AgriTwin Edge Gateway Runbook

This runbook covers the local mixed edge-cloud ADS gateway path added for D1 alignment. The edge gateway buffers sensor packets locally, produces reduced-confidence fallback recommendations when OpenAgri/cloud inputs are stale, and synchronises packets to the cloud DSS after 4G recovery.

## Installation

1. Install PHP CLI with JSON and cURL support.
2. Copy `.env.example` to `.env` for local deployment and set gateway-specific values.
3. Ensure writable local storage exists:

```bash
mkdir -p var/edge/queue var/edge/cache
make test-edge
```

The module is loaded through `src/edge_gateway/autoload.php` and does not require Composer.

## Environment Variables

| Variable | Default | Purpose |
| --- | --- | --- |
| `AGRITWIN_EDGE_GATEWAY_ID` | `edge-demo-001` | Stable gateway identifier used in idempotency keys. |
| `AGRITWIN_EDGE_QUEUE_DIR` | `var/edge/queue` | Durable outbound queue for sensor packets and backfill events. |
| `AGRITWIN_EDGE_CACHE_DIR` | `var/edge/cache` | Last-known OpenAgri/weather/irrigation/pest responses. |
| `AGRITWIN_EDGE_SAMPLING_INTERVAL_MINUTES` | `15` | Expected sampling cadence for sensor packets. |
| `AGRITWIN_EDGE_TIMEOUT_SECONDS` | `10` | Cloud DSS request timeout. |
| `AGRITWIN_EDGE_MAX_RETRIES` | `3` | Retry attempts before an event is marked permanently failed. |
| `AGRITWIN_EDGE_BACKOFF_SECONDS` | `30` | Initial retry delay; retries use exponential backoff. |
| `AGRITWIN_EDGE_OPENAGRI_STALE_SECONDS` | `21600` | Staleness threshold for reduced-confidence fallback. |
| `AGRITWIN_EDGE_FORCE_OFFLINE` | `false` | Forces offline mode for testing or commissioning. |
| `AGRITWIN_CLOUD_DSS_URL` | empty | Cloud DSS endpoint that accepts `/api/edge/sensor-packets`. |
| `AGRITWIN_CLOUD_DSS_TOKEN` | empty | Optional bearer token for cloud DSS sync. |

## Local Storage

- `var/edge/queue`: JSON files for outbound events. Files are idempotent by `gateway_id:packet_id`.
- `var/edge/cache`: JSON files for last OpenAgri service responses, keyed by service name.
- Runtime files are ignored by git; only `.gitkeep` placeholders are committed.
- Do not store raw private pilot data, credentials, parcel boundaries, or farm network secrets in this repository.

## Network Assumptions

- The gateway may lose 4G connectivity for multiple sampling windows.
- Sensor packets are accepted locally even when the cloud DSS and OpenAgri services are unavailable.
- Synchronisation is sensor-first, then OpenAgri refresh/backlog work.
- Cloud DSS writes must be idempotent using the `Idempotency-Key` header.
- The local prototype endpoint is `POST /api/edge/sensor-packets.php`.
- HTTP `409` is treated as an already-synced duplicate and removed from the local queue.

## Commissioning

1. Set a stable `AGRITWIN_EDGE_GATEWAY_ID`.
2. Confirm the gateway clock is synchronised; packet timestamps drive latency and uptime KPIs.
3. Validate representative payloads against `docs/edge/sensor_payload_spec.md`.
4. Start with `AGRITWIN_EDGE_FORCE_OFFLINE=true` and confirm packets are queued locally.
5. Disable forced offline mode and run the sync worker.
6. Confirm cloud DSS receives canonical `sensor_packet` records and returns success or duplicate acknowledgement.
7. Confirm OpenAgri cache files are refreshed during normal operation.
8. Confirm recommendations show `confidence.mode = reduced` when cache files are stale or missing.

## Troubleshooting

| Symptom | Check | Action |
| --- | --- | --- |
| Queue keeps growing | Connectivity status and cloud DSS URL | Verify 4G, DNS, `AGRITWIN_CLOUD_DSS_URL`, and firewall rules. |
| Duplicate packets appear in cloud | Idempotency handling | Ensure cloud DSS stores and respects `gateway_id:packet_id`. |
| Recommendations are always reduced | OpenAgri cache age | Refresh weather/irrigation/pest cache or raise stale threshold for testing. |
| Packets rejected locally | Sensor payload schema | Check required fields, supported parameter names, numeric values, and timestamps. |
| Retries are delayed | Backoff state | Inspect `next_attempt_at` in queue JSON files. |
| KPI continuity is low | Gateway clock and packet cadence | Confirm 15-minute sampling and processing within 30 minutes. |

## Cloud DSS Compatibility

The edge gateway emits the same canonical records documented in `docs/architecture/data_model.md`:

- `sensor_packet` for local sensor batches.
- `sensor_reading` for individual measurements.
- `recommendation` for local reduced-confidence fallback output.

The cloud DSS should accept `POST /api/edge/sensor-packets.php` with an `Idempotency-Key` header and preserve the canonical envelope fields for KPI evaluation and downstream advisory generation. The endpoint stores accepted prototype packets under `var/cloud_dss/sensor_packets/`, which is ignored by git.
