<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/includes/reporting.php';

$lang = agritwin_current_language();
$labels = agritwin_report_labels($lang);
$types = agritwin_report_types($lang);
$selectedType = isset($_POST['report_type'], $types[$_POST['report_type']]) ? (string) $_POST['report_type'] : 'field-operational';
$generatedReport = null;
$statusMessage = '';

if (($_GET['action'] ?? '') === 'download') {
    $path = agritwin_report_path_from_id((string) ($_GET['id'] ?? ''));
    if ($path === null) {
        http_response_code(404);
        echo agritwin_h($labels['report_unavailable']);
        exit();
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $generatedReport = agritwin_report_generate($selectedType, $lang);
        $statusMessage = $labels['report_generated'];
    } catch (Throwable) {
        $statusMessage = $labels['pdf_generation_failed'];
    }
}

$previewReport = $generatedReport ?? agritwin_report_build($selectedType, $lang);
$availableReports = agritwin_reports_list();

agritwin_render_page('reports', $labels['report_centre'], function () use ($labels, $types, $selectedType, $previewReport, $generatedReport, $availableReports, $statusMessage): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h($labels['report_centre']) ?></p>
                <h2><?= agritwin_h($labels['report_preview']) ?></h2>
            </div>
            <div class="action-row"><?= agritwin_help_link('reports') ?></div>
        </div>
        <p><?= agritwin_h($labels['preview_note']) ?></p>
        <p><?= agritwin_h($labels['storage_note']) ?></p>

        <form class="settings-grid" method="post">
            <label>
                <span><?= agritwin_h($labels['select_type']) ?></span>
                <select name="report_type">
                    <?php foreach ($types as $key => $type): ?>
                        <option value="<?= agritwin_h($key) ?>" <?= $selectedType === $key ? 'selected' : '' ?>><?= agritwin_h($type['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="setting-actions">
                <button class="primary-action" type="submit"><i class="fas fa-file-pdf"></i> <?= agritwin_h($labels['generate_report']) ?></button>
                <?php if ($generatedReport !== null): ?>
                    <a class="secondary-action" href="<?= agritwin_h($generatedReport['url']) ?>" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square"></i> <?= agritwin_h($labels['open_pdf']) ?></a>
                    <a class="secondary-action" href="<?= agritwin_h($generatedReport['url']) ?>" download><i class="fas fa-download"></i> <?= agritwin_h($labels['download_pdf']) ?></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($statusMessage !== ''): ?>
            <p class="confidence-pill <?= $generatedReport !== null ? 'confidence-normal' : 'confidence-unavailable' ?>"><?= agritwin_h($statusMessage) ?></p>
        <?php endif; ?>
    </section>

    <section class="view-section">
        <div class="report-branding-panel">
            <div class="report-branding-row report-branding-primary">
                <?php foreach (($previewReport['branding']['primary'] ?? []) as $brand): ?>
                    <figure>
                        <img src="../<?= agritwin_h(basename((string) $brand['path'])) ?>" alt="<?= agritwin_h((string) $brand['label']) ?>">
                        <figcaption><?= agritwin_h((string) $brand['name']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="report-branding-row report-branding-secondary">
                <?php foreach (($previewReport['branding']['secondary'] ?? []) as $brand): ?>
                    <figure>
                        <img src="../<?= agritwin_h(basename((string) $brand['path'])) ?>" alt="<?= agritwin_h((string) $brand['label']) ?>">
                        <figcaption><?= agritwin_h((string) $brand['name']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="section-heading">
            <h2><?= agritwin_h($previewReport['title']) ?></h2>
            <span class="confidence-pill confidence-normal"><?= agritwin_h($labels['pdf_ready']) ?></span>
        </div>
        <div class="metric-grid">
            <div class="metric-card"><span><?= agritwin_h($labels['report_type']) ?></span><strong><?= agritwin_h($previewReport['title']) ?></strong></div>
            <div class="metric-card"><span><?= agritwin_h($labels['generated_at']) ?></span><strong><?= agritwin_h($previewReport['generated_at']) ?></strong></div>
            <div class="metric-card"><span><?= agritwin_h($labels['location']) ?></span><strong><?= agritwin_h($previewReport['location']) ?></strong></div>
            <div class="metric-card"><span><?= agritwin_h($labels['data_source']) ?></span><strong><?= agritwin_h($previewReport['source']) ?></strong></div>
        </div>

        <?php foreach ($previewReport['sections'] as $section): ?>
            <article class="report-preview-section">
                <h3>
                    <?php if (!empty($section['icon'])): ?>
                        <i class="<?= agritwin_h((string) $section['icon']) ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <span><?= agritwin_h((string) $section['heading']) ?></span>
                </h3>
                <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                    <p><?= agritwin_h((string) $paragraph) ?></p>
                <?php endforeach; ?>
                <?php if (!empty($section['rows'])): ?>
                    <div class="table-wrap">
                        <table class="status-table">
                            <?php if (!empty($section['headers'])): ?>
                                <thead><tr><?php foreach ($section['headers'] as $header): ?><th><?= agritwin_h((string) $header) ?></th><?php endforeach; ?></tr></thead>
                            <?php endif; ?>
                            <tbody>
                            <?php foreach ($section['rows'] as $row): ?>
                                <tr>
                                    <?php foreach ((array) $row as $cell): ?>
                                        <td><?= agritwin_h((string) $cell) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <footer class="report-preview-footer">
            <div class="report-footer-logos">
                <?php foreach (($previewReport['branding']['footer'] ?? []) as $brand): ?>
                    <img src="../<?= agritwin_h(basename((string) $brand['path'])) ?>" alt="<?= agritwin_h((string) $brand['label']) ?>">
                <?php endforeach; ?>
                <span><?= agritwin_h($previewReport['copyright']) ?></span>
            </div>
            <p><?= agritwin_h($previewReport['funding_acknowledgement']) ?></p>
        </footer>
    </section>

    <section class="view-section">
        <div class="section-heading">
            <h2><?= agritwin_h($labels['available_reports']) ?></h2>
        </div>
        <?php if ($availableReports === []): ?>
            <p><?= agritwin_h($labels['no_reports']) ?></p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="status-table">
                    <thead><tr><th><?= agritwin_h($labels['report_type']) ?></th><th><?= agritwin_h($labels['generated_at']) ?></th><th><?= agritwin_h($labels['status']) ?></th><th><?= agritwin_h($labels['download_pdf']) ?></th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($availableReports, 0, 10) as $report): ?>
                        <tr>
                            <td><?= agritwin_h($report['filename']) ?></td>
                            <td><?= agritwin_h($report['generated_at']) ?></td>
                            <td><?= agritwin_badge('completed') ?></td>
                            <td><a class="secondary-action" href="<?= agritwin_h($report['url']) ?>" target="_blank" rel="noopener"><?= agritwin_h($labels['open_pdf']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
    <?php
});
