<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Kpi;

final class KpiEventStore
{
    public function __construct(private string $path)
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    public function append(array $event): void
    {
        $event = $this->normalizeEvent($event);
        file_put_contents($this->path, json_encode($event, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function replaceAll(array $events): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($this->path, '');
        foreach ($events as $event) {
            $this->append($event);
        }
    }

    public function all(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $events = [];
        foreach (file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $events[] = $decoded;
            }
        }
        return $events;
    }

    public function path(): string
    {
        return $this->path;
    }

    private function normalizeEvent(array $event): array
    {
        $event['event_id'] ??= 'evt_' . bin2hex(random_bytes(8));
        $event['recorded_at'] ??= gmdate('c');
        $event['source'] ??= ['system' => 'agritwin_kpi'];
        $event['privacy'] ??= ['synthetic' => true, 'raw_pilot_data' => false];
        return $event;
    }
}
