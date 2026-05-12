<?php
declare(strict_types=1);

require_once __DIR__ . '/../agritwin_dashboard/includes/hmi_data.php';

$weatherSensor = null;
foreach (agritwin_sensors() as $sensor) {
    if ($sensor['parameter'] === 'wind_rainfall') {
        $weatherSensor = $sensor;
        break;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'source' => agritwin_weather_source(),
    'values' => [
        'air_temperature_c' => 21.6,
        'relative_humidity_pct' => 56,
        'wind_kmh' => 14.0,
        'rainfall_today_mm' => 1.2,
        'sensor' => $weatherSensor,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
