<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$weatherSource = agritwin_weather_source();
agritwin_render_page('digital_twin', agritwin_t('digital_twin'), function () use ($sensors, $weatherSource): void {
    ?>
    <section class="view-section split-section">
        <div>
            <p class="eyebrow"><?= agritwin_h(agritwin_t('map_geometry')) ?></p>
            <h2><?= agritwin_h(agritwin_t('sensor_overlays')) ?></h2>
            <p><?= agritwin_h(agritwin_t('map_context_note')) ?></p>
            <div class="action-row">
                <a class="primary-action" href="field1.php?lang=<?= agritwin_h(rawurlencode(agritwin_current_language())) ?>"><i class="fas fa-map"></i> <?= agritwin_h(agritwin_t('open_legacy_cesium')) ?></a>
                <a class="secondary-action" href="telemetry.php"><i class="fas fa-chart-line"></i> <?= agritwin_h(agritwin_t('view_telemetry')) ?></a>
            </div>
        </div>
        <div class="map-placeholder">
            <span><?= agritwin_h(agritwin_t('digital_twin_marker_title')) ?></span>
            <ul>
                <li><?= agritwin_h(agritwin_t('almond_blocks_ab')) ?></li>
                <li><?= agritwin_h(agritwin_t('pump_house')) ?></li>
                <li><?= agritwin_h(agritwin_t('weather_mast')) ?></li>
                <li><?= agritwin_h(agritwin_t('pistachio_reference_row')) ?></li>
            </ul>
        </div>
    </section>
    <section class="view-section">
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h(agritwin_t('map_context_weather')) ?></p>
    </section>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('overlay_marker_list')) ?></h2>
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
