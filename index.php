<?php
// login.php
session_start();
require_once __DIR__ . "/agritwin_dashboard/includes/hmi_data.php";

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: agritwin_dashboard/index.php");
    exit();
}

// Check if form is submitted
$error = "";

// Include DB connection
require_once "./agritwin_dashboard/db_config.php";
require_once __DIR__ . "/db_init.php";

// 1. Fetch the list of farms for the dropdown
try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException($agritwin_db_error ?? 'Database unavailable');
    }
    $stmt = $pdo->query("SELECT id, farm_name FROM farms ORDER BY farm_name ASC");
    $allFarms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle DB error (e.g., table doesn't exist)
    $allFarms = [];
    $error = agritwin_t('db_status') . ': ' . agritwin_t('unavailable');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";
    $selectedFarmId = $_POST['farm_id'] ?? ""; // Capture selected farm from the form

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $error = agritwin_t('db_status') . ': ' . agritwin_t('unavailable');
    } elseif (!empty($username) && !empty($password)) {
        // Prepare statement to select user
        $stmt = $pdo->prepare("SELECT id, username, password, role, preferred_language, enabled FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && (int) ($user['enabled'] ?? 1) === 1) {
            // Verify the password using password_verify
            if (password_verify($password, $user['password'])) {
                // Store user ID in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'viewer';
                $_SESSION['agritwin_lang'] = $user['preferred_language'] ?? agritwin_current_language();
                $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $user['id']]);

                // Store the chosen farm in session
                // (You can fetch the farm_name from DB if you want the name in session as well.)
                $_SESSION['farm_id'] = $selectedFarmId;

                // Optionally fetch and store the farm_name
                if (!empty($selectedFarmId)) {
                    $farmStmt = $pdo->prepare("SELECT farm_name FROM farms WHERE id = :fid");
                    $farmStmt->execute(['fid' => $selectedFarmId]);
                    $farmData = $farmStmt->fetch(PDO::FETCH_ASSOC);
                    if ($farmData) {
                        $_SESSION['farm_name'] = $farmData['farm_name'];
                    }
                }

                // Redirect to dashboard
                header("Location: agritwin_dashboard/index.php");
                exit();
            } else {
                $error = agritwin_t('invalid_password');
            }
        } else {
            $error = agritwin_t('user_not_found');
        }
    } else {
        $error = agritwin_t('username_password_required');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(agritwin_current_language()) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(agritwin_t('login')) ?> - AgriTwin</title>
    <style>
        /* Reset and Body Setup */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            background: #F1F8E9;
            min-height: 100vh; /* Ensures footer sits at bottom if content is small */
        }

        /* Top Bar (matches dashboard style) */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #4CAF50;
            color: white;
            padding: 10px;
        }
        .language-login {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .top-bar h1 {
            margin: 0;
            font-size: 22px;
        }
        .top-bar span {
            font-size: 16px;
        }

        /* Main Content Wrapper */
        .content-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Login Container */
        .login-container {
            background: #FFFFFF;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 280px;
        }
        .login-container h2 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            color: #2E7D32; /* A darker green for emphasis */
        }
        .login-container label {
            display: block;
            margin: 0 0 5px 0;
        }
        .login-container input[type="text"],
        .login-container input[type="password"],
        .login-container select {
            font-size: 14px;
            width: 100%;
            max-width: 250px;
            padding: 6px 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container input[type="submit"] {
            width: 100%;
            padding: 8px;
            background: #2E7D32;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container input[type="submit"]:hover {
            background: #1B5E20; /* Slightly darker on hover */
        }
        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Footer Section (matches dashboard style) */
        .footer {
            text-align: center;
            background: #FFFFFF;
            color: #000000;
            padding: 16px;
        }
        .logo-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        .logo-section img {
            max-height: 92px;
            max-width: 220px;
            object-fit: contain;
        }
        .logo-section img.partner-logo {
            max-height: 112px;
            max-width: 260px;
        }
    </style>
</head>
<body>
    <!-- Top Bar (similar to Dashboard) -->
    <div class="top-bar">
        <h1>AgriTwin - <?= htmlspecialchars(agritwin_t('app_tagline')) ?></h1>
        <form class="language-login" method="get">
            <label for="lang"><?= htmlspecialchars(agritwin_t('language')) ?></label>
            <select id="lang" name="lang" onchange="this.form.submit()">
                <?php foreach (agritwin_supported_languages() as $code => $language): ?>
                    <option value="<?= htmlspecialchars($code) ?>" <?= agritwin_current_language() === $code ? 'selected' : '' ?>><?= htmlspecialchars($language['flag'] . ' ' . $language['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Content Wrapper: Centers the Login Box -->
    <div class="content-wrapper">
        <div class="login-container">
            <h2><?= htmlspecialchars(agritwin_t('login_access')) ?></h2>

            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <label for="username"><?= htmlspecialchars(agritwin_t('username')) ?>:</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    placeholder="<?= htmlspecialchars(agritwin_t('username')) ?>"
                    autocomplete="off"
                />

                <label for="password"><?= htmlspecialchars(agritwin_t('password')) ?>:</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="<?= htmlspecialchars(agritwin_t('password')) ?>"
                />

                <!-- New: Farm Selection Dropdown -->
                <label for="farm_id"><?= htmlspecialchars(agritwin_t('select_farm')) ?>:</label>
                <select name="farm_id" id="farm_id">
                    <?php if (empty($allFarms)): ?>
                        <option value=""><?= htmlspecialchars(agritwin_t('no_farms_found')) ?></option>
                    <?php else: ?>
                        <?php foreach ($allFarms as $farm): ?>
                            <option value="<?php echo $farm['id']; ?>">
                                <?php echo htmlspecialchars($farm['farm_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <input type="submit" value="<?= htmlspecialchars(agritwin_t('login')) ?>">
            </form>
        </div>
    </div>

    <!-- Footer (matches dashboard style) -->
    <div class="footer">
        <div class="logo-section">
        <img src="AgriTwin_logo.png" alt="AgriTwin Logo">
        <img class="partner-logo" src="fotomaco.png" alt="Fotovoltaica Macotera SL Logo">
        <img class="partner-logo" src="obsidian.png" alt="Obsidian Innovation Institute / OBSIDIAN Logo">
        <img src="OpenAgri.png" alt="OpenAgri Logo">
        <img src="eufunded.png" alt="EU Funded">
        </div>
        <p><?= htmlspecialchars(agritwin_t('footer_rights')) ?></p>
    </div>
</body>
</html>
