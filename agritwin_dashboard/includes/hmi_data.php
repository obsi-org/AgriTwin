<?php
declare(strict_types=1);

function agritwin_base_path(): string
{
    return '/agritwin3/agritwin_dashboard/';
}

function agritwin_demo_date(): DateTimeImmutable
{
    return new DateTimeImmutable('2026-05-12 10:30:00', new DateTimeZone('Europe/Madrid'));
}

function agritwin_farm_context(): array
{
    return [
        'site' => 'Fotovoltaica Macotera SL Macotera pilot farm',
        'operator' => 'Fotovoltaica Macotera SL',
        'location' => 'Macotera, Salamanca, Castilla y Leon, Spain',
        'lat' => 40.9492,
        'lon' => -5.6958,
        'primary_crop' => 'Almonds',
        'secondary_crops' => ['Pistachios', 'Maize'],
        'timezone' => 'Europe/Madrid',
        'base_path' => '/agritwin3/',
        'data_mode' => 'local demo data',
        'last_sync' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
    ];
}

function agritwin_hmi_env(string $primary, ?string $fallback = null, ?string $default = null): ?string
{
    $value = getenv($primary);
    if ($value !== false && $value !== '') {
        return $value;
    }
    if ($fallback !== null) {
        $fallbackValue = getenv($fallback);
        if ($fallbackValue !== false && $fallbackValue !== '') {
            return $fallbackValue;
        }
    }
    return $default;
}

function agritwin_load_local_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    $path = dirname(__DIR__, 2) . '/.env';
    if (!is_file($path) || !is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function agritwin_sensors(): array
{
    $sensors = [];
    foreach (agritwin_sensor_stations() as $station) {
        foreach ($station['readings'] as $reading) {
            $sensors[] = [
                'id' => $station['id'] . '-' . strtoupper(str_replace('_', '-', $reading['parameter'])),
                'station_id' => $station['id'],
                'name' => $reading['label'],
                'asset' => $station['name'],
                'block' => $station['block'],
                'crop' => $station['crop'],
                'parameter' => $reading['parameter'],
                'value' => $reading['value'],
                'unit' => $reading['unit'],
                'timestamp' => $station['timestamp'],
                'status' => $station['status'],
                'confidence' => $station['confidence'],
                'lat' => $station['lat'],
                'lon' => $station['lon'],
            ];
        }
    }
    return $sensors;
}

function agritwin_sensor_stations(): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $stationDefaults = [
        'site' => 'Fotovoltaica Macotera SL',
        'location' => 'Macotera, Salamanca, Castilla y Leon, Spain',
        'timestamp' => $ts,
        'status' => 'healthy',
        'confidence' => 'normal',
        'source' => 'generated local station dataset',
    ];
    $stations = [
        [
            'id' => 'MAC-ALM-A',
            'name' => 'Station MAC-ALM-A',
            'block' => 'Almond block A',
            'crop' => 'Almonds',
            'lat' => 40.9492,
            'lon' => -5.6958,
            'readings' => agritwin_station_readings(28.4, 18.7, 21.6, 56, 18, 1.2, 14.0, 690, 34.0, 6.1),
        ],
        [
            'id' => 'MAC-ALM-B',
            'name' => 'Station MAC-ALM-B',
            'block' => 'Almond block B',
            'crop' => 'Almonds',
            'confidence' => 'reduced',
            'lat' => 40.9474,
            'lon' => -5.6931,
            'readings' => agritwin_station_readings(22.1, 19.2, 22.4, 52, 14, 0.8, 16.5, 715, 42.5, 7.8),
        ],
        [
            'id' => 'MAC-ALM-C',
            'name' => 'Station MAC-ALM-C',
            'block' => 'Almond block C',
            'crop' => 'Almonds',
            'lat' => 40.9468,
            'lon' => -5.6967,
            'readings' => agritwin_station_readings(25.6, 18.3, 21.9, 58, 21, 1.0, 13.2, 675, null, null),
        ],
        [
            'id' => 'MAC-PIST-A',
            'name' => 'Station MAC-PIST-A',
            'block' => 'Pistachio block A',
            'crop' => 'Pistachios',
            'lat' => 40.9469,
            'lon' => -5.6924,
            'readings' => agritwin_station_readings(24.8, 19.0, 22.1, 54, 12, 0.6, 15.0, 705, 22.0, 3.6),
        ],
        [
            'id' => 'MAC-MAIZE-A',
            'name' => 'Station MAC-MAIZE-A',
            'block' => 'Maize block A',
            'crop' => 'Maize',
            'lat' => 40.9457,
            'lon' => -5.6918,
            'readings' => agritwin_station_readings(31.2, 20.1, 23.4, 49, 9, 0.4, 18.4, 735, 38.0, 5.4),
        ],
    ];
    return array_map(static fn (array $station): array => array_merge($stationDefaults, $station), $stations);
}

function agritwin_station_readings(float $soilMoisture, float $soilTemp, float $airTemp, int $humidity, int $leafWetness, float $rainfall, float $wind, int $solar, ?float $flow, ?float $energy): array
{
    $readings = [
        ['parameter' => 'soil_moisture', 'label' => 'Soil moisture', 'value' => $soilMoisture, 'unit' => '% VWC'],
        ['parameter' => 'soil_temperature', 'label' => 'Soil temperature', 'value' => $soilTemp, 'unit' => 'degC'],
        ['parameter' => 'air_temperature', 'label' => 'Air temperature', 'value' => $airTemp, 'unit' => 'degC'],
        ['parameter' => 'relative_humidity', 'label' => 'Relative humidity', 'value' => $humidity, 'unit' => '%'],
        ['parameter' => 'leaf_wetness', 'label' => 'Leaf wetness', 'value' => $leafWetness, 'unit' => '%'],
        ['parameter' => 'rainfall', 'label' => 'Rainfall today', 'value' => $rainfall, 'unit' => 'mm'],
        ['parameter' => 'wind_speed', 'label' => 'Wind speed', 'value' => $wind, 'unit' => 'km/h'],
        ['parameter' => 'solar_radiation', 'label' => 'Solar radiation', 'value' => $solar, 'unit' => 'W/m2'],
    ];
    if ($flow !== null) {
        $readings[] = ['parameter' => 'irrigation_flow', 'label' => 'Irrigation flow', 'value' => $flow, 'unit' => 'm3/h'];
    }
    if ($energy !== null) {
        $readings[] = ['parameter' => 'pump_energy', 'label' => 'Pump energy', 'value' => $energy, 'unit' => 'kWh today'];
    }
    return $readings;
}

function agritwin_weather_source(): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $weather = agritwin_service_cards()['weather'] ?? null;
    if ($weather !== null && $weather['mode'] === 'live' && $weather['status'] === 'live_healthy') {
        $stationValues = agritwin_weather_from_station();
        return [
            'label' => 'OpenAgri Weather Data Service - live',
            'source_name' => 'OpenAgri Weather Data Service',
            'mode' => 'live',
            'status' => 'healthy',
            'timestamp' => $weather['last_successful_live_check'] ?? $ts,
            'endpoint' => $weather['endpoint'],
            'latency_ms' => $weather['latency_ms'],
            'reason' => 'Real endpoint returned a successful response.',
            'temperature_c' => $stationValues['temperature_c'],
            'humidity_pct' => $stationValues['humidity_pct'],
            'wind_kmh' => $stationValues['wind_kmh'],
            'rainfall_mm' => $stationValues['rainfall_mm'],
            'forecast_summary' => 'OpenAgri Weather live endpoint was verified. Current display values use the local normalized weather model.',
        ];
    }

    $public = agritwin_public_weather();
    if ($public['ok']) {
        return [
            'label' => 'External weather service: Open-Meteo public forecast - live fallback',
            'source_name' => 'Open-Meteo public forecast',
            'mode' => 'fallback',
            'status' => 'live_fallback',
            'timestamp' => $public['timestamp'],
            'endpoint' => $public['endpoint'],
            'latency_ms' => $public['latency_ms'],
            'reason' => 'OpenAgri Weather is not live-verified; no-secret public weather fallback returned current data.',
            'temperature_c' => $public['temperature_c'],
            'humidity_pct' => $public['humidity_pct'],
            'wind_kmh' => $public['wind_kmh'],
            'rainfall_mm' => $public['rainfall_mm'],
            'forecast_summary' => $public['forecast_summary'],
        ];
    }

    $stationValues = agritwin_weather_from_station();
    return [
        'label' => 'Weather source: local demo data',
        'source_name' => 'Local demo fallback',
        'mode' => 'local demo data',
        'status' => 'fallback',
        'timestamp' => $ts,
        'endpoint' => $weather['endpoint'] ?? 'not configured',
        'latency_ms' => null,
        'reason' => ($weather['error_summary'] ?? 'OpenAgri Weather live endpoint has not been verified for this local run.') . ' Public weather fallback was unavailable.',
        'temperature_c' => $stationValues['temperature_c'],
        'humidity_pct' => $stationValues['humidity_pct'],
        'wind_kmh' => $stationValues['wind_kmh'],
        'rainfall_mm' => $stationValues['rainfall_mm'],
        'forecast_summary' => 'Local fallback values from generated station data.',
    ];
}

function agritwin_public_weather(): array
{
    agritwin_load_local_env();
    $preference = strtolower((string) agritwin_hmi_env('AGRITWIN_WEATHER_SOURCE_PREFERENCE', null, 'auto'));
    if ($preference === 'local_demo') {
        return ['ok' => false, 'error' => 'public_weather_disabled'];
    }
    $context = agritwin_farm_context();
    $url = sprintf(
        'https://api.open-meteo.com/v1/forecast?latitude=%.4F&longitude=%.4F&current=temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m&daily=weather_code,precipitation_sum&timezone=Europe%%2FMadrid&forecast_days=1',
        $context['lat'],
        $context['lon']
    );
    $started = microtime(true);
    $raw = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]));
    $latency = (int) round((microtime(true) - $started) * 1000);
    $status = agritwin_http_status($http_response_header ?? []);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if ($status < 200 || $status >= 300 || !is_array($data) || !isset($data['current'])) {
        return ['ok' => false, 'error' => $status > 0 ? 'http_' . $status : 'request_failed'];
    }
    $current = $data['current'];
    return [
        'ok' => true,
        'endpoint' => 'https://api.open-meteo.com/v1/forecast',
        'latency_ms' => $latency,
        'timestamp' => isset($current['time']) ? $current['time'] . ':00+02:00' : gmdate(DateTimeInterface::ATOM),
        'temperature_c' => (float) ($current['temperature_2m'] ?? 0),
        'humidity_pct' => (int) ($current['relative_humidity_2m'] ?? 0),
        'wind_kmh' => (float) ($current['wind_speed_10m'] ?? 0),
        'rainfall_mm' => (float) ($current['precipitation'] ?? ($data['daily']['precipitation_sum'][0] ?? 0)),
        'forecast_summary' => 'Open-Meteo current conditions for Macotera/Salamanca with no API key required.',
    ];
}

function agritwin_weather_from_station(): array
{
    $stations = agritwin_sensor_stations();
    $first = $stations[0];
    $values = [];
    foreach ($first['readings'] as $reading) {
        $values[$reading['parameter']] = $reading['value'];
    }
    return [
        'temperature_c' => (float) ($values['air_temperature'] ?? 21.6),
        'humidity_pct' => (int) ($values['relative_humidity'] ?? 56),
        'wind_kmh' => (float) ($values['wind_speed'] ?? 14.0),
        'rainfall_mm' => (float) ($values['rainfall'] ?? 1.2),
    ];
}

function agritwin_service_cards(): array
{
    agritwin_load_local_env();
    $services = [
        'gatekeeper' => ['name' => 'OpenAgri Gatekeeper / Access Control', 'env' => 'OPENAGRI_GATEKEEPER_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_GATEKEEPER_URL', 'path' => '/health', 'token_env' => 'OPENAGRI_GATEKEEPER_CLIENT_SECRET', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN'],
        'weather' => ['name' => 'OpenAgri Weather Data Service', 'env' => 'OPENAGRI_WEATHER_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_WEATHER_URL', 'path' => '/api/data/forecast5?lat=40.9492&lon=-5.6958', 'token_env' => 'OPENAGRI_WEATHER_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_WEATHER_TOKEN'],
        'irrigation' => ['name' => 'OpenAgri Irrigation Management Service', 'env' => 'OPENAGRI_IRRIGATION_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_IRRIGATION_URL', 'path' => '/api/irrigation/advisory', 'token_env' => 'OPENAGRI_IRRIGATION_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_IRRIGATION_TOKEN'],
        'pest_disease' => ['name' => 'OpenAgri Pest & Disease Management Service', 'env' => 'OPENAGRI_PEST_DISEASE_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_URL', 'path' => '/api/pest/risk', 'token_env' => 'OPENAGRI_PEST_DISEASE_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_TOKEN'],
        'farmcalendar' => ['name' => 'OpenAgri FarmCalendar', 'env' => 'OPENAGRI_FARMCALENDAR_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_URL', 'path' => '/api/calendar/operations', 'token_env' => 'OPENAGRI_FARMCALENDAR_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_TOKEN'],
        'reporting' => ['name' => 'OpenAgri Reporting Service', 'env' => 'OPENAGRI_REPORTING_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_REPORTING_URL', 'path' => '/api/reports', 'token_env' => 'OPENAGRI_REPORTING_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_REPORTING_TOKEN'],
        'geodatabase' => ['name' => 'OpenAgri Geodatabase / OCSM', 'env' => 'OPENAGRI_GEODATABASE_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_GEODATABASE_URL', 'path' => '/health', 'token_env' => 'OPENAGRI_GEODATABASE_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_GEODATABASE_TOKEN'],
    ];
    $cards = [];
    foreach ($services as $key => $service) {
        $cards[$key] = agritwin_check_openagri_service($key, $service);
    }
    return $cards;
}

function agritwin_check_openagri_service(string $key, array $service): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $rawMode = strtolower((string) agritwin_hmi_env('OPENAGRI_MODE', 'AGRITWIN_OPENAGRI_MODE', 'demo'));
    $mode = $rawMode === 'mock' ? 'demo' : $rawMode;
    $endpoint = rtrim((string) agritwin_hmi_env($service['env'], $service['legacy_env'] ?? null, ''), '/');
    $token = (string) (agritwin_hmi_env($service['token_env'], $service['legacy_token_env'] ?? null, '') ?: agritwin_hmi_env('AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN', null, ''));
    $isLocal = $endpoint !== '' && (str_contains($endpoint, 'localhost') || str_contains($endpoint, '127.0.0.1') || str_contains($endpoint, 'openagri-mock'));

    $base = [
        'key' => $key,
        'name' => $service['name'],
        'endpoint' => $endpoint !== '' ? $endpoint : 'not configured',
        'configured' => $endpoint !== '',
        'mode' => 'not_configured',
        'status' => 'not_configured',
        'http_status' => null,
        'latency_ms' => null,
        'last_successful_live_check' => null,
        'last_mock_fallback_generation' => null,
        'last_check' => $ts,
        'error_summary' => 'Endpoint is not configured.',
    ];

    if ($endpoint === '') {
        return $base;
    }

    if ($mode === 'demo') {
        return array_merge($base, [
            'mode' => 'demo',
            'status' => 'demo_healthy',
            'latency_ms' => match ($key) {
                'gatekeeper' => 32,
                'weather' => 44,
                'irrigation' => 58,
                'pest_disease' => 63,
                'farmcalendar' => 39,
                'geodatabase' => 48,
                default => 71,
            },
            'last_mock_fallback_generation' => $ts,
            'error_summary' => 'Configured for local demo data; no live OpenAgri request was claimed.',
        ]);
    }

    if ($isLocal) {
        return array_merge($base, [
            'mode' => 'demo',
            'status' => 'demo_healthy',
            'latency_ms' => 45,
            'last_mock_fallback_generation' => $ts,
            'error_summary' => 'Endpoint points to a local mock service, so it is not treated as live.',
        ]);
    }

    $url = $endpoint . $service['path'];
    $started = microtime(true);
    $headers = "Accept: application/json\r\n";
    if ($token !== '') {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 4,
            'ignore_errors' => true,
            'header' => $headers,
        ],
    ]);
    $raw = @file_get_contents($url, false, $context);
    $latency = (int) round((microtime(true) - $started) * 1000);
    $status = agritwin_http_status($http_response_header ?? []);

    if ($status === 401 || $status === 403) {
        return array_merge($base, [
            'mode' => 'live',
            'status' => 'unauthenticated',
            'http_status' => $status,
            'latency_ms' => $latency,
            'error_summary' => 'Live endpoint rejected the request; authentication is required or invalid.',
        ]);
    }

    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if ($status >= 200 && $status < 300 && (is_array($decoded) || $raw !== false)) {
        return array_merge($base, [
            'mode' => 'live',
            'status' => 'live_healthy',
            'http_status' => $status,
            'latency_ms' => $latency,
            'last_successful_live_check' => gmdate(DateTimeInterface::ATOM),
            'error_summary' => 'Live endpoint returned a successful response.',
        ]);
    }

    return array_merge($base, [
        'mode' => $mode === 'auto' ? 'fallback' : 'live',
        'status' => 'unavailable',
        'http_status' => $status ?: null,
        'latency_ms' => $latency,
        'last_mock_fallback_generation' => $ts,
        'error_summary' => $status > 0 ? 'Live endpoint returned HTTP ' . $status . '; local demo data remains active.' : 'Live endpoint could not be reached; local demo data remains active.',
    ]);
}

function agritwin_http_status(array $headers): int
{
    foreach ($headers as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', (string) $header, $matches)) {
            return (int) $matches[1];
        }
    }
    return 0;
}

function agritwin_operations(): array
{
    return [
        ['time' => '2026-05-12T07:30:00+02:00', 'operation' => 'Irrigation pump inspection', 'block' => 'Pump house', 'status' => 'completed'],
        ['time' => '2026-05-12T09:00:00+02:00', 'operation' => 'Almond block A sensor check', 'block' => 'Almond block A', 'status' => 'completed'],
        ['time' => '2026-05-12T12:30:00+02:00', 'operation' => 'Almond block B irrigation review', 'block' => 'Almond block B', 'status' => 'planned'],
        ['time' => '2026-05-12T17:00:00+02:00', 'operation' => 'Pest scouting pheromone trap check', 'block' => 'Almond buffer rows', 'status' => 'planned'],
    ];
}

function agritwin_recommendations(): array
{
    return [
        'water_stress' => ['label' => 'Moderate in block B', 'value' => '22.1% VWC', 'confidence' => 'reduced'],
        'irrigation' => ['label' => 'Irrigate almond block B for 35 minutes after 18:30 if wind remains below 18 km/h.', 'confidence' => 'normal'],
        'pest' => ['label' => 'Low to moderate fungal pressure; leaf wetness is low but continue scouting after light rain.', 'risk' => '34/100', 'confidence' => 'normal'],
        'nitrogen' => ['label' => 'No immediate nitrogen correction. Keep next fertigation review on 2026-05-14.', 'confidence' => 'normal'],
    ];
}

function agritwin_kpis(): array
{
    return [
        ['metric' => 'Irrigation schedule latency', 'value' => '38 s', 'status' => 'within D1 target'],
        ['metric' => 'Water-stress MAE demo', 'value' => '0.002 m3/m3', 'status' => 'synthetic sample'],
        ['metric' => 'Data continuity', 'value' => '100%', 'status' => 'local demo packets on time'],
        ['metric' => 'Network use', 'value' => '23.8 MB/h', 'status' => 'mixed edge-cloud sample'],
        ['metric' => 'Forecast-cycle energy', 'value' => '0.009 kWh', 'status' => 'synthetic estimate'],
    ];
}

function agritwin_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function agritwin_mode_label(string $mode): string
{
    return match ($mode) {
        'live' => 'Live service',
        'demo' => 'Demo service',
        'mock' => 'Local demo data',
        'fallback' => 'Fallback data',
        'not_configured' => 'Not configured',
        'local demo data' => 'Local demo data',
        default => ucfirst(str_replace('_', ' ', $mode)),
    };
}
