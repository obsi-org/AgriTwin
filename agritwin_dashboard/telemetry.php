<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$weatherSource = agritwin_weather_source();
agritwin_render_page('telemetry', 'Sensor Telemetry', function () use ($sensors, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Macotera multi-sensor table</h2>
            <?= agritwin_badge('normal') ?>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Sensor</th><th>Block</th><th>Crop</th><th>Value</th><th>Timestamp</th><th>Status</th></tr></thead>
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
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> Weather station values shown here are part of the synthetic local dataset unless a live source is verified.</p>
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
