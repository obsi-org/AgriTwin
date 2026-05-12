<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

$context = agritwin_farm_context();
$services = agritwin_service_cards();
$weatherSource = agritwin_weather_source();
$dbOk = isset($pdo) && $pdo instanceof PDO;

agritwin_render_page('settings', 'Settings / Connectivity', function () use ($context, $services, $weatherSource, $dbOk): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2>AgriTwin local configuration</h2>
            <?= agritwin_badge($dbOk ? 'healthy' : 'unavailable') ?>
        </div>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span>Base path</span><strong><?= agritwin_h($context['base_path']) ?></strong></article>
            <article class="telemetry-card"><span>Data mode</span><strong><?= agritwin_h($context['data_mode']) ?></strong></article>
            <article class="telemetry-card"><span>DB status</span><strong><?= $dbOk ? 'Connected' : 'Unavailable' ?></strong></article>
            <article class="telemetry-card"><span>Farm context</span><strong>Fotovoltaica Macotera SL, Salamanca</strong></article>
            <article class="telemetry-card"><span>Weather source</span><strong><?= agritwin_h(agritwin_mode_label($weatherSource['mode'])) ?></strong></article>
            <article class="telemetry-card"><span>API documentation</span><strong>/agritwin3/api/openapi.json</strong></article>
        </div>
    </section>

    <section class="view-section">
        <h2>OpenAgri connector mode</h2>
        <div class="service-grid">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <?= agritwin_badge($service['status']) ?>
                    <h3><?= agritwin_h($service['name']) ?></h3>
                    <p>Mode: <?= agritwin_h(agritwin_mode_label($service['mode'])) ?></p>
                    <p>Endpoint: <?= agritwin_h($service['endpoint']) ?></p>
                    <p><?= agritwin_h($service['error_summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2>Environment-managed parameters</h2>
            <span>Configuration is environment-managed in this local deployment.</span>
        </div>
        <form class="settings-form" aria-label="AgriTwin deployment settings">
            <label>Platform mode
                <select disabled><option>demo</option><option>live</option><option>auto</option></select>
            </label>
            <label>OpenAgri mode
                <select disabled><option><?= agritwin_h(agritwin_mode_label(strtolower((string) agritwin_hmi_env('OPENAGRI_MODE', 'AGRITWIN_OPENAGRI_MODE', 'demo')))) ?></option></select>
            </label>
            <label>Weather source preference
                <select disabled><option><?= agritwin_h((string) agritwin_hmi_env('AGRITWIN_WEATHER_SOURCE_PREFERENCE', null, 'auto')) ?></option></select>
            </label>
            <label>Farm location
                <input type="text" value="Macotera, Salamanca, Castilla y Leon, Spain" readonly>
            </label>
            <label>Latitude
                <input type="text" value="<?= agritwin_h((string) $context['lat']) ?>" readonly>
            </label>
            <label>Longitude
                <input type="text" value="<?= agritwin_h((string) $context['lon']) ?>" readonly>
            </label>
            <label>Sensor station refresh interval
                <input type="text" value="15 minutes" readonly>
            </label>
            <label>OpenAgri health-check interval
                <input type="text" value="5 minutes" readonly>
            </label>
            <label>API base path
                <input type="text" value="/agritwin3/" readonly>
            </label>
            <label>Timezone
                <input type="text" value="Europe/Madrid" readonly>
            </label>
            <label>Data retention / demo-data mode
                <input type="text" value="Synthetic station data; runtime caches ignored by git" readonly>
            </label>
            <label>Report branding
                <input type="text" value="AgriTwin, Fotovoltaica Macotera SL, OBSIDIAN" readonly>
            </label>
        </form>
    </section>
    <?php
});
