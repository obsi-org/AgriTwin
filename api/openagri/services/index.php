<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
$services = agritwin_service_cards();
echo json_encode([
    'services' => array_values($services),
    'groups' => array_map(static fn (array $group): array => array_values($group), agritwin_openagri_service_groups($services)),
    'weather_source' => agritwin_weather_source(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
