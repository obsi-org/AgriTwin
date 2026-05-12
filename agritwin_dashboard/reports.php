<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$context = agritwin_farm_context();
$recommendations = agritwin_recommendations();
agritwin_render_page('reports', 'Reports / PDF Advisories', function () use ($context, $recommendations): void {
    $payload = [
        '@context' => 'https://schema.openagri.local/agritwin-advisory',
        'site' => $context['site'],
        'generated_at' => $context['last_sync'],
        'recommendations' => $recommendations,
        'branding' => ['FOTOMACO', 'OBSIDIAN', 'AgriTwin'],
    ];
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Advisory export</h2>
            <button class="primary-action" type="button" onclick="window.print()"><i class="fas fa-print"></i> Print / save PDF</button>
        </div>
        <p>Reporting Service is healthy in local demo mode. The payload below is safe synthetic data for report validation.</p>
        <pre class="payload-preview"><?= agritwin_h(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
    </section>
    <?php
});
