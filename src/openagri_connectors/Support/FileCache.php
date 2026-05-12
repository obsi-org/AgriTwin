<?php
namespace AgriTwin\OpenAgri\Support;

final class FileCache
{
    public function __construct(private string $directory)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
    }

    public function put(string $key, array $payload): void
    {
        $record = [
            'stored_at' => time(),
            'payload' => $payload,
        ];
        file_put_contents($this->path($key), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function get(string $key, ?int $ttlSeconds = null): ?array
    {
        $path = $this->path($key);
        if (!is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded) || !isset($decoded['stored_at'], $decoded['payload'])) {
            return null;
        }

        if ($ttlSeconds !== null && time() - (int) $decoded['stored_at'] > $ttlSeconds) {
            return null;
        }

        return [
            'stored_at' => (int) $decoded['stored_at'],
            'age_seconds' => time() - (int) $decoded['stored_at'],
            'payload' => $decoded['payload'],
        ];
    }

    public function getStale(string $key): ?array
    {
        return $this->get($key, null);
    }

    public function keysModifiedSince(int $sinceEpoch): array
    {
        $keys = [];
        foreach (glob($this->directory . '/*.json') ?: [] as $path) {
            if (filemtime($path) >= $sinceEpoch) {
                $keys[] = basename($path, '.json');
            }
        }
        return $keys;
    }

    private function path(string $key): string
    {
        return $this->directory . '/' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key) . '.json';
    }
}

