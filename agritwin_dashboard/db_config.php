<?php
// db_config.php
$host = getenv('AGRITWIN_DB_HOST') ?: "localhost";
$db_name = getenv('AGRITWIN_DB_NAME') ?: "agritwin_db";
$username = getenv('AGRITWIN_DB_USER') ?: "ceres_db_user";
$password = getenv('AGRITWIN_DB_PASSWORD') ?: "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Enable PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
