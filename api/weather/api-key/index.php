<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}
if (!agritwin_role_allows('settings')) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}
agritwin_require_csrf();

$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
    $body = $_POST;
}

$result = agritwin_save_weather_api_key(
    (string) ($body['api_key'] ?? ''),
    (string) ($body['transport'] ?? 'bearer'),
    (string) ($body['header_name'] ?? 'X-API-Key'),
    (string) ($body['query_parameter_name'] ?? 'api_key')
);
http_response_code($result['ok'] ? 200 : 422);
echo json_encode([
    'ok' => $result['ok'],
    'message' => $result['message'],
    'weather_api_key' => agritwin_weather_api_key_status(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
