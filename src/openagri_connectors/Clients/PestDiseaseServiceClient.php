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

final class PestDiseaseServiceClient extends BaseOpenAgriClient
{
    public function __construct(OpenAgriConfig $config)
    {
        parent::__construct($config, 'pest_disease');
    }

    public function fuzzyRiskForecast(string $parcelId, string $fromDate, string $toDate, array $threatModelIds = [], array $context = []): Response
    {
        $cacheKey = sprintf('pest_disease_forecast_%s_%s_%s_%s', $parcelId, $fromDate, $toDate, md5(json_encode($threatModelIds)));
        $body = [
            'parcel_id' => $parcelId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'threat_model_ids' => $threatModelIds,
        ];
        $response = $this->post('/api/v1/fuzzy-risk/forecast/', $body, $cacheKey, fn (array $d): bool => $d !== []);
        return $this->withNormalized($response, $context);
    }

    public function gdd(string $parcelId, string $modelIds, string $fromDate, string $toDate, array $context = []): Response
    {
        $cacheKey = sprintf('pest_disease_gdd_%s_%s_%s_%s', $parcelId, $modelIds, $fromDate, $toDate);
        $path = sprintf('/api/v1/tool/calculate-gdd/parcel/%s/model/%s/verbose/%s/from/%s/to/', rawurlencode($parcelId), rawurlencode($modelIds), rawurlencode($fromDate), rawurlencode($toDate));
        $response = $this->get($path, [], $cacheKey, fn (array $d): bool => isset($d['models']) || $d !== []);
        return $this->withNormalized($response, $context);
    }

    private function withNormalized(Response $response, array $context): Response
    {
        if (!$response->ok || $response->data === null) {
            return $response;
        }
        $ctx = $context + $response->meta + ['from_cache' => $response->fromCache, 'stale' => $response->stale];
        return new Response(true, Normalizers::pestDisease($response->data, $ctx), $response->statusCode, $response->error, $response->fromCache, $response->stale, $response->meta);
    }
}

