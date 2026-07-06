<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once __DIR__ . '/hmi_data.php';

function agritwin_ai_root_path(): string
{
    return dirname(__DIR__, 2);
}

function agritwin_ai_python_binary(): string
{
    $binary = getenv('AGRITWIN_AI_PYTHON');
    return is_string($binary) && $binary !== '' ? $binary : 'python3';
}

function agritwin_ai_config_path(): string
{
    return agritwin_ai_root_path() . '/ai/config/training_config.example.json';
}

function agritwin_ai_generated_dir(): string
{
    $configured = getenv('AGRITWIN_AI_MODEL_DIR');
    if (is_string($configured) && $configured !== '') {
        return $configured;
    }
    return agritwin_ai_root_path() . '/ai/models/generated';
}

function agritwin_ai_relative_path(string $path): string
{
    $root = rtrim(agritwin_ai_root_path(), '/') . '/';
    return str_starts_with($path, $root) ? substr($path, strlen($root)) : $path;
}

function agritwin_ai_read_json_file(string $path, array $default = []): array
{
    if (!is_file($path) || !is_readable($path)) {
        return $default;
    }
    $payload = json_decode((string) file_get_contents($path), true);
    return is_array($payload) ? $payload : $default;
}

function agritwin_ai_config(): array
{
    return agritwin_ai_read_json_file(agritwin_ai_config_path(), [
        'model_name' => 'agritwin_water_stress_forecast',
        'dataset_path' => 'datasets/agritwin_sample_dataset/synthetic_developer/synthetic_sensor_timeseries.csv',
        'output_path' => 'ai/models/generated',
        'target_variable' => 'water_stress_index',
        'feature_columns' => ['soil_moisture_vwc_pct', 'soil_temperature_c', 'air_temperature_c', 'relative_humidity_pct', 'leaf_wetness_pct', 'rainfall_mm', 'solar_radiation_w_m2'],
    ]);
}

function agritwin_ai_run_command(array $parts): array
{
    $command = implode(' ', array_map('escapeshellarg', $parts));
    $output = [];
    $code = 0;
    exec($command . ' 2>&1', $output, $code);
    $text = trim(implode("\n", $output));
    $json = json_decode($text, true);
    return [
        'ok' => $code === 0,
        'exit_code' => $code,
        'json' => is_array($json) ? $json : null,
        'summary' => $code === 0 ? 'ok' : 'command_failed',
        'output_excerpt' => $text === '' ? null : mb_substr($text, 0, 800),
    ];
}

function agritwin_ai_framework_status(): array
{
    $script = agritwin_ai_root_path() . '/ai/train.py';
    if (!is_file($script)) {
        return [
            'framework' => 'not_available',
            'tensorflow_available' => false,
            'compatible_framework_available' => false,
        ];
    }
    $result = agritwin_ai_run_command([agritwin_ai_python_binary(), $script, '--framework-status']);
    if ($result['ok'] && is_array($result['json'])) {
        return $result['json'];
    }
    return [
        'framework' => 'unavailable',
        'tensorflow_available' => false,
        'compatible_framework_available' => false,
    ];
}

function agritwin_ai_latest_metadata(): array
{
    return agritwin_ai_read_json_file(agritwin_ai_generated_dir() . '/latest_model_metadata.json');
}

function agritwin_ai_training_runs(): array
{
    $runs = agritwin_ai_read_json_file(agritwin_ai_generated_dir() . '/training_runs.json');
    return array_values(array_filter($runs, 'is_array'));
}

function agritwin_ai_models(): array
{
    $config = agritwin_ai_config();
    $latest = agritwin_ai_latest_metadata();
    $models = [[
        'model_name' => (string) ($config['model_name'] ?? 'agritwin_water_stress_forecast'),
        'target_variable' => (string) ($config['target_variable'] ?? 'water_stress_index'),
        'dataset_source' => (string) ($config['dataset_path'] ?? 'datasets/agritwin_sample_dataset/synthetic_developer/synthetic_sensor_timeseries.csv'),
        'trained' => $latest !== [],
        'framework' => (string) ($latest['framework'] ?? 'tensorflow_or_compatible'),
        'trained_at' => $latest['trained_at'] ?? null,
        'validation_mae' => $latest['validation_mae'] ?? null,
        'training_records' => $latest['training_records'] ?? null,
    ]];
    return $models;
}

function agritwin_ai_status(): array
{
    $config = agritwin_ai_config();
    $framework = agritwin_ai_framework_status();
    $latest = agritwin_ai_latest_metadata();
    return [
        'ok' => true,
        'framework' => $framework['framework'] ?? 'tensorflow_or_compatible',
        'tensorflow_available' => (bool) ($framework['tensorflow_available'] ?? false),
        'compatible_framework_available' => (bool) ($framework['compatible_framework_available'] ?? false),
        'dataset_source' => (string) ($config['dataset_path'] ?? ''),
        'model_output_path' => agritwin_ai_relative_path(agritwin_ai_generated_dir()),
        'training_mode' => 'cli_or_administrator_triggered',
        'model_trained' => $latest !== [],
        'last_training_run' => $latest['trained_at'] ?? null,
        'target_variable' => (string) ($config['target_variable'] ?? 'water_stress_index'),
        'feature_columns' => array_values($config['feature_columns'] ?? []),
        'privacy_policy' => 'sample_dataset_or_operator_supplied_payload_only',
    ];
}

function agritwin_ai_run_training(): array
{
    $script = agritwin_ai_root_path() . '/ai/train.py';
    if (!is_file($script)) {
        return ['ok' => false, 'message' => 'training_script_unavailable'];
    }
    $result = agritwin_ai_run_command([
        agritwin_ai_python_binary(),
        $script,
        '--config',
        agritwin_ai_config_path(),
        '--epochs',
        '1',
    ]);
    return [
        'ok' => $result['ok'],
        'message' => $result['ok'] ? 'training_completed' : 'training_failed',
        'training_result' => $result['json'],
        'output_excerpt' => $result['ok'] ? null : $result['output_excerpt'],
    ];
}

function agritwin_ai_example_payload(): array
{
    return agritwin_ai_read_json_file(agritwin_ai_root_path() . '/ai/examples/inference_input.json', [
        'soil_moisture_vwc_pct' => 22.5,
        'soil_temperature_c' => 19.4,
        'air_temperature_c' => 27.1,
        'relative_humidity_pct' => 48,
        'leaf_wetness_pct' => 12,
        'rainfall_mm' => 0.0,
        'solar_radiation_w_m2' => 720,
    ]);
}

function agritwin_ai_filter_inference_payload(array $payload): array
{
    $allowed = [
        'soil_moisture_vwc_pct',
        'soil_temperature_c',
        'air_temperature_c',
        'relative_humidity_pct',
        'leaf_wetness_pct',
        'rainfall_mm',
        'solar_radiation_w_m2',
        'wind_speed_kmh',
        'irrigation_flow_m3_h',
        'pump_energy_kwh',
    ];
    $safe = [];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $payload) && is_numeric($payload[$field])) {
            $safe[$field] = (float) $payload[$field];
        }
    }
    return $safe ?: agritwin_ai_example_payload();
}

function agritwin_ai_run_inference(array $payload = []): array
{
    $script = agritwin_ai_root_path() . '/ai/infer.py';
    if (!is_file($script)) {
        return ['ok' => false, 'message' => 'inference_script_unavailable'];
    }
    $safePayload = agritwin_ai_filter_inference_payload($payload);
    $result = agritwin_ai_run_command([
        agritwin_ai_python_binary(),
        $script,
        '--model',
        'latest',
        '--input-json',
        json_encode($safePayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    ]);
    return [
        'ok' => $result['ok'],
        'message' => $result['ok'] ? 'inference_completed' : 'inference_failed',
        'inference' => $result['json'],
        'output_excerpt' => $result['ok'] ? null : $result['output_excerpt'],
    ];
}
