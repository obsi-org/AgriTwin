<?php
namespace AgriTwin\OpenAgri\Clients;

use AgriTwin\OpenAgri\Adapters\Normalizers;
use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Support\Response;

final class FarmCalendarClient extends BaseOpenAgriClient
{
    public function __construct(OpenAgriConfig $config)
    {
        parent::__construct($config, 'farmcalendar');
    }

    public function listOperations(string $operationEndpoint = 'IrrigationOperations', array $query = [], array $context = []): Response
    {
        $endpoint = trim($operationEndpoint, '/');
        $cacheKey = 'farmcalendar_' . strtolower($endpoint) . '_' . md5(json_encode($query));
        $response = $this->get('/api/v1/' . $endpoint . '/', $query + ['format' => 'jsonld'], $cacheKey, fn (array $d): bool => isset($d['@graph']) || $d !== []);
        return $this->withNormalized($response, $context);
    }

    public function createOperation(string $operationEndpoint, array $jsonLdPayload): Response
    {
        $endpoint = trim($operationEndpoint, '/');
        return $this->post('/api/v1/' . $endpoint . '/', $jsonLdPayload, null, fn (array $d): bool => $d !== []);
    }

    public function listParcels(array $context = []): Response
    {
        return $this->listOperations('FarmParcels', [], $context);
    }

    public function listAssets(array $context = []): Response
    {
        return $this->listOperations('AgriculturalMachines', [], $context);
    }

    private function withNormalized(Response $response, array $context): Response
    {
        if (!$response->ok || $response->data === null) {
            return $response;
        }
        $ctx = $context + $response->meta + ['from_cache' => $response->fromCache, 'stale' => $response->stale];
        return new Response(true, Normalizers::farmCalendar($response->data, $ctx), $response->statusCode, $response->error, $response->fromCache, $response->stale, $response->meta);
    }
}

