<?php
require_once __DIR__ . '/includes/hmi_layout.php';

$operations = agritwin_operations();
agritwin_render_page('calendar', 'Farm Calendar', function () use ($operations): void {
    ?>
    <section class="view-section">
        <h2>Operations for 2026-05-12</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Time</th><th>Operation</th><th>Block</th><th>Status</th></tr></thead>
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
