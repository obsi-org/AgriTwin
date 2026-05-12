<?php
/**
 * db_init.php – Minimal schema/seed loader for CERES (app_settings, farms, users)
 * Called automatically from index.php; safe to leave in production.
 */

if (!isset($pdo) || !($pdo instanceof PDO)) {
    // If $pdo not present, try to include config.
    require_once __DIR__ . '/ceres_dashboard/db_config.php';
}

/**
 * Build schema & seed if necessary (idempotent).
 */
function ensureSchemaAndSeed_min(PDO $pdo): void
{
    $pdo->beginTransaction();

    /* === tables === */
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
        id    INT AUTO_INCREMENT PRIMARY KEY,
        name  VARCHAR(64) NOT NULL UNIQUE,
        value TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS farms (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        farm_name VARCHAR(255) NOT NULL,
        farm_code VARCHAR(50)  NOT NULL UNIQUE,
        location  VARCHAR(255) NOT NULL,
        country   VARCHAR(50)  NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        username  VARCHAR(100) NOT NULL UNIQUE,
        password  VARCHAR(255) NOT NULL,
        role      VARCHAR(100) NOT NULL DEFAULT 'User',
        location  VARCHAR(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    /* === seed data === */
    $pdo->exec("INSERT INTO app_settings (id,name,value) VALUES
        (1,'cesium_token','eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI5MjFiYWY1Ni0zMTQ0LTQ3NTgtODdiOS01MzdhYmVhMzAyOGQiLCJpZCI6MjU3OTMxLCJpYXQiOjE3MzI1NjMzNzl9.AEKDkClJUhfzlk9EDsFhb75ipK1Q7h1x3ttvCb0emxk'),
        (2,'copernicus_id','682d23b8-7012-458e-b85a-f33e13c5e6c8')
    ON DUPLICATE KEY UPDATE id=id");

    $pdo->exec("INSERT INTO farms (id,farm_name,farm_code,location,country) VALUES
        (1,'Fotovoltaica Macotera SL','FOTOMACO1','Salamanca Region','Spain'),
    ON DUPLICATE KEY UPDATE id=id");

    // Password hash for 'admin' as in original dump
    $adminHash = '$2y$10$l5oI7EZQXhX6Ibtxabbdi.Q3gPEvhC.yCs.b/9G.fOfidI/qDPVgq';

    $pdo->exec("INSERT INTO users (id,username,password,role,location) VALUES
        (1,'admin',{$pdo->quote($adminHash)},'System Administrator','Spain'),
        (2,'Maria Lopes','$2y$10$EncryptedPw','Technician','Greece')
    ON DUPLICATE KEY UPDATE id=id");

    $pdo->commit();
}

try {
    ensureSchemaAndSeed_min($pdo);
} catch (Throwable $e) {
    // rollback is automatic on exception if using InnoDB
    file_put_contents('php://stderr', 'DB init error: ' . $e->getMessage());
    // still allow the app to proceed; login page will show error if needed
}
?>
