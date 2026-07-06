# Data Quality Checks

AgriTwin ingestion should flag:

| Check | Condition | Suggested action |
| --- | --- | --- |
| Missing data | No payload within expected interval | Check power, connectivity and firmware loop. |
| Stale timestamp | `observed_at` is old or gateway-assigned for too long | Check node clock and gateway sync. |
| Out-of-range soil moisture | Value below 0 or above calibrated range | Check probe wiring and calibration. |
| Out-of-range humidity | Value below 0% or above 100% | Check air sensor and enclosure exposure. |
| Rain without humidity change | Rain gauge pulses inconsistent with weather context | Inspect gauge and debounce logic. |
| Battery low | Battery status low or voltage below threshold | Inspect solar panel, charger and battery health. |
| Inconsistent readings | Adjacent probes diverge sharply | Check installation depth and cable damage. |
| Service unavailable | Edge queue grows or cloud sync fails | Check network and service health. |

Quality flags should be stored with telemetry and surfaced as reduced-confidence status where needed.
