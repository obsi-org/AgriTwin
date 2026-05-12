<?php
namespace AgriTwin\Edge\Config;

final class EdgeConfig
{
    public function __construct(
        public readonly string $gatewayId,
        public readonly string $queueDir,
        public readonly string $cacheDir,
        public readonly string $cloudDssUrl,
        public readonly ?string $cloudDssToken,
        public readonly int $samplingIntervalMinutes,
        public readonly int $timeoutSeconds,
        public readonly int $maxRetries,
        public readonly int $backoffSeconds,
        public readonly int $openAgriStaleSeconds,
        public readonly bool $forceOffline
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            self::env('AGRITWIN_EDGE_GATEWAY_ID', 'edge-demo-001'),
            self::env('AGRITWIN_EDGE_QUEUE_DIR', __DIR__ . '/../../../var/edge/queue'),
            self::env('AGRITWIN_EDGE_CACHE_DIR', __DIR__ . '/../../../var/edge/cache'),
            rtrim(self::env('AGRITWIN_CLOUD_DSS_URL', ''), '/'),
            self::envNullable('AGRITWIN_CLOUD_DSS_TOKEN'),
            self::int('AGRITWIN_EDGE_SAMPLING_INTERVAL_MINUTES', 15),
            self::int('AGRITWIN_EDGE_TIMEOUT_SECONDS', 10),
            self::int('AGRITWIN_EDGE_MAX_RETRIES', 3),
            self::int('AGRITWIN_EDGE_BACKOFF_SECONDS', 30),
            self::int('AGRITWIN_EDGE_OPENAGRI_STALE_SECONDS', 21600),
            self::bool('AGRITWIN_EDGE_FORCE_OFFLINE', false)
        );
    }

    private static function env(string $name, string $default): string
    {
        $value = getenv($name);
        return $value === false || $value === '' ? $default : $value;
    }

    private static function envNullable(string $name): ?string
    {
        $value = getenv($name);
        return $value === false || $value === '' ? null : $value;
    }

    private static function int(string $name, int $default): int
    {
        $value = getenv($name);
        return $value === false || $value === '' ? $default : (int) $value;
    }

    private static function bool(string $name, bool $default): bool
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
