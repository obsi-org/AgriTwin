<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Sync;

final class CloudDssClient
{
    public function __construct(
        private string $baseUrl,
        private ?string $token = null,
        private mixed $transport = null
    ) {
    }

    public function submit(array $record, string $idempotencyKey): array
    {
        if (is_callable($this->transport)) {
            return ($this->transport)($record, $idempotencyKey);
        }
        if ($this->baseUrl === '') {
            return ['ok' => false, 'status' => 0, 'error' => 'missing_cloud_dss_url'];
        }
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'status' => 0, 'error' => 'curl extension is not available'];
        }

        $ch = curl_init($this->baseUrl . '/api/edge/sensor-packets.php');
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Idempotency-Key: ' . $idempotencyKey,
        ];
        if ($this->token !== null) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($record, JSON_UNESCAPED_SLASHES),
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'status' => 0, 'error' => $error];
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => json_decode((string) $raw, true), 'error' => $status >= 300 ? 'http_' . $status : null];
    }
}
