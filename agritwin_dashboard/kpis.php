<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

require_once __DIR__ . '/includes/hmi_layout.php';

$kpis = agritwin_kpis();
$stations = agritwin_sensor_stations();
agritwin_render_page('kpis', agritwin_t('kpis'), function () use ($kpis, $stations): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('d1_indicators')) ?></h2>
            <?= agritwin_help_link('analytics') ?>
        </div>
        <div class="kpi-grid">
            <?php foreach ($kpis as $kpi): ?>
                <article>
                    <span><?= agritwin_h($kpi['metric']) ?></span>
                    <strong><?= agritwin_h($kpi['value']) ?></strong>
                    <p><?= agritwin_h($kpi['status']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="source-note"><?= agritwin_h(sprintf(agritwin_t('kpi_source_note'), count($stations))) ?></p>
    </section>
    <?php
});
