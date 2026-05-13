<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');

$services = agritwin_service_cards();
$mandatory = array_intersect_key($services, array_flip(agritwin_mandatory_openagri_keys()));

$records = array_map(static function (array $service): array {
    return [
        'key' => $service['key'],
        'name' => $service['name'],
        'required_for_d1' => true,
        'required_for_proposal' => true,
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
}, array_values($mandatory));

echo json_encode([
    'group' => 'mandatory',
    'proposal_d1_compliance_basis' => true,
    'services' => $records,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
