<?php
require_once __DIR__ . '/includes/hmi_layout.php';

agritwin_render_page('api', 'API / Swagger', function (): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow">OpenAPI 3.0</p>
                <h2>Swagger UI</h2>
            </div>
            <a class="primary-action" href="../api/openapi.json"><i class="fas fa-file-code"></i> Open openapi.json</a>
        </div>
        <p>Swagger UI loads the implemented AgriTwin local API specification from `/agritwin3/api/openapi.json`. Examples use synthetic station data and never include secrets.</p>
        <div id="swagger-ui" class="swagger-frame"></div>
        <noscript><p>JavaScript is required for Swagger UI. The OpenAPI JSON remains available at <a href="../api/openapi.json">/agritwin3/api/openapi.json</a>.</p></noscript>
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
