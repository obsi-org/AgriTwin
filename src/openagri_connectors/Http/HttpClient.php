<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\OpenAgri\Http;

use AgriTwin\OpenAgri\Support\StructuredLogger;

final class HttpClient
{
    public function __construct(
        private int $timeoutSeconds,
        private int $retries,
        private int $backoffMs,
        private StructuredLogger $logger
    ) {
    }

    public function request(string $method, string $url, array $headers = [], mixed $body = null): array
    {
        $attempt = 0;
        $last = ['status' => 0, 'body' => null, 'error' => 'not attempted'];

        while ($attempt <= $this->retries) {
            $attempt++;
            $last = $this->send($method, $url, $headers, $body);
            if ($last['error'] === null && $last['status'] < 500 && $last['status'] !== 429) {
                return $last + ['attempts' => $attempt];
            }

            $this->logger->warning('openagri_http_retry', [
                'method' => $method,
                'url' => $this->safeUrl($url),
                'status' => $last['status'],
                'error' => $last['error'],
                'attempt' => $attempt,
            ]);
            if ($attempt <= $this->retries) {
                usleep($this->backoffMs * 1000 * (2 ** ($attempt - 1)));
            }
        }

        return $last + ['attempts' => $attempt];
    }

    private function send(string $method, string $url, array $headers, mixed $body): array
    {
        if (!function_exists('curl_init')) {
            return ['status' => 0, 'body' => null, 'headers' => '', 'error' => 'curl extension is not available'];
        }

        $ch = curl_init($url);
        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        $payload = null;
        if ($body !== null) {
            $payload = is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_SLASHES);
            if (!$this->hasHeader($headerLines, 'Content-Type')) {
                $headerLines[] = 'Content-Type: application/json';
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => $headerLines,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['status' => 0, 'body' => null, 'headers' => '', 'error' => $error];
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $rawHeaders = substr((string) $raw, 0, $headerSize);
        $rawBody = substr((string) $raw, $headerSize);
        $decoded = json_decode($rawBody, true);
        $bodyValue = json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $rawBody];

        return [
            'status' => $status,
            'body' => $bodyValue,
            'headers' => $rawHeaders,
            'error' => $status >= 400 ? 'http_' . $status : null,
        ];
    }

    private function hasHeader(array $headers, string $name): bool
    {
        foreach ($headers as $line) {
            if (stripos($line, $name . ':') === 0) {
                return true;
            }
        }
        return false;
    }

    private function safeUrl(string $url): string
    {
        $parts = parse_url($url);
        if (!$parts) {
            return $url;
        }
        $safe = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? '');
        if (isset($parts['port'])) {
            $safe .= ':' . $parts['port'];
        }
        $safe .= $parts['path'] ?? '';
        return $safe;
    }
}

