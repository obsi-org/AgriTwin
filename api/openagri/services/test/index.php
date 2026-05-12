<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');

$key = $_POST['service'] ?? $_GET['service'] ?? '';
$services = agritwin_service_cards();
if (!is_string($key) || !isset($services[$key])) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'service_not_found']);
    exit;
}

echo json_encode([
    'ok' => in_array($services[$key]['status'], ['live_healthy', 'local_fallback_active'], true),
    'service' => $services[$key],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
