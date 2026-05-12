<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$recommendations = agritwin_recommendations();
agritwin_render_page('pest_disease', 'Pest & Disease Alerts', function () use ($recommendations): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Almond scouting advisory</h2>
            <?= agritwin_badge($recommendations['pest']['confidence']) ?>
        </div>
        <article class="alert-card calm">
            <h3>Risk index <?= agritwin_h($recommendations['pest']['risk']) ?></h3>
            <p><?= agritwin_h($recommendations['pest']['label']) ?></p>
            <p>Inputs: 21.6 degC air temperature, 56% humidity, 18% leaf wetness, 1.2 mm rainfall today, OpenAgri Pest & Disease mock status.</p>
        </article>
    </section>
    <?php
});
