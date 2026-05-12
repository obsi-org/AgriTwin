<?php
namespace AgriTwin\OpenAgri\Clients;

use AgriTwin\OpenAgri\Adapters\Normalizers;
use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Support\Response;

final class WeatherServiceClient extends BaseOpenAgriClient
{
    public function __construct(OpenAgriConfig $config)
    {
        parent::__construct($config, 'weather');
    }

    public function forecast(float $lat, float $lon, array $context = []): Response
    {
        $cacheKey = sprintf('weather_forecast_%s_%s', round($lat, 5), round($lon, 5));
        $response = $this->get('/api/data/forecast5', ['lat' => $lat, 'lon' => $lon], $cacheKey, fn (array $d): bool => $d !== []);
        return $this->withNormalized($response, fn (array $data, array $ctx): array => Normalizers::weatherForecast($data, $ctx), $context);
    }

    public function current(float $lat, float $lon, array $context = []): Response
    {
        $cacheKey = sprintf('weather_current_%s_%s', round($lat, 5), round($lon, 5));
        $response = $this->get('/api/data/weather', ['lat' => $lat, 'lon' => $lon], $cacheKey, fn (array $d): bool => isset($d['data']) || $d !== []);
        return $this->withNormalized($response, fn (array $data, array $ctx): array => Normalizers::weatherForecast($data, $ctx), $context);
    }

    private function withNormalized(Response $response, callable $normalizer, array $context): Response
    {
        if (!$response->ok || $response->data === null) {
            return $response;
        }
        $ctx = $context + $response->meta + ['from_cache' => $response->fromCache, 'stale' => $response->stale];
        return new Response(true, $normalizer($response->data, $ctx), $response->statusCode, $response->error, $response->fromCache, $response->stale, $response->meta);
    }
}

