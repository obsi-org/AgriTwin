<?php
namespace AgriTwin\Kpi;

final class KpiSampleData
{
    public static function events(): array
    {
        return array_merge(
            self::modeEvents('cloud', '2026-05-12T08:00:00Z', 92, 0.041, 0.046, 58, 62, 18.5, 41.2, 0.41, 0.0, 0.018, 0.82),
            self::modeEvents('mixed', '2026-05-12T09:00:00Z', 38, 0.038, 0.040, 55, 57, 7.2, 16.6, 0.18, 0.0, 0.009, 0.79)
        );
    }

    private static function modeEvents(
        string $mode,
        string $baseTime,
        int $scheduleLatencySeconds,
        float $predictedMoisture,
        float $actualMoisture,
        float $nitrogenRecommendation,
        float $nitrogenReference,
        float $mbSent,
        float $mbReceived,
        float $cpuHours,
        float $gpuHours,
        float $cycleEnergyKwh,
        float $operationEnergyKwh
    ): array {
        $base = strtotime($baseTime);
        $events = [
            [
                'event_type' => 'sensor_packet_received',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'packet_id' => $mode . '-pkt-001',
                'packet_created_at' => gmdate('c', $base - 120),
                'received_at' => gmdate('c', $base),
                'processed_at' => gmdate('c', $base + 600),
                'expected_interval_minutes' => 15,
            ],
            [
                'event_type' => 'sensor_packet_received',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'packet_id' => $mode . '-pkt-002',
                'packet_created_at' => gmdate('c', $base + 900),
                'received_at' => gmdate('c', $base + 980),
                'processed_at' => gmdate('c', $base + 1300),
                'expected_interval_minutes' => 15,
            ],
            [
                'event_type' => 'sensor_packet_received',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'packet_id' => $mode . '-pkt-003',
                'packet_created_at' => gmdate('c', $base + 1800),
                'received_at' => gmdate('c', $base + 2010),
                'processed_at' => gmdate('c', $base + ($mode === 'cloud' ? 3900 : 2500)),
                'expected_interval_minutes' => 15,
            ],
            [
                'event_type' => 'input_packet_received',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'received_at' => gmdate('c', $base),
            ],
            [
                'event_type' => 'irrigation_schedule_generated',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'generated_at' => gmdate('c', $base + $scheduleLatencySeconds),
            ],
            [
                'event_type' => 'water_stress_forecast',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'prediction_id' => $mode . '-water-001',
                'predicted_soil_moisture_m3_m3' => $predictedMoisture,
                'ground_truth_soil_moisture_m3_m3' => $actualMoisture,
                'ground_truth_source' => 'synthetic_probe_reading',
            ],
            [
                'event_type' => 'nitrogen_recommendation',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'recommendation_id' => $mode . '-n-001',
                'recommended_nitrogen_kg_ha' => $nitrogenRecommendation,
                'agronomist_reference_nitrogen_kg_ha' => $nitrogenReference,
                'reference_source' => 'synthetic_agronomist_prescription',
            ],
            [
                'event_type' => 'network_usage',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'service' => 'openagri',
                'window_start' => gmdate('c', $base),
                'window_end' => gmdate('c', $base + 3600),
                'bytes_sent' => (int) round($mbSent * 1024 * 1024),
                'bytes_received' => (int) round($mbReceived * 1024 * 1024),
                'peak_uplink_mbps' => $mode === 'cloud' ? 4.8 : 1.3,
                'peak_downlink_mbps' => $mode === 'cloud' ? 9.2 : 3.1,
            ],
            [
                'event_type' => 'compute_usage',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'component' => $mode === 'cloud' ? 'cloud_dss_worker' : 'edge_gateway',
                'cpu_hours' => $cpuHours,
                'gpu_hours' => $gpuHours,
                'source' => ['system' => 'synthetic_resource_meter'],
            ],
            [
                'event_type' => 'energy_usage',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'scope' => 'forecast_cycle',
                'kwh' => $cycleEnergyKwh,
                'source' => ['system' => 'synthetic_estimate', 'assumption' => 'device power draw multiplied by processing duration'],
            ],
            [
                'event_type' => 'energy_usage',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'scope' => 'farming_operation',
                'operation' => 'irrigation_pump',
                'kwh' => $operationEnergyKwh,
                'source' => ['system' => 'synthetic_meter', 'assumption' => 'pump meter reading for one irrigation event'],
            ],
            [
                'event_type' => 'standards_evidence',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'category' => 'data_formats',
                'evidence' => ['JSON', 'CSV', 'Markdown', 'JSON-LD/OCSM-compatible payloads'],
            ],
            [
                'event_type' => 'standards_evidence',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'category' => 'software_modules',
                'evidence' => ['PHP connectors', 'PWA dashboard', 'Docker Compose profiles'],
            ],
        ];

        if ($mode === 'mixed') {
            $events[] = [
                'event_type' => 'standards_evidence',
                'architecture_mode' => $mode,
                'cycle_id' => $mode . '-cycle-001',
                'category' => 'hardware_modules',
                'evidence' => ['edge gateway', 'soil moisture probes', 'pump telemetry adapter'],
            ];
        }

        return $events;
    }
}
