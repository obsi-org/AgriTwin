<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "db_config.php";

function hmi_percent_value(?string $value): ?float {
    if ($value === null) {
        return null;
    }
    if (preg_match('/-?\d+(\.\d+)?/', $value, $matches)) {
        return (float) $matches[0];
    }
    return null;
}

function hmi_confidence(string $mode): array {
    return match ($mode) {
        'normal' => ['label' => 'Normal', 'class' => 'confidence-normal'],
        'reduced' => ['label' => 'Reduced', 'class' => 'confidence-reduced'],
        'stale' => ['label' => 'Stale', 'class' => 'confidence-stale'],
        default => ['label' => 'Unavailable', 'class' => 'confidence-unavailable'],
    };
}

function hmi_cache_status(string $service, int $ttlSeconds): array {
    $cacheDir = realpath(__DIR__ . '/../var/cache/openagri');
    $latest = null;
    if ($cacheDir !== false) {
        foreach (glob($cacheDir . '/' . $service . '*') ?: [] as $file) {
            $mtime = filemtime($file);
            if ($latest === null || $mtime > $latest) {
                $latest = $mtime;
            }
        }
    }

    if ($latest === null) {
        return [
            'service' => $service,
            'status' => 'unavailable',
            'last_pull' => 'No local pull recorded',
            'age_seconds' => null,
            'confidence' => hmi_confidence('unavailable'),
        ];
    }

    $age = time() - $latest;
    $mode = $age <= $ttlSeconds ? 'normal' : ($age <= 259200 ? 'stale' : 'unavailable');
    return [
        'service' => $service,
        'status' => $mode,
        'last_pull' => gmdate('Y-m-d H:i', $latest) . ' UTC',
        'age_seconds' => $age,
        'confidence' => hmi_confidence($mode === 'stale' ? 'stale' : $mode),
    ];
}

function hmi_queue_count(): int {
    $queueDir = realpath(__DIR__ . '/../var/queue/openagri');
    if ($queueDir === false) {
        return 0;
    }
    return count(glob($queueDir . '/*.json') ?: []);
}

$allFarms = [];
try {
    $stmt = $pdo->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allFarms = [];
}

$farmId = $_SESSION['farm_id'] ?? ($allFarms[0]['id'] ?? null);
$farmName = $_SESSION['farm_name'] ?? ($allFarms[0]['farm_name'] ?? "Unknown Farm");

$latestData = [
    'soil_moisture' => 'N/A',
    'temperature' => 'N/A',
    'energy_solar' => 'N/A',
    'energy_wind' => 'N/A',
    'weather_forecast' => 'N/A',
    'rain_today' => 'N/A',
    'rain_tomorrow' => 'N/A',
    'wind_speed' => 'N/A',
];
$tasks = [];
$recommendations = [];
$sensors = [];
$performanceRows = [];

if ($farmId) {
    try {
        $stmt = $pdo->prepare("
            SELECT soil_moisture, temperature, energy_solar, energy_wind,
                   forecast AS weather_forecast, rain_today, rain_tomorrow, wind_speed
            FROM farm_measurements
            WHERE farm_id = :fid
            ORDER BY reading_time DESC
            LIMIT 1
        ");
        $stmt->execute(['fid' => $farmId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $latestData = array_merge($latestData, $row);
        }
    } catch (Exception $e) {}

    try {
        $stmt = $pdo->prepare("
            SELECT task_name, next_schedule, duration, extra_info, icon_class
            FROM task_schedules
            WHERE farm_id = :fid
            ORDER BY id ASC
        ");
        $stmt->execute(['fid' => $farmId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    try {
        $stmt = $pdo->prepare("
            SELECT title, description, reason, icon_class, created_at
            FROM ai_recommendations
            WHERE farm_id = :fid
            ORDER BY created_at DESC
            LIMIT 6
        ");
        $stmt->execute(['fid' => $farmId]);
        $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    try {
        $stmt = $pdo->prepare("
            SELECT sensor_name, sensor_type, node_count
            FROM farm_sensors
            WHERE farm_id = :fid
            ORDER BY id ASC
        ");
        $stmt->execute(['fid' => $farmId]);
        $sensors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    try {
        $stmt = $pdo->prepare("
            SELECT server_name, measure_time, cpu_usage, memory_usage, inbound_mbps, outbound_mbps, pods
            FROM server_performance
            WHERE farm_id = :fid
            ORDER BY measure_time DESC
            LIMIT 8
        ");
        $stmt->execute(['fid' => $farmId]);
        $performanceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

$soilMoistureNumber = hmi_percent_value($latestData['soil_moisture']);
$waterStressMode = ($soilMoistureNumber !== null && $soilMoistureNumber < 35) ? 'reduced' : 'normal';
$rainTomorrowNumber = hmi_percent_value($latestData['rain_tomorrow']);
$connectivityMode = hmi_queue_count() > 0 ? 'reduced' : 'normal';
$lastSyncTime = gmdate('Y-m-d H:i') . ' UTC';
$queueCount = hmi_queue_count();

$serviceStatuses = [
    'Gatekeeper' => hmi_cache_status('gatekeeper', 3600),
    'Weather' => hmi_cache_status('weather', 21600),
    'Irrigation' => hmi_cache_status('irrigation', 86400),
    'Pest/Disease' => hmi_cache_status('pest_disease', 86400),
    'FarmCalendar' => hmi_cache_status('farmcalendar', 900),
    'Reporting' => hmi_cache_status('reporting', 0),
];

$irrigationTask = null;
$fertiliserTask = null;
foreach ($tasks as $task) {
    $name = strtolower($task['task_name'] ?? '');
    if ($irrigationTask === null && str_contains($name, 'irrigation')) {
        $irrigationTask = $task;
    }
    if ($fertiliserTask === null && (str_contains($name, 'fertil') || str_contains($name, 'nitrogen'))) {
        $fertiliserTask = $task;
    }
}

$pestRecommendation = null;
$irrigationRecommendation = null;
$fertiliserRecommendation = null;
foreach ($recommendations as $recommendation) {
    $text = strtolower(($recommendation['title'] ?? '') . ' ' . ($recommendation['description'] ?? ''));
    if ($pestRecommendation === null && str_contains($text, 'pest')) {
        $pestRecommendation = $recommendation;
    }
    if ($irrigationRecommendation === null && str_contains($text, 'irrigation')) {
        $irrigationRecommendation = $recommendation;
    }
    if ($fertiliserRecommendation === null && (str_contains($text, 'fertil') || str_contains($text, 'nitrogen'))) {
        $fertiliserRecommendation = $recommendation;
    }
}

$dashboardState = [
    'farm' => ['id' => $farmId, 'name' => $farmName],
    'lastSyncTime' => $lastSyncTime,
    'queuedOperations' => $queueCount,
    'cards' => [
        'soilMoisture' => $latestData['soil_moisture'],
        'temperature' => $latestData['temperature'],
        'weather' => $latestData['weather_forecast'],
        'rainTomorrow' => $latestData['rain_tomorrow'],
    ],
    'serviceStatuses' => $serviceStatuses,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2f6f4e">
    <title>AgriTwin Farm Operator Dashboard</title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/hmi.css">
</head>
<body>
    <a class="skip-link" href="#main">Skip to dashboard</a>
    <div class="offline-banner" id="offlineBanner" role="status" aria-live="polite" hidden>
        Offline mode: showing the last saved farm state. New actions will be queued.
    </div>

    <header class="app-header">
        <div>
            <p class="eyebrow">Salamanca pilot operations</p>
            <h1><?php echo htmlspecialchars($farmName); ?></h1>
        </div>
        <div class="sync-summary" aria-label="Connectivity summary">
            <?php $connectivity = hmi_confidence($connectivityMode); ?>
            <span class="confidence-pill <?php echo $connectivity['class']; ?>"><?php echo $connectivity['label']; ?></span>
            <span id="lastSyncText">Last sync: <?php echo htmlspecialchars($lastSyncTime); ?></span>
            <span id="queueCountText">Queue: <?php echo $queueCount; ?></span>
        </div>
    </header>

    <div class="app-shell">
        <nav class="side-nav" aria-label="AgriTwin sections">
            <div class="brand-row">
                <img src="AgriTwin_logo.png" alt="AgriTwin">
                <strong>AgriTwin</strong>
            </div>
            <a href="#field-overview"><i class="fas fa-table-cells-large"></i> Field Overview</a>
            <a href="#digital-twin"><i class="fas fa-map-location-dot"></i> Digital Twin Map</a>
            <a href="#irrigation"><i class="fas fa-droplet"></i> Irrigation Schedule</a>
            <a href="#alerts"><i class="fas fa-triangle-exclamation"></i> Pest & Disease Alerts</a>
            <a href="#telemetry"><i class="fas fa-chart-line"></i> Sensor Telemetry</a>
            <a href="#openagri"><i class="fas fa-plug-circle-check"></i> OpenAgri Services</a>
            <a href="#calendar"><i class="fas fa-calendar-days"></i> Farm Calendar</a>
            <a href="#reports"><i class="fas fa-file-pdf"></i> Reports</a>
            <a href="#kpis"><i class="fas fa-gauge-high"></i> KPI Dashboard</a>
            <a href="#settings-connectivity"><i class="fas fa-wifi"></i> Settings / Connectivity</a>
            <form action="change_farm.php" method="POST" class="farm-switcher">
                <label for="farmSelect">Switch farm</label>
                <select name="farm_id" id="farmSelect" onchange="this.form.submit()">
                    <?php foreach ($allFarms as $farm): ?>
                        <option value="<?php echo $farm['id']; ?>" <?php if ($farm['id'] == $farmId) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($farm['farm_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </nav>

        <main id="main" class="main-panel">
            <section id="field-overview" class="view-section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Today</p>
                        <h2>Field Overview</h2>
                    </div>
                    <button class="secondary-action" type="button" id="refreshStateBtn"><i class="fas fa-rotate"></i> Save state offline</button>
                </div>

                <div class="priority-grid">
                    <article class="priority-card">
                        <?php $c = hmi_confidence($waterStressMode); ?>
                        <span class="confidence-pill <?php echo $c['class']; ?>"><?php echo $c['label']; ?></span>
                        <h3>Water stress</h3>
                        <p class="metric"><?php echo htmlspecialchars($latestData['soil_moisture']); ?></p>
                        <p>Soil moisture is the first operational signal. Keep current irrigation if moisture stays above the farm threshold.</p>
                    </article>
                    <article class="priority-card">
                        <?php $c = hmi_confidence($rainTomorrowNumber !== null && $rainTomorrowNumber > 40 ? 'reduced' : 'normal'); ?>
                        <span class="confidence-pill <?php echo $c['class']; ?>"><?php echo $c['label']; ?></span>
                        <h3>Irrigation recommendation</h3>
                        <p class="metric"><?php echo htmlspecialchars($irrigationRecommendation['description'] ?? 'Review next cycle'); ?></p>
                        <p>Inputs: sensors, weather forecast, OpenAgri Irrigation cache, FarmCalendar tasks.</p>
                    </article>
                    <article class="priority-card">
                        <?php $c = hmi_confidence($pestRecommendation ? 'reduced' : 'normal'); ?>
                        <span class="confidence-pill <?php echo $c['class']; ?>"><?php echo $c['label']; ?></span>
                        <h3>Pest risk</h3>
                        <p class="metric"><?php echo htmlspecialchars($pestRecommendation['title'] ?? 'No active high-risk alert'); ?></p>
                        <p>Inputs: weather, crop stage, OpenAgri Pest & Disease, field observations.</p>
                    </article>
                    <article class="priority-card">
                        <?php $c = hmi_confidence($fertiliserRecommendation ? 'normal' : 'unavailable'); ?>
                        <span class="confidence-pill <?php echo $c['class']; ?>"><?php echo $c['label']; ?></span>
                        <h3>Nitrogen / fertiliser</h3>
                        <p class="metric"><?php echo htmlspecialchars($fertiliserRecommendation['description'] ?? 'No new nitrogen advisory'); ?></p>
                        <p>Inputs: field notes, crop state, weather, and planned FarmCalendar operations.</p>
                    </article>
                    <article class="priority-card">
                        <?php $c = hmi_confidence($connectivityMode); ?>
                        <span class="confidence-pill <?php echo $c['class']; ?>"><?php echo $c['label']; ?></span>
                        <h3>Connectivity</h3>
                        <p class="metric" id="onlineStateText">Checking...</p>
                        <p>Queued operations: <strong id="queuedOperations"><?php echo $queueCount; ?></strong>. Last sync: <?php echo htmlspecialchars($lastSyncTime); ?>.</p>
                    </article>
                </div>
            </section>

            <section id="digital-twin" class="view-section split-section">
                <div>
                    <p class="eyebrow">Cesium</p>
                    <h2>Digital Twin Map / Overlays</h2>
                    <p>The detailed Cesium view remains available and includes parcel outlines, sensor markers, tree overlays, NDVI/LAI/SAVI layers, solar assets, and weather fetches.</p>
                    <div class="action-row">
                        <a class="primary-action" href="field.php"><i class="fas fa-map"></i> Open Cesium field view</a>
                        <a class="secondary-action" href="mobile.html"><i class="fas fa-mobile-screen"></i> Legacy mobile map</a>
                    </div>
                </div>
                <div class="map-placeholder" role="img" aria-label="Map preview placeholder">
                    <span>Digital Twin overlay summary</span>
                    <ul>
                        <li>EO/Copernicus vegetation layers</li>
                        <li>Sensor and actuator markers</li>
                        <li>Farm parcel context</li>
                    </ul>
                </div>
            </section>

            <section id="irrigation" class="view-section">
                <div class="section-heading">
                    <h2>Irrigation Schedule</h2>
                    <?php $c = hmi_confidence($irrigationTask ? 'normal' : 'unavailable'); ?>
                    <span class="confidence-pill <?php echo $c['class']; ?>"><?php echo $c['label']; ?></span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Task</th><th>Next schedule</th><th>Duration</th><th>Notes</th></tr></thead>
                        <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['next_schedule']); ?></td>
                                <td><?php echo htmlspecialchars($task['duration']); ?></td>
                                <td><?php echo htmlspecialchars($task['extra_info'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$tasks): ?><tr><td colspan="4">No tasks are loaded for this farm.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="alerts" class="view-section">
                <h2>Pest & Disease Alerts</h2>
                <div class="alert-list">
                    <?php if ($pestRecommendation): ?>
                        <article class="alert-card">
                            <span class="confidence-pill confidence-reduced">Reduced</span>
                            <h3><?php echo htmlspecialchars($pestRecommendation['title']); ?></h3>
                            <p><?php echo htmlspecialchars($pestRecommendation['description']); ?></p>
                            <p><strong>Why:</strong> <?php echo htmlspecialchars($pestRecommendation['reason'] ?? 'OpenAgri pest risk and local observations should be checked.'); ?></p>
                        </article>
                    <?php else: ?>
                        <article class="alert-card calm">
                            <span class="confidence-pill confidence-normal">Normal</span>
                            <h3>No urgent pest alert</h3>
                            <p>Keep routine scouting. If OpenAgri Pest & Disease is stale, refresh service data before treatment decisions.</p>
                        </article>
                    <?php endif; ?>
                </div>
            </section>

            <section id="telemetry" class="view-section">
                <h2>Sensor Telemetry</h2>
                <div class="telemetry-grid">
                    <div class="telemetry-card"><span>Soil moisture</span><strong><?php echo htmlspecialchars($latestData['soil_moisture']); ?></strong></div>
                    <div class="telemetry-card"><span>Temperature</span><strong><?php echo htmlspecialchars($latestData['temperature']); ?></strong></div>
                    <div class="telemetry-card"><span>Rain tomorrow</span><strong><?php echo htmlspecialchars($latestData['rain_tomorrow']); ?></strong></div>
                    <div class="telemetry-card"><span>Wind</span><strong><?php echo htmlspecialchars($latestData['wind_speed']); ?></strong></div>
                </div>
                <div class="chip-list">
                    <?php foreach ($sensors as $sensor): ?>
                        <span class="sensor-chip"><?php echo htmlspecialchars($sensor['sensor_name']); ?> · <?php echo htmlspecialchars($sensor['node_count']); ?> node(s)</span>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="openagri" class="view-section">
                <h2>OpenAgri Services Status</h2>
                <div class="service-grid">
                    <?php foreach ($serviceStatuses as $name => $status): ?>
                        <article class="service-card">
                            <span class="confidence-pill <?php echo $status['confidence']['class']; ?>"><?php echo $status['confidence']['label']; ?></span>
                            <h3><?php echo htmlspecialchars($name); ?></h3>
                            <p>Last pull: <?php echo htmlspecialchars($status['last_pull']); ?></p>
                            <p>Staleness: <?php echo $status['age_seconds'] === null ? 'unknown' : htmlspecialchars((string) round($status['age_seconds'] / 60)) . ' min'; ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="calendar" class="view-section">
                <h2>Farm Calendar / Operations</h2>
                <p>Accepted advisories should become FarmCalendar operations when the connector is configured. Current local tasks are shown below.</p>
                <div class="chip-list">
                    <?php foreach ($tasks as $task): ?>
                        <span class="operation-chip"><?php echo htmlspecialchars($task['task_name']); ?> · <?php echo htmlspecialchars($task['next_schedule']); ?></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="reports" class="view-section">
                <div class="section-heading">
                    <h2>Reports / PDF Advisories</h2>
                    <button class="primary-action" type="button" id="requestReportBtn"><i class="fas fa-file-export"></i> Prepare advisory export</button>
                </div>
                <p>Exports use the same JSON-LD-style advisory payload expected by the OpenAgri Reporting Service. If the reporting backend is unavailable, the browser keeps a safe local JSON export and offers print-to-PDF.</p>
                <pre id="reportPayloadPreview" class="payload-preview" tabindex="0"></pre>
            </section>

            <section id="kpis" class="view-section">
                <h2>KPI Dashboard</h2>
                <div class="kpi-grid">
                    <div><span>Irrigation latency</span><strong>Pending live KPI</strong></div>
                    <div><span>Data continuity</span><strong><?php echo $queueCount === 0 ? 'No queued sync' : $queueCount . ' queued'; ?></strong></div>
                    <div><span>Network volume</span><strong><?php echo htmlspecialchars((string) array_sum(array_column($performanceRows, 'inbound_mbps'))); ?> Mbps in</strong></div>
                    <div><span>CPU load</span><strong><?php echo $performanceRows ? htmlspecialchars((string) round(array_sum(array_column($performanceRows, 'cpu_usage')) / count($performanceRows), 1)) . '%' : 'N/A'; ?></strong></div>
                </div>
            </section>

            <section id="settings-connectivity" class="view-section">
                <h2>Settings / Connectivity</h2>
                <div class="action-row">
                    <a class="secondary-action" href="settings.php"><i class="fas fa-gear"></i> Open settings</a>
                    <button class="secondary-action" type="button" id="clearOfflineStateBtn"><i class="fas fa-trash"></i> Clear offline cache</button>
                </div>
                <p>Do not enter production secrets into shared screenshots or issue reports. Use environment variables and the connector configuration documented in `docs/integration/openagri_connectors.md`.</p>
            </section>

            <footer class="footer">
                <img src="AgriTwin_logo.png" alt="AgriTwin">
                <img src="OpenAgri.png" alt="OpenAgri">
                <img src="eufunded.png" alt="EU Funded">
                <span>AgriTwin Smart Farming · OpenAgri pilot interface</span>
            </footer>
        </main>
    </div>

    <script id="dashboardState" type="application/json"><?php echo json_encode($dashboardState, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
    <script src="assets/hmi.js" defer></script>
</body>
</html>
