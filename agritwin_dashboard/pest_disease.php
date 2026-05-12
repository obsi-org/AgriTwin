<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$recommendations = agritwin_recommendations();
$weatherSource = agritwin_weather_source();
agritwin_render_page('pest_disease', agritwin_t('pest_disease'), function () use ($recommendations, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('almond_scouting_advisory')) ?></h2>
            <?= agritwin_badge($recommendations['pest']['confidence']) ?>
        </div>
        <article class="alert-card calm">
            <h3><?= agritwin_h(agritwin_t('risk_index')) ?> <?= agritwin_h($recommendations['pest']['risk']) ?></h3>
            <p><?= agritwin_h($recommendations['pest']['label']) ?></p>
            <p><?= agritwin_h(agritwin_t('pest_inputs')) ?></p>
            <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        </article>
    </section>
    <?php
});
