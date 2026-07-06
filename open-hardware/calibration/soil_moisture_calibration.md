# Soil Moisture Calibration

Capacitive soil moisture probes require calibration for sensor type, soil texture, salinity and installation depth.

## Dry/Wet Procedure

1. Record raw ADC value in dry reference medium.
2. Record raw ADC value in saturated reference medium.
3. Install probe at target depth with good soil contact.
4. Collect field reference samples if volumetric water content calibration is required.
5. Fit a linear or polynomial conversion as appropriate.
6. Store coefficients in local deployment configuration.

## Example Linear Conversion

```text
fraction = (dry_adc - measured_adc) / (dry_adc - wet_adc)
soil_moisture_vwc_pct = clamp(fraction * max_vwc_pct, 0, max_vwc_pct)
```

## Metadata to Record

- Probe model or open design revision.
- Installation depth and crop zone.
- Calibration date.
- Calibration method.
- Dry ADC and wet ADC values.
- Conversion formula and valid range.

Do not publish exact site-specific calibration coefficients if they reveal private operational thresholds.
