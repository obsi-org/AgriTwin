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

function agritwin_analytics_time_labels(string $range = '24h'): array
{
    $count = match ($range) {
        '7d' => 7,
        '30d' => 10,
        default => 8,
    };
    $step = match ($range) {
        '7d' => new DateInterval('P1D'),
        '30d' => new DateInterval('P3D'),
        default => new DateInterval('PT3H'),
    };
    $start = agritwin_demo_date()->sub(match ($range) {
        '7d' => new DateInterval('P6D'),
        '30d' => new DateInterval('P27D'),
        default => new DateInterval('PT21H'),
    });
    $labels = [];
    $cursor = $start;
    for ($i = 0; $i < $count; $i++) {
        $labels[] = [
            'iso' => $cursor->format(DateTimeInterface::ATOM),
            'label' => $range === '24h' ? $cursor->format('H:i') : $cursor->format('d M'),
        ];
        $cursor = $cursor->add($step);
    }
    return $labels;
}

function agritwin_station_base_values(string $stationId): array
{
    foreach (agritwin_sensor_stations() as $station) {
        if ($station['id'] === $stationId) {
            $values = [];
            foreach ($station['readings'] as $reading) {
                $values[$reading['parameter']] = (float) $reading['value'];
            }
            return ['station' => $station, 'values' => $values];
        }
    }
    return ['station' => null, 'values' => []];
}

function agritwin_metric_timeseries(string $parameter, ?string $stationId = null, string $range = '24h'): array
{
    $labels = agritwin_analytics_time_labels($range);
    $series = [];
    $stations = $stationId !== null && $stationId !== 'all'
        ? array_values(array_filter(agritwin_sensor_stations(), static fn (array $station): bool => $station['id'] === $stationId))
        : agritwin_sensor_stations();

    foreach ($stations as $stationIndex => $station) {
        $base = agritwin_station_base_values($station['id'])['values'][$parameter] ?? null;
        if ($base === null) {
            continue;
        }
        $points = [];
        foreach ($labels as $idx => $label) {
            $wave = sin(($idx + 1 + $stationIndex) / 2.2);
            $drift = match ($parameter) {
                'soil_moisture' => -0.28 * $idx,
                'soil_temperature' => 0.18 * $idx,
                'air_temperature' => 1.4 * $wave,
                'relative_humidity' => -2.6 * $wave,
                'wind_speed' => 1.8 * $wave,
                'rainfall' => $idx % 5 === 1 ? 0.45 : -0.05 * $idx,
                'solar_radiation' => 60 * $wave,
                'irrigation_flow' => $idx >= 5 ? 1.4 * ($idx - 4) : -0.7 * $idx,
                'pump_energy' => $idx >= 5 ? 0.25 * ($idx - 4) : -0.2 * $idx,
                default => 0.12 * $idx,
            };
            $value = max(0, round((float) $base + $drift, 2));
            $points[] = ['time' => $label['iso'], 'label' => $label['label'], 'value' => $value];
        }
        $series[] = [
            'station_id' => $station['id'],
            'label' => $station['id'],
            'block' => $station['block'],
            'crop' => $station['crop'],
            'parameter' => $parameter,
            'unit' => agritwin_parameter_unit($parameter),
            'points' => $points,
            'stats' => agritwin_statistics_values(array_column($points, 'value')),
        ];
    }
    return ['labels' => $labels, 'series' => $series];
}

function agritwin_parameter_unit(string $parameter): string
{
    return match ($parameter) {
        'soil_moisture' => '% VWC',
        'soil_temperature', 'air_temperature' => 'degC',
        'relative_humidity', 'leaf_wetness' => '%',
        'rainfall' => 'mm',
        'wind_speed' => 'km/h',
        'solar_radiation' => 'W/m2',
        'irrigation_flow' => 'm3/h',
        'pump_energy' => 'kWh',
        default => '',
    };
}

function agritwin_weather_timeseries(string $range = '24h'): array
{
    return [
        'labels' => agritwin_analytics_time_labels($range),
        'air_temperature' => agritwin_metric_timeseries('air_temperature', 'all', $range)['series'],
        'relative_humidity' => agritwin_metric_timeseries('relative_humidity', 'all', $range)['series'],
        'wind_speed' => agritwin_metric_timeseries('wind_speed', 'all', $range)['series'],
        'rainfall' => agritwin_metric_timeseries('rainfall', 'all', $range)['series'],
    ];
}

function agritwin_irrigation_timeline(string $range = '24h'): array
{
    $flow = agritwin_metric_timeseries('irrigation_flow', 'all', $range);
    $energy = agritwin_metric_timeseries('pump_energy', 'all', $range);
    return [
        'flow' => $flow,
        'energy' => $energy,
        'recommendation_timeline' => [
            ['time' => '07:30', 'label' => agritwin_t('op_pump_inspection'), 'status' => 'completed'],
            ['time' => '12:30', 'label' => agritwin_t('op_irrigation_review'), 'status' => 'planned'],
            ['time' => '18:30', 'label' => agritwin_t('irrigation_recommendation'), 'status' => 'planned'],
        ],
    ];
}

function agritwin_pest_risk_timeseries(string $range = '24h'): array
{
    $labels = agritwin_analytics_time_labels($range);
    $points = [];
    foreach ($labels as $idx => $label) {
        $points[] = [
            'time' => $label['iso'],
            'label' => $label['label'],
            'value' => min(100, max(0, round(29 + ($idx * 1.2) + sin($idx / 1.8) * 4, 1))),
        ];
    }
    return [
        'unit' => '0-100',
        'category' => 'moderate',
        'points' => $points,
        'gdd_accumulation' => round(318.4 + count($points) * 2.7, 1),
    ];
}

function agritwin_station_rankings(): array
{
    $rows = agritwin_station_comparison();
    usort($rows, static fn (array $a, array $b): int => $a['soil_moisture_pct'] <=> $b['soil_moisture_pct']);
    return array_map(static function (array $row, int $idx): array {
        return $row + [
            'rank' => $idx + 1,
            'irrigation_need_score' => max(0, min(100, round((30 - $row['soil_moisture_pct']) * 6, 1))),
            'freshness_score' => 98,
        ];
    }, $rows, array_keys($rows));
}

function agritwin_operational_insights(): array
{
    return [
        agritwin_t('insight_soil_moisture_block_b'),
        agritwin_t('insight_irrigation_demand'),
        agritwin_t('insight_pest_risk'),
        agritwin_t('insight_service_freshness'),
    ];
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
        'timeseries' => agritwin_weather_timeseries(),
        'forecast_cards' => [
            ['label' => agritwin_t('today'), 'value' => '18-24 degC', 'detail' => agritwin_t('light_wind_low_rain')],
            ['label' => agritwin_t('next_24h'), 'value' => '0.8 mm', 'detail' => agritwin_t('low_rainfall_expected')],
            ['label' => agritwin_t('field_work_window'), 'value' => '18:30-21:00', 'detail' => agritwin_t('recommended_window')],
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
        'water_balance_score' => $deficit !== null ? max(0, min(100, round(100 - ($deficit * 12), 1))) : null,
        'timeline' => agritwin_irrigation_timeline(),
        'anomaly_flags' => $deficit !== null && $deficit > 4 ? ['soil_moisture_below_target'] : [],
    ];
}

function agritwin_pest_disease_analytics(): array
{
    $recommendation = agritwin_recommendations()['pest'];
    return [
        'risk' => $recommendation,
        'risk_index' => (int) strtok((string) $recommendation['risk'], '/'),
        'timeseries' => agritwin_pest_risk_timeseries(),
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
            'core_service' => $service['core_service'],
            'integration_priority' => $service['integration_priority'],
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
        'insights' => agritwin_operational_insights(),
        'freshness_score' => 98,
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
                'soil_moisture_timeseries' => agritwin_metric_timeseries('soil_moisture'),
                'soil_temperature_timeseries' => agritwin_metric_timeseries('soil_temperature'),
                'air_temperature' => agritwin_statistics_series('air_temperature'),
                'rainfall' => agritwin_statistics_series('rainfall'),
            ],
            'rankings' => agritwin_station_rankings(),
            'thresholds' => ['soil_moisture_preferred_pct' => 28, 'soil_moisture_watch_pct' => 23],
        ],
        'irrigation' => agritwin_irrigation_analytics(),
        'pest_disease' => agritwin_pest_disease_analytics(),
        'kpis' => agritwin_kpis(),
        'service_freshness' => agritwin_service_freshness_analytics(),
    ];
}
