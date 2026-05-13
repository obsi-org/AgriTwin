<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');

$services = agritwin_service_cards();
$core = array_intersect_key($services, array_flip(agritwin_mandatory_openagri_keys()));

$records = array_map(static function (array $service): array {
    return [
        'key' => $service['key'],
        'name' => $service['name'],
        'core_service' => true,
        'integration_priority' => 'core',
        'role' => $service['role'],
        'status' => $service['status'],
        'mode' => $service['mode'],
        'endpoint' => $service['endpoint'],
        'api_docs' => $service['docs_url'],
        'source_repository' => $service['source_repository'],
        'last_check' => $service['last_check'],
        'last_successful_live_check' => $service['last_successful_live_check'],
        'latency_ms' => $service['latency_ms'],
        'http_status' => $service['http_status'],
        'diagnostics' => $service['diagnostics'],
    ];
}, array_values($core));

echo json_encode([
    'integration_group' => 'core',
    'core_service_basis' => true,
    'services' => $records,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
