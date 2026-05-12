# Contributing

Thanks for contributing to AgriTwin. This repository is prepared as a safe local prototype and publication candidate for OpenAgri catalogue review.

## Development Workflow

1. Use synthetic data only.
2. Copy `.env.example` to `.env` locally and keep `.env` untracked.
3. Run the test suite before submitting changes:

```bash
make test
make scan-secrets
```

4. Keep changes scoped and document new operational assumptions.

## Data and Security Rules

Do not commit:

- Real passwords, API keys, tokens, certificates, private keys, or `.env` files.
- Raw pilot-farm data, raw telemetry streams, operational logs, or consent records.
- Private parcel boundaries, exact asset coordinates, farm network details, or site-specific thresholds.
- Generated PDFs or reports containing identifiable farm information.

Use `fixtures/` only for synthetic or approved anonymised data and mark examples clearly.

## Code Style

- Follow the existing PHP module style.
- Keep adapters and external service clients isolated under `src/`.
- Add tests for new calculations, connectors, edge behaviour, and publication checks.
- Prefer environment variables over hard-coded configuration.

## Licence

Unless explicitly stated otherwise, contributions are submitted under Apache-2.0 for original AgriTwin code and documentation. Do not remove third-party notices or relicense third-party material.
