<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

require_once __DIR__ . '/../../src/edge_gateway/autoload.php';

use AgriTwin\Edge\Config\EdgeConfig;
use AgriTwin\Edge\Connectivity\ConnectivityDetector;
use AgriTwin\Edge\Inference\LocalFallbackEngine;
use AgriTwin\Edge\Recommendation\RecommendationApi;
use AgriTwin\Edge\Sensor\SensorPacketSchema;
use AgriTwin\Edge\Storage\DurableQueue;
use AgriTwin\Edge\Storage\ResponseCache;
use AgriTwin\Edge\Sync\CloudDssClient;
use AgriTwin\Edge\Sync\SyncWorker;

final class EdgeGatewayTest
{
    private int $passed = 0;
    private int $failed = 0;
    private string $baseDir;

    public function run(): int
    {
        $tests = [
            'normal connectivity' => fn () => $this->testNormalConnectivity(),
            '4G outage queues locally' => fn () => $this->testOutageQueuesLocally(),
            'service timeout retries later' => fn () => $this->testServiceTimeout(),
            'duplicate packet is idempotent' => fn () => $this->testDuplicatePacket(),
            'reconnect and backfill' => fn () => $this->testReconnectBackfill(),
            'stale OpenAgri cache reduces confidence' => fn () => $this->testStaleOpenAgriCache(),
        ];

        foreach ($tests as $name => $test) {
            try {
                $this->reset();
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

    private function testNormalConnectivity(): void
    {
        $api = $this->api();
        $result = $api->ingestSensorPacket($this->packet('pkt-normal'));
        $this->assert($result['packet']['sampling_interval_minutes'] === 15, 'default 15-minute sampling');
        $this->assert($result['recommendation']['record_type'] === 'recommendation', 'canonical recommendation returned');

        $worker = $this->worker(fn () => true, fn () => ['ok' => true, 'status' => 202]);
        $sync = $worker->run();
        $this->assert($sync['synced'] === 1, 'packet synced');
        $this->assert($sync['remaining'] === 0, 'queue empty after sync');
    }

    private function testOutageQueuesLocally(): void
    {
        $this->api()->ingestSensorPacket($this->packet('pkt-outage'));
        $sync = $this->worker(fn () => ['online' => false, 'reason' => '4g_unavailable'], fn () => ['ok' => true])->run();
        $this->assert($sync['online'] === false, 'offline detected');
        $this->assert($sync['remaining'] === 1, 'packet retained during outage');
    }

    private function testServiceTimeout(): void
    {
        $this->api()->ingestSensorPacket($this->packet('pkt-timeout'));
        $worker = $this->worker(fn () => true, fn () => ['ok' => false, 'status' => 0, 'error' => 'timeout']);
        $sync = $worker->run();
        $this->assert($sync['failed'] === 1, 'timeout counted as failure');
        $records = $this->queue()->all();
        $this->assert($records[0]['attempts'] === 1, 'attempt incremented');
        $this->assert(($records[0]['last_error'] ?? '') === 'timeout', 'timeout reason stored');
    }

    private function testDuplicatePacket(): void
    {
        $api = $this->api();
        $first = $api->ingestSensorPacket($this->packet('pkt-duplicate'));
        $second = $api->ingestSensorPacket($this->packet('pkt-duplicate'));
        $this->assert($first['queue_id'] === $second['queue_id'], 'duplicate uses same queue item');
        $this->assert($this->queue()->count() === 1, 'duplicate not enqueued twice');
    }

    private function testReconnectBackfill(): void
    {
        $api = $this->api();
        $api->ingestSensorPacket($this->packet('pkt-backfill-1'));
        $api->ingestSensorPacket($this->packet('pkt-backfill-2', ['soil_moisture' => 0.18]));
        $offline = $this->worker(fn () => false, fn () => ['ok' => true])->run();
        $this->assert($offline['remaining'] === 2, 'two packets queued while offline');

        $sent = [];
        $online = $this->worker(fn () => true, function (array $record, string $key) use (&$sent): array {
            $sent[] = $key;
            return ['ok' => true, 'status' => 202];
        })->run(10);
        $this->assert($online['synced'] === 2, 'backfill synced after reconnect');
        $this->assert($this->queue()->count() === 0, 'queue empty after backfill');
        $this->assert(count($sent) === 2, 'two idempotent writes sent');
    }

    private function testStaleOpenAgriCache(): void
    {
        $cache = $this->cache();
        $cache->put('weather', ['forecast' => 'old'], time() - 999999);
        $cache->put('irrigation', ['advisory' => 'old'], time() - 999999);
        $cache->put('pest_disease', ['risk' => 'old'], time() - 999999);
        $result = $this->api(cache: $cache)->ingestSensorPacket($this->packet('pkt-stale'));
        $this->assert($result['recommendation']['confidence']['mode'] === 'reduced', 'stale cache reduces confidence');
        $this->assert($result['recommendation']['pest_risk']['risk_index'] >= 45, 'local pest fallback produces risk');
    }

    private function api(?ResponseCache $cache = null): RecommendationApi
    {
        $config = $this->config();
        $cache ??= $this->cache();
        foreach (['weather', 'irrigation', 'pest_disease'] as $service) {
            if ($cache->last($service) === null) {
                $cache->put($service, ['synthetic' => true], time());
            }
        }
        return new RecommendationApi(
            $config,
            new SensorPacketSchema(),
            $this->queue(),
            new LocalFallbackEngine($cache, $config->openAgriStaleSeconds)
        );
    }

    private function worker(callable $probe, callable $transport): SyncWorker
    {
        $config = $this->config();
        return new SyncWorker(
            $this->queue(),
            new ConnectivityDetector(false, $probe),
            new CloudDssClient($config->cloudDssUrl, $config->cloudDssToken, $transport),
            1,
            3
        );
    }

    private function config(): EdgeConfig
    {
        return new EdgeConfig(
            'edge-test-001',
            $this->baseDir . '/queue',
            $this->baseDir . '/cache',
            'http://cloud-dss.test',
            null,
            15,
            2,
            3,
            1,
            3600,
            false
        );
    }

    private function queue(): DurableQueue
    {
        return new DurableQueue($this->baseDir . '/queue');
    }

    private function cache(): ResponseCache
    {
        return new ResponseCache($this->baseDir . '/cache');
    }

    private function packet(string $packetId, array $overrides = []): array
    {
        $values = array_merge([
            'soil_moisture' => 0.19,
            'soil_temperature' => 19.6,
            'air_temperature' => 31.0,
            'humidity' => 78.0,
            'leaf_wetness' => 65.0,
            'pump_energy_kwh' => 0.42,
        ], $overrides);

        return [
            'gateway_id' => 'edge-test-001',
            'packet_id' => $packetId,
            'farm_id' => 'farm_demo_001',
            'parcel_id' => 'parcel_demo_almond_01',
            'observed_at' => '2026-05-12T08:00:00Z',
            'readings' => array_map(
                fn (string $parameter, float $value): array => [
                    'asset_id' => 'asset_' . $parameter,
                    'parameter' => $parameter,
                    'value' => $value,
                    'quality' => ['flag' => 'ok', 'rssi_dbm' => -74],
                ],
                array_keys($values),
                array_values($values)
            ),
        ];
    }

    private function reset(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/agritwin_edge_gateway_tests_' . bin2hex(random_bytes(3));
        $this->rrmdir($this->baseDir);
        mkdir($this->baseDir . '/queue', 0775, true);
        mkdir($this->baseDir . '/cache', 0775, true);
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }
}

exit((new EdgeGatewayTest())->run());
