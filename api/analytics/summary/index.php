<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../agritwin_dashboard/includes/statistics_engine.php';
header('Content-Type: application/json');
echo json_encode(agritwin_analytics_summary(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
