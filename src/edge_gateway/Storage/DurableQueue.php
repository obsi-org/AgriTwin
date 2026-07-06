<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Storage;

final class DurableQueue
{
    public function __construct(private string $directory)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
    }

    public function enqueue(string $eventType, array $payload, string $priority = 'sensor', ?string $idempotencyKey = null): string
    {
        $idempotencyKey ??= $payload['sync']['idempotency_key'] ?? $eventType . ':' . hash('sha256', json_encode($payload));
        $existing = $this->findByIdempotencyKey($idempotencyKey);
        if ($existing !== null) {
            return $existing['id'];
        }

        $id = gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
        $record = [
            'id' => $id,
            'event_type' => $eventType,
            'priority' => $priority,
            'idempotency_key' => $idempotencyKey,
            'created_at' => gmdate('c'),
            'attempts' => 0,
            'next_attempt_at' => gmdate('c'),
            'payload' => $payload,
        ];
        $this->write($id, $record);
        return $id;
    }

    public function due(string $priorityOrder = 'sensor,openagri,backlog'): array
    {
        $now = time();
        return array_values(array_filter($this->all($priorityOrder), function (array $record) use ($now): bool {
            return strtotime($record['next_attempt_at'] ?? 'now') <= $now;
        }));
    }

    public function all(string $priorityOrder = 'sensor,openagri,backlog'): array
    {
        $order = array_flip(array_map('trim', explode(',', $priorityOrder)));
        $records = [];
        foreach (glob($this->directory . '/*.json') ?: [] as $path) {
            $record = json_decode((string) file_get_contents($path), true);
            if (is_array($record)) {
                $record['_path'] = $path;
                $records[] = $record;
            }
        }
        usort($records, function (array $a, array $b) use ($order): int {
            $pa = $order[$a['priority'] ?? 'backlog'] ?? 99;
            $pb = $order[$b['priority'] ?? 'backlog'] ?? 99;
            return $pa <=> $pb ?: strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
        });
        return $records;
    }

    public function ack(string $id): void
    {
        $path = $this->path($id);
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function markFailure(string $id, string $reason, int $backoffSeconds, int $maxRetries): void
    {
        $path = $this->path($id);
        if (!is_file($path)) {
            return;
        }
        $record = json_decode((string) file_get_contents($path), true);
        if (!is_array($record)) {
            return;
        }
        $record['attempts'] = ((int) ($record['attempts'] ?? 0)) + 1;
        $record['last_error'] = $reason;
        $delay = $backoffSeconds * (2 ** max(0, $record['attempts'] - 1));
        $record['next_attempt_at'] = gmdate('c', time() + $delay);
        $record['failed_permanently'] = $record['attempts'] >= $maxRetries;
        $this->write($id, $record);
    }

    public function count(): int
    {
        return count(glob($this->directory . '/*.json') ?: []);
    }

    private function findByIdempotencyKey(string $idempotencyKey): ?array
    {
        foreach ($this->all() as $record) {
            if (($record['idempotency_key'] ?? null) === $idempotencyKey) {
                return $record;
            }
        }
        return null;
    }

    private function write(string $id, array $record): void
    {
        file_put_contents($this->path($id), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function path(string $id): string
    {
        return $this->directory . '/' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $id) . '.json';
    }
}
