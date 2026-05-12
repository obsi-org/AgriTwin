<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$recommendations = agritwin_recommendations();
$weatherSource = agritwin_weather_source();
agritwin_render_page('pest_disease', 'Pest & Disease Alerts', function () use ($recommendations, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Almond scouting advisory</h2>
            <?= agritwin_badge($recommendations['pest']['confidence']) ?>
        </div>
        <article class="alert-card calm">
            <h3>Risk index <?= agritwin_h($recommendations['pest']['risk']) ?></h3>
            <p><?= agritwin_h($recommendations['pest']['label']) ?></p>
            <p>Inputs: 21.6 degC air temperature, 56% humidity, 18% leaf wetness and 1.2 mm rainfall today.</p>
            <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        </article>
    </section>
    <?php
});
