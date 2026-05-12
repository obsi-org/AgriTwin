<?php
namespace AgriTwin\OpenAgri\Support;

final class Response
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?array $data,
        public readonly int $statusCode = 0,
        public readonly ?string $error = null,
        public readonly bool $fromCache = false,
        public readonly bool $stale = false,
        public readonly array $meta = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'status_code' => $this->statusCode,
            'error' => $this->error,
            'from_cache' => $this->fromCache,
            'stale' => $this->stale,
            'meta' => $this->meta,
            'data' => $this->data,
        ];
    }
}

