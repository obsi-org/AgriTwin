<?php
namespace AgriTwin\OpenAgri\Clients;

use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Http\HttpClient;
use AgriTwin\OpenAgri\Support\FileCache;
use AgriTwin\OpenAgri\Support\Response;
use AgriTwin\OpenAgri\Support\StructuredLogger;

abstract class BaseOpenAgriClient
{
    protected FileCache $cache;
    protected HttpClient $http;
    protected StructuredLogger $logger;

    public function __construct(
        protected OpenAgriConfig $config,
        protected string $service,
        ?FileCache $cache = null,
        ?HttpClient $http = null,
        ?StructuredLogger $logger = null
    ) {
        $this->logger = $logger ?? new StructuredLogger($config->logEnabled);
        $this->cache = $cache ?? new FileCache($config->cacheDir);
        $this->http = $http ?? new HttpClient($config->timeoutSeconds, $config->retries, $config->backoffMs, $this->logger);
    }

    protected function get(string $path, array $query = [], ?string $cacheKey = null, ?callable $validator = null): Response
    {
        return $this->request('GET', $path, $query, null, $cacheKey, $validator);
    }

    protected function post(string $path, array $body = [], ?string $cacheKey = null, ?callable $validator = null): Response
    {
        return $this->request('POST', $path, [], $body, $cacheKey, $validator);
    }

    protected function request(string $method, string $path, array $query = [], mixed $body = null, ?string $cacheKey = null, ?callable $validator = null): Response
    {
        $cacheKey ??= $this->service . ':' . $method . ':' . $path . ':' . md5(json_encode([$query, $body]));
        $ttl = $this->config->ttl($this->service);

        if ($this->config->offlineMode) {
            return $this->cachedOrFailure($cacheKey, 'offline_mode');
        }

        $baseUrl = $this->config->baseUrl($this->service);
        if ($baseUrl === '') {
            return $this->cachedOrFailure($cacheKey, 'missing_base_url');
        }

        $url = $baseUrl . '/' . ltrim($path, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = ['Accept' => 'application/json'];
        $token = $this->config->token($this->service);
        if ($token !== null) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $result = $this->http->request($method, $url, $headers, $body);
        if (($result['error'] ?? null) !== null || ($result['status'] ?? 0) < 200 || ($result['status'] ?? 0) >= 300) {
            return $this->cachedOrFailure($cacheKey, (string) ($result['error'] ?? 'http_error'), (int) ($result['status'] ?? 0));
        }

        $data = $result['body'] ?? null;
        if (!is_array($data)) {
            return $this->cachedOrFailure($cacheKey, 'invalid_json', (int) ($result['status'] ?? 0));
        }
        if ($validator !== null && !$validator($data)) {
            return $this->cachedOrFailure($cacheKey, 'invalid_schema', (int) ($result['status'] ?? 0));
        }

        if ($this->config->cacheEnabled && $ttl >= 0) {
            $this->cache->put($cacheKey, $data);
        }

        $this->logger->info('openagri_request_success', [
            'service' => $this->service,
            'method' => $method,
            'path' => $path,
            'status' => $result['status'] ?? 0,
            'attempts' => $result['attempts'] ?? 1,
        ]);

        return new Response(true, $data, (int) ($result['status'] ?? 200), null, false, false, [
            'service' => $this->service,
            'cache_key' => $cacheKey,
        ]);
    }

    protected function cachedOrFailure(string $cacheKey, string $error, int $statusCode = 0): Response
    {
        if ($this->config->cacheEnabled) {
            $cached = $this->cache->get($cacheKey, $this->config->ttl($this->service));
            if ($cached !== null) {
                $this->logger->warning('openagri_cache_fallback', [
                    'service' => $this->service,
                    'cache_key' => $cacheKey,
                    'error' => $error,
                    'age_seconds' => $cached['age_seconds'],
                ]);
                return new Response(true, $cached['payload'], $statusCode, $error, true, false, [
                    'service' => $this->service,
                    'age_seconds' => $cached['age_seconds'],
                    'confidence_mode' => 'normal',
                ]);
            }

            $stale = $this->cache->getStale($cacheKey);
            if ($stale !== null) {
                return new Response(true, $stale['payload'], $statusCode, $error, true, true, [
                    'service' => $this->service,
                    'age_seconds' => $stale['age_seconds'],
                    'confidence_mode' => 'reduced',
                ]);
            }
        }

        $this->logger->error('openagri_request_failed', [
            'service' => $this->service,
            'cache_key' => $cacheKey,
            'error' => $error,
            'status' => $statusCode,
        ]);
        return new Response(false, null, $statusCode, $error, false, false, ['service' => $this->service]);
    }

    public function restoreBackfillKeys(): array
    {
        $since = time() - $this->config->restoreWindowSeconds();
        return array_values(array_filter(
            $this->cache->keysModifiedSince($since),
            fn (string $key): bool => str_starts_with($key, $this->service . '_') || str_starts_with($key, $this->service . ':')
        ));
    }
}

