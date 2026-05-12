<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$context = agritwin_farm_context();
$recommendations = agritwin_recommendations();
$weatherSource = agritwin_weather_source();
$stations = agritwin_sensor_stations();
agritwin_render_page('reports', agritwin_t('reports'), function () use ($context, $recommendations, $weatherSource, $stations): void {
    $payload = [
        '@context' => 'https://schema.openagri.local/agritwin-advisory',
        'site' => $context['site'],
        'generated_at' => $context['last_sync'],
        'recommendations' => $recommendations,
        'weather_source' => $weatherSource,
        'sensor_stations' => array_column($stations, 'id'),
        'branding' => ['Fotovoltaica Macotera SL', 'OBSIDIAN', 'AgriTwin'],
    ];
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('advisory_export')) ?></h2>
            <button class="primary-action" type="button" onclick="window.print()"><i class="fas fa-print"></i> <?= agritwin_h(agritwin_t('print_save_pdf')) ?></button>
        </div>
        <p><?= agritwin_h(agritwin_t('report_generation_note')) ?></p>
        <pre class="payload-preview"><?= agritwin_h(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
    </section>
    <?php
});
