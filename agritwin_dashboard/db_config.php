<?php
// db_config.php
$host = "localhost";       // Replace with your database host
$db_name = "agritwin_db";     // Replace with your database name
$username = "ceres_db_user";     // Replace with your database username
$password = "db_pass";     // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Enable PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
