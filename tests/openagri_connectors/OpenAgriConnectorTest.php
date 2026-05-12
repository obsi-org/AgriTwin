<?php
require_once __DIR__ . '/../../src/openagri_connectors/autoload.php';

use AgriTwin\OpenAgri\Clients\FarmCalendarClient;
use AgriTwin\OpenAgri\Clients\GatekeeperClient;
use AgriTwin\OpenAgri\Clients\IrrigationServiceClient;
use AgriTwin\OpenAgri\Clients\PestDiseaseServiceClient;
use AgriTwin\OpenAgri\Clients\ReportingServiceClient;
use AgriTwin\OpenAgri\Clients\WeatherServiceClient;
use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Edge\EdgeModeCoordinator;
use AgriTwin\OpenAgri\Support\FileCache;
use AgriTwin\OpenAgri\Support\SyncQueue;

final class OpenAgriConnectorTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int
    {
        $tests = [
            'success normalization' => fn () => $this->testSuccessNormalization(),
            'timeout falls back to cache' => fn () => $this->testTimeoutFallback(),
            'invalid schema falls back to cache' => fn () => $this->testInvalidSchemaFallback(),
            'stale cache returns reduced confidence' => fn () => $this->testStaleCache(),
            'offline mode returns cache' => fn () => $this->testOfflineMode(),
            'missing token/credentials is explicit' => fn () => $this->testMissingGatekeeperCredentials(),
            'restore backfill window lists cached keys' => fn () => $this->testRestoreBackfill(),
            'edge queue priority' => fn () => $this->testEdgeQueuePriority(),
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

    private function testSuccessNormalization(): void
    {
        $config = $this->config();
        $weather = (new WeatherServiceClient($config))->forecast(40.0, -5.0, ['farm_id' => 'farm_demo']);
        $this->assert($weather->ok, 'weather response should be ok');
        $this->assert($weather->data['record_type'] === 'weather_forecast', 'weather record type');
        $this->assert($weather->data['forecasts'][0]['temperature_deg_c'] === 18.2, 'temperature normalized');

        $irrigation = (new IrrigationServiceClient($config))->etoCalculations(1, '2026-05-12', '2026-05-13');
        $this->assert($irrigation->data['record_type'] === 'irrigation_advisory', 'irrigation record type');
        $this->assert(count($irrigation->data['et0_series']) === 2, 'irrigation ET0 series');

        $pest = (new PestDiseaseServiceClient($config))->fuzzyRiskForecast('parcel-demo', '2026-05-12', '2026-05-13');
        $this->assert($pest->data['risks'][0]['risk_index'] === 68.5, 'pest risk normalized');

        $calendar = (new FarmCalendarClient($config))->listOperations('IrrigationOperations');
        $this->assert($calendar->data['records'][0]['operation'] === 'IrrigationOperation', 'calendar operation normalized');

        $report = (new ReportingServiceClient($config))->requestReport('irrigation', ['@graph' => []]);
        $this->assert($report->data['report_id'] === '00000000-0000-4000-8000-000000000001', 'report id normalized');
    }

    private function testTimeoutFallback(): void
    {
        $config = $this->config(['AGRITWIN_OPENAGRI_WEATHER_URL' => 'http://127.0.0.1:18080/timeout', 'AGRITWIN_OPENAGRI_TIMEOUT_SECONDS' => '1']);
        $cache = new FileCache($this->cacheDir());
        $cache->put('weather_forecast_40_-5', [['value' => 19, 'timestamp' => '2026-05-12T08:00:00+00:00', 'measurement_type' => 'ambient_temperature']]);
        $response = (new WeatherServiceClient($config))->forecast(40.0, -5.0);
        $this->assert($response->ok && $response->fromCache, 'timeout should use cache');
    }

    private function testInvalidSchemaFallback(): void
    {
        $config = $this->config(['AGRITWIN_OPENAGRI_GATEKEEPER_URL' => 'http://127.0.0.1:18080/invalid']);
        $cache = new FileCache($this->cacheDir());
        $cache->put('gatekeeper_login', ['success' => true, 'access' => 'cached']);
        putenv('AGRITWIN_OPENAGRI_GATEKEEPER_USERNAME=user');
        putenv('AGRITWIN_OPENAGRI_GATEKEEPER_PASSWORD=pass');
        $response = (new GatekeeperClient($config))->login();
        $this->assert($response->ok && $response->fromCache, 'invalid schema should use cache');
    }

    private function testStaleCache(): void
    {
        $config = $this->config(['AGRITWIN_OPENAGRI_WEATHER_URL' => '']);
        $cache = new FileCache($this->cacheDir());
        $cache->put('weather_forecast_40_-5', [['value' => 18, 'timestamp' => '2026-05-12T08:00:00+00:00', 'measurement_type' => 'ambient_temperature']]);
        $path = $this->cacheDir() . '/weather_forecast_40_-5.json';
        $record = json_decode((string) file_get_contents($path), true);
        $record['stored_at'] = time() - 999999;
        file_put_contents($path, json_encode($record));
        $response = (new WeatherServiceClient($config))->forecast(40.0, -5.0);
        $this->assert($response->ok && $response->stale, 'stale cache should be returned');
        $this->assert($response->data['confidence']['mode'] === 'reduced', 'stale confidence should be reduced');
    }

    private function testOfflineMode(): void
    {
        $config = $this->config(['AGRITWIN_OPENAGRI_OFFLINE_MODE' => 'true']);
        $cache = new FileCache($this->cacheDir());
        $cache->put('weather_forecast_40_-5', [['value' => 20, 'timestamp' => '2026-05-12T08:00:00+00:00', 'measurement_type' => 'ambient_temperature']]);
        $response = (new WeatherServiceClient($config))->forecast(40.0, -5.0);
        $this->assert($response->ok && $response->fromCache, 'offline should use cache');
    }

    private function testMissingGatekeeperCredentials(): void
    {
        putenv('AGRITWIN_OPENAGRI_GATEKEEPER_USERNAME');
        putenv('AGRITWIN_OPENAGRI_GATEKEEPER_PASSWORD');
        $response = (new GatekeeperClient($this->config()))->login();
        $this->assert(!$response->ok && $response->error === 'missing_gatekeeper_credentials', 'missing credentials should fail safely');
    }

    private function testRestoreBackfill(): void
    {
        $config = $this->config();
        $cache = new FileCache($this->cacheDir());
        $cache->put('weather_forecast_backfill_demo', ['demo' => true]);
        $keys = (new WeatherServiceClient($config))->restoreBackfillKeys();
        $this->assert(in_array('weather_forecast_backfill_demo', $keys, true), 'backfill key should be listed');
    }

    private function testEdgeQueuePriority(): void
    {
        $queueDir = $this->queueDir();
        $coordinator = new EdgeModeCoordinator(new SyncQueue($queueDir));
        $coordinator->queueBacklogEvent('farmcalendar', 'activity_sync', ['id' => 1]);
        $coordinator->queueOpenAgriRefresh('weather', ['lat' => 40]);
        $coordinator->queueSensorMeasurement(['sensor' => 'soil']);
        $pending = $coordinator->pendingWork();
        $this->assert($pending[0]['priority'] === 'sensor', 'sensor should be first');
        $this->assert($pending[1]['priority'] === 'openagri', 'openagri refresh should be second');
    }

    private function config(array $overrides = []): OpenAgriConfig
    {
        $this->resetRuntimeDirs();
        $defaults = [
            'AGRITWIN_OPENAGRI_MODE' => 'mock',
            'AGRITWIN_OPENAGRI_CACHE_DIR' => $this->cacheDir(),
            'AGRITWIN_OPENAGRI_QUEUE_DIR' => $this->queueDir(),
            'AGRITWIN_OPENAGRI_TIMEOUT_SECONDS' => '2',
            'AGRITWIN_OPENAGRI_RETRIES' => '0',
            'AGRITWIN_OPENAGRI_BACKOFF_MS' => '1',
            'AGRITWIN_OPENAGRI_CACHE_ENABLED' => 'true',
            'AGRITWIN_OPENAGRI_OFFLINE_MODE' => 'false',
            'AGRITWIN_OPENAGRI_LOG_ENABLED' => 'false',
            'AGRITWIN_OPENAGRI_WEATHER_URL' => 'http://127.0.0.1:18080',
            'AGRITWIN_OPENAGRI_IRRIGATION_URL' => 'http://127.0.0.1:18080',
            'AGRITWIN_OPENAGRI_PEST_DISEASE_URL' => 'http://127.0.0.1:18080',
            'AGRITWIN_OPENAGRI_FARMCALENDAR_URL' => 'http://127.0.0.1:18080',
            'AGRITWIN_OPENAGRI_REPORTING_URL' => 'http://127.0.0.1:18080',
            'AGRITWIN_OPENAGRI_GATEKEEPER_URL' => 'http://127.0.0.1:18080',
        ];
        foreach ($defaults + $overrides as $name => $value) {
            putenv($name . '=' . $value);
        }
        foreach ($overrides as $name => $value) {
            putenv($name . '=' . $value);
        }
        return OpenAgriConfig::fromEnv();
    }

    private function resetRuntimeDirs(): void
    {
        $base = sys_get_temp_dir() . '/agritwin_openagri_tests';
        $this->rrmdir($base);
        mkdir($base . '/cache', 0775, true);
        mkdir($base . '/queue', 0775, true);
    }

    private function cacheDir(): string
    {
        return sys_get_temp_dir() . '/agritwin_openagri_tests/cache';
    }

    private function queueDir(): string
    {
        return sys_get_temp_dir() . '/agritwin_openagri_tests/queue';
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

exit((new OpenAgriConnectorTest())->run());
