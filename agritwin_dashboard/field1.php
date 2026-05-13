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

$copernicusId = '';
try {
    $stmtCop = $pdo->prepare("SELECT value FROM app_settings WHERE name = 'copernicus_id' LIMIT 1");
    $stmtCop->execute();
    $copernicusId = (string) ($stmtCop->fetchColumn() ?: '');
} catch (Throwable $e) {
    $copernicusId = '';
}

$context = agritwin_farm_context();
$stations = agritwin_sensor_stations();
$weatherSource = agritwin_weather_source();
$latestUpdate = $context['last_sync'];
$soilReadings = array_values(array_filter(agritwin_sensors(), static fn (array $sensor): bool => $sensor['parameter'] === 'soil_moisture'));
$avgSoilMoisture = count($soilReadings) > 0
    ? round(array_sum(array_map(static fn (array $sensor): float => (float) $sensor['value'], $soilReadings)) / count($soilReadings), 1)
    : null;
$fieldStationPositions = [
    'MAC-ALM-A' => ['lat' => 40.8860, 'lon' => -5.2614],
    'MAC-ALM-B' => ['lat' => 40.8880, 'lon' => -5.2602],
    'MAC-ALM-C' => ['lat' => 40.8901, 'lon' => -5.2587],
    'MAC-PIST-A' => ['lat' => 40.8857, 'lon' => -5.2572],
    'MAC-MAIZE-A' => ['lat' => 40.8885, 'lon' => -5.2565],
];
$fieldConfig = [
    'tokenConfigured' => $cesiumToken !== '',
    'copernicusConfigured' => $copernicusId !== '',
    'cesiumToken' => $cesiumToken,
    'copernicusId' => $copernicusId,
    'basePath' => '/agritwin3/agritwin_dashboard/',
    'assetBasePath' => '/agritwin3/agritwin_dashboard/',
    'center' => ['lat' => 40.8880, 'lon' => -5.2589],
    'bounds' => [
        'west' => -5.262770243294276,
        'south' => 40.883388135762168,
        'east' => -5.255199073804725,
        'north' => 40.892776720220887,
    ],
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
        'almondCsv' => 'almond_trees.csv',
        'pistachioCsv' => 'pistachio.csv',
        'almondTreeImage' => 'almond_tree.png',
        'pistachioTreeImage' => 'pistachio.png',
        'pivotModel' => 'result.gltf',
        'solarModel' => 'solar_panel/solar.gltf',
        'ndviLegend' => 'ndvi_legend_custom.png',
        'laiSaviLegend' => 'lai_savi_legend_custom.png',
    ],
    'sensors' => [
        ['name' => 'Sensor A', 'lat' => 40.8860, 'lon' => -5.2615, 'temp' => 28, 'moist10' => 22, 'moist30' => 24, 'moist50' => 26],
        ['name' => 'Sensor B', 'lat' => 40.8900, 'lon' => -5.2590, 'temp' => 27, 'moist10' => 23, 'moist30' => 25, 'moist50' => 27],
        ['name' => 'Sensor C', 'lat' => 40.8860, 'lon' => -5.2590, 'temp' => 27, 'moist10' => 23, 'moist30' => 25, 'moist50' => 27],
        ['name' => 'Sensor D', 'lat' => 40.8860, 'lon' => -5.2580, 'temp' => 27, 'moist10' => 23, 'moist30' => 25, 'moist50' => 27],
        ['name' => 'Sensor E', 'lat' => 40.8920, 'lon' => -5.2580, 'temp' => 26, 'moist10' => 23, 'moist30' => 25, 'moist50' => 28],
        ['name' => 'Sensor F', 'lat' => 40.8905, 'lon' => -5.2559, 'temp' => 27, 'moist10' => 23, 'moist30' => 25, 'moist50' => 27],
        ['name' => 'Sensor G', 'lat' => 40.8915, 'lon' => -5.2596, 'temp' => 24, 'moist10' => 23, 'moist30' => 25, 'moist50' => 24],
        ['name' => 'Sensor H', 'lat' => 40.8916, 'lon' => -5.2559, 'temp' => 27, 'moist10' => 23, 'moist30' => 25, 'moist50' => 27],
        ['name' => 'Sensor I', 'lat' => 40.8846, 'lon' => -5.2597, 'temp' => 28, 'moist10' => 22, 'moist30' => 24, 'moist50' => 26],
        ['name' => 'Sensor J', 'lat' => 40.8910, 'lon' => -5.2609, 'temp' => 28, 'moist10' => 22, 'moist30' => 24, 'moist50' => 28],
        ['name' => 'Sensor L', 'lat' => 40.8871, 'lon' => -5.2603, 'temp' => 28, 'moist10' => 22, 'moist30' => 24, 'moist50' => 28],
        ['name' => 'Sensor M', 'lat' => 40.8871, 'lon' => -5.2568, 'temp' => 28, 'moist10' => 22, 'moist30' => 24, 'moist50' => 28],
    ],
    'machinery' => [
        ['name' => 'Tractor 1', 'lat' => 40.8887, 'lon' => -5.2608, 'status' => agritwin_t('active')],
        ['name' => 'Harvester 1', 'lat' => 40.8875, 'lon' => -5.2612, 'status' => agritwin_t('idle')],
    ],
    'weatherStations' => [
        ['name' => agritwin_t('weather_station_1'), 'lat' => 40.8865, 'lon' => -5.2620, 'status' => agritwin_t('online'), 'temperature' => 25, 'humidity' => 55, 'windSpeed' => 5],
        ['name' => agritwin_t('weather_station_2'), 'lat' => 40.886275, 'lon' => -5.2579, 'status' => agritwin_t('online'), 'temperature' => 22, 'humidity' => 60, 'windSpeed' => 4, 'rainfall' => 1.0],
    ],
    'solarPanels' => [
        ['lon' => -5.25721844, 'lat' => 40.8918752, 'rotation' => -1.33],
        ['lon' => -5.25686481, 'lat' => 40.8919468, 'rotation' => -1.39],
        ['lon' => -5.25754584, 'lat' => 40.8918067, 'rotation' => -1.37],
        ['lon' => -5.25724743, 'lat' => 40.891806, 'rotation' => -1.38],
        ['lon' => -5.25653943, 'lat' => 40.8920817, 'rotation' => -1.32],
        ['lon' => -5.25651547, 'lat' => 40.892015, 'rotation' => -1.3],
        ['lon' => -5.25720005, 'lat' => 40.8919468, 'rotation' => -1.34],
        ['lon' => -5.25755804, 'lat' => 40.8918815, 'rotation' => -1.43],
        ['lon' => -5.25686189, 'lat' => 40.8920228, 'rotation' => -1.39],
        ['lon' => -5.25691331, 'lat' => 40.8918703, 'rotation' => -1.38],
        ['lon' => -5.25651768, 'lat' => 40.891946, 'rotation' => -1.34],
        ['lon' => -5.25756409, 'lat' => 40.8917434, 'rotation' => -1.38],
        ['lon' => -5.25755467, 'lat' => 40.8919547, 'rotation' => -1.29],
        ['lon' => -5.25719114, 'lat' => 40.8920366, 'rotation' => -1.29],
        ['lon' => -5.25682728, 'lat' => 40.8921143, 'rotation' => -1.29],
        ['lon' => -5.25649289, 'lat' => 40.892177, 'rotation' => -1.31],
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
        'temperature' => agritwin_t('temperature'),
        'soilMoisture10' => agritwin_t('soil_moisture') . ' (10 cm)',
        'soilMoisture30' => agritwin_t('soil_moisture') . ' (30 cm)',
        'soilMoisture50' => agritwin_t('soil_moisture') . ' (50 cm)',
        'humidity' => agritwin_t('humidity'),
        'windSpeed' => agritwin_t('wind_speed'),
        'rainfall' => agritwin_t('rainfall_today'),
        'assetUnavailable' => agritwin_t('asset_unavailable'),
        'copernicusRequired' => agritwin_t('copernicus_configuration_required'),
        'terrainUnavailable' => agritwin_t('terrain_unavailable'),
        'cesiumLoaded' => agritwin_t('cesium_loaded'),
        'viewerInitialised' => agritwin_t('viewer_initialised'),
        'terrainProvider' => agritwin_t('terrain_provider'),
        'cesiumTokenConfigured' => agritwin_t('cesium_token_configured'),
        'copernicusIdConfigured' => agritwin_t('copernicus_id_configured'),
        'entitiesLoaded' => agritwin_t('entities_loaded'),
        'failedAssets' => agritwin_t('failed_assets'),
        'ionWorldTerrain' => agritwin_t('ion_world_terrain'),
        'asyncWorldTerrain' => agritwin_t('async_world_terrain'),
        'ellipsoidFallback' => agritwin_t('ellipsoid_fallback'),
        'yes' => agritwin_t('yes'),
        'no' => agritwin_t('no'),
    ],
];

agritwin_render_page('digital_twin', agritwin_t('field_digital_twin'), function () use ($fieldConfig, $context, $stations, $weatherSource, $latestUpdate, $avgSoilMoisture): void {
    ?>
    <link rel="stylesheet" href="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Widgets/widgets.css">
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Cesium.js"></script>

    <section class="view-section field-view-section">
        <div class="section-heading field-heading">
            <div>
                <p class="eyebrow">Fotovoltaica Macotera SL / Macotera / Salamanca</p>
                <h2><?= agritwin_h(agritwin_t('field_digital_twin')) ?></h2>
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
            <div id="fieldLegend" class="field-legend" hidden>
                <strong id="fieldLegendTitle"><?= agritwin_h(agritwin_t('legend')) ?></strong>
                <img id="fieldLegendImage" alt="<?= agritwin_h(agritwin_t('legend')) ?>">
                <p id="fieldLegendFallback" hidden></p>
            </div>

            <aside class="field-overlay-panel" aria-label="<?= agritwin_h(agritwin_t('digital_twin')) ?>">
                <strong><?= agritwin_h(agritwin_t('digital_twin_marker_title')) ?></strong>
                <span><?= agritwin_h($context['location']) ?></span>
                <dl>
                    <div><dt><?= agritwin_h(agritwin_t('active_sensor_stations')) ?></dt><dd><?= count($stations) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('last_telemetry_update')) ?></dt><dd><?= agritwin_h($latestUpdate) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('openagri_weather_source')) ?></dt><dd><?= agritwin_h($weatherSource['label']) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('average_soil_moisture')) ?></dt><dd><?= $avgSoilMoisture !== null ? agritwin_h((string) $avgSoilMoisture) . ' % VWC' : agritwin_h(agritwin_t('not_available')) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('irrigation_status')) ?></dt><dd><?= agritwin_h(agritwin_t('operational')) ?></dd></div>
                    <div><dt><?= agritwin_h(agritwin_t('pest_disease_risk')) ?></dt><dd><?= agritwin_h(agritwin_t('reduced')) ?></dd></div>
                </dl>
                <div class="field-task-panel">
                    <strong><?= agritwin_h(agritwin_t('task_scheduling')) ?></strong>
                    <ul>
                        <li><?= agritwin_h(agritwin_t('field_task_irrigation_today')) ?></li>
                        <li><?= agritwin_h(agritwin_t('field_task_drone_ndvi')) ?></li>
                        <li><?= agritwin_h(agritwin_t('field_task_sensor_review')) ?></li>
                    </ul>
                    <a class="primary-action" href="index.php"><i class="fas fa-table-cells-large"></i> <?= agritwin_h(agritwin_t('back_to_dashboard')) ?></a>
                </div>
                <div class="field-runtime-panel">
                    <strong><?= agritwin_h(agritwin_t('field_diagnostics')) ?></strong>
                    <dl id="fieldRuntimeDiagnostics" class="field-runtime-diagnostics">
                        <div><dt><?= agritwin_h(agritwin_t('cesium_loaded')) ?></dt><dd><?= agritwin_h(agritwin_t('no')) ?></dd></div>
                        <div><dt><?= agritwin_h(agritwin_t('viewer_initialised')) ?></dt><dd><?= agritwin_h(agritwin_t('no')) ?></dd></div>
                        <div><dt><?= agritwin_h(agritwin_t('terrain_provider')) ?></dt><dd><?= agritwin_h(agritwin_t('terrain_unavailable')) ?></dd></div>
                    </dl>
                </div>
            </aside>

            <div class="field-layer-controls" aria-label="<?= agritwin_h(agritwin_t('digital_twin_controls')) ?>">
                <button type="button" class="field-layer-toggle active" data-layer="sensors"><i class="fas fa-tower-broadcast"></i> <?= agritwin_h(agritwin_t('sensors')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="machinery"><i class="fas fa-tractor"></i> <?= agritwin_h(agritwin_t('machinery')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="weather"><i class="fas fa-cloud-sun"></i> <?= agritwin_h(agritwin_t('weather_stations')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="almondTrees"><i class="fas fa-tree"></i> <?= agritwin_h(agritwin_t('almond_trees')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="pistachioTrees"><i class="fas fa-tree"></i> <?= agritwin_h(agritwin_t('pistachio_trees')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="solarPanels"><i class="fas fa-solar-panel"></i> <?= agritwin_h(agritwin_t('solar_panels')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="pivot"><i class="fas fa-circle-nodes"></i> <?= agritwin_h(agritwin_t('irrigation_pivot')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="boundaries"><i class="fas fa-draw-polygon"></i> <?= agritwin_h(agritwin_t('field_boundaries')) ?></button>
                <button type="button" class="field-layer-toggle" data-layer="ndvi"><i class="fas fa-layer-group"></i> NDVI</button>
                <button type="button" class="field-layer-toggle" data-layer="laiSavi"><i class="fas fa-layer-group"></i> <?= agritwin_h(agritwin_t('lai_savi')) ?></button>
                <button type="button" class="field-layer-toggle" data-layer="nasaNdvi"><i class="fas fa-earth-europe"></i> <?= agritwin_h(agritwin_t('nasa_ndvi')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="labels"><i class="fas fa-tags"></i> <?= agritwin_h(agritwin_t('labels')) ?></button>
                <button type="button" class="field-layer-toggle active" data-layer="infoPanel"><i class="fas fa-table-columns"></i> <?= agritwin_h(agritwin_t('field_information_panel')) ?></button>
            </div>
        </div>
    </section>

    <script>
        window.agritwinFieldConfig = <?= json_encode($fieldConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/js/field1_cesium.js" defer></script>
    <?php
});
