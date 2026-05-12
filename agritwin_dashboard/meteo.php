<?php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// The municipality code for Nava de Sotrobal (Salamanca) is 37219.
$municipio = '37219';

// Your AEMET API key
$apiKey = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJwYkBwZWRyb2JyYW5jby5jb20iLCJqdGkiOiJlNDI3M2ZhMS1kMTNlLTQ2N2YtYjk2ZC04ZWFiMmEyZjY2NWMiLCJpc3MiOiJBRU1FVCIsImlhdCI6MTY2MDMzMTk2NCwidXNlcklkIjoiZTQyNzNmYTEtZDEzZS00NjdmLWI5NmQtOGVhYjJhMmY2NjVjIiwicm9sZSI6IiJ9.OaYo6yz_-kTWdCm2TkyL7Lsvy02eE01lMl6etZbIKWk';

// Daily forecast endpoint for the municipality
$endpoint = 'https://opendata.aemet.es/opendata/api/prediccion/especifica/municipio/diaria/' . $municipio . '/';
// Append ?api_key=
$url = $endpoint . '?api_key=' . $apiKey;

// 1) First request to get the "datos" URL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$initialResponse = curl_exec($ch);
curl_close($ch);

$data = json_decode($initialResponse, true);

// AEMET typically sends a JSON containing a "datos" field that points to the real data URL
if (isset($data['datos'])) {
    $meteoDataUrl = $data['datos'];

    // 2) Second request: fetch actual forecast JSON
    $ch2 = curl_init($meteoDataUrl);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    $forecastJson = curl_exec($ch2);
    curl_close($ch2);

    // Output as JSON for front-end consumption
    header('Content-Type: application/json');
    echo $forecastJson;
} else {
    // If something fails or the structure changes
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Could not retrieve "datos" field from AEMET response',
        'raw_response' => $data
    ]);
}
