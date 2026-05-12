<?php
// field.php
session_start();

// If the user is not logged in, redirect to the login page in the root
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Determine the selected farm from session
$farmId = $_SESSION['farm_id'] ?? null;

// Example: Suppose farm_id=1 is Agia (Greece), farm_id=2 is Fotovoltaica Macotera (Spain).
// Adjust these IDs according to your actual `farms` table records.
if ($farmId == 1) {
    // The Greek property => Go to field1.php
    header("Location: field1.php");
    exit();
} elseif ($farmId == 2) {
    // The Spanish property => Go to field2.php
    header("Location: field2.php");
    exit();
} elseif ($farmId == 3) {
    // The Spanish property => Go to field2.php
    header("Location: field3.php");
    exit();
} else {
    // If no farm selected or unknown ID, just fallback (could be a 404 or default)
    echo "No valid farm selected.";
    // or redirect somewhere else, e.g. the dashboard:
    // header("Location: index.php");
}
?>
