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
        'primary_crop' => 'Almonds',
        'secondary_crops' => ['Pistachios', 'Maize'],
        'timezone' => 'Europe/Madrid',
        'base_path' => '/agritwin3/',
        'data_mode' => 'local demo data',
        'last_sync' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
    ];
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
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    return [
        [
            'id' => 'MAC-ALM-A-SM-01',
            'name' => 'Soil moisture sensor - almond block A',
            'asset' => 'Soil probe A-01',
            'block' => 'Almond block A',
            'crop' => 'Almonds',
            'parameter' => 'soil_moisture',
            'value' => 28.4,
            'unit' => '% VWC',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9492,
            'lon' => -5.6958,
        ],
        [
            'id' => 'MAC-ALM-A-ST-01',
            'name' => 'Soil temperature sensor - almond block A',
            'asset' => 'Soil probe A-01',
            'block' => 'Almond block A',
            'crop' => 'Almonds',
            'parameter' => 'soil_temperature',
            'value' => 18.7,
            'unit' => 'degC',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9491,
            'lon' => -5.6957,
        ],
        [
            'id' => 'MAC-ALM-A-MET-01',
            'name' => 'Air temperature / humidity station - almond block A',
            'asset' => 'Meteo station A',
            'block' => 'Almond block A',
            'crop' => 'Almonds',
            'parameter' => 'air_temperature_humidity',
            'value' => 21.6,
            'unit' => 'degC / 56% RH',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9488,
            'lon' => -5.6962,
        ],
        [
            'id' => 'MAC-ALM-A-LW-01',
            'name' => 'Leaf wetness sensor - almond block A',
            'asset' => 'Leaf wetness A-01',
            'block' => 'Almond block A',
            'crop' => 'Almonds',
            'parameter' => 'leaf_wetness',
            'value' => 18.0,
            'unit' => '%',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9485,
            'lon' => -5.6964,
        ],
        [
            'id' => 'MAC-ALM-B-SM-01',
            'name' => 'Soil moisture sensor - almond block B',
            'asset' => 'Soil probe B-01',
            'block' => 'Almond block B',
            'crop' => 'Almonds',
            'parameter' => 'soil_moisture',
            'value' => 22.1,
            'unit' => '% VWC',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'reduced',
            'lat' => 40.9474,
            'lon' => -5.6931,
        ],
        [
            'id' => 'MAC-PUMP-FLOW-01',
            'name' => 'Irrigation pump flow meter',
            'asset' => 'Pump house flow meter',
            'block' => 'Pump house',
            'crop' => 'Almonds / Pistachios',
            'parameter' => 'pump_flow',
            'value' => 42.5,
            'unit' => 'm3/h',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9480,
            'lon' => -5.6946,
        ],
        [
            'id' => 'MAC-PV-ENERGY-01',
            'name' => 'PV/energy meter for irrigation pump',
            'asset' => 'PV inverter meter',
            'block' => 'Pump house',
            'crop' => 'Farm energy',
            'parameter' => 'pump_energy',
            'value' => 7.8,
            'unit' => 'kWh today',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9481,
            'lon' => -5.6948,
        ],
        [
            'id' => 'MAC-WEATHER-WIND-01',
            'name' => 'Weather station wind/rainfall sensor',
            'asset' => 'Weather station W-01',
            'block' => 'Farm weather mast',
            'crop' => 'All blocks',
            'parameter' => 'wind_rainfall',
            'value' => 14.0,
            'unit' => 'km/h wind, 1.2 mm rain today',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9498,
            'lon' => -5.6940,
        ],
        [
            'id' => 'MAC-PIST-SM-01',
            'name' => 'Soil moisture sensor - pistachio reference row',
            'asset' => 'Soil probe P-01',
            'block' => 'Pistachio reference row',
            'crop' => 'Pistachios',
            'parameter' => 'soil_moisture',
            'value' => 24.8,
            'unit' => '% VWC',
            'timestamp' => $ts,
            'status' => 'healthy',
            'confidence' => 'normal',
            'lat' => 40.9469,
            'lon' => -5.6924,
        ],
    ];
}

function agritwin_weather_source(): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $weather = agritwin_service_cards()['weather'] ?? null;
    if ($weather !== null && $weather['mode'] === 'live' && $weather['status'] === 'healthy') {
        return [
            'label' => 'OpenAgri Weather Data Service - live',
            'mode' => 'live',
            'status' => 'healthy',
            'timestamp' => $weather['last_successful_live_check'] ?? $ts,
            'endpoint' => $weather['endpoint'],
            'latency_ms' => $weather['latency_ms'],
            'reason' => 'Real endpoint returned a successful response.',
        ];
    }

    return [
        'label' => 'Weather source: local demo data',
        'mode' => 'local demo data',
        'status' => 'fallback',
        'timestamp' => $ts,
        'endpoint' => $weather['endpoint'] ?? 'not configured',
        'latency_ms' => null,
        'reason' => $weather['error_summary'] ?? 'OpenAgri Weather live endpoint has not been verified for this local run.',
    ];
}

function agritwin_service_cards(): array
{
    agritwin_load_local_env();
    $services = [
        'gatekeeper' => ['name' => 'OpenAgri Gatekeeper / Access Control', 'env' => 'AGRITWIN_OPENAGRI_GATEKEEPER_URL', 'path' => '/health', 'token_env' => 'AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN'],
        'weather' => ['name' => 'OpenAgri Weather Data Service', 'env' => 'AGRITWIN_OPENAGRI_WEATHER_URL', 'path' => '/api/data/forecast5?lat=40.9492&lon=-5.6958', 'token_env' => 'AGRITWIN_OPENAGRI_WEATHER_TOKEN'],
        'irrigation' => ['name' => 'OpenAgri Irrigation Management Service', 'env' => 'AGRITWIN_OPENAGRI_IRRIGATION_URL', 'path' => '/api/irrigation/advisory', 'token_env' => 'AGRITWIN_OPENAGRI_IRRIGATION_TOKEN'],
        'pest_disease' => ['name' => 'OpenAgri Pest & Disease Management Service', 'env' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_URL', 'path' => '/api/pest/risk', 'token_env' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_TOKEN'],
        'farmcalendar' => ['name' => 'OpenAgri FarmCalendar', 'env' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_URL', 'path' => '/api/calendar/operations', 'token_env' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_TOKEN'],
        'reporting' => ['name' => 'OpenAgri Reporting Service', 'env' => 'AGRITWIN_OPENAGRI_REPORTING_URL', 'path' => '/api/reports', 'token_env' => 'AGRITWIN_OPENAGRI_REPORTING_TOKEN'],
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
    $mode = strtolower((string) (getenv('AGRITWIN_OPENAGRI_MODE') ?: 'mock'));
    $endpoint = rtrim((string) (getenv($service['env']) ?: ''), '/');
    $token = (string) (getenv($service['token_env']) ?: getenv('AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN') ?: '');
    $isLocal = $endpoint !== '' && (str_contains($endpoint, 'localhost') || str_contains($endpoint, '127.0.0.1') || str_contains($endpoint, 'openagri-mock'));

    $base = [
        'key' => $key,
        'name' => $service['name'],
        'endpoint' => $endpoint !== '' ? $endpoint : 'not configured',
        'configured' => $endpoint !== '',
        'mode' => 'not_configured',
        'status' => 'unavailable',
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

    if ($mode !== 'live') {
        return array_merge($base, [
            'mode' => 'mock',
            'status' => 'healthy',
            'latency_ms' => match ($key) {
                'gatekeeper' => 32,
                'weather' => 44,
                'irrigation' => 58,
                'pest_disease' => 63,
                'farmcalendar' => 39,
                default => 71,
            },
            'last_mock_fallback_generation' => $ts,
            'error_summary' => 'Configured for local demo data; no live OpenAgri request was claimed.',
        ]);
    }

    if ($isLocal) {
        return array_merge($base, [
            'mode' => 'mock',
            'status' => 'healthy',
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
            'status' => 'healthy',
            'http_status' => $status,
            'latency_ms' => $latency,
            'last_successful_live_check' => gmdate(DateTimeInterface::ATOM),
            'error_summary' => 'Live endpoint returned a successful response.',
        ]);
    }

    return array_merge($base, [
        'mode' => 'fallback',
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
        'mock' => 'Local demo data',
        'fallback' => 'Fallback data',
        'not_configured' => 'Not configured',
        default => ucfirst(str_replace('_', ' ', $mode)),
    };
}
