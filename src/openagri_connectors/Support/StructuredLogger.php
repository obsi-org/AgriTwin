<?php
namespace AgriTwin\OpenAgri\Support;

final class StructuredLogger
{
    public function __construct(private bool $enabled = true)
    {
    }

    public function info(string $event, array $context = []): void
    {
        $this->log('info', $event, $context);
    }

    public function warning(string $event, array $context = []): void
    {
        $this->log('warning', $event, $context);
    }

    public function error(string $event, array $context = []): void
    {
        $this->log('error', $event, $context);
    }

    private function log(string $level, string $event, array $context): void
    {
        if (!$this->enabled) {
            return;
        }

        $record = [
            'ts' => gmdate('c'),
            'level' => $level,
            'event' => $event,
            'context' => $this->redact($context),
        ];
        fwrite(STDERR, json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    private function redact(array $context): array
    {
        $redacted = [];
        foreach ($context as $key => $value) {
            if (preg_match('/token|secret|password|authorization|api[_-]?key/i', (string) $key)) {
                $redacted[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $redacted[$key] = $this->redact($value);
            } else {
                $redacted[$key] = $value;
            }
        }
        return $redacted;
    }
}

