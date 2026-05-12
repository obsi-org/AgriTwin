<?php
// db_config.php
agritwin_load_env(dirname(__DIR__) . '/.env');

$host = agritwin_env(['AGRITWIN_DB_HOST', 'DB_HOST'], "localhost");
$port = agritwin_env(['AGRITWIN_DB_PORT', 'DB_PORT'], "3306");
$db_name = agritwin_env(['AGRITWIN_DB_NAME', 'AGRITWIN_DB_DATABASE', 'DB_DATABASE', 'DB_NAME'], "agritwin_db");
$username = agritwin_env(['AGRITWIN_DB_USER', 'AGRITWIN_DB_USERNAME', 'DB_USERNAME', 'DB_USER'], "ceres_db_user");
$password = agritwin_env(['AGRITWIN_DB_PASSWORD', 'AGRITWIN_DB_PASS', 'DB_PASSWORD', 'DB_PASS'], "agritwin_local_dev_password");

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Enable PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log(sprintf(
        'AgriTwin DB connection failed host=%s port=%s database=%s user=%s password_configured=%s error=%s',
        $host,
        $port,
        $db_name,
        $username,
        $password !== '' ? 'yes' : 'no',
        $e->getMessage()
    ));
    echo "Connection failed: " . $e->getMessage();
    exit();
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

function agritwin_load_env(string $path): void
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
        if ($name === '' || getenv($name) !== false) {
            continue;
        }
        $value = trim($value, "\"'");
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
?>
