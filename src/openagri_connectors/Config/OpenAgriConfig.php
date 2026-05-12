<?php
namespace AgriTwin\OpenAgri\Config;

use AgriTwin\OpenAgri\Support\Env;

final class OpenAgriConfig
{
    public function __construct(
        public readonly string $mode,
        public readonly string $cacheDir,
        public readonly string $queueDir,
        public readonly int $timeoutSeconds,
        public readonly int $retries,
        public readonly int $backoffMs,
        public readonly bool $cacheEnabled,
        public readonly bool $offlineMode,
        public readonly bool $logEnabled
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            Env::get('AGRITWIN_OPENAGRI_MODE', 'mock') ?? 'mock',
            Env::get('AGRITWIN_OPENAGRI_CACHE_DIR', __DIR__ . '/../../../var/cache/openagri') ?? __DIR__ . '/../../../var/cache/openagri',
            Env::get('AGRITWIN_OPENAGRI_QUEUE_DIR', __DIR__ . '/../../../var/queue/openagri') ?? __DIR__ . '/../../../var/queue/openagri',
            Env::int('AGRITWIN_OPENAGRI_TIMEOUT_SECONDS', 10),
            Env::int('AGRITWIN_OPENAGRI_RETRIES', 2),
            Env::int('AGRITWIN_OPENAGRI_BACKOFF_MS', 250),
            Env::bool('AGRITWIN_OPENAGRI_CACHE_ENABLED', true),
            Env::bool('AGRITWIN_OPENAGRI_OFFLINE_MODE', false),
            Env::bool('AGRITWIN_OPENAGRI_LOG_ENABLED', true)
        );
    }

    public function baseUrl(string $service): string
    {
        $env = match ($service) {
            'gatekeeper' => 'AGRITWIN_OPENAGRI_GATEKEEPER_URL',
            'weather' => 'AGRITWIN_OPENAGRI_WEATHER_URL',
            'irrigation' => 'AGRITWIN_OPENAGRI_IRRIGATION_URL',
            'pest_disease' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_URL',
            'farmcalendar' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_URL',
            'reporting' => 'AGRITWIN_OPENAGRI_REPORTING_URL',
            default => '',
        };
        return rtrim(Env::get($env, ''), '/');
    }

    public function token(string $service): ?string
    {
        $env = match ($service) {
            'weather' => 'AGRITWIN_OPENAGRI_WEATHER_TOKEN',
            'irrigation' => 'AGRITWIN_OPENAGRI_IRRIGATION_TOKEN',
            'pest_disease' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_TOKEN',
            'farmcalendar' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_TOKEN',
            'reporting' => 'AGRITWIN_OPENAGRI_REPORTING_TOKEN',
            default => 'AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN',
        };
        return Env::get($env) ?? Env::get('AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN');
    }

    public function ttl(string $service): int
    {
        return match ($service) {
            'weather' => Env::int('AGRITWIN_OPENAGRI_WEATHER_TTL_SECONDS', 21600),
            'irrigation' => Env::int('AGRITWIN_OPENAGRI_IRRIGATION_TTL_SECONDS', 86400),
            'pest_disease' => Env::int('AGRITWIN_OPENAGRI_PEST_DISEASE_TTL_SECONDS', 86400),
            'farmcalendar' => Env::int('AGRITWIN_OPENAGRI_FARMCALENDAR_TTL_SECONDS', 900),
            'reporting' => Env::int('AGRITWIN_OPENAGRI_REPORTING_TTL_SECONDS', 0),
            default => 3600,
        };
    }

    public function restoreWindowSeconds(): int
    {
        return Env::int('AGRITWIN_OPENAGRI_RESTORE_WINDOW_SECONDS', 259200);
    }
}
