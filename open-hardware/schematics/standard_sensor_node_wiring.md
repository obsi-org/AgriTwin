# Standard Sensor Node Wiring

This reference node uses an Arduino-compatible low-power controller and open sensor probes. Pin names are examples; adapt them to the selected board.

## Wiring Table

| Sensor or module | Signal | Voltage | Example pin | Notes |
| --- | --- | --- | --- | --- |
| Capacitive soil moisture probe A | Analog voltage | 3.3 V | A0 | Add RC filtering if the cable is long. Calibrate dry and wet points. |
| Capacitive soil moisture probe B | Analog voltage | 3.3 V | A1 | Place at a different depth or crop zone if needed. |
| DS18B20 soil temperature probe | 1-Wire data | 3.3 V | GPIO 4 | Use a 4.7 kOhm pull-up resistor to 3.3 V. |
| SHT31/SHT35 air sensor | I2C SDA/SCL | 3.3 V | SDA/SCL | Mount in a ventilated radiation shield. |
| Leaf wetness sensor | Analog or digital | 3.3 V | A2 or GPIO 5 | Record sensor type and wet/dry calibration state. |
| Rain gauge pulse input | Dry contact pulse | 3.3 V pull-up | GPIO 6 interrupt | Debounce in firmware and document mm per tip. |
| RS485 transceiver | UART TX/RX + DE/RE | 3.3 V or 5 V module | UART pins | Use isolated transceiver for longer cable runs. |
| Solar charger status | Analog or digital | Per charger spec | A3 or GPIO 7 | Optional diagnostic signal. |
| Battery voltage divider | Analog voltage | Scaled to ADC | A4 | Use high-value resistors and disable when sleeping if possible. |
| Status LED | Digital output | 3.3 V | GPIO 13 | Disable during normal sleep to save energy. |

## Power Notes

- Prefer 3.3 V sensors to avoid level shifting.
- Switch sensor power using a MOSFET where low-power sleep is required.
- Size battery capacity for expected sleep current, transmission current and autonomy target.
- Protect batteries with charge, discharge, over-current and temperature safeguards.

## Signal Integrity

- Keep analog cable runs short where possible.
- Use shielded cable or differential interfaces for noisy environments.
- Add strain relief and drip loops at enclosure entries.
- Record all calibration coefficients in deployment metadata rather than in public documentation.
