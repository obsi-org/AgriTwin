<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
echo json_encode([
    'recommendation' => agritwin_recommendations()['irrigation'],
    'soil_moisture_inputs' => array_values(array_filter(agritwin_sensors(), static fn (array $sensor): bool => $sensor['parameter'] === 'soil_moisture')),
    'weather_source' => agritwin_weather_source(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
