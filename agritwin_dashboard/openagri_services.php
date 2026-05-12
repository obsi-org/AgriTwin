<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
agritwin_render_page('openagri', agritwin_t('openagri'), function () use ($services, $weatherSource): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('service_health')) ?></h2>
            <?= agritwin_badge($weatherSource['mode'] === 'live' ? 'healthy' : 'reduced') ?>
        </div>
        <p><?= agritwin_h(agritwin_t('service_health_explanation')) ?></p>
        <div class="action-row">
            <a class="secondary-action" href="openagri_services.php"><i class="fas fa-rotate"></i> <?= agritwin_h(agritwin_t('run_health_check')) ?></a>
            <a class="secondary-action" href="api_swagger.php"><i class="fas fa-code"></i> API / Swagger</a>
        </div>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        <div class="table-wrap">
            <table class="status-table">
                <thead>
                    <tr><th><?= agritwin_h(agritwin_t('openagri')) ?></th><th><?= agritwin_h(agritwin_t('mode')) ?></th><th><?= agritwin_h(agritwin_t('status')) ?></th><th><?= agritwin_h(agritwin_t('endpoint')) ?></th><th><?= agritwin_h(agritwin_t('api_docs')) ?></th><th><?= agritwin_h(agritwin_t('authentication')) ?></th><th><?= agritwin_h(agritwin_t('http_status')) ?></th><th><?= agritwin_h(agritwin_t('latency')) ?></th><th><?= agritwin_h(agritwin_t('last_success')) ?></th><th><?= agritwin_h(agritwin_t('action')) ?></th></tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><strong><?= agritwin_h($service['name']) ?></strong></td>
                        <td><?= agritwin_h(agritwin_mode_label($service['mode'])) ?></td>
                        <td><?= agritwin_badge($service['status']) ?></td>
                        <td><?= $service['configured'] ? '<a href="' . agritwin_h($service['endpoint']) . '" target="_blank" rel="noopener">' . agritwin_h($service['endpoint']) . '</a>' : agritwin_h(agritwin_t('configuration_required')) ?></td>
                        <td><?= $service['docs_url'] !== '' ? '<a href="' . agritwin_h($service['docs_url']) . '" target="_blank" rel="noopener">' . agritwin_h(agritwin_t('api_docs')) . '</a>' : '<a href="' . agritwin_h($service['source_repository']) . '" target="_blank" rel="noopener">' . agritwin_h(agritwin_t('source_repository')) . '</a>' ?></td>
                        <td><?= agritwin_h($service['auth_type']) ?></td>
                        <td><?= agritwin_h((string) ($service['http_status'] ?? agritwin_t('not_available'))) ?></td>
                        <td><?= $service['latency_ms'] !== null ? agritwin_h((string) $service['latency_ms']) . ' ms' : agritwin_h(agritwin_t('not_available')) ?></td>
                        <td><?= agritwin_h((string) ($service['last_successful_live_check'] ?? agritwin_t('none'))) ?></td>
                        <td><a class="secondary-action" href="openagri_services.php?test=<?= agritwin_h($service['key']) ?>"><?= agritwin_h(agritwin_t('test')) ?></a><br><?= agritwin_h($service['error_summary']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
