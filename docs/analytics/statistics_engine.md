# AgriTwin Statistics Engine

Date: 2026-05-13

The analytics module is implemented in `agritwin_dashboard/includes/statistics_engine.php` and reuses the central HMI data providers. It avoids duplicating sensor, weather, OpenAgri and KPI values across pages.

## Coverage

- Weather source status and current observations.
- Sensor station comparison across Macotera blocks.
- Soil moisture, soil temperature, air temperature, humidity, wind, rainfall and solar radiation statistics.
- Irrigation flow, pump energy and irrigation deficit estimates.
- Water-stress status based on soil-moisture thresholds.
- Pest/disease risk inputs from leaf wetness, humidity and rainfall.
- OpenAgri service freshness, status and latency.
- D1 KPI records already exposed by the platform.

## Calculations

The engine computes count, minimum, maximum, average, median, latest value and trend direction where applicable. It also reports irrigation deficit and simple anomaly flags when soil moisture is below target.

## UI and API

- Analytics page: `/agritwin3/agritwin_dashboard/analytics.php`
- Summary API: `/agritwin3/api/analytics/summary`
- Weather API: `/agritwin3/api/analytics/weather`
- Sensor station API: `/agritwin3/api/analytics/sensor-stations`
- Irrigation API: `/agritwin3/api/analytics/irrigation`
- Pest/disease API: `/agritwin3/api/analytics/pest-disease`
- KPI API: `/agritwin3/api/analytics/kpis`
- Service freshness API: `/agritwin3/api/analytics/service-freshness`

The graphical page uses local SVG charts rendered by PHP, so it does not require external JavaScript or CDN access.

## Tests

Run:

```bash
make test-analytics
```

The tests cover core descriptive statistics, station comparison and summary generation.
