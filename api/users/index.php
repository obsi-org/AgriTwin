<?php
declare(strict_types=1);

require_once __DIR__ . '/../../agritwin_dashboard/includes/hmi_data.php';
require_once __DIR__ . '/../../agritwin_dashboard/db_config.php';

header('Content-Type: application/json');

if (!agritwin_role_allows('users')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'administrator_role_required']);
    exit;
}

$rows = $pdo->query("SELECT id, username, role, preferred_language, enabled, last_login_at FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['users' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
