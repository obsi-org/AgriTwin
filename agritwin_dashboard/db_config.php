<?php
// db_config.php
agritwin_load_env(dirname(__DIR__) . '/.env');
agritwin_load_env(dirname(__DIR__) . '/config/database.local.env', true);

$host = agritwin_env(['AGRITWIN_DB_HOST', 'DB_HOST'], "localhost");
$port = agritwin_env(['AGRITWIN_DB_PORT', 'DB_PORT'], "3306");
$db_name = agritwin_env(['AGRITWIN_DB_NAME', 'AGRITWIN_DB_DATABASE', 'DB_DATABASE', 'DB_NAME'], "agritwin_db");
$username = agritwin_env(['AGRITWIN_DB_USER', 'AGRITWIN_DB_USERNAME', 'DB_USERNAME', 'DB_USER'], "ceres_db_user");
$password = agritwin_env(['AGRITWIN_DB_PASSWORD', 'AGRITWIN_DB_PASS', 'DB_PASSWORD', 'DB_PASS'], "agritwin_local_dev_password");
$agritwin_db_diagnostics = [
    'host' => $host,
    'port' => $port,
    'database' => $db_name,
    'user' => $username,
    'state' => 'unknown',
    'reason' => 'unknown',
    'last_successful_connection' => null,
];
$agritwin_db_last_success_file = dirname(__DIR__) . '/var/runtime/db_last_success.txt';
if (is_file($agritwin_db_last_success_file)) {
    $lastSuccess = trim((string) file_get_contents($agritwin_db_last_success_file));
    $agritwin_db_diagnostics['last_successful_connection'] = $lastSuccess !== '' ? $lastSuccess : null;
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Enable PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $now = gmdate(DateTimeInterface::ATOM);
    $agritwin_db_diagnostics['state'] = 'connected';
    $agritwin_db_diagnostics['reason'] = 'connected';
    $agritwin_db_diagnostics['last_successful_connection'] = $now;
    if (!is_dir(dirname($agritwin_db_last_success_file))) {
        @mkdir(dirname($agritwin_db_last_success_file), 0775, true);
    }
    @file_put_contents($agritwin_db_last_success_file, $now);
} catch (PDOException $e) {
    $message = $e->getMessage();
    $reason = 'unknown';
    if (stripos($message, 'Access denied') !== false) {
        $reason = 'authentication_failed';
    } elseif (stripos($message, 'Unknown database') !== false) {
        $reason = 'database_missing';
    } elseif (stripos($message, 'Connection refused') !== false || stripos($message, 'No such file') !== false) {
        $reason = 'connection_refused';
    } elseif (stripos($message, 'php_network_getaddresses') !== false || stripos($message, 'Name or service not known') !== false) {
        $reason = 'host_unreachable';
    }
    $agritwin_db_diagnostics['state'] = 'unavailable';
    $agritwin_db_diagnostics['reason'] = $reason;
    error_log(sprintf(
        'AgriTwin DB connection failed host=%s port=%s database=%s user=%s password_configured=%s error=%s',
        $host,
        $port,
        $db_name,
        $username,
        $password !== '' ? 'yes' : 'no',
        $e->getMessage()
    ));
    $pdo = null;
    $agritwin_db_error = sprintf(
        'Database unavailable for host=%s port=%s database=%s user=%s password_configured=%s reason=%s',
        $host,
        $port,
        $db_name,
        $username,
        $password !== '' ? 'yes' : 'no',
        $reason
    );
}

function agritwin_env(array $names, string $default): string
{
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
            return (string) $_ENV[$name];
        }
        if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') {
            return (string) $_SERVER[$name];
        }
    }
    return $default;
}

function agritwin_load_env(string $path, bool $override = false): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        if ($name === '' || (!$override && getenv($name) !== false)) {
            continue;
        }
        $value = trim($value, "\"'");
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
?>
