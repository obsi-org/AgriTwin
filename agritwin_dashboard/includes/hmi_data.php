<?php
declare(strict_types=1);

function agritwin_base_path(): string
{
    return '/agritwin3/agritwin_dashboard/';
}

function agritwin_funding_acknowledgement(): string
{
    return "This software prototype is part of a sub-project that has received funding from the European Union's Horizon Europe research and innovation programme under project OpenAgri (grant agreement No 101134083)";
}

function agritwin_supported_languages(): array
{
    return [
        'en' => ['label' => 'English', 'flag' => '🇬🇧'],
        'pt-PT' => ['label' => 'Português', 'flag' => '🇵🇹'],
        'es-ES' => ['label' => 'Español', 'flag' => '🇪🇸'],
    ];
}

function agritwin_current_language(): string
{
    static $currentLanguage = null;
    if ($currentLanguage !== null) {
        return $currentLanguage;
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $requested = $_GET['lang'] ?? null;
    if (is_string($requested) && isset(agritwin_supported_languages()[$requested])) {
        $_SESSION['agritwin_lang'] = $requested;
        if (!headers_sent()) {
            setcookie('agritwin_lang', $requested, ['expires' => time() + 31536000, 'path' => '/agritwin3/', 'samesite' => 'Lax']);
        }
        $currentLanguage = $requested;
        return $currentLanguage;
    }
    $sessionLang = $_SESSION['agritwin_lang'] ?? null;
    if (is_string($sessionLang) && isset(agritwin_supported_languages()[$sessionLang])) {
        $currentLanguage = $sessionLang;
        return $currentLanguage;
    }
    $cookieLang = $_COOKIE['agritwin_lang'] ?? null;
    if (is_string($cookieLang) && isset(agritwin_supported_languages()[$cookieLang])) {
        $currentLanguage = $cookieLang;
        return $currentLanguage;
    }
    $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (str_starts_with(strtolower($accept), 'pt')) {
        $currentLanguage = 'pt-PT';
        return $currentLanguage;
    }
    if (str_starts_with(strtolower($accept), 'es')) {
        $currentLanguage = 'es-ES';
        return $currentLanguage;
    }
    $currentLanguage = 'en';
    return $currentLanguage;
}

function agritwin_translations(): array
{
    static $translations = null;
    if ($translations !== null) {
        return $translations;
    }
    $translations = [];
    foreach (array_keys(agritwin_supported_languages()) as $lang) {
        $path = dirname(__DIR__) . '/lang/' . $lang . '.php';
        $translations[$lang] = is_file($path) ? require $path : [];
    }
    return $translations;
}

function agritwin_t(string $key): string
{
    $lang = agritwin_current_language();
    $translations = agritwin_translations();
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

function agritwin_demo_date(): DateTimeImmutable
{
    return new DateTimeImmutable('2026-05-12 10:30:00', new DateTimeZone('Europe/Madrid'));
}

function agritwin_farm_context(): array
{
    return [
        'site' => agritwin_t('fotovoltaica_macotera_site'),
        'operator' => 'Fotovoltaica Macotera SL',
        'location' => 'Macotera, Salamanca, Castilla y Leon, Spain',
        'lat' => 40.9492,
        'lon' => -5.6958,
        'primary_crop' => agritwin_t('almonds'),
        'secondary_crops' => [agritwin_t('pistachios'), agritwin_t('maize')],
        'timezone' => 'Europe/Madrid',
        'base_path' => '/agritwin3/',
        'data_mode' => agritwin_t('local_fallback'),
        'last_sync' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
    ];
}

function agritwin_hmi_env(string $primary, ?string $fallback = null, ?string $default = null): ?string
{
    $value = getenv($primary);
    if ($value !== false && $value !== '') {
        return $value;
    }
    if ($fallback !== null) {
        $fallbackValue = getenv($fallback);
        if ($fallbackValue !== false && $fallbackValue !== '') {
            return $fallbackValue;
        }
    }
    return $default;
}

function agritwin_load_local_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    $root = dirname(__DIR__, 2);
    foreach ([$root . '/.env', $root . '/config/openagri.local.env'] as $path) {
        if (!is_file($path) || !is_readable($path)) {
            continue;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if ($key !== '' && (getenv($key) === false || str_starts_with($path, $root . '/config/'))) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

function agritwin_sensors(): array
{
    $sensors = [];
    foreach (agritwin_sensor_stations() as $station) {
        foreach ($station['readings'] as $reading) {
            $sensors[] = [
                'id' => $station['id'] . '-' . strtoupper(str_replace('_', '-', $reading['parameter'])),
                'station_id' => $station['id'],
                'name' => $reading['label'],
                'asset' => $station['name'],
                'block' => $station['block'],
                'crop' => $station['crop'],
                'parameter' => $reading['parameter'],
                'value' => $reading['value'],
                'unit' => $reading['unit'],
                'timestamp' => $station['timestamp'],
                'status' => $station['status'],
                'confidence' => $station['confidence'],
                'lat' => $station['lat'],
                'lon' => $station['lon'],
            ];
        }
    }
    return $sensors;
}

function agritwin_sensor_stations(): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $stationDefaults = [
        'site' => 'Fotovoltaica Macotera SL',
        'location' => 'Macotera, Salamanca, Castilla y Leon, Spain',
        'timestamp' => $ts,
        'status' => 'healthy',
        'confidence' => 'normal',
        'source' => agritwin_t('local_operational_dataset'),
    ];
    $stations = [
        [
            'id' => 'MAC-ALM-A',
            'name' => 'Station MAC-ALM-A',
            'block' => agritwin_t('almond_block_a'),
            'crop' => agritwin_t('almonds'),
            'lat' => 40.9492,
            'lon' => -5.6958,
            'readings' => agritwin_station_readings(28.4, 18.7, 21.6, 56, 18, 1.2, 14.0, 690, 34.0, 6.1),
        ],
        [
            'id' => 'MAC-ALM-B',
            'name' => 'Station MAC-ALM-B',
            'block' => agritwin_t('almond_block_b'),
            'crop' => agritwin_t('almonds'),
            'confidence' => 'reduced',
            'lat' => 40.9474,
            'lon' => -5.6931,
            'readings' => agritwin_station_readings(22.1, 19.2, 22.4, 52, 14, 0.8, 16.5, 715, 42.5, 7.8),
        ],
        [
            'id' => 'MAC-ALM-C',
            'name' => 'Station MAC-ALM-C',
            'block' => agritwin_t('almond_block_c'),
            'crop' => agritwin_t('almonds'),
            'lat' => 40.9468,
            'lon' => -5.6967,
            'readings' => agritwin_station_readings(25.6, 18.3, 21.9, 58, 21, 1.0, 13.2, 675, null, null),
        ],
        [
            'id' => 'MAC-PIST-A',
            'name' => 'Station MAC-PIST-A',
            'block' => agritwin_t('pistachio_block_a'),
            'crop' => agritwin_t('pistachios'),
            'lat' => 40.9469,
            'lon' => -5.6924,
            'readings' => agritwin_station_readings(24.8, 19.0, 22.1, 54, 12, 0.6, 15.0, 705, 22.0, 3.6),
        ],
        [
            'id' => 'MAC-MAIZE-A',
            'name' => 'Station MAC-MAIZE-A',
            'block' => agritwin_t('maize_block_a'),
            'crop' => agritwin_t('maize'),
            'lat' => 40.9457,
            'lon' => -5.6918,
            'readings' => agritwin_station_readings(31.2, 20.1, 23.4, 49, 9, 0.4, 18.4, 735, 38.0, 5.4),
        ],
    ];
    return array_map(static fn (array $station): array => array_merge($stationDefaults, $station), $stations);
}

function agritwin_station_readings(float $soilMoisture, float $soilTemp, float $airTemp, int $humidity, int $leafWetness, float $rainfall, float $wind, int $solar, ?float $flow, ?float $energy): array
{
    $readings = [
        ['parameter' => 'soil_moisture', 'label' => agritwin_t('soil_moisture'), 'value' => $soilMoisture, 'unit' => '% VWC'],
        ['parameter' => 'soil_temperature', 'label' => agritwin_t('soil_temperature'), 'value' => $soilTemp, 'unit' => 'degC'],
        ['parameter' => 'air_temperature', 'label' => agritwin_t('air_temperature'), 'value' => $airTemp, 'unit' => 'degC'],
        ['parameter' => 'relative_humidity', 'label' => agritwin_t('relative_humidity'), 'value' => $humidity, 'unit' => '%'],
        ['parameter' => 'leaf_wetness', 'label' => agritwin_t('leaf_wetness'), 'value' => $leafWetness, 'unit' => '%'],
        ['parameter' => 'rainfall', 'label' => agritwin_t('rainfall_today'), 'value' => $rainfall, 'unit' => 'mm'],
        ['parameter' => 'wind_speed', 'label' => agritwin_t('wind_speed'), 'value' => $wind, 'unit' => 'km/h'],
        ['parameter' => 'solar_radiation', 'label' => agritwin_t('solar_radiation'), 'value' => $solar, 'unit' => 'W/m2'],
    ];
    if ($flow !== null) {
        $readings[] = ['parameter' => 'irrigation_flow', 'label' => agritwin_t('irrigation_flow'), 'value' => $flow, 'unit' => 'm3/h'];
    }
    if ($energy !== null) {
        $readings[] = ['parameter' => 'pump_energy', 'label' => agritwin_t('pump_energy'), 'value' => $energy, 'unit' => 'kWh today'];
    }
    return $readings;
}

function agritwin_weather_source(): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $weather = agritwin_service_cards()['weather'] ?? null;
    if ($weather !== null && $weather['mode'] === 'live' && $weather['status'] === 'live_healthy') {
        $openAgriWeather = agritwin_openagri_weather_data($weather);
        if ($openAgriWeather['ok']) {
            return [
                'label' => agritwin_t('openagri_weather_live_label'),
                'source_name' => 'OpenAgri Weather Data Service',
                'mode' => 'live',
                'status' => 'healthy',
                'timestamp' => $openAgriWeather['timestamp'],
                'endpoint' => $openAgriWeather['endpoint'],
                'latency_ms' => $openAgriWeather['latency_ms'],
                'reason' => agritwin_t('weather_live_reason'),
                'temperature_c' => $openAgriWeather['temperature_c'],
                'humidity_pct' => $openAgriWeather['humidity_pct'],
                'wind_kmh' => $openAgriWeather['wind_kmh'],
                'rainfall_mm' => $openAgriWeather['rainfall_mm'],
                'forecast_summary' => agritwin_t('weather_live_summary'),
            ];
        }
    }

    $public = agritwin_public_weather();
    if ($public['ok']) {
        return [
            'label' => agritwin_t('external_weather_open_meteo_label'),
            'source_name' => agritwin_t('open_meteo_public_forecast'),
            'mode' => 'fallback',
            'status' => 'live_fallback',
            'timestamp' => $public['timestamp'],
            'endpoint' => $public['endpoint'],
            'latency_ms' => $public['latency_ms'],
            'reason' => agritwin_t('public_weather_reason'),
            'temperature_c' => $public['temperature_c'],
            'humidity_pct' => $public['humidity_pct'],
            'wind_kmh' => $public['wind_kmh'],
            'rainfall_mm' => $public['rainfall_mm'],
            'forecast_summary' => $public['forecast_summary'],
        ];
    }

    $stationValues = agritwin_weather_from_station();
    return [
        'label' => agritwin_t('weather_source_local_fallback'),
        'source_name' => agritwin_t('local_fallback_label'),
        'mode' => 'local_fallback',
        'status' => 'fallback',
        'timestamp' => $ts,
        'endpoint' => $weather['endpoint'] ?? agritwin_t('not_configured'),
        'latency_ms' => null,
        'reason' => ($weather['error_summary'] ?? agritwin_t('local_weather_reason')),
        'temperature_c' => $stationValues['temperature_c'],
        'humidity_pct' => $stationValues['humidity_pct'],
        'wind_kmh' => $stationValues['wind_kmh'],
        'rainfall_mm' => $stationValues['rainfall_mm'],
        'forecast_summary' => agritwin_t('local_weather_summary'),
    ];
}

function agritwin_secret_storage_dir(): string
{
    return dirname(__DIR__, 2) . '/storage/secrets';
}

function agritwin_secret_key_path(): string
{
    return agritwin_secret_storage_dir() . '/agritwin_secret.key';
}

function agritwin_weather_secret_path(): string
{
    return agritwin_secret_storage_dir() . '/weather_provider.local.json';
}

function agritwin_secret_key(): string
{
    $envKey = (string) (agritwin_hmi_env('AGRITWIN_SECRET_KEY', 'APP_KEY', '') ?: '');
    if ($envKey !== '') {
        return hash('sha256', $envKey, true);
    }
    $dir = agritwin_secret_storage_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $path = agritwin_secret_key_path();
    if (!is_file($path)) {
        @file_put_contents($path, bin2hex(random_bytes(32)), LOCK_EX);
        @chmod($path, 0600);
    }
    $raw = trim((string) @file_get_contents($path));
    return hash('sha256', $raw, true);
}

function agritwin_encrypt_secret(string $plain): array
{
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', agritwin_secret_key(), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) {
        throw new RuntimeException('Secret encryption failed.');
    }
    return [
        'ciphertext' => base64_encode($cipher),
        'iv' => base64_encode($iv),
        'tag' => base64_encode($tag),
        'algorithm' => 'AES-256-GCM',
    ];
}

function agritwin_decrypt_secret(array $record): ?string
{
    $cipher = base64_decode((string) ($record['ciphertext'] ?? ''), true);
    $iv = base64_decode((string) ($record['iv'] ?? ''), true);
    $tag = base64_decode((string) ($record['tag'] ?? ''), true);
    if ($cipher === false || $iv === false || $tag === false) {
        return null;
    }
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', agritwin_secret_key(), OPENSSL_RAW_DATA, $iv, $tag);
    return is_string($plain) ? $plain : null;
}

function agritwin_weather_api_key_record(): array
{
    $path = agritwin_weather_secret_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) @file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function agritwin_weather_api_key_value(): ?string
{
    $envKey = (string) (agritwin_hmi_env('OPENAGRI_WEATHER_TOKEN', 'AGRITWIN_OPENAGRI_WEATHER_TOKEN', '') ?: '');
    if ($envKey !== '') {
        return $envKey;
    }
    $record = agritwin_weather_api_key_record();
    if (($record['configured'] ?? false) !== true || !isset($record['secret'])) {
        return null;
    }
    return agritwin_decrypt_secret((array) $record['secret']);
}

function agritwin_weather_api_key_transport(): array
{
    $record = agritwin_weather_api_key_record();
    $transport = (string) ($record['transport'] ?? agritwin_hmi_env('OPENAGRI_WEATHER_KEY_TRANSPORT', null, 'bearer'));
    $transport = in_array($transport, ['bearer', 'header', 'query'], true) ? $transport : 'bearer';
    return [
        'transport' => $transport,
        'header_name' => (string) ($record['header_name'] ?? agritwin_hmi_env('OPENAGRI_WEATHER_KEY_HEADER', null, 'X-API-Key')),
        'query_parameter_name' => (string) ($record['query_parameter_name'] ?? agritwin_hmi_env('OPENAGRI_WEATHER_KEY_QUERY_PARAM', null, 'api_key')),
    ];
}

function agritwin_weather_api_key_status(): array
{
    $record = agritwin_weather_api_key_record();
    $envConfigured = (string) (agritwin_hmi_env('OPENAGRI_WEATHER_TOKEN', 'AGRITWIN_OPENAGRI_WEATHER_TOKEN', '') ?: '') !== '';
    $localConfigured = ($record['configured'] ?? false) === true;
    $transport = agritwin_weather_api_key_transport();
    return [
        'configured' => $envConfigured || $localConfigured,
        'source' => $envConfigured ? 'environment' : ($localConfigured ? 'settings' : 'not_configured'),
        'masked' => $localConfigured && isset($record['last4']) ? '************' . (string) $record['last4'] : null,
        'provider' => (string) ($record['provider'] ?? 'OpenAgri Weather Data Service'),
        'transport' => $transport['transport'],
        'header_name' => $transport['header_name'],
        'query_parameter_name' => $transport['query_parameter_name'],
        'last_updated_at' => $record['last_updated_at'] ?? null,
        'last_validated_at' => $record['last_validated_at'] ?? null,
        'last_validation_status' => $record['last_validation_status'] ?? 'not_configured',
        'last_validation_message' => $record['last_validation_message'] ?? agritwin_t('credentials_required'),
    ];
}

function agritwin_reject_placeholder_api_key(string $key): bool
{
    $normalized = strtoupper(str_replace(['-', '_', ' '], '', $key));
    return in_array($normalized, ['CHANGEME', 'YOURAPIKEY', 'APIKEYHERE', 'REDACTED', 'PLACEHOLDER'], true);
}

function agritwin_save_weather_api_key(string $key, string $transport, string $headerName, string $queryName): array
{
    $key = trim($key);
    if ($key === '' || strlen($key) < 12 || agritwin_reject_placeholder_api_key($key)) {
        return ['ok' => false, 'message' => agritwin_t('api_key_validation_failed')];
    }
    $transport = in_array($transport, ['bearer', 'header', 'query'], true) ? $transport : 'bearer';
    $dir = agritwin_secret_storage_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $record = agritwin_weather_api_key_record();
    $record = array_merge($record, [
        'configured' => true,
        'provider' => 'OpenAgri Weather Data Service',
        'last4' => substr($key, -4),
        'transport' => $transport,
        'header_name' => trim($headerName) !== '' ? trim($headerName) : 'X-API-Key',
        'query_parameter_name' => trim($queryName) !== '' ? trim($queryName) : 'api_key',
        'last_updated_at' => gmdate(DateTimeInterface::ATOM),
        'last_validation_status' => $record['last_validation_status'] ?? 'unknown',
        'last_validation_message' => $record['last_validation_message'] ?? agritwin_t('not_available'),
        'secret' => agritwin_encrypt_secret($key),
    ]);
    @file_put_contents(agritwin_weather_secret_path(), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @chmod(agritwin_weather_secret_path(), 0600);
    return ['ok' => true, 'message' => agritwin_t('api_key_saved')];
}

function agritwin_remove_weather_api_key(): array
{
    if (is_file(agritwin_weather_secret_path())) {
        @unlink(agritwin_weather_secret_path());
    }
    return ['ok' => true, 'message' => agritwin_t('api_key_removed')];
}

function agritwin_apply_weather_auth(string $url, string $headers, ?string $key): array
{
    if ($key === null || $key === '') {
        return [$url, $headers];
    }
    $transport = agritwin_weather_api_key_transport();
    if ($transport['transport'] === 'query') {
        $param = $transport['query_parameter_name'] !== '' ? $transport['query_parameter_name'] : 'api_key';
        $url .= (str_contains($url, '?') ? '&' : '?') . rawurlencode($param) . '=' . rawurlencode($key);
    } elseif ($transport['transport'] === 'header') {
        $header = $transport['header_name'] !== '' ? $transport['header_name'] : 'X-API-Key';
        $headers .= $header . ': ' . $key . "\r\n";
    } else {
        $headers .= 'Authorization: Bearer ' . $key . "\r\n";
    }
    return [$url, $headers];
}

function agritwin_validate_weather_api_key(): array
{
    $key = agritwin_weather_api_key_value();
    if ($key === null || $key === '') {
        return ['ok' => false, 'status' => 'credentials_required', 'message' => agritwin_t('credentials_required')];
    }
    $service = agritwin_service_cards()['weather'] ?? null;
    if ($service === null || !$service['configured']) {
        return ['ok' => false, 'status' => 'configuration_required', 'message' => agritwin_t('configuration_required')];
    }
    $result = agritwin_openagri_weather_data($service, $key);
    $status = $result['ok'] ? 'live_observations_available' : (($result['error'] ?? '') === 'Credentials required' ? 'authentication_failed' : 'connection_unavailable');
    $message = $result['ok'] ? agritwin_t('live_observations_available') : agritwin_t($status);
    $record = agritwin_weather_api_key_record();
    if (($record['configured'] ?? false) === true) {
        $record['last_validated_at'] = gmdate(DateTimeInterface::ATOM);
        $record['last_validation_status'] = $status;
        $record['last_validation_message'] = $message;
        @file_put_contents(agritwin_weather_secret_path(), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod(agritwin_weather_secret_path(), 0600);
    }
    return ['ok' => $result['ok'], 'status' => $status, 'message' => $message];
}

function agritwin_weather_diagnostics(): array
{
    $services = agritwin_service_cards();
    $weather = $services['weather'] ?? null;
    $keyStatus = agritwin_weather_api_key_status();
    $source = agritwin_weather_source();
    return [
        'service_status' => $weather['status'] ?? 'unknown',
        'service_endpoint' => $weather['endpoint'] ?? agritwin_t('not_configured'),
        'api_key_configured' => $keyStatus['configured'],
        'api_key_source' => $keyStatus['source'],
        'api_key_masked' => $keyStatus['masked'],
        'transport' => $keyStatus['transport'],
        'last_validation_status' => $keyStatus['last_validation_status'],
        'last_validation_message' => $keyStatus['last_validation_message'],
        'live_observations_available' => ($source['source_name'] ?? '') === 'OpenAgri Weather Data Service',
        'reason' => ($source['source_name'] ?? '') === 'OpenAgri Weather Data Service' ? agritwin_t('live_observations_available') : ($keyStatus['configured'] ? agritwin_t('external_provider_configuration_required') : agritwin_t('credentials_required')),
    ];
}

function agritwin_openagri_weather_data(array $weather, ?string $overrideKey = null): array
{
    agritwin_load_local_env();
    $context = agritwin_farm_context();
    $dataPath = (string) agritwin_hmi_env('OPENAGRI_WEATHER_DATA_URL', null, '/api/data/weather/?lat=40.9492&lon=-5.6958');
    if ($dataPath === '') {
        return ['ok' => false, 'error' => 'Configuration required'];
    }
    $url = str_starts_with($dataPath, 'http') ? $dataPath : rtrim((string) $weather['endpoint'], '/') . $dataPath;
    if (!str_contains($url, 'lat=')) {
        $url .= (str_contains($url, '?') ? '&' : '?') . sprintf('lat=%.4F&lon=%.4F', $context['lat'], $context['lon']);
    }
    $token = $overrideKey ?? agritwin_weather_api_key_value();
    $headers = "Accept: application/json\r\n";
    [$url, $headers] = agritwin_apply_weather_auth($url, $headers, $token);
    $started = microtime(true);
    $raw = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true, 'header' => $headers]]));
    $latency = (int) round((microtime(true) - $started) * 1000);
    $status = agritwin_http_status($http_response_header ?? []);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        return ['ok' => false, 'error' => $status === 401 || $status === 403 ? 'Credentials required' : 'Connection unavailable'];
    }
    $temperature = $data['temperature'] ?? $data['temperature_2m'] ?? $data['temp'] ?? $data['main']['temp'] ?? null;
    $humidity = $data['humidity'] ?? $data['relative_humidity'] ?? $data['relative_humidity_2m'] ?? $data['main']['humidity'] ?? null;
    $wind = $data['wind_speed'] ?? $data['wind_speed_10m'] ?? $data['wind']['speed'] ?? null;
    $rain = $data['rainfall'] ?? $data['precipitation'] ?? $data['rain']['1h'] ?? 0;
    if ($temperature === null || $humidity === null || $wind === null) {
        return ['ok' => false, 'error' => 'Invalid response'];
    }
    return [
        'ok' => true,
        'endpoint' => preg_replace('/([?&](appid|api_key|token|key)=)[^&]+/i', '$1redacted', $url),
        'latency_ms' => $latency,
        'timestamp' => (string) ($data['timestamp'] ?? $data['time'] ?? gmdate(DateTimeInterface::ATOM)),
        'temperature_c' => (float) $temperature,
        'humidity_pct' => (int) $humidity,
        'wind_kmh' => (float) $wind,
        'rainfall_mm' => (float) $rain,
    ];
}

function agritwin_public_weather(): array
{
    agritwin_load_local_env();
    $preference = strtolower((string) agritwin_hmi_env('AGRITWIN_WEATHER_SOURCE_PREFERENCE', null, 'auto'));
    if ($preference === 'local_fallback') {
        return ['ok' => false, 'error' => 'public_weather_disabled'];
    }
    $context = agritwin_farm_context();
    $url = sprintf(
        'https://api.open-meteo.com/v1/forecast?latitude=%.4F&longitude=%.4F&current=temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m&daily=weather_code,precipitation_sum&timezone=Europe%%2FMadrid&forecast_days=1',
        $context['lat'],
        $context['lon']
    );
    $started = microtime(true);
    $raw = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]));
    $latency = (int) round((microtime(true) - $started) * 1000);
    $status = agritwin_http_status($http_response_header ?? []);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if ($status < 200 || $status >= 300 || !is_array($data) || !isset($data['current'])) {
        return ['ok' => false, 'error' => $status > 0 ? 'http_' . $status : 'request_failed'];
    }
    $current = $data['current'];
    return [
        'ok' => true,
        'endpoint' => 'https://api.open-meteo.com/v1/forecast',
        'latency_ms' => $latency,
        'timestamp' => isset($current['time']) ? $current['time'] . ':00+02:00' : gmdate(DateTimeInterface::ATOM),
        'temperature_c' => (float) ($current['temperature_2m'] ?? 0),
        'humidity_pct' => (int) ($current['relative_humidity_2m'] ?? 0),
        'wind_kmh' => (float) ($current['wind_speed_10m'] ?? 0),
        'rainfall_mm' => (float) ($current['precipitation'] ?? ($data['daily']['precipitation_sum'][0] ?? 0)),
        'forecast_summary' => agritwin_t('public_weather_summary'),
    ];
}

function agritwin_weather_from_station(): array
{
    $stations = agritwin_sensor_stations();
    $first = $stations[0];
    $values = [];
    foreach ($first['readings'] as $reading) {
        $values[$reading['parameter']] = $reading['value'];
    }
    return [
        'temperature_c' => (float) ($values['air_temperature'] ?? 21.6),
        'humidity_pct' => (int) ($values['relative_humidity'] ?? 56),
        'wind_kmh' => (float) ($values['wind_speed'] ?? 14.0),
        'rainfall_mm' => (float) ($values['rainfall'] ?? 1.2),
    ];
}

function agritwin_mandatory_openagri_keys(): array
{
    return ['gatekeeper', 'weather', 'irrigation', 'pest_disease'];
}

function agritwin_extension_openagri_keys(): array
{
    return ['farmcalendar', 'reporting'];
}

function agritwin_optional_openagri_keys(): array
{
    return ['geodatabase'];
}

function agritwin_openagri_service_groups(array $services): array
{
    return [
        'mandatory' => array_intersect_key($services, array_flip(agritwin_mandatory_openagri_keys())),
        'core' => array_intersect_key($services, array_flip(agritwin_mandatory_openagri_keys())),
        'extensions' => array_intersect_key($services, array_flip(agritwin_extension_openagri_keys())),
        'optional' => array_intersect_key($services, array_flip(agritwin_optional_openagri_keys())),
    ];
}

function agritwin_openagri_service_role(string $key): string
{
    return match ($key) {
        'gatekeeper' => agritwin_t('role_gatekeeper'),
        'weather' => agritwin_t('role_weather'),
        'irrigation' => agritwin_t('role_irrigation'),
        'pest_disease' => agritwin_t('role_pest_disease'),
        'farmcalendar' => agritwin_t('role_farmcalendar'),
        'reporting' => agritwin_t('role_reporting'),
        'geodatabase' => agritwin_t('role_geodatabase'),
        default => agritwin_t('role_unknown'),
    };
}

function agritwin_openagri_service_relevance(string $key): string
{
    if (in_array($key, agritwin_mandatory_openagri_keys(), true)) {
        return agritwin_t('mandatory_relevance');
    }
    if (in_array($key, agritwin_extension_openagri_keys(), true)) {
        return agritwin_t('extension_relevance');
    }
    return agritwin_t('optional_relevance');
}

function agritwin_service_cards(): array
{
    agritwin_load_local_env();
    $services = [
        'gatekeeper' => ['name' => 'OpenAgri Gatekeeper / Access Control', 'env' => 'OPENAGRI_GATEKEEPER_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_GATEKEEPER_URL', 'path_env' => 'OPENAGRI_GATEKEEPER_HEALTH_URL', 'docs_env' => 'OPENAGRI_GATEKEEPER_DOCS_URL', 'path' => '/health', 'token_env' => 'OPENAGRI_GATEKEEPER_CLIENT_SECRET', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/OpenAgri-GateKeeper'],
        'weather' => ['name' => 'OpenAgri Weather Data Service', 'env' => 'OPENAGRI_WEATHER_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_WEATHER_URL', 'path_env' => 'OPENAGRI_WEATHER_HEALTH_URL', 'docs_env' => 'OPENAGRI_WEATHER_DOCS_URL', 'path' => '/api/data/forecast5?lat=40.9492&lon=-5.6958', 'token_env' => 'OPENAGRI_WEATHER_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_WEATHER_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/OpenAgri-WeatherService'],
        'irrigation' => ['name' => 'OpenAgri Irrigation Management Service', 'env' => 'OPENAGRI_IRRIGATION_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_IRRIGATION_URL', 'path_env' => 'OPENAGRI_IRRIGATION_HEALTH_URL', 'docs_env' => 'OPENAGRI_IRRIGATION_DOCS_URL', 'path' => '/api/irrigation/advisory', 'token_env' => 'OPENAGRI_IRRIGATION_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_IRRIGATION_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/OpenAgri-IrrigationManagement'],
        'pest_disease' => ['name' => 'OpenAgri Pest & Disease Management Service', 'env' => 'OPENAGRI_PEST_DISEASE_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_URL', 'path_env' => 'OPENAGRI_PEST_DISEASE_HEALTH_URL', 'docs_env' => 'OPENAGRI_PEST_DISEASE_DOCS_URL', 'path' => '/api/pest/risk', 'token_env' => 'OPENAGRI_PEST_DISEASE_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_PEST_DISEASE_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/OpenAgri-PestAndDiseaseManagement'],
        'farmcalendar' => ['name' => 'OpenAgri FarmCalendar', 'env' => 'OPENAGRI_FARMCALENDAR_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_URL', 'path_env' => 'OPENAGRI_FARMCALENDAR_HEALTH_URL', 'docs_env' => 'OPENAGRI_FARMCALENDAR_DOCS_URL', 'path' => '/api/calendar/operations', 'token_env' => 'OPENAGRI_FARMCALENDAR_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_FARMCALENDAR_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/farmcalendar'],
        'reporting' => ['name' => 'OpenAgri Reporting Service', 'env' => 'OPENAGRI_REPORTING_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_REPORTING_URL', 'path_env' => 'OPENAGRI_REPORTING_HEALTH_URL', 'docs_env' => 'OPENAGRI_REPORTING_DOCS_URL', 'path' => '/api/reports', 'token_env' => 'OPENAGRI_REPORTING_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_REPORTING_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/OpenAgri-ReportingService'],
        'geodatabase' => ['name' => 'OpenAgri Geodatabase / OCSM', 'env' => 'OPENAGRI_GEODATABASE_URL', 'legacy_env' => 'AGRITWIN_OPENAGRI_GEODATABASE_URL', 'path_env' => 'OPENAGRI_GEODATABASE_HEALTH_URL', 'docs_env' => 'OPENAGRI_GEODATABASE_DOCS_URL', 'path' => '/health', 'token_env' => 'OPENAGRI_GEODATABASE_TOKEN', 'legacy_token_env' => 'AGRITWIN_OPENAGRI_GEODATABASE_TOKEN', 'source_repository' => 'https://github.com/openagri-eu/'],
    ];
    $cards = [];
    foreach ($services as $key => $service) {
        $cards[$key] = agritwin_check_openagri_service($key, $service);
    }
    return $cards;
}

function agritwin_check_openagri_service(string $key, array $service): array
{
    $ts = agritwin_demo_date()->format(DateTimeInterface::ATOM);
    $rawMode = strtolower((string) agritwin_hmi_env('OPENAGRI_MODE', 'AGRITWIN_OPENAGRI_MODE', 'fallback'));
    $mode = in_array($rawMode, ['demo', 'mock'], true) ? 'fallback' : $rawMode;
    $endpoint = rtrim((string) agritwin_hmi_env($service['env'], $service['legacy_env'] ?? null, ''), '/');
    $healthPath = (string) agritwin_hmi_env($service['path_env'] ?? '', null, $service['path']);
    $docsUrl = (string) agritwin_hmi_env($service['docs_env'] ?? '', null, '');
    $token = (string) (agritwin_hmi_env($service['token_env'], $service['legacy_token_env'] ?? null, '') ?: agritwin_hmi_env('AGRITWIN_OPENAGRI_GATEKEEPER_ACCESS_TOKEN', null, ''));
    $isLocalFallback = $endpoint !== '' && str_contains($endpoint, 'openagri-mock');

    $base = [
        'key' => $key,
        'name' => $service['name'],
        'endpoint' => $endpoint !== '' ? $endpoint : agritwin_t('not_configured'),
        'health_path' => $healthPath,
        'docs_url' => $docsUrl,
        'source_repository' => $service['source_repository'] ?? '',
        'auth_type' => $token !== '' ? agritwin_t('auth_secret_environment') : agritwin_t('configuration_required'),
        'configured' => $endpoint !== '',
        'core_service' => in_array($key, agritwin_mandatory_openagri_keys(), true),
        'integration_priority' => in_array($key, agritwin_mandatory_openagri_keys(), true) ? 'core' : (in_array($key, agritwin_extension_openagri_keys(), true) ? 'additional' : 'optional'),
        'service_group' => in_array($key, agritwin_mandatory_openagri_keys(), true) ? 'mandatory' : (in_array($key, agritwin_extension_openagri_keys(), true) ? 'extensions' : 'optional'),
        'role' => agritwin_openagri_service_role($key),
        'integration_relevance' => agritwin_openagri_service_relevance($key),
        'mode' => 'not_configured',
        'status' => $key === 'geodatabase' ? 'optional_not_configured' : 'not_configured',
        'http_status' => null,
        'latency_ms' => null,
        'last_successful_live_check' => null,
        'last_fallback_generation' => null,
        'last_check' => $ts,
        'error_summary' => agritwin_t('endpoint_not_configured'),
        'diagnostics' => agritwin_t('endpoint_not_configured'),
    ];

    if ($endpoint === '') {
        return $base;
    }

    if ($mode === 'fallback') {
        return array_merge($base, [
            'mode' => 'fallback',
            'status' => 'local_fallback_active',
            'latency_ms' => match ($key) {
                'gatekeeper' => 32,
                'weather' => 44,
                'irrigation' => 58,
                'pest_disease' => 63,
                'farmcalendar' => 39,
                'geodatabase' => 48,
                default => 71,
            },
            'last_fallback_generation' => $ts,
            'error_summary' => agritwin_t('awaiting_live_configuration_error'),
            'diagnostics' => agritwin_t('awaiting_live_configuration_error'),
        ]);
    }

    if ($isLocalFallback) {
        return array_merge($base, [
            'mode' => 'fallback',
            'status' => 'local_fallback_active',
            'latency_ms' => 45,
            'last_fallback_generation' => $ts,
            'error_summary' => agritwin_t('local_endpoint_fallback_error'),
            'diagnostics' => agritwin_t('local_endpoint_fallback_error'),
        ]);
    }

    $url = str_starts_with($healthPath, 'http') ? $healthPath : $endpoint . $healthPath;
    $started = microtime(true);
    $headers = "Accept: application/json\r\n";
    if ($token !== '') {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 4,
            'ignore_errors' => true,
            'header' => $headers,
        ],
    ]);
    $raw = @file_get_contents($url, false, $context);
    $latency = (int) round((microtime(true) - $started) * 1000);
    $status = agritwin_http_status($http_response_header ?? []);

    if ($status === 401 || $status === 403) {
        return array_merge($base, [
            'mode' => 'live',
            'status' => 'unauthenticated',
            'http_status' => $status,
            'latency_ms' => $latency,
            'error_summary' => agritwin_t('endpoint_auth_error'),
            'diagnostics' => agritwin_t('endpoint_auth_error'),
        ]);
    }

    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if ($status >= 200 && $status < 300 && (is_array($decoded) || $raw !== false)) {
        return array_merge($base, [
            'mode' => 'live',
            'status' => 'live_healthy',
            'http_status' => $status,
            'latency_ms' => $latency,
            'last_successful_live_check' => gmdate(DateTimeInterface::ATOM),
            'error_summary' => agritwin_t('endpoint_success'),
            'diagnostics' => agritwin_t('endpoint_success'),
        ]);
    }

    return array_merge($base, [
        'mode' => $mode === 'auto' ? 'fallback' : 'live',
        'status' => 'unavailable',
        'http_status' => $status ?: null,
        'latency_ms' => $latency,
        'last_fallback_generation' => $ts,
        'error_summary' => $status > 0 ? 'HTTP ' . $status . '; ' . agritwin_t('local_fallback') : agritwin_t('endpoint_unreachable_error'),
        'diagnostics' => $status > 0 ? 'HTTP ' . $status : agritwin_t('endpoint_unreachable_error'),
    ]);
}

function agritwin_http_status(array $headers): int
{
    foreach ($headers as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', (string) $header, $matches)) {
            return (int) $matches[1];
        }
    }
    return 0;
}

function agritwin_operations(): array
{
    return [
        ['time' => '2026-05-12T07:30:00+02:00', 'operation' => agritwin_t('op_pump_inspection'), 'block' => agritwin_t('pump_house'), 'status' => 'completed'],
        ['time' => '2026-05-12T09:00:00+02:00', 'operation' => agritwin_t('op_sensor_check'), 'block' => agritwin_t('almond_block_a'), 'status' => 'completed'],
        ['time' => '2026-05-12T12:30:00+02:00', 'operation' => agritwin_t('op_irrigation_review'), 'block' => agritwin_t('almond_block_b'), 'status' => 'planned'],
        ['time' => '2026-05-12T17:00:00+02:00', 'operation' => agritwin_t('op_pest_scouting'), 'block' => agritwin_t('almond_buffer_rows'), 'status' => 'planned'],
    ];
}

function agritwin_recommendations(): array
{
    return [
        'water_stress' => ['label' => agritwin_t('water_stress_label'), 'value' => '22.1% VWC', 'confidence' => 'reduced'],
        'irrigation' => ['label' => agritwin_t('irrigation_label'), 'confidence' => 'normal'],
        'pest' => ['label' => agritwin_t('pest_label'), 'risk' => '34/100', 'confidence' => 'normal'],
        'nitrogen' => ['label' => agritwin_t('nitrogen_label'), 'confidence' => 'normal'],
    ];
}

function agritwin_kpis(): array
{
    return [
        ['metric' => agritwin_t('kpi_irrigation_latency'), 'value' => '38 s', 'status' => agritwin_t('kpi_within_target')],
        ['metric' => agritwin_t('kpi_water_stress_mae'), 'value' => '0.002 m3/m3', 'status' => agritwin_t('kpi_sample_dataset')],
        ['metric' => agritwin_t('kpi_data_continuity'), 'value' => '100%', 'status' => agritwin_t('kpi_packets_on_time')],
        ['metric' => agritwin_t('kpi_network_use'), 'value' => '23.8 MB/h', 'status' => agritwin_t('kpi_mixed_sample')],
        ['metric' => agritwin_t('kpi_energy'), 'value' => '0.009 kWh', 'status' => agritwin_t('kpi_synthetic_estimate')],
    ];
}

function agritwin_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function agritwin_mode_label(string $mode): string
{
    return match ($mode) {
        'live' => agritwin_t('live_service'),
        'demo' => agritwin_t('local_fallback'),
        'mock' => agritwin_t('local_fallback'),
        'fallback' => agritwin_t('fallback_data'),
        'not_configured' => agritwin_t('not_configured'),
        'local_fallback' => agritwin_t('local_fallback'),
        'auto' => agritwin_t('auto'),
        default => ucfirst(str_replace('_', ' ', $mode)),
    };
}

function agritwin_role_label(string $role): string
{
    return match ($role) {
        'administrator' => agritwin_t('role_administrator'),
        'technical_manager' => agritwin_t('role_technical_manager'),
        'farm_manager' => agritwin_t('role_farm_manager'),
        'advisor_researcher' => agritwin_t('role_advisor_researcher'),
        'viewer' => agritwin_t('role_viewer'),
        default => ucfirst(str_replace('_', ' ', $role)),
    };
}

function agritwin_js_translations(): array
{
    return [
        'online' => agritwin_t('online'),
        'offline' => agritwin_t('offline'),
        'saved_offline' => agritwin_t('saved_offline'),
        'queue' => agritwin_t('queue'),
        'advisory_report_title' => agritwin_t('advisory_report_title'),
        'cache_restore_warning' => agritwin_t('cache_restore_warning'),
        'service_worker_warning' => agritwin_t('service_worker_warning'),
    ];
}

function agritwin_status_label(string $status): string
{
    return agritwin_t($status) !== $status ? agritwin_t($status) : ucfirst(str_replace('_', ' ', $status));
}

function agritwin_current_user(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) ($_SESSION['username'] ?? ''),
        'role' => (string) ($_SESSION['role'] ?? 'viewer'),
        'preferred_language' => agritwin_current_language(),
    ];
}

function agritwin_role_allows(string $permission): bool
{
    $role = strtolower((string) (agritwin_current_user()['role'] ?? 'viewer'));
    $matrix = [
        'administrator' => ['users', 'settings', 'api', 'reports', 'operations', 'dashboard'],
        'technical_manager' => ['settings', 'api', 'reports', 'operations', 'dashboard'],
        'farm_manager' => ['reports', 'operations', 'dashboard'],
        'advisor_researcher' => ['api', 'reports', 'dashboard'],
        'viewer' => ['dashboard'],
    ];
    return in_array($permission, $matrix[$role] ?? $matrix['viewer'], true);
}

function agritwin_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function agritwin_require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        exit('Invalid request token');
    }
}
