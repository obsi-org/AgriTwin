<?php
declare(strict_types=1);

function agritwin_help_sections(?string $language = null): array
{
    $language ??= agritwin_current_language();
    if (!isset(agritwin_supported_languages()[$language])) {
        $language = 'en';
    }
    $path = dirname(__DIR__) . '/lang/help/' . $language . '.php';
    if (!is_file($path)) {
        $path = dirname(__DIR__) . '/lang/help/en.php';
    }
    $sections = require $path;
    return is_array($sections) ? $sections : [];
}

function agritwin_help_url(string $section): string
{
    return 'help.php?lang=' . rawurlencode(agritwin_current_language()) . '#' . rawurlencode($section);
}

function agritwin_help_link(string $section): string
{
    return '<a class="context-help-link" href="' . agritwin_h(agritwin_help_url($section)) . '"><i class="fas fa-circle-question"></i> ' . agritwin_h(agritwin_t('see_help')) . '</a>';
}
