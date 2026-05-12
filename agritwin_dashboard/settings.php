<?php
// settings.php — GAIA global settings
// ------------------------------------------------------------
// • Cesium Ion access token  (key: cesium_token)
// • Copernicus Dataspace project ID (key: copernicus_id)
// Both values are stored in `app_settings` as simple key‑value pairs.
// ------------------------------------------------------------

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_config.php';

//--------------------------------------------------------------
// 0. Helper – tiny UPSERT wrapper
//--------------------------------------------------------------
function save_setting(PDO $pdo, string $name, string $value): void {
    $stmt = $pdo->prepare(
        "INSERT INTO app_settings (name, value) VALUES (:name, :val)
         ON DUPLICATE KEY UPDATE value = :val"
    );
    $stmt->execute(['name' => $name, 'val' => $value]);
}

//--------------------------------------------------------------
// 1. Handle POST from either form
//--------------------------------------------------------------
$noticeMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cesium_token'])) {
        $newToken = trim($_POST['cesium_token']);
        try {
            save_setting($pdo, 'cesium_token', $newToken);
            $noticeMsg = 'Cesium Ion token saved!';
        } catch (Exception $e) {
            $noticeMsg = 'Error saving Cesium token: ' . $e->getMessage();
        }
    } elseif (isset($_POST['copernicus_id'])) {
        $newId = trim($_POST['copernicus_id']);
        try {
            save_setting($pdo, 'copernicus_id', $newId);
            $noticeMsg = 'Copernicus Dataspace ID saved!';
        } catch (Exception $e) {
            $noticeMsg = 'Error saving Copernicus ID: ' . $e->getMessage();
        }
    }
}

//--------------------------------------------------------------
// 2. Load current values for display
//--------------------------------------------------------------
function load_setting(PDO $pdo, string $name): string {
    $stmt = $pdo->prepare('SELECT value FROM app_settings WHERE name = :name LIMIT 1');
    $stmt->execute(['name' => $name]);
    return $stmt->fetchColumn() ?? '';
}

$currentToken       = load_setting($pdo, 'cesium_token');
$currentCopernicus  = load_setting($pdo, 'copernicus_id');

//--------------------------------------------------------------
// 3. Load farms, sensors, actuators (unchanged)
//--------------------------------------------------------------
try {
    $stmt = $pdo->query('SELECT id, farm_name FROM farms ORDER BY farm_name ASC');
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allFarms = [];
}
$farmId   = $_SESSION['farm_id']   ?? null;
$farmName = $_SESSION['farm_name'] ?? 'Synthetic Demo Farm';

$sensors = $actuators = [];
if ($farmId) {
    try {
        $stmt = $pdo->prepare('SELECT sensor_name, sensor_type, node_count FROM farm_sensors WHERE farm_id = :fid ORDER BY id ASC');
        $stmt->execute(['fid' => $farmId]);
        $sensors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    try {
        $stmt = $pdo->prepare('SELECT actuator_name, actuator_type, node_count FROM farm_actuators WHERE farm_id = :fid ORDER BY id ASC');
        $stmt->execute(['fid' => $farmId]);
        $actuators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GAIA Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body{font-family:Arial, sans-serif;display:flex;margin:0}
        .sidebar{width:250px;background:#2E7D32;color:#fff;padding:20px;height:100vh;box-sizing:border-box}
        .sidebar h2{text-align:center;margin-top:0}
        .sidebar ul{list-style:none;padding:0;margin-bottom:20px}
        .sidebar ul li{border-bottom:1px solid rgba(255,255,255,.2)}
        .sidebar ul li a{display:block;padding:10px;color:#fff;text-decoration:none}
        .sidebar ul li a:hover{background:#1B5E20}
        .farm-select-container label{display:block;margin-bottom:5px}
        .farm-select-container select{width:100%;padding:8px;border-radius:4px;border:1px solid #ccc;font-size:14px}
        .main-content{flex:1;padding:20px;background:#F1F8E9;display:flex;flex-direction:column}
        .top-bar{display:flex;justify-content:space-between;align-items:center;background:#4CAF50;color:#fff;padding:10px}
        .settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
        .settings-panel{background:#fff;padding:20px;box-shadow:0 4px 6px rgba(0,0,0,.1);border-radius:10px}
        .settings-panel h3{margin-top:0;margin-bottom:10px}
        .settings-panel label{display:block;margin-bottom:6px;font-weight:bold}
        .settings-panel input[type=text],.settings-panel input[type=number],.settings-panel select{width:100%;padding:6px;margin-bottom:12px;border:1px solid #ccc;border-radius:4px}
        .settings-panel textarea{width:100%;height:60px;margin-bottom:12px;border:1px solid #ccc;border-radius:4px;padding:6px;resize:vertical}
        .settings-panel button{background:#4CAF50;color:#fff;border:none;padding:8px 14px;border-radius:5px;cursor:pointer}
        .settings-panel button:hover{background:#388E3C}
        .sensor-list,.actuator-list{margin-top:10px;border-top:1px solid #ccc;padding-top:10px}
        .sensor-list h4,.actuator-list h4{margin:0 0 6px;font-weight:bold}
        .item-row{margin-bottom:6px}
        .footer{text-align:center;background:#fff;color:#000;padding:10px;margin-top:auto}
        .logo-section{display:flex;justify-content:center;align-items:center;gap:20px;margin-bottom:10px}
        .logo-section img{height:50px}
        .notice{padding:10px 15px;border-radius:6px;margin-bottom:10px}
        .success{background:#DFF2BF;color:#4F8A10}
        .error{background:#FFBABA;color:#D8000C}
    </style>
</head>
<body>
<div class="sidebar">
    <h2>GAIA Dashboard</h2>
    <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="field.php"><i class="fas fa-seedling"></i> Field Monitoring</a></li>
        <li><a href="task_scheduling.php"><i class="fas fa-tasks"></i> Task Scheduling</a></li>
        <li><a href="ai_recommendations.php"><i class="fas fa-brain"></i> AI Recommendations</a></li>
        <li><a href="energy.php"><i class="fas fa-solar-panel"></i> Energy Management</a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="user_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    <div class="farm-select-container">
        <label for="farmSelect">Switch Farm:</label>
        <form action="change_farm.php" method="POST">
            <select name="farm_id" id="farmSelect" onchange="this.form.submit()">
                <option value="">-- Choose a Farm --</option>
                <?php foreach ($allFarms as $farm): ?>
                    <option value="<?= $farm['id'] ?>" <?= $farm['id']==$farmId?'selected':'' ?>>
                        <?= htmlspecialchars($farm['farm_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1><?= htmlspecialchars($farmName) ?> - Settings</h1>
        <span>Configure Sensors, Actuators, AI & API Keys</span>
    </div>

    <?php if ($noticeMsg): ?>
        <div class="notice <?= strpos($noticeMsg,'Error')===false?'success':'error' ?>">
            <?= htmlspecialchars($noticeMsg) ?>
        </div>
    <?php endif; ?>

    <div class="settings-grid">
        <!-- API keys panel (2 inputs) -->
        <div class="settings-panel">
            <h3>External API Credentials</h3>
            <form method="post">
                <label for="cesiumToken">Cesium Ion Token:</label>
                <input type="text" id="cesiumToken" name="cesium_token" value="<?= htmlspecialchars($currentToken,ENT_QUOTES,'UTF-8') ?>" placeholder="Paste Cesium token">
                <button type="submit"><i class="fas fa-save"></i> Save Token</button>
            </form>
            <hr style="margin:15px 0">
            <form method="post">
                <label for="copernicusId">Copernicus Dataspace ID:</label>
                <input type="text" id="copernicusId" name="copernicus_id" value="<?= htmlspecialchars($currentCopernicus,ENT_QUOTES,'UTF-8') ?>" placeholder="682d23b8‑...">
                <button type="submit"><i class="fas fa-save"></i> Save ID</button>
            </form>
        </div>

        <!-- Sensor Access Setup -->
        <div class="settings-panel">
            <h3>Sensor Access Setup</h3>
            <label for="restEndpoint">REST Endpoint URL:</label>
            <input type="text" id="restEndpoint" placeholder="http://api.example.com/sensor-data">
            <label for="sensorParams">Sensor Parameters (JSON):</label>
            <textarea id="sensorParams" placeholder='{"soilMoisture":true,"temperature":true}'></textarea>
            <label for="refreshInterval">Data Refresh Interval (seconds):</label>
            <input type="number" id="refreshInterval" min="1" placeholder="60">
            <button>Save Sensor Setup</button>
            <div class="sensor-list">
                <h4>Registered Sensors (<?= count($sensors) ?>)</h4>
                <?php if ($sensors): foreach ($sensors as $s): ?>
                    <div class="item-row"><strong><?= htmlspecialchars($s['sensor_name']) ?></strong> (Type: <?= htmlspecialchars($s['sensor_type']) ?>, Nodes: <?= htmlspecialchars($s['node_count']) ?>)</div>
                <?php endforeach; else: ?>
                    <p>No sensor nodes configured for this farm.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actuator Control -->
        <div class="settings-panel">
            <h3>Actuator Control</h3>
            <label for="actuatorRestURL">Actuator REST URL:</label>
            <input type="text" id="actuatorRestURL" placeholder="http://api.example.com/actuators">
            <label for="ros2Topic">ROS2 Topic Name:</label>
            <input type="text" id="ros2Topic" placeholder="/farm/actuators">
            <label for="actuatorParams">Actuator Command Template (JSON):</label>
            <textarea id="actuatorParams" placeholder='{"irrigation":{"on":false,"duration":0}}'></textarea>
            <button>Save Actuator Setup</button>
            <div class="actuator-list">
                <h4>Registered Actuators (<?= count($actuators) ?>)</h4>
                <?php if ($actuators): foreach ($actuators as $a): ?>
                    <div class="item-row"><strong><?= htmlspecialchars($a['actuator_name']) ?></strong> (Type: <?= htmlspecialchars($a['actuator_type']) ?>, Nodes: <?= htmlspecialchars($a['node_count']) ?>)</div>
                <?php endforeach; else: ?>
                    <p>No actuators configured for this farm.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Machine Learning Models (span 2 columns) -->
        <div class="settings-panel" style="grid-column:1/span 2;">
            <h3>Machine Learning Model Selection</h3>
            <label for="cropsModel">Crops Model:</label>
            <select id="cropsModel"><option value="baseline">Baseline Crop Model</option><option value="advanced">Advanced Yield Predictor</option><option value="soil-optimiser">Soil Optimiser Model</option></select>
            <label for="energyModel">Energy Model (Wind / Solar):</label>
            <select id="energyModel"><option value="standard">Standard Efficiency Model</option><option value="ml-forecast">ML-Based Forecast Model</option><option value="hybrid-ai">Hybrid AI Model</option></select>
            <button>Save Model Selection</button>
        </div>
    </div>

        <div class="footer">
            <div class="logo-section">
        <img src="GAIA_logo_transparent.png" alt="GAIA Logo">
        <img src="xilbi.png" alt="Xilbi Logo">
        <img src="Logo_GGS.png" alt="GGS Logo">
        <img src="farmtopia_logo.png" alt="Farmtopia Logo">
        <img src="eufunded.png" alt="EU Funded">
            </div>
            <p>© 2025 GAIA Smart Farming | Powered by Farmtopia</p>
        </div>
</div>
</body>
</html>
