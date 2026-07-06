<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require __DIR__ . '/../agritwin_dashboard/db_config.php';

function copernicus_setting(?PDO $pdo, array $names): string
{
    if (!$pdo instanceof PDO || $names === []) {
        return '';
    }

    $placeholders = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT name, value FROM app_settings WHERE name IN ($placeholders)");
    $stmt->execute($names);
    $values = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        $value = trim((string) ($values[$name] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function copernicus_env(array $names): string
{
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && trim((string) $value) !== '') {
            return trim((string) $value);
        }
        if (isset($_ENV[$name]) && trim((string) $_ENV[$name]) !== '') {
            return trim((string) $_ENV[$name]);
        }
        if (isset($_SERVER[$name]) && trim((string) $_SERVER[$name]) !== '') {
            return trim((string) $_SERVER[$name]);
        }
    }

    return '';
}

function append_query(string $url, array $params): string
{
    $separator = str_contains($url, '?') ? (str_ends_with($url, '?') || str_ends_with($url, '&') ? '' : '&') : '?';
    return $url . $separator . http_build_query($params);
}

$wmsUrl = copernicus_setting($pdo ?? null, ['sentinel_hub_wms_url']);
$instanceId = copernicus_setting($pdo ?? null, [
    'copernicus_id',
    'sentinelhub_instance_id',
    'sentinel_hub_instance_id',
]);

if ($instanceId === '') {
    $instanceId = copernicus_env([
        'COPERNICUS_ID',
        'SENTINELHUB_INSTANCE_ID',
        'SENTINEL_HUB_INSTANCE_ID',
    ]);
}
if ($wmsUrl === '') {
    $wmsUrl = copernicus_env(['SENTINEL_HUB_WMS_URL']);
}
if ($wmsUrl === '' && $instanceId !== '') {
    $wmsUrl = 'https://sh.dataspace.copernicus.eu/ogc/wms/' . rawurlencode($instanceId) . '?';
}

echo 'configured=' . ($wmsUrl !== '' ? 'yes' : 'no') . PHP_EOL;

if ($wmsUrl === '') {
    echo 'result=configuration required' . PHP_EOL;
    exit(2);
}

$testUrl = append_query($wmsUrl, [
    'service' => 'WMS',
    'request' => 'GetCapabilities',
    'version' => '1.3.0',
]);
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 15,
        'ignore_errors' => true,
        'header' => "User-Agent: AgriTwin-Copernicus-Check/1.0\r\n",
    ],
]);
$body = @file_get_contents($testUrl, false, $context);
$headers = $http_response_header ?? [];
$status = 0;
$contentType = 'unknown';
foreach ($headers as $header) {
    if (preg_match('/^HTTP\\/\\S+\\s+(\\d+)/', $header, $match)) {
        $status = (int) $match[1];
    }
    if (stripos($header, 'Content-Type:') === 0) {
        $contentType = trim(substr($header, strlen('Content-Type:')));
    }
}

echo 'http_status=' . ($status ?: 'unavailable') . PHP_EOL;
echo 'content_type=' . $contentType . PHP_EOL;

if ($status >= 200 && $status < 300 && is_string($body) && stripos($body, 'WMS_Capabilities') !== false) {
    echo 'result=Copernicus WMS available' . PHP_EOL;
    exit(0);
}
if ($status === 401 || $status === 403) {
    echo 'result=Copernicus configuration rejected by service' . PHP_EOL;
    exit(3);
}
if ($status >= 400) {
    echo 'result=Copernicus WMS unavailable' . PHP_EOL;
    exit(4);
}

echo 'result=Copernicus WMS response invalid' . PHP_EOL;
exit(5);
