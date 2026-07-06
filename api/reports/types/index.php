<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/reporting.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['types' => agritwin_report_types()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
