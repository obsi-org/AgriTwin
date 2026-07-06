<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

$root = dirname(__DIR__, 2);

$checks = [
    'dashboard page routing' => function () use ($root): void {
        $html = file_get_contents($root . '/agritwin_dashboard/includes/hmi_layout.php');
        foreach ([
            'index.php',
            'digital_twin.php',
            'irrigation.php',
            'pest_disease.php',
            'telemetry.php',
            'openagri_services.php',
            'farm_calendar.php',
            'reports.php',
            'kpis.php',
            'analytics.php',
            'api_swagger.php',
            'settings.php',
        ] as $page) {
            if (!str_contains($html, $page)) {
                throw new RuntimeException("Missing route $page");
            }
        }
    },
    'pwa files' => function () use ($root): void {
        $manifest = json_decode(file_get_contents($root . '/agritwin_dashboard/manifest.webmanifest'), true);
        if (($manifest['display'] ?? '') !== 'standalone') {
            throw new RuntimeException('Manifest must be standalone');
        }
        $sw = file_get_contents($root . '/agritwin_dashboard/sw.js');
        foreach (['install', 'activate', 'fetch', 'agritwin-hmi-v1'] as $needle) {
            if (!str_contains($sw, $needle)) {
                throw new RuntimeException("Service worker missing $needle");
            }
        }
    },
    'offline and report actions' => function () use ($root): void {
        $js = file_get_contents($root . '/agritwin_dashboard/assets/hmi.js');
        foreach ([
            'agritwin:last-dashboard-state',
            'agritwin:queued-operations',
            'report_advisory.php',
            'serviceWorker',
            'navigator.onLine',
        ] as $needle) {
            if (!str_contains($js, $needle)) {
                throw new RuntimeException("HMI JS missing $needle");
            }
        }
    },
    'accessibility hooks and branding' => function () use ($root): void {
        $html = file_get_contents($root . '/agritwin_dashboard/includes/hmi_layout.php');
        foreach (['Skip to content', 'aria-label', '© 2026 AgriTwin', 'Fotovoltaica Macotera SL', 'OBSIDIAN'] as $needle) {
            if (!str_contains($html, $needle)) {
                throw new RuntimeException("Missing accessibility hook $needle");
            }
        }
    },
];

$failed = 0;
foreach ($checks as $name => $check) {
    try {
        $check();
        echo "[PASS] $name\n";
    } catch (Throwable $e) {
        $failed++;
        echo "[FAIL] $name: {$e->getMessage()}\n";
    }
}

exit($failed === 0 ? 0 : 1);
