<?php
declare(strict_types=1);

require_once __DIR__ . '/../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
echo json_encode([
    'context' => agritwin_farm_context(),
    'stations' => agritwin_sensor_stations(),
    'sensors' => agritwin_sensors(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
