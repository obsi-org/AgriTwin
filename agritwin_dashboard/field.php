<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

// field.php
session_start();

// If the user is not logged in, redirect to the login page in the root
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Determine the selected farm from session
$farmId = $_SESSION['farm_id'] ?? null;
$supportedLangs = ['en', 'pt-PT', 'es-ES'];
$lang = $_GET['lang'] ?? ($_SESSION['agritwin_lang'] ?? ($_COOKIE['agritwin_lang'] ?? ''));
$langQuery = is_string($lang) && in_array($lang, $supportedLangs, true) ? '?lang=' . rawurlencode($lang) : '';

// Example: farm identifiers are publication-safe operational records for publication.
// Adjust these IDs according to your actual `farms` table records.
if ($farmId == 1) {
    // The Greek property => Go to field1.php
    header("Location: field1.php" . $langQuery);
    exit();
} elseif ($farmId == 2) {
    // The Spanish property => Go to field2.php
    header("Location: field2.php" . $langQuery);
    exit();
} elseif ($farmId == 3) {
    // The Spanish property => Go to field2.php
    header("Location: field3.php" . $langQuery);
    exit();
} else {
    // If no farm selected or unknown ID, just fallback (could be a 404 or default)
    echo "No valid farm selected.";
    // or redirect somewhere else, e.g. the dashboard:
    // header("Location: index.php");
}
?>
