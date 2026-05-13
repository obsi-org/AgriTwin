<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
agritwin_render_page('openagri', agritwin_t('openagri'), function () use ($services, $weatherSource): void {
    $groups = agritwin_openagri_service_groups($services);
    $renderServiceTable = static function (array $groupServices, string $groupClass = ''): void {
        ?>
        <div class="table-wrap <?= agritwin_h($groupClass) ?>">
            <table class="status-table">
                <thead>
                    <tr>
                        <th><?= agritwin_h(agritwin_t('openagri')) ?></th>
                        <th><?= agritwin_h(agritwin_t('status')) ?></th>
                        <th><?= agritwin_h(agritwin_t('endpoint')) ?></th>
                        <th><?= agritwin_h(agritwin_t('api_docs')) ?></th>
                        <th><?= agritwin_h(agritwin_t('source_repository')) ?></th>
                        <th><?= agritwin_h(agritwin_t('last_check')) ?></th>
                        <th><?= agritwin_h(agritwin_t('latency')) ?></th>
                        <th><?= agritwin_h(agritwin_t('integration_role')) ?></th>
                        <th><?= agritwin_h(agritwin_t('integration_relevance')) ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($groupServices as $service): ?>
                    <tr class="<?= $service['required_for_d1'] ? 'mandatory-row' : '' ?>">
                        <td><strong><?= agritwin_h($service['name']) ?></strong><br><small><?= agritwin_h(agritwin_mode_label($service['mode'])) ?></small></td>
                        <td><?= agritwin_badge($service['status']) ?><br><small><?= agritwin_h($service['error_summary']) ?></small></td>
                        <td><?= $service['configured'] ? '<a href="' . agritwin_h($service['endpoint']) . '" target="_blank" rel="noopener">' . agritwin_h($service['endpoint']) . '</a>' : agritwin_h(agritwin_t('configuration_required')) ?></td>
                        <td><?= $service['docs_url'] !== '' ? '<a href="' . agritwin_h($service['docs_url']) . '" target="_blank" rel="noopener">' . agritwin_h(agritwin_t('api_docs')) . '</a>' : agritwin_h(agritwin_t('not_available')) ?></td>
                        <td><a href="<?= agritwin_h($service['source_repository']) ?>" target="_blank" rel="noopener"><?= agritwin_h(agritwin_t('source_repository')) ?></a></td>
                        <td><?= agritwin_h((string) ($service['last_check'] ?? agritwin_t('not_available'))) ?></td>
                        <td><?= $service['latency_ms'] !== null ? agritwin_h((string) $service['latency_ms']) . ' ms' : agritwin_h(agritwin_t('not_available')) ?></td>
                        <td><?= agritwin_h($service['role']) ?></td>
                        <td><?= agritwin_h($service['integration_relevance']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    };
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('core_openagri_services')) ?></h2>
            <?= agritwin_badge($weatherSource['mode'] === 'live' ? 'healthy' : 'reduced') ?>
        </div>
        <p><?= agritwin_h(agritwin_t('service_health_explanation')) ?></p>
        <div class="action-row">
            <a class="secondary-action" href="openagri_services.php"><i class="fas fa-rotate"></i> <?= agritwin_h(agritwin_t('run_health_check')) ?></a>
            <a class="secondary-action" href="api_swagger.php"><i class="fas fa-code"></i> API / Swagger</a>
        </div>
        <p class="source-note"><strong><?= agritwin_h($weatherSource['label']) ?>.</strong> <?= agritwin_h($weatherSource['reason']) ?></p>
        <?php $renderServiceTable($groups['core'], 'mandatory-service-table'); ?>
    </section>

    <section class="view-section compact-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('additional_operational_services')) ?></h2>
        </div>
        <?php $renderServiceTable($groups['extensions']); ?>
    </section>

    <section class="view-section compact-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('optional_services')) ?></h2>
            <?= agritwin_badge('optional_not_configured') ?>
        </div>
        <p><?= agritwin_h(agritwin_t('optional_service_note')) ?></p>
        <?php $renderServiceTable($groups['optional']); ?>
    </section>
    <?php
});
