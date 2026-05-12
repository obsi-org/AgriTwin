# Security Policy

## Supported Scope

This local prototype is not production software. Security reports should focus on the repository contents, demo deployment path, OpenAgri connector handling, edge gateway buffering, and documentation that could lead to unsafe deployment.

## Reporting a Vulnerability

Do not open a public issue containing secrets, private pilot-farm data, exploit details, or farm network information.

Use the repository owner's private reporting channel when available. If no private channel exists yet, contact the project maintainer directly and include:

- A concise description of the issue.
- Affected files, configuration, or endpoint.
- Reproduction steps using synthetic data only.
- Whether credentials, private data, or operational details may be exposed.

## Secret Handling

- Never commit real tokens, passwords, client secrets, TLS keys, API keys, `.env` files, or farm network credentials.
- Use `.env.example` as a template only.
- Rotate any credential that was ever committed to git history or shared through logs.
- Do not log Authorization headers, refresh tokens, access tokens, private APN details, or raw pilot payloads.

## Private Data Handling

Do not publish:

- Raw pilot-farm sensor streams.
- Private parcel boundaries or asset coordinates.
- Operational logs.
- Site-specific agronomic thresholds.
- Consent records or personal data.
- Identifiable report PDFs.

Use synthetic or approved anonymised data for tests, fixtures, demos, and screenshots.

## Pre-Publication Checks

Run:

```bash
make publication-check
```

The included scan is conservative and grep-based. It does not inspect binary files, PDFs, images, or full git history; those require separate manual review before a public release.
