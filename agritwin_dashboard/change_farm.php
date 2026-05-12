<?php
// change_farm.php
session_start();
require_once "db_config.php";

if (isset($_POST['farm_id']) && !empty($_POST['farm_id'])) {
    $newFarmId = $_POST['farm_id'];

    // Validate that this farm actually exists
    $stmt = $pdo->prepare("SELECT id, farm_name FROM farms WHERE id = :fid");
    $stmt->execute(['fid' => $newFarmId]);
    $farmRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($farmRow) {
        // Update the session with the new farm ID and name
        $_SESSION['farm_id']   = $farmRow['id'];
        $_SESSION['farm_name'] = $farmRow['farm_name'];
    }
}

// Redirect back to the referring page or a default
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
