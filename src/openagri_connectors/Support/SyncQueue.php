<?php
namespace AgriTwin\OpenAgri\Support;

final class SyncQueue
{
    public function __construct(private string $directory)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
    }

    public function enqueue(string $service, string $eventType, array $payload, string $priority = 'backlog'): string
    {
        $id = gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
        $record = [
            'id' => $id,
            'service' => $service,
            'event_type' => $eventType,
            'priority' => $priority,
            'created_at' => gmdate('c'),
            'attempts' => 0,
            'payload' => $payload,
        ];
        file_put_contents($this->directory . '/' . $id . '.json', json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $id;
    }

    public function list(string $priorityOrder = 'sensor,openagri,backlog'): array
    {
        $order = array_flip(array_map('trim', explode(',', $priorityOrder)));
        $records = [];
        foreach (glob($this->directory . '/*.json') ?: [] as $path) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $decoded['_path'] = $path;
                $records[] = $decoded;
            }
        }

        usort($records, function (array $a, array $b) use ($order): int {
            $pa = $order[$a['priority'] ?? 'backlog'] ?? 99;
            $pb = $order[$b['priority'] ?? 'backlog'] ?? 99;
            return $pa <=> $pb ?: strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
        });

        return $records;
    }

    public function remove(string $id): void
    {
        $path = $this->directory . '/' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $id) . '.json';
        if (is_file($path)) {
            unlink($path);
        }
    }
}

