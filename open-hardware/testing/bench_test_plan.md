# Bench Test Plan

## Electronics

- Verify controller boots reliably.
- Confirm sensor supply voltage.
- Confirm no sensor pin exceeds controller voltage limits.
- Confirm enclosure cable glands fit planned cables.

## Firmware

- Compile firmware examples after replacing placeholders locally.
- Confirm JSON payload structure.
- Confirm low battery status logic.
- Confirm sleep and wake cycle.

## Connectivity

- Publish to a local MQTT broker or HTTP receiver.
- Validate payload against JSON Schema.
- Confirm queued retry behaviour if network is unavailable.

## Power

- Measure sleep current.
- Measure active sampling current.
- Measure transmit current.
- Estimate battery autonomy.
