<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/includes/ai_training.php';

$message = '';
$trainingResult = null;
$inferenceResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_action'])) {
    if (!agritwin_role_allows('ai')) {
        http_response_code(403);
        $message = agritwin_t('administrator_or_technical_manager_required');
    } else {
        agritwin_require_csrf();
        $action = (string) $_POST['ai_action'];
        if ($action === 'train') {
            $trainingResult = agritwin_ai_run_training();
            $message = agritwin_t((string) $trainingResult['message']);
        } elseif ($action === 'infer') {
            $inferenceResult = agritwin_ai_run_inference();
            $message = agritwin_t((string) $inferenceResult['message']);
        }
    }
}

$status = agritwin_ai_status();
$models = agritwin_ai_models();
$runs = agritwin_ai_training_runs();

agritwin_render_page('ai_models', agritwin_t('ai_models'), function () use ($status, $models, $runs, $message, $trainingResult, $inferenceResult): void {
    ?>
    <section class="view-section">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h(agritwin_t('privacy_preserving_training')) ?></p>
                <h2><?= agritwin_h(agritwin_t('model_training')) ?></h2>
                <p><?= agritwin_h(agritwin_t('ai_training_intro')) ?></p>
            </div>
            <div class="action-row">
                <?= agritwin_help_link('api') ?>
                <?= agritwin_badge($status['compatible_framework_available'] ? 'configured' : 'unavailable') ?>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <p class="source-note"><?= agritwin_h($message) ?></p>
        <?php endif; ?>

        <?php if (!agritwin_role_allows('ai')): ?>
            <p class="source-note"><?= agritwin_h(agritwin_t('administrator_or_technical_manager_required')) ?></p>
        <?php else: ?>
            <div class="telemetry-grid">
                <article class="telemetry-card">
                    <span><?= agritwin_h(agritwin_t('ai_framework')) ?></span>
                    <strong><?= agritwin_h((string) $status['framework']) ?></strong>
                </article>
                <article class="telemetry-card">
                    <span><?= agritwin_h(agritwin_t('tensorflow_status')) ?></span>
                    <strong><?= agritwin_h($status['tensorflow_available'] ? agritwin_t('tensorflow_available') : agritwin_t('compatible_fallback_available')) ?></strong>
                </article>
                <article class="telemetry-card">
                    <span><?= agritwin_h(agritwin_t('dataset_source')) ?></span>
                    <strong><?= agritwin_h((string) $status['dataset_source']) ?></strong>
                </article>
                <article class="telemetry-card">
                    <span><?= agritwin_h(agritwin_t('model_output_path')) ?></span>
                    <strong><?= agritwin_h((string) $status['model_output_path']) ?></strong>
                </article>
                <article class="telemetry-card">
                    <span><?= agritwin_h(agritwin_t('training_mode')) ?></span>
                    <strong><?= agritwin_h(agritwin_t('cli_or_admin_triggered')) ?></strong>
                </article>
                <article class="telemetry-card">
                    <span><?= agritwin_h(agritwin_t('last_training_run')) ?></span>
                    <strong><?= agritwin_h((string) ($status['last_training_run'] ?? agritwin_t('model_not_trained'))) ?></strong>
                </article>
            </div>

            <p class="source-note"><?= agritwin_h(agritwin_t('ai_data_policy_note')) ?></p>

            <form class="settings-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= agritwin_h(agritwin_csrf_token()) ?>">
                <div class="action-row">
                    <button class="primary-action" type="submit" name="ai_action" value="train">
                        <i class="fas fa-play"></i> <?= agritwin_h(agritwin_t('run_training')) ?>
                    </button>
                    <button class="secondary-action" type="submit" name="ai_action" value="infer">
                        <i class="fas fa-wave-square"></i> <?= agritwin_h(agritwin_t('run_inference')) ?>
                    </button>
                    <a class="secondary-action" href="../api/ai/status"><?= agritwin_h(agritwin_t('ai_service_status')) ?></a>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <?php if (agritwin_role_allows('ai')): ?>
        <section class="view-section">
            <div class="section-heading">
                <div>
                    <h2><?= agritwin_h(agritwin_t('available_models')) ?></h2>
                    <p><?= agritwin_h(agritwin_t('model_artifacts_ignored')) ?></p>
                </div>
            </div>
            <table class="status-table">
                <thead>
                    <tr>
                        <th><?= agritwin_h(agritwin_t('model_name')) ?></th>
                        <th><?= agritwin_h(agritwin_t('target_variable')) ?></th>
                        <th><?= agritwin_h(agritwin_t('framework')) ?></th>
                        <th><?= agritwin_h(agritwin_t('trained_at')) ?></th>
                        <th><?= agritwin_h(agritwin_t('validation_mae')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><?= agritwin_h((string) $model['model_name']) ?></td>
                            <td><?= agritwin_h((string) $model['target_variable']) ?></td>
                            <td><?= agritwin_h((string) $model['framework']) ?></td>
                            <td><?= agritwin_h((string) ($model['trained_at'] ?? agritwin_t('model_not_trained'))) ?></td>
                            <td><?= agritwin_h($model['validation_mae'] !== null ? (string) $model['validation_mae'] : agritwin_t('not_available')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="view-section">
            <div class="section-heading">
                <h2><?= agritwin_h(agritwin_t('last_training_run')) ?></h2>
                <a class="secondary-action" href="../api/ai/training-runs"><?= agritwin_h(agritwin_t('training_runs')) ?></a>
            </div>
            <?php if (count($runs) === 0): ?>
                <p class="source-note"><?= agritwin_h(agritwin_t('ai_model_not_trained')) ?> <?= agritwin_h(agritwin_t('rule_based_advisory_active')) ?></p>
            <?php else: ?>
                <table class="status-table">
                    <thead>
                        <tr>
                            <th><?= agritwin_h(agritwin_t('model_name')) ?></th>
                            <th><?= agritwin_h(agritwin_t('trained_at')) ?></th>
                            <th><?= agritwin_h(agritwin_t('target_variable')) ?></th>
                            <th><?= agritwin_h(agritwin_t('validation_mae')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($runs) as $run): ?>
                            <tr>
                                <td><?= agritwin_h((string) ($run['model_name'] ?? 'AgriTwin')) ?></td>
                                <td><?= agritwin_h((string) ($run['trained_at'] ?? agritwin_t('not_available'))) ?></td>
                                <td><?= agritwin_h((string) ($run['target_variable'] ?? agritwin_t('not_available'))) ?></td>
                                <td><?= agritwin_h((string) ($run['validation_mae'] ?? agritwin_t('not_available'))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <?php if (is_array($trainingResult) && isset($trainingResult['training_result']) && is_array($trainingResult['training_result'])): ?>
            <section class="view-section compact-section">
                <h2><?= agritwin_h(agritwin_t('training_completed')) ?></h2>
                <div class="telemetry-grid">
                    <?php foreach (['model_name', 'framework', 'target_variable', 'validation_mae', 'training_records'] as $field): ?>
                        <article class="telemetry-card"><span><?= agritwin_h(agritwin_t($field)) ?></span><strong><?= agritwin_h((string) ($trainingResult['training_result'][$field] ?? agritwin_t('not_available'))) ?></strong></article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (is_array($inferenceResult) && isset($inferenceResult['inference']) && is_array($inferenceResult['inference'])): ?>
            <section class="view-section compact-section">
                <h2><?= agritwin_h(agritwin_t('inference_result')) ?></h2>
                <div class="telemetry-grid">
                    <?php foreach (['framework', 'target_variable', 'prediction', 'generated_at'] as $field): ?>
                        <article class="telemetry-card"><span><?= agritwin_h(agritwin_t($field)) ?></span><strong><?= agritwin_h((string) ($inferenceResult['inference'][$field] ?? agritwin_t('not_available'))) ?></strong></article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
    <?php
});
