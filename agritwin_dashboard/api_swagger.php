<?php
require_once __DIR__ . '/includes/hmi_layout.php';

agritwin_render_page('api', agritwin_t('api'), function (): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow">OpenAPI 3.0</p>
                <h2><?= agritwin_h(agritwin_t('swagger_ui')) ?></h2>
            </div>
            <div class="action-row"><?= agritwin_help_link('api-swagger') ?><a class="primary-action" href="../api/openapi.json"><i class="fas fa-file-code"></i> <?= agritwin_h(agritwin_t('open_openapi_json')) ?></a></div>
        </div>
        <p><?= agritwin_h(agritwin_t('swagger_note')) ?></p>
        <div id="swagger-ui" class="swagger-frame"></div>
        <noscript><p><?= agritwin_h(agritwin_t('javascript_required')) ?> <a href="../api/openapi.json">/agritwin3/api/openapi.json</a>.</p></noscript>
        <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
        <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
        <script>
            window.addEventListener("load", function () {
                SwaggerUIBundle({
                    url: "/agritwin3/api/openapi.json",
                    dom_id: "#swagger-ui",
                    deepLinking: true,
                    presets: [SwaggerUIBundle.presets.apis],
                    layout: "BaseLayout"
                });
            });
        </script>
    </section>
    <?php
});
