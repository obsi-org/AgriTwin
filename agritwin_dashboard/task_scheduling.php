<?php
// task_scheduling.php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include DB config for PDO
require_once "db_config.php";

/*
  1. Fetch all farms for the farm selector
*/
try {
    $stmt = $pdo->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allFarms = [];
    // In production, you might log this error rather than display
    // echo "Error loading farms: " . $e->getMessage();
}

// Determine currently selected farm from session
$farmId   = $_SESSION['farm_id']   ?? null;
$farmName = $_SESSION['farm_name'] ?? "Synthetic Demo Farm";

/*
  2. Load Task Scheduling data from 'task_schedules' table for the chosen farm
*/
$tasks = [];
if ($farmId) {
    try {
        // Suppose table 'task_schedules' has columns:
        // id, farm_id, task_name, next_schedule, duration, extra_info, icon_class
        $sql = "
            SELECT
                task_name,
                next_schedule,
                duration,
                extra_info,
                icon_class
            FROM task_schedules
            WHERE farm_id = :fid
            ORDER BY id ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fid' => $farmId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Handle query error
        // echo "Error fetching tasks: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GAIA Task Scheduling</title>

    <!-- Font Awesome (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Matching your new dashboard layout */
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

        /* Farm Selector below menu */
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

        /* Top bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #4CAF50;
            color: white;
            padding: 10px;
        }

        /* Task Grid */
        .task-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr; /* 3 columns */
            gap: 20px;
            margin-top: 20px;
        }

        /* Task Card */
        .task-card {
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        /* Icon styling */
        .task-icon {
            display: block;
            font-size: 50px;
            margin-bottom: 8px;
        }
        /* Colour-coded classes */
        .irrigation-icon {
            color: #42A5F5; /* Blue for irrigation */
        }
        .fertiliser-icon {
            color: #FFA726; /* Orange for fertilisation */
        }
        /* Use 'fa-helicopter' to represent drone tasks in free icons */
        .drone-icon {
            color: #9E9E9E; /* Grey for drone surveillance */
        }

        .task-card button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            margin-right: 5px;
        }
        .task-card button:hover {
            background: #388E3C;
        }

        /* Footer and Logos */
        .footer {
            text-align: center;
            background: #FFFFFF;
            color: #000000;
            padding: 10px;
            margin-top: auto; /* Push footer down */
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

    <!-- Sidebar (matching dashboard) -->
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
            <h1><?php echo htmlspecialchars($farmName); ?> - Task Scheduling</h1>
            <span>Manage and Optimise Farm Operations</span>
        </div>

        <!-- Task Grid -->
        <div class="task-grid">
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <!-- Show the icon_class from DB or fallback to 'fa-helicopter drone-icon' -->
                        <i class="task-icon 
                            <?php echo htmlspecialchars($task['icon_class'] ?? 'fas fa-helicopter drone-icon'); ?>">
                        </i>
                        <h3><?php echo htmlspecialchars($task['task_name']); ?></h3>
                        <p>Next Schedule: <?php echo htmlspecialchars($task['next_schedule']); ?></p>
                        <p>Duration: <?php echo htmlspecialchars($task['duration']); ?></p>
                        <p><?php echo htmlspecialchars($task['extra_info']); ?></p>
                        <button>Modify</button>
                        <button>Cancel</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No scheduled tasks for this farm.</p>
            <?php endif; ?>
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
