<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/reporting.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'unauthenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'method_not_allowed']);
    exit();
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$type = is_array($payload) ? (string) ($payload['type'] ?? 'field-operational') : 'field-operational';
$lang = is_array($payload) ? (string) ($payload['lang'] ?? agritwin_current_language()) : agritwin_current_language();
$report = agritwin_report_generate($type, $lang);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'id' => $report['id'],
    'type' => $report['type'],
    'title' => $report['title'],
    'generated_at' => $report['generated_at'],
    'download_url' => '/agritwin3/api/reports/download?id=' . rawurlencode($report['id']),
    'content_type' => 'application/pdf',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
