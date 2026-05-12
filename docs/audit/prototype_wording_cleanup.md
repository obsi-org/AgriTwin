# Prototype Wording Cleanup

## Removed From Active UI

- demo
- demo healthy
- mock
- dummy
- fake
- stub
- GAIA
- Pilot HMI

## Replacements

- Local fallback active
- Configuration required
- Credentials required
- Connection unavailable
- Endpoint unreachable
- Authentication failed
- Offline mode
- Awaiting live service configuration

## Retained Technical Identifiers

Some non-rendered development identifiers remain in environment templates, Docker profile names, test fixture paths or historical documentation. They are not shown in the operational UI.

## Follow-up on 2026-05-12

- Central page templates now use translation keys for English, Portuguese and Spanish.
- User-facing service states use operational terminology such as `Configuration required`, `Connection unavailable`, `Local fallback active` and `Available`.
- Swagger/OpenAPI examples retain technical status enums where required for API compatibility; rendered HMI labels are translated.
