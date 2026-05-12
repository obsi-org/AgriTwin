<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$user = agritwin_current_user();
agritwin_render_page('settings', agritwin_t('profile'), function () use ($user): void {
    ?>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('profile')) ?></h2>
        <p><?= agritwin_h(agritwin_t('account_preferences_managed')) ?></p>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('username')) ?></span><strong><?= agritwin_h($user['username'] ?? agritwin_t('guest')) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('role')) ?></span><strong><?= agritwin_h(ucwords(str_replace('_', ' ', $user['role'] ?? 'viewer'))) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('site')) ?></span><strong>Fotovoltaica Macotera SL Macotera</strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('preferred_language')) ?></span><strong><?= agritwin_h(agritwin_current_language()) ?></strong></article>
        </div>
    </section>
    <?php
});
