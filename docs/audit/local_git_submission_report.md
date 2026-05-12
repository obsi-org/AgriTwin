# Local Git Submission Report

Date: 2026-05-12
Repository path: `/var/www/html/agritwin3`

## Current Branch

`main`

## Commit History Before Final Report Commit

```text
a1f861f chore: prepare AgriTwin for open-source publication
b156af3 feat: harden edge gateway buffering and synchronisation
ef6019a feat: add D1 KPI instrumentation and evaluation evidence
65f225b feat: improve AgriTwin farmer HMI and offline dashboard
8588ba8 feat: integrate OpenAgri service connectors and offline fallback
72b8a5d docs: add AgriTwin architecture and proposal traceability pack
2eff802 chore: initialise AgriTwin audit and local repository baseline
```

## Uncommitted Changes Before This Report Commit

Expected final-report changes:

- `.env.example`: add configurable demo ports.
- `README.md`: document alternate demo port.
- `docker-compose.yml`: make app/OpenAgri mock published ports configurable.
- `docs/audit/final_verification_report.md`: final verification report.
- `docs/audit/local_git_submission_report.md`: this report.

No unexpected runtime artefacts were present under `exports/` or `var/`.

## Verification Summary

- `make publication-check`: passed.
- `docker compose --profile mock config`: passed.
- `AGRITWIN_APP_PORT=18081 docker compose --profile mock up --build -d`: passed.
- Health check `http://127.0.0.1:18081/`: passed.
- Health check `http://127.0.0.1:18080/api/data/forecast5`: passed.
- `docker compose --profile mock down -v --remove-orphans`: passed.
- `git diff --check`: passed.

## Tag Recommendation

Create local tag:

```bash
git tag agritwin-local-d2-readiness
```

Rationale: all relevant local lint, tests, publication scan, Compose config, Docker image build, mock stack startup, HTTP health checks, and clean shutdown passed.

## Suggested Manual Checks Before Remote Push

- Confirm project-owner approval for Apache-2.0 or adjust to the required OpenAgri/EUPL-compatible policy.
- Review and clear all binary/image/3D assets for redistribution.
- Confirm whether `docs/source/` PDFs may be included in a public repository.
- Scan full git history for secrets and private data; rotate any historical credentials.
- Confirm catalogue metadata repository URL, maintainer contact, and release tag.
- Test against live OpenAgri services in a controlled environment with non-sensitive credentials.
- Review GDPR/data-protection assumptions with the project data owner before pilot validation.
