<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Inference;

use AgriTwin\Edge\Storage\ResponseCache;

final class LocalFallbackEngine
{
    public function __construct(private ResponseCache $cache, private int $staleSeconds)
    {
    }

    public function recommendation(array $sensorPacket): array
    {
        $soilMoisture = $this->reading($sensorPacket, 'soil_moisture');
        $airTemperature = $this->reading($sensorPacket, 'air_temperature');
        $humidity = $this->reading($sensorPacket, 'humidity');
        $leafWetness = $this->reading($sensorPacket, 'leaf_wetness');
        $weatherStale = $this->cache->isStale('weather', $this->staleSeconds);
        $irrigationStale = $this->cache->isStale('irrigation', $this->staleSeconds);
        $pestStale = $this->cache->isStale('pest_disease', $this->staleSeconds);

        $waterBalance = $this->waterBalance($soilMoisture, $airTemperature);
        $pestRisk = $this->pestRisk($airTemperature, $humidity, $leafWetness);
        $reduced = $weatherStale || $irrigationStale || $pestStale;

        return [
            'record_type' => 'recommendation',
            'recommendation_id' => 'edge-' . hash('sha1', ($sensorPacket['sync']['idempotency_key'] ?? json_encode($sensorPacket))),
            'farm_id' => $sensorPacket['farm_id'] ?? null,
            'parcel_id' => $sensorPacket['parcel_id'] ?? null,
            'domain' => 'mixed_edge_cloud_advisory',
            'architecture_mode' => 'mixed',
            'action' => [
                'type' => $waterBalance['action'],
                'value' => $waterBalance['duration_minutes'],
                'unit' => 'minutes',
                'valid_until' => gmdate('c', time() + 3600),
            ],
            'water_balance' => $waterBalance,
            'pest_risk' => $pestRisk,
            'confidence' => [
                'mode' => $reduced ? 'reduced' : 'normal',
                'score' => $reduced ? 0.52 : 0.72,
                'reasons' => $reduced
                    ? ['local_sensor_rule', 'stale_or_missing_openagri_indicator']
                    : ['local_sensor_rule', 'fresh_openagri_cache'],
            ],
            'inputs' => [
                'sensors' => array_column($sensorPacket['readings'] ?? [], 'parameter'),
                'openagri_cache' => [
                    'weather_stale' => $weatherStale,
                    'irrigation_stale' => $irrigationStale,
                    'pest_disease_stale' => $pestStale,
                ],
            ],
            'source' => [
                'system' => 'edge_gateway',
                'method' => 'local_rule_based_fallback',
            ],
        ];
    }

    private function waterBalance(?float $soilMoisture, ?float $airTemperature): array
    {
        $threshold = 0.22;
        $temperatureFactor = $airTemperature !== null && $airTemperature >= 30.0 ? 1.25 : 1.0;
        if ($soilMoisture === null) {
            return ['water_balance_mm' => null, 'action' => 'hold_for_cloud_dss', 'duration_minutes' => 0, 'reason' => 'missing_soil_moisture'];
        }
        $deficit = max(0.0, ($threshold - $soilMoisture) * 100.0 * $temperatureFactor);
        return [
            'water_balance_mm' => -$deficit,
            'action' => $deficit > 0 ? 'irrigate' : 'hold_irrigation',
            'duration_minutes' => $deficit > 0 ? (int) min(60, max(10, round($deficit * 3))) : 0,
            'reason' => $deficit > 0 ? 'soil_moisture_below_local_threshold' : 'soil_moisture_above_local_threshold',
        ];
    }

    private function pestRisk(?float $airTemperature, ?float $humidity, ?float $leafWetness): array
    {
        $gdd = $airTemperature === null ? null : max(0.0, $airTemperature - 10.0);
        $risk = 20.0;
        if ($gdd !== null) {
            $risk += min(35.0, $gdd * 1.5);
        }
        if ($humidity !== null && $humidity >= 75.0) {
            $risk += 20.0;
        }
        if ($leafWetness !== null && $leafWetness >= 50.0) {
            $risk += 20.0;
        }
        $risk = min(100.0, $risk);

        return [
            'risk_index' => $risk,
            'gdd' => $gdd,
            'risk_class' => $risk >= 70 ? 'high' : ($risk >= 45 ? 'medium' : 'low'),
            'alert_threshold' => 70,
            'method' => 'degree_day_leaf_wetness_rule',
        ];
    }

    private function reading(array $packet, string $parameter): ?float
    {
        foreach ($packet['readings'] ?? [] as $reading) {
            if (($reading['parameter'] ?? null) === $parameter && isset($reading['value'])) {
                return (float) $reading['value'];
            }
        }
        return null;
    }
}
