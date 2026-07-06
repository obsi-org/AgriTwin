-- AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
-- Copyright 2026 Obsidian Innovation Institute - Associação
-- Licensed under the Apache License, Version 2.0. See LICENSE for details.
-- Funding and disclaimer information is provided in NOTICE.

CREATE TABLE IF NOT EXISTS roles (
    role_key VARCHAR(64) PRIMARY KEY,
    role_label VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS openagri_service_config (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT DEFAULT NULL,
    action VARCHAR(120) NOT NULL,
    details TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (role_key, role_label) VALUES
    ('administrator', 'Administrator'),
    ('technical_manager', 'Technical Manager'),
    ('farm_manager', 'Farm Manager'),
    ('advisor_researcher', 'Advisor/Researcher'),
    ('viewer', 'Viewer')
ON DUPLICATE KEY UPDATE role_label = VALUES(role_label);

-- Apply these ALTER statements idempotently through db_init.php or manually
-- by skipping statements that report duplicate columns.
ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN preferred_language VARCHAR(12) NOT NULL DEFAULT 'en';
ALTER TABLE users ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN last_login_at DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN password_change_required TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
