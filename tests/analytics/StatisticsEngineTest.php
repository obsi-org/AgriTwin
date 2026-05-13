<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/agritwin_dashboard/includes/statistics_engine.php';

$checks = [
    'basic statistics' => function (): void {
        $stats = agritwin_statistics_values([4, 2, 10, 8]);
        if ($stats['min'] !== 2.0 || $stats['max'] !== 10.0 || $stats['avg'] !== 6.0 || $stats['median'] !== 6.0) {
            throw new RuntimeException('Unexpected min/max/avg/median.');
        }
    },
    'station comparison' => function (): void {
        $rows = agritwin_station_comparison();
        if (count($rows) < 5) {
            throw new RuntimeException('Expected at least five stations.');
        }
        foreach ($rows as $row) {
            if (!isset($row['soil_moisture_pct'], $row['water_stress_status'])) {
                throw new RuntimeException('Missing station analytics fields.');
            }
        }
    },
    'analytics summary' => function (): void {
        $summary = agritwin_analytics_summary();
        if (($summary['station_count'] ?? 0) < 5 || !isset($summary['service_status_summary'])) {
            throw new RuntimeException('Invalid analytics summary.');
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
