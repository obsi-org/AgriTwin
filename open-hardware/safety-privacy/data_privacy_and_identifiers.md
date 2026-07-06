# Data Privacy and Identifier Guidance

## Identifier Rules

- Use generic deployment IDs, node IDs and station IDs.
- Do not publish exact coordinates in public documentation or sample data.
- Do not publish private parcel identifiers, infrastructure identifiers or operator names.
- Do not encode site identity in MQTT topics or HTTP paths.
- Keep credentials, certificates and local service configuration outside git.

## Public Documentation Rules

Allowed:

- Generic crop codes.
- Regional climate context.
- Synthetic or anonymised values.
- Coarse or synthetic coordinates only when clearly labelled.

Excluded:

- Raw operational logs.
- Exact private boundaries and sensor locations.
- Site-specific thresholds and calibration coefficients.
- Real passwords, tokens, radio keys or certificates.

## Telemetry Handling

- Use data minimisation for payloads.
- Store only identifiers needed for operations.
- Apply role-based access to operational dashboards.
- Remove or aggregate private details before external sharing.
