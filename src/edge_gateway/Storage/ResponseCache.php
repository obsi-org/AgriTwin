<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Storage;

final class ResponseCache
{
    public function __construct(private string $directory)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
    }

    public function put(string $service, array $payload, ?int $storedAt = null): void
    {
        $record = [
            'service' => $service,
            'stored_at' => $storedAt ?? time(),
            'payload' => $payload,
        ];
        file_put_contents($this->path($service), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    public function last(string $service): ?array
    {
        $path = $this->path($service);
        if (!is_file($path)) {
            return null;
        }
        $record = json_decode((string) file_get_contents($path), true);
        if (!is_array($record) || !isset($record['stored_at'], $record['payload'])) {
            return null;
        }
        $record['age_seconds'] = time() - (int) $record['stored_at'];
        return $record;
    }

    public function isStale(string $service, int $ttlSeconds): bool
    {
        $record = $this->last($service);
        return $record === null || (int) $record['age_seconds'] > $ttlSeconds;
    }

    private function path(string $service): string
    {
        return $this->directory . '/' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $service) . '.json';
    }
}
