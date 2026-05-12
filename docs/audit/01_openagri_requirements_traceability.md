# OpenAgri Requirements Traceability

Audit date: 2026-05-12

Status values: `Implemented`, `Partial/demo`, `Documented only`, `Missing`.

| Requirement | Source basis | Local evidence | Status | Gap / next action |
| --- | --- | --- | --- | --- |
| AI-powered DSS for irrigation, fertilisation/nitrogen, and pest-management recommendations | Proposal objectives and technology description; D1 claims predictive algorithms for water stress, pest risk, and nitrogen demand | `ai_recommendations` seed table and `ai_recommendations.php` display static recommendation rows | Partial/demo | Implement model/data pipelines, inference service, agronomic rules, confidence, provenance, feedback capture, and validation metrics |
| Environmental Digital Twin, preferably Cesium-based 2D/3D view | Proposal names Cesium-based 2D/3D Environmental Digital Twin; D1 figures show digital twin and Copernicus NDVI views | `field1.php`, `mobile.html`, CesiumJS, WMS imagery, local 3D assets, CSV overlays | Partial/demo | Generalize beyond hard-coded farm, remove missing page refs, add live telemetry, scenario simulation, and tested mobile/desktop behavior |
| IoT sensor ingestion and edge gateway | Proposal requires IoT sensors and mixed edge-cloud model; D1 claims edge IoT gateway pipelines | Seeded `farm_sensors`, `farm_measurements`, settings placeholders for REST endpoints | Documented only | Build ingestion API/queue, gateway adapter, local buffering, schema validation, and sync logic |
| Copernicus/EO and weather data ingestion | Proposal requires Copernicus C3S/LMS/EO and weather services; D1 references Copernicus datasets | `field1.php` builds Copernicus/Sentinel Hub WMS; `meteo.php` fetches AEMET; NASA GIBS WMS used | Partial/demo | Add robust server-side ingestion, caching, credentials management, error handling, normalized records, and historical storage |
| OpenAgri Weather Data Service | Proposal section 3.6; D1 claims validated connector | No OpenAgri Weather client found | Missing | Implement Gatekeeper-authenticated connector, cache, and mapping to internal weather records |
| OpenAgri Irrigation Service | Proposal section 3.6; D1 claims validated connector | No OpenAgri Irrigation client found | Missing | Implement connector and recommendation workflow using soil moisture, precipitation, and ET inputs |
| OpenAgri Pest & Disease Management Service | Proposal section 3.6; D1 claims validated connector | No OpenAgri pest/disease client found | Missing | Implement connector, crop/growth-stage inputs, risk index display, and alerts |
| OpenAgri Gatekeeper/access control | Proposal section 3.6; D1 mentions Gatekeeper | Session login only; no external OAuth/token exchange or policy enforcement | Missing | Add OpenAgri Gatekeeper integration, service credentials, token refresh, authorization model, and audit logs |
| FarmCalendar integration | User-provided OpenAgri reference and D1 integration context | No FarmCalendar references in code | Missing | Add calendar API client or export/import path for farm tasks and schedules |
| Reporting Service/PDF advisory export | D1/source terminology and OpenAgri service expectations | No PDF/report generation code | Missing | Add advisory report generator with input provenance and export tests |
| Offline-first PWA and low-connectivity operation | Proposal requires remote/weak-connectivity support; D1 claims offline-first PWA sync | `mobile.html` exists but no manifest, service worker, cache, or sync queue detected | Missing | Add web manifest, service worker, local persistence, conflict handling, and connectivity tests |
| Cloud-only ADS | D1 requires comparable cloud-only ADS | PHP/MySQL app can run centrally but lacks deployment definition and service boundaries | Partial/demo | Define cloud runtime, environment config, database migrations, external service connectors, and monitoring |
| Mixed edge-cloud ADS | Proposal and D1 require hybrid edge/cloud operation | Seeded server names include SensorNode/Wazuh/AI/DB/WebSrv; no edge runtime | Documented only | Add edge gateway package, local inference/cache split, sync protocol, and deployment instructions |
| KPI: irrigation schedule latency | D1 KPI capture requirements | No metric field or instrumentation | Missing | Capture recommendation/request timestamps and schedule publication latency |
| KPI: water-stress forecast MAE | D1 KPI capture requirements | No forecast/ground-truth model metrics | Missing | Store predictions, observed outcomes, and MAE reports |
| KPI: nitrogen recommendation error | D1 KPI capture requirements | Static fertilisation recommendation text only | Missing | Add nitrogen recommendation model outputs, lab/field validation, and error calculation |
| KPI: data-continuity/service uptime | D1 KPI capture requirements | `server_performance` seed has CPU/memory/network/pods only | Missing | Add heartbeat, uptime, ingestion continuity, and alert metrics |
| KPI: network volume | D1 KPI capture requirements | `server_performance` has inbound/outbound Mbps seed values | Partial/demo | Replace static seed with measured network counters by component and mode |
| KPI: CPU/GPU hours | D1 KPI capture requirements | Seeded CPU usage only; no GPU/hour accounting | Missing | Add compute usage accounting for cloud and edge inference |
| KPI: energy consumption | D1 KPI capture requirements | `energy_readings` stores solar/wind/battery demo values | Partial/demo | Add typed energy measurements and ADS energy usage, not only farm energy status |
| KPI: standards/interoperability and switchability | Proposal stresses open-source/interoperable standards | No OpenAPI, adapters, or standards conformance tests | Missing | Publish APIs, data schemas, connector contracts, and switchability evidence |
| Open-source packaging requirements | Proposal and D1 require open-source reusable ADS components | Local source present; minimal Dockerfile; no license, README, compose, package metadata, CI | Partial/demo | Add license, README, complete container/compose, dependency metadata, release process, and reproducible setup |
| GDPR/data-protection constraints | Proposal/D1 risk management and data-sharing constraints | Login/session exists; no privacy design, retention, encryption, DPA notes, or PII inventory | Missing | Add DPIA-oriented docs, data inventory, consent/retention rules, encryption, secret handling, and access logs |

## Summary

The local prototype demonstrates early user-interface concepts and a Cesium field view, but most OpenAgri integration, DSS, edge-cloud, offline, KPI, packaging, and GDPR requirements are either missing or represented by static/demo data. The next milestone should prioritize configuration/secrets hygiene, a reproducible local stack, and one real end-to-end connector path with typed data and tests.

