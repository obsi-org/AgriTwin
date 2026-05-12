# User Management

AgriTwin uses the existing PHP session login flow with PDO/MySQL users and `password_hash` / `password_verify`.

## Roles

- Administrator: full configuration and user management.
- Technical Manager: service, API and technical configuration.
- Farm Manager: operational pages and reports.
- Advisor/Researcher: read-only dashboard, reports, KPIs and selected datasets.
- Viewer: read-only dashboard access.

## Permissions

User Management is available to Administrators only. Settings and OpenAgri configuration are intended for Administrators and Technical Managers. Report access is available to Administrator, Technical Manager, Farm Manager and Advisor/Researcher roles.

## First Run

`db_init.php` creates the required tables and a local bootstrap administrator if the user table is empty. The placeholder password must be changed before any real deployment.

## Security Limitations

This is a local prototype. Secrets are not stored through the UI; real OpenAgri credentials must be supplied through environment variables or a deployment secret store.
