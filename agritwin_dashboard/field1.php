<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/hmi_layout.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/db_config.php';

$cesiumToken = '';
try {
    $stmtTok = $pdo->prepare("SELECT value FROM app_settings WHERE name = 'cesium_token' LIMIT 1");
    $stmtTok->execute();
    $cesiumToken = (string) ($stmtTok->fetchColumn() ?: '');
} catch (Throwable $e) {
    $cesiumToken = '';
}

$context = agritwin_farm_context();
$stations = agritwin_sensor_stations();
$weatherSource = agritwin_weather_source();
$latestUpdate = $context['last_sync'];
$fieldStationPositions = [
    'MAC-ALM-A' => ['lat' => 40.8860, 'lon' => -5.2614],
    'MAC-ALM-B' => ['lat' => 40.8880, 'lon' => -5.2602],
    'MAC-ALM-C' => ['lat' => 40.8901, 'lon' => -5.2587],
    'MAC-PIST-A' => ['lat' => 40.8857, 'lon' => -5.2572],
    'MAC-MAIZE-A' => ['lat' => 40.8885, 'lon' => -5.2565],
];
$fieldConfig = [
    'tokenConfigured' => $cesiumToken !== '',
    'center' => ['lat' => 40.8880, 'lon' => -5.2589],
    'boundary' => [
        [-5.262770243294276, 40.883388135762168],
        [-5.255199073804725, 40.883388135762168],
        [-5.255199073804725, 40.892776720220887],
        [-5.262770243294276, 40.892776720220887],
        [-5.262770243294276, 40.883388135762168],
    ],
    'stations' => array_map(static fn (array $station): array => [
        'id' => $station['id'],
        'name' => $station['name'],
        'block' => $station['block'],
        'crop' => $station['crop'],
        'lat' => ($fieldStationPositions[$station['id']]['lat'] ?? $station['lat']),
        'lon' => ($fieldStationPositions[$station['id']]['lon'] ?? $station['lon']),
        'status' => agritwin_status_label($station['status']),
        'timestamp' => $station['timestamp'],
        'readings' => $station['readings'],
    ], $stations),
    'assets' => [
        'pivotModel' => 'result.gltf',
        'solarModel' => 'solar_panel/solar.gltf',
    ],
    'labels' => [
        'cesiumUnavailable' => agritwin_t('cesium_viewer_unavailable'),
        'configurationRequired' => agritwin_t('configuration_required'),
        'fieldBoundary' => agritwin_t('field_boundaries'),
        'trees' => agritwin_t('trees'),
        'pvPanels' => agritwin_t('pv_panels'),
        'sensorStations' => agritwin_t('sensor_stations_label'),
        'irrigationAssets' => agritwin_t('irrigation_assets'),
        'weatherLayer' => agritwin_t('weather_layer'),
        'pumpHouse' => agritwin_t('pump_house'),
        'irrigationLine' => agritwin_t('irrigation_line'),
        'almondBlockA' => agritwin_t('almond_block_a'),
        'almondBlockB' => agritwin_t('almond_block_b'),
        'almondBlockC' => agritwin_t('almond_block_c'),
        'pistachioBlockA' => agritwin_t('pistachio_block_a'),
        'maizeBlockA' => agritwin_t('maize_block_a'),
        'weatherMast' => agritwin_t('weather_mast'),
        'status' => agritwin_t('status'),
        'coordinates' => agritwin_t('coordinates'),
        'value' => agritwin_t('value'),
    ],
];

agritwin_render_page('digital_twin', agritwin_t('field_view'), function () use ($fieldConfig, $context, $stations, $weatherSource, $latestUpdate): void {
    ?>
    <link rel="stylesheet" href="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Widgets/widgets.css">
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Cesium.js"></script>

    <section class="view-section field-view-section">
        <div class="section-heading field-heading">
            <div>
                <p class="eyebrow">Fotovoltaica Macotera SL / Macotera / Salamanca</p>
                <h2><?= agritwin_h(agritwin_t('field_view')) ?></h2>
                <p><?= agritwin_h(agritwin_t('field_view_summary')) ?></p>
            </div>
            <div class="action-row">
                <a class="context-help-link" href="help.php#sensor-stations"><i class="fas fa-circle-question"></i> <?= agritwin_h(agritwin_t('see_help')) ?></a>
                <button id="fieldResetView" class="secondary-action" type="button"><i class="fas fa-location-crosshairs"></i> <?= agritwin_h(agritwin_t('reset_view')) ?></button>
            </div>
        </div>

        <div class="field-twin-shell">
            <div id="cesiumContainer" class="field-cesium-container" aria-label="<?= agritwin_h(agritwin_t('field_view')) ?>"></div>
            <div id="fieldCesiumStatus" class="field-cesium-status" hidden></div>

            <aside class="field-overlay-panel" aria-label="<?= agritwin_h(agritwin_t('digital_twin')) ?>">
                <strong><?= agritwin_h(agritwin_t('digital_twin_marker_title')) ?></strong>
                <span><?= agritwin_h($context['location']) ?></span>
                <dl>
                    <div><dt><?= agritwin_h(agritwin_t('active_sensor_stations')) ?></dt><dd><?= count($stations) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('last_telemetry_update')) ?></dt><dd><?= agritwin_h($latestUpdate) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('weather_layer')) ?></dt><dd><?= agritwin_h($weatherSource['label']) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('irrigation_status')) ?></dt><dd><?= agritwin_h(agritwin_t('operational')) ?></dd></div>
                </dl>
            </aside>

            <div class="field-layer-controls" aria-label="<?= agritwin_h(agritwin_t('digital_twin_controls')) ?>">
                <button type="button" class="field-layer-toggle active" data-layer="trees"><i class="fas fa-tree"></i> <?= agritwin_h(agritwin_t('trees')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="pv"><i class="fas fa-solar-panel"></i> <?= agritwin_h(agritwin_t('pv_panels')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="sensors"><i class="fas fa-tower-broadcast"></i> <?= agritwin_h(agritwin_t('sensor_stations_label')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="irrigation"><i class="fas fa-droplet"></i> <?= agritwin_h(agritwin_t('irrigation_assets')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="boundaries"><i class="fas fa-draw-polygon"></i> <?= agritwin_h(agritwin_t('field_boundaries')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="weather"><i class="fas fa-cloud-sun"></i> <?= agritwin_h(agritwin_t('weather_layer')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="labels"><i class="fas fa-tags"></i> <?= agritwin_h(agritwin_t('labels')) ?></button>
            </div>
        </div>
    </section>

    <script>
        window.agritwinFieldConfig = <?= json_encode($fieldConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/js/field1_cesium.js" defer></script>
    <?php
});
