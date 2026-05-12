# OpenAgri Catalogue Submission Notes

## Project

- Name: AgriTwin
- Category: Agricultural Digital Twin / Decision Support System
- Target users: farm operators, agronomists, pilot engineers, OpenAgri integrators.
- Repository URL: `https://github.com/openagri-eu/agritwin-placeholder`
- Licence: Apache-2.0 for original AgriTwin code and documentation, subject to project-owner confirmation.

## Summary

AgriTwin is a local prototype for an agricultural digital twin and advisory decision-support system. It demonstrates a farmer-facing HMI, OpenAgri service connectors, mixed edge-cloud sensor buffering, offline/reduced-confidence operation, KPI instrumentation, and engineering documentation derived from the AgriTwin Proposal and D1.

## Components for Catalogue Listing

- DSS core pipelines: canonical records, recommendations, KPI evidence, and advisory export stubs.
- Environmental Digital Twin toolkit: Cesium-oriented frontend and synthetic parcel geometry extension points.
- OpenAgri integration adapters: Gatekeeper, Weather, Irrigation, Pest/Disease, FarmCalendar, and Reporting clients.
- Deployment artefacts: Dockerfile, Docker Compose mock profile, Makefile tasks, environment template.
- Edge-node specifications: PHP edge gateway module, sensor payload schema, durable queue, local cache, sync worker, and fallback inference rules.

## Reproducible Demo

Start the mock demo:

```bash
make demo
```

Run tests and publication checks:

```bash
make publication-check
```

The mock profile starts:

- AgriTwin PHP app on `http://127.0.0.1:8080`.
- MariaDB seeded from synthetic `mysql.sql`.
- OpenAgri mock service on `http://127.0.0.1:18080`.

## Privacy Limitations

The public repository must not contain raw pilot-farm sensor streams, private parcel boundaries, operational logs, credentials, tokens, TLS certificates, farm network secrets, consent records, precise asset coordinates, or site-specific thresholds.

The current demo data is synthetic or legacy demo data and must not be presented as validated pilot evidence.

## Submission Checklist

1. Confirm repository URL and release tag.
2. Confirm licence and third-party asset rights.
3. Confirm `make publication-check` passes.
4. Confirm source PDFs under `docs/source/` are cleared for redistribution or removed.
5. Attach `docs/publication/catalogue_metadata.yml`.
6. Include links to architecture, local deployment, security, edge gateway, integration, and KPI methodology documentation.
7. Identify known limitations and maturity level as prototype/local demo.
