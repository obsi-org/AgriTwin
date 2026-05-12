# AgriTwin HMI Design Notes

## User Journeys

### Morning field check

1. Open the AgriTwin dashboard on phone or desktop.
2. Review high-priority cards: water stress, irrigation recommendation, pest risk, nitrogen/fertiliser, connectivity.
3. Confirm confidence labels before acting.
4. Open the Cesium Digital Twin only when map context or overlays are needed.

### Irrigation decision

1. Check soil moisture, rain tomorrow, and OpenAgri Irrigation status.
2. Review the irrigation schedule table.
3. Use the recommendation explanation to confirm input sources: sensors, weather, OpenAgri Irrigation, FarmCalendar.
4. If service confidence is stale/reduced, treat the recommendation as advisory and verify locally.

### Pest scouting

1. Open Pest & Disease Alerts.
2. Review risk card and reason text.
3. Check OpenAgri Pest/Disease staleness.
4. Create or follow the scouting task in Farm Calendar once connector wiring is complete.

### Low-connectivity operation

1. Continue using the dashboard over farm Wi-Fi or from browser cache.
2. Watch the offline banner and queued operation count.
3. Avoid irreversible actions when confidence is stale or unavailable.
4. Sync once 4G/cloud connectivity returns.

### Advisory export

1. Open Reports / PDF Advisories.
2. Select Prepare advisory export.
3. The current implementation sends the JSON advisory payload to a safe backend stub, downloads the JSON payload, and opens browser print for PDF output.
4. When OpenAgri ReportingService is configured, this action can be replaced by a live PDF request.

## Screen Inventory

- Field Overview: daily decision cards for water stress, irrigation, pest risk, nitrogen/fertiliser, connectivity, and last sync.
- Digital Twin Map / Cesium view: entry point to the existing Cesium field view and legacy mobile map.
- Irrigation Schedule: current task schedule from local database.
- Pest & Disease Alerts: high-level pest-risk advisory area.
- Sensor Telemetry: latest soil, temperature, rain, wind, and configured sensor nodes.
- OpenAgri Services Status: Gatekeeper, Weather, Irrigation, Pest/Disease, FarmCalendar, Reporting cache status and staleness.
- Farm Calendar / Operations: local task view and future FarmCalendar sync point.
- Reports / PDF Advisories: JSON-LD-style advisory payload preview, safe report stub, and print/export action.
- KPI Dashboard: early operational KPI summary using current local data.
- Settings / Connectivity: links to existing settings and offline cache controls.

## Offline Behaviour

- `manifest.webmanifest` enables installable PWA behavior.
- `sw.js` caches the app shell and last fetched GET responses.
- `assets/hmi.js` saves the last dashboard state in `localStorage`.
- Offline state is shown with a visible banner.
- Report/export actions are queued in browser storage when used.
- The dashboard shows queued operations and uses confidence labels to flag reduced or stale service state.

## Usability Decisions

- The first screen prioritises daily operational decisions rather than detailed analytics.
- Confidence labels are short and color-coded: normal, reduced, stale, unavailable.
- Recommendation cards list input sources in plain language.
- The Digital Twin remains available, but it is not the only way to understand the field state.
- The layout is mobile-first, with large tap targets and no map-only controls on the main screen.
- Navigation uses section anchors so keyboard and screen-reader users can move predictably.
- The report action uses safe JSON and print-to-PDF until live ReportingService credentials and endpoints are configured.

## Limitations

- Dashboard data still comes from the current PHP/MySQL prototype tables and seeded/demo data.
- Live OpenAgri connector responses are not yet rendered directly in the HMI; cache status is inferred from connector cache files.
- FarmCalendar write-back and live ReportingService PDF generation are not wired into production workflows.
- Cesium remains in the existing `field1.php` implementation and still uses external CDN assets.
- Browser offline queues are local to one device until backend sync endpoints are implemented.

