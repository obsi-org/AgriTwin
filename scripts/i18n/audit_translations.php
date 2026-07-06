<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$langDir = $root . '/agritwin_dashboard/lang';
$locales = ['en', 'pt-PT', 'es-ES'];
$allowlist = [
    'AgriTwin', 'OpenAgri', 'OBSIDIAN', 'Fotovoltaica Macotera SL', 'FarmCalendar',
    'OpenAPI', 'Swagger', 'API', 'JSON', 'OAuth2', 'JWT', 'HTTPS', 'Docker', 'PHP',
    'SQL', 'KPI', 'KPIs', 'NDVI', 'NDRE', 'LAI_SAVI', 'Sentinel Hub', 'ET0', 'Kc', 'Cesium', 'Chart.js',
    'Macotera', 'Salamanca', 'Castilla y Leon', 'Europe/Madrid', 'Open-Meteo',
    'HTML', 'PDF', 'URL', 'URLs', 'MySQL', 'MariaDB', 'PDO', 'CSRF',
];
$forbidden = [
    'demo', 'mock', 'dummy', 'fake', 'stub', 'GAIA', 'Pilot HMI', 'proposal',
    'deliverable', 'D1', 'D2', 'D3', 'WP1', 'WP2', 'WP3',
];

$translations = [];
foreach ($locales as $locale) {
    $path = $langDir . '/' . $locale . '.php';
    $translations[$locale] = is_file($path) ? require $path : [];
}

$helpContent = [];
foreach ($locales as $locale) {
    $path = $langDir . '/help/' . $locale . '.php';
    $helpContent[$locale] = is_file($path) ? require $path : [];
}

$allKeys = [];
foreach ($translations as $dict) {
    $allKeys = array_unique(array_merge($allKeys, array_keys($dict)));
}
sort($allKeys);

$report = [
    'generated_at' => gmdate(DateTimeInterface::ATOM),
    'locales' => $locales,
    'summary' => [],
    'missing_keys' => [],
    'extra_keys' => [],
    'empty_values' => [],
    'identical_to_english' => [],
    'forbidden_terms' => [],
    'help_content' => [],
    'used_keys_missing_from_english' => [],
    'suspicious_template_strings' => [],
];

$englishKeys = array_keys($translations['en']);
foreach ($translations as $locale => $dict) {
    $keys = array_keys($dict);
    $report['summary'][$locale] = ['key_count' => count($dict)];
    $report['missing_keys'][$locale] = array_values(array_diff($allKeys, $keys));
    $report['extra_keys'][$locale] = array_values(array_diff($keys, $englishKeys));
    foreach ($dict as $key => $value) {
        $value = (string) $value;
        if (trim($value) === '') {
            $report['empty_values'][$locale][] = $key;
        }
        foreach ($forbidden as $term) {
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $value)) {
                $report['forbidden_terms'][$locale][] = ['key' => $key, 'term' => $term, 'value' => $value];
            }
        }
        if ($locale !== 'en' && isset($translations['en'][$key]) && $value === (string) $translations['en'][$key]) {
            $allowed = false;
            foreach ($allowlist as $term) {
                if ($value === $term || str_contains($value, $term)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed && preg_match('/[A-Za-z]{4,}/', $value)) {
                $report['identical_to_english'][$locale][] = ['key' => $key, 'value' => $value];
            }
        }
    }
}

$englishHelpIds = array_map(static fn (array $section): string => (string) ($section['id'] ?? ''), $helpContent['en']);
foreach ($helpContent as $locale => $sections) {
    $ids = array_map(static fn (array $section): string => (string) ($section['id'] ?? ''), $sections);
    $report['help_content'][$locale] = [
        'section_count' => count($sections),
        'missing_sections' => array_values(array_diff($englishHelpIds, $ids)),
        'extra_sections' => array_values(array_diff($ids, $englishHelpIds)),
        'empty_sections' => [],
        'forbidden_terms' => [],
    ];
    foreach ($sections as $section) {
        $id = (string) ($section['id'] ?? '');
        $strings = array_merge([(string) ($section['title'] ?? ''), (string) ($section['audience'] ?? '')], array_map('strval', $section['items'] ?? []));
        if ($id === '' || trim((string) ($section['title'] ?? '')) === '' || empty($section['items'])) {
            $report['help_content'][$locale]['empty_sections'][] = $id !== '' ? $id : '[missing-id]';
        }
        foreach ($strings as $value) {
            foreach ($forbidden as $term) {
                if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $value)) {
                    $report['help_content'][$locale]['forbidden_terms'][] = ['id' => $id, 'term' => $term, 'value' => $value];
                }
            }
        }
    }
}

$sourceFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/agritwin_dashboard'));
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $path = $file->getPathname();
    if (!preg_match('/\.(php|js)$/', $path) || str_contains($path, '/lang/')) {
        continue;
    }
    $sourceFiles[] = $path;
}

$usedKeys = [];
$literalPattern = '/>\s*([A-Z][A-Za-z][^<>{}]{6,})\s*</';
foreach ($sourceFiles as $path) {
    $content = (string) file_get_contents($path);
    if (preg_match_all('/agritwin_t\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $matches)) {
        foreach ($matches[1] as $key) {
            $usedKeys[$key] = true;
        }
    }
    if (preg_match_all($literalPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as [$literal, $offset]) {
            $literal = trim(html_entity_decode($literal, ENT_QUOTES, 'UTF-8'));
            if ($literal === '' || preg_match('/^(DOCTYPE|OpenAPI 3\.0)$/', $literal)) {
                continue;
            }
            $allowed = false;
            foreach ($allowlist as $term) {
                if ($literal === $term || str_contains($literal, $term)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                $report['suspicious_template_strings'][] = [
                    'file' => str_replace($root . '/', '', $path),
                    'line' => substr_count(substr($content, 0, $offset), "\n") + 1,
                    'text' => $literal,
                ];
            }
        }
    }
}

$report['used_keys_missing_from_english'] = array_values(array_diff(array_keys($usedKeys), $englishKeys));
foreach (['missing_keys', 'extra_keys', 'empty_values', 'identical_to_english'] as $section) {
    foreach ($report[$section] as &$items) {
        if (is_array($items)) {
            sort($items);
        }
    }
}
unset($items);

$outDir = $root . '/docs/i18n';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}
file_put_contents($outDir . '/translation_audit_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

$lines = [
    '# Translation Audit Report',
    '',
    'Generated: ' . $report['generated_at'],
    '',
    '## Summary',
    '',
];
foreach ($report['summary'] as $locale => $summary) {
    $lines[] = '- ' . $locale . ': ' . $summary['key_count'] . ' keys';
}
$sections = [
    'missing_keys' => 'Missing Keys',
    'empty_values' => 'Empty Values',
    'identical_to_english' => 'Potentially Untranslated Values',
    'forbidden_terms' => 'Forbidden Terms',
    'help_content' => 'Help Content',
    'used_keys_missing_from_english' => 'Used Keys Missing From English',
    'suspicious_template_strings' => 'Suspicious Template Strings',
];
foreach ($sections as $key => $title) {
    $lines[] = '';
    $lines[] = '## ' . $title;
    $data = $report[$key];
    if ($key === 'help_content') {
        foreach ($data as $locale => $items) {
            $lines[] = '### ' . $locale;
            $lines[] = '- sections: ' . $items['section_count'];
            $lines[] = '- missing sections: ' . ($items['missing_sections'] === [] ? 'None' : implode(', ', $items['missing_sections']));
            $lines[] = '- extra sections: ' . ($items['extra_sections'] === [] ? 'None' : implode(', ', $items['extra_sections']));
            $lines[] = '- empty sections: ' . ($items['empty_sections'] === [] ? 'None' : implode(', ', $items['empty_sections']));
            $lines[] = '- forbidden terms: ' . ($items['forbidden_terms'] === [] ? 'None' : json_encode($items['forbidden_terms'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        continue;
    }
    if ($data === [] || $data === null) {
        $lines[] = 'None.';
        continue;
    }
    if (array_is_list($data)) {
        $items = $data;
        $lines[] = $items === [] ? 'None.' : '';
        foreach (array_slice($items, 0, 80) as $item) {
            $lines[] = '- `' . str_replace('`', '', is_array($item) ? json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $item) . '`';
        }
        continue;
    }
    foreach ($data as $locale => $items) {
        $lines[] = '### ' . $locale;
        if ($items === []) {
            $lines[] = 'None.';
            continue;
        }
        foreach (array_slice($items, 0, 80) as $item) {
            $lines[] = '- `' . str_replace('`', '', is_array($item) ? json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $item) . '`';
        }
    }
}
file_put_contents($outDir . '/translation_audit_report.md', implode("\n", $lines) . "\n");

$hardFailures = count($report['used_keys_missing_from_english']);
foreach (['missing_keys', 'empty_values', 'forbidden_terms'] as $section) {
    foreach ($report[$section] as $items) {
        $hardFailures += count($items);
    }
}
foreach ($report['help_content'] as $items) {
    $hardFailures += count($items['missing_sections']) + count($items['empty_sections']) + count($items['forbidden_terms']);
}

echo 'translation-audit: locales=' . implode(',', $locales) . ' keys=' . count($allKeys) . ' hard_failures=' . $hardFailures . PHP_EOL;
exit($hardFailures > 0 ? 1 : 0);
