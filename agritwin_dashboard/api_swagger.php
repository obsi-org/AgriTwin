<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$endpoints = [
    ['method' => 'GET', 'path' => '/agritwin3/api/health.php', 'summary' => 'Application health and local data mode'],
    ['method' => 'GET', 'path' => '/agritwin3/api/openagri-health.php', 'summary' => 'Truthful OpenAgri connector health and weather source provenance'],
    ['method' => 'GET', 'path' => '/agritwin3/api/sensors.php', 'summary' => 'Synthetic Macotera sensor inventory and latest readings'],
    ['method' => 'GET', 'path' => '/agritwin3/api/weather-current.php', 'summary' => 'Weather source label and local demo values'],
    ['method' => 'POST', 'path' => '/agritwin3/api/edge/sensor-packets.php', 'summary' => 'Edge gateway sensor packet ingestion'],
];

agritwin_render_page('api', 'API / Swagger', function () use ($endpoints): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow">OpenAPI 3.0</p>
                <h2>AgriTwin API catalogue</h2>
            </div>
            <a class="primary-action" href="../api/openapi.json"><i class="fas fa-file-code"></i> Open openapi.json</a>
        </div>
        <p>This page provides a local Swagger/OpenAPI entry point for the currently exposed prototype API. It avoids secrets and uses synthetic public demo examples.</p>
        <ul class="endpoint-list">
            <?php foreach ($endpoints as $endpoint): ?>
                <li>
                    <strong><?= agritwin_h($endpoint['method']) ?> <?= agritwin_h($endpoint['path']) ?></strong>
                    <p><?= agritwin_h($endpoint['summary']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php
});
