<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'unauthenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'method_not_allowed']);
    exit();
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);
if (!is_array($payload)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'invalid_json']);
    exit();
}

$response = [
    'status' => 'queued',
    'message' => 'Reporting Service request payload accepted locally. Configure OpenAgri ReportingService to generate a PDF.',
    'report_type' => $payload['@type'] ?? 'AgriTwinAdvisoryReportRequest',
    'created_at' => gmdate('c'),
    'payload_structure' => [
        '@context',
        '@type',
        'farm',
        'cards',
        'serviceStatuses',
        'requestedReports',
        'privacy',
    ],
];

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
