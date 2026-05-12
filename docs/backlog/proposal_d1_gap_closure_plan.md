# Proposal and D1 Gap Closure Plan

This plan maps the gaps identified in `docs/audit/01_openagri_requirements_traceability.md` to implementation actions.

| Gap | Source requirement | Implementation action | Backlog item | Evidence of closure |
| --- | --- | --- | --- | --- |
| Static AI recommendations only | AI-powered DSS for irrigation, fertilisation/nitrogen, pest management, water stress | Build advisory engine, canonical inputs, model outputs, feedback, and validation metrics | M04, M09, M10, S05 | Recommendations include provenance, confidence, model/service inputs, feedback status, and KPI evidence |
| No real irrigation DSS | OpenAgri Irrigation Service and resource optimization | Implement irrigation connector and irrigation event workflow | M08, M09 | Accepted irrigation recommendations create typed events and optional FarmCalendar tasks |
| No nitrogen/fertilisation model | Proposal requires fertilizer/nitrogen optimization | Add nitrogen recommendation records, model/rule output, and error KPI | M09, M10, S05 | `nitrogen_recommendation_error` KPI and fertilisation recommendations exist |
| No pest/disease connector | OpenAgri Pest & Disease Management Service | Implement pest connector and alert/activity mapping | M08, S01 | Pest risk records create alerts and scouting activities |
| Cesium view is hard-coded demo | High-fidelity Environmental Digital Twin using real-time IoT and EO | Back Digital Twin from canonical farm, parcel, asset, sensor, and EO APIs | M04, M05, S04 | Digital Twin renders from API data rather than hard-coded coordinates only |
| Missing IoT ingestion | IoT sensor ingestion and edge gateway | Add sensor batch API and edge gateway service | M05, M06 | Edge batch persists locally, syncs, and appears in cloud store |
| Missing edge buffering and sync | Mixed edge-cloud ADS and low-connectivity operation | Implement local cache, queue, retry, reduced-confidence mode | M06 | Gateway passes offline/reconnect acceptance tests |
| Missing cloud-only ADS packaging | Cloud ADS prototype | Add reproducible stack and service boundaries | M02, M04 | Local/compose stack runs cloud-only mode with canonical stores |
| Missing OpenAgri Gatekeeper | Gatekeeper/access control and data exchange | Add Gatekeeper connector foundation | M07 | Mock and live-configured token/service discovery checks pass |
| Missing Weather Service integration | OpenAgri Weather Data Service | Add weather connector normalization and cache | M08 | Weather records are stored with source freshness |
| Missing Irrigation Service integration | OpenAgri Irrigation Service | Add irrigation connector normalization and cache | M08 | Irrigation recommendations include OpenAgri provenance |
| Missing Pest & Disease integration | OpenAgri Pest & Disease Management Service | Add pest/disease connector normalization and cache | M08 | Risk indices and alerts are stored |
| Missing FarmCalendar integration | Farm operations, observations, parcels, and assets exchange | Add FarmCalendar connector and conflict-aware sync | S01 | Accepted tasks create/sync farm activities |
| Missing PDF reports | Reporting Service/PDF advisory export | Add Reporting Service/local PDF fallback | S02 | Synthetic PDF report generated and metadata stored |
| Missing offline-first PWA | D1 offline mobile dashboard and low 4G sync | Add service worker, manifest, local cache, queued actions | S03 | Playwright offline tests pass |
| Missing KPI telemetry | Proposal/D1 KPI capture and validation | Add KPI schema and service | M10 | KPI records exist for all required names |
| No standards/switchability evidence | Open-source/interoperable standards | Add documented API contracts, connector interfaces, and interoperability checks | M04, M07, M08, M10 | API docs and `interoperability_check_status` KPI exist |
| No reproducible open-source package | Open-source packaging and catalogue publication | Add license, README, compose, docs, synthetic fixtures | M02, C02 | Fresh checkout can run demo without private data |
| Hard-coded secrets | GDPR/security and secret handling | Externalize configuration and rotate leaked secrets | M01, M11 | No source files contain live secrets; `.env.example` exists |
| No role access control | Gatekeeper/access control and GDPR | Add roles, CSRF, secure sessions, audit logging | M11 | Role and CSRF tests pass |
| No private data publication guard | GDPR/data-protection constraints | Document and enforce non-publication boundaries; add fixtures policy | M11, C02 | Security doc plus synthetic fixtures replace private samples |
| No local deployment runbook | Deploy phase readiness | Maintain local and pilot deployment docs | M02, M06, docs already added | Developers can follow documented local/pilot checklists |
| No tests beyond PHP lint | System testing and validation | Add unit, API, browser, connector, and offline tests | S06 plus each feature item | `make test` runs meaningful suite |
| Incomplete Dockerfile | Reproducible deployment | Build full app image and compose stack | M02 | Container serves dashboard and connects to database |
| Missing EO ingestion pipeline | Copernicus/EO and Digital Twin requirements | Add EO metadata ingestion and object references | S04 | EO observations stored and rendered from canonical records |
| Missing model registry | D1 claims training/retraining and edge/cloud model split | Add training workflow and artifact registry | S05 | Model versions, metrics, and edge artifacts are tracked |

## Closure Order

1. Stabilize and secure the local prototype: M01, M02, M03.
2. Establish canonical data and ingestion: M04, M05.
3. Implement mixed ADS foundations: M06, M07.
4. Implement OpenAgri connectors and advisory logic: M08, M09.
5. Add KPI/security controls: M10, M11.
6. Add FarmCalendar, reporting, offline PWA, EO, and model registry: S01 to S05.
7. Expand tests, fixtures, and optional deployment templates: S06 and COULD items.

