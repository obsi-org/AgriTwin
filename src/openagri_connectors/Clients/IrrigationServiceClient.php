<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\OpenAgri\Clients;

use AgriTwin\OpenAgri\Adapters\Normalizers;
use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Support\Response;

final class IrrigationServiceClient extends BaseOpenAgriClient
{
    public function __construct(OpenAgriConfig $config)
    {
        parent::__construct($config, 'irrigation');
    }

    public function etoCalculations(int|string $locationId, string $fromDate, string $toDate, array $context = []): Response
    {
        $cacheKey = sprintf('irrigation_eto_%s_%s_%s', $locationId, $fromDate, $toDate);
        $path = sprintf('/api/v1/eto/get-calculations/%s/from/%s/to/%s/', rawurlencode((string) $locationId), rawurlencode($fromDate), rawurlencode($toDate));
        $response = $this->get($path, ['formatting' => 'JSON'], $cacheKey, fn (array $d): bool => isset($d['@graph']) || $d !== []);
        return $this->withNormalized($response, $context);
    }

    public function createParcelLocation(string $wktPolygon): Response
    {
        return $this->post('/api/v1/location/parcel-wkt/', ['coordinates' => $wktPolygon], null, fn (array $d): bool => isset($d['message']) || $d !== []);
    }

    private function withNormalized(Response $response, array $context): Response
    {
        if (!$response->ok || $response->data === null) {
            return $response;
        }
        $ctx = $context + $response->meta + ['from_cache' => $response->fromCache, 'stale' => $response->stale];
        return new Response(true, Normalizers::irrigation($response->data, $ctx), $response->statusCode, $response->error, $response->fromCache, $response->stale, $response->meta);
    }
}

