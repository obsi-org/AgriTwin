# GDPR and Security

Source basis: Proposal and D1 requirements for privacy, Gatekeeper access control, low-connectivity deployment, and open-source publication.

This document defines repository and deployment boundaries. It is not legal advice; it is an engineering control baseline for AgriTwin.

## Non-Publication Rules

Never publish or commit:

- Raw pilot-farm data.
- Operational logs from pilot systems.
- Credentials, tokens, API keys, passwords, or client secrets.
- TLS certificates or private keys.
- Farm network secrets, SIM/router details, Wi-Fi names, or passwords.
- Private parcel boundaries or asset coordinates.
- Site-specific thresholds, irrigation strategies, or commercial operating parameters.
- Identifiable user data, consent records, emails, phone numbers, or report PDFs.
- Model artifacts trained on private data unless separately approved for publication.

The public repository may include source code, synthetic examples, schemas, non-sensitive assets, and documentation.

## Pseudonymisation and Anonymisation

Pseudonymisation for internal pilot use:

- Replace real farm, parcel, user, and asset identifiers with stable pseudonymous IDs.
- Keep the re-identification mapping in a private controlled environment.
- Generalize coordinates where exact location is not required.
- Store personal identifiers separately from operational telemetry.

Anonymisation for publication:

- Remove direct and indirect identifiers.
- Replace real geometries with synthetic/demo geometries.
- Aggregate time-series data where needed to prevent operational inference.
- Remove thresholds and exact operational schedules.
- Verify examples cannot identify FOTOMACO, staff, network topology, or commercial practices unless explicit publication approval exists.

## Consent-Related Data Handling

- Keep consent records outside the public repository.
- Link consent records to pseudonymous participant IDs, not source-code examples.
- Enforce data-use purpose limits in documentation and access policy.
- Record consent withdrawal events in private audit logs.
- Remove or quarantine related personal data when withdrawal or retention expiry requires it.

## Secret Management

Development:

- Use `.env` or shell environment variables ignored by git.
- Do not edit source files with local secret values.
- Rotate any credential that has been committed historically before shared deployment.

Pilot/cloud:

- Use a secret manager or orchestrator secrets.
- Scope credentials by service and role.
- Prefer short-lived Gatekeeper tokens.
- Log token expiry and credential reference names, never token values.
- Restrict access to production secrets to named operators.

Target variables include:

- `AGRITWIN_DB_PASSWORD`
- `AGRITWIN_CESIUM_TOKEN`
- `AGRITWIN_COPERNICUS_ID`
- `AGRITWIN_AEMET_API_KEY`
- `AGRITWIN_OPENAGRI_GATEKEEPER_CLIENT_SECRET`
- FarmCalendar and Reporting service credentials.

## Access-Control Assumptions

Roles:

- Administrator: configuration, users, connectors, deployment status.
- Agronomist: recommendations, reports, model review, threshold approval.
- Technician: sensor/asset commissioning, farm operations, issue triage.
- Viewer: read-only dashboards and approved reports.
- Edge gateway service account: sensor batch upload and config pull only.
- Connector service account: OpenAgri service access through Gatekeeper only.

Required controls:

- Authenticated sessions with secure cookies.
- CSRF protection for state-changing browser actions.
- Role checks for every write operation.
- Separate service credentials from user credentials.
- Audit logging for settings, recommendations, reports, users, and connector configuration.

## Audit Logging

Audit logs must capture:

- Authentication events.
- Settings changes, especially credentials references and connector endpoints.
- Sensor batch ingestion summary and validation failures.
- Recommendation generation, acceptance, rejection, and supersession.
- FarmCalendar sync create/update/conflict events.
- PDF report generation and access.
- Model artifact publication and rollback.
- Edge sync status, queue replay, and reduced-confidence mode activation.

Audit logs must not contain:

- Raw secrets or tokens.
- Full private payloads unless stored in a private protected audit store.
- Personal data beyond what is required for accountability.

## Current Prototype Security Debt

- Hard-coded database credentials exist in `agritwin_dashboard/db_config.php`.
- Hard-coded AEMET API key exists in `agritwin_dashboard/meteo.php`.
- Seeded Cesium/Copernicus values exist in `db_init.php`/database settings.
- Forms lack CSRF protection.
- Session cookie security settings are not centralized.
- Role-based authorization is not implemented.

These items must be resolved before pilot or public deployment.

