<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/reporting.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['types' => agritwin_report_types()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
