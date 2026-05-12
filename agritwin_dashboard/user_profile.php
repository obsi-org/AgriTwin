<?php
// user_profile.php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include DB config
require_once "db_config.php";

/*
  1. Fetch all farms for the farm selector
*/
try {
    $stmt = $pdo->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allFarms = [];
    // In production, you might log this error
    // echo "Error loading farms: " . $e->getMessage();
}

// Determine currently selected farm from session
$farmId   = $_SESSION['farm_id']   ?? null;
$farmName = $_SESSION['farm_name'] ?? "Unknown Farm";

// Retrieve user information from DB
// e.g., SELECT name, email, role, location FROM users WHERE id = :uid
$userId = $_SESSION['user_id'] ?? null;
$currentUserName  = "Pedro Branco";
$currentUserEmail = "pedro.branco@xilbi.com";
$userRole         = "System Administrator"; // Example role
$userLocation     = "Spain"; // Example location
$message          = "";

// If you have a real users table with role/location columns, do something like:
// $stmtUser = $pdo->prepare("SELECT name, email, role, location FROM users WHERE id = :uid");
// $stmtUser->execute(['uid' => $userId]);
// $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
// if ($userRow) { ... }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In real code, you'd retrieve posted values, validate, and update your DB.
    // For example:
    /*
    $newName     = $_POST['display_name'] ?? "";
    $newEmail    = $_POST['email'] ?? "";
    $newPassword = $_POST['password'] ?? "";
    $confirmPass = $_POST['confirm_password'] ?? "";
    $newLocation = $_POST['location'] ?? "";
    // $newRole   = ... (if you want to let the user change roles)

    // Validate input, check password match, then hash password & update DB.
    // e.g.:
    // if ($newPassword !== $confirmPass) { $message = "Passwords do not match!"; }
    // else { ... update users set name=?, email=?, password=? where id=? ... }

    $message = "Profile updated successfully!";
    */
    $message = "Profile updated (example only, no DB code).";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GAIA - My Profile</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Matching the GAIA Task Scheduling page styling */
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
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px; /* for spacing above farm selector */
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

        /* Farm Selector */
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

        /* Profile Form Container */
        .profile-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px;
        }
        .profile-container h2 {
            margin: 0 0 15px 0;
        }
        .profile-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .profile-container input[type="text"],
        .profile-container input[type="email"],
        .profile-container input[type="password"] {
            width: 100%;
            max-width: 500px;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .profile-container input[type="submit"] {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .profile-container input[type="submit"]:hover {
            background: #388E3C;
        }
        .profile-message {
            margin: 10px 0;
            color: green;
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

            <!-- Insert My Profile link HERE (before logout) -->
            <li><a href="user_profile.php"><i class="fas fa-user"></i> My Profile</a></li>

            <!-- Logout link -->
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>

        <!-- Farm Selector below menu -->
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
            <h1>User Profile Management</h1>
            <span>Update Personal Information & Password</span>
        </div>

        <div class="profile-container">
            <h2>Your Profile</h2>

            <!-- Display success or error messages -->
            <?php if (!empty($message)): ?>
                <p class="profile-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" 
                       value="<?php echo htmlspecialchars($currentUserName); ?>">

                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($currentUserEmail); ?>">

                <label for="role">Role (read-only)</label>
                <input type="text" id="role" name="role" 
                       value="<?php echo htmlspecialchars($userRole); ?>" readonly>

                <label for="location">Location (e.g., Spain)</label>
                <input type="text" id="location" name="location"
                       value="<?php echo htmlspecialchars($userLocation); ?>">

                <hr>

                <label for="password">New Password (leave blank to keep existing)</label>
                <input type="password" id="password" name="password" placeholder="Enter new password">

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">

                <input type="submit" value="Save Changes">
            </form>
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
