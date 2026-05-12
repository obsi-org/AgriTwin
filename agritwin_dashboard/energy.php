<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$sensors = array_values(array_filter(agritwin_sensors(), static fn ($sensor): bool => str_contains($sensor['parameter'], 'pump') || str_contains($sensor['parameter'], 'energy')));
agritwin_render_page('dashboard', 'Energy Management', function () use ($sensors): void {
    ?>
    <section class="view-section">
        <h2>Pump and PV energy telemetry</h2>
        <div class="telemetry-grid">
            <?php foreach ($sensors as $sensor): ?>
                <article class="telemetry-card">
                    <span><?= agritwin_h($sensor['name']) ?></span>
                    <strong><?= agritwin_h((string) $sensor['value']) ?> <?= agritwin_h($sensor['unit']) ?></strong>
                    <p><?= agritwin_h($sensor['timestamp']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
