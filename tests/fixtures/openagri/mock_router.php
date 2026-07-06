<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$map = [
    '/api/data/forecast5' => 'weather_forecast.json',
    '/api/data/weather' => 'weather_forecast.json',
    '/api/v1/eto/get-calculations/1/from/2026-05-12/to/2026-05-13/' => 'irrigation_eto.json',
    '/api/v1/fuzzy-risk/forecast/' => 'pest_risk.json',
    '/api/v1/tool/calculate-gdd/parcel/parcel-demo/model/model-demo/verbose/2026-05-12/from/2026-05-13/to/' => 'pest_risk.json',
    '/api/v1/IrrigationOperations/' => 'farmcalendar_operations.json',
    '/api/v1/FarmParcels/' => 'farmcalendar_operations.json',
    '/api/v1/AgriculturalMachines/' => 'farmcalendar_operations.json',
    '/api/v1/openagri-report/irrigation-report/' => 'reporting_response.json',
    '/api/login/' => null,
    '/api/service_directory/' => null,
    '/timeout' => null,
    '/invalid' => 'invalid.json',
];

if ($path === '/timeout') {
    sleep(3);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'slow']);
    return;
}

if ($path === '/api/login/') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'access' => 'mock-access-token', 'refresh' => 'mock-refresh-token']);
    return;
}

if ($path === '/api/service_directory/') {
    header('Content-Type: application/json');
    echo json_encode(['services' => ['weather', 'irrigation', 'pest_disease', 'farmcalendar', 'reporting']]);
    return;
}

$file = $map[$path] ?? null;
if ($file === null) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'not found', 'path' => $path]);
    return;
}

header('Content-Type: application/json');
echo file_get_contents(__DIR__ . '/' . $file);

