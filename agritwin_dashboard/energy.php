<?php
// energy.php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Include database config for PDO connection
require_once "db_config.php";

/*
  1. Fetch all farms for the farm selector (like your other pages)
*/
try {
    $stmt = $pdo->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allFarms = [];
    // In production, log this error rather than displaying
    // echo "Error loading farms: " . $e->getMessage();
}

// Determine currently selected farm from session
$farmId   = $_SESSION['farm_id']   ?? null;
$farmName = $_SESSION['farm_name'] ?? "Synthetic Demo Farm";

/*
  2. Load energy data for the selected farm, from an example 'energy_readings' table
*/
$solarOutput     = "N/A";
$solarUsage      = "N/A";
$windOutput      = "N/A";
$windUsage       = "N/A";
$batteryCharge   = "N/A";
$batteryCapacity = "N/A";

if ($farmId) {
    try {
        $sql = "
            SELECT
                solar_output,
                solar_usage,
                wind_output,
                wind_usage,
                battery_charge,
                battery_capacity
            FROM energy_readings
            WHERE farm_id = :fid
            ORDER BY reading_time DESC
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fid' => $farmId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $solarOutput     = $row['solar_output'];
            $solarUsage      = $row['solar_usage'];
            $windOutput      = $row['wind_output'];
            $windUsage       = $row['wind_usage'];
            $batteryCharge   = $row['battery_charge'];
            $batteryCapacity = $row['battery_capacity'];
        }
    } catch (Exception $e) {
        // In production, log or handle error
        // echo "Error fetching energy data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GAIA Energy Management</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Overall Layout */
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
        }
        .sidebar h2 {
            text-align: center;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px; /* spacing for farm selector */
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

        /* Farm Selector below the menu */
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

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #4CAF50;
            color: white;
            padding: 10px;
        }

        /* Energy Grid */
        .energy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        /* Energy Card */
        .energy-card {
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        /* Icon styling */
        .card-icon {
            display: block;
            font-size: 50px;
            margin-bottom: 8px;
        }
        /* Different icon colours for each card */
        .solar-icon {
            color: #FFC107; /* Bright yellow for Solar */
        }
        .wind-icon {
            color: #2196F3; /* Blue for Wind */
        }
        .battery-icon {
            color: #4CAF50; /* Green for Battery */
        }

        .energy-card button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            margin-right: 5px;
        }
        .energy-card button:hover {
            background: #388E3C;
        }

        /* Footer and Logos */
        .footer {
            text-align: center;
            background: #FFFFFF;
            color: #000000;
            padding: 10px;
            margin-top: auto; /* Push footer to bottom */
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
    </style>
</head>
<body>
    <!-- Sidebar -->
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

            <!-- Logout link -->
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

        <!-- Farm Selector below the menu -->
        <div class="farm-select-container">
            <label for="farmSelect">Switch Farm:</label>
            <form action="change_farm.php" method="POST">
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
        <div class="top-bar">
            <h1><?php echo htmlspecialchars($farmName); ?> - Energy Management</h1>
            <span>Optimise Farm Energy Usage</span>
        </div>

        <!-- Energy Cards -->
        <div class="energy-grid">
            <!-- Solar Energy Card -->
            <div class="energy-card">
                <i class="fas fa-sun card-icon solar-icon" aria-hidden="true"></i>
                <h3>Solar Energy</h3>
                <p>Current Output: <?php echo htmlspecialchars($solarOutput); ?></p>
                <p>Usage: <?php echo htmlspecialchars($solarUsage); ?></p>
                <button>Optimise</button>
            </div>

            <!-- Wind Energy Card -->
            <div class="energy-card">
                <i class="fas fa-wind card-icon wind-icon" aria-hidden="true"></i>
                <h3>Wind Energy</h3>
                <p>Current Output: <?php echo htmlspecialchars($windOutput); ?></p>
                <p>Usage: <?php echo htmlspecialchars($windUsage); ?></p>
                <button>Optimise</button>
            </div>

            <!-- Battery Storage Card -->
            <div class="energy-card">
                <i class="fas fa-battery-full card-icon battery-icon" aria-hidden="true"></i>
                <h3>Battery Storage</h3>
                <p>Charge Level: <?php echo htmlspecialchars($batteryCharge); ?></p>
                <p>Capacity: <?php echo htmlspecialchars($batteryCapacity); ?></p>
                <button>Distribute</button>
            </div>
        </div>

        <!-- Footer -->
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
