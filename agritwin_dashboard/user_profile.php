<?php
require_once __DIR__ . '/includes/hmi_layout.php';

agritwin_render_page('settings', 'Operator Profile', function (): void {
    ?>
    <section class="view-section">
        <h2>Local demo operator</h2>
        <p>This local prototype uses synthetic operator and farm data. User profile editing remains a deployment-specific workflow.</p>
        <div class="telemetry-grid">
            <article class="telemetry-card"><span>Role</span><strong>Farm operator</strong></article>
            <article class="telemetry-card"><span>Site</span><strong>Fotovoltaica Macotera SL Macotera</strong></article>
            <article class="telemetry-card"><span>Platform</span><strong>AgriTwin</strong></article>
        </div>
    </section>
    <?php
});
