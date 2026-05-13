# OpenAgri Weather Activation

Date: 2026-05-13

AgriTwin Settings can store the Weather provider API key needed for live OpenAgri Weather observations. The key is handled as a local secret and is never committed to git.

## Configure Through Settings

1. Log in as an Administrator or Technical Manager.
2. Open `/agritwin3/agritwin_dashboard/settings.php`.
3. In **Weather provider API key**, enter the new provider key.
4. Select the transport expected by the provider: bearer token, HTTP header or query parameter.
5. Set the header name or query parameter name when that transport is used.
6. Select **Save API key**.
7. Select **Validate API key** to run an OpenAgri Weather observation request.

After saving, the field is cleared. The UI shows only status metadata and, for locally stored keys, a masked value using the final four characters.

## Storage

The default storage is an encrypted local file:

- Secret metadata and encrypted value: `storage/secrets/weather_provider.local.json`
- Local encryption key when no `AGRITWIN_SECRET_KEY` or `APP_KEY` exists: `storage/secrets/agritwin_secret.key`

`storage/secrets/` is ignored by git. Do not copy these files into commits, issue reports or logs.

## Secret Precedence

1. `OPENAGRI_WEATHER_TOKEN` or `AGRITWIN_OPENAGRI_WEATHER_TOKEN` from the runtime environment.
2. Weather provider key stored through AgriTwin Settings.

Environment-provided keys are preferred for production deployments. Settings-stored keys are intended for controlled local validation.

## Validation

Validation calls the configured OpenAgri Weather data endpoint using the selected key transport. It records:

- `last_validated_at`
- `last_validation_status`
- `last_validation_message`

The raw key is not returned by the UI or API.

## Removal

Use **Remove API key** on the Settings page. This deletes the local encrypted weather secret file and clears metadata.

## Security Limitations

This is a local prototype. File permissions are set restrictively, but the host machine administrator can still access local files. Do not use production credentials on an untrusted machine. Back up secret files only through approved secret-management procedures, not through repository archives.
