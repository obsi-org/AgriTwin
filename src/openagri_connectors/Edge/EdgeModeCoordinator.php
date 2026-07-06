<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\OpenAgri\Edge;

use AgriTwin\OpenAgri\Support\SyncQueue;

final class EdgeModeCoordinator
{
    public function __construct(private SyncQueue $queue)
    {
    }

    public function queueSensorMeasurement(array $payload): string
    {
        return $this->queue->enqueue('cloud_dss', 'sensor_measurement', $payload, 'sensor');
    }

    public function queueOpenAgriRefresh(string $service, array $payload): string
    {
        return $this->queue->enqueue($service, 'openagri_refresh', $payload, 'openagri');
    }

    public function queueBacklogEvent(string $service, string $eventType, array $payload): string
    {
        return $this->queue->enqueue($service, $eventType, $payload, 'backlog');
    }

    public function pendingWork(): array
    {
        return $this->queue->list('sensor,openagri,backlog');
    }

    public function confidenceFromCacheAge(?int $ageSeconds, int $ttlSeconds): array
    {
        if ($ageSeconds === null) {
            return ['mode' => 'reduced', 'reason' => 'external_service_unavailable'];
        }
        if ($ageSeconds > $ttlSeconds) {
            return ['mode' => 'reduced', 'reason' => 'external_service_stale', 'age_seconds' => $ageSeconds];
        }
        return ['mode' => 'normal', 'age_seconds' => $ageSeconds];
    }
}

