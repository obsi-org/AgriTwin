<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

require_once __DIR__ . '/../../src/kpi/autoload.php';

use AgriTwin\Kpi\KpiCalculator;
use AgriTwin\Kpi\KpiSampleData;

final class KpiCalculatorTest
{
    private int $passed = 0;
    private int $failed = 0;
    private KpiCalculator $calculator;

    public function __construct()
    {
        $this->calculator = new KpiCalculator();
    }

    public function run(): int
    {
        $tests = [
            'irrigation latency' => fn () => $this->testIrrigationLatency(),
            'water stress MAE' => fn () => $this->testWaterStressMae(),
            'nitrogen recommendation error' => fn () => $this->testNitrogenRecommendationError(),
            'uptime ratio' => fn () => $this->testUptimeRatio(),
            'byte aggregation' => fn () => $this->testByteAggregation(),
            'stale and missing data' => fn () => $this->testStaleAndMissingData(),
        ];

        foreach ($tests as $name => $test) {
            try {
                $test();
                $this->passed++;
                echo "[PASS] $name\n";
            } catch (Throwable $e) {
                $this->failed++;
                echo "[FAIL] $name: {$e->getMessage()}\n";
            }
        }

        echo "Passed: {$this->passed}; Failed: {$this->failed}\n";
        return $this->failed === 0 ? 0 : 1;
    }

    private function testIrrigationLatency(): void
    {
        $summary = $this->calculator->summarize(KpiSampleData::events(), 'cloud');
        $this->assertNear(92.0, $summary['irrigation_schedule_latency']['average_seconds'], 0.0001, 'cloud latency');
    }

    private function testWaterStressMae(): void
    {
        $events = [
            ['event_type' => 'water_stress_forecast', 'predicted_soil_moisture_m3_m3' => 0.10, 'ground_truth_soil_moisture_m3_m3' => 0.15],
            ['event_type' => 'water_stress_forecast', 'predicted_soil_moisture_m3_m3' => 0.30, 'ground_truth_soil_moisture_m3_m3' => 0.25],
        ];
        $metric = $this->calculator->waterStressMae($events);
        $this->assertNear(0.05, $metric['mae_m3_m3'], 0.0001, 'MAE should average absolute errors');
    }

    private function testNitrogenRecommendationError(): void
    {
        $events = [
            ['event_type' => 'nitrogen_recommendation', 'recommended_nitrogen_kg_ha' => 60, 'agronomist_reference_nitrogen_kg_ha' => 55],
            ['event_type' => 'nitrogen_recommendation', 'recommended_nitrogen_kg_ha' => 48, 'agronomist_reference_nitrogen_kg_ha' => 54],
        ];
        $metric = $this->calculator->nitrogenRecommendationError($events);
        $this->assertNear(5.5, $metric['mae_kg_ha'], 0.0001, 'nitrogen MAE');
    }

    private function testUptimeRatio(): void
    {
        $events = [
            ['event_type' => 'sensor_packet_received', 'packet_created_at' => '2026-05-12T08:00:00Z', 'processed_at' => '2026-05-12T08:20:00Z'],
            ['event_type' => 'sensor_packet_received', 'packet_created_at' => '2026-05-12T08:15:00Z', 'processed_at' => '2026-05-12T09:00:01Z'],
            ['event_type' => 'sensor_packet_received', 'packet_created_at' => '2026-05-12T08:30:00Z'],
        ];
        $metric = $this->calculator->dataContinuityUptime($events);
        $this->assertNear(1 / 3, $metric['ratio'], 0.0001, 'uptime ratio');
        $this->assert($metric['late_packets'] === 1, 'late packet count');
        $this->assert($metric['missing_processed_at'] === 1, 'missing processed timestamp count');
    }

    private function testByteAggregation(): void
    {
        $events = [
            [
                'event_type' => 'network_usage',
                'service' => 'weather',
                'window_start' => '2026-05-12T08:00:00Z',
                'window_end' => '2026-05-12T09:00:00Z',
                'bytes_sent' => 1048576,
                'bytes_received' => 2097152,
                'peak_uplink_mbps' => 1.2,
                'peak_downlink_mbps' => 2.5,
            ],
            [
                'event_type' => 'network_usage',
                'service' => 'irrigation',
                'window_start' => '2026-05-12T09:00:00Z',
                'window_end' => '2026-05-12T10:00:00Z',
                'bytes_sent' => 1048576,
                'bytes_received' => 0,
                'peak_uplink_mbps' => 1.8,
                'peak_downlink_mbps' => 1.0,
            ],
        ];
        $metric = $this->calculator->networkUsage($events);
        $this->assertNear(4.0, $metric['total_mb'], 0.0001, 'total MB');
        $this->assertNear(2.0, $metric['mb_per_hour'], 0.0001, 'MB/hour');
        $this->assertNear(1.8, $metric['peak_uplink_mbps'], 0.0001, 'peak uplink');
    }

    private function testStaleAndMissingData(): void
    {
        $events = [
            ['event_type' => 'water_stress_forecast', 'predicted_soil_moisture_m3_m3' => 0.22],
            ['event_type' => 'nitrogen_recommendation', 'recommended_nitrogen_kg_ha' => 50],
            ['event_type' => 'irrigation_schedule_generated', 'cycle_id' => 'missing-input', 'generated_at' => '2026-05-12T08:00:00Z'],
        ];
        $summary = $this->calculator->summarize($events);
        $this->assert($summary['water_stress_forecast_mae']['mae_m3_m3'] === null, 'missing water truth should not fabricate MAE');
        $this->assert($summary['water_stress_forecast_mae']['missing_ground_truth'] === 1, 'missing water truth counted');
        $this->assert($summary['nitrogen_recommendation_error']['missing_reference'] === 1, 'missing nitrogen reference counted');
        $this->assert($summary['irrigation_schedule_latency']['missing_pairs'] === 1, 'missing latency pair counted');
    }

    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    private function assertNear(float $expected, mixed $actual, float $tolerance, string $message): void
    {
        if (!is_numeric($actual) || abs((float) $actual - $expected) > $tolerance) {
            throw new RuntimeException($message . " expected $expected got " . var_export($actual, true));
        }
    }
}

exit((new KpiCalculatorTest())->run());
