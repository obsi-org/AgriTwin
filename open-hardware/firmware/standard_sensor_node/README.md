# Standard Sensor Node Firmware

`agritwin_sensor_node_example.ino` shows the structure of a generic AgriTwin sensor-node firmware:

- read soil moisture, soil temperature, air temperature, humidity, leaf wetness and battery voltage;
- apply calibration coefficients stored locally on the device;
- build a JSON payload compatible with `../../data-protocols/sensor_payload_schema.json`;
- publish through MQTT or an edge gateway transport function;
- enter sleep between samples.

Replace all placeholder values before deployment. Store real credentials outside source control.

## Calibration Parameters

The example includes placeholder constants:

- dry and wet ADC points for each capacitive soil moisture probe;
- battery voltage divider ratio;
- optional leaf wetness scaling;
- node and deployment identifiers.

Use the calibration guide before field use.
