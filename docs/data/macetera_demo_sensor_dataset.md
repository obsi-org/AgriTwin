# Macotera Demo Sensor Dataset

The AgriTwin HMI uses a synthetic local-demo dataset for 2026-05-12 at the FOTOMACO Macotera pilot farm in Macotera, Salamanca, Castilla y Leon, Spain.

The canonical fixture is `data/demo/macetera_sensors_2026_05_12.json`; the PHP HMI data provider mirrors the same values in `agritwin_dashboard/includes/hmi_data.php`.

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

## Included Sensors

- Soil moisture sensor - almond block A: 28.4% VWC.
- Soil temperature sensor - almond block A: 18.7 degC.
- Air temperature / humidity station - almond block A: 21.6 degC / 56% RH.
- Leaf wetness sensor - almond block A: 18%.
- Soil moisture sensor - almond block B: 22.1% VWC.
- Irrigation pump flow meter: 42.5 m3/h.
- PV/energy meter for irrigation pump: 7.8 kWh today.
- Weather station wind/rainfall sensor: 14 km/h wind, 1.2 mm rainfall today.
- Pistachio reference soil moisture: 24.8% VWC.

## Privacy

This dataset is synthetic. It must not be treated as raw pilot-farm telemetry, validation evidence, or operational advice. Do not replace it with private farm telemetry unless publication and GDPR/data-protection review has cleared the data.
