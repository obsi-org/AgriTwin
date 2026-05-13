<?php
declare(strict_types=1);

require_once __DIR__ . '/hmi_data.php';

function agritwin_statistics_values(array $values): array
{
    $values = array_values(array_map('floatval', $values));
    sort($values);
    $count = count($values);
    if ($count === 0) {
        return ['count' => 0, 'min' => null, 'max' => null, 'avg' => null, 'median' => null, 'latest' => null, 'trend' => 'stable'];
    }
    $mid = intdiv($count, 2);
    $median = $count % 2 === 0 ? ($values[$mid - 1] + $values[$mid]) / 2 : $values[$mid];
    $trend = $count > 1 ? ($values[$count - 1] > $values[0] ? 'rising' : ($values[$count - 1] < $values[0] ? 'falling' : 'stable')) : 'stable';
    return [
        'count' => $count,
        'min' => round($values[0], 2),
        'max' => round($values[$count - 1], 2),
        'avg' => round(array_sum($values) / $count, 2),
        'median' => round($median, 2),
        'latest' => round($values[$count - 1], 2),
        'trend' => $trend,
    ];
}

function agritwin_statistics_by_parameter(): array
{
    $grouped = [];
    foreach (agritwin_sensors() as $sensor) {
        $grouped[$sensor['parameter']][] = (float) $sensor['value'];
    }
    return array_map('agritwin_statistics_values', $grouped);
}

function agritwin_station_comparison(): array
{
    $rows = [];
    foreach (agritwin_sensor_stations() as $station) {
        $values = [];
        foreach ($station['readings'] as $reading) {
            $values[$reading['parameter']] = $reading['value'];
        }
        $soil = (float) ($values['soil_moisture'] ?? 0);
        $stress = $soil < 23 ? 'reduced' : 'normal';
        $rows[] = [
            'station_id' => $station['id'],
            'block' => $station['block'],
            'crop' => $station['crop'],
            'soil_moisture_pct' => $soil,
            'air_temperature_c' => (float) ($values['air_temperature'] ?? 0),
            'rainfall_mm' => (float) ($values['rainfall'] ?? 0),
            'irrigation_flow_m3h' => isset($values['irrigation_flow']) ? (float) $values['irrigation_flow'] : null,
            'pump_energy_kwh' => isset($values['pump_energy']) ? (float) $values['pump_energy'] : null,
            'water_stress_status' => $stress,
            'freshness' => $station['timestamp'],
        ];
    }
    return $rows;
}

function agritwin_statistics_series(string $parameter): array
{
    $series = [];
    foreach (agritwin_sensors() as $sensor) {
        if ($sensor['parameter'] === $parameter) {
            $series[] = [
                'label' => $sensor['station_id'],
                'value' => (float) $sensor['value'],
                'unit' => $sensor['unit'],
                'timestamp' => $sensor['timestamp'],
            ];
        }
    }
    return $series;
}

function agritwin_weather_analytics(): array
{
    $weather = agritwin_weather_source();
    $parameters = agritwin_statistics_by_parameter();
    return [
        'source' => $weather['source_name'] ?? $weather['label'],
        'status' => $weather['status'],
        'latest' => [
            'temperature_c' => $weather['temperature_c'],
            'humidity_pct' => $weather['humidity_pct'],
            'wind_kmh' => $weather['wind_kmh'],
            'rainfall_mm' => $weather['rainfall_mm'],
        ],
        'station_weather_stats' => [
            'air_temperature' => $parameters['air_temperature'] ?? [],
            'relative_humidity' => $parameters['relative_humidity'] ?? [],
            'wind_speed' => $parameters['wind_speed'] ?? [],
            'rainfall' => $parameters['rainfall'] ?? [],
            'solar_radiation' => $parameters['solar_radiation'] ?? [],
        ],
    ];
}

function agritwin_irrigation_analytics(): array
{
    $parameters = agritwin_statistics_by_parameter();
    $soil = $parameters['soil_moisture'] ?? agritwin_statistics_values([]);
    $flow = $parameters['irrigation_flow'] ?? agritwin_statistics_values([]);
    $energy = $parameters['pump_energy'] ?? agritwin_statistics_values([]);
    $deficit = $soil['avg'] !== null ? max(0, round(28 - (float) $soil['avg'], 2)) : null;
    return [
        'recommendation' => agritwin_recommendations()['irrigation'],
        'soil_moisture' => $soil,
        'irrigation_flow' => $flow,
        'pump_energy' => $energy,
        'estimated_irrigation_deficit_pct_vwc' => $deficit,
        'anomaly_flags' => $deficit !== null && $deficit > 4 ? ['soil_moisture_below_target'] : [],
    ];
}

function agritwin_pest_disease_analytics(): array
{
    $recommendation = agritwin_recommendations()['pest'];
    return [
        'risk' => $recommendation,
        'risk_index' => (int) strtok((string) $recommendation['risk'], '/'),
        'weather_inputs' => [
            'leaf_wetness' => agritwin_statistics_by_parameter()['leaf_wetness'] ?? [],
            'relative_humidity' => agritwin_statistics_by_parameter()['relative_humidity'] ?? [],
            'rainfall' => agritwin_statistics_by_parameter()['rainfall'] ?? [],
        ],
    ];
}

function agritwin_service_freshness_analytics(): array
{
    $services = agritwin_service_cards();
    $rows = [];
    foreach ($services as $service) {
        $rows[] = [
            'service' => $service['name'],
            'group' => $service['service_group'],
            'status' => $service['status'],
            'last_check' => $service['last_check'],
            'latency_ms' => $service['latency_ms'],
            'required_for_d1' => $service['required_for_d1'],
        ];
    }
    return $rows;
}

function agritwin_analytics_summary(): array
{
    $parameters = agritwin_statistics_by_parameter();
    $services = agritwin_service_cards();
    $mandatory = agritwin_openagri_service_groups($services)['mandatory'];
    $availableMandatory = count(array_filter($mandatory, static fn (array $service): bool => $service['status'] === 'live_healthy'));
    return [
        'generated_at' => gmdate(DateTimeInterface::ATOM),
        'data_source' => agritwin_t('local_operational_dataset'),
        'location' => agritwin_farm_context()['location'],
        'timezone' => agritwin_farm_context()['timezone'],
        'station_count' => count(agritwin_sensor_stations()),
        'average_soil_moisture_pct' => $parameters['soil_moisture']['avg'] ?? null,
        'latest_weather_source' => agritwin_weather_source()['source_name'] ?? agritwin_weather_source()['label'],
        'irrigation_status' => agritwin_recommendations()['irrigation']['confidence'],
        'pest_risk_level' => agritwin_recommendations()['pest']['risk'],
        'kpi_freshness' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
        'service_status_summary' => [
            'mandatory_available' => $availableMandatory,
            'mandatory_total' => count($mandatory),
        ],
    ];
}

function agritwin_full_analytics(): array
{
    return [
        'summary' => agritwin_analytics_summary(),
        'weather' => agritwin_weather_analytics(),
        'sensor_stations' => [
            'comparison' => agritwin_station_comparison(),
            'parameters' => agritwin_statistics_by_parameter(),
            'series' => [
                'soil_moisture' => agritwin_statistics_series('soil_moisture'),
                'air_temperature' => agritwin_statistics_series('air_temperature'),
                'rainfall' => agritwin_statistics_series('rainfall'),
            ],
        ],
        'irrigation' => agritwin_irrigation_analytics(),
        'pest_disease' => agritwin_pest_disease_analytics(),
        'kpis' => agritwin_kpis(),
        'service_freshness' => agritwin_service_freshness_analytics(),
    ];
}
