<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Recommendation;

use AgriTwin\Edge\Config\EdgeConfig;
use AgriTwin\Edge\Inference\LocalFallbackEngine;
use AgriTwin\Edge\Sensor\SensorPacketSchema;
use AgriTwin\Edge\Storage\DurableQueue;

final class RecommendationApi
{
    public function __construct(
        private EdgeConfig $config,
        private SensorPacketSchema $schema,
        private DurableQueue $queue,
        private LocalFallbackEngine $fallback
    ) {
    }

    public function ingestSensorPacket(array $payload): array
    {
        $packet = $this->schema->normalize($payload, $this->config->samplingIntervalMinutes);
        $queueId = $this->queue->enqueue('sensor_packet', $packet, 'sensor', $packet['sync']['idempotency_key']);
        $recommendation = $this->fallback->recommendation($packet);

        return [
            'record_type' => 'edge_ingestion_result',
            'queue_id' => $queueId,
            'packet' => $packet,
            'recommendation' => $recommendation,
            'api_surface' => 'canonical_recommendation_v1',
        ];
    }
}
