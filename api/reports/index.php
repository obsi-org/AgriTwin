<?php
declare(strict_types=1);

require_once __DIR__ . '/../../agritwin_dashboard/includes/reporting.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'unauthenticated']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['reports' => agritwin_reports_list()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
