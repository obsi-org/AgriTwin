<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

require_once __DIR__ . '/../../src/edge_gateway/autoload.php';

use AgriTwin\Edge\Sensor\SensorPacketSchema;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode((string) $raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_json']);
    exit;
}

try {
    $packet = ($payload['record_type'] ?? null) === 'sensor_packet'
        ? $payload
        : (new SensorPacketSchema())->normalize($payload, (int) (getenv('AGRITWIN_EDGE_SAMPLING_INTERVAL_MINUTES') ?: 15));

    $idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? $packet['sync']['idempotency_key'] ?? null;
    if (!$idempotencyKey) {
        throw new InvalidArgumentException('Missing idempotency key');
    }

    $dir = __DIR__ . '/../../var/cloud_dss/sensor_packets';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $path = $dir . '/' . preg_replace('/[^a-zA-Z0-9_.:-]/', '_', $idempotencyKey) . '.json';
    if (is_file($path)) {
        http_response_code(409);
        echo json_encode(['ok' => true, 'status' => 'duplicate', 'idempotency_key' => $idempotencyKey]);
        exit;
    }

    $packet['sync']['status'] = 'synced';
    $packet['sync']['synced_at'] = gmdate('c');
    file_put_contents($path, json_encode($packet, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

    http_response_code(202);
    echo json_encode(['ok' => true, 'status' => 'accepted', 'idempotency_key' => $idempotencyKey]);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
