# GAIA Reference Cleanup

Command used:

```bash
grep -R "GAIA\|Gaia\|gaia" -n . --exclude-dir=.git --exclude-dir=node_modules --exclude-dir=vendor
```

## User-Facing References Fixed

- `agritwin_dashboard/settings.php`: replaced the legacy GAIA settings UI with the shared AgriTwin HMI settings page.
- `agritwin_dashboard/task_scheduling.php`: replaced with AgriTwin Farm Calendar page wrapper.
- `agritwin_dashboard/user_profile.php`: replaced with AgriTwin local-demo operator profile page.
- `agritwin_dashboard/energy.php`: replaced with AgriTwin energy telemetry page.
- `agritwin_dashboard/field1.php`: updated the visible/commented dashboard reference from GAIA to AgriTwin.

## Remaining References

No user-facing GAIA references are intended to remain in rendered HMI pages. Historical references in unrelated documentation or third-party metadata should be left untouched if they are not shown in the local AgriTwin UI.
