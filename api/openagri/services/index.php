<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../agritwin_dashboard/includes/hmi_data.php';

header('Content-Type: application/json');
$services = agritwin_service_cards();
echo json_encode([
    'services' => array_values($services),
    'groups' => array_map(static fn (array $group): array => array_values($group), agritwin_openagri_service_groups($services)),
    'weather_source' => agritwin_weather_source(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
