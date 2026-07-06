# Deployment Mode Comparison Matrix

| Criterion | Cloud-based ADS mode | Edge + cloud ADS mode |
| --- | --- | --- |
| Connectivity tolerance | Low to moderate; nodes need reliable uplink | High; gateway buffers during outages |
| Latency | Direct cloud latency; depends on backhaul | Low local receipt; cloud sync may lag |
| Data volume | Each node sends directly to cloud | Gateway can batch, compress or filter |
| Energy consumption | Higher radio use per node | Lower node energy if using local low-power network |
| Maintenance | Fewer local services | Gateway requires updates and monitoring |
| Deployment complexity | Lower | Higher, with local compute and storage |
| Resilience | Depends on cloud reachability | Continues local capture during outages |
| Rural suitability | Best with stable cellular/Wi-Fi/LoRaWAN backhaul | Best for weak or intermittent connectivity |
| Local decision support | Limited unless nodes have local logic | Supports local checks and reduced-confidence outputs |
| Data recovery | Depends on node storage | Gateway sync queue supports backfill |
