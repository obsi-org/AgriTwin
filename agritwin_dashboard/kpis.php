<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$kpis = agritwin_kpis();
agritwin_render_page('kpis', 'KPI Dashboard', function () use ($kpis): void {
    ?>
    <section class="view-section">
        <h2>D1 evaluation indicators</h2>
        <div class="kpi-grid">
            <?php foreach ($kpis as $kpi): ?>
                <article>
                    <span><?= agritwin_h($kpi['metric']) ?></span>
                    <strong><?= agritwin_h($kpi['value']) ?></strong>
                    <p><?= agritwin_h($kpi['status']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
