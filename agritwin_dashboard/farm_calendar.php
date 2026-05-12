<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$operations = agritwin_operations();
agritwin_render_page('calendar', agritwin_t('calendar'), function () use ($operations): void {
    ?>
    <section class="view-section">
        <h2><?= agritwin_h(agritwin_t('operations_for_date')) ?></h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th><?= agritwin_h(agritwin_t('time')) ?></th><th><?= agritwin_h(agritwin_t('operation')) ?></th><th><?= agritwin_h(agritwin_t('block')) ?></th><th><?= agritwin_h(agritwin_t('status')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($operations as $operation): ?>
                    <tr>
                        <td><?= agritwin_h($operation['time']) ?></td>
                        <td><?= agritwin_h($operation['operation']) ?></td>
                        <td><?= agritwin_h($operation['block']) ?></td>
                        <td><?= agritwin_badge($operation['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
