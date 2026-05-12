# Macotera Demo Sensor Dataset

The AgriTwin HMI uses a synthetic local-demo dataset for 2026-05-12 at the Fotovoltaica Macotera SL Macotera pilot farm in Macotera, Salamanca, Castilla y Leon, Spain.

The canonical fixture is `data/demo/macetera_sensors_2026_05_12.json`; the PHP HMI data provider mirrors the same station values in `agritwin_dashboard/includes/hmi_data.php`.

## Assumptions

- Date/time: `2026-05-12T10:30:00+02:00`, Europe/Madrid.
- Main crop: almonds.
- Secondary references: pistachios and maize where useful.
- Air temperature: late-spring Salamanca range around 16-26 degC.
- Relative humidity: 40-70%.
- Soil moisture: 18-34% VWC depending on block.
- Soil temperature: 15-22 degC.
- Wind: 6-22 km/h.
- Rainfall today: 0-4 mm.
- Pump flow and PV energy values are plausible local-demo values, not measured pilot records.

## Included Stations

- MAC-ALM-A: almond block A station with soil, weather, leaf wetness, irrigation flow and pump energy readings.
- MAC-ALM-B: almond block B station with reduced-confidence water-stress context.
- MAC-ALM-C: almond block C station with soil and microclimate readings.
- MAC-PIST-A: pistachio block A station with irrigation flow and pump energy readings.
- MAC-MAIZE-A: maize block A station with irrigation flow and pump energy readings.

## External Weather

Dashboard weather is not treated as station-only data. AgriTwin first attempts configured OpenAgri Weather live access, then uses the no-secret Open-Meteo public forecast as a truthful live fallback, and only then falls back to local station demo data.

## Privacy

This dataset is synthetic. It must not be treated as raw pilot-farm telemetry, validation evidence, or operational advice. Do not replace it with private farm telemetry unless publication and GDPR/data-protection review has cleared the data.
