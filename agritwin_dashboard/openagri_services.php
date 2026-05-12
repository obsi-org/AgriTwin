<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$services = agritwin_service_cards();
agritwin_render_page('openagri', 'OpenAgri Services', function () use ($services): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Local demo service health</h2>
            <?= agritwin_badge('healthy') ?>
        </div>
        <p>Live endpoints are used when configured. In local demo mode, required OpenAgri modules are represented by realistic healthy mock responses.</p>
        <div class="service-grid">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <?= agritwin_badge($service['status']) ?>
                    <h3><?= agritwin_h($service['name']) ?></h3>
                    <p class="latency"><?= agritwin_h((string) $service['latency_ms']) ?> ms</p>
                    <p>Endpoint: <?= agritwin_h($service['endpoint']) ?></p>
                    <p>Mode: <?= agritwin_h($service['mode']) ?></p>
                    <p>Last check: <?= agritwin_h($service['last_check']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
