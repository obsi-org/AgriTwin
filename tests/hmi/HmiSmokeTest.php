<?php
$root = dirname(__DIR__, 2);

$checks = [
    'dashboard sections' => function () use ($root): void {
        $html = file_get_contents($root . '/agritwin_dashboard/index.php');
        foreach ([
            'field-overview',
            'digital-twin',
            'irrigation',
            'alerts',
            'telemetry',
            'openagri',
            'calendar',
            'reports',
            'kpis',
            'settings-connectivity',
        ] as $id) {
            if (!str_contains($html, 'id="' . $id . '"')) {
                throw new RuntimeException("Missing section $id");
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
    'accessibility hooks' => function () use ($root): void {
        $html = file_get_contents($root . '/agritwin_dashboard/index.php');
        foreach (['Skip to dashboard', 'aria-label', 'aria-live', 'tabindex="0"'] as $needle) {
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

