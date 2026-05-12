<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

$context = agritwin_farm_context();
$services = agritwin_service_cards();
$dbOk = isset($pdo) && $pdo instanceof PDO;

agritwin_render_page('settings', 'Settings / Connectivity', function () use ($context, $services, $dbOk): void {
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
            <article class="telemetry-card"><span>Farm context</span><strong>Macotera, Salamanca</strong></article>
        </div>
    </section>

    <section class="view-section">
        <h2>OpenAgri connector mode</h2>
        <div class="service-grid">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <?= agritwin_badge($service['status']) ?>
                    <h3><?= agritwin_h($service['name']) ?></h3>
                    <p>Endpoint: <?= agritwin_h($service['endpoint']) ?></p>
                    <p>Last check: <?= agritwin_h($service['last_check']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section">
        <h2>Safe configuration notes</h2>
        <p>Real credentials must stay in local environment variables or secret stores. The repository contains only local demo placeholders and mock service labels.</p>
        <p>Current local development date for demo data: <?= agritwin_h(agritwin_demo_date()->format('Y-m-d')) ?>.</p>
    </section>
    <?php
});
