<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$recommendations = agritwin_recommendations();
$weatherSource = agritwin_weather_source();
agritwin_render_page('irrigation', 'Irrigation Schedule', function () use ($sensors, $recommendations, $weatherSource): void {
    $soilSensors = array_values(array_filter($sensors, static fn ($sensor): bool => $sensor['parameter'] === 'soil_moisture'));
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Today's recommendation</h2>
            <?= agritwin_badge($recommendations['irrigation']['confidence']) ?>
        </div>
        <p><?= agritwin_h($recommendations['irrigation']['label']) ?></p>
        <p>Data sources: almond block A/B soil moisture, pump flow meter, PV energy meter and local weather observations.</p>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
    </section>
    <section class="view-section">
        <h2>Soil moisture inputs</h2>
        <div class="telemetry-grid">
            <?php foreach ($soilSensors as $sensor): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($sensor['block']) ?></span>
                    <strong><?= agritwin_h((string) $sensor['value']) ?> <?= agritwin_h($sensor['unit']) ?></strong>
                    <p><?= agritwin_h($sensor['timestamp']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
