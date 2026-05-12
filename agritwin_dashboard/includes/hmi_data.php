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
        'site' => 'FOTOMACO Macotera pilot farm',
        'operator' => 'Fotovoltaica Macotera SL',
        'location' => 'Macotera, Salamanca, Castilla y Leon, Spain',
        'primary_crop' => 'Almonds',
        'secondary_crops' => ['Pistachios', 'Maize'],
        'timezone' => 'Europe/Madrid',
        'base_path' => '/agritwin3/',
        'data_mode' => 'local demo/mock',
        'last_sync' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
    ];
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

function agritwin_openagri_services(): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    return [
        ['name' => 'OpenAgri Gatekeeper / Access Control', 'endpoint' => 'local-gatekeeper-mock', 'latency_ms' => 32],
        ['name' => 'OpenAgri Weather Data Service', 'endpoint' => 'local-weather-mock', 'latency_ms' => 44],
        ['name' => 'OpenAgri Irrigation Management Service', 'endpoint' => 'local-irrigation-mock', 'latency_ms' => 58],
        ['name' => 'OpenAgri Pest & Disease Management Service', 'endpoint' => 'local-pest-disease-mock', 'latency_ms' => 63],
        ['name' => 'OpenAgri FarmCalendar', 'endpoint' => 'local-farmcalendar-mock', 'latency_ms' => 39],
        ['name' => 'OpenAgri Reporting Service', 'endpoint' => 'local-reporting-mock', 'latency_ms' => 71],
    ];
}

function agritwin_service_cards(): array
{
    return array_map(static fn (array $service): array => $service + [
        'status' => 'healthy',
        'mode' => 'local demo/mock',
        'last_check' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
    ], agritwin_openagri_services());
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
