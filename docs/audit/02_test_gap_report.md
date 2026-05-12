# Test Gap Report

Audit date: 2026-05-12

## Existing Coverage

The repository now includes a lightweight mocked OpenAgri connector test runner in `tests/openagri_connectors/OpenAgriConnectorTest.php`.

Safe verification performed for the initial audit:

```text
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

Result: all PHP files reported no syntax errors.

Additional OpenAgri verification:

```text
make test-openagri
```

This covers connector success paths, timeout fallback, invalid schema fallback, stale cache reduced-confidence mode, offline mode, missing GateKeeper credentials, restore/backfill cache discovery, and edge queue priority.

## Key Gaps

- No database migration/import test for `mysql.sql` or `db_init.php`.
- No authentication/session tests.
- No authorization/role tests.
- No CSRF or input-validation tests.
- No live OpenAgri connector tests against deployed services.
- No weather/EO ingestion tests.
- No Cesium/browser smoke tests.
- No offline/PWA tests.
- No KPI computation tests.
- No Docker image runtime test.

## Recommended First Test Milestones

1. Add a local compose stack with PHP and MySQL, then test schema import.
2. Add PHPUnit or Pest for PHP helper code once configuration is extracted from page scripts.
3. Add Playwright smoke tests for login, dashboard navigation, settings, and Cesium page load.
4. Add contract tests for OpenAgri Weather/Irrigation/Pest/Gatekeeper clients when implemented.
5. Add static checks for secrets and external URLs before publishing.
