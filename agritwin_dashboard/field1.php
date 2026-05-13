<?php
session_start();

// If user is not logged in, redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Include DB connection
require_once "db_config.php";
require_once __DIR__ . "/includes/field_data_provider.php";

// The Spanish farm is farm_id=1
$spainFarmId = 1;

$supportedLangs = ['en', 'pt-PT', 'es-ES'];
$fieldLang = $_GET['lang'] ?? ($_SESSION['preferred_language'] ?? 'en');
if (!in_array($fieldLang, $supportedLangs, true)) {
    $fieldLang = 'en';
}

$fieldText = [
    'en' => [
        'page_title' => 'AgriTwin Field Digital Twin',
        'field_monitoring' => 'Fotovoltaica Macotera SL - Field Monitoring',
        'local_weather_station' => 'Local Weather Station - Nava de Sotrobal',
        'operational_insights' => 'Operational Insights - Fotovoltaica Macotera SL',
        'task_scheduling' => 'Task Scheduling - Fotovoltaica Macotera SL',
        'temperature' => 'Temperature',
        'humidity' => 'Humidity',
        'wind_speed' => 'Wind speed',
        'soil_moisture' => 'Soil moisture',
        'alert' => 'Alert',
        'targeted_actions' => 'Targeted actions for orchard sectors',
        'aemet_forecast' => 'AEMET Weather Forecast - Nava de Sotrobal',
        'loading_forecast' => 'Loading forecast...',
        'error_loading_forecast' => 'Error loading forecast.',
        'no_forecast_days' => 'No forecast day data found.',
        'no_valid_forecast' => 'No valid forecast data available.',
        'select_index' => 'Select Index',
        'none' => 'None',
        'default_imagery_only' => 'Default imagery only',
        'legend' => 'Legend',
        'index_legend' => 'Index legend',
        'toggle_sidebar' => 'Toggle side panel (R)',
        'back_dashboard' => 'Back to AgriTwin Dashboard',
        'today' => 'Today',
        'tomorrow' => 'Tomorrow',
        'rain_probability' => 'Rain probability',
        'rainfall_today' => 'Rainfall today',
        'weather_source' => 'Weather source',
        'last_updated' => 'Last updated',
        'wind' => 'Wind',
        'status' => 'Status',
        'coordinates' => 'Coordinates',
        'active' => 'Active',
        'idle' => 'Idle',
        'online' => 'Online',
        'orchard_context' => 'Almond and pistachio orchard',
        'insight_line_1' => 'Soil moisture and weather conditions are evaluated from the current field dataset.',
        'insight_line_2' => 'Wind, rainfall and humidity are reviewed for irrigation and pest monitoring decisions.',
        'field_action_1' => 'Almond Block A-3 - Trigger the micro-sprinkler set for a 22 mm recharge before 18:30.',
        'field_action_2' => 'Pistachio Block P-1 (Kerman) - Schedule an evening wettable-sulphur spray to curb mite pressure favoured by today’s warmth.',
        'field_action_3' => 'Almond Buffer Row B-7 - Inspect for early navel-orangeworm flight; deploy pheromone traps if more than 2 moths/night are captured.',
        'field_task_1' => 'Inspect soil-moisture sensors today.',
        'field_task_2' => 'Verify irrigation line pressure before the next watering window.',
        'field_task_3' => 'Review almond block irrigation schedule tomorrow.',
        'field_task_4' => 'Check pest and disease monitoring traps within the next 48 hours.',
    ],
    'pt-PT' => [
        'page_title' => 'Gémeo Digital de Campo AgriTwin',
        'field_monitoring' => 'Fotovoltaica Macotera SL - Monitorização de Campo',
        'local_weather_station' => 'Estação meteorológica local - Nava de Sotrobal',
        'operational_insights' => 'Informação operacional - Fotovoltaica Macotera SL',
        'task_scheduling' => 'Planeamento de tarefas - Fotovoltaica Macotera SL',
        'temperature' => 'Temperatura',
        'humidity' => 'Humidade',
        'wind_speed' => 'Velocidade do vento',
        'soil_moisture' => 'Humidade do solo',
        'alert' => 'Alerta',
        'targeted_actions' => 'Ações direcionadas para setores do pomar',
        'aemet_forecast' => 'Previsão meteorológica AEMET - Nava de Sotrobal',
        'loading_forecast' => 'A carregar previsão...',
        'error_loading_forecast' => 'Erro ao carregar a previsão.',
        'no_forecast_days' => 'Não foram encontrados dados diários de previsão.',
        'no_valid_forecast' => 'Não existem dados de previsão válidos disponíveis.',
        'select_index' => 'Selecionar índice',
        'none' => 'Nenhum',
        'default_imagery_only' => 'Apenas imagem base',
        'legend' => 'Legenda',
        'index_legend' => 'Legenda do índice',
        'toggle_sidebar' => 'Alternar painel lateral (R)',
        'back_dashboard' => 'Voltar ao painel de controlo AgriTwin',
        'today' => 'Hoje',
        'tomorrow' => 'Amanhã',
        'rain_probability' => 'Probabilidade de chuva',
        'rainfall_today' => 'Chuva hoje',
        'weather_source' => 'Fonte meteorológica',
        'last_updated' => 'Última atualização',
        'wind' => 'Vento',
        'status' => 'Estado',
        'coordinates' => 'Coordenadas',
        'active' => 'Ativo',
        'idle' => 'Inativo',
        'online' => 'Online',
        'orchard_context' => 'Pomar de amendoeiras e pistácios',
        'insight_line_1' => 'A humidade do solo e as condições meteorológicas são avaliadas a partir do conjunto de dados atual do campo.',
        'insight_line_2' => 'Vento, chuva e humidade são revistos para decisões de rega e monitorização fitossanitária.',
        'field_action_1' => 'Bloco de amendoeiras A-3 - Acionar o conjunto de microaspersores para uma recarga de 22 mm antes das 18:30.',
        'field_action_2' => 'Bloco de pistácios P-1 (Kerman) - Planear uma aplicação noturna de enxofre molhável para reduzir a pressão de ácaros favorecida pelo calor de hoje.',
        'field_action_3' => 'Linha tampão de amendoeiras B-7 - Inspecionar o voo inicial de traça-da-amendoeira; instalar armadilhas de feromona se forem capturadas mais de 2 traças/noite.',
        'field_task_1' => 'Inspecionar os sensores de humidade do solo hoje.',
        'field_task_2' => 'Verificar a pressão da linha de rega antes da próxima janela de rega.',
        'field_task_3' => 'Rever amanhã o plano de rega dos blocos de amendoeiras.',
        'field_task_4' => 'Verificar as armadilhas de monitorização de pragas e doenças nas próximas 48 horas.',
    ],
    'es-ES' => [
        'page_title' => 'Gemelo digital de campo AgriTwin',
        'field_monitoring' => 'Fotovoltaica Macotera SL - Monitorización de campo',
        'local_weather_station' => 'Estación meteorológica local - Nava de Sotrobal',
        'operational_insights' => 'Información operativa - Fotovoltaica Macotera SL',
        'task_scheduling' => 'Planificación de tareas - Fotovoltaica Macotera SL',
        'temperature' => 'Temperatura',
        'humidity' => 'Humedad',
        'wind_speed' => 'Velocidad del viento',
        'soil_moisture' => 'Humedad del suelo',
        'alert' => 'Alerta',
        'targeted_actions' => 'Acciones dirigidas para sectores del huerto',
        'aemet_forecast' => 'Previsión meteorológica AEMET - Nava de Sotrobal',
        'loading_forecast' => 'Cargando previsión...',
        'error_loading_forecast' => 'Error al cargar la previsión.',
        'no_forecast_days' => 'No se han encontrado datos diarios de previsión.',
        'no_valid_forecast' => 'No hay datos de previsión válidos disponibles.',
        'select_index' => 'Seleccionar índice',
        'none' => 'Ninguno',
        'default_imagery_only' => 'Solo imagen base',
        'legend' => 'Leyenda',
        'index_legend' => 'Leyenda del índice',
        'toggle_sidebar' => 'Alternar panel lateral (R)',
        'back_dashboard' => 'Volver al panel de control AgriTwin',
        'today' => 'Hoy',
        'tomorrow' => 'Mañana',
        'rain_probability' => 'Probabilidad de lluvia',
        'rainfall_today' => 'Lluvia hoy',
        'weather_source' => 'Fuente meteorológica',
        'last_updated' => 'Última actualización',
        'wind' => 'Viento',
        'status' => 'Estado',
        'coordinates' => 'Coordenadas',
        'active' => 'Activo',
        'idle' => 'Inactivo',
        'online' => 'Online',
        'orchard_context' => 'Huerto de almendros y pistachos',
        'insight_line_1' => 'La humedad del suelo y las condiciones meteorológicas se evalúan a partir del conjunto de datos actual del campo.',
        'insight_line_2' => 'El viento, la lluvia y la humedad se revisan para decisiones de riego y seguimiento fitosanitario.',
        'field_action_1' => 'Bloque de almendros A-3 - Activar el conjunto de microaspersores para una recarga de 22 mm antes de las 18:30.',
        'field_action_2' => 'Bloque de pistachos P-1 (Kerman) - Programar una aplicación vespertina de azufre mojable para contener la presión de ácaros favorecida por el calor de hoy.',
        'field_action_3' => 'Fila tampón de almendros B-7 - Inspeccionar el vuelo inicial de polilla del almendro; desplegar trampas de feromonas si se capturan más de 2 polillas/noche.',
        'field_task_1' => 'Inspeccionar hoy los sensores de humedad del suelo.',
        'field_task_2' => 'Verificar la presión de la línea de riego antes de la próxima ventana de riego.',
        'field_task_3' => 'Revisar mañana la programación de riego de los bloques de almendros.',
        'field_task_4' => 'Comprobar las trampas de seguimiento de plagas y enfermedades en las próximas 48 horas.',
    ],
];

$t = static fn (string $key): string => $fieldText[$fieldLang][$key] ?? $fieldText['en'][$key] ?? $key;
$fieldData = agritwin_field_current_data($pdo);
$fieldWeather = $fieldData['local_weather_station'];


// 2a) Cesium token (global setting) ---------------------------------------
try {
    $stmtTok = $pdo->prepare("SELECT value FROM app_settings WHERE name = 'cesium_token' LIMIT 1");
    $stmtTok->execute();
    $cesiumToken = $stmtTok->fetchColumn() ?: '';
} catch (Exception $e) {
    $cesiumToken = '';
    /* In production, log error */
}

/* Copernicus ID (global setting) --------------------------------------- */
try {
    $stmtCop = $pdo->prepare(
        "SELECT value FROM app_settings WHERE name = 'copernicus_id' LIMIT 1"
    );
    $stmtCop->execute();
    $copernicusId = $stmtCop->fetchColumn() ?: '';
} catch (Exception $e) {
    $copernicusId = '';
    // Optionally: error_log($e->getMessage());
}

// Operational data defaults used when the latest database row is unavailable.
$dbSoilMoisture = "N/A";
$dbTemperature  = "N/A";
$dbHumidity     = "N/A";

// 1) Fetch the latest measurement from DB
try {
    $sql = "
        SELECT soil_moisture, temperature, forecast
        -- add humidity column if you store it separately
        FROM farm_measurements
        WHERE farm_id = :fid
        ORDER BY reading_time DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['fid' => $spainFarmId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $dbSoilMoisture = $row['soil_moisture'] ?? "N/A"; // e.g. "42%"
        $dbTemperature  = $row['temperature']  ?? "N/A"; // e.g. "10°C"
        // If you have a separate humidity column, map it here
        // $dbHumidity     = $row['humidity'] ?? "N/A";
        $dbHumidity = "45%"; // fallback
    }
} catch (Exception $e) {
    // In production, handle/log the error
    // echo "DB error: " . $e->getMessage();
}

// Everything else remains your original code structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($t('page_title'), ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Cesium JS + CSS -->
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Cesium.js"></script>
    <link
      href="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Widgets/widgets.css"
      rel="stylesheet"
    />

    <!-- Font Awesome for icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />

    <style>
        /* Overall layout and styling */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #1b5e20, #2e7d32);
            color: white;
            overflow: hidden; /* Because Cesium is taking up the main area */
            display: flex;    /* So #cesiumContainer can flex if needed */
        }

        #cesiumContainer {
            flex: 1;
            height: 100vh;
            width: 100%;
            position: relative;
        }

        /* Fix the right-hand sidebar so it is always fully visible and scrollable */
        #sidebar {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0; /* Ensure the sidebar extends to the bottom of the viewport */
            width: 300px;
            background: rgba(0, 51, 0, 0.8);
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        h2 {
            margin-top: 0;
            font-size: 1.5em;
        }

        .sensor-data, .task-list {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        #logoContainer {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
        }
        #logoContainer img {
            height: 40px;
        }

        /* Move Cesium toolbar to top-left */
        .cesium-viewer-toolbar {
            position: absolute;
            top: 10px;
            left: 200px;
            right: auto !important;
        }

        /* Button for AgriTwin Dashboard */
        .dashboard-button {
            margin-top: 10px;
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px 0;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .dashboard-button:hover {
            background-color: #45a049;
        }

        /* Panel for NDVI / LAI_SAVI toggles */
        .layerTogglePanel {
            position: absolute;
            top: 10px;
            left: 450px;
            background: rgba(42, 42, 42, 0.8);
            padding: 8px;
            border-radius: 4px;
            color: #fff;
            font-family: sans-serif;
            z-index: 1100;
        }

        /* Legend container for NDVI/LAI_SAVI */
        #legendContainer {
            position: absolute;
            bottom: 40px;
            right: 310px;
            background: rgba(42, 42, 42, 0.8);
            padding: 8px;
            border-radius: 4px;
            display: none;
            z-index: 1100;
            width: 230px;
        }
        #legendTitle {
            text-align: center;
            font-weight: bold;
            margin-bottom: 6px;
            color: #fff;
            font-family: sans-serif;
        }
        #legendImage {
            display: block;
            width: 200px;
            margin: 0 auto;
        }

        /* InfoBox and selection indicator repositioning */
        .cesium-viewer-infoBoxContainer {
            left: 10px !important;
            top: 100px !important;
            right: auto !important;
            width: 370px !important;
            max-height: 400px !important;
            overflow-y: auto;
        }
        .cesium-viewer-selectionIndicatorContainer {
            left: 10px !important;
            top: 60px !important;
            right: auto !important;
        }

        /* Button to toggle sidebar */
        #toggleSidebarBtn {
            position: absolute;
            top: 10px;
            right: 320px;
            z-index: 1200;
            background-color: #5d5d5d;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        #toggleSidebarBtn:hover {
            background-color: #4b4b4b;
        }
    </style>
</head>
<body>

<!-- Main Cesium container -->
<div id="cesiumContainer">
    <div id="logoContainer">
        <img src="AgriTwin_logo.png" alt="AgriTwin logo">
        <img src="obsidian.png" alt="OBSIDIAN logo">
        <img src="fotomaco.png" alt="Fotovoltaica Macotera SL logo">
        <img src="OpenAgri.png" alt="OpenAgri logo">
        <img src="eufunded.png" alt="EU Funded">
    </div>
</div>

<!-- Button to toggle the right sidebar -->
<button id="toggleSidebarBtn"><?= htmlspecialchars($t('toggle_sidebar'), ENT_QUOTES, 'UTF-8') ?></button>

<!-- Right sidebar for sensor data, AI insights, tasks, button -->
<div id="sidebar">
    <h2><?= htmlspecialchars($t('field_monitoring'), ENT_QUOTES, 'UTF-8') ?></h2>

<div class="sensor-data">
  <h3><?= htmlspecialchars($t('local_weather_station'), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) $fieldWeather['last_updated_label'], ENT_QUOTES, 'UTF-8') ?>&nbsp;<?= htmlspecialchars($t('today'), ENT_QUOTES, 'UTF-8') ?></h3>

  <p><strong><?= htmlspecialchars($t('temperature'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $fieldWeather['temperature_c'], ENT_QUOTES, 'UTF-8') ?> °C</p>
  <p><strong><?= htmlspecialchars($t('humidity'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $fieldWeather['humidity_pct'], ENT_QUOTES, 'UTF-8') ?> %</p>
  <p><strong><?= htmlspecialchars($t('wind_speed'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $fieldWeather['wind_kmh'], ENT_QUOTES, 'UTF-8') ?> km h<sup>-1</sup> (<?= htmlspecialchars((string) $fieldWeather['wind_direction'], ENT_QUOTES, 'UTF-8') ?>)</p>
  <p><strong><?= htmlspecialchars($t('rainfall_today'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $fieldWeather['rainfall_mm'], ENT_QUOTES, 'UTF-8') ?> mm</p>
  <p><strong><?= htmlspecialchars($t('weather_source'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $fieldWeather['source_label'], ENT_QUOTES, 'UTF-8') ?></p>
  <p><strong><?= htmlspecialchars($t('last_updated'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $fieldWeather['timestamp'], ENT_QUOTES, 'UTF-8') ?></p>
</div>

<div class="sensor-data">
  <h3><?= htmlspecialchars($t('operational_insights'), ENT_QUOTES, 'UTF-8') ?> - Nava de Sotrobal • <?= htmlspecialchars($t('orchard_context'), ENT_QUOTES, 'UTF-8') ?></h3>

  <p><strong><?= htmlspecialchars($t('alert'), ENT_QUOTES, 'UTF-8') ?>:</strong>
    <?php foreach ($fieldData['operational_insights'] as $insight): ?>
      • <?= htmlspecialchars((string) $insight, ENT_QUOTES, 'UTF-8') ?><br/>
    <?php endforeach; ?>
  </p>

  <p><strong><?= htmlspecialchars($t('targeted_actions'), ENT_QUOTES, 'UTF-8') ?></strong></p>
  <ul>
    <li><?= htmlspecialchars((string) $fieldData['irrigation_status'], ENT_QUOTES, 'UTF-8') ?></li>
    <li><?= htmlspecialchars((string) $fieldData['pest_disease_status'], ENT_QUOTES, 'UTF-8') ?></li>
  </ul>
</div>

<!-- ---------- Property work queue ---------- -->
<!-- ---------- Property work queue ---------- -->
<div class="task-list">
  <h3><?= htmlspecialchars($t('task_scheduling'), ENT_QUOTES, 'UTF-8') ?></h3>
  <ul>
    <?php foreach ($fieldData['task_queue'] as $task): ?>
      <li>• <?= htmlspecialchars((string) $task, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
</div>

    <!-- New DIV: AEMET Weather Forecast -->
    <div class="sensor-data">
        <h3><?= htmlspecialchars($t('aemet_forecast'), ENT_QUOTES, 'UTF-8') ?></h3>
        <div id="aemetForecast"><?= htmlspecialchars($t('loading_forecast'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <!-- End of new weather forecast block -->

    <!-- Button for navigating back to AgriTwin Dashboard -->
    <button class="dashboard-button" onclick="window.location.href='index.php'">
        <?= htmlspecialchars($t('back_dashboard'), ENT_QUOTES, 'UTF-8') ?>
    </button>
</div>

<!-- Panel for NDVI / LAI_SAVI toggles via radio buttons -->
<div class="layerTogglePanel">
    <p><strong><?= htmlspecialchars($t('select_index'), ENT_QUOTES, 'UTF-8') ?></strong></p>
    <label>
      <input type="radio" name="indexLayer" value="none" checked/>
      <?= htmlspecialchars($t('none'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($t('default_imagery_only'), ENT_QUOTES, 'UTF-8') ?>)
    </label>
    <br>
    <label>
      <input type="radio" name="indexLayer" value="NDVI"/>
      NDVI (Sentinel Hub)
    </label>
    <br>
    <label>
      <input type="radio" name="indexLayer" value="LAI_SAVI"/>
      LAI_SAVI (Sentinel Hub)
    </label>
</div>

<!-- Legend container for Sentinel NDVI/LAI_SAVI -->
<div id="legendContainer">
    <div id="legendTitle"><?= htmlspecialchars($t('legend'), ENT_QUOTES, 'UTF-8') ?></div>
    <img id="legendImage" src="" alt="<?= htmlspecialchars($t('index_legend'), ENT_QUOTES, 'UTF-8') ?>" />
</div>


<!-------------------------------------------------------------------------->
<!-- SINGLE, VALID <script> SECTION  (no PHP inside JS!) ------------------->
<script>
const FIELD_TEXT = <?= json_encode($fieldText[$fieldLang], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const FIELD_DATA = <?= json_encode($fieldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
// -------------- 1) Cesium initialisation ---------------------------------
const CESIUM_TOKEN = <?= json_encode($cesiumToken) ?>;
Cesium.Ion.defaultAccessToken = CESIUM_TOKEN;

const viewer = new Cesium.Viewer('cesiumContainer', {
  terrainProvider: Cesium.createWorldTerrain()
});

    // Inject Font Awesome into the InfoBox iFrame so icons appear
    viewer.infoBox.frame.addEventListener("load", function() {
      var cssLink = viewer.infoBox.frame.contentDocument.createElement("link");
      cssLink.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css";
      cssLink.rel = "stylesheet";
      viewer.infoBox.frame.contentDocument.head.appendChild(cssLink);
    });

    /*********************************************
     * 2) NASA GIBS NDVI (toggle via 'n')
     *********************************************/
    var oldNdviProvider = new Cesium.WebMapServiceImageryProvider({
        url: 'https://gibs.earthdata.nasa.gov/wms/epsg3857/best/wms.cgi',
        layers: 'MODIS_Terra_NDVI_8Day',
        parameters: {
            service: 'WMS',
            version: '1.3.0',
            request: 'GetMap',
            format: 'image/png',
            transparent: 'true',
            styles: '',
            crs: 'EPSG:3857'
        }
    });
    var oldNdviLayer = viewer.imageryLayers.addImageryProvider(oldNdviProvider);
    oldNdviLayer.show = false; // hidden initially

    document.addEventListener("keydown", function(event) {
        if (event.key === 'n') {
            oldNdviLayer.show = !oldNdviLayer.show;
        }
    });

    /*********************************************
     * 3) Sentinel Hub NDVI / LAI_SAVI + bounding
     *********************************************/
    var boundingRectangle = Cesium.Rectangle.fromDegrees(
        -5.262770243294276,  // West
        40.883388135762168,  // South
        -5.255199073804725,  // East
        40.892776720220887   // North
    );
    var initialFieldView = {
      longitude: -5.259723,
      latitude: 40.883642,
      height: 950,
      heading: 0,
      pitch: -55,
      roll: 0
    };
    viewer.camera.flyTo({
      destination: Cesium.Cartesian3.fromDegrees(
        initialFieldView.longitude,
        initialFieldView.latitude,
        initialFieldView.height
      ),
      orientation: {
        heading: Cesium.Math.toRadians(initialFieldView.heading),
        pitch: Cesium.Math.toRadians(initialFieldView.pitch),
        roll: initialFieldView.roll
      },
      duration: 2
    });

const COPERNICUS_ID = <?= json_encode($copernicusId) ?>;
const sentinelWmsUrl = `https://sh.dataspace.copernicus.eu/ogc/wms/${COPERNICUS_ID}?`;


    var wmsOptions = {
      url: sentinelWmsUrl,
      parameters: {
        service: "WMS",
        version: "1.3.0",
        request: "GetMap",
        format: "image/png",
        transparent: "true",
        CRS: "EPSG:3857"
      },
      rectangle: boundingRectangle,
      tilingScheme: new Cesium.WebMercatorTilingScheme(),
      enablePickFeatures: false,
      maximumLevel: 14,
      credit: "Sentinel Hub / Copernicus"
    };

    var newNdviProvider = new Cesium.WebMapServiceImageryProvider({
      ...wmsOptions,
      layers: "NDVI"
    });
    var newNdviLayer = viewer.imageryLayers.addImageryProvider(newNdviProvider);
    newNdviLayer.show = false;

    var laiSaviProvider = new Cesium.WebMapServiceImageryProvider({
      ...wmsOptions,
      layers: "LAI_SAVI"
    });
    var laiSaviLayer = viewer.imageryLayers.addImageryProvider(laiSaviProvider);
    laiSaviLayer.show = false;

    // Custom legend images
    var legendUrls = {
      NDVI: "ndvi_legend_custom.png",
      LAI_SAVI: "lai_savi_legend_custom.png"
    };

    var radios = document.querySelectorAll('input[name="indexLayer"]');
    var legendContainer = document.getElementById("legendContainer");
    var legendImage = document.getElementById("legendImage");
    var legendTitle = document.getElementById("legendTitle");

    radios.forEach(rb => {
      rb.addEventListener("change", () => {
        newNdviLayer.show = false;
        laiSaviLayer.show = false;

        if (rb.value === "none") {
          legendContainer.style.display = "none";
          return;
        }
        if (rb.value === "NDVI") {
          newNdviLayer.show = true;
        } else if (rb.value === "LAI_SAVI") {
          laiSaviLayer.show = true;
        }
        legendContainer.style.display = "block";
        legendTitle.textContent = rb.value;
        legendImage.src = legendUrls[rb.value] || "";
      });
    });

    /*********************************************
     * 4) Spanish property boundary
     *********************************************/
    var boundaryCoordinates = [
      Cesium.Cartesian3.fromDegrees(-5.262770243294276, 40.883388135762168),
      Cesium.Cartesian3.fromDegrees(-5.255199073804725, 40.883388135762168),
      Cesium.Cartesian3.fromDegrees(-5.255199073804725, 40.892776720220887),
      Cesium.Cartesian3.fromDegrees(-5.262770243294276, 40.892776720220887),
      Cesium.Cartesian3.fromDegrees(-5.262770243294276, 40.883388135762168)
    ];
    viewer.entities.add({
        name: "Property Boundary",
        polyline: {
            positions: boundaryCoordinates,
            width: 4,
            material: Cesium.Color.RED,
            clampToGround: true
        }
    });

    /*********************************************
     * 5) Multiple pins (sensors, machinery, weather)
     *********************************************/
    var SENSOR_ICON_URL   = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
    var MACHINE_ICON_URL  = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
    var WEATHER_ICON_URL  = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
    var SELECTED_ICON_URL = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";

    var sensorsData = FIELD_DATA.sensor_markers || [];

    var machineryData = [
      { name: "Tractor 1",   lat: 40.8887, lon: -5.2608, status: FIELD_TEXT.active },
      { name: "Harvester 1", lat: 40.8875, lon: -5.2612, status: FIELD_TEXT.idle   }
    ];

    var weatherStationsData = FIELD_DATA.weather_stations || [];

    function buildSensorDescription(sensor) {
      return `
        <div>
          <i class="fa-solid fa-microchip" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${sensor.name}</strong><br/>
          ${FIELD_TEXT.temperature}: ${sensor.temp}°C<br/>
          ${FIELD_TEXT.soil_moisture} (10 cm): ${sensor.moist10}%<br/>
          ${FIELD_TEXT.soil_moisture} (30 cm): ${sensor.moist30}%<br/>
          ${FIELD_TEXT.soil_moisture} (50 cm): ${sensor.moist50}%<br/><br/>
          <em>${FIELD_TEXT.coordinates}:</em> ${sensor.lat.toFixed(5)}, ${sensor.lon.toFixed(5)}
        </div>
      `;
    }
    function buildMachineryDescription(mach) {
      return `
        <div>
          <i class="fa-solid fa-tractor" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${mach.name}</strong><br/>
          ${FIELD_TEXT.status}: ${mach.status}<br/><br/>
          <em>${FIELD_TEXT.coordinates}:</em> ${mach.lat.toFixed(5)}, ${mach.lon.toFixed(5)}
        </div>
      `;
    }
    function buildWeatherDescription(ws) {
      let rainfallLine = ws.rainfall !== undefined ? `${FIELD_TEXT.rainfall_today}: ${ws.rainfall} mm<br/>` : "";
      let windDirection = ws.windDirection ? ` (${ws.windDirection})` : "";
      return `
        <div>
          <i class="fa-solid fa-cloud-sun" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${ws.name}</strong><br/>
          ${FIELD_TEXT.status}: ${ws.status}<br/>
          ${FIELD_TEXT.temperature}: ${ws.temperature}°C<br/>
          ${FIELD_TEXT.humidity}: ${ws.humidity}%<br/>
          ${FIELD_TEXT.wind_speed}: ${ws.windSpeed} km h⁻¹${windDirection}<br/>
          ${rainfallLine}<br/>
          <em>${FIELD_TEXT.coordinates}:</em> ${ws.lat.toFixed(5)}, ${ws.lon.toFixed(5)}
        </div>
      `;
    }

    var sensorEntities = [];
    sensorsData.forEach(function(sensor) {
      var entity = viewer.entities.add({
        name: sensor.name,
        position: Cesium.Cartesian3.fromDegrees(sensor.lon, sensor.lat),
        billboard: {
          image: SENSOR_ICON_URL,
          width: 32,
          height: 32,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        },
        description: buildSensorDescription(sensor)
      });
      entity._originalIcon = SENSOR_ICON_URL;
      sensorEntities.push(entity);
    });

    var machineryEntities = [];
    machineryData.forEach(function(mach) {
      var entity = viewer.entities.add({
        name: mach.name,
        position: Cesium.Cartesian3.fromDegrees(mach.lon, mach.lat),
        billboard: {
          image: MACHINE_ICON_URL,
          width: 32,
          height: 32,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        },
        description: buildMachineryDescription(mach)
      });
      entity._originalIcon = MACHINE_ICON_URL;
      machineryEntities.push(entity);
    });

    var weatherEntities = [];
    weatherStationsData.forEach(function(ws) {
      var entity = viewer.entities.add({
        name: ws.name,
        position: Cesium.Cartesian3.fromDegrees(ws.lon, ws.lat),
        billboard: {
          image: WEATHER_ICON_URL,
          width: 32,
          height: 32,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        },
        description: buildWeatherDescription(ws)
      });
      entity._originalIcon = WEATHER_ICON_URL;
      weatherEntities.push(entity);
    });

    /*********************************************
     * 6) Selection handling (change pin to green)
     *********************************************/
    var previouslySelectedEntity;
    viewer.selectedEntityChanged.addEventListener(function(newEntity) {
      if (previouslySelectedEntity && previouslySelectedEntity._originalIcon) {
        previouslySelectedEntity.billboard.image = previouslySelectedEntity._originalIcon;
      }
      if (newEntity && newEntity.billboard && newEntity._originalIcon) {
        newEntity.billboard.image = SELECTED_ICON_URL;
      }
      previouslySelectedEntity = newEntity;
    });

    /*********************************************
     * 7) Toggles for sensors, machinery, weather
     *********************************************/
    var sensorsVisible = true;
    document.addEventListener("keydown", function(event) {
      if (event.key === 's') {
        sensorsVisible = !sensorsVisible;
        sensorEntities.forEach(function(ent) {
          ent.show = sensorsVisible;
        });
      }
    });

    var machineryVisible = true;
    document.addEventListener("keydown", function(event) {
      if (event.key === 'm') {
        machineryVisible = !machineryVisible;
        machineryEntities.forEach(function(ent) {
          ent.show = machineryVisible;
        });
      }
    });

    var weatherVisible = true;
    document.addEventListener("keydown", function(event) {
      if (event.key === 'w') {
        weatherVisible = !weatherVisible;
        weatherEntities.forEach(function(ent) {
          ent.show = weatherVisible;
        });
      }
    });

    /*********************************************
     * 8) Toggle sidebar via button and 'R' key
     *********************************************/
    var sidebar = document.getElementById("sidebar");
    var toggleBtn = document.getElementById("toggleSidebarBtn");
    var sidebarVisible = true;

    function toggleSidebar() {
      sidebarVisible = !sidebarVisible;
      if (sidebarVisible) {
        sidebar.style.display = "flex";
      } else {
        sidebar.style.display = "none";
      }
    }
    toggleBtn.addEventListener("click", toggleSidebar);
    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 'r') {
        toggleSidebar();
      }
    });

    /*********************************************
     * Additional: Load Almond Trees from CSV
     *********************************************/
    var almondTreeEntities = [];
    var almondTreesVisible = true;

    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 't') {
        almondTreesVisible = !almondTreesVisible;
        almondTreeEntities.forEach(function(ent) {
          ent.show = almondTreesVisible;
        });
      }
    });

    function loadAlmondTreesCsv() {
      fetch('almond_trees.csv')
        .then(response => response.text())
        .then(csvText => {
          const lines = csvText.trim().split('\n');
          const cartographics = [];
          for (let i = 1; i < lines.length; i++) {
            const row = lines[i].trim();
            if (!row) continue;
            const [lonStr, latStr] = row.split(',');
            const lon = parseFloat(lonStr);
            const lat = parseFloat(latStr);
            cartographics.push(Cesium.Cartographic.fromDegrees(lon, lat));
          }
          Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics)
            .then(updatedPositions => {
              updatedPositions.forEach(pos => {
                const treePosition = Cesium.Cartesian3.fromRadians(
                  pos.longitude,
                  pos.latitude,
                  pos.height
                );
                const treeEntity = viewer.entities.add({
                  position: treePosition,
                  billboard: {
                    image: 'almond_tree.png',
                    width: 40,
                    height: 50,
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM
                  }
                });
                almondTreeEntities.push(treeEntity);
              });
              console.log("Almond trees loaded:", almondTreeEntities.length);
            })
            .catch(error => {
              console.error("Error sampling terrain for almond trees:", error);
            });
        })
        .catch(error => {
          console.error("Error loading almond tree CSV:", error);
        });
    }
    loadAlmondTreesCsv();

    /*********************************************
     * Additional: Load Pistachio Trees from CSV
     *********************************************/
    var pistachioTreeEntities = [];
    var pistachioTreesVisible = true;

    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 'p') {
        pistachioTreesVisible = !pistachioTreesVisible;
        pistachioTreeEntities.forEach(function(ent) {
          ent.show = pistachioTreesVisible;
        });
      }
    });

    function loadPistachioTreesCsv() {
      fetch('pistachio.csv')
        .then(response => response.text())
        .then(csvText => {
          const lines = csvText.trim().split('\n');
          const cartographics = [];
          for (let i = 1; i < lines.length; i++) {
            const row = lines[i].trim();
            if (!row) continue;
            const [lonStr, latStr] = row.split(',');
            const lon = parseFloat(lonStr);
            const lat = parseFloat(latStr);
            cartographics.push(Cesium.Cartographic.fromDegrees(lon, lat));
          }
          Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics)
            .then(updatedPositions => {
              updatedPositions.forEach(pos => {
                const treePosition = Cesium.Cartesian3.fromRadians(
                  pos.longitude,
                  pos.latitude,
                  pos.height
                );
                const treeEntity = viewer.entities.add({
                  position: treePosition,
                  billboard: {
                    image: 'pistachio.png',
                    width: 27,
                    height: 32,
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM
                  }
                });
                pistachioTreeEntities.push(treeEntity);
              });
              console.log("Pistachio trees loaded:", pistachioTreeEntities.length);
            })
            .catch(error => {
              console.error("Error sampling terrain for pistachio trees:", error);
            });
        })
        .catch(error => {
          console.error("Error loading pistachio tree CSV:", error);
        });
    }
    loadPistachioTreesCsv();

    /*********************************************
     * 9) Load pivot13.obj as "result.gltf" on ground
     *********************************************/
    var pivotLon = -5.25854965;
    var pivotLat = 40.8893203;
    var pivotRotationDeg = -1.2;
    var pivotCartographic = Cesium.Cartographic.fromDegrees(pivotLon, pivotLat);
    var pivotHeading = Cesium.Math.toRadians(pivotRotationDeg);
    var pivotPitch   = 0.0;
    var pivotRoll    = 0.0;

    var pivotEntity = viewer.entities.add({
      name: "Pivot 13 Model",
      orientation: Cesium.Quaternion.IDENTITY,
      model: {
        uri: "result.gltf",
        scale: 1.0,
        minimumPixelSize: 0,
        maximumScale: 1000
      }
    });

    Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, [pivotCartographic])
      .then(function(updatedPositions) {
        var updated = updatedPositions[0];
        var groundHeight = updated.height || 0;
        var pivotPosition = Cesium.Cartesian3.fromRadians(
          updated.longitude,
          updated.latitude,
          updated.height
        );
        var pivotOrientation = Cesium.Transforms.headingPitchRollQuaternion(
          pivotPosition,
          new Cesium.HeadingPitchRoll(pivotHeading, pivotPitch, pivotRoll)
        );
        pivotEntity.position = pivotPosition;
        pivotEntity.orientation = pivotOrientation;
      });

    /*********************************************
     * 10) Load Solar Panels from XML data
     *********************************************/
    var solarPanelsData = [
      { lon:-5.25721844, lat:40.8918752,  rotation:-1.33, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25686481, lat:40.8919468,  rotation:-1.39, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25754584, lat:40.8918067,  rotation:-1.37, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25724743, lat:40.891806,   rotation:-1.38, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25653943, lat:40.8920817,  rotation:-1.32, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25651547, lat:40.892015,   rotation:-1.3,  filename:"./solar_panel/solar.gltf" },
      { lon:-5.25720005, lat:40.8919468,  rotation:-1.34, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25755804, lat:40.8918815,  rotation:-1.43, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25686189, lat:40.8920228,  rotation:-1.39, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25691331, lat:40.8918703,  rotation:-1.38, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25651768, lat:40.891946,   rotation:-1.34, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25756409, lat:40.8917434,  rotation:-1.38, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25755467, lat:40.8919547,  rotation:-1.29, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25719114, lat:40.8920366,  rotation:-1.29, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25682728, lat:40.8921143,  rotation:-1.29, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25649289, lat:40.892177,   rotation:-1.31, filename:"./solar_panel/solar.gltf" }
    ];

    var solarPanelEntities = [];
    var solarPanelsVisible = true;

    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 'o') {
        solarPanelsVisible = !solarPanelsVisible;
        solarPanelEntities.forEach(function(ent) {
          ent.show = solarPanelsVisible;
        });
      }
    });

    function loadSolarPanels() {
      var cartographics = [];
      for (var i = 0; i < solarPanelsData.length; i++) {
        var sp = solarPanelsData[i];
        cartographics.push(Cesium.Cartographic.fromDegrees(sp.lon, sp.lat));
      }
      Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics)
        .then(function(updatedPositions) {
          for (var j = 0; j < updatedPositions.length; j++) {
            var updated = updatedPositions[j];
            var groundHeight = updated.height || 0;
            var spData = solarPanelsData[j];

            var position = Cesium.Cartesian3.fromRadians(
              updated.longitude,
              updated.latitude,
              groundHeight
            );
            var heading = Cesium.Math.toRadians(spData.rotation - 15 + 180);
            var pitch   = 0.0;
            var roll    = 0.0;
            var orientation = Cesium.Transforms.headingPitchRollQuaternion(
              position,
              new Cesium.HeadingPitchRoll(heading, pitch, roll)
            );

            var solarEntity = viewer.entities.add({
              name: "Solar Panel",
              position: position,
              orientation: orientation,
              model: {
                uri: spData.filename,
                scale: 1.0,
                minimumPixelSize: 0,
                maximumScale: 1000
              }
            });
            solarPanelEntities.push(solarEntity);
          }
          console.log("Solar panels loaded:", solarPanelEntities.length);
        })
        .catch(function(error) {
          console.error("Error sampling terrain for solar panels:", error);
        });
    }
    loadSolarPanels();

    /*********************************************
     * 11) Google Tiles (toggle via 'g')
     *********************************************/
    // Note: Using Google Maps tiles outside of official APIs can violate
    // Google's Terms of Service. Ensure you have the right to do so.
    var googleImageryProvider = new Cesium.UrlTemplateImageryProvider({
      // 's' for satellite, 'm' for map, 'y' for hybrid, etc.
      url: 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
      maximumLevel: 20
    });
    var googleLayer = viewer.imageryLayers.addImageryProvider(googleImageryProvider);
    googleLayer.show = false;

    document.addEventListener("keydown", function(event) {
      if (event.key === 'g') {
        googleLayer.show = !googleLayer.show;
      }
    });

    /*********************************************
     * 12) Fetch AEMET Forecast from meteo.php
     *********************************************/
    window.addEventListener('DOMContentLoaded', function() {
      fetch('meteo.php')
        .then(response => response.json())
        .then(forecastData => {
          var container = document.getElementById("aemetForecast");
          var html = "";

          if (Array.isArray(forecastData) && forecastData.length > 0) {
            var dias = forecastData[0].prediccion.dia;
            if (dias && dias.length > 0) {
              // Show just today (dia[0]) and tomorrow (dia[1]) if available
              for (var i = 0; i < 2; i++) {
                if (!dias[i]) break;

                var d = dias[i];
                var dateStr = d.fecha;

                // Temperatures
                var minTemp = (d.temperatura && d.temperatura.minima) ? d.temperatura.minima : "N/A";
                var maxTemp = (d.temperatura && d.temperatura.maxima) ? d.temperatura.maxima : "N/A";

                // Humidity (min and max)
                var minHum = (d.humedadRelativa && d.humedadRelativa.minima) ? d.humedadRelativa.minima : "N/A";
                var maxHum = (d.humedadRelativa && d.humedadRelativa.maxima) ? d.humedadRelativa.maxima : "N/A";

                // Probability of precipitation
                var probLluvia = "N/A";
                if (Array.isArray(d.probPrecipitacion) && d.probPrecipitacion.length > 0) {
                  var maxProb = 0;
                  d.probPrecipitacion.forEach(pp => {
                    var val = parseInt(pp.value, 10);
                    if (!isNaN(val) && val > maxProb) {
                      maxProb = val;
                    }
                  });
                  if (maxProb >= 0) {
                    probLluvia = maxProb + "%";
                  }
                }

                // Wind data: first interval
                var windInfo = "N/A";
                if (Array.isArray(d.vientoAndRachaMax) && d.vientoAndRachaMax.length > 0) {
                  var w = d.vientoAndRachaMax[0];
                  if (w && w.direccion && w.velocidad) {
                    windInfo = w.direccion + " " + w.velocidad + " km/h";
                  }
                }

                html += `<div style="margin-bottom:10px;">
                          <strong>${i === 0 ? FIELD_TEXT.today : FIELD_TEXT.tomorrow} (${dateStr})</strong><br/>
                          ${FIELD_TEXT.temperature}: ${minTemp}°C / ${maxTemp}°C<br/>
                          ${FIELD_TEXT.humidity}: ${minHum}% / ${maxHum}%<br/>
                          ${FIELD_TEXT.rain_probability}: ${probLluvia}<br/>
                          ${FIELD_TEXT.wind}: ${windInfo}
                        </div>`;
              }
            } else {
              html = FIELD_TEXT.no_forecast_days;
            }
          } else {
            html = FIELD_TEXT.no_valid_forecast;
          }

          container.innerHTML = html;
        })
        .catch(err => {
          console.error('Error retrieving forecast:', err);
          document.getElementById("aemetForecast").innerHTML = FIELD_TEXT.error_loading_forecast;
        });
    });
</script>
</body>
</html>
