<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

$context = agritwin_farm_context();
$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
$dbOk = isset($pdo) && $pdo instanceof PDO;
$dbDiagnostics = $agritwin_db_diagnostics ?? [
    'host' => 'not configured',
    'port' => 'not configured',
    'database' => 'not configured',
    'state' => 'unknown',
    'reason' => 'unknown',
    'last_successful_connection' => null,
];

agritwin_render_page('settings', agritwin_t('settings'), function () use ($context, $services, $weatherSource, $dbOk, $dbDiagnostics): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('platform_settings')) ?></h2>
            <?= agritwin_badge($dbOk ? 'healthy' : 'unavailable') ?>
        </div>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('api_base_path')) ?></span><strong><?= agritwin_h($context['base_path']) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('db_status')) ?></span><strong><?= $dbOk ? agritwin_h(agritwin_t('connected')) : agritwin_h(agritwin_t('unavailable')) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('farm_location')) ?></span><strong>Fotovoltaica Macotera SL, Salamanca</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('weather_preference')) ?></span><strong><?= agritwin_h(agritwin_mode_label($weatherSource['mode'])) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('api_docs')) ?></span><strong>/agritwin3/api/openapi.json</strong></article>
        </div>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('database_diagnostics')) ?></h2>
            <a class="button-link" href="settings.php"><?= agritwin_h(agritwin_t('check_database_connection')) ?></a>
        </div>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('db_host')) ?></span><strong><?= agritwin_h((string) $dbDiagnostics['host']) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('db_port')) ?></span><strong><?= agritwin_h((string) $dbDiagnostics['port']) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('db_name')) ?></span><strong><?= agritwin_h((string) $dbDiagnostics['database']) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('connection_state')) ?></span><strong><?= agritwin_h(agritwin_t((string) $dbDiagnostics['state'])) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('connection_reason')) ?></span><strong><?= agritwin_h(agritwin_t((string) $dbDiagnostics['reason'])) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('last_successful_connection')) ?></span><strong><?= agritwin_h((string) ($dbDiagnostics['last_successful_connection'] ?? agritwin_t('not_available'))) ?></strong></article>
        </div>
    </section>

    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('openagri')) ?></h2>
        <div class="service-grid">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <?= agritwin_badge($service['status']) ?>
                    <h3><?= agritwin_h($service['name']) ?></h3>
                    <p>Mode: <?= agritwin_h(agritwin_mode_label($service['mode'])) ?></p>
                    <p><?= agritwin_h(agritwin_t('endpoint')) ?>: <?= $service['configured'] ? '<a href="' . agritwin_h($service['endpoint']) . '">' . agritwin_h($service['endpoint']) . '</a>' : agritwin_h(agritwin_t('configuration_required')) ?></p>
                    <p><?= agritwin_h($service['error_summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('settings_parameters')) ?></h2>
            <span><?= agritwin_h(agritwin_t('configuration_managed')) ?></span>
        </div>
        <form class="settings-form" aria-label="<?= agritwin_h(agritwin_t('platform_settings')) ?>">
            <label><?= agritwin_h(agritwin_t('platform_mode')) ?>
                <select disabled><option><?= agritwin_h(agritwin_t('operational')) ?></option><option><?= agritwin_h(agritwin_t('live_service')) ?></option><option><?= agritwin_h(agritwin_t('auto')) ?></option></select>
            </label>
            <label><?= agritwin_h(agritwin_t('openagri_mode')) ?>
                <select disabled><option><?= agritwin_h(agritwin_mode_label(strtolower((string) agritwin_hmi_env('OPENAGRI_MODE', 'AGRITWIN_OPENAGRI_MODE', 'fallback')))) ?></option></select>
            </label>
            <label><?= agritwin_h(agritwin_t('weather_preference')) ?>
                <select disabled><option><?= agritwin_h((string) agritwin_hmi_env('AGRITWIN_WEATHER_SOURCE_PREFERENCE', null, 'auto')) ?></option></select>
            </label>
            <label><?= agritwin_h(agritwin_t('farm_location')) ?>
                <input type="text" value="Macotera, Salamanca, Castilla y Leon, Spain" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('latitude')) ?>
                <input type="text" value="<?= agritwin_h((string) $context['lat']) ?>" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('longitude')) ?>
                <input type="text" value="<?= agritwin_h((string) $context['lon']) ?>" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('refresh_interval')) ?>
                <input type="text" value="<?= agritwin_h(agritwin_t('fifteen_minutes')) ?>" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('health_interval')) ?>
                <input type="text" value="<?= agritwin_h(agritwin_t('five_minutes')) ?>" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('api_base_path')) ?>
                <input type="text" value="/agritwin3/" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('timezone')) ?>
                <input type="text" value="Europe/Madrid" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('data_retention')) ?>
                <input type="text" value="<?= agritwin_h(agritwin_t('sample_operational_station_data')) ?>" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('report_branding')) ?>
                <input type="text" value="AgriTwin, Fotovoltaica Macotera SL, OBSIDIAN" readonly>
            </label>
        </form>
        <h3><?= agritwin_h(agritwin_t('openagri')) ?></h3>
        <div class="settings-form">
            <?php foreach ($services as $service): ?>
                <label><?= agritwin_h($service['name']) ?> URL
                    <input value="<?= $service['configured'] ? agritwin_h($service['endpoint']) : agritwin_h(agritwin_t('configuration_required')) ?>" readonly>
                </label>
                <label><?= agritwin_h($service['name']) ?> <?= agritwin_h(agritwin_t('api_docs')) ?>
                    <input value="<?= agritwin_h($service['docs_url'] ?: $service['source_repository']) ?>" readonly>
                </label>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
});
