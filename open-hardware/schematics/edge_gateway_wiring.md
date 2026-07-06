# Edge Gateway Wiring and Interfaces

The edge gateway connects local sensor traffic to AgriTwin services. It may be installed in a weatherproof cabinet, a pump room or another protected field location.

## Interface Table

| Interface | Connection | Notes |
| --- | --- | --- |
| DC power input | UPS, solar battery controller or protected mains adapter | Provide clean shutdown and surge protection. |
| Ethernet | Router, switch or local LAN | Prefer wired backhaul where available. |
| Cellular modem | USB or Ethernet modem | Use external antenna and monitor signal quality. |
| LoRaWAN concentrator | USB, SPI or Ethernet gateway | Optional for low-power wide-area sensor nodes. |
| Local MQTT broker | TCP listener on private network | Configure credentials outside the repository. |
| Local HTTP receiver | HTTPS or private LAN HTTP | Use only trusted local networks or TLS termination. |
| Storage | SSD, eMMC or high-endurance card | Stores queue, cache and logs. |
| Service diagnostics | HDMI/USB or SSH on maintenance network | Disable unnecessary access in unattended deployments. |

## Service Structure

- MQTT broker or local HTTP receiver receives telemetry.
- Local queue/cache persists measurements during outages.
- Lightweight validation checks ranges, timestamps and node identifiers.
- Sync worker sends queued data to cloud ingestion when connectivity returns.
- OpenAgri/weather cache stores recent inputs for reduced-confidence edge decisions.

No private credentials should be written into service files committed to git.
