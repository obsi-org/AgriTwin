<?php
declare(strict_types=1);

require_once __DIR__ . '/../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
echo json_encode([
    'source' => agritwin_weather_source(),
    'on_farm_station_readings' => array_values(array_filter(agritwin_sensors(), static fn (array $sensor): bool => in_array($sensor['parameter'], ['air_temperature', 'relative_humidity', 'rainfall', 'wind_speed', 'solar_radiation'], true))),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
