<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
agritwin_render_page('openagri', 'OpenAgri Services', function () use ($services, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>Service health and data provenance</h2>
            <?= agritwin_badge($weatherSource['mode'] === 'live' ? 'healthy' : 'reduced') ?>
        </div>
        <p>AgriTwin only marks a connector as live after a real endpoint request succeeds. Local services are identified as local demo data, fallback, unavailable or not configured.</p>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        <div class="table-wrap">
            <table class="status-table">
                <thead>
                    <tr><th>Service</th><th>Mode</th><th>Status</th><th>Endpoint</th><th>HTTP</th><th>Latency</th><th>Last live check</th><th>Last fallback generation</th><th>Summary</th></tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><strong><?= agritwin_h($service['name']) ?></strong></td>
                        <td><?= agritwin_h(agritwin_mode_label($service['mode'])) ?></td>
                        <td><?= $service['mode'] === 'mock' ? agritwin_badge('reduced') : agritwin_badge($service['status']) ?></td>
                        <td><?= agritwin_h($service['endpoint']) ?></td>
                        <td><?= agritwin_h((string) ($service['http_status'] ?? 'n/a')) ?></td>
                        <td><?= $service['latency_ms'] !== null ? agritwin_h((string) $service['latency_ms']) . ' ms' : 'n/a' ?></td>
                        <td><?= agritwin_h((string) ($service['last_successful_live_check'] ?? 'none')) ?></td>
                        <td><?= agritwin_h((string) ($service['last_mock_fallback_generation'] ?? 'none')) ?></td>
                        <td><?= agritwin_h($service['error_summary']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
