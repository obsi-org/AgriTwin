# Weather Station Wiring

AgriTwin supports both discrete open weather sensors and compact RS485/Modbus weather stations. Use the payload schema in `../data-protocols/weather_payload_schema.json` for telemetry output.

## Discrete Sensor Wiring

| Sensor | Signal | Voltage | Example pin | Notes |
| --- | --- | --- | --- | --- |
| Air temperature and humidity | I2C SDA/SCL | 3.3 V | SDA/SCL | Place in a radiation shield. |
| Tipping bucket rain gauge | Dry contact pulse | 3.3 V pull-up | GPIO interrupt | Store `rain_mm_per_tip` in configuration. |
| Anemometer | Pulse frequency | 3.3 V pull-up | GPIO interrupt | Store slope and offset in calibration metadata. |
| Wind vane | Analog voltage or resistor ladder | 3.3 V | A0 | Record north alignment offset. |
| Solar radiation sensor | Analog or I2C | Per sensor spec | A1 or I2C | State whether values are calibrated or relative. |
| Pressure sensor | I2C | 3.3 V | SDA/SCL | Optional for local diagnostics. |

## RS485/Modbus Option

| Interface | Signal | Voltage | Example connection | Notes |
| --- | --- | --- | --- | --- |
| RS485 A/B | Differential data | Per transceiver spec | RS485 adapter | Use twisted pair and termination as required. |
| Sensor power | DC supply | Per station spec | Fused output | Protect outdoor cable runs. |
| Ground/reference | Common reference | Per station spec | Controller ground | Follow vendor and local safety guidance. |

## Siting Notes

- Avoid shade, reflective walls, hot roofs and irrigation spray.
- Keep the rain gauge level and accessible for cleaning.
- Mount wind sensors above local obstructions where feasible.
- Record instrument height, sensor orientation and calibration date.
