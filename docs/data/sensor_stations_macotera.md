# Macotera Sensor Stations

Date: 2026-05-12

The AgriTwin HMI now models telemetry as station-level data for Fotovoltaica Macotera SL in Macotera, Salamanca, Castilla y Leon, Spain.

## Stations

| Station | Block | Crop | Data mode |
| --- | --- | --- | --- |
| MAC-ALM-A | Almond block A | Almonds | Generated local station dataset |
| MAC-ALM-B | Almond block B | Almonds | Generated local station dataset |
| MAC-ALM-C | Almond block C | Almonds | Generated local station dataset |
| MAC-PIST-A | Pistachio block A | Pistachios | Generated local station dataset |
| MAC-MAIZE-A | Maize block A | Maize | Generated local station dataset |

## Reading Types

Each station carries realistic May values for soil moisture, soil temperature, air temperature, relative humidity, leaf wetness, rainfall, wind speed and solar radiation. Stations with irrigation assets also include irrigation flow and pump energy.

## Source Status

Station telemetry is synthetic demo data. External weather on the dashboard is sourced in this order:

1. OpenAgri Weather Data Service when live mode is configured and verified.
2. Open-Meteo public weather API as a no-secret live fallback.
3. Local station demo fallback if live sources are unavailable.

No raw private pilot-farm data is included.
