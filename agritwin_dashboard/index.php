<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = agritwin_sensors();
$recommendations = agritwin_recommendations();
$context = agritwin_farm_context();
$services = agritwin_service_cards();

agritwin_render_page('dashboard', 'Field Overview', function () use ($sensors, $recommendations, $context, $services): void {
    $healthyServices = count(array_filter($services, static fn ($service): bool => $service['status'] === 'healthy'));
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
                <?= agritwin_badge('healthy') ?>
                <h3>OpenAgri health</h3>
                <p class="metric"><?= $healthyServices ?>/<?= count($services) ?></p>
                <p>All required OpenAgri services are available in local demo/mock mode.</p>
            </article>
        </div>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2>Sensor highlights</h2>
            <span><?= agritwin_h($context['last_sync']) ?></span>
        </div>
        <div class="telemetry-grid">
            <?php foreach (array_slice($sensors, 0, 8) as $sensor): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($sensor['block']) ?></span>
                    <strong><?= agritwin_h((string) $sensor['value']) ?> <?= agritwin_h($sensor['unit']) ?></strong>
                    <p><strong><?= agritwin_h($sensor['id']) ?></strong><br><?= agritwin_h($sensor['name']) ?></p>
                    <?= agritwin_badge($sensor['confidence']) ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section">
        <h2>Branding and pilot context</h2>
        <div class="logo-strip">
            <img src="AgriTwin_logo.png" alt="AgriTwin logo">
            <img src="fotomaco.png" alt="Fotovoltaica Macotera SL / FOTOMACO logo">
            <img src="obsidian.png" alt="Obsidian Innovation Institute / OBSIDIAN logo">
            <img src="OpenAgri.png" alt="OpenAgri logo">
        </div>
        <p><?= agritwin_h($context['operator']) ?> · <?= agritwin_h($context['location']) ?> · primary crop: <?= agritwin_h($context['primary_crop']) ?>.</p>
    </section>
    <?php
});
