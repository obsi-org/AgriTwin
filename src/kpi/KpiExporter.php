<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Kpi;

final class KpiExporter
{
    public function writeJson(array $data, string $path): void
    {
        $this->ensureDirectory($path);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    public function writeCsv(array $summary, string $path): void
    {
        $this->ensureDirectory($path);
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open $path for writing");
        }

        fputcsv($handle, ['metric', 'value', 'unit', 'mode']);
        $mode = (string) ($summary['architecture_mode'] ?? 'all');
        foreach ($this->flattenSummary($summary) as $row) {
            fputcsv($handle, [$row['metric'], $row['value'], $row['unit'], $mode]);
        }
        fclose($handle);
    }

    public function writeMarkdownReport(array $comparison, string $path): void
    {
        $this->ensureDirectory($path);
        $baseline = $comparison['baseline'];
        $candidate = $comparison['comparison'];
        $deltas = $comparison['deltas'];

        $lines = [
            '# AgriTwin D1 KPI Evidence Report',
            '',
            'This generated report uses synthetic/sample KPI events only. It must not be treated as pilot performance evidence until populated from approved, pseudonymised pilot telemetry.',
            '',
            '- Generated at: `' . ($comparison['generated_at'] ?? gmdate('c')) . '`',
            '- Baseline ADS: `' . ($comparison['baseline_mode'] ?? 'cloud') . '`',
            '- Comparison ADS: `' . ($comparison['comparison_mode'] ?? 'mixed') . '`',
            '',
            '## Cloud-Only vs Mixed Edge-Cloud Summary',
            '',
            '| Metric | Cloud-only | Mixed edge-cloud | Delta mixed-cloud |',
            '| --- | ---: | ---: | ---: |',
            $this->tableRow('Irrigation schedule latency, seconds', $baseline['irrigation_schedule_latency']['average_seconds'] ?? null, $candidate['irrigation_schedule_latency']['average_seconds'] ?? null, $deltas['irrigation_schedule_latency_seconds_avg'] ?? null),
            $this->tableRow('Water-stress forecast MAE, m3/m3', $baseline['water_stress_forecast_mae']['mae_m3_m3'] ?? null, $candidate['water_stress_forecast_mae']['mae_m3_m3'] ?? null, $deltas['water_stress_mae_m3_m3'] ?? null),
            $this->tableRow('Nitrogen recommendation MAE, kg/ha', $baseline['nitrogen_recommendation_error']['mae_kg_ha'] ?? null, $candidate['nitrogen_recommendation_error']['mae_kg_ha'] ?? null, $deltas['nitrogen_mae_kg_ha'] ?? null),
            $this->tableRow('Data-continuity uptime ratio', $baseline['data_continuity_uptime']['ratio'] ?? null, $candidate['data_continuity_uptime']['ratio'] ?? null, $deltas['uptime_ratio'] ?? null),
            $this->tableRow('Network use, MB/hour', $baseline['network_usage']['mb_per_hour'] ?? null, $candidate['network_usage']['mb_per_hour'] ?? null, $deltas['network_total_mb'] ?? null),
            $this->tableRow('CPU-hours', $baseline['compute_usage']['cpu_hours'] ?? null, $candidate['compute_usage']['cpu_hours'] ?? null, $deltas['cpu_hours'] ?? null),
            $this->tableRow('GPU-hours', $baseline['compute_usage']['gpu_hours'] ?? null, $candidate['compute_usage']['gpu_hours'] ?? null, $deltas['gpu_hours'] ?? null),
            $this->tableRow('Forecast-cycle energy, kWh', $baseline['energy_usage']['forecast_cycle_kwh'] ?? null, $candidate['energy_usage']['forecast_cycle_kwh'] ?? null, $deltas['forecast_cycle_energy_kwh'] ?? null),
            $this->tableRow('Farming-operation energy, kWh', $baseline['energy_usage']['farming_operation_kwh'] ?? null, $candidate['energy_usage']['farming_operation_kwh'] ?? null, $deltas['farming_operation_energy_kwh'] ?? null),
            '',
            '## Evidence Notes',
            '',
            '- Sensor continuity is counted from 15-minute packets processed within 30 minutes.',
            '- Forecast accuracy uses MAE where ground truth or agronomist reference values are present.',
            '- Network, compute, and energy values support measured values or explicit estimates with a source/assumption field.',
            '- Standards/interoperability evidence is collected as structured event categories and should be backed by implementation artefacts during pilot evaluation.',
        ];

        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    private function flattenSummary(array $summary): array
    {
        return [
            ['metric' => 'irrigation_schedule_latency_average', 'value' => $summary['irrigation_schedule_latency']['average_seconds'] ?? '', 'unit' => 'seconds'],
            ['metric' => 'water_stress_mae', 'value' => $summary['water_stress_forecast_mae']['mae_m3_m3'] ?? '', 'unit' => 'm3/m3'],
            ['metric' => 'nitrogen_recommendation_mae', 'value' => $summary['nitrogen_recommendation_error']['mae_kg_ha'] ?? '', 'unit' => 'kg/ha'],
            ['metric' => 'data_continuity_uptime_ratio', 'value' => $summary['data_continuity_uptime']['ratio'] ?? '', 'unit' => 'ratio'],
            ['metric' => 'network_total_mb', 'value' => $summary['network_usage']['total_mb'] ?? '', 'unit' => 'MB'],
            ['metric' => 'network_mb_per_hour', 'value' => $summary['network_usage']['mb_per_hour'] ?? '', 'unit' => 'MB/hour'],
            ['metric' => 'network_peak_uplink', 'value' => $summary['network_usage']['peak_uplink_mbps'] ?? '', 'unit' => 'Mbps'],
            ['metric' => 'network_peak_downlink', 'value' => $summary['network_usage']['peak_downlink_mbps'] ?? '', 'unit' => 'Mbps'],
            ['metric' => 'compute_cpu_hours', 'value' => $summary['compute_usage']['cpu_hours'] ?? '', 'unit' => 'CPU-hours'],
            ['metric' => 'compute_gpu_hours', 'value' => $summary['compute_usage']['gpu_hours'] ?? '', 'unit' => 'GPU-hours'],
            ['metric' => 'energy_forecast_cycle', 'value' => $summary['energy_usage']['forecast_cycle_kwh'] ?? '', 'unit' => 'kWh'],
            ['metric' => 'energy_farming_operation', 'value' => $summary['energy_usage']['farming_operation_kwh'] ?? '', 'unit' => 'kWh'],
        ];
    }

    private function tableRow(string $label, mixed $left, mixed $right, mixed $delta): string
    {
        return '| ' . $label . ' | ' . $this->format($left) . ' | ' . $this->format($right) . ' | ' . $this->format($delta) . ' |';
    }

    private function format(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }
        return is_numeric($value) ? rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.') : (string) $value;
    }

    private function ensureDirectory(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
