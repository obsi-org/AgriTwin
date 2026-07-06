<?php
declare(strict_types=1);

$service = getenv('OPENAGRI_LOCAL_SERVICE') ?: 'openagri';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
    http_response_code(204);
    exit;
}

function respond_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function service_status(string $service): array
{
    return [
        'service' => $service,
        'status' => 'available',
        'source' => 'local OpenAgri-compatible runtime',
        'generated_at' => gmdate(DateTimeInterface::ATOM),
    ];
}

if (in_array($path, ['/', '/health', '/docs'], true)) {
    respond_json(service_status($service) + [
        'documentation' => $path === '/docs' ? 'OpenAgri local service endpoint reference' : null,
    ]);
}

switch ($service) {
    case 'gatekeeper':
        if ($path === '/api/login/') {
            respond_json([
                'success' => true,
                'access' => 'local-openagri-access-token',
                'refresh' => 'local-openagri-refresh-token',
            ]);
        }
        if ($path === '/api/me/') {
            respond_json([
                'username' => 'local_service_user',
                'roles' => ['operator'],
            ]);
        }
        if ($path === '/api/service_directory/') {
            respond_json([
                'services' => ['weather', 'irrigation', 'pest_disease', 'farmcalendar', 'reporting'],
            ]);
        }
        break;

    case 'weather':
        if (str_starts_with($path, '/api/data/forecast5') || str_starts_with($path, '/api/data/weather')) {
            respond_json([
                [
                    'value' => 24.6,
                    'timestamp' => gmdate(DateTimeInterface::ATOM),
                    'source' => 'openagri-weather-local',
                    'measurement_type' => 'ambient_temperature',
                ],
                [
                    'value' => 0.4,
                    'timestamp' => gmdate(DateTimeInterface::ATOM),
                    'source' => 'openagri-weather-local',
                    'measurement_type' => 'rainfall',
                ],
                [
                    'value' => 12.2,
                    'timestamp' => gmdate(DateTimeInterface::ATOM),
                    'source' => 'openagri-weather-local',
                    'measurement_type' => 'wind_speed',
                ],
                [
                    'value' => 5.1,
                    'timestamp' => gmdate(DateTimeInterface::ATOM),
                    'source' => 'openagri-weather-local',
                    'measurement_type' => 'et0',
                ],
                [
                    'value' => 13.8,
                    'timestamp' => gmdate(DateTimeInterface::ATOM),
                    'source' => 'openagri-weather-local',
                    'measurement_type' => 'gdd',
                ],
            ]);
        }
        break;

    case 'irrigation':
        if ($path === '/api/irrigation/advisory') {
            respond_json([
                'status' => 'available',
                'recommendation' => 'Review almond block irrigation window before evening operation.',
                'confidence' => 0.82,
                'generated_at' => gmdate(DateTimeInterface::ATOM),
            ]);
        }
        if (str_starts_with($path, '/api/v1/eto/get-calculations/')) {
            respond_json([
                '@context' => ['https://w3id.org/ocsm/main-context.jsonld'],
                '@graph' => [
                    [
                        '@id' => 'urn:openagri:evaporation:calculation:local-1',
                        '@type' => 'Observation',
                        'resultTime' => gmdate('Y-m-d'),
                        'hasSimpleResult' => '4.9',
                    ],
                    [
                        '@id' => 'urn:openagri:evaporation:calculation:local-2',
                        '@type' => 'Observation',
                        'resultTime' => gmdate('Y-m-d', time() + 86400),
                        'hasSimpleResult' => '5.2',
                    ],
                ],
                'kc' => 0.72,
                'water_balance' => -10.8,
                'suggested_schedule' => [
                    'start' => gmdate(DateTimeInterface::ATOM, time() + 3600),
                    'duration_minutes' => 32,
                ],
            ]);
        }
        if ($path === '/api/v1/location/parcel-wkt/') {
            respond_json(['message' => 'Parcel location accepted for local processing']);
        }
        break;

    case 'pest_disease':
        if ($path === '/api/pest/risk' || $path === '/api/v1/fuzzy-risk/forecast/' || str_starts_with($path, '/api/v1/tool/calculate-gdd/')) {
            respond_json([
                'models' => [
                    [
                        'name' => 'Almond fungal risk',
                        'eppo_code' => 'LOCAL',
                        'risk_score' => 42.5,
                        'risk_class' => 'Moderate',
                        'confidence' => 0.76,
                        'alert_threshold' => 65,
                        'date' => gmdate('Y-m-d'),
                    ],
                ],
            ]);
        }
        break;

    case 'farmcalendar':
        if ($path === '/api/calendar/operations' || $path === '/api/v1/' || str_starts_with($path, '/api/v1/')) {
            respond_json([
                '@context' => ['https://w3id.org/ocsm/main-context.jsonld'],
                '@graph' => [
                    [
                        '@type' => 'IrrigationOperation',
                        '@id' => 'urn:farmcalendar:IrrigationOperation:local-1',
                        'title' => 'Evening drip irrigation',
                        'details' => 'Operational task for almond block monitoring',
                        'hasStartDatetime' => gmdate(DateTimeInterface::ATOM, time() + 3600),
                        'hasAgriParcel' => [
                            '@type' => 'Parcel',
                            '@id' => 'urn:farmcalendar:Parcel:macotera-almond-a',
                        ],
                        'usesAgriculturalMachinery' => [
                            ['@id' => 'urn:farmcalendar:AgriculturalMachine:pump-local'],
                        ],
                    ],
                ],
            ]);
        }
        break;

    case 'reporting':
        if ($path === '/api/reports') {
            respond_json([
                'status' => 'available',
                'reports' => [
                    ['type' => 'irrigation', 'status' => 'ready'],
                    ['type' => 'field-operational', 'status' => 'ready'],
                ],
            ]);
        }
        if (str_starts_with($path, '/api/v1/openagri-report/')) {
            respond_json([
                'report_id' => '00000000-0000-4000-8000-000000000101',
                'status' => 'accepted',
            ]);
        }
        break;
}

respond_json([
    'error' => 'route_not_available',
    'service' => $service,
    'path' => $path,
], 404);
