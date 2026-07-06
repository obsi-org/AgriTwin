<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/agritwin_dashboard/includes/hmi_data.php';
require_once dirname(__DIR__, 2) . '/agritwin_dashboard/db_config.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Database connection unavailable.\n");
    exit(1);
}

foreach ([
    "ALTER TABLE users ADD COLUMN full_name VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN organisation VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 1",
    "ALTER TABLE users ADD UNIQUE KEY users_username_unique (username)",
] as $sql) {
    try {
        $pdo->exec($sql);
    } catch (Throwable) {
    }
}

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

function agritwin_generate_password(): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < 24; $i++) {
        $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $password;
}

$accounts = [
    [
        'username' => 'pedro.branco',
        'full_name' => 'Pedro Branco',
        'email' => 'pedro.branco@obsidiani2.org',
        'organisation' => 'Obsidian Innovation Institute - Associação',
        'role' => 'administrator',
        'preferred_language' => 'pt-PT',
    ],
    [
        'username' => 'gregorio.sanchez',
        'full_name' => 'Gregorio Sanchez',
        'email' => 'gregorio.sanchez@fotovoltaicamacotera.com',
        'organisation' => 'Fotovoltaica Macotera SL',
        'role' => 'farm_manager',
        'preferred_language' => 'es-ES',
    ],
];

$stmt = $pdo->prepare("INSERT INTO users
    (username, password, role, preferred_language, enabled, password_change_required, must_change_password, full_name, email, organisation)
    VALUES
    (:username, :password, :role, :preferred_language, 1, 1, 1, :full_name, :email, :organisation)
    ON DUPLICATE KEY UPDATE
        password = VALUES(password),
        role = VALUES(role),
        preferred_language = VALUES(preferred_language),
        enabled = 1,
        password_change_required = 1,
        must_change_password = 1,
        full_name = VALUES(full_name),
        email = VALUES(email),
        organisation = VALUES(organisation)");

$created = [];
foreach ($accounts as $account) {
    $password = agritwin_generate_password();
    $stmt->execute([
        'username' => $account['username'],
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $account['role'],
        'preferred_language' => $account['preferred_language'],
        'full_name' => $account['full_name'],
        'email' => $account['email'],
        'organisation' => $account['organisation'],
    ]);
    $created[] = $account + ['password' => $password];
}

foreach ($created as $account) {
    echo $account['full_name'] . "\n";
    echo "Username: " . $account['username'] . "\n";
    echo "Email: " . $account['email'] . "\n";
    echo "Temporary password: " . $account['password'] . "\n";
    echo "Password change required: yes\n\n";
}
