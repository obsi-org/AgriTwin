<?php
session_start();

// If user is not logged in, redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include DB connection
require_once "db_config.php";
require_once __DIR__ . "/includes/field_data_provider.php";

// The Spanish farm is farm_id=1
$spainFarmId = 1;

$fieldLang = agritwin_current_language();
$fieldLangQuery = '?lang=' . rawurlencode($fieldLang);

$fieldText = [
    'en' => [
        'page_title' => 'AgriTwin Field Digital Twin',
        'field_monitoring' => 'Field Monitoring',
        'local_weather_station' => 'Local Weather Station',
        'weather_source' => 'Weather source',
        'last_updated' => 'Last updated',
        'rainfall_today' => 'Rainfall today',
        'temperature' => 'Temperature',
        'humidity' => 'Humidity',
        'wind_speed' => 'Wind speed',
        'soil_moisture' => 'Soil moisture',
        'soil_temperature' => 'Soil temperature',
        'solar_radiation' => 'Solar radiation',
        'operational_insights' => 'Operational Insights',
        'task_scheduling' => 'Task Scheduling',
        'irrigation_status' => 'Irrigation status',
        'pest_disease_risk' => 'Pest and disease risk',
        'openagri_weather_service' => 'OpenAgri Weather Service',
        'aemet' => 'AEMET',
        'public_weather_source' => 'Public weather source',
        'local_fallback' => 'Local fallback',
        'local_operational_dataset' => 'Local operational dataset',
        'credentials_required' => 'Credentials required',
        'configuration_required' => 'Configuration required',
        'external_provider_configuration_required' => 'External provider configuration required',
        'today' => 'Today',
        'tomorrow' => 'Tomorrow',
        'back_dashboard' => 'Back to AgriTwin Dashboard',
        'select_index' => 'Select Index',
        'none' => 'None',
        'default_imagery_only' => 'Default imagery only',
        'legend' => 'Legend',
        'index_legend' => 'Index legend',
        'loading_forecast' => 'Loading forecast...',
        'error_loading_forecast' => 'Error loading forecast.',
        'weather_forecast_unavailable' => 'Weather forecast unavailable.',
        'no_forecast_days' => 'No forecast day data found.',
        'no_valid_forecast' => 'No valid forecast data available.',
        'property_boundary' => 'Property Boundary',
        'solar_panel' => 'Solar Panel',
        'irrigation_pivot' => 'Irrigation pivot',
        'tractor' => 'Tractor',
        'harvester' => 'Harvester',
        'sensor' => 'Sensor',
        'weather_station' => 'Weather station',
        'machinery' => 'Machinery',
        'status' => 'Status',
        'coordinates' => 'Coordinates',
        'active' => 'Active',
        'idle' => 'Idle',
        'online' => 'Online',
        'rain_probability' => 'Rain probability',
        'wind' => 'Wind',
        'toggle_sidebar' => 'Toggle Sidebar (R)',
        'copernicus_vegetation_index' => 'Copernicus vegetation index',
        'copernicus_layer_unavailable' => 'Copernicus layer unavailable',
        'legend_unavailable' => 'Legend unavailable',
        'local_weather_heading' => 'Local Weather Station - Nava de Sotrobal',
        'forecast_heading' => 'AEMET Weather Forecast - Nava de Sotrobal',
        'avg_moisture_insight' => 'Average near-surface soil moisture is %.1f %% VWC across field markers.',
        'lowest_moisture_insight' => '%s is the lowest marker at %.1f %% VWC; review irrigation timing before the evening cycle.',
        'weather_operational_insight' => 'Weather source: %s. Wind is %.1f km h⁻¹ from %s, with %.1f mm rainfall today.',
        'task_sensor_check' => 'Inspect soil-moisture sensors and compare almond block readings today at %s.',
        'task_irrigation_pressure' => 'Verify irrigation line pressure and pump telemetry before the next scheduled watering window.',
        'task_schedule_review' => 'Review almond block irrigation schedule tomorrow (%s).',
        'task_trap_check' => 'Check pest and disease monitoring traps within the next 48 hours.',
        'task_pv_energy' => 'Verify PV and pump energy readings before the next irrigation cycle.',
        'irrigation_review_required_today' => 'Irrigation review required today',
        'irrigation_schedule_within_target_range' => 'Irrigation schedule within target range',
        'moderate_monitoring_priority' => 'Moderate monitoring priority',
        'low_monitoring_priority' => 'Low monitoring priority',
    ],
    'pt-PT' => [
        'page_title' => 'Gémeo Digital de Campo AgriTwin',
        'field_monitoring' => 'Monitorização de Campo',
        'local_weather_station' => 'Estação Meteorológica Local',
        'weather_source' => 'Fonte meteorológica',
        'last_updated' => 'Última atualização',
        'rainfall_today' => 'Precipitação hoje',
        'temperature' => 'Temperatura',
        'humidity' => 'Humidade',
        'wind_speed' => 'Velocidade do vento',
        'soil_moisture' => 'Humidade do solo',
        'soil_temperature' => 'Temperatura do solo',
        'solar_radiation' => 'Radiação solar',
        'operational_insights' => 'Análise operacional',
        'task_scheduling' => 'Planeamento de tarefas',
        'irrigation_status' => 'Estado da rega',
        'pest_disease_risk' => 'Risco de pragas e doenças',
        'openagri_weather_service' => 'OpenAgri Weather Service',
        'aemet' => 'AEMET',
        'public_weather_source' => 'Fonte meteorológica pública',
        'local_fallback' => 'Modo local de contingência',
        'local_operational_dataset' => 'Conjunto de dados operacional local',
        'credentials_required' => 'Credenciais necessárias',
        'configuration_required' => 'Configuração necessária',
        'external_provider_configuration_required' => 'Configuração do fornecedor externo necessária',
        'today' => 'Hoje',
        'tomorrow' => 'Amanhã',
        'back_dashboard' => 'Voltar ao Painel AgriTwin',
        'select_index' => 'Selecionar índice',
        'none' => 'Nenhum',
        'default_imagery_only' => 'Apenas imagem base',
        'legend' => 'Legenda',
        'index_legend' => 'Legenda do índice',
        'loading_forecast' => 'A carregar previsão...',
        'error_loading_forecast' => 'Erro ao carregar a previsão.',
        'weather_forecast_unavailable' => 'Previsão meteorológica indisponível.',
        'no_forecast_days' => 'Não foram encontrados dados diários de previsão.',
        'no_valid_forecast' => 'Não existem dados de previsão válidos disponíveis.',
        'property_boundary' => 'Limite da propriedade',
        'solar_panel' => 'Painel solar',
        'irrigation_pivot' => 'Pivot de rega',
        'tractor' => 'Trator',
        'harvester' => 'Ceifeira',
        'sensor' => 'Sensor',
        'weather_station' => 'Estação meteorológica',
        'machinery' => 'Máquina',
        'status' => 'Estado',
        'coordinates' => 'Coordenadas',
        'active' => 'Ativo',
        'idle' => 'Inativo',
        'online' => 'Online',
        'rain_probability' => 'Probabilidade de precipitação',
        'wind' => 'Vento',
        'toggle_sidebar' => 'Alternar painel lateral (R)',
        'copernicus_vegetation_index' => 'Índice de vegetação Copernicus',
        'copernicus_layer_unavailable' => 'Camada Copernicus indisponível',
        'legend_unavailable' => 'Legenda indisponível',
        'local_weather_heading' => 'Estação Meteorológica Local - Nava de Sotrobal',
        'forecast_heading' => 'Previsão meteorológica AEMET - Nava de Sotrobal',
        'avg_moisture_insight' => 'A humidade média do solo à superfície é %.1f %% VWC nos marcadores de campo.',
        'lowest_moisture_insight' => '%s é o marcador mais baixo, com %.1f %% VWC; rever o horário de rega antes do ciclo do fim do dia.',
        'weather_operational_insight' => 'Fonte meteorológica: %s. O vento é %.1f km h⁻¹ de %s, com %.1f mm de precipitação hoje.',
        'task_sensor_check' => 'Inspecionar os sensores de humidade do solo e comparar as leituras dos blocos de amendoeiras hoje às %s.',
        'task_irrigation_pressure' => 'Verificar a pressão da linha de rega e a telemetria da bomba antes da próxima janela de rega.',
        'task_schedule_review' => 'Rever o plano de rega dos blocos de amendoeiras amanhã (%s).',
        'task_trap_check' => 'Verificar as armadilhas de monitorização de pragas e doenças nas próximas 48 horas.',
        'task_pv_energy' => 'Verificar as leituras de energia fotovoltaica e da bomba antes do próximo ciclo de rega.',
        'irrigation_review_required_today' => 'Revisão da rega necessária hoje',
        'irrigation_schedule_within_target_range' => 'Plano de rega dentro do intervalo-alvo',
        'moderate_monitoring_priority' => 'Prioridade de monitorização moderada',
        'low_monitoring_priority' => 'Prioridade de monitorização baixa',
    ],
    'es-ES' => [
        'page_title' => 'Gemelo digital de campo AgriTwin',
        'field_monitoring' => 'Monitorización de campo',
        'local_weather_station' => 'Estación meteorológica local',
        'weather_source' => 'Fuente meteorológica',
        'last_updated' => 'Última actualización',
        'rainfall_today' => 'Precipitación hoy',
        'temperature' => 'Temperatura',
        'humidity' => 'Humedad',
        'wind_speed' => 'Velocidad del viento',
        'soil_moisture' => 'Humedad del suelo',
        'soil_temperature' => 'Temperatura del suelo',
        'solar_radiation' => 'Radiación solar',
        'operational_insights' => 'Análisis operativo',
        'task_scheduling' => 'Planificación de tareas',
        'irrigation_status' => 'Estado del riego',
        'pest_disease_risk' => 'Riesgo de plagas y enfermedades',
        'openagri_weather_service' => 'OpenAgri Weather Service',
        'aemet' => 'AEMET',
        'public_weather_source' => 'Fuente meteorológica pública',
        'local_fallback' => 'Modo local de contingencia',
        'local_operational_dataset' => 'Conjunto de datos operativo local',
        'credentials_required' => 'Credenciales necesarias',
        'configuration_required' => 'Configuración necesaria',
        'external_provider_configuration_required' => 'Configuración del proveedor externo necesaria',
        'today' => 'Hoy',
        'tomorrow' => 'Mañana',
        'back_dashboard' => 'Volver al panel AgriTwin',
        'select_index' => 'Seleccionar índice',
        'none' => 'Ninguno',
        'default_imagery_only' => 'Solo imagen base',
        'legend' => 'Leyenda',
        'index_legend' => 'Leyenda del índice',
        'loading_forecast' => 'Cargando previsión...',
        'error_loading_forecast' => 'Error al cargar la previsión.',
        'weather_forecast_unavailable' => 'Previsión meteorológica no disponible.',
        'no_forecast_days' => 'No se han encontrado datos diarios de previsión.',
        'no_valid_forecast' => 'No hay datos de previsión válidos disponibles.',
        'property_boundary' => 'Límite de la propiedad',
        'solar_panel' => 'Panel solar',
        'irrigation_pivot' => 'Pivote de riego',
        'tractor' => 'Tractor',
        'harvester' => 'Cosechadora',
        'sensor' => 'Sensor',
        'weather_station' => 'Estación meteorológica',
        'machinery' => 'Maquinaria',
        'status' => 'Estado',
        'coordinates' => 'Coordenadas',
        'active' => 'Activo',
        'idle' => 'Inactivo',
        'online' => 'Online',
        'rain_probability' => 'Probabilidad de precipitación',
        'wind' => 'Viento',
        'toggle_sidebar' => 'Alternar panel lateral (R)',
        'copernicus_vegetation_index' => 'Índice de vegetación Copernicus',
        'copernicus_layer_unavailable' => 'Capa Copernicus no disponible',
        'legend_unavailable' => 'Leyenda no disponible',
        'local_weather_heading' => 'Estación meteorológica local - Nava de Sotrobal',
        'forecast_heading' => 'Previsión meteorológica AEMET - Nava de Sotrobal',
        'avg_moisture_insight' => 'La humedad media del suelo superficial es %.1f %% VWC en los marcadores de campo.',
        'lowest_moisture_insight' => '%s es el marcador más bajo, con %.1f %% VWC; revise el horario de riego antes del ciclo de la tarde.',
        'weather_operational_insight' => 'Fuente meteorológica: %s. El viento es de %.1f km h⁻¹ desde %s, con %.1f mm de precipitación hoy.',
        'task_sensor_check' => 'Inspeccionar los sensores de humedad del suelo y comparar las lecturas de los bloques de almendros hoy a las %s.',
        'task_irrigation_pressure' => 'Verificar la presión de la línea de riego y la telemetría de la bomba antes de la próxima ventana de riego.',
        'task_schedule_review' => 'Revisar la programación de riego de los bloques de almendros mañana (%s).',
        'task_trap_check' => 'Comprobar las trampas de seguimiento de plagas y enfermedades en las próximas 48 horas.',
        'task_pv_energy' => 'Verificar las lecturas de energía fotovoltaica y de la bomba antes del próximo ciclo de riego.',
        'irrigation_review_required_today' => 'Revisión del riego necesaria hoy',
        'irrigation_schedule_within_target_range' => 'Programación de riego dentro del rango objetivo',
        'moderate_monitoring_priority' => 'Prioridad de seguimiento moderada',
        'low_monitoring_priority' => 'Prioridad de seguimiento baja',
    ],
];

$t = static fn (string $key): string => $fieldText[$fieldLang][$key] ?? $fieldText['en'][$key] ?? $key;
$fieldData = agritwin_field_current_data($pdo);
$fieldWeather = $fieldData['local_weather_station'];
$sourceMap = [
    'OpenAgri Weather Data Service' => $t('openagri_weather_service'),
    'OpenAgri Weather Service' => $t('openagri_weather_service'),
    'AEMET' => $t('aemet'),
    'Public weather source' => $t('public_weather_source'),
    'Local fallback' => $t('local_fallback'),
    'Local operational dataset' => $t('local_operational_dataset'),
    'Credentials required' => $t('credentials_required'),
    'Configuration required' => $t('configuration_required'),
    'External provider configuration required' => $t('external_provider_configuration_required'),
];
$weatherSourceLabel = $sourceMap[(string) ($fieldWeather['source_label'] ?? '')] ?? (string) ($fieldWeather['source_label'] ?? $t('local_fallback'));
$weatherSourceStatus = $sourceMap[(string) ($fieldWeather['source_status'] ?? '')] ?? (string) ($fieldWeather['source_status'] ?? '');
$sensorRows = $fieldData['sensor_markers'] ?? [];
$avgMoisture = count($sensorRows) > 0 ? round(array_sum(array_column($sensorRows, 'moist10')) / count($sensorRows), 1) : 0.0;
$lowestSensor = array_reduce($sensorRows, static fn (?array $carry, array $item): array => $carry === null || $item['moist10'] < $carry['moist10'] ? $item : $carry) ?? ['name' => 'Sensor', 'moist10' => 0];
$tomorrow = (new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid')))->modify('+1 day')->format('Y-m-d');
$translatedInsights = [
    sprintf($t('avg_moisture_insight'), $avgMoisture),
    sprintf($t('lowest_moisture_insight'), (string) $lowestSensor['name'], (float) $lowestSensor['moist10']),
    sprintf($t('weather_operational_insight'), $weatherSourceLabel, (float) ($fieldWeather['wind_kmh'] ?? 0), (string) ($fieldWeather['wind_direction'] ?? ''), (float) ($fieldWeather['rainfall_mm'] ?? 0)),
];
$translatedTasks = [
    sprintf($t('task_sensor_check'), (string) ($fieldWeather['last_updated_label'] ?? $fieldData['local_time'] ?? '')),
    $t('task_irrigation_pressure'),
    sprintf($t('task_schedule_review'), $tomorrow),
    $t('task_trap_check'),
    $t('task_pv_energy'),
];
$fieldJsData = [
    'sensor_markers' => $fieldData['sensor_markers'] ?? [],
    'weather_stations' => $fieldData['weather_stations'] ?? [],
];
$statusMap = [
    'Irrigation review required today' => $t('irrigation_review_required_today'),
    'Irrigation schedule within target range' => $t('irrigation_schedule_within_target_range'),
    'Moderate monitoring priority' => $t('moderate_monitoring_priority'),
    'Low monitoring priority' => $t('low_monitoring_priority'),
];
$irrigationStatusLabel = $statusMap[(string) ($fieldData['irrigation_status'] ?? '')] ?? (string) ($fieldData['irrigation_status'] ?? '');
$pestDiseaseStatusLabel = $statusMap[(string) ($fieldData['pest_disease_status'] ?? '')] ?? (string) ($fieldData['pest_disease_status'] ?? '');

function agritwin_field_setting(?PDO $pdo, array $names): string
{
    if (!$pdo instanceof PDO || $names === []) {
        return '';
    }

    $placeholders = implode(',', array_fill(0, count($names), '?'));
    $stmt = $pdo->prepare("SELECT name, value FROM app_settings WHERE name IN ($placeholders)");
    $stmt->execute($names);
    $values = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($names as $name) {
        $value = trim((string) ($values[$name] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function agritwin_field_env(array $names): string
{
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && trim((string) $value) !== '') {
            return trim((string) $value);
        }
        if (isset($_ENV[$name]) && trim((string) $_ENV[$name]) !== '') {
            return trim((string) $_ENV[$name]);
        }
        if (isset($_SERVER[$name]) && trim((string) $_SERVER[$name]) !== '') {
            return trim((string) $_SERVER[$name]);
        }
    }

    return '';
}

// 2a) Cesium token (global setting) ---------------------------------------
try {
    $stmtTok = $pdo->prepare("SELECT value FROM app_settings WHERE name = 'cesium_token' LIMIT 1");
    $stmtTok->execute();
    $cesiumToken = $stmtTok->fetchColumn() ?: '';
} catch (Exception $e) {
    $cesiumToken = '';
    /* In production, log error */
}

/* Copernicus/Sentinel Hub configuration (global setting) ---------------- */
try {
    $copernicusWmsUrl = agritwin_field_setting($pdo, ['sentinel_hub_wms_url']);
    $copernicusId = agritwin_field_setting($pdo, [
        'copernicus_id',
        'sentinelhub_instance_id',
        'sentinel_hub_instance_id',
    ]);
    if ($copernicusId === '') {
        $copernicusId = agritwin_field_env([
            'COPERNICUS_ID',
            'SENTINELHUB_INSTANCE_ID',
            'SENTINEL_HUB_INSTANCE_ID',
        ]);
    }
    if ($copernicusWmsUrl === '') {
        $copernicusWmsUrl = agritwin_field_env(['SENTINEL_HUB_WMS_URL']);
    }
} catch (Exception $e) {
    $copernicusId = '';
    $copernicusWmsUrl = '';
    // Optionally: error_log($e->getMessage());
}
$copernicusConfigured = $copernicusId !== '' || $copernicusWmsUrl !== '';

// Operational placeholders used when the latest database row is unavailable.
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
<html lang="<?= htmlspecialchars($fieldLang, ENT_QUOTES, 'UTF-8') ?>">
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
        .layer-status {
            margin: 8px 0 0;
            max-width: 220px;
            color: #d6f5d6;
            font-size: 12px;
            line-height: 1.3;
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
        <img src="eufunded.png" alt="European Union funding logo">
    </div>
</div>

<!-- Button to toggle the right sidebar -->
<button id="toggleSidebarBtn"><?= htmlspecialchars($t('toggle_sidebar'), ENT_QUOTES, 'UTF-8') ?></button>

<!-- Right sidebar for sensor data, operational insights, tasks and navigation -->
<div id="sidebar">
    <h2>Fotovoltaica Macotera SL - <?= htmlspecialchars($t('field_monitoring'), ENT_QUOTES, 'UTF-8') ?></h2>

<div class="sensor-data">
  <h3><?= htmlspecialchars($t('local_weather_heading'), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) ($fieldWeather['last_updated_label'] ?? $fieldData['local_time']), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($t('today'), ENT_QUOTES, 'UTF-8') ?></h3>

  <p><strong><?= htmlspecialchars($t('temperature'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($fieldWeather['temperature_c'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?> °C</p>
  <p><strong><?= htmlspecialchars($t('humidity'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($fieldWeather['humidity_pct'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?> %</p>
  <p><strong><?= htmlspecialchars($t('wind_speed'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($fieldWeather['wind_kmh'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?> km h<sup>-1</sup> (<?= htmlspecialchars((string) ($fieldWeather['wind_direction'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</p>
  <p><strong><?= htmlspecialchars($t('rainfall_today'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($fieldWeather['rainfall_mm'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> mm</p>
  <p><strong><?= htmlspecialchars($t('solar_radiation'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($fieldWeather['solar_radiation_wm2'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> W/m²</p>
  <p><strong><?= htmlspecialchars($t('weather_source'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars($weatherSourceLabel, ENT_QUOTES, 'UTF-8') ?><?= $weatherSourceStatus !== '' ? ' - ' . htmlspecialchars($weatherSourceStatus, ENT_QUOTES, 'UTF-8') : '' ?></p>
  <p><strong><?= htmlspecialchars($t('last_updated'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($fieldWeather['last_updated_label'] ?? $fieldData['local_time']), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<div class="sensor-data">
  <h3><?= htmlspecialchars($t('operational_insights'), ENT_QUOTES, 'UTF-8') ?> - Fotovoltaica Macotera SL</h3>
  <ul>
    <?php foreach ($translatedInsights as $insight): ?>
      <li><?= htmlspecialchars($insight, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
  <p><strong><?= htmlspecialchars($t('soil_moisture'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) $avgMoisture, ENT_QUOTES, 'UTF-8') ?> % VWC</p>
  <p><strong><?= htmlspecialchars($t('irrigation_status'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars($irrigationStatusLabel, ENT_QUOTES, 'UTF-8') ?></p>
  <p><strong><?= htmlspecialchars($t('pest_disease_risk'), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars($pestDiseaseStatusLabel, ENT_QUOTES, 'UTF-8') ?></p>
</div>

<!-- ---------- Property work queue ---------- -->
<div class="task-list">
  <h3><?= htmlspecialchars($t('task_scheduling'), ENT_QUOTES, 'UTF-8') ?> - Fotovoltaica Macotera SL</h3>
  <ul>
    <?php foreach ($translatedTasks as $task): ?>
      <li><?= htmlspecialchars($task, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
</div>
    <!-- New DIV: AEMET Weather Forecast -->
    <div class="sensor-data">
        <h3><?= htmlspecialchars($t('forecast_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <div id="aemetForecast"><?= htmlspecialchars($t('loading_forecast'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <!-- End of new weather forecast block -->

    <!-- Button for navigating back to AgriTwin Dashboard -->
    <button class="dashboard-button" onclick="window.location.href='index.php<?= htmlspecialchars($fieldLangQuery, ENT_QUOTES, 'UTF-8') ?>'">
        <?= htmlspecialchars($t('back_dashboard'), ENT_QUOTES, 'UTF-8') ?>
    </button>
</div>

<!-- Panel for Copernicus NDVI / LAI_SAVI toggles via radio buttons -->
<div class="layerTogglePanel">
    <p><strong><?= htmlspecialchars($t('select_index'), ENT_QUOTES, 'UTF-8') ?></strong></p>
    <label>
      <input type="radio" name="indexLayer" value="none" checked/>
      <?= htmlspecialchars($t('none'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($t('default_imagery_only'), ENT_QUOTES, 'UTF-8') ?>)
    </label>
    <br>
    <label>
      <input type="radio" name="indexLayer" value="NDVI"<?= $copernicusConfigured ? '' : ' disabled' ?>/>
      NDVI (Copernicus)
    </label>
    <br>
    <label>
      <input type="radio" name="indexLayer" value="LAI_SAVI"<?= $copernicusConfigured ? '' : ' disabled' ?>/>
      LAI/SAVI (Copernicus)
    </label>
    <p id="copernicusLayerStatus"
       class="layer-status"
       data-ready-text="<?= htmlspecialchars($t('copernicus_vegetation_index'), ENT_QUOTES, 'UTF-8') ?>"
       data-required-text="<?= htmlspecialchars($t('configuration_required'), ENT_QUOTES, 'UTF-8') ?>"
       data-unavailable-text="<?= htmlspecialchars($t('copernicus_layer_unavailable'), ENT_QUOTES, 'UTF-8') ?>"
       data-legend-unavailable-text="<?= htmlspecialchars($t('legend_unavailable'), ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($copernicusConfigured ? $t('copernicus_vegetation_index') : $t('configuration_required'), ENT_QUOTES, 'UTF-8') ?>
    </p>
</div>

<!-- Legend container for Copernicus NDVI/LAI_SAVI -->
<div id="legendContainer">
    <div id="legendTitle"><?= htmlspecialchars($t('legend'), ENT_QUOTES, 'UTF-8') ?></div>
    <img id="legendImage" src="" alt="<?= htmlspecialchars($t('index_legend'), ENT_QUOTES, 'UTF-8') ?>" />
</div>


<!-------------------------------------------------------------------------->
<!-- SINGLE, VALID <script> SECTION  (no PHP inside JS!) ------------------->
<script>
const FIELD_TEXT = <?= json_encode($fieldText[$fieldLang], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const FIELD_DATA = <?= json_encode($fieldJsData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
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
     * 3) Copernicus NDVI / LAI_SAVI + bounding
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
      height: 940,
      heading: 0,
      pitch: 0,
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

const COPERNICUS_ID = String(<?= json_encode($copernicusId) ?> || '').trim();
const CONFIGURED_COPERNICUS_WMS_URL = String(<?= json_encode($copernicusWmsUrl) ?> || '').trim();
const sentinelWmsUrl = CONFIGURED_COPERNICUS_WMS_URL || (COPERNICUS_ID ? `https://sh.dataspace.copernicus.eu/ogc/wms/${COPERNICUS_ID}?` : '');
const copernicusDiagnostics = {
  idConfigured: Boolean(COPERNICUS_ID || CONFIGURED_COPERNICUS_WMS_URL),
  wmsUrlConfigured: Boolean(sentinelWmsUrl),
  ndviProviderInitialised: false,
  laiSaviProviderInitialised: false,
  selectedIndexLayer: 'none',
  lastLayerError: ''
};
window.AgriTwinCopernicusDiagnostics = copernicusDiagnostics;


    var newNdviLayer = null;
    var laiSaviLayer = null;
    var copernicusStatus = document.getElementById("copernicusLayerStatus");

    function updateCopernicusStatus(message) {
      if (copernicusStatus) {
        copernicusStatus.textContent = message;
      }
    }

    function registerImageryError(provider, layerName) {
      if (provider && provider.errorEvent) {
        provider.errorEvent.addEventListener(function(error) {
          copernicusDiagnostics.lastLayerError = layerName + ': ' + (error && error.message ? error.message : 'imagery request failed');
          updateCopernicusStatus(copernicusStatus ? copernicusStatus.dataset.unavailableText : FIELD_TEXT.copernicus_layer_unavailable);
        });
      }
    }

    if (sentinelWmsUrl) {
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
      registerImageryError(newNdviProvider, "NDVI");
      newNdviLayer = viewer.imageryLayers.addImageryProvider(newNdviProvider);
      newNdviLayer.show = false;
      copernicusDiagnostics.ndviProviderInitialised = true;

      var laiSaviProvider = new Cesium.WebMapServiceImageryProvider({
        ...wmsOptions,
        layers: "LAI_SAVI"
      });
      registerImageryError(laiSaviProvider, "LAI_SAVI");
      laiSaviLayer = viewer.imageryLayers.addImageryProvider(laiSaviProvider);
      laiSaviLayer.show = false;
      copernicusDiagnostics.laiSaviProviderInitialised = true;
      updateCopernicusStatus(copernicusStatus ? copernicusStatus.dataset.readyText : FIELD_TEXT.copernicus_vegetation_index);
    } else {
      updateCopernicusStatus(copernicusStatus ? copernicusStatus.dataset.requiredText : FIELD_TEXT.configuration_required);
    }

    // Custom legend images
    var legendUrls = {
      NDVI: "ndvi_legend_custom.png",
      LAI_SAVI: "lai_savi_legend_custom.png"
    };

    var radios = document.querySelectorAll('input[name="indexLayer"]');
    var legendContainer = document.getElementById("legendContainer");
    var legendImage = document.getElementById("legendImage");
    var legendTitle = document.getElementById("legendTitle");
    if (legendImage) {
      legendImage.addEventListener("error", function() {
        legendContainer.style.display = "block";
        legendTitle.textContent = copernicusStatus ? copernicusStatus.dataset.legendUnavailableText : FIELD_TEXT.legend_unavailable;
        legendImage.removeAttribute("src");
      });
    }

    radios.forEach(rb => {
      rb.addEventListener("change", () => {
        if (newNdviLayer) {
          newNdviLayer.show = false;
        }
        if (laiSaviLayer) {
          laiSaviLayer.show = false;
        }
        copernicusDiagnostics.selectedIndexLayer = rb.value;

        if (rb.value === "none") {
          legendContainer.style.display = "none";
          return;
        }
        if (!sentinelWmsUrl || !newNdviLayer || !laiSaviLayer) {
          updateCopernicusStatus(copernicusStatus ? copernicusStatus.dataset.requiredText : FIELD_TEXT.configuration_required);
          rb.checked = false;
          document.querySelector('input[name="indexLayer"][value="none"]').checked = true;
          legendContainer.style.display = "none";
          return;
        }
        if (rb.value === "NDVI") {
          newNdviLayer.show = true;
        } else if (rb.value === "LAI_SAVI") {
          laiSaviLayer.show = true;
        }
        updateCopernicusStatus(copernicusStatus ? copernicusStatus.dataset.readyText : FIELD_TEXT.copernicus_vegetation_index);
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
        name: FIELD_TEXT.property_boundary,
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
      { name: `${FIELD_TEXT.tractor} 1`,   lat: 40.8887, lon: -5.2608, status: FIELD_TEXT.active },
      { name: `${FIELD_TEXT.harvester} 1`, lat: 40.8875, lon: -5.2612, status: FIELD_TEXT.idle }
    ];

    var weatherStationsData = (FIELD_DATA.weather_stations || []).map(function(ws) {
      var stationNumber = String(ws.name || '').match(/\d+/);
      return {
        name: stationNumber ? `${FIELD_TEXT.weather_station} ${stationNumber[0]}` : (ws.name || FIELD_TEXT.weather_station),
        lat: ws.lat,
        lon: ws.lon,
        status: FIELD_TEXT.online,
        temperature: ws.temperature,
        humidity: ws.humidity,
        windSpeed: ws.windSpeed,
        windDirection: ws.windDirection || '',
        rainfall: ws.rainfall
      };
    });

    function buildSensorDescription(sensor) {
      return `
        <div>
          <i class="fa-solid fa-microchip" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${sensor.name}</strong><br/>
          ${FIELD_TEXT.temperature}: ${sensor.temp}°C<br/>
          ${FIELD_TEXT.soil_moisture} (10 cm): ${sensor.moist10}% VWC<br/>
          ${FIELD_TEXT.soil_moisture} (30 cm): ${sensor.moist30}% VWC<br/>
          ${FIELD_TEXT.soil_moisture} (50 cm): ${sensor.moist50}% VWC<br/><br/>
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
      let rainfallLine = ws.rainfall ? `${FIELD_TEXT.rainfall_today}: ${ws.rainfall} mm<br/>` : "";
      return `
        <div>
          <i class="fa-solid fa-cloud-sun" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${ws.name}</strong><br/>
          ${FIELD_TEXT.status}: ${ws.status}<br/>
          ${FIELD_TEXT.temperature}: ${ws.temperature}°C<br/>
          ${FIELD_TEXT.humidity}: ${ws.humidity}%<br/>
          ${FIELD_TEXT.wind_speed}: ${ws.windSpeed} km h⁻¹ ${ws.windDirection ? '(' + ws.windDirection + ')' : ''}<br/>
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
      name: `${FIELD_TEXT.irrigation_pivot} 13`,
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
              name: FIELD_TEXT.solar_panel,
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
      fetch('meteo.php?lang=<?= rawurlencode($fieldLang) ?>')
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
