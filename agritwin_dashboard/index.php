<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$recommendations = agritwin_recommendations();
$context = agritwin_farm_context();
$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
$stations = agritwin_sensor_stations();

agritwin_render_page('dashboard', agritwin_t('dashboard'), function () use ($sensors, $recommendations, $context, $services, $weatherSource, $stations): void {
    $liveServices = count(array_filter($services, static fn ($service): bool => $service['mode'] === 'live' && $service['status'] === 'live_healthy'));
    $fallbackServices = count(array_filter($services, static fn ($service): bool => $service['mode'] === 'fallback'));
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h($context['site']) ?></p>
                <h2>Daily operator summary</h2>
            </div>
            <?= agritwin_badge('normal') ?>
        </div>
        <div class="priority-grid">
            <article class="priority-card">
                <?= agritwin_badge($recommendations['water_stress']['confidence']) ?>
                <h3>Water stress</h3>
                <p class="metric"><?= agritwin_h($recommendations['water_stress']['value']) ?></p>
                <p><?= agritwin_h($recommendations['water_stress']['label']) ?>. Inputs: almond block A/B probes and local weather station.</p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($recommendations['irrigation']['confidence']) ?>
                <h3>Irrigation recommendation</h3>
                <p><?= agritwin_h($recommendations['irrigation']['label']) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($recommendations['pest']['confidence']) ?>
                <h3>Pest risk</h3>
                <p class="metric"><?= agritwin_h($recommendations['pest']['risk']) ?></p>
                <p><?= agritwin_h($recommendations['pest']['label']) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($recommendations['nitrogen']['confidence']) ?>
                <h3>Nitrogen / fertiliser</h3>
                <p><?= agritwin_h($recommendations['nitrogen']['label']) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($liveServices > 0 ? 'healthy' : 'reduced') ?>
                <h3>OpenAgri status</h3>
                <p class="metric"><?= $liveServices ?> live / <?= $fallbackServices ?> fallback</p>
                <p>Service cards distinguish live checks from fallback data. No fallback service is labelled as live.</p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge(in_array($weatherSource['mode'], ['live', 'fallback'], true) ? 'healthy' : 'reduced') ?>
                <h3>External weather service</h3>
                <p class="metric"><?= agritwin_h(agritwin_mode_label($weatherSource['mode'])) ?></p>
                <p><?= agritwin_h($weatherSource['source_name'] ?? $weatherSource['label']) ?>: <?= agritwin_h((string) $weatherSource['temperature_c']) ?> degC, <?= agritwin_h((string) $weatherSource['humidity_pct']) ?>% RH, wind <?= agritwin_h((string) $weatherSource['wind_kmh']) ?> km/h, rain <?= agritwin_h((string) $weatherSource['rainfall_mm']) ?> mm.</p>
            </article>
        </div>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2>External weather service</h2>
            <span><?= agritwin_h($weatherSource['timestamp']) ?></span>
        </div>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span>Temperature</span><strong><?= agritwin_h((string) $weatherSource['temperature_c']) ?> degC</strong></article>
            <article class="telemetry-card"><span>Humidity</span><strong><?= agritwin_h((string) $weatherSource['humidity_pct']) ?>%</strong></article>
            <article class="telemetry-card"><span>Wind</span><strong><?= agritwin_h((string) $weatherSource['wind_kmh']) ?> km/h</strong></article>
            <article class="telemetry-card"><span>Precipitation</span><strong><?= agritwin_h((string) $weatherSource['rainfall_mm']) ?> mm</strong></article>
        </div>
        <p><?= agritwin_h($weatherSource['forecast_summary']) ?></p>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2>On-farm sensor stations</h2>
            <span><?= agritwin_h($context['last_sync']) ?></span>
        </div>
        <div class="telemetry-grid">
            <?php foreach ($stations as $station): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($station['block']) ?></span>
                    <strong><?= agritwin_h($station['id']) ?></strong>
                    <p><?= agritwin_h($station['crop']) ?> · <?= count($station['readings']) ?> active readings</p>
                    <?= agritwin_badge($station['confidence']) ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
