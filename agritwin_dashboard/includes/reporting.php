<?php
declare(strict_types=1);

require_once __DIR__ . '/statistics_engine.php';

function agritwin_reports_storage_dir(): string
{
    $dir = dirname(__DIR__, 2) . '/var/reports';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    if (!is_writable($dir)) {
        @chmod($dir, 0777);
    }
    return $dir;
}

function agritwin_report_branding(): array
{
    $root = dirname(__DIR__, 2);
    return [
        'primary' => [
            ['label' => 'AgriTwin logo', 'name' => 'AgriTwin', 'path' => $root . '/AgriTwin_logo.png'],
            ['label' => 'Fotovoltaica Macotera SL logo', 'name' => 'Fotovoltaica Macotera SL', 'path' => $root . '/fotomaco.png'],
        ],
        'secondary' => [
            ['label' => 'OpenAgri logo', 'name' => 'OpenAgri', 'path' => $root . '/OpenAgri.png'],
            ['label' => 'Obsidian Innovation Institute - Associação logo', 'name' => 'Obsidian Innovation Institute - Associação', 'path' => $root . '/obsidian.png'],
        ],
        'footer' => [
            ['label' => 'European Union funding logo', 'name' => 'European Union', 'path' => $root . '/eufunded.png'],
        ],
    ];
}

function agritwin_report_labels(?string $lang = null): array
{
    $lang ??= agritwin_current_language();
    $labels = [
        'en' => [
            'report_centre' => 'Report Centre',
            'generate_report' => 'Generate report',
            'download_pdf' => 'Download PDF',
            'open_pdf' => 'Open PDF',
            'report_preview' => 'Report preview',
            'available_reports' => 'Available reports',
            'report_type' => 'Report type',
            'generation_status' => 'Generation status',
            'generated_at' => 'Generated at',
            'location' => 'Location',
            'data_source' => 'Data source',
            'summary' => 'Summary',
            'observations' => 'Observations',
            'recommendation' => 'Recommendation',
            'status' => 'Status',
            'report_generated' => 'Report generated',
            'report_unavailable' => 'Report unavailable',
            'configuration_required' => 'Configuration required',
            'pdf_generation_failed' => 'PDF generation failed',
            'field_operational_report' => 'Field Operational Report',
            'weather_sensor_report' => 'Weather and Sensor Report',
            'irrigation_report' => 'Irrigation Report',
            'pest_disease_report' => 'Pest and Disease Risk Report',
            'openagri_services_report' => 'OpenAgri Services Status Report',
            'kpi_analytics_report' => 'KPI / Analytics Report',
            'temperature' => 'Temperature',
            'humidity' => 'Humidity',
            'wind_speed' => 'Wind speed',
            'rainfall_today' => 'Rainfall today',
            'soil_moisture' => 'Soil moisture',
            'irrigation_recommendation' => 'Irrigation recommendation',
            'pest_disease_risk' => 'Pest and disease risk',
            'service_available' => 'Service available',
            'service_unavailable' => 'Service unavailable',
            'preview_note' => 'The preview below is a readable summary of the generated report. The PDF contains formatted sections and tables.',
            'storage_note' => 'Generated PDFs are stored locally in the ignored runtime reports directory.',
            'reporting_service_note' => 'PDFs are generated locally in AgriTwin; OpenAgri Reporting Service status remains visible in the service report.',
            'no_reports' => 'No generated reports found yet.',
            'select_type' => 'Select report type',
            'source_summary' => 'Source summary',
            'operational_interpretation' => 'Operational interpretation',
            'metric' => 'Metric',
            'value' => 'Value',
            'unit' => 'Unit',
            'service' => 'Service',
            'sensor_stations' => 'Sensor stations',
            'station' => 'Station',
            'block' => 'Block',
            'latency' => 'Latency',
            'last_check' => 'Last check',
            'pdf_ready' => 'PDF ready',
            'copyright' => '© 2026 AgriTwin',
        ],
        'pt-PT' => [
            'report_centre' => 'Centro de Relatórios',
            'generate_report' => 'Gerar relatório',
            'download_pdf' => 'Descarregar PDF',
            'open_pdf' => 'Abrir PDF',
            'report_preview' => 'Pré-visualização do relatório',
            'available_reports' => 'Relatórios disponíveis',
            'report_type' => 'Tipo de relatório',
            'generation_status' => 'Estado da geração',
            'generated_at' => 'Gerado em',
            'location' => 'Localização',
            'data_source' => 'Fonte de dados',
            'summary' => 'Resumo',
            'observations' => 'Observações',
            'recommendation' => 'Recomendação',
            'status' => 'Estado',
            'report_generated' => 'Relatório gerado',
            'report_unavailable' => 'Relatório indisponível',
            'configuration_required' => 'Configuração necessária',
            'pdf_generation_failed' => 'Falha na geração do PDF',
            'field_operational_report' => 'Relatório operacional de campo',
            'weather_sensor_report' => 'Relatório meteorológico e de sensores',
            'irrigation_report' => 'Relatório de rega',
            'pest_disease_report' => 'Relatório de risco de pragas e doenças',
            'openagri_services_report' => 'Relatório de estado dos serviços OpenAgri',
            'kpi_analytics_report' => 'Relatório de KPI / análise',
            'temperature' => 'Temperatura',
            'humidity' => 'Humidade',
            'wind_speed' => 'Velocidade do vento',
            'rainfall_today' => 'Precipitação hoje',
            'soil_moisture' => 'Humidade do solo',
            'irrigation_recommendation' => 'Recomendação de rega',
            'pest_disease_risk' => 'Risco de pragas e doenças',
            'service_available' => 'Serviço disponível',
            'service_unavailable' => 'Serviço indisponível',
            'preview_note' => 'A pré-visualização abaixo é um resumo legível do relatório gerado. O PDF contém secções e tabelas formatadas.',
            'storage_note' => 'Os PDFs gerados são guardados localmente no diretório de relatórios de execução ignorado pelo Git.',
            'reporting_service_note' => 'Os PDFs são gerados localmente no AgriTwin; o estado do OpenAgri Reporting Service continua visível no relatório de serviços.',
            'no_reports' => 'Ainda não existem relatórios gerados.',
            'select_type' => 'Selecionar tipo de relatório',
            'source_summary' => 'Resumo das fontes',
            'operational_interpretation' => 'Interpretação operacional',
            'metric' => 'Métrica',
            'value' => 'Valor',
            'unit' => 'Unidade',
            'service' => 'Serviço',
            'sensor_stations' => 'Estações de sensores',
            'station' => 'Estação',
            'block' => 'Bloco',
            'latency' => 'Latência',
            'last_check' => 'Última verificação',
            'pdf_ready' => 'PDF pronto',
            'copyright' => '© 2026 AgriTwin',
        ],
        'es-ES' => [
            'report_centre' => 'Centro de informes',
            'generate_report' => 'Generar informe',
            'download_pdf' => 'Descargar PDF',
            'open_pdf' => 'Abrir PDF',
            'report_preview' => 'Vista previa del informe',
            'available_reports' => 'Informes disponibles',
            'report_type' => 'Tipo de informe',
            'generation_status' => 'Estado de generación',
            'generated_at' => 'Generado el',
            'location' => 'Ubicación',
            'data_source' => 'Fuente de datos',
            'summary' => 'Resumen',
            'observations' => 'Observaciones',
            'recommendation' => 'Recomendación',
            'status' => 'Estado',
            'report_generated' => 'Informe generado',
            'report_unavailable' => 'Informe no disponible',
            'configuration_required' => 'Configuración necesaria',
            'pdf_generation_failed' => 'Error al generar el PDF',
            'field_operational_report' => 'Informe operativo de campo',
            'weather_sensor_report' => 'Informe meteorológico y de sensores',
            'irrigation_report' => 'Informe de riego',
            'pest_disease_report' => 'Informe de riesgo de plagas y enfermedades',
            'openagri_services_report' => 'Informe de estado de servicios OpenAgri',
            'kpi_analytics_report' => 'Informe de KPI / analítica',
            'temperature' => 'Temperatura',
            'humidity' => 'Humedad',
            'wind_speed' => 'Velocidad del viento',
            'rainfall_today' => 'Precipitación hoy',
            'soil_moisture' => 'Humedad del suelo',
            'irrigation_recommendation' => 'Recomendación de riego',
            'pest_disease_risk' => 'Riesgo de plagas y enfermedades',
            'service_available' => 'Servicio disponible',
            'service_unavailable' => 'Servicio no disponible',
            'preview_note' => 'La vista previa siguiente es un resumen legible del informe generado. El PDF contiene secciones y tablas formateadas.',
            'storage_note' => 'Los PDF generados se guardan localmente en el directorio de informes de ejecución ignorado por Git.',
            'reporting_service_note' => 'Los PDF se generan localmente en AgriTwin; el estado de OpenAgri Reporting Service permanece visible en el informe de servicios.',
            'no_reports' => 'Aún no hay informes generados.',
            'select_type' => 'Seleccionar tipo de informe',
            'source_summary' => 'Resumen de fuentes',
            'operational_interpretation' => 'Interpretación operativa',
            'metric' => 'Métrica',
            'value' => 'Valor',
            'unit' => 'Unidad',
            'service' => 'Servicio',
            'sensor_stations' => 'Estaciones de sensores',
            'station' => 'Estación',
            'block' => 'Bloque',
            'latency' => 'Latencia',
            'last_check' => 'Última comprobación',
            'pdf_ready' => 'PDF listo',
            'copyright' => '© 2026 AgriTwin',
        ],
    ];
    return $labels[$lang] ?? $labels['en'];
}

function agritwin_report_types(?string $lang = null): array
{
    $l = agritwin_report_labels($lang);
    return [
        'field-operational' => ['title' => $l['field_operational_report'], 'description' => $l['summary']],
        'weather-sensor' => ['title' => $l['weather_sensor_report'], 'description' => $l['observations']],
        'irrigation' => ['title' => $l['irrigation_report'], 'description' => $l['irrigation_recommendation']],
        'pest-disease' => ['title' => $l['pest_disease_report'], 'description' => $l['pest_disease_risk']],
        'openagri-services' => ['title' => $l['openagri_services_report'], 'description' => 'OpenAgri'],
        'kpi-analytics' => ['title' => $l['kpi_analytics_report'], 'description' => 'KPI'],
    ];
}

function agritwin_report_build(string $type, ?string $lang = null): array
{
    $lang ??= agritwin_current_language();
    $l = agritwin_report_labels($lang);
    $types = agritwin_report_types($lang);
    if (!isset($types[$type])) {
        $type = 'field-operational';
    }
    $context = agritwin_farm_context();
    $analytics = agritwin_full_analytics();
    $weather = $analytics['weather'];
    $irrigation = $analytics['irrigation'];
    $pest = $analytics['pest_disease'];
    $summary = $analytics['summary'];
    $generatedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid')))->format('Y-m-d H:i T');

    $sections = [
        [
            'heading' => $l['source_summary'],
            'rows' => [
                [$l['generated_at'], $generatedAt],
                [$l['location'], 'Nava de Sotrobal / Macotera, Salamanca, Castilla y Leon, Spain'],
                [$l['data_source'], (string) $summary['data_source']],
                ['AgriTwin', 'AgriTwin / Fotovoltaica Macotera SL / OpenAgri / Obsidian Innovation Institute - Associação'],
            ],
        ],
    ];

    if (in_array($type, ['field-operational', 'weather-sensor'], true)) {
        $sections[] = [
            'heading' => $l['weather_sensor_report'],
            'rows' => [
                [$l['temperature'], $weather['latest']['temperature_c'] . ' degC'],
                [$l['humidity'], $weather['latest']['humidity_pct'] . ' %'],
                [$l['wind_speed'], $weather['latest']['wind_kmh'] . ' km h-1'],
                [$l['rainfall_today'], $weather['latest']['rainfall_mm'] . ' mm'],
                [$l['weather_sensor_report'], (string) $weather['source']],
                [$l['soil_moisture'], $summary['average_soil_moisture_pct'] . ' % VWC'],
            ],
        ];
        $stationRows = [];
        foreach (array_slice($analytics['sensor_stations']['comparison'], 0, 6) as $station) {
            $stationRows[] = [
                $station['station_id'],
                $station['block'],
                $station['soil_moisture_pct'] . ' % VWC',
                $station['air_temperature_c'] . ' degC',
                $station['water_stress_status'],
            ];
        }
        $sections[] = ['heading' => $l['sensor_stations'], 'headers' => [$l['station'], $l['block'], $l['soil_moisture'], $l['temperature'], $l['status']], 'rows' => $stationRows];
    }

    if (in_array($type, ['field-operational', 'irrigation'], true)) {
        $sections[] = [
            'heading' => $l['irrigation_report'],
            'rows' => [
                [$l['irrigation_recommendation'], (string) ($irrigation['recommendation']['text'] ?? $irrigation['recommendation']['label'] ?? '')],
                ['Confidence', (string) $irrigation['recommendation']['confidence']],
                [$l['soil_moisture'], ($irrigation['soil_moisture']['avg'] ?? 'n/a') . ' % VWC'],
                ['Irrigation deficit estimate', ($irrigation['estimated_irrigation_deficit_pct_vwc'] ?? 'n/a') . ' % VWC'],
                ['Water balance score', ($irrigation['water_balance_score'] ?? 'n/a')],
            ],
        ];
    }

    if (in_array($type, ['field-operational', 'pest-disease'], true)) {
        $sections[] = [
            'heading' => $l['pest_disease_report'],
            'rows' => [
                [$l['pest_disease_risk'], (string) $pest['risk']['risk']],
                [$l['recommendation'], (string) ($pest['risk']['text'] ?? $pest['risk']['label'] ?? '')],
                ['Growing degree days', (string) $pest['timeseries']['gdd_accumulation']],
                [$l['status'], (string) $pest['timeseries']['category']],
            ],
        ];
    }

    if (in_array($type, ['field-operational', 'openagri-services'], true)) {
        $serviceRows = [];
        foreach (agritwin_service_freshness_analytics() as $service) {
            $serviceRows[] = [
                $service['service'],
                agritwin_status_label((string) $service['status']),
                (string) $service['last_check'],
                ($service['latency_ms'] === null ? '-' : $service['latency_ms'] . ' ms'),
            ];
        }
        $sections[] = ['heading' => $l['openagri_services_report'], 'headers' => [$l['service'], $l['status'], $l['last_check'], $l['latency']], 'rows' => $serviceRows];
    }

    if (in_array($type, ['field-operational', 'kpi-analytics'], true)) {
        $kpiRows = [];
        foreach (agritwin_kpis() as $kpi) {
            $kpiRows[] = [$kpi['label'] ?? $kpi['metric'] ?? '', $kpi['value'], $kpi['unit'] ?? '', $kpi['status']];
        }
        $sections[] = ['heading' => $l['kpi_analytics_report'], 'headers' => [$l['metric'], $l['value'], $l['unit'], $l['status']], 'rows' => $kpiRows];
    }

    $sections[] = [
        'heading' => $l['operational_interpretation'],
        'paragraphs' => [
            implode(' ', array_slice($summary['insights'], 0, 3)),
            $l['reporting_service_note'],
        ],
    ];

    return [
        'id' => '',
        'type' => $type,
        'title' => $types[$type]['title'],
        'language' => $lang,
        'generated_at' => $generatedAt,
        'location' => 'Nava de Sotrobal / Macotera, Salamanca, Castilla y Leon, Spain',
        'source' => (string) $summary['data_source'],
        'branding' => agritwin_report_branding(),
        'copyright' => '© 2026 AgriTwin',
        'funding_acknowledgement' => agritwin_funding_acknowledgement(),
        'sections' => $sections,
    ];
}

function agritwin_report_wrap_text(string $text, int $width = 88): array
{
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
    if ($text === '') {
        return [''];
    }
    return explode("\n", wordwrap($text, $width, "\n", false));
}

function agritwin_report_pdf_string(string $text): string
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
    if ($encoded === false) {
        $encoded = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
    }
    $escaped = '';
    $length = strlen($encoded);
    for ($i = 0; $i < $length; $i++) {
        $ord = ord($encoded[$i]);
        if ($encoded[$i] === '\\' || $encoded[$i] === '(' || $encoded[$i] === ')') {
            $escaped .= '\\' . $encoded[$i];
        } elseif ($ord < 32 || $ord > 126) {
            $escaped .= '\\' . str_pad(decoct($ord), 3, '0', STR_PAD_LEFT);
        } else {
            $escaped .= $encoded[$i];
        }
    }
    return '(' . $escaped . ')';
}

function agritwin_report_pdf_image(string $path, float $maxWidth, float $maxHeight): ?array
{
    if (!is_file($path) || !function_exists('imagecreatefromstring')) {
        return null;
    }
    $bytes = file_get_contents($path);
    if ($bytes === false) {
        return null;
    }
    $image = @imagecreatefromstring($bytes);
    if (!$image) {
        return null;
    }
    $width = imagesx($image);
    $height = imagesy($image);
    $scale = min($maxWidth / max(1, $width), $maxHeight / max(1, $height), 1.0);
    $drawWidth = round($width * $scale, 2);
    $drawHeight = round($height * $scale, 2);
    $canvas = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
    imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
    ob_start();
    imagejpeg($canvas, null, 86);
    $jpeg = ob_get_clean();
    imagedestroy($image);
    imagedestroy($canvas);
    if (!is_string($jpeg) || $jpeg === '') {
        return null;
    }
    return [
        'width_px' => $width,
        'height_px' => $height,
        'draw_width' => $drawWidth,
        'draw_height' => $drawHeight,
        'stream' => $jpeg,
    ];
}

function agritwin_report_pdf_content(array $report): string
{
    $branding = $report['branding'] ?? agritwin_report_branding();
    $imageSpecs = [
        'Im1' => ['asset' => $branding['primary'][0] ?? null, 'max_width' => 150, 'max_height' => 42, 'x' => 50, 'y' => 772],
        'Im2' => ['asset' => $branding['primary'][1] ?? null, 'max_width' => 80, 'max_height' => 58, 'x' => 232, 'y' => 758],
        'Im3' => ['asset' => $branding['secondary'][0] ?? null, 'max_width' => 100, 'max_height' => 34, 'x' => 352, 'y' => 774],
        'Im4' => ['asset' => $branding['secondary'][1] ?? null, 'max_width' => 58, 'max_height' => 50, 'x' => 485, 'y' => 762],
        'Im5' => ['asset' => $branding['footer'][0] ?? null, 'max_width' => 92, 'max_height' => 22, 'x' => 50, 'y' => 28],
    ];
    $images = [];
    foreach ($imageSpecs as $name => $spec) {
        $asset = $spec['asset'];
        if (is_array($asset)) {
            $image = agritwin_report_pdf_image((string) $asset['path'], (float) $spec['max_width'], (float) $spec['max_height']);
            if ($image !== null) {
                $images[$name] = $image + ['x' => $spec['x'], 'y' => $spec['y']];
            }
        }
    }

    $lines = [];
    $lines[] = ['text' => 'AgriTwin + Fotovoltaica Macotera SL', 'size' => 8];
    $lines[] = ['text' => 'OpenAgri + Obsidian Innovation Institute - Associação', 'size' => 8];
    $lines[] = ['text' => '', 'size' => 7];
    $lines[] = ['text' => $report['title'], 'size' => 16];
    $lines[] = ['text' => 'Nava de Sotrobal / Macotera, Salamanca, Castilla y Leon, Spain', 'size' => 10];
    $lines[] = ['text' => '', 'size' => 10];
    foreach ($report['sections'] as $section) {
        $lines[] = ['text' => (string) $section['heading'], 'size' => 13];
        if (isset($section['paragraphs'])) {
            foreach ($section['paragraphs'] as $paragraph) {
                foreach (agritwin_report_wrap_text((string) $paragraph, 86) as $wrapped) {
                    $lines[] = ['text' => $wrapped, 'size' => 10];
                }
            }
        }
        if (isset($section['headers'])) {
            $lines[] = ['text' => implode(' | ', array_map('strval', $section['headers'])), 'size' => 10];
            $lines[] = ['text' => str_repeat('-', 88), 'size' => 10];
        }
        foreach (($section['rows'] ?? []) as $row) {
            $text = is_array($row) && count($row) === 2
                ? ((string) $row[0] . ': ' . (string) $row[1])
                : implode(' | ', array_map('strval', (array) $row));
            foreach (agritwin_report_wrap_text($text, 92) as $wrapped) {
                $lines[] = ['text' => $wrapped, 'size' => 9];
            }
        }
        $lines[] = ['text' => '', 'size' => 8];
    }
    $lines[] = ['text' => (string) ($report['copyright'] ?? '© 2026 AgriTwin'), 'size' => 8];
    foreach (agritwin_report_wrap_text((string) ($report['funding_acknowledgement'] ?? agritwin_funding_acknowledgement()), 96) as $wrapped) {
        $lines[] = ['text' => $wrapped, 'size' => 8];
    }

    $content = "";
    foreach ($images as $name => $image) {
        $content .= sprintf(
            "q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n",
            $image['draw_width'],
            $image['draw_height'],
            (float) $image['x'],
            (float) $image['y'],
            $name
        );
    }
    $content .= "BT\n/F1 10 Tf\n50 728 Td\n";
    $currentSize = 10;
    $lineNo = 0;
    foreach ($lines as $line) {
        $size = (int) $line['size'];
        if ($size !== $currentSize) {
            $content .= "/F1 {$size} Tf\n";
            $currentSize = $size;
        }
        if ($lineNo > 0) {
            $content .= "0 -" . max(11, $size + 4) . " Td\n";
        }
        $content .= agritwin_report_pdf_string((string) $line['text']) . " Tj\n";
        $lineNo++;
    }
    $content .= "ET\n";
    $content .= "BT\n/F1 8 Tf\n150 48 Td\n" . agritwin_report_pdf_string((string) ($report['copyright'] ?? '© 2026 AgriTwin')) . " Tj\n";
    foreach (agritwin_report_wrap_text((string) ($report['funding_acknowledgement'] ?? agritwin_funding_acknowledgement()), 86) as $idx => $wrapped) {
        $content .= "0 -" . ($idx === 0 ? 10 : 9) . " Td\n" . agritwin_report_pdf_string($wrapped) . " Tj\n";
    }
    $content .= "ET\n";

    $xobjectEntries = [];
    $objects = [];
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[] = "__PAGE_OBJECT__";
    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}endstream";
    foreach ($images as $name => $image) {
        $objectNumber = count($objects) + 1;
        $xobjectEntries[] = "/{$name} {$objectNumber} 0 R";
        $objects[] = "<< /Type /XObject /Subtype /Image /Width {$image['width_px']} /Height {$image['height_px']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($image['stream']) . " >>\nstream\n{$image['stream']}\nendstream";
    }
    $xobjectResource = $xobjectEntries === [] ? '' : ' /XObject << ' . implode(' ', $xobjectEntries) . ' >>';
    $objects[2] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >>{$xobjectResource} >> /Contents 5 0 R >>";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $idx => $object) {
        $offsets[$idx + 1] = strlen($pdf);
        $pdf .= ($idx + 1) . " 0 obj\n{$object}\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    return $pdf;
}

function agritwin_report_generate(string $type, ?string $lang = null): array
{
    $report = agritwin_report_build($type, $lang);
    $stamp = (new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid')))->format('Ymd-Hi');
    $safeType = preg_replace('/[^a-z0-9-]/', '-', strtolower($report['type'])) ?: 'report';
    $id = 'agritwin-' . $safeType . '-' . $stamp . '-' . substr(hash('sha256', random_bytes(16)), 0, 8);
    $filename = $id . '.pdf';
    $path = agritwin_reports_storage_dir() . '/' . $filename;
    $report['id'] = $id;
    file_put_contents($path, agritwin_report_pdf_content($report), LOCK_EX);
    return $report + [
        'filename' => $filename,
        'path' => $path,
        'url' => 'reports.php?action=download&id=' . rawurlencode($id),
    ];
}

function agritwin_report_path_from_id(string $id): ?string
{
    if (!preg_match('/^agritwin-[a-z0-9-]+-[0-9]{8}-[0-9]{4}-[a-f0-9]{8}$/', $id)) {
        return null;
    }
    $path = agritwin_reports_storage_dir() . '/' . $id . '.pdf';
    return is_file($path) ? $path : null;
}

function agritwin_reports_list(): array
{
    $reports = [];
    foreach (glob(agritwin_reports_storage_dir() . '/agritwin-*.pdf') ?: [] as $path) {
        $reports[] = [
            'id' => basename($path, '.pdf'),
            'filename' => basename($path),
            'generated_at' => date('Y-m-d H:i T', filemtime($path) ?: time()),
            'size_bytes' => filesize($path) ?: 0,
            'url' => 'reports.php?action=download&id=' . rawurlencode(basename($path, '.pdf')),
        ];
    }
    usort($reports, static fn (array $a, array $b): int => strcmp($b['generated_at'], $a['generated_at']));
    return $reports;
}
