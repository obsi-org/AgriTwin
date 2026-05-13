<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}
if (!agritwin_role_allows('settings')) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    agritwin_require_csrf();
}

$result = agritwin_remove_weather_api_key();
echo json_encode([
    'ok' => $result['ok'],
    'message' => $result['message'],
    'weather_api_key' => agritwin_weather_api_key_status(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
