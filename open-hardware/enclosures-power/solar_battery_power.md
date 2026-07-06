# Solar and Battery Power

## Power Budget

Estimate:

```text
daily_energy_wh = sleep_power_wh + sensor_power_wh + transmit_power_wh + gateway_margin_wh
```

Then size the battery for the required autonomy period and local temperature range.

## Battery Guidance

- Prefer LiFePO4 chemistry for outdoor safety where feasible.
- Use protected packs and charger modules matched to chemistry.
- Avoid unattended charging with unknown or damaged batteries.
- Provide fusing where appropriate.

## Solar Guidance

- Size panel for winter or low-light periods if year-round operation is required.
- Mount securely and avoid shading.
- Include strain relief and UV-rated cable.
- Monitor battery voltage and report low-power status in telemetry.

## Low-Power Firmware

- Sleep between samples.
- Switch sensors off between readings if supported.
- Reduce transmit retries when battery is low.
- Avoid status LEDs during normal operation.
