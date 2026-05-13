<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

$context = agritwin_farm_context();
$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
$weatherKeyStatus = agritwin_weather_api_key_status();
$weatherDiagnostics = agritwin_weather_diagnostics();
$dbOk = isset($pdo) && $pdo instanceof PDO;
$dbDiagnostics = $agritwin_db_diagnostics ?? [
    'host' => 'not configured',
    'port' => 'not configured',
    'database' => 'not configured',
    'state' => 'unknown',
    'reason' => 'unknown',
    'last_successful_connection' => null,
];
$settingsMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weather_api_key_action'])) {
    if (!agritwin_role_allows('settings')) {
        http_response_code(403);
        $settingsMessage = agritwin_t('credentials_required');
    } else {
        agritwin_require_csrf();
        $action = (string) $_POST['weather_api_key_action'];
        if ($action === 'save') {
            $result = agritwin_save_weather_api_key(
                (string) ($_POST['weather_provider_api_key'] ?? ''),
                (string) ($_POST['weather_key_transport'] ?? 'bearer'),
                (string) ($_POST['weather_key_header_name'] ?? 'X-API-Key'),
                (string) ($_POST['weather_key_query_parameter_name'] ?? 'api_key')
            );
            $settingsMessage = $result['message'];
        } elseif ($action === 'validate') {
            $result = agritwin_validate_weather_api_key();
            $settingsMessage = $result['message'];
        } elseif ($action === 'remove') {
            $result = agritwin_remove_weather_api_key();
            $settingsMessage = $result['message'];
        }
        $weatherSource = agritwin_weather_source();
        $weatherKeyStatus = agritwin_weather_api_key_status();
        $weatherDiagnostics = agritwin_weather_diagnostics();
    }
}

agritwin_render_page('settings', agritwin_t('settings'), function () use ($context, $services, $weatherSource, $weatherKeyStatus, $weatherDiagnostics, $dbOk, $dbDiagnostics, $settingsMessage): void {
    $groups = agritwin_openagri_service_groups($services);
    $renderServiceCards = static function (array $group): void {
        ?>
        <div class="service-grid">
            <?php foreach ($group as $service): ?>
                <article class="service-card <?= $service['required_for_d1'] ? 'mandatory-service-card' : '' ?>">
                    <?= agritwin_badge($service['status']) ?>
                    <h3><?= agritwin_h($service['name']) ?></h3>
                    <p><strong><?= agritwin_h(agritwin_t('integration_role')) ?>:</strong> <?= agritwin_h($service['role']) ?></p>
                    <p><?= agritwin_h(agritwin_t('endpoint')) ?>: <?= $service['configured'] ? '<a href="' . agritwin_h($service['endpoint']) . '">' . agritwin_h($service['endpoint']) . '</a>' : agritwin_h(agritwin_t('configuration_required')) ?></p>
                    <p><?= agritwin_h($service['diagnostics'] ?? $service['error_summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    };
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('platform_settings')) ?></h2>
            <div><?= $settingsMessage !== '' ? '<span class="confidence-pill confidence-normal">' . agritwin_h($settingsMessage) . '</span>' : '' ?> <?= agritwin_badge($dbOk ? 'healthy' : 'unavailable') ?></div>
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
            <div>
                <p class="eyebrow"><?= agritwin_h(agritwin_t('openagri')) ?></p>
                <h2><?= agritwin_h(agritwin_t('weather_provider_api_key')) ?></h2>
            </div>
            <?= agritwin_badge($weatherKeyStatus['configured'] ? 'configured' : 'not_configured') ?>
        </div>
        <p class="source-note"><?= agritwin_h(agritwin_t('api_key_storage_notice')) ?> <?= agritwin_h(agritwin_t('untrusted_machine_warning')) ?></p>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('api_key_status')) ?></span><strong><?= agritwin_h($weatherKeyStatus['configured'] ? agritwin_t('api_key_configured') : agritwin_t('api_key_not_configured')) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('api_key_source')) ?></span><strong><?= agritwin_h(agritwin_t('api_key_source_' . $weatherKeyStatus['source'])) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('masked_value')) ?></span><strong><?= agritwin_h($weatherKeyStatus['masked'] ?? ($weatherKeyStatus['configured'] ? agritwin_t('configured') : agritwin_t('not_available'))) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('last_updated')) ?></span><strong><?= agritwin_h((string) ($weatherKeyStatus['last_updated_at'] ?? agritwin_t('not_available'))) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('last_validation')) ?></span><strong><?= agritwin_h((string) ($weatherKeyStatus['last_validated_at'] ?? agritwin_t('not_available'))) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('last_validation_result')) ?></span><strong><?= agritwin_h(agritwin_t((string) $weatherKeyStatus['last_validation_status'])) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('live_observations_available')) ?></span><strong><?= agritwin_h($weatherDiagnostics['live_observations_available'] ? agritwin_t('connected') : agritwin_t('unavailable')) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('connection_reason')) ?></span><strong><?= agritwin_h($weatherDiagnostics['reason']) ?></strong></article>
        </div>
        <?php if (agritwin_role_allows('settings')): ?>
            <form class="settings-form" method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= agritwin_h(agritwin_csrf_token()) ?>">
                <label><?= agritwin_h(agritwin_t('new_weather_provider_api_key')) ?>
                    <input id="weather-provider-api-key" name="weather_provider_api_key" type="password" autocomplete="new-password">
                </label>
                <label><?= agritwin_h(agritwin_t('provider_key_transport')) ?>
                    <select name="weather_key_transport">
                        <?php foreach (['bearer', 'header', 'query'] as $transport): ?>
                            <option value="<?= agritwin_h($transport) ?>" <?= $weatherKeyStatus['transport'] === $transport ? 'selected' : '' ?>><?= agritwin_h(agritwin_t('transport_' . $transport)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= agritwin_h(agritwin_t('header_name')) ?>
                    <input name="weather_key_header_name" value="<?= agritwin_h($weatherKeyStatus['header_name']) ?>">
                </label>
                <label><?= agritwin_h(agritwin_t('query_parameter_name')) ?>
                    <input name="weather_key_query_parameter_name" value="<?= agritwin_h($weatherKeyStatus['query_parameter_name']) ?>">
                </label>
                <div class="action-row">
                    <button class="secondary-action" type="button" onclick="const i=document.getElementById('weather-provider-api-key'); i.type = i.type === 'password' ? 'text' : 'password';"><?= agritwin_h(agritwin_t('show_hide_key')) ?></button>
                    <button class="primary-action" type="submit" name="weather_api_key_action" value="save"><?= agritwin_h(agritwin_t('save_api_key')) ?></button>
                    <button class="secondary-action" type="submit" name="weather_api_key_action" value="validate"><?= agritwin_h(agritwin_t('validate_api_key')) ?></button>
                    <button class="secondary-action danger-action" type="submit" name="weather_api_key_action" value="remove"><?= agritwin_h(agritwin_t('remove_api_key')) ?></button>
                </div>
            </form>
        <?php else: ?>
            <p><?= agritwin_h(agritwin_t('administrator_or_technical_manager_required')) ?></p>
        <?php endif; ?>
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
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h(agritwin_t('proposal_d1_compliance')) ?></p>
                <h2><?= agritwin_h(agritwin_t('mandatory_openagri_services')) ?></h2>
            </div>
            <?= agritwin_badge('normal') ?>
        </div>
        <p class="source-note"><?= agritwin_h(agritwin_t('mandatory_service_explanation')) ?></p>
        <?php $renderServiceCards($groups['mandatory']); ?>
    </section>

    <section class="view-section compact-section">
        <h2><?= agritwin_h(agritwin_t('operational_extensions')) ?></h2>
        <?php $renderServiceCards($groups['extensions']); ?>
    </section>

    <section class="view-section compact-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('optional_services')) ?></h2>
            <?= agritwin_badge('optional_not_configured') ?>
        </div>
        <p class="source-note"><?= agritwin_h(agritwin_t('optional_not_required_for_d1')) ?></p>
        <?php $renderServiceCards($groups['optional']); ?>
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
            <label><?= agritwin_h(agritwin_t('analytics_refresh_interval')) ?>
                <input type="text" value="<?= agritwin_h(agritwin_t('fifteen_minutes')) ?>" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('default_analytics_window')) ?>
                <input type="text" value="24 h / 7 d" readonly>
            </label>
            <label><?= agritwin_h(agritwin_t('chart_aggregation_interval')) ?>
                <input type="text" value="<?= agritwin_h(agritwin_t('fifteen_minutes')) ?>" readonly>
            </label>
        </form>
        <h3><?= agritwin_h(agritwin_t('mandatory_openagri_services')) ?></h3>
        <div class="settings-form">
            <?php foreach ($groups['mandatory'] as $service): ?>
                <label><?= agritwin_h($service['name']) ?> URL
                    <input value="<?= $service['configured'] ? agritwin_h($service['endpoint']) : agritwin_h(agritwin_t('configuration_required')) ?>" readonly>
                </label>
                <label><?= agritwin_h($service['name']) ?> <?= agritwin_h(agritwin_t('api_docs')) ?>
                    <input value="<?= agritwin_h($service['docs_url'] ?: $service['source_repository']) ?>" readonly>
                </label>
            <?php endforeach; ?>
        </div>
        <h3><?= agritwin_h(agritwin_t('operational_extensions')) ?></h3>
        <div class="settings-form">
            <?php foreach ($groups['extensions'] as $service): ?>
                <label><?= agritwin_h($service['name']) ?> URL
                    <input value="<?= $service['configured'] ? agritwin_h($service['endpoint']) : agritwin_h(agritwin_t('configuration_required')) ?>" readonly>
                </label>
                <label><?= agritwin_h($service['name']) ?> <?= agritwin_h(agritwin_t('api_docs')) ?>
                    <input value="<?= agritwin_h($service['docs_url'] ?: $service['source_repository']) ?>" readonly>
                </label>
            <?php endforeach; ?>
        </div>
        <h3><?= agritwin_h(agritwin_t('optional_services')) ?></h3>
        <div class="settings-form">
            <?php foreach ($groups['optional'] as $service): ?>
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
