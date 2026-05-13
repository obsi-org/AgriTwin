<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/includes/statistics_engine.php';

$sensors = agritwin_sensors();
$recommendations = agritwin_recommendations();
$context = agritwin_farm_context();
$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
$stations = agritwin_sensor_stations();
$analyticsSummary = agritwin_analytics_summary();

agritwin_render_page('dashboard', agritwin_t('dashboard'), function () use ($sensors, $recommendations, $context, $services, $weatherSource, $stations, $analyticsSummary): void {
    $groups = agritwin_openagri_service_groups($services);
    $mandatoryServices = $groups['core'];
    $mandatoryAvailable = count(array_filter($mandatoryServices, static fn ($service): bool => $service['mode'] === 'live' && $service['status'] === 'live_healthy'));
    $weatherService = $mandatoryServices['weather'] ?? null;
    $weatherServiceReachable = $weatherService !== null && $weatherService['status'] === 'live_healthy';
    $weatherObservationsAvailable = ($weatherSource['source_name'] ?? '') === 'OpenAgri Weather Data Service';
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h($context['site']) ?></p>
                <h2><?= agritwin_h(agritwin_t('daily_operator_summary')) ?></h2>
            </div>
            <?= agritwin_badge('normal') ?>
        </div>
        <div class="priority-grid">
            <article class="priority-card">
                <?= agritwin_badge($recommendations['water_stress']['confidence']) ?>
                <h3><?= agritwin_h(agritwin_t('water_stress')) ?></h3>
                <p class="metric"><?= agritwin_h($recommendations['water_stress']['value']) ?></p>
                <p><?= agritwin_h($recommendations['water_stress']['label']) ?>. <?= agritwin_h(agritwin_t('inputs')) ?>: almond block A/B probes and local weather station.</p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($recommendations['irrigation']['confidence']) ?>
                <h3><?= agritwin_h(agritwin_t('irrigation_recommendation')) ?></h3>
                <p><?= agritwin_h($recommendations['irrigation']['label']) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($recommendations['pest']['confidence']) ?>
                <h3><?= agritwin_h(agritwin_t('pest_risk')) ?></h3>
                <p class="metric"><?= agritwin_h($recommendations['pest']['risk']) ?></p>
                <p><?= agritwin_h($recommendations['pest']['label']) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($recommendations['nitrogen']['confidence']) ?>
                <h3><?= agritwin_h(agritwin_t('nitrogen_fertiliser')) ?></h3>
                <p><?= agritwin_h($recommendations['nitrogen']['label']) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge($mandatoryAvailable === count($mandatoryServices) ? 'healthy' : 'reduced') ?>
                <h3><?= agritwin_h(agritwin_t('core_openagri_services')) ?></h3>
                <p class="metric"><?= $mandatoryAvailable ?> / <?= count($mandatoryServices) ?></p>
                <p><?= agritwin_h(agritwin_t('core_service_explanation')) ?></p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge(in_array($weatherSource['mode'], ['live', 'fallback'], true) ? 'healthy' : 'reduced') ?>
                <h3><?= agritwin_h(agritwin_t('external_weather')) ?></h3>
                <p class="metric"><?= agritwin_h(agritwin_mode_label($weatherSource['mode'])) ?></p>
                <p><?= agritwin_h($weatherSource['source_name'] ?? $weatherSource['label']) ?>: <?= agritwin_h((string) $weatherSource['temperature_c']) ?> degC, <?= agritwin_h((string) $weatherSource['humidity_pct']) ?>% RH, wind <?= agritwin_h((string) $weatherSource['wind_kmh']) ?> km/h, rain <?= agritwin_h((string) $weatherSource['rainfall_mm']) ?> mm.</p>
                <p><?= agritwin_h(agritwin_t('weather_service_reachable')) ?>: <?= agritwin_h($weatherServiceReachable ? agritwin_t('connected') : agritwin_t('connection_unavailable')) ?>. <?= agritwin_h(agritwin_t('weather_observations')) ?>: <?= agritwin_h($weatherObservationsAvailable ? agritwin_t('weather_observations_available') : agritwin_t('external_provider_configuration_required')) ?>.</p>
            </article>
            <article class="priority-card">
                <?= agritwin_badge('normal') ?>
                <h3><?= agritwin_h(agritwin_t('analytics_summary')) ?></h3>
                <p class="metric"><?= (int) $analyticsSummary['station_count'] ?> <?= agritwin_h(agritwin_t('active_sensor_stations')) ?></p>
                <p><?= agritwin_h(agritwin_t('average_soil_moisture')) ?>: <?= agritwin_h((string) $analyticsSummary['average_soil_moisture_pct']) ?>% VWC. <?= agritwin_h(agritwin_t('pest_risk')) ?>: <?= agritwin_h($analyticsSummary['pest_risk_level']) ?>.</p>
                <a class="secondary-action" href="analytics.php"><?= agritwin_h(agritwin_t('open_analytics')) ?></a>
            </article>
        </div>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('external_weather')) ?></h2>
            <span><?= agritwin_h($weatherSource['timestamp']) ?></span>
        </div>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('temperature')) ?></span><strong><?= agritwin_h((string) $weatherSource['temperature_c']) ?> degC</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('humidity')) ?></span><strong><?= agritwin_h((string) $weatherSource['humidity_pct']) ?>%</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('wind')) ?></span><strong><?= agritwin_h((string) $weatherSource['wind_kmh']) ?> km/h</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('precipitation')) ?></span><strong><?= agritwin_h((string) $weatherSource['rainfall_mm']) ?> mm</strong></article>
        </div>
        <p><?= agritwin_h($weatherSource['forecast_summary']) ?></p>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('on_farm_stations')) ?></h2>
            <span><?= agritwin_h($context['last_sync']) ?></span>
        </div>
        <div class="telemetry-grid">
            <?php foreach ($stations as $station): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($station['block']) ?></span>
                    <strong><?= agritwin_h($station['id']) ?></strong>
                    <p><?= agritwin_h($station['crop']) ?> · <?= count($station['readings']) ?> <?= agritwin_h(agritwin_t('active_readings')) ?></p>
                    <?= agritwin_badge($station['confidence']) ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
