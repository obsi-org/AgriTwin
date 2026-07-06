<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/hmi_layout.php';

$sections = agritwin_help_sections();

agritwin_render_page('help', agritwin_t('help'), function () use ($sections): void {
    ?>
    <section class="view-section help-hero">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h(agritwin_t('agritwin_help_center')) ?></p>
                <h2><?= agritwin_h(agritwin_t('help')) ?></h2>
                <p><?= agritwin_h(agritwin_t('help_intro')) ?></p>
            </div>
        </div>
        <label class="help-search">
            <span><?= agritwin_h(agritwin_t('search_help')) ?></span>
            <input id="helpSearch" type="search" placeholder="<?= agritwin_h(agritwin_t('search_help_placeholder')) ?>" autocomplete="off">
        </label>
        <nav class="help-toc" aria-label="<?= agritwin_h(agritwin_t('help_sections')) ?>">
            <?php foreach ($sections as $section): ?>
                <a href="#<?= agritwin_h($section['id']) ?>"><?= agritwin_h($section['title']) ?></a>
            <?php endforeach; ?>
        </nav>
    </section>

    <section class="help-grid" id="helpResults">
        <?php foreach ($sections as $section): ?>
            <article class="view-section help-section" id="<?= agritwin_h($section['id']) ?>" data-help-section data-help-text="<?= agritwin_h(strtolower($section['title'] . ' ' . implode(' ', $section['items']))) ?>">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= agritwin_h($section['audience']) ?></p>
                        <h2><?= agritwin_h($section['title']) ?></h2>
                    </div>
                    <?php if (!empty($section['admin'])): ?>
                        <span class="confidence-pill confidence-reduced"><?= agritwin_h(agritwin_t('administrator_access_required')) ?></span>
                    <?php endif; ?>
                </div>
                <ul class="help-list">
                    <?php foreach ($section['items'] as $item): ?>
                        <li><?= agritwin_h($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </section>
    <p id="helpNoResults" class="view-section help-no-results" hidden><?= agritwin_h(agritwin_t('no_results_found')) ?></p>
    <script>
        window.agritwinHelpSearch = {
            noResultsId: "helpNoResults",
            inputId: "helpSearch"
        };
    </script>
    <?php
});
