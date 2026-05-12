# AgriTwin D1 KPI Methodology

This document defines the KPI instrumentation used to compare cloud-only ADS and mixed edge-cloud ADS operation for the AgriTwin prototype. The local implementation stores auditable KPI events in JSON Lines format and exports JSON, CSV, and Markdown evidence. Only synthetic sample data is committed to the repository.

## Collection Boundary

- Local persistent store: `var/kpi/events.jsonl`, configurable through `AGRITWIN_KPI_STORE`.
- Committed sample fixture: `fixtures/kpi/sample_events.json`.
- Generated evidence: `docs/evidence/generated/`.
- Runtime exports: `exports/kpi/`.
- Privacy rule: raw pilot-farm telemetry, private parcel boundaries, credentials, operational logs, consent records, and site-specific thresholds must not be committed.

## Metric Definitions

### Irrigation Schedule Latency

- Metric: latency to generate an irrigation schedule.
- Data required: `input_packet_received.received_at`, `irrigation_schedule_generated.generated_at`, shared `cycle_id`, `architecture_mode`.
- Collection method: emit an input event when the DSS has the required packet set and a schedule event when the advisory schedule is produced.
- Formula: `generated_at - received_at`, aggregated as average, minimum, and maximum seconds.
- Limitation: sample data has one cycle per mode; pilot evidence needs repeated cycles across equivalent agronomic conditions.

### Water-Stress Forecast Accuracy

- Metric: mean absolute error against soil-moisture probe readings.
- Data required: predicted soil moisture, ground-truth probe reading, forecast cycle, architecture mode, ground-truth source.
- Collection method: emit `water_stress_forecast` after prediction and update it or emit a paired event once the probe reading is available.
- Formula: `MAE = mean(abs(predicted_soil_moisture_m3_m3 - ground_truth_soil_moisture_m3_m3))`.
- Limitation: ground-truth timing must be aligned with the forecast horizon; stale probes must be excluded or flagged.

### Nitrogen/Fertiliser Recommendation Accuracy

- Metric: absolute error against agronomist reference prescription.
- Data required: DSS recommended nitrogen or fertiliser quantity, agronomist reference quantity, units, crop/parcel context, cycle.
- Collection method: emit `nitrogen_recommendation` with the DSS value and add the reference value after agronomist validation.
- Formula: `MAE = mean(abs(recommended_nitrogen_kg_ha - agronomist_reference_nitrogen_kg_ha))`.
- Limitation: prescriptions may use product-specific units; conversion to kg/ha of nitrogen must be explicit.

### Data Continuity and Service Uptime

- Metric: ratio of 15-minute sensor packets ingested and processed within 30 minutes.
- Data required: `sensor_packet_received.packet_created_at`, `processed_at`, expected interval, packet id, mode.
- Collection method: edge or cloud ingestion emits packet events after processing completes.
- Formula: `uptime_ratio = packets_processed_within_30_minutes / total_received_packets`.
- Limitation: this ratio measures processing continuity for received packets; missing packets at source require gateway sequence counters or expected schedule reconciliation.

### Networking Resources

- Metric: MB/hour and peak uplink/downlink where feasible.
- Data required: bytes sent, bytes received, window start/end, service, mode, peak rates if available.
- Collection method: record HTTP client counters, edge gateway network interface counters, or proxy metrics per service window.
- Formula: `total_mb = (bytes_sent + bytes_received) / 1048576`; `mb_per_hour = total_mb / window_hours`.
- Limitation: peak values depend on measurement granularity and may not be available without gateway or network-device counters.

### Computing Resources

- Metric: CPU-hours and GPU-hours used for data processing.
- Data required: direct CPU/GPU hours or duration and utilization ratios, component, mode.
- Collection method: use container/runtime metrics where available; otherwise record a clearly labelled estimate.
- Formula: direct hours if provided, otherwise `duration_seconds / 3600 * utilization_ratio`.
- Limitation: PHP local prototype estimates do not represent production model-training workloads.

### Forecast-Cycle Energy

- Metric: kWh for data processing and transmission per forecast cycle.
- Data required: kWh or power draw and duration, source, assumption, mode.
- Collection method: use measured node energy where available; otherwise use device power draw multiplied by processing duration.
- Formula: sum `energy_usage.kwh` where `scope = forecast_cycle`.
- Limitation: estimates must carry a source and assumption field and should be replaced by meter data for final evaluation.

### Farming-Operation Energy

- Metric: kWh consumed to complete farming operation, including pumps or related equipment where data exists.
- Data required: operation type, kWh, meter/source, mode, forecast cycle or operation id.
- Collection method: ingest pump meter or controller telemetry where available.
- Formula: sum `energy_usage.kwh` where `scope = farming_operation`.
- Limitation: this metric may be unavailable for farms without pump telemetry.

### Standards, Interoperability and Switchability

- Metric: evidence for replaceable data formats, communication protocols, software modules, farm data collections, and hardware modules.
- Data required: structured `standards_evidence` events with category and evidence list.
- Collection method: emit or document evidence when interfaces are added, exported, or validated.
- Formula: categorical evidence count and item list; qualitative review remains required.
- Limitation: this KPI is not a single scalar score; it is an auditable evidence pack.

## Cloud-Only vs Mixed-Mode Comparison

The CLI compares two architecture modes with:

```bash
php scripts/agritwin-kpi.php compare-ads cloud mixed
```

The report shows baseline cloud-only values, mixed edge-cloud values, and `mixed - cloud` deltas. Lower values are better for latency, error, network use, compute use, and energy use. Higher values are better for uptime ratio and evidence coverage. Pilot analysis must use equivalent time windows, crops, sensor coverage, and agronomic decisions before drawing conclusions.

## Commands

```bash
make collect-kpis
make export-kpis
make compare-ads
make generate-d1-evidence
make test-kpi
```

`collect-kpis` currently seeds synthetic events for local development. Production/pilot collectors should append approved pseudonymised telemetry only.
