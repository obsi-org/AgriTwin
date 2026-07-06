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

final class ReportingServiceClient extends BaseOpenAgriClient
{
    public function __construct(OpenAgriConfig $config)
    {
        parent::__construct($config, 'reporting');
    }

    public function requestReport(string $type, array $jsonLdPayload, array $context = []): Response
    {
        $allowed = ['irrigation', 'fertilization', 'pesticides', 'standalone-observation', 'field-notebook', 'advisory'];
        $normalizedType = in_array($type, $allowed, true) ? $type : 'standalone-observation';
        $endpointType = $normalizedType === 'advisory' ? 'standalone-observation' : $normalizedType;
        $path = '/api/v1/openagri-report/' . $endpointType . '-report/';
        $response = $this->post($path, $jsonLdPayload, null, fn (array $d): bool => $d !== []);
        if (!$response->ok || $response->data === null) {
            return $response;
        }
        $ctx = $context + $response->meta + [
            'from_cache' => $response->fromCache,
            'stale' => $response->stale,
            'report_type' => $normalizedType,
        ];
        return new Response(true, Normalizers::reporting($response->data, $ctx), $response->statusCode, $response->error, $response->fromCache, $response->stale, $response->meta);
    }

    public function download(string $reportId, bool $standalone = false): Response
    {
        $path = $standalone
            ? '/api/v1/openagri-report/standalone-observation-report/' . rawurlencode($reportId) . '/'
            : '/api/v1/openagri-report/' . rawurlencode($reportId) . '/';
        return $this->get($path, [], 'reporting_download_' . $reportId);
    }
}

