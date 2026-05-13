# Analytics Module

Date: 2026-05-13

The Analytics page at `/agritwin3/agritwin_dashboard/analytics.php` has been redesigned as an operational analytics workspace for the Fotovoltaica Macotera SL pilot context in Macotera, Salamanca.

## Structure

- Executive Summary: active stations, average soil moisture, weather source, pest risk and data freshness.
- Weather and Microclimate: multi-series weather trends, rainfall/temperature combined chart and forecast cards.
- Soil and Sensor Stations: soil moisture/temperature trends, irrigation-need ranking and latest-reading table.
- Irrigation Performance: water-balance score, deficit estimate, pump energy and recommendation timeline.
- Pest & Disease Risk: risk gauge, risk trend and growing-degree-day context.
- Operational KPIs: D1 operational performance indicators.
- OpenAgri Data and Service Freshness: service freshness, latency and D1 relevance.

## Data Sources

The module uses existing AgriTwin providers:

- `agritwin_sensor_stations()` for Macotera station readings.
- `agritwin_weather_source()` for Weather/OpenAgri/public weather status.
- `agritwin_service_cards()` for OpenAgri service freshness.
- `agritwin_recommendations()` and `agritwin_kpis()` for advisory and D1 metrics.

Where live upstream data is unavailable, the UI labels the source as a local operational dataset or local fallback data.

## Filters

The page supports:

- Time range: `24h`, `7d`, `30d`.
- Station selector.
- Crop/block selector.
- Refresh action.
- JSON export link to `/agritwin3/api/analytics/summary`.

## Extension

New metrics should be added to `agritwin_dashboard/includes/statistics_engine.php` and surfaced through the corresponding `/api/analytics/*` endpoint before being rendered in the HMI.
