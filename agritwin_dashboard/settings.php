<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

$context = agritwin_farm_context();
$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
$dbOk = isset($pdo) && $pdo instanceof PDO;

agritwin_render_page('settings', 'Settings / Connectivity', function () use ($context, $services, $weatherSource, $dbOk): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>AgriTwin local configuration</h2>
            <?= agritwin_badge($dbOk ? 'healthy' : 'unavailable') ?>
        </div>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span>Base path</span><strong><?= agritwin_h($context['base_path']) ?></strong></article>
            <article class="telemetry-card"><span>Data mode</span><strong><?= agritwin_h($context['data_mode']) ?></strong></article>
            <article class="telemetry-card"><span>DB status</span><strong><?= $dbOk ? 'Connected' : 'Unavailable' ?></strong></article>
            <article class="telemetry-card"><span>Farm context</span><strong>Fotovoltaica Macotera SL, Salamanca</strong></article>
            <article class="telemetry-card"><span>Weather source</span><strong><?= agritwin_h(agritwin_mode_label($weatherSource['mode'])) ?></strong></article>
            <article class="telemetry-card"><span>API documentation</span><strong>/agritwin3/api/openapi.json</strong></article>
        </div>
    </section>

    <section class="view-section">
        <h2>OpenAgri connector mode</h2>
        <div class="service-grid">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <?= $service['mode'] === 'mock' ? agritwin_badge('reduced') : agritwin_badge($service['status']) ?>
                    <h3><?= agritwin_h($service['name']) ?></h3>
                    <p>Mode: <?= agritwin_h(agritwin_mode_label($service['mode'])) ?></p>
                    <p>Endpoint: <?= agritwin_h($service['endpoint']) ?></p>
                    <p><?= agritwin_h($service['error_summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section">
        <h2>Safe configuration notes</h2>
        <p>Real credentials must stay in local environment variables or secret stores. The repository contains only local demo placeholders and explicit local-demo service labels.</p>
        <p>Current local development date for demo data: <?= agritwin_h(agritwin_demo_date()->format('Y-m-d')) ?>.</p>
    </section>
    <?php
});
