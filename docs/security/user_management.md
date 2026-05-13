# User Management

AgriTwin uses the existing PHP session login flow with PDO/MySQL users and `password_hash` / `password_verify`.

## Roles

- Administrator: full platform access, settings, OpenAgri configuration, user management, API/Swagger, all data and reports.
- Technical Manager: OpenAgri configuration, API/Swagger, service health, sensor integration, analytics and KPI configuration.
- Farm Manager: dashboard, sensor stations, irrigation, pest/disease, farm calendar, reports and analytics.
- Advisor/Researcher: read-only dashboard, reports, KPIs and selected datasets.
- Viewer: read-only dashboard access.

## Permissions

User Management is available to Administrators only. Settings and OpenAgri configuration are intended for Administrators and Technical Managers. Report access is available to Administrator, Technical Manager, Farm Manager and Advisor/Researcher roles.

## Operational Users

Operational users are created or reset with:

```bash
php scripts/users/create_operational_users.php
```

The script creates/updates:

- Pedro Branco, Administrator access, preferred language `pt-PT`.
- Gregorio Sanchez, Farm Manager access for Fotovoltaica Macotera SL, preferred language `es-ES`.

The script generates strong random temporary passwords at runtime, hashes them with PHP `password_hash`, stores only hashes in MySQL and prints temporary credentials only to the terminal. Do not copy the credentials into documentation, commits, screenshots or logs.

## First Run

`db_init.php` creates the required tables and a local bootstrap administrator if the user table is empty. The placeholder password must be changed before any real deployment.

Operational accounts created by the script set `password_change_required` and `must_change_password`, and the profile page provides a first-login password change form.

## Security Limitations

This is a local prototype. The Weather provider API key can be managed by Administrators and Technical Managers through Settings using encrypted local storage. Other production secrets should be supplied through environment variables or a deployment secret store. See `docs/security/secrets_handling.md`.
