<?php
declare(strict_types=1);

require_once __DIR__ . '/../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
echo json_encode([
    'services' => array_values(agritwin_service_cards()),
    'weather_source' => agritwin_weather_source(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
