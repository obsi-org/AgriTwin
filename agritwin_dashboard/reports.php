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
            <h2>Advisory export</h2>
            <button class="primary-action" type="button" onclick="window.print()"><i class="fas fa-print"></i> Print / save PDF</button>
        </div>
        <p>Report generation uses local fallback station data unless a live OpenAgri Reporting Service endpoint is configured and verified. The payload below is safe sample data for report validation.</p>
        <pre class="payload-preview"><?= agritwin_h(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
    </section>
    <?php
});
