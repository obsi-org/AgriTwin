# Secrets Handling

Date: 2026-05-13

## Weather Provider API Key

The Weather provider API key can be managed from AgriTwin Settings by Administrators and Technical Managers. The key is stored in an encrypted local file under `storage/secrets/`, which is ignored by git.

## Controls

- Role restriction: only users with Settings permission can save, validate or remove the key.
- CSRF: Settings form POST requests include the existing AgriTwin CSRF token.
- No clear-text display: saved keys are never pre-filled or rendered. The UI can show only a masked value.
- No API disclosure: status and diagnostics endpoints return metadata only.
- No logging: secret values are not written to logs by the Settings handler or Weather connector.
- No git storage: `.env`, local config files and `storage/secrets/` are ignored.

## Encryption

If `AGRITWIN_SECRET_KEY` or `APP_KEY` is set, AgriTwin derives the local encryption key from that environment value. Otherwise, it creates an ignored local key at `storage/secrets/agritwin_secret.key`.

## Operational Notes

Environment variables take precedence over the Settings-stored key. Production deployments should use environment or orchestrator secret stores rather than browser-managed local settings.
