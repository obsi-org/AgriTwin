<?php
/**
 * db_init.php – Minimal schema/seed loader for CERES (app_settings, farms, users)
 * Called automatically from index.php; safe to leave in production.
 */

if (!isset($pdo) || !($pdo instanceof PDO)) {
    return;
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
        role      VARCHAR(100) NOT NULL DEFAULT 'viewer',
        location  VARCHAR(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    foreach ([
        "ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN preferred_language VARCHAR(12) NOT NULL DEFAULT 'en'",
        "ALTER TABLE users ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1",
        "ALTER TABLE users ADD COLUMN last_login_at DATETIME DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN password_change_required TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE users ADD COLUMN full_name VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN organisation VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP"
    ] as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Throwable) {
            // Column already exists or engine does not support the exact clause.
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        role_key VARCHAR(64) PRIMARY KEY,
        role_label VARCHAR(120) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS openagri_service_config (
        service_key VARCHAR(64) PRIMARY KEY,
        enabled TINYINT(1) NOT NULL DEFAULT 0,
        base_url VARCHAR(500) DEFAULT NULL,
        health_path VARCHAR(255) DEFAULT '/health',
        docs_url VARCHAR(500) DEFAULT NULL,
        auth_type VARCHAR(40) NOT NULL DEFAULT 'none',
        client_id VARCHAR(255) DEFAULT NULL,
        token_url VARCHAR(500) DEFAULT NULL,
        scope VARCHAR(255) DEFAULT NULL,
        timeout_seconds INT NOT NULL DEFAULT 10,
        retry_count INT NOT NULL DEFAULT 2,
        last_successful_connection DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        actor_user_id INT DEFAULT NULL,
        action VARCHAR(120) NOT NULL,
        details TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    /* === seed data === */
    $cesiumToken = getenv('AGRITWIN_CESIUM_TOKEN') ?: 'replace-with-local-cesium-token';
    $copernicusId = getenv('AGRITWIN_COPERNICUS_ID') ?: 'replace-with-local-copernicus-project-id';
    $pdo->exec("INSERT INTO app_settings (id,name,value) VALUES
        (1,'cesium_token',{$pdo->quote($cesiumToken)}),
        (2,'copernicus_id',{$pdo->quote($copernicusId)})
    ON DUPLICATE KEY UPDATE id=id");

    $pdo->exec("INSERT INTO farms (id,farm_name,farm_code,location,country) VALUES
        (1,'Fotovoltaica Macotera SL','MACOTERA','Macotera, Salamanca','Spain')
    ON DUPLICATE KEY UPDATE id=id");

    $roles = [
        'administrator' => 'Administrator',
        'technical_manager' => 'Technical Manager',
        'farm_manager' => 'Farm Manager',
        'advisor_researcher' => 'Advisor/Researcher',
        'viewer' => 'Viewer',
    ];
    $roleStmt = $pdo->prepare("INSERT INTO roles (role_key, role_label) VALUES (:role_key, :role_label)
        ON DUPLICATE KEY UPDATE role_label = VALUES(role_label)");
    foreach ($roles as $key => $label) {
        $roleStmt->execute(['role_key' => $key, 'role_label' => $label]);
    }

    // Local-only bootstrap password hash; rotate in any deployment.
    $adminHash = getenv('AGRITWIN_ADMIN_PASSWORD_HASH') ?: password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $technicianHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    $pdo->exec("INSERT INTO users (id,username,password,role,location,preferred_language,enabled,password_change_required) VALUES
        (1,'admin',{$pdo->quote($adminHash)},'administrator','Spain','en',1,1),
        (2,'technical.manager',{$pdo->quote($technicianHash)},'technical_manager','Spain','en',1,1)
    ON DUPLICATE KEY UPDATE id=id");

    $pdo->commit();
}

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        ensureSchemaAndSeed_min($pdo);
    }
} catch (Throwable $e) {
    // rollback is automatic on exception if using InnoDB
    file_put_contents('php://stderr', 'DB init error: ' . $e->getMessage());
    // still allow the app to proceed; login page will show error if needed
}
?>
