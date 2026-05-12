# Open-Source Publication Checklist

This checklist prepares AgriTwin for safe public release and OpenAgri Open Source Catalogue submission.

## Licence and Governance

- [x] `LICENSE` added with Apache-2.0 for original AgriTwin code and documentation.
- [x] `NOTICE` added.
- [x] `THIRD_PARTY_LICENSES.md` added with current dependency and asset review notes.
- [x] `SECURITY.md` added.
- [x] `CONTRIBUTING.md` added.
- [x] `CODE_OF_CONDUCT.md` added.
- [ ] Project owner confirms Apache-2.0 is acceptable or requests EUPL-1.2 compatibility wording before public publication.

## Data and Secret Safety

- [x] Hard-coded Cesium and AEMET tokens removed from PHP seed/runtime code.
- [x] Database credentials moved to environment variables.
- [x] Precise parcel boundary JavaScript replaced with synthetic demo geometry.
- [x] Runtime KPI/export files removed from the working tree.
- [x] `.gitignore` covers local secrets, raw pilot data, runtime queues, caches, and generated outputs.
- [x] `make scan-secrets` added for tracked text files.
- [ ] Git history reviewed or rewritten if public release must guarantee no historical secret exposure.
- [ ] Binary files, PDFs, images, and 3D assets manually reviewed for redistribution rights and sensitive content.
- [ ] `docs/source/` redistribution approval confirmed; otherwise remove source PDFs from public repository.

## Synthetic Demo Readiness

- [x] Synthetic KPI fixtures are clearly marked.
- [x] Synthetic parcel geometry is clearly marked.
- [x] Docker Compose mock profile added for local demo.
- [x] One command to start demo: `make demo`.
- [x] One command to test: `make test`.
- [x] Publication check command: `make publication-check`.

## Manual Review Before Publishing

- Confirm all logos, screenshots, 3D models, textures, and CSV files are original or cleared.
- Confirm no exact pilot-farm parcel boundaries or asset coordinates remain.
- Confirm no site-specific agronomic thresholds are embedded in committed files.
- Confirm no private operational logs are present.
- Confirm OpenAgri service references use public repository URLs and do not embed credentials.
- Confirm repository URL, maintainers, support contact, and release tag are final in catalogue metadata.

## Current Automated Scan

The local scan script is conservative:

```bash
make scan-secrets
```

Limitations:

- It scans tracked text files only.
- It excludes binary assets and PDFs.
- It does not inspect full git history.
- It is not a substitute for a formal legal/IP review.
