<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
agritwin_render_page('digital_twin', 'Digital Twin Map', function () use ($sensors): void {
    ?>
    <section class="view-section split-section">
        <div>
            <p class="eyebrow">Macotera synthetic public geometry</p>
            <h2>Sensor overlays</h2>
            <p>Map markers use synthetic-safe coordinates around Macotera for local HMI validation. Private parcel boundaries are not published.</p>
            <div class="action-row">
                <a class="primary-action" href="field.php"><i class="fas fa-map"></i> Open legacy Cesium view</a>
                <a class="secondary-action" href="telemetry.php"><i class="fas fa-chart-line"></i> View telemetry</a>
            </div>
        </div>
        <div class="map-placeholder">
            <span>FOTOMACO Macotera Digital Twin</span>
            <ul><li>Almond block A/B</li><li>Pump house</li><li>Weather mast</li><li>Pistachio reference row</li></ul>
        </div>
    </section>
    <section class="view-section">
        <h2>Overlay marker list</h2>
        <div class="sensor-map-grid">
            <?php foreach ($sensors as $sensor): ?>
                <article class="sensor-marker-card">
                    <strong><?= agritwin_h($sensor['id']) ?></strong>
                    <p><?= agritwin_h($sensor['name']) ?></p>
                    <p><?= agritwin_h((string) $sensor['lat']) ?>, <?= agritwin_h((string) $sensor['lon']) ?></p>
                    <p><?= agritwin_h((string) $sensor['value']) ?> <?= agritwin_h($sensor['unit']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
