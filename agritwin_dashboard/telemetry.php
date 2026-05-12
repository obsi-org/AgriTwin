<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$stations = agritwin_sensor_stations();
$weatherSource = agritwin_weather_source();
agritwin_render_page('telemetry', 'Sensor Stations', function () use ($sensors, $stations, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Fotovoltaica Macotera SL station overview</h2>
            <?= agritwin_badge('normal') ?>
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
        <h2>On-farm telemetry readings</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Station / Reading</th><th>Block</th><th>Crop</th><th>Value</th><th>Timestamp</th><th>Status</th></tr></thead>
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
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> External weather values are shown separately from generated on-farm station readings.</p>
    </section>
    <section class="view-section">
        <h2>Simple telemetry trend</h2>
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
