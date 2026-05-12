<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/db_config.php';

if (!agritwin_role_allows('users')) {
    http_response_code(403);
    agritwin_render_page('users', agritwin_t('user_management'), function (): void {
        echo '<section class="view-section"><h2>' . agritwin_h(agritwin_t('configuration_required')) . '</h2><p>Administrator role required.</p></section>';
    });
    exit;
}

agritwin_require_csrf();
$roles = ['administrator', 'technical_manager', 'farm_manager', 'advisor_researcher', 'viewer'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = in_array($_POST['role'] ?? '', $roles, true) ? (string) $_POST['role'] : 'viewer';
        $language = array_key_exists($_POST['preferred_language'] ?? '', agritwin_supported_languages()) ? (string) $_POST['preferred_language'] : 'en';
        if ($username !== '' && strlen($password) >= 10) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, preferred_language, enabled, password_change_required) VALUES (:username, :password, :role, :language, 1, 1)");
            $stmt->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'language' => $language,
            ]);
            $message = 'User created.';
        } else {
            $message = 'Username and password of at least 10 characters are required.';
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $role = in_array($_POST['role'] ?? '', $roles, true) ? (string) $_POST['role'] : 'viewer';
        $language = array_key_exists($_POST['preferred_language'] ?? '', agritwin_supported_languages()) ? (string) $_POST['preferred_language'] : 'en';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE users SET role = :role, preferred_language = :language, enabled = :enabled WHERE id = :id");
        $stmt->execute(['role' => $role, 'language' => $language, 'enabled' => $enabled, 'id' => $id]);
        if (!empty($_POST['new_password'])) {
            $pdo->prepare("UPDATE users SET password = :password, password_change_required = 1 WHERE id = :id")
                ->execute(['password' => password_hash((string) $_POST['new_password'], PASSWORD_DEFAULT), 'id' => $id]);
        }
        $message = 'User updated.';
    }
}

$users = $pdo->query("SELECT id, username, role, preferred_language, enabled, last_login_at, password_change_required FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

agritwin_render_page('users', agritwin_t('user_management'), function () use ($users, $roles, $message): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h(agritwin_t('user_management')) ?></h2>
            <?= $message !== '' ? '<span class="confidence-pill confidence-normal">' . agritwin_h($message) . '</span>' : '' ?>
        </div>
        <form class="settings-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= agritwin_h(agritwin_csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <label><?= agritwin_h(agritwin_t('username')) ?><input name="username" required></label>
            <label><?= agritwin_h(agritwin_t('password')) ?><input name="password" type="password" minlength="10" required></label>
            <label><?= agritwin_h(agritwin_t('role')) ?><select name="role"><?php foreach ($roles as $role): ?><option value="<?= agritwin_h($role) ?>"><?= agritwin_h(ucwords(str_replace('_', ' ', $role))) ?></option><?php endforeach; ?></select></label>
            <label><?= agritwin_h(agritwin_t('preferred_language')) ?><select name="preferred_language"><?php foreach (agritwin_supported_languages() as $code => $language): ?><option value="<?= agritwin_h($code) ?>"><?= agritwin_h($language['flag'] . ' ' . $language['label']) ?></option><?php endforeach; ?></select></label>
            <button class="primary-action" type="submit"><?= agritwin_h(agritwin_t('create_user')) ?></button>
        </form>
    </section>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('user_management')) ?></h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th><?= agritwin_h(agritwin_t('username')) ?></th><th><?= agritwin_h(agritwin_t('role')) ?></th><th><?= agritwin_h(agritwin_t('preferred_language')) ?></th><th><?= agritwin_h(agritwin_t('enabled')) ?></th><th><?= agritwin_h(agritwin_t('last_login')) ?></th><th><?= agritwin_h(agritwin_t('action')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="post">
                            <td><?= agritwin_h($user['username']) ?><input type="hidden" name="csrf_token" value="<?= agritwin_h(agritwin_csrf_token()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"></td>
                            <td><select name="role"><?php foreach ($roles as $role): ?><option value="<?= agritwin_h($role) ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= agritwin_h(ucwords(str_replace('_', ' ', $role))) ?></option><?php endforeach; ?></select></td>
                            <td><select name="preferred_language"><?php foreach (agritwin_supported_languages() as $code => $language): ?><option value="<?= agritwin_h($code) ?>" <?= $user['preferred_language'] === $code ? 'selected' : '' ?>><?= agritwin_h($language['flag'] . ' ' . $language['label']) ?></option><?php endforeach; ?></select></td>
                            <td><input type="checkbox" name="enabled" <?= (int) $user['enabled'] === 1 ? 'checked' : '' ?>></td>
                            <td><?= agritwin_h((string) ($user['last_login_at'] ?? '')) ?></td>
                            <td><input name="new_password" type="password" placeholder="New password"><button class="secondary-action" type="submit"><?= agritwin_h(agritwin_t('save')) ?></button></td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
