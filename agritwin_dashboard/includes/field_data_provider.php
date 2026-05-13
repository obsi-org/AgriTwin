<?php
declare(strict_types=1);

require_once __DIR__ . '/hmi_data.php';

function agritwin_field_now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid'));
}

function agritwin_field_local_weather_fallback(DateTimeImmutable $now): array
{
    $hour = (int) $now->format('G');
    $seasonal = 20.5;
    $dayCurve = sin((($hour - 7) / 14) * pi());
    $dayCurve = max(-0.35, min(1.0, $dayCurve));
    $temperature = round($seasonal + ($dayCurve * 7.2), 1);
    $humidity = (int) max(35, min(74, round(68 - (($temperature - 14) * 2.0))));
    $wind = round(8 + (($hour % 6) * 1.7) + ($temperature > 24 ? 3.0 : 0.0), 1);
    $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    $rainfall = $humidity > 68 ? 1.4 : 0.2;
    $solar = ($hour >= 7 && $hour <= 20) ? (int) max(80, min(860, sin((($hour - 7) / 13) * pi()) * 840)) : 0;

    return [
        'source_label' => 'Local fallback',
        'source_status' => 'Local operational dataset',
        'temperature_c' => $temperature,
        'humidity_pct' => $humidity,
        'wind_kmh' => $wind,
        'wind_direction' => $directions[((int) $now->format('z') + $hour) % count($directions)],
        'rainfall_mm' => round($rainfall, 1),
        'solar_radiation_wm2' => $solar,
        'timestamp' => $now->format(DateTimeInterface::ATOM),
        'last_updated_label' => $now->format('H:i'),
    ];
}

function agritwin_field_weather(DateTimeImmutable $now): array
{
    $fallback = agritwin_field_local_weather_fallback($now);
    try {
        $source = agritwin_weather_source();
        $hasValues = isset($source['temperature_c'], $source['humidity_pct'], $source['wind_kmh']);
        if ($hasValues && in_array($source['mode'] ?? '', ['live', 'fallback'], true)) {
            return [
                'source_label' => (string) ($source['source_name'] ?? $source['label'] ?? 'Public weather source'),
                'source_status' => (string) ($source['label'] ?? 'Public weather source'),
                'temperature_c' => round((float) $source['temperature_c'], 1),
                'humidity_pct' => (int) $source['humidity_pct'],
                'wind_kmh' => round((float) $source['wind_kmh'], 1),
                'wind_direction' => $fallback['wind_direction'],
                'rainfall_mm' => round((float) ($source['rainfall_mm'] ?? 0), 1),
                'solar_radiation_wm2' => $fallback['solar_radiation_wm2'],
                'timestamp' => (string) ($source['timestamp'] ?? $now->format(DateTimeInterface::ATOM)),
                'last_updated_label' => $now->format('H:i'),
                'reason' => (string) ($source['reason'] ?? ''),
            ];
        }
    } catch (Throwable) {
        // Keep field rendering independent from external weather availability.
    }
    return $fallback;
}

function agritwin_field_sensor_rows(array $weather, DateTimeImmutable $now): array
{
    $baseTemp = (float) $weather['temperature_c'];
    $baseMoisture = max(17.0, min(33.0, 31.5 - max(0, $baseTemp - 18) * 0.55 + ((float) $weather['rainfall_mm'] * 0.7)));
    $rows = [
        ['Sensor A', 40.8860, -5.2615, -1.2, 0.0, 'MAC-ALM-A'],
        ['Sensor B', 40.8900, -5.2590, -0.4, 1.2, 'MAC-ALM-B'],
        ['Sensor C', 40.8860, -5.2590, 0.1, -0.8, 'MAC-ALM-A'],
        ['Sensor D', 40.8860, -5.2580, 0.4, -1.4, 'MAC-ALM-C'],
        ['Sensor E', 40.8920, -5.2580, -0.7, 1.8, 'MAC-ALM-C'],
        ['Sensor F', 40.8905, -5.2559, 0.2, -0.5, 'MAC-PIST-A'],
        ['Sensor G', 40.8915, -5.2596, -1.8, 1.0, 'MAC-ALM-B'],
        ['Sensor H', 40.8916, -5.2559, 0.5, -1.0, 'MAC-PIST-A'],
        ['Sensor I', 40.8846, -5.2597, 0.8, 0.6, 'MAC-MAIZE-A'],
        ['Sensor J', 40.8910, -5.2609, 0.3, 1.5, 'MAC-ALM-B'],
        ['Sensor L', 40.8871, -5.2603, 0.1, -0.2, 'MAC-ALM-A'],
        ['Sensor M', 40.8871, -5.2568, 0.6, 0.7, 'MAC-MAIZE-A'],
    ];

    return array_map(static function (array $row) use ($baseTemp, $baseMoisture): array {
        [$name, $lat, $lon, $tempDelta, $moistureDelta, $station] = $row;
        $moist10 = round(max(16, min(34, $baseMoisture + $moistureDelta)), 1);
        return [
            'name' => $name,
            'lat' => $lat,
            'lon' => $lon,
            'station' => $station,
            'temp' => round($baseTemp + $tempDelta, 1),
            'moist10' => $moist10,
            'moist30' => round(min(35, $moist10 + 1.6), 1),
            'moist50' => round(min(36, $moist10 + 2.8), 1),
        ];
    }, $rows);
}

function agritwin_field_current_data(?PDO $pdo = null): array
{
    $now = agritwin_field_now();
    $weather = agritwin_field_weather($now);
    $sensors = agritwin_field_sensor_rows($weather, $now);
    $avgMoisture = round(array_sum(array_column($sensors, 'moist10')) / max(1, count($sensors)), 1);
    $lowest = array_reduce($sensors, static fn (?array $carry, array $item): array => $carry === null || $item['moist10'] < $carry['moist10'] ? $item : $carry);
    $tomorrow = $now->modify('+1 day');
    $next48 = $now->modify('+2 days');

    return [
        'generated_at' => $now->format(DateTimeInterface::ATOM),
        'local_date' => $now->format('Y-m-d'),
        'local_time' => $now->format('H:i'),
        'timezone' => 'Europe/Madrid',
        'location_label' => 'Nava de Sotrobal / Macotera, Salamanca, Castilla y Leon, Spain',
        'weather_source' => $weather['source_label'],
        'local_weather_station' => $weather,
        'external_weather' => [
            'source_label' => $weather['source_label'],
            'status' => $weather['source_status'],
        ],
        'sensor_stations' => $sensors,
        'sensor_markers' => $sensors,
        'weather_stations' => [
            [
                'name' => 'Weather Station 1',
                'lat' => 40.8865,
                'lon' => -5.2620,
                'status' => 'Online',
                'temperature' => $weather['temperature_c'],
                'humidity' => $weather['humidity_pct'],
                'windSpeed' => $weather['wind_kmh'],
                'windDirection' => $weather['wind_direction'],
                'rainfall' => $weather['rainfall_mm'],
            ],
            [
                'name' => 'Weather Station 2',
                'lat' => 40.886275,
                'lon' => -5.2579,
                'status' => 'Online',
                'temperature' => round($weather['temperature_c'] - 0.8, 1),
                'humidity' => min(100, (int) $weather['humidity_pct'] + 3),
                'windSpeed' => max(0, round($weather['wind_kmh'] - 1.4, 1)),
                'windDirection' => $weather['wind_direction'],
                'rainfall' => $weather['rainfall_mm'],
            ],
        ],
        'irrigation_status' => $avgMoisture < 22 ? 'Irrigation review required today' : 'Irrigation schedule within target range',
        'pest_disease_status' => ((int) $weather['humidity_pct'] > 65 || (float) $weather['rainfall_mm'] > 1.0) ? 'Moderate monitoring priority' : 'Low monitoring priority',
        'operational_insights' => [
            sprintf('Average near-surface soil moisture is %.1f %% VWC across field markers.', $avgMoisture),
            sprintf('%s is the lowest marker at %.1f %% VWC; review irrigation timing before the evening cycle.', $lowest['name'], $lowest['moist10']),
            sprintf('Weather source: %s. Wind is %.1f km h⁻¹ from %s, with %.1f mm rainfall today.', $weather['source_label'], $weather['wind_kmh'], $weather['wind_direction'], $weather['rainfall_mm']),
        ],
        'task_queue' => [
            'Inspect soil-moisture sensors and compare Almond Block readings today at ' . $now->format('H:i') . '.',
            'Verify irrigation line pressure and pump telemetry before the next scheduled watering window.',
            'Review almond block irrigation schedule on ' . $tomorrow->format('Y-m-d') . '.',
            'Check pest and disease monitoring traps by ' . $next48->format('Y-m-d') . '.',
        ],
        'data_source_status' => [
            'weather' => $weather['source_label'],
            'updated_at' => $weather['timestamp'],
        ],
    ];
}
