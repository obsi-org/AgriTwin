# OpenAgri Reporting Service Integration

## Current Behaviour

AgriTwin keeps OpenAgri Reporting Service status visible through the OpenAgri service-health layer. User-facing PDF reports are currently generated locally by AgriTwin so that operators always receive a human-readable PDF document instead of raw service metadata.

## Local PDF Generation

The local generator uses AgriTwin data providers and stores generated PDFs under:

```text
var/reports/
```

This runtime directory is ignored by Git.

Report generation and PDF download endpoints require an authenticated AgriTwin session.

## Service Metadata

If the OpenAgri Reporting Service returns JSON metadata, AgriTwin does not show that metadata as the main report view. The Reports page displays report status, preview sections and PDF actions. Technical JSON remains limited to API metadata responses.

## Future Extension

When the live Reporting Service is configured for PDF generation, the local formatter can pass the same structured report sections to the service and keep the current user-facing preview/download behaviour.

## Security

Reports must not include credentials, API keys, tokens or private configuration. Generated runtime PDFs must not be committed.
