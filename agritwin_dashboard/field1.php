<?php
session_start();

// If user is not logged in, redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include DB connection
require_once "db_config.php";

// The Spanish farm is farm_id=1
$spainFarmId = 1;


// 2a) Cesium token (global setting) ---------------------------------------
try {
    $stmtTok = $pdo->prepare("SELECT value FROM app_settings WHERE name = 'cesium_token' LIMIT 1");
    $stmtTok->execute();
    $cesiumToken = $stmtTok->fetchColumn() ?: '';
} catch (Exception $e) {
    $cesiumToken = '';
    /* In production, log error */
}

/* Copernicus ID (global setting) --------------------------------------- */
try {
    $stmtCop = $pdo->prepare(
        "SELECT value FROM app_settings WHERE name = 'copernicus_id' LIMIT 1"
    );
    $stmtCop->execute();
    $copernicusId = $stmtCop->fetchColumn() ?: '';
} catch (Exception $e) {
    $copernicusId = '';
    // Optionally: error_log($e->getMessage());
}

// Default placeholders for demonstration
$dbSoilMoisture = "N/A";
$dbTemperature  = "N/A";
$dbHumidity     = "N/A";

// 1) Fetch the latest measurement from DB
try {
    $sql = "
        SELECT soil_moisture, temperature, forecast
        -- add humidity column if you store it separately
        FROM farm_measurements
        WHERE farm_id = :fid
        ORDER BY reading_time DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['fid' => $spainFarmId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $dbSoilMoisture = $row['soil_moisture'] ?? "N/A"; // e.g. "42%"
        $dbTemperature  = $row['temperature']  ?? "N/A"; // e.g. "10°C"
        // If you have a separate humidity column, map it here
        // $dbHumidity     = $row['humidity'] ?? "N/A";
        $dbHumidity = "45%"; // fallback
    }
} catch (Exception $e) {
    // In production, handle/log the error
    // echo "DB error: " . $e->getMessage();
}

// Everything else remains your original code structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgriTwin Synthetic Field Monitoring</title>

    <!-- Cesium JS + CSS -->
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Cesium.js"></script>
    <link
      href="https://cesium.com/downloads/cesiumjs/releases/1.106/Build/Cesium/Widgets/widgets.css"
      rel="stylesheet"
    />

    <!-- Font Awesome for icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />

    <style>
        /* Overall layout and styling */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #1b5e20, #2e7d32);
            color: white;
            overflow: hidden; /* Because Cesium is taking up the main area */
            display: flex;    /* So #cesiumContainer can flex if needed */
        }

        #cesiumContainer {
            flex: 1;
            height: 100vh;
            width: 100%;
            position: relative;
        }

        /* Fix the right-hand sidebar so it is always fully visible and scrollable */
        #sidebar {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0; /* Ensure the sidebar extends to the bottom of the viewport */
            width: 300px;
            background: rgba(0, 51, 0, 0.8);
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        h2 {
            margin-top: 0;
            font-size: 1.5em;
        }

        .sensor-data, .task-list {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        #logoContainer {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
        }
        #logoContainer img {
            height: 40px;
        }

        /* Move Cesium toolbar to top-left */
        .cesium-viewer-toolbar {
            position: absolute;
            top: 10px;
            left: 200px;
            right: auto !important;
        }

        /* Button for CERES Dashboard */
        .dashboard-button {
            margin-top: 10px;
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px 0;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .dashboard-button:hover {
            background-color: #45a049;
        }

        /* Panel for NDVI / LAI_SAVI toggles */
        .layerTogglePanel {
            position: absolute;
            top: 10px;
            left: 450px;
            background: rgba(42, 42, 42, 0.8);
            padding: 8px;
            border-radius: 4px;
            color: #fff;
            font-family: sans-serif;
            z-index: 1100;
        }

        /* Legend container for NDVI/LAI_SAVI */
        #legendContainer {
            position: absolute;
            bottom: 40px;
            right: 310px; 
            background: rgba(42, 42, 42, 0.8);
            padding: 8px;
            border-radius: 4px;
            display: none;
            z-index: 1100;
            width: 230px;
        }
        #legendTitle {
            text-align: center;
            font-weight: bold;
            margin-bottom: 6px;
            color: #fff;
            font-family: sans-serif;
        }
        #legendImage {
            display: block;
            width: 200px;
            margin: 0 auto;
        }

        /* InfoBox and selection indicator repositioning */
        .cesium-viewer-infoBoxContainer {
            left: 10px !important;
            top: 100px !important;
            right: auto !important;
            width: 370px !important;
            max-height: 400px !important;
            overflow-y: auto;
        }
        .cesium-viewer-selectionIndicatorContainer {
            left: 10px !important;
            top: 60px !important;
            right: auto !important;
        }

        /* Button to toggle sidebar */
        #toggleSidebarBtn {
            position: absolute;
            top: 10px;
            right: 320px;
            z-index: 1200;
            background-color: #5d5d5d;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        #toggleSidebarBtn:hover {
            background-color: #4b4b4b;
        }
    </style>
</head>
<body>

<!-- Main Cesium container -->
<div id="cesiumContainer">
    <div id="logoContainer">
        <img src="AgriTwin_logo.png" alt="agritwin Logo">
        <img src="obsidian.png" alt="Obsidian Logo">
        <img src="fotomaco.png" alt="FOTOMACO Logo">
        <img src="OpenAgri.png" alt="OpenAgri Logo">
        <img src="eufunded.png" alt="EU Funded">
    </div>
</div>

<!-- Button to toggle the right sidebar -->
<button id="toggleSidebarBtn">Toggle Sidebar (R)</button>

<!-- Right sidebar for sensor data, AI insights, tasks, button -->
<div id="sidebar">
    <h2>FOTOMACO - Field Monitoring</h2>

<div class="sensor-data">
  <h3>Local Weather Station (Nava de Sotrobal) – 15 : 00&nbsp;Today</h3>

  <p><strong>Temperature:</strong> 29 °C</p>
  <p><strong>Humidity:</strong>    33 %</p>
  <p><strong>Wind&nbsp;Speed:</strong> 9 km h<sup>-1</sup> (NW)</p>
</div>

<div class="sensor-data">
  <h3>AI Insights - Synthetic Salamanca Demo Orchard</h3>

  <p><strong>Alert:</strong>  
    • Orchard soil-moisture reading sits at 17 % VWC after a mid-afternoon high of 29 °C.<br/>
    • Light westerly winds (≈ 12 km h⁻¹) are forecast through dusk, keeping evapotranspiration elevated.
  </p>

  <p><strong>Targeted actions (3 randomly chosen orchard sectors)</strong></p>
  <ul>
    <li><em>Almond Block&nbsp;A-3</em> – Trigger the micro-sprinkler set for a 22 mm recharge before 18 : 30.</li>
    <li><em>Pistachio Block&nbsp;P-1 (Kerman)</em> – Schedule an evening wettable-sulphur spray to curb mite pressure favoured by today’s warmth.</li>
    <li><em>Almond Buffer&nbsp;Row&nbsp;B-7</em> – Inspect for early navel-orangeworm flight; deploy pheromone traps if > 2 moths/night are captured.</li>
  </ul>
</div>

<!-- ---------- Property work queue ---------- -->
<!-- ---------- Property work queue ---------- -->
<div class="task-list">
  <h3>Task Scheduling - Synthetic Demo Orchard</h3>
  <ul>
    <li>✅ Irrigation (A-1 &amp; A-3) – Completed 04 / 08 / 2025 at 14 : 45</li>
    <li>🔄 Drone Mission #27 – Canopy NDVI &amp; pest hot-spot mapping (<strong>In Progress</strong>)</li>
    <li>🕒 Orchard-floor sweep (Pistachio P-2) – Queued for 05 / 08 / 2025 at 07 : 30</li>
    <li>📅 Post-bloom N-K fertigation – Planned for 10 / 08 / 2025 <small>(pending weather &amp; leaf-analysis)</small></li>
  </ul>
</div>


<!-- ---------- Property-level work queue ---------- -->
<div class="task-list">
  <h3>Task Scheduling - Synthetic Demo Farm</h3>
  <ul>
    <li>✅ Irrigation (blocks C-4 &amp; D-1) – Completed 14:45 today</li>
    <li>🔄 Drone pass&nbsp;#27 (stand count &amp; NDVI) – <strong>In Progress</strong></li>
    <li>🕒 Mower deployment (pasture B-1) – <strong>Queued for 08:00 tomorrow</strong></li>
    <li>📅 Fertilisation (vine terrace A-sector) – Held for 10 May 2025 <small>(awaiting ≤ 10 km h⁻¹ winds)</small></li>
  </ul>
</div>
    <!-- New DIV: AEMET Weather Forecast -->
    <div class="sensor-data">
        <h3>AEMET Weather Forecast (Nava de Sotrobal)</h3>
        <div id="aemetForecast">Loading forecast...</div>
    </div>
    <!-- End of new weather forecast block -->

    <!-- Button for navigating back to GAIA Dashboard -->
    <button class="dashboard-button" onclick="window.location.href='index.php'">
        Go to AgriTwin Dashboard
    </button>
</div>

<!-- Panel for NDVI / LAI_SAVI toggles via radio buttons -->
<div class="layerTogglePanel">
    <p><strong>Select Index</strong></p>
    <label>
      <input type="radio" name="indexLayer" value="none" checked/>
      None (Default Imagery Only)
    </label>
    <br>
    <label>
      <input type="radio" name="indexLayer" value="NDVI"/>
      NDVI (Sentinel Hub)
    </label>
    <br>
    <label>
      <input type="radio" name="indexLayer" value="LAI_SAVI"/>
      LAI_SAVI (Sentinel Hub)
    </label>
</div>

<!-- Legend container for Sentinel NDVI/LAI_SAVI -->
<div id="legendContainer">
    <div id="legendTitle">Legend</div>
    <img id="legendImage" src="" alt="Index Legend" />
</div>


<!-------------------------------------------------------------------------->
<!-- SINGLE, VALID <script> SECTION  (no PHP inside JS!) ------------------->
<script>
// -------------- 1) Cesium initialisation ---------------------------------
const CESIUM_TOKEN = <?= json_encode($cesiumToken) ?>;
Cesium.Ion.defaultAccessToken = CESIUM_TOKEN;

const viewer = new Cesium.Viewer('cesiumContainer', {
  terrainProvider: Cesium.createWorldTerrain()
});

    // Inject Font Awesome into the InfoBox iFrame so icons appear
    viewer.infoBox.frame.addEventListener("load", function() {
      var cssLink = viewer.infoBox.frame.contentDocument.createElement("link");
      cssLink.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css";
      cssLink.rel = "stylesheet";
      viewer.infoBox.frame.contentDocument.head.appendChild(cssLink);
    });

    /*********************************************
     * 2) NASA GIBS NDVI (toggle via 'n')
     *********************************************/
    var oldNdviProvider = new Cesium.WebMapServiceImageryProvider({
        url: 'https://gibs.earthdata.nasa.gov/wms/epsg3857/best/wms.cgi',
        layers: 'MODIS_Terra_NDVI_8Day',
        parameters: {
            service: 'WMS',
            version: '1.3.0',
            request: 'GetMap',
            format: 'image/png',
            transparent: 'true',
            styles: '',
            crs: 'EPSG:3857'
        }
    });
    var oldNdviLayer = viewer.imageryLayers.addImageryProvider(oldNdviProvider);
    oldNdviLayer.show = false; // hidden initially

    document.addEventListener("keydown", function(event) {
        if (event.key === 'n') {
            oldNdviLayer.show = !oldNdviLayer.show;
        }
    });

    /*********************************************
     * 3) Sentinel Hub NDVI / LAI_SAVI + bounding
     *********************************************/
    var boundingRectangle = Cesium.Rectangle.fromDegrees(
        -5.262770243294276,  // West
        40.883388135762168,  // South
        -5.255199073804725,  // East
        40.892776720220887   // North
    );
    viewer.camera.flyTo({
      destination: boundingRectangle,
      duration: 2
    });

const COPERNICUS_ID = <?= json_encode($copernicusId) ?>;
const sentinelWmsUrl = `https://sh.dataspace.copernicus.eu/ogc/wms/${COPERNICUS_ID}?`;


    var wmsOptions = {
      url: sentinelWmsUrl,
      parameters: {
        service: "WMS",
        version: "1.3.0",
        request: "GetMap",
        format: "image/png",
        transparent: "true",
        CRS: "EPSG:3857"
      },
      rectangle: boundingRectangle,
      tilingScheme: new Cesium.WebMercatorTilingScheme(),
      enablePickFeatures: false,
      maximumLevel: 14,
      credit: "Sentinel Hub / Copernicus"
    };

    var newNdviProvider = new Cesium.WebMapServiceImageryProvider({
      ...wmsOptions,
      layers: "NDVI"
    });
    var newNdviLayer = viewer.imageryLayers.addImageryProvider(newNdviProvider);
    newNdviLayer.show = false;

    var laiSaviProvider = new Cesium.WebMapServiceImageryProvider({
      ...wmsOptions,
      layers: "LAI_SAVI"
    });
    var laiSaviLayer = viewer.imageryLayers.addImageryProvider(laiSaviProvider);
    laiSaviLayer.show = false;

    // Custom legend images
    var legendUrls = {
      NDVI: "ndvi_legend_custom.png",
      LAI_SAVI: "lai_savi_legend_custom.png"
    };

    var radios = document.querySelectorAll('input[name="indexLayer"]');
    var legendContainer = document.getElementById("legendContainer");
    var legendImage = document.getElementById("legendImage");
    var legendTitle = document.getElementById("legendTitle");

    radios.forEach(rb => {
      rb.addEventListener("change", () => {
        newNdviLayer.show = false;
        laiSaviLayer.show = false;

        if (rb.value === "none") {
          legendContainer.style.display = "none";
          return;
        }
        if (rb.value === "NDVI") {
          newNdviLayer.show = true;
        } else if (rb.value === "LAI_SAVI") {
          laiSaviLayer.show = true;
        }
        legendContainer.style.display = "block";
        legendTitle.textContent = rb.value;
        legendImage.src = legendUrls[rb.value] || "";
      });
    });

    /*********************************************
     * 4) Spanish property boundary
     *********************************************/
    var boundaryCoordinates = [
      Cesium.Cartesian3.fromDegrees(-5.262770243294276, 40.883388135762168),
      Cesium.Cartesian3.fromDegrees(-5.255199073804725, 40.883388135762168),
      Cesium.Cartesian3.fromDegrees(-5.255199073804725, 40.892776720220887),
      Cesium.Cartesian3.fromDegrees(-5.262770243294276, 40.892776720220887),
      Cesium.Cartesian3.fromDegrees(-5.262770243294276, 40.883388135762168)
    ];
    viewer.entities.add({
        name: "Property Boundary",
        polyline: {
            positions: boundaryCoordinates,
            width: 4,
            material: Cesium.Color.RED,
            clampToGround: true
        }
    });

    /*********************************************
     * 5) Multiple pins (sensors, machinery, weather)
     *********************************************/
    var SENSOR_ICON_URL   = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
    var MACHINE_ICON_URL  = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
    var WEATHER_ICON_URL  = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
    var SELECTED_ICON_URL = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";

    var sensorsData = [
      { name: "Sensor A", lat: 40.8860, lon: -5.2615, temp: 28, moist10: 22, moist30: 24, moist50: 26 },
      { name: "Sensor B", lat: 40.8900, lon: -5.2590, temp: 27, moist10: 23, moist30: 25, moist50: 27 },
      { name: "Sensor C", lat: 40.8860, lon: -5.2590, temp: 27, moist10: 23, moist30: 25, moist50: 27 },
      { name: "Sensor D", lat: 40.8860, lon: -5.2580, temp: 27, moist10: 23, moist30: 25, moist50: 27 },
      { name: "Sensor E", lat: 40.8920, lon: -5.2580, temp: 26, moist10: 23, moist30: 25, moist50: 28 },
      { name: "Sensor F", lat: 40.8905, lon: -5.2559, temp: 27, moist10: 23, moist30: 25, moist50: 27 },
      { name: "Sensor G", lat: 40.8915, lon: -5.2596, temp: 24, moist10: 23, moist30: 25, moist50: 24 },
      { name: "Sensor H", lat: 40.8916, lon: -5.2559, temp: 27, moist10: 23, moist30: 25, moist50: 27 },
      { name: "Sensor I", lat: 40.8846, lon: -5.2597, temp: 28, moist10: 22, moist30: 24, moist50: 26 },
      { name: "Sensor J", lat: 40.8910, lon: -5.2609, temp: 28, moist10: 22, moist30: 24, moist50: 28 },
      { name: "Sensor L", lat: 40.8871, lon: -5.2603, temp: 28, moist10: 22, moist30: 24, moist50: 28 },
      { name: "Sensor M", lat: 40.8871, lon: -5.2568, temp: 28, moist10: 22, moist30: 24, moist50: 28 },
    ];

    var machineryData = [
      { name: "Tractor 1",   lat: 40.8887, lon: -5.2608, status: "Active" },
      { name: "Harvester 1", lat: 40.8875, lon: -5.2612, status: "Idle"   }
    ];

    var weatherStationsData = [
      {
        name: "Weather Station 1",
        lat: 40.8865,
        lon: -5.2620,
        status: "Online",
        temperature: 25,
        humidity: 55,
        windSpeed: 5
      },
      {
        name: "Weather Station 2",
        lat: 40.886275,
        lon: -5.2579,
        status: "Online",
        temperature: 22,
        humidity: 60,
        windSpeed: 4,
        rainfall: 1.0
      }
    ];

    function buildSensorDescription(sensor) {
      return `
        <div>
          <i class="fa-solid fa-microchip" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${sensor.name}</strong><br/>
          Temperature: ${sensor.temp}°C<br/>
          Soil Moisture (10 cm): ${sensor.moist10}%<br/>
          Soil Moisture (30 cm): ${sensor.moist30}%<br/>
          Soil Moisture (50 cm): ${sensor.moist50}%<br/><br/>
          <em>Coordinates:</em> ${sensor.lat.toFixed(5)}, ${sensor.lon.toFixed(5)}
        </div>
      `;
    }
    function buildMachineryDescription(mach) {
      return `
        <div>
          <i class="fa-solid fa-tractor" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${mach.name}</strong><br/>
          Status: ${mach.status}<br/><br/>
          <em>Coordinates:</em> ${mach.lat.toFixed(5)}, ${mach.lon.toFixed(5)}
        </div>
      `;
    }
    function buildWeatherDescription(ws) {
      let rainfallLine = ws.rainfall ? `Rainfall: ${ws.rainfall} mm/h<br/>` : "";
      return `
        <div>
          <i class="fa-solid fa-cloud-sun" style="float:right; margin-left:8px; font-size:32px;"></i>
          <strong>${ws.name}</strong><br/>
          Status: ${ws.status}<br/>
          Temperature: ${ws.temperature}°C<br/>
          Humidity: ${ws.humidity}%<br/>
          Wind Speed: ${ws.windSpeed} m/s<br/>
          ${rainfallLine}<br/>
          <em>Coordinates:</em> ${ws.lat.toFixed(5)}, ${ws.lon.toFixed(5)}
        </div>
      `;
    }

    var sensorEntities = [];
    sensorsData.forEach(function(sensor) {
      var entity = viewer.entities.add({
        name: sensor.name,
        position: Cesium.Cartesian3.fromDegrees(sensor.lon, sensor.lat),
        billboard: {
          image: SENSOR_ICON_URL,
          width: 32,
          height: 32,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        },
        description: buildSensorDescription(sensor)
      });
      entity._originalIcon = SENSOR_ICON_URL;
      sensorEntities.push(entity);
    });

    var machineryEntities = [];
    machineryData.forEach(function(mach) {
      var entity = viewer.entities.add({
        name: mach.name,
        position: Cesium.Cartesian3.fromDegrees(mach.lon, mach.lat),
        billboard: {
          image: MACHINE_ICON_URL,
          width: 32,
          height: 32,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        },
        description: buildMachineryDescription(mach)
      });
      entity._originalIcon = MACHINE_ICON_URL;
      machineryEntities.push(entity);
    });

    var weatherEntities = [];
    weatherStationsData.forEach(function(ws) {
      var entity = viewer.entities.add({
        name: ws.name,
        position: Cesium.Cartesian3.fromDegrees(ws.lon, ws.lat),
        billboard: {
          image: WEATHER_ICON_URL,
          width: 32,
          height: 32,
          verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
          heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
        },
        description: buildWeatherDescription(ws)
      });
      entity._originalIcon = WEATHER_ICON_URL;
      weatherEntities.push(entity);
    });

    /*********************************************
     * 6) Selection handling (change pin to green)
     *********************************************/
    var previouslySelectedEntity;
    viewer.selectedEntityChanged.addEventListener(function(newEntity) {
      if (previouslySelectedEntity && previouslySelectedEntity._originalIcon) {
        previouslySelectedEntity.billboard.image = previouslySelectedEntity._originalIcon;
      }
      if (newEntity && newEntity.billboard && newEntity._originalIcon) {
        newEntity.billboard.image = SELECTED_ICON_URL;
      }
      previouslySelectedEntity = newEntity;
    });

    /*********************************************
     * 7) Toggles for sensors, machinery, weather
     *********************************************/
    var sensorsVisible = true;
    document.addEventListener("keydown", function(event) {
      if (event.key === 's') {
        sensorsVisible = !sensorsVisible;
        sensorEntities.forEach(function(ent) {
          ent.show = sensorsVisible;
        });
      }
    });

    var machineryVisible = true;
    document.addEventListener("keydown", function(event) {
      if (event.key === 'm') {
        machineryVisible = !machineryVisible;
        machineryEntities.forEach(function(ent) {
          ent.show = machineryVisible;
        });
      }
    });

    var weatherVisible = true;
    document.addEventListener("keydown", function(event) {
      if (event.key === 'w') {
        weatherVisible = !weatherVisible;
        weatherEntities.forEach(function(ent) {
          ent.show = weatherVisible;
        });
      }
    });

    /*********************************************
     * 8) Toggle sidebar via button and 'R' key
     *********************************************/
    var sidebar = document.getElementById("sidebar");
    var toggleBtn = document.getElementById("toggleSidebarBtn");
    var sidebarVisible = true;

    function toggleSidebar() {
      sidebarVisible = !sidebarVisible;
      if (sidebarVisible) {
        sidebar.style.display = "flex";
      } else {
        sidebar.style.display = "none";
      }
    }
    toggleBtn.addEventListener("click", toggleSidebar);
    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 'r') {
        toggleSidebar();
      }
    });

    /*********************************************
     * Additional: Load Almond Trees from CSV
     *********************************************/
    var almondTreeEntities = [];
    var almondTreesVisible = true;

    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 't') {
        almondTreesVisible = !almondTreesVisible;
        almondTreeEntities.forEach(function(ent) {
          ent.show = almondTreesVisible;
        });
      }
    });

    function loadAlmondTreesCsv() {
      fetch('almond_trees.csv')
        .then(response => response.text())
        .then(csvText => {
          const lines = csvText.trim().split('\n');
          const cartographics = [];
          for (let i = 1; i < lines.length; i++) {
            const row = lines[i].trim();
            if (!row) continue;
            const [lonStr, latStr] = row.split(',');
            const lon = parseFloat(lonStr);
            const lat = parseFloat(latStr);
            cartographics.push(Cesium.Cartographic.fromDegrees(lon, lat));
          }
          Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics)
            .then(updatedPositions => {
              updatedPositions.forEach(pos => {
                const treePosition = Cesium.Cartesian3.fromRadians(
                  pos.longitude,
                  pos.latitude,
                  pos.height
                );
                const treeEntity = viewer.entities.add({
                  position: treePosition,
                  billboard: {
                    image: 'almond_tree.png',
                    width: 40,
                    height: 50,
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM
                  }
                });
                almondTreeEntities.push(treeEntity);
              });
              console.log("Almond trees loaded:", almondTreeEntities.length);
            })
            .catch(error => {
              console.error("Error sampling terrain for almond trees:", error);
            });
        })
        .catch(error => {
          console.error("Error loading almond tree CSV:", error);
        });
    }
    loadAlmondTreesCsv();

    /*********************************************
     * Additional: Load Pistachio Trees from CSV
     *********************************************/
    var pistachioTreeEntities = [];
    var pistachioTreesVisible = true;

    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 'p') {
        pistachioTreesVisible = !pistachioTreesVisible;
        pistachioTreeEntities.forEach(function(ent) {
          ent.show = pistachioTreesVisible;
        });
      }
    });

    function loadPistachioTreesCsv() {
      fetch('pistachio.csv')
        .then(response => response.text())
        .then(csvText => {
          const lines = csvText.trim().split('\n');
          const cartographics = [];
          for (let i = 1; i < lines.length; i++) {
            const row = lines[i].trim();
            if (!row) continue;
            const [lonStr, latStr] = row.split(',');
            const lon = parseFloat(lonStr);
            const lat = parseFloat(latStr);
            cartographics.push(Cesium.Cartographic.fromDegrees(lon, lat));
          }
          Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics)
            .then(updatedPositions => {
              updatedPositions.forEach(pos => {
                const treePosition = Cesium.Cartesian3.fromRadians(
                  pos.longitude,
                  pos.latitude,
                  pos.height
                );
                const treeEntity = viewer.entities.add({
                  position: treePosition,
                  billboard: {
                    image: 'pistachio.png',
                    width: 27,
                    height: 32,
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM
                  }
                });
                pistachioTreeEntities.push(treeEntity);
              });
              console.log("Pistachio trees loaded:", pistachioTreeEntities.length);
            })
            .catch(error => {
              console.error("Error sampling terrain for pistachio trees:", error);
            });
        })
        .catch(error => {
          console.error("Error loading pistachio tree CSV:", error);
        });
    }
    loadPistachioTreesCsv();

    /*********************************************
     * 9) Load pivot13.obj as "result.gltf" on ground
     *********************************************/
    var pivotLon = -5.25854965;
    var pivotLat = 40.8893203;
    var pivotRotationDeg = -1.2;
    var pivotCartographic = Cesium.Cartographic.fromDegrees(pivotLon, pivotLat);
    var pivotHeading = Cesium.Math.toRadians(pivotRotationDeg);
    var pivotPitch   = 0.0;
    var pivotRoll    = 0.0;

    var pivotEntity = viewer.entities.add({
      name: "Pivot 13 Model",
      orientation: Cesium.Quaternion.IDENTITY,
      model: {
        uri: "result.gltf",
        scale: 1.0,
        minimumPixelSize: 0,
        maximumScale: 1000
      }
    });

    Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, [pivotCartographic])
      .then(function(updatedPositions) {
        var updated = updatedPositions[0];
        var groundHeight = updated.height || 0;
        var pivotPosition = Cesium.Cartesian3.fromRadians(
          updated.longitude,
          updated.latitude,
          updated.height
        );
        var pivotOrientation = Cesium.Transforms.headingPitchRollQuaternion(
          pivotPosition,
          new Cesium.HeadingPitchRoll(pivotHeading, pivotPitch, pivotRoll)
        );
        pivotEntity.position = pivotPosition;
        pivotEntity.orientation = pivotOrientation;
      });

    /*********************************************
     * 10) Load Solar Panels from XML data
     *********************************************/
    var solarPanelsData = [
      { lon:-5.25721844, lat:40.8918752,  rotation:-1.33, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25686481, lat:40.8919468,  rotation:-1.39, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25754584, lat:40.8918067,  rotation:-1.37, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25724743, lat:40.891806,   rotation:-1.38, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25653943, lat:40.8920817,  rotation:-1.32, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25651547, lat:40.892015,   rotation:-1.3,  filename:"./solar_panel/solar.gltf" },
      { lon:-5.25720005, lat:40.8919468,  rotation:-1.34, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25755804, lat:40.8918815,  rotation:-1.43, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25686189, lat:40.8920228,  rotation:-1.39, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25691331, lat:40.8918703,  rotation:-1.38, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25651768, lat:40.891946,   rotation:-1.34, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25756409, lat:40.8917434,  rotation:-1.38, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25755467, lat:40.8919547,  rotation:-1.29, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25719114, lat:40.8920366,  rotation:-1.29, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25682728, lat:40.8921143,  rotation:-1.29, filename:"./solar_panel/solar.gltf" },
      { lon:-5.25649289, lat:40.892177,   rotation:-1.31, filename:"./solar_panel/solar.gltf" }
    ];

    var solarPanelEntities = [];
    var solarPanelsVisible = true;

    document.addEventListener("keydown", function(event) {
      if (event.key.toLowerCase() === 'o') {
        solarPanelsVisible = !solarPanelsVisible;
        solarPanelEntities.forEach(function(ent) {
          ent.show = solarPanelsVisible;
        });
      }
    });

    function loadSolarPanels() {
      var cartographics = [];
      for (var i = 0; i < solarPanelsData.length; i++) {
        var sp = solarPanelsData[i];
        cartographics.push(Cesium.Cartographic.fromDegrees(sp.lon, sp.lat));
      }
      Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics)
        .then(function(updatedPositions) {
          for (var j = 0; j < updatedPositions.length; j++) {
            var updated = updatedPositions[j];
            var groundHeight = updated.height || 0;
            var spData = solarPanelsData[j];

            var position = Cesium.Cartesian3.fromRadians(
              updated.longitude,
              updated.latitude,
              groundHeight
            );
            var heading = Cesium.Math.toRadians(spData.rotation - 15 + 180);
            var pitch   = 0.0;
            var roll    = 0.0;
            var orientation = Cesium.Transforms.headingPitchRollQuaternion(
              position,
              new Cesium.HeadingPitchRoll(heading, pitch, roll)
            );

            var solarEntity = viewer.entities.add({
              name: "Solar Panel",
              position: position,
              orientation: orientation,
              model: {
                uri: spData.filename,
                scale: 1.0,
                minimumPixelSize: 0,
                maximumScale: 1000
              }
            });
            solarPanelEntities.push(solarEntity);
          }
          console.log("Solar panels loaded:", solarPanelEntities.length);
        })
        .catch(function(error) {
          console.error("Error sampling terrain for solar panels:", error);
        });
    }
    loadSolarPanels();

    /*********************************************
     * 11) Google Tiles (toggle via 'g')
     *********************************************/
    // Note: Using Google Maps tiles outside of official APIs can violate
    // Google's Terms of Service. Ensure you have the right to do so.
    var googleImageryProvider = new Cesium.UrlTemplateImageryProvider({
      // 's' for satellite, 'm' for map, 'y' for hybrid, etc.
      url: 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
      maximumLevel: 20
    });
    var googleLayer = viewer.imageryLayers.addImageryProvider(googleImageryProvider);
    googleLayer.show = false;

    document.addEventListener("keydown", function(event) {
      if (event.key === 'g') {
        googleLayer.show = !googleLayer.show;
      }
    });

    /*********************************************
     * 12) Fetch AEMET Forecast from meteo.php
     *********************************************/
    window.addEventListener('DOMContentLoaded', function() {
      fetch('meteo.php')
        .then(response => response.json())
        .then(forecastData => {
          var container = document.getElementById("aemetForecast");
          var html = "";

          if (Array.isArray(forecastData) && forecastData.length > 0) {
            var dias = forecastData[0].prediccion.dia;
            if (dias && dias.length > 0) {
              // Show just today (dia[0]) and tomorrow (dia[1]) if available
              for (var i = 0; i < 2; i++) {
                if (!dias[i]) break;

                var d = dias[i];
                var dateStr = d.fecha; 

                // Temperatures
                var minTemp = (d.temperatura && d.temperatura.minima) ? d.temperatura.minima : "N/A";
                var maxTemp = (d.temperatura && d.temperatura.maxima) ? d.temperatura.maxima : "N/A";

                // Humidity (min and max)
                var minHum = (d.humedadRelativa && d.humedadRelativa.minima) ? d.humedadRelativa.minima : "N/A";
                var maxHum = (d.humedadRelativa && d.humedadRelativa.maxima) ? d.humedadRelativa.maxima : "N/A";

                // Probability of precipitation
                var probLluvia = "N/A";
                if (Array.isArray(d.probPrecipitacion) && d.probPrecipitacion.length > 0) {
                  var maxProb = 0;
                  d.probPrecipitacion.forEach(pp => {
                    var val = parseInt(pp.value, 10);
                    if (!isNaN(val) && val > maxProb) {
                      maxProb = val;
                    }
                  });
                  if (maxProb >= 0) {
                    probLluvia = maxProb + "%";
                  }
                }

                // Wind data: first interval
                var windInfo = "N/A";
                if (Array.isArray(d.vientoAndRachaMax) && d.vientoAndRachaMax.length > 0) {
                  var w = d.vientoAndRachaMax[0];
                  if (w && w.direccion && w.velocidad) {
                    windInfo = w.direccion + " " + w.velocidad + " km/h";
                  }
                }

                html += `<div style="margin-bottom:10px;">
                          <strong>${i === 0 ? "Today" : "Tomorrow"} (${dateStr})</strong><br/>
                          Temp: ${minTemp}°C / ${maxTemp}°C<br/>
                          Humidity: ${minHum}% / ${maxHum}%<br/>
                          Rain Probability: ${probLluvia}<br/>
                          Wind: ${windInfo}
                        </div>`;
              }
            } else {
              html = "No forecast day data found.";
            }
          } else {
            html = "No valid forecast data or unexpected structure.";
          }

          container.innerHTML = html;
        })
        .catch(err => {
          console.error('Error retrieving forecast:', err);
          document.getElementById("aemetForecast").innerHTML = "Error loading forecast.";
        });
    });
</script>
</body>
</html>
