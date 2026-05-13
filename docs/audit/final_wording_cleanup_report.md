# Final Wording Cleanup Report

Date: 2026-05-13

## Purpose

This cleanup makes the active AgriTwin interface read as a final operational platform rather than an engineering reporting surface. Historical audit files may still contain source-programme terminology where they document past work, but active rendered pages and current operator-facing text use product and platform terminology.

## Rendered UI Terms Removed

The active page templates, translation files and OpenAPI descriptions were updated to remove visible references to:

- proposal/reporting-stage wording.
- deliverable wording.
- work-package shorthand.
- prototype wording.
- non-operational local-test wording.
- historical third-party platform naming.

## Replacement Terminology

- Core OpenAgri services.
- Core OpenAgri integration.
- Additional operational services.
- Optional services.
- Local operational dataset.
- Local fallback.
- Configuration required.
- Credentials required.
- Service available.
- Service unavailable.
- Operational performance indicators.

## Credential Handling

`LOCAL_CREDENTIALS.md` was created at the repository root for local temporary credentials and added to `.gitignore`. The file is intentionally local-only and must not be staged or committed. Tracked documentation only states where the local credential file is stored; it does not include passwords.

## OpenAgri API Compatibility

The preferred OpenAgri core-services endpoint is:

```text
/agritwin3/api/openagri/core-services
```

The older route remains available for compatibility, but current UI labels, OpenAPI descriptions and documentation use core/additional/optional service terminology.

## Verification Record

Verification completed for this cleanup:

- PHP syntax checks on modified PHP files.
- `make test`.
- Docker Compose configuration validation.
- Local stack health check.
- OpenAgri core service check.
- Curl checks for dashboard, analytics, OpenAgri Services, Settings, Users, Swagger/OpenAPI and the OpenAPI JSON.
- Rendered-page grep checks for removed UI terms.
- Git checks confirming `LOCAL_CREDENTIALS.md` is ignored and plaintext credentials are not staged.

Observed results:

- Core OpenAgri services check returned available for Gatekeeper, Weather, Irrigation and Pest & Disease local endpoints.
- The local stack check reported the database container healthy and rendered platform pages successfully.
- Rendered-page checks found none of the removed UI terms on the checked active pages.
- Authenticated Users page check passed after the bootstrap technical account was renamed to a professional username.
- Plaintext credential checks found no local credentials in tracked diffs, rendered HTML or OpenAPI JSON.
