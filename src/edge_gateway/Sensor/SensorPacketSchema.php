<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Sensor;

final class SensorPacketSchema
{
    private const PARAMETERS = [
        'soil_moisture' => 'm3_m3',
        'soil_temperature' => 'degC',
        'air_temperature' => 'degC',
        'humidity' => 'percent',
        'leaf_wetness' => 'percent',
        'pump_flow_l_min' => 'l_min',
        'pump_power_kw' => 'kW',
        'pump_energy_kwh' => 'kWh',
    ];

    public function normalize(array $payload, int $samplingIntervalMinutes = 15): array
    {
        foreach (['gateway_id', 'packet_id', 'farm_id', 'observed_at', 'readings'] as $required) {
            if (!isset($payload[$required]) || $payload[$required] === '') {
                throw new \InvalidArgumentException("Missing required sensor packet field: $required");
            }
        }
        if (!is_array($payload['readings']) || $payload['readings'] === []) {
            throw new \InvalidArgumentException('Sensor packet readings must be a non-empty array');
        }

        $readings = [];
        foreach ($payload['readings'] as $reading) {
            if (!is_array($reading)) {
                throw new \InvalidArgumentException('Sensor reading must be an object');
            }
            $parameter = (string) ($reading['parameter'] ?? '');
            if (!array_key_exists($parameter, self::PARAMETERS)) {
                throw new \InvalidArgumentException("Unsupported sensor parameter: $parameter");
            }
            if (!array_key_exists('value', $reading) || !is_numeric($reading['value'])) {
                throw new \InvalidArgumentException("Sensor parameter $parameter requires a numeric value");
            }

            $readings[] = [
                'record_type' => 'sensor_reading',
                'asset_id' => $reading['asset_id'] ?? null,
                'parcel_id' => $reading['parcel_id'] ?? ($payload['parcel_id'] ?? null),
                'parameter' => $parameter,
                'value' => (float) $reading['value'],
                'unit' => $reading['unit'] ?? self::PARAMETERS[$parameter],
                'observed_at' => $reading['observed_at'] ?? $payload['observed_at'],
                'quality' => $reading['quality'] ?? ['flag' => 'ok'],
            ];
        }

        $idempotencyKey = ($payload['gateway_id'] . ':' . $payload['packet_id']);

        return [
            'record_type' => 'sensor_packet',
            'gateway_id' => (string) $payload['gateway_id'],
            'packet_id' => (string) $payload['packet_id'],
            'farm_id' => (string) $payload['farm_id'],
            'parcel_id' => $payload['parcel_id'] ?? null,
            'architecture_mode' => 'mixed',
            'observed_at' => $this->isoTime((string) $payload['observed_at']),
            'received_at' => gmdate('c'),
            'sampling_interval_minutes' => (int) ($payload['sampling_interval_minutes'] ?? $samplingIntervalMinutes),
            'readings' => $readings,
            'sync' => [
                'status' => 'queued',
                'attempts' => 0,
                'idempotency_key' => $idempotencyKey,
            ],
            'source' => [
                'system' => 'edge_gateway',
                'external_id' => $idempotencyKey,
                'connector_version' => 'edge-gateway-0.1.0',
            ],
        ];
    }

    private function isoTime(string $value): string
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Invalid timestamp: $value");
        }
        return gmdate('c', $timestamp);
    }
}
