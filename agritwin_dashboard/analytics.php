<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/includes/statistics_engine.php';

$analytics = agritwin_full_analytics();

function agritwin_svg_bar_chart(array $series, string $unit = ''): string
{
    $max = max(array_map(static fn (array $point): float => (float) $point['value'], $series)) ?: 1;
    $bars = '';
    $x = 18;
    foreach ($series as $point) {
        $height = max(6, (int) round(((float) $point['value'] / $max) * 120));
        $y = 140 - $height;
        $label = agritwin_h($point['label']);
        $value = agritwin_h((string) $point['value'] . $unit);
        $bars .= "<g><rect x=\"{$x}\" y=\"{$y}\" width=\"34\" height=\"{$height}\" rx=\"4\"></rect><text x=\"{$x}\" y=\"158\">{$label}</text><title>{$label}: {$value}</title></g>";
        $x += 56;
    }
    return '<svg class="analytics-chart" viewBox="0 0 330 170" role="img">' . $bars . '</svg>';
}

function agritwin_svg_line_chart(array $series, string $unit = ''): string
{
    if (count($series) === 0) {
        return '';
    }
    $values = array_map(static fn (array $point): float => (float) $point['value'], $series);
    $min = min($values);
    $max = max($values);
    $range = max(1, $max - $min);
    $points = [];
    foreach ($series as $idx => $point) {
        $x = 18 + ($idx * (280 / max(1, count($series) - 1)));
        $y = 140 - (((float) $point['value'] - $min) / $range) * 115;
        $points[] = round($x, 1) . ',' . round($y, 1);
    }
    $labels = '';
    foreach ($series as $idx => $point) {
        $x = 18 + ($idx * (280 / max(1, count($series) - 1)));
        $labels .= '<circle cx="' . round($x, 1) . '" cy="' . explode(',', $points[$idx])[1] . '" r="4"><title>' . agritwin_h($point['label'] . ': ' . $point['value'] . $unit) . '</title></circle>';
    }
    return '<svg class="analytics-chart" viewBox="0 0 330 170" role="img"><polyline points="' . agritwin_h(implode(' ', $points)) . '"></polyline>' . $labels . '</svg>';
}

agritwin_render_page('analytics', agritwin_t('analytics'), function () use ($analytics): void {
    $summary = $analytics['summary'];
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h($summary['data_source']) ?></p>
                <h2><?= agritwin_h(agritwin_t('analytics_overview')) ?></h2>
            </div>
            <?= agritwin_badge('normal') ?>
        </div>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('active_sensor_stations')) ?></span><strong><?= (int) $summary['station_count'] ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('average_soil_moisture')) ?></span><strong><?= agritwin_h((string) $summary['average_soil_moisture_pct']) ?>% VWC</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('latest_weather_source')) ?></span><strong><?= agritwin_h($summary['latest_weather_source']) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('mandatory_openagri_services')) ?></span><strong><?= (int) $summary['service_status_summary']['mandatory_available'] ?> / <?= (int) $summary['service_status_summary']['mandatory_total'] ?></strong></article>
        </div>
    </section>

    <section class="view-section split-section">
        <div>
            <h2><?= agritwin_h(agritwin_t('weather_analytics')) ?></h2>
            <?= agritwin_svg_line_chart($analytics['sensor_stations']['series']['air_temperature'], ' degC') ?>
        </div>
        <div>
            <h3><?= agritwin_h(agritwin_t('weather_observations')) ?></h3>
            <p><?= agritwin_h($analytics['weather']['source']) ?></p>
            <p><?= agritwin_h(agritwin_t('temperature')) ?>: <?= agritwin_h((string) $analytics['weather']['latest']['temperature_c']) ?> degC</p>
            <p><?= agritwin_h(agritwin_t('humidity')) ?>: <?= agritwin_h((string) $analytics['weather']['latest']['humidity_pct']) ?>%</p>
            <p><?= agritwin_h(agritwin_t('wind')) ?>: <?= agritwin_h((string) $analytics['weather']['latest']['wind_kmh']) ?> km/h</p>
        </div>
    </section>

    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('sensor_station_analytics')) ?></h2>
        <div class="split-section">
            <div><?= agritwin_svg_line_chart($analytics['sensor_stations']['series']['soil_moisture'], '% VWC') ?></div>
            <div><?= agritwin_svg_bar_chart($analytics['sensor_stations']['series']['rainfall'], ' mm') ?></div>
        </div>
        <div class="table-wrap">
            <table class="status-table">
                <thead><tr><th><?= agritwin_h(agritwin_t('station_reading')) ?></th><th><?= agritwin_h(agritwin_t('crop')) ?></th><th><?= agritwin_h(agritwin_t('soil_moisture')) ?></th><th><?= agritwin_h(agritwin_t('water_stress')) ?></th><th><?= agritwin_h(agritwin_t('timestamp')) ?></th></tr></thead>
                <tbody>
                    <?php foreach ($analytics['sensor_stations']['comparison'] as $row): ?>
                        <tr><td><?= agritwin_h($row['station_id']) ?></td><td><?= agritwin_h($row['crop']) ?></td><td><?= agritwin_h((string) $row['soil_moisture_pct']) ?>% VWC</td><td><?= agritwin_badge($row['water_stress_status']) ?></td><td><?= agritwin_h($row['freshness']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('irrigation_analytics')) ?></h2>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('average_soil_moisture')) ?></span><strong><?= agritwin_h((string) $analytics['irrigation']['soil_moisture']['avg']) ?>% VWC</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('irrigation_deficit')) ?></span><strong><?= agritwin_h((string) $analytics['irrigation']['estimated_irrigation_deficit_pct_vwc']) ?>% VWC</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('irrigation_flow')) ?></span><strong><?= agritwin_h((string) $analytics['irrigation']['irrigation_flow']['avg']) ?> m3/h</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('pump_energy')) ?></span><strong><?= agritwin_h((string) $analytics['irrigation']['pump_energy']['avg']) ?> kWh</strong></article>
        </div>
    </section>

    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('pest_disease_analytics')) ?></h2>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('risk_index')) ?></span><strong><?= (int) $analytics['pest_disease']['risk_index'] ?>/100</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('leaf_wetness')) ?></span><strong><?= agritwin_h((string) $analytics['pest_disease']['weather_inputs']['leaf_wetness']['avg']) ?>%</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('rainfall_today')) ?></span><strong><?= agritwin_h((string) $analytics['pest_disease']['weather_inputs']['rainfall']['avg']) ?> mm</strong></article>
        </div>
    </section>

    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('kpi_operational_performance')) ?></h2>
        <div class="kpi-grid">
            <?php foreach ($analytics['kpis'] as $kpi): ?>
                <div><strong><?= agritwin_h($kpi['metric']) ?></strong><p><?= agritwin_h($kpi['value']) ?></p><span><?= agritwin_h($kpi['status']) ?></span></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('service_freshness')) ?></h2>
        <div class="table-wrap">
            <table class="status-table">
                <thead><tr><th>OpenAgri</th><th><?= agritwin_h(agritwin_t('status')) ?></th><th><?= agritwin_h(agritwin_t('last_check')) ?></th><th><?= agritwin_h(agritwin_t('latency')) ?></th></tr></thead>
                <tbody>
                    <?php foreach ($analytics['service_freshness'] as $service): ?>
                        <tr><td><?= agritwin_h($service['service']) ?></td><td><?= agritwin_badge($service['status']) ?></td><td><?= agritwin_h($service['last_check']) ?></td><td><?= $service['latency_ms'] !== null ? agritwin_h((string) $service['latency_ms']) . ' ms' : agritwin_h(agritwin_t('not_available')) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
