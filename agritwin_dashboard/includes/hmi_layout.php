<?php
declare(strict_types=1);

require_once __DIR__ . '/hmi_data.php';
require_once __DIR__ . '/help_content.php';

function agritwin_nav_items(): array
{
    $items = [
        'dashboard' => ['href' => 'index.php', 'label' => agritwin_t('dashboard'), 'icon' => 'fas fa-table-cells-large'],
        'digital_twin' => ['href' => 'digital_twin.php', 'label' => agritwin_t('digital_twin'), 'icon' => 'fas fa-map-location-dot'],
        'irrigation' => ['href' => 'irrigation.php', 'label' => agritwin_t('irrigation'), 'icon' => 'fas fa-droplet'],
        'pest_disease' => ['href' => 'pest_disease.php', 'label' => agritwin_t('pest_disease'), 'icon' => 'fas fa-triangle-exclamation'],
        'telemetry' => ['href' => 'telemetry.php', 'label' => agritwin_t('telemetry'), 'icon' => 'fas fa-tower-broadcast'],
        'openagri' => ['href' => 'openagri_services.php', 'label' => agritwin_t('openagri'), 'icon' => 'fas fa-network-wired'],
        'calendar' => ['href' => 'farm_calendar.php', 'label' => agritwin_t('calendar'), 'icon' => 'fas fa-calendar-days'],
        'reports' => ['href' => 'reports.php', 'label' => agritwin_t('reports'), 'icon' => 'fas fa-file-pdf'],
        'kpis' => ['href' => 'kpis.php', 'label' => agritwin_t('kpis'), 'icon' => 'fas fa-gauge-high'],
        'analytics' => ['href' => 'analytics.php', 'label' => agritwin_t('analytics'), 'icon' => 'fas fa-chart-line'],
        'api' => ['href' => 'api_swagger.php', 'label' => agritwin_t('api'), 'icon' => 'fas fa-code'],
        'settings' => ['href' => 'settings.php', 'label' => agritwin_t('settings'), 'icon' => 'fas fa-gear'],
        'help' => ['href' => 'help.php', 'label' => agritwin_t('help'), 'icon' => 'fas fa-circle-question'],
    ];
    if (agritwin_role_allows('users')) {
        $items['users'] = ['href' => 'users.php', 'label' => agritwin_t('users'), 'icon' => 'fas fa-users-gear'];
    }
    return $items;
}

function agritwin_render_page(string $active, string $title, callable $content): void
{
    $context = agritwin_farm_context();
    ?>
<!DOCTYPE html>
<html lang="<?= agritwin_h(agritwin_current_language()) ?>">
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
    <a class="skip-link" href="#main" aria-label="Skip to content"><?= agritwin_h(agritwin_t('skip_to_content')) ?></a>
    <header class="app-header">
        <div>
            <p class="eyebrow">Macotera / Salamanca / Castilla y Leon / Spain</p>
            <h1><?= agritwin_h($title) ?></h1>
        </div>
        <div class="sync-summary">
            <span class="confidence-pill confidence-normal"><?= agritwin_h(agritwin_t('online')) ?></span>
            <span><?= agritwin_h(agritwin_t('last_sync')) ?>: <?= agritwin_h($context['last_sync']) ?></span>
            <span><?= agritwin_h(agritwin_t('mode')) ?>: <?= agritwin_h($context['data_mode']) ?></span>
            <form class="language-form" method="get">
                <label for="lang"><?= agritwin_h(agritwin_t('language')) ?></label>
                <select id="lang" name="lang" onchange="this.form.submit()">
                    <?php foreach (agritwin_supported_languages() as $code => $language): ?>
                        <option value="<?= agritwin_h($code) ?>" <?= agritwin_current_language() === $code ? 'selected' : '' ?>><?= agritwin_h($language['flag'] . ' ' . $language['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </header>
    <div class="app-shell">
        <nav class="side-nav" aria-label="AgriTwin pages">
            <div class="brand-card" aria-label="<?= agritwin_h(agritwin_t('platform_branding')) ?>">
                <img class="brand-logo-primary" src="AgriTwin_logo.png" alt="<?= agritwin_h(agritwin_t('agritwin_logo_alt')) ?>">
                <span class="brand-divider" aria-hidden="true"></span>
                <img class="brand-logo-secondary" src="OpenAgri.png" alt="<?= agritwin_h(agritwin_t('openagri_logo_alt')) ?>">
            </div>
            <?php foreach (agritwin_nav_items() as $key => $item): ?>
                <a href="<?= agritwin_h($item['href']) ?>" class="<?= $key === $active ? 'active' : '' ?>">
                    <i class="<?= agritwin_h($item['icon']) ?>"></i> <?= agritwin_h($item['label']) ?>
                </a>
            <?php endforeach; ?>
            <a href="user_profile.php"><i class="fas fa-user"></i> <?= agritwin_h(agritwin_t('profile')) ?></a>
            <a href="logout.php"><i class="fas fa-right-from-bracket"></i> <?= agritwin_h(agritwin_t('logout')) ?></a>
        </nav>
        <main id="main" class="main-panel">
            <?php $content(); ?>
            <footer class="footer">
                <div class="footer-logos">
                    <img src="AgriTwin_logo.png" alt="AgriTwin logo">
                    <img class="footer-logo-partner" src="fotomaco.png" alt="Fotovoltaica Macotera SL logo">
                    <img src="obsidian.png" alt="Obsidian Innovation Institute / OBSIDIAN logo">
                    <img src="OpenAgri.png" alt="OpenAgri logo">
                    <img src="eufunded.png" alt="EU funded acknowledgement">
                </div>
                <span title="© 2026 AgriTwin"><?= agritwin_h(agritwin_t('footer_rights')) ?></span>
            </footer>
        </main>
    </div>
    <script>
        window.agritwinI18n = <?= json_encode(agritwin_js_translations(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/hmi.js" defer></script>
</body>
</html>
    <?php
}

function agritwin_badge(string $mode): string
{
    $class = match ($mode) {
        'normal', 'healthy', 'completed', 'live_healthy', 'configured', 'live_observations_available' => 'confidence-normal',
        'reduced', 'planned', 'local_fallback_active', 'live_fallback' => 'confidence-reduced',
        'stale', 'fallback', 'optional_not_configured' => 'confidence-stale',
        default => 'confidence-unavailable',
    };
    return '<span class="confidence-pill ' . $class . '">' . agritwin_h(agritwin_status_label($mode)) . '</span>';
}
