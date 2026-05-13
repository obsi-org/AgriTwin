<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

$user = agritwin_current_user();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo) && $pdo instanceof PDO) {
    agritwin_require_csrf();
    $password = (string) ($_POST['new_password'] ?? '');
    if (strlen($password) >= 12 && $user !== null) {
        $pdo->prepare("UPDATE users SET password = :password, password_change_required = 0, must_change_password = 0 WHERE id = :id")
            ->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
        $_SESSION['must_change_password'] = false;
        $message = agritwin_t('password_updated');
    } else {
        $message = agritwin_t('username_password_required');
    }
}

agritwin_render_page('settings', agritwin_t('profile'), function () use ($user, $message): void {
    ?>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('profile')) ?></h2>
        <?= $message !== '' ? '<p class="confidence-pill confidence-normal">' . agritwin_h($message) . '</p>' : '' ?>
        <p><?= agritwin_h(agritwin_t('account_preferences_managed')) ?></p>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('username')) ?></span><strong><?= agritwin_h($user['username'] ?? agritwin_t('guest')) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('role')) ?></span><strong><?= agritwin_h(agritwin_role_label((string) ($user['role'] ?? 'viewer'))) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('site')) ?></span><strong><?= agritwin_h(agritwin_t('fotovoltaica_macotera_site')) ?></strong></article>
            <article class="telemetry-card"><span><?= agritwin_h(agritwin_t('preferred_language')) ?></span><strong><?= agritwin_h(agritwin_current_language()) ?></strong></article>
        </div>
        <?php if (!empty($_SESSION['must_change_password'])): ?>
            <form class="settings-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= agritwin_h(agritwin_csrf_token()) ?>">
                <label><?= agritwin_h(agritwin_t('new_password')) ?><input name="new_password" type="password" minlength="12" required></label>
                <button class="primary-action" type="submit"><?= agritwin_h(agritwin_t('save')) ?></button>
            </form>
        <?php endif; ?>
    </section>
    <?php
});
