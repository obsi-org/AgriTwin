<?php
// ai_recommendations.php
session_start();

// If user is not logged in, redirect to login (index.php in root)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Include database config for PDO connection
require_once "db_config.php";

/*
  1. Fetch all farms for the farm selector
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
  2. Load AI recommendations for the selected farm.
     E.g. from a table 'ai_recommendations' with columns like:
     id, farm_id, icon_class, title, description, reason, created_at, ...
*/
$recommendations = [];
if ($farmId) {
    try {
        // Example query: adjust column names / table name as needed
        $sql = "
            SELECT
                icon_class,
                title,
                description,
                reason
            FROM ai_recommendations
            WHERE farm_id = :fid
            ORDER BY created_at DESC
            LIMIT 3
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fid' => $farmId]);
        $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // In production, log or handle error
        // echo "Error fetching AI recommendations: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgriTwin AI Recommendations</title>

    <!-- Including Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Overall Page Style (matching your second snippet) */
        body {
            font-family: Arial, sans-serif;
            display: flex; /* Sidebar + main-content side by side */
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background: #2E7D32;
            color: white;
            padding: 20px;
            height: 100vh; /* Keep sidebar at full viewport height */
            box-sizing: border-box;
        }
        .sidebar h2 {
            text-align: center;
            margin-top: 0;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px; /* spacing for the farm selector */
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

        /* Farm Selector Container (below the menu) */
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

        /* Main Content - flex column so footer can get margin-top: auto */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            background: #F1F8E9;
        }

        /* Top Bar (matching snippet) */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #4CAF50;
            color: white;
            padding: 10px;
        }

        /* AI Recommendation Grid */
        .recommendation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr; /* 3 columns */
            gap: 20px;
            margin-top: 20px;
        }

        /* Recommendation Card */
        .recommendation-card {
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        /* Icon styling for each recommendation card */
        .recommendation-icon {
            display: block;
            font-size: 50px;
            margin-bottom: 8px;
        }
        /* Colour-coded icons for each card */
        .irrigation-icon {
            color: #42A5F5; /* Blue for irrigation */
        }
        .fertiliser-icon {
            color: #FFA726; /* Orange for fertiliser */
        }
        .pest-icon {
            color: #F44336; /* Red for pest control */
        }

        /* Recommendation card buttons */
        .recommendation-card button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            margin-right: 5px;
        }
        .recommendation-card button:hover {
            background: #388E3C;
        }

        /* Footer & Logos (matching snippet) */
        .footer {
            text-align: center;
            background: #FFFFFF;
            color: #000000;
            padding: 10px;
            margin-top: auto; /* push footer to bottom if not enough content */
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
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>AgriTwin Dashboard</h2>
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
            <h1><?php echo htmlspecialchars($farmName); ?> - AI Recommendations</h1>
            <span>Optimise Farm Operations with AI</span>
        </div>

        <!-- Recommendation Grid -->
        <div class="recommendation-grid">
            <?php if (!empty($recommendations)): ?>
                <?php foreach ($recommendations as $recommend): ?>
                    <div class="recommendation-card">
                        <i class="recommendation-icon 
                           <?php echo htmlspecialchars($recommend['icon_class'] ?? 'fas fa-robot'); ?>">
                        </i>
                        <h3><?php echo htmlspecialchars($recommend['title'] ?? 'AI Recommendation'); ?></h3>
                        <p><?php echo htmlspecialchars($recommend['description'] ?? 'No details'); ?></p>
                        <p>Reason: <?php echo htmlspecialchars($recommend['reason'] ?? 'N/A'); ?></p>
                        <button>Apply</button>
                        <button>Dismiss</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- If no recommendations found -->
                <div class="recommendation-card">
                    <i class="recommendation-icon fas fa-robot"></i>
                    <h3>No AI Recommendations</h3>
                    <p>No suggestions are currently available for this farm.</p>
                </div>
            <?php endif; ?>
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
