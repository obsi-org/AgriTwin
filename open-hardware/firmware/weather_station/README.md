# Weather Station Firmware

`agritwin_weather_station_example.ino` provides a generic weather station telemetry example:

- air temperature and humidity;
- rainfall pulse accumulation;
- wind speed and direction;
- solar radiation;
- atmospheric pressure;
- battery voltage and station status.

The output payload follows `../../data-protocols/weather_payload_schema.json`.

Real station calibration constants, network credentials and deployment identifiers must be configured outside git.
