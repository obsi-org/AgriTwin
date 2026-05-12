<?php
declare(strict_types=1);

require_once __DIR__ . '/hmi_data.php';

function agritwin_nav_items(): array
{
    return [
        'dashboard' => ['href' => 'index.php', 'label' => 'Field Overview', 'icon' => 'fas fa-table-cells-large'],
        'digital_twin' => ['href' => 'digital_twin.php', 'label' => 'Digital Twin Map', 'icon' => 'fas fa-map-location-dot'],
        'irrigation' => ['href' => 'irrigation.php', 'label' => 'Irrigation Schedule', 'icon' => 'fas fa-droplet'],
        'pest_disease' => ['href' => 'pest_disease.php', 'label' => 'Pest & Disease Alerts', 'icon' => 'fas fa-triangle-exclamation'],
        'telemetry' => ['href' => 'telemetry.php', 'label' => 'Sensor Telemetry', 'icon' => 'fas fa-chart-line'],
        'openagri' => ['href' => 'openagri_services.php', 'label' => 'OpenAgri Services', 'icon' => 'fas fa-plug-circle-check'],
        'calendar' => ['href' => 'farm_calendar.php', 'label' => 'Farm Calendar', 'icon' => 'fas fa-calendar-days'],
        'reports' => ['href' => 'reports.php', 'label' => 'Reports', 'icon' => 'fas fa-file-pdf'],
        'kpis' => ['href' => 'kpis.php', 'label' => 'KPI Dashboard', 'icon' => 'fas fa-gauge-high'],
        'settings' => ['href' => 'settings.php', 'label' => 'Settings', 'icon' => 'fas fa-gear'],
    ];
}

function agritwin_render_page(string $active, string $title, callable $content): void
{
    $context = agritwin_farm_context();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2f6f4e">
    <title><?= agritwin_h($title) ?> - AgriTwin</title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/hmi.css">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="app-header">
        <div>
            <p class="eyebrow">Macotera / Salamanca / Castilla y Leon / Spain</p>
            <h1><?= agritwin_h($title) ?></h1>
        </div>
        <div class="sync-summary">
            <span class="confidence-pill confidence-normal">Online</span>
            <span>Last sync: <?= agritwin_h($context['last_sync']) ?></span>
            <span>Mode: local demo/mock</span>
        </div>
    </header>
    <div class="app-shell">
        <nav class="side-nav" aria-label="AgriTwin pages">
            <div class="brand-card">
                <img src="AgriTwin_logo.png" alt="AgriTwin logo">
                <strong>AgriTwin</strong>
                <span>FOTOMACO pilot HMI</span>
            </div>
            <?php foreach (agritwin_nav_items() as $key => $item): ?>
                <a href="<?= agritwin_h($item['href']) ?>" class="<?= $key === $active ? 'active' : '' ?>">
                    <i class="<?= agritwin_h($item['icon']) ?>"></i> <?= agritwin_h($item['label']) ?>
                </a>
            <?php endforeach; ?>
            <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </nav>
        <main id="main" class="main-panel">
            <?php $content(); ?>
            <footer class="footer">
                <div class="footer-logos">
                    <img src="fotomaco.png" alt="Fotovoltaica Macotera SL / FOTOMACO logo">
                    <img src="obsidian.png" alt="Obsidian Innovation Institute / OBSIDIAN logo">
                    <img src="OpenAgri.png" alt="OpenAgri logo">
                    <img src="eufunded.png" alt="EU funded acknowledgement">
                </div>
                <span>© 2026 AgriTwin. All rights reserved.</span>
            </footer>
        </main>
    </div>
    <script src="assets/hmi.js" defer></script>
</body>
</html>
    <?php
}

function agritwin_badge(string $mode): string
{
    $class = match ($mode) {
        'normal', 'healthy', 'completed' => 'confidence-normal',
        'reduced', 'planned' => 'confidence-reduced',
        'stale' => 'confidence-stale',
        default => 'confidence-unavailable',
    };
    return '<span class="confidence-pill ' . $class . '">' . agritwin_h(ucfirst($mode)) . '</span>';
}
