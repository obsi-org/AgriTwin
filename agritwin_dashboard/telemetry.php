<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$stations = agritwin_sensor_stations();
$weatherSource = agritwin_weather_source();
agritwin_render_page('telemetry', agritwin_t('telemetry'), function () use ($sensors, $stations, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('station_overview')) ?></h2>
            <div class="action-row"><?= agritwin_help_link('sensor-stations') ?><?= agritwin_badge('normal') ?></div>
        </div>
        <div class="telemetry-grid">
            <?php foreach ($stations as $station): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($station['block']) ?></span>
                    <strong><?= agritwin_h($station['id']) ?></strong>
                    <p><?= agritwin_h($station['crop']) ?> · <?= agritwin_h($station['timestamp']) ?></p>
                    <?= agritwin_badge($station['confidence']) ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('telemetry_readings')) ?></h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th><?= agritwin_h(agritwin_t('station_reading')) ?></th><th><?= agritwin_h(agritwin_t('block')) ?></th><th><?= agritwin_h(agritwin_t('crop')) ?></th><th><?= agritwin_h(agritwin_t('value')) ?></th><th><?= agritwin_h(agritwin_t('timestamp')) ?></th><th><?= agritwin_h(agritwin_t('status')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($sensors as $sensor): ?>
                    <tr>
                        <td><strong><?= agritwin_h($sensor['id']) ?></strong><br><?= agritwin_h($sensor['name']) ?></td>
                        <td><?= agritwin_h($sensor['block']) ?></td>
                        <td><?= agritwin_h($sensor['crop']) ?></td>
                        <td><?= agritwin_h((string) $sensor['value']) ?> <?= agritwin_h($sensor['unit']) ?></td>
                        <td><?= agritwin_h($sensor['timestamp']) ?></td>
                        <td><?= agritwin_badge($sensor['confidence']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h(agritwin_t('telemetry_weather_note')) ?></p>
    </section>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('simple_telemetry_trend')) ?></h2>
        <div class="telemetry-grid">
            <?php foreach ($sensors as $sensor): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($sensor['parameter']) ?></span>
                    <strong><?= agritwin_h((string) $sensor['value']) ?> <?= agritwin_h($sensor['unit']) ?></strong>
                    <p><?= agritwin_h($sensor['asset']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
