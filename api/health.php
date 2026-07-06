<?php
declare(strict_types=1);

require_once __DIR__ . '/../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'application' => 'AgriTwin',
    'base_path' => rtrim(agritwin_root_path(), '/') . '/',
    'data_mode' => agritwin_farm_context()['data_mode'],
    'timestamp' => agritwin_demo_date()->format(DateTimeInterface::ATOM),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
