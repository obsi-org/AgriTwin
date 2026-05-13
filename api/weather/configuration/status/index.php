<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
echo json_encode([
    'weather_api_key' => agritwin_weather_api_key_status(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
