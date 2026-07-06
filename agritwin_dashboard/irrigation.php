<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$recommendations = agritwin_recommendations();
$weatherSource = agritwin_weather_source();
agritwin_render_page('irrigation', agritwin_t('irrigation'), function () use ($sensors, $recommendations, $weatherSource): void {
    $soilSensors = array_values(array_filter($sensors, static fn ($sensor): bool => $sensor['parameter'] === 'soil_moisture'));
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('todays_recommendation')) ?></h2>
            <div class="action-row"><?= agritwin_help_link('irrigation') ?><?= agritwin_badge($recommendations['irrigation']['confidence']) ?></div>
        </div>
        <p><?= agritwin_h($recommendations['irrigation']['label']) ?></p>
        <p><?= agritwin_h(agritwin_t('data_sources_irrigation')) ?></p>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
    </section>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('soil_moisture_inputs')) ?></h2>
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
