# AgriTwin D1 KPI Evidence Report

This generated report uses synthetic/sample KPI events only. It must not be treated as pilot performance evidence until populated from approved, pseudonymised pilot telemetry.

- Generated at: `2026-05-12T09:24:35+00:00`
- Baseline ADS: `cloud`
- Comparison ADS: `mixed`

## Cloud-Only vs Mixed Edge-Cloud Summary

| Metric | Cloud-only | Mixed edge-cloud | Delta mixed-cloud |
| --- | ---: | ---: | ---: |
| Irrigation schedule latency, seconds | 92 | 38 | -54 |
| Water-stress forecast MAE, m3/m3 | 0.005 | 0.002 | -0.003 |
| Nitrogen recommendation MAE, kg/ha | 4 | 2 | -2 |
| Data-continuity uptime ratio | 0.666667 | 1 | 0.333333 |
| Network use, MB/hour | 59.7 | 23.8 | -35.9 |
| CPU-hours | 0.41 | 0.18 | -0.23 |
| GPU-hours | 0 | 0 | 0 |
| Forecast-cycle energy, kWh | 0.018 | 0.009 | -0.009 |
| Farming-operation energy, kWh | 0.82 | 0.79 | -0.03 |

## Evidence Notes

- Sensor continuity is counted from 15-minute packets processed within 30 minutes.
- Forecast accuracy uses MAE where ground truth or agronomist reference values are present.
- Network, compute, and energy values support measured values or explicit estimates with a source/assumption field.
- Standards/interoperability evidence is collected as structured event categories and should be backed by implementation artefacts during pilot evaluation.
