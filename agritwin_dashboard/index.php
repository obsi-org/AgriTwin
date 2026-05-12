<?php
// dashboard.php
session_start();

// If the user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database config for PDO connection
require_once "db_config.php";

// 1. Fetch all farms for the drop-down
try {
    $stmt = $pdo->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // In production, you might log this error
    $allFarms = [];
}

// Determine currently selected farm from session
$farmId   = $_SESSION['farm_id']   ?? null;
$farmName = $_SESSION['farm_name'] ?? "Unknown Farm";

// Default placeholders
$soilMoisture    = "N/A";
$temperature     = "N/A";
$energySolar     = "N/A";
$energyWind      = "N/A";
$weatherForecast = "N/A";
$rainToday       = "N/A";
$rainTomorrow    = "N/A";
$windInfo        = "N/A";

// 2. Load data for the selected farm
if ($farmId) {
    try {
        $sql = "
            SELECT 
                soil_moisture,
                temperature,
                energy_solar,
                energy_wind,
                forecast AS weather_forecast,
                rain_today,
                rain_tomorrow,
                wind_speed
            FROM farm_measurements
            WHERE farm_id = :fid
            ORDER BY reading_time DESC
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fid' => $farmId]);
        $latestData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($latestData) {
            $soilMoisture    = $latestData['soil_moisture']      ?? "N/A";
            $temperature     = $latestData['temperature']        ?? "N/A";
            $energySolar     = $latestData['energy_solar']       ?? "N/A";
            $energyWind      = $latestData['energy_wind']        ?? "N/A";
            $weatherForecast = $latestData['weather_forecast']   ?? "N/A";
            $rainToday       = $latestData['rain_today']         ?? "N/A";
            $rainTomorrow    = $latestData['rain_tomorrow']      ?? "N/A";
            $windInfo        = $latestData['wind_speed']         ?? "N/A";
        }
    } catch (Exception $e) {
        // Handle query errors
        // echo "Error fetching farm data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AGRITWIN Smart Farming Dashboard</title>
    <!-- Including Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background: #2E7D32;
            color: white;
            padding: 20px;
            height: 100vh;
            box-sizing: border-box;
        }
        .sidebar h2 {
            text-align: center;
            margin-top: 0;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px; /* for spacing above the drop-down */
        }
        .sidebar ul li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar ul li a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
        }
        .sidebar ul li a:hover {
            background: #1B5E20;
        }

        /* Farm selection box container */
        .farm-select-container label {
            display: block;
            margin-bottom: 5px;
        }
        .farm-select-container select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: #F1F8E9;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #4CAF50;
            color: white;
            padding: 10px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            flex: 1;
        }
        .card {
            background: white;
            padding: 10px;
            box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 20px;
        }
        .card h3 {
            margin: 0;
            font-size: 22px;
        }
        .card p {
            margin: 5px 0 0;
        }
        .footer {
            text-align: center;
            background: #FFFFFF;
            color: #000000;
            padding: 10px;
            margin-top: 20px;
        }
        .logo-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        .logo-section img {
            height: 50px;
        }
        .icon {
            margin: 0 8px;
            color: #4CAF50;
            font-size: 36px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>AGRITWIN Dashboard</h2>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="field.php"><i class="fas fa-seedling"></i> Field Monitoring</a></li>
            <li><a href="task_scheduling.php"><i class="fas fa-tasks"></i> Task Scheduling</a></li>
            <li><a href="ai_recommendations.php"><i class="fas fa-brain"></i> AI Recommendations</a></li>
            <li><a href="energy.php"><i class="fas fa-solar-panel"></i> Energy Management</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="user_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <!-- Logout link -->
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

        <!-- Farm Selector at the bottom of the sidebar -->
        <div class="farm-select-container">
            <form action="change_farm.php" method="POST">
                <label for="farmSelect">Switch Farm:</label>
                <select name="farm_id" id="farmSelect" onchange="this.form.submit()">
                    <option value="">-- Choose a Farm --</option>
                    <?php foreach ($allFarms as $farm): ?>
                        <option 
                            value="<?php echo $farm['id']; ?>"
                            <?php if ($farm['id'] == $farmId) echo 'selected'; ?>
                        >
                            <?php echo htmlspecialchars($farm['farm_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><?php echo htmlspecialchars($farmName); ?> - Environmental Digital Twin</h1>
            <span>Real-time Farm Insights</span>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <!-- Soil Moisture -->
            <div class="card">
                <h3>Soil Moisture <i class="fas fa-tint icon"></i></h3>
                <p><?php echo htmlspecialchars($soilMoisture); ?></p>
            </div>
            
            <!-- Temperature -->
            <div class="card">
                <h3>Temperature <i class="fas fa-thermometer-half icon"></i></h3>
                <p><?php echo htmlspecialchars($temperature); ?></p>
            </div>

            <!-- Energy Levels -->
            <div class="card">
                <h3>Energy Levels <i class="fas fa-bolt icon"></i></h3>
                <p>Solar: <?php echo htmlspecialchars($energySolar); ?> | Wind: <?php echo htmlspecialchars($energyWind); ?></p>
            </div>

            <!-- Weather Forecast -->
            <div class="card">
                <h3>Weather Forecast <i class="fas fa-cloud-sun icon"></i></h3>
                <p><strong>Next 3 days:</strong> <?php echo htmlspecialchars($weatherForecast); ?></p>
            </div>

            <!-- Rainfall Probability -->
            <div class="card">
                <h3>Rainfall Probability <i class="fas fa-cloud-rain icon"></i></h3>
                <p>Today: <?php echo htmlspecialchars($rainToday); ?><br>Tomorrow: <?php echo htmlspecialchars($rainTomorrow); ?></p>
            </div>

            <!-- Wind Speed -->
            <div class="card">
                <h3>Wind Speed <i class="fas fa-wind icon"></i></h3>
                <p><?php echo htmlspecialchars($windInfo); ?></p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
        <div class="logo-section">
        <img src="AgriTwin_logo.png" alt="agritwin Logo">
        <img src="obsidian.png" alt="Obsidian Logo">
        <img src="fotomaco.png" alt="FOTOMACO Logo">
        <img src="OpenAgri.png" alt="OpenAgri Logo">
        <img src="eufunded.png" alt="EU Funded">
        </div>
        <p>© 2025 AgriTwin Smart Farming | Powered by OpenAgri</p>
    </div>
</body>
</html>
