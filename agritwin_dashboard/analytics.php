<?php
require_once __DIR__ . '/includes/hmi_layout.php';
require_once __DIR__ . '/includes/statistics_engine.php';

$range = in_array($_GET['range'] ?? '24h', ['24h', '7d', '30d'], true) ? (string) ($_GET['range'] ?? '24h') : '24h';
$stationFilter = (string) ($_GET['station'] ?? 'all');
$cropFilter = (string) ($_GET['crop'] ?? 'all');
$analytics = agritwin_full_analytics();
$stations = agritwin_sensor_stations();

function agritwin_chart_palette(): array
{
    return ['#2f6f4e', '#245a7a', '#9a6500', '#6b5b95', '#8f4f4f', '#3f7d7a'];
}

function agritwin_chart_scale(array $series, int $height = 170): array
{
    $values = [];
    foreach ($series as $line) {
        foreach ($line['points'] ?? [] as $point) {
            $values[] = (float) $point['value'];
        }
    }
    $min = min($values ?: [0]);
    $max = max($values ?: [1]);
    if ($min === $max) {
        $min -= 1;
        $max += 1;
    }
    return ['min' => $min, 'max' => $max, 'range' => $max - $min, 'height' => $height];
}

function agritwin_svg_multi_line_chart(array $series, string $title, string $unit = ''): string
{
    $palette = agritwin_chart_palette();
    $scale = agritwin_chart_scale($series);
    $width = 760;
    $height = 230;
    $plotW = 690;
    $plotH = 160;
    $svg = '<svg class="analytics-chart analytics-chart-large" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . agritwin_h($title) . '">';
    $svg .= '<rect class="chart-bg" x="0" y="0" width="' . $width . '" height="' . $height . '" rx="12"></rect>';
    for ($i = 0; $i <= 4; $i++) {
        $y = 24 + ($i * ($plotH / 4));
        $svg .= '<line class="chart-grid" x1="48" y1="' . $y . '" x2="738" y2="' . $y . '"></line>';
    }
    foreach ($series as $idx => $line) {
        $points = [];
        $pointCount = max(1, count($line['points']) - 1);
        foreach ($line['points'] as $pIdx => $point) {
            $x = 48 + ($pIdx * ($plotW / $pointCount));
            $y = 24 + (($scale['max'] - (float) $point['value']) / $scale['range']) * $plotH;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
        $color = $palette[$idx % count($palette)];
        $svg .= '<polyline class="chart-line" style="stroke:' . $color . '" points="' . agritwin_h(implode(' ', $points)) . '"></polyline>';
        foreach ($line['points'] as $pIdx => $point) {
            if ($pIdx === count($line['points']) - 1 || $pIdx === 0) {
                [$x, $y] = explode(',', $points[$pIdx]);
                $svg .= '<circle class="chart-dot" style="stroke:' . $color . '" cx="' . $x . '" cy="' . $y . '" r="4"><title>' . agritwin_h($line['label'] . ': ' . $point['value'] . ' ' . $unit) . '</title></circle>';
            }
        }
        $legendX = 54 + (($idx % 3) * 210);
        $legendY = 204 + (intdiv($idx, 3) * 16);
        $svg .= '<g class="chart-legend"><rect x="' . $legendX . '" y="' . ($legendY - 9) . '" width="12" height="8" rx="2" style="fill:' . $color . '"></rect><text x="' . ($legendX + 18) . '" y="' . $legendY . '">' . agritwin_h($line['label']) . '</text></g>';
    }
    $svg .= '</svg>';
    return $svg;
}

function agritwin_svg_bar_line_chart(array $bars, array $line, string $title): string
{
    $width = 760;
    $height = 230;
    $barValues = array_map(static fn (array $point): float => (float) $point['value'], $bars);
    $lineValues = array_map(static fn (array $point): float => (float) $point['value'], $line);
    $barMax = max($barValues ?: [1]);
    $lineMin = min($lineValues ?: [0]);
    $lineMax = max($lineValues ?: [1]);
    $lineRange = max(1, $lineMax - $lineMin);
    $svg = '<svg class="analytics-chart analytics-chart-large" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . agritwin_h($title) . '">';
    $svg .= '<rect class="chart-bg" x="0" y="0" width="' . $width . '" height="' . $height . '" rx="12"></rect>';
    $count = max(1, count($bars));
    $points = [];
    foreach ($bars as $idx => $bar) {
        $x = 55 + ($idx * (660 / $count));
        $h = max(4, ((float) $bar['value'] / max(0.1, $barMax)) * 125);
        $y = 170 - $h;
        $svg .= '<rect class="chart-bar" x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="42" height="' . round($h, 1) . '" rx="5"><title>' . agritwin_h($bar['label'] . ': ' . $bar['value']) . '</title></rect>';
        $linePoint = $line[$idx] ?? $line[array_key_last($line)];
        $lineY = 35 + (($lineMax - (float) $linePoint['value']) / $lineRange) * 130;
        $points[] = round($x + 21, 1) . ',' . round($lineY, 1);
        $svg .= '<text x="' . round($x, 1) . '" y="192">' . agritwin_h($bar['label']) . '</text>';
    }
    $svg .= '<polyline class="chart-line accent" points="' . agritwin_h(implode(' ', $points)) . '"></polyline>';
    $svg .= '<g class="chart-legend"><rect x="54" y="210" width="12" height="8" rx="2"></rect><text x="72" y="218">mm</text><line x1="128" y1="214" x2="154" y2="214" class="legend-line"></line><text x="162" y="218">degC</text></g>';
    $svg .= '</svg>';
    return $svg;
}

function agritwin_svg_horizontal_bars(array $rows, string $valueKey, string $labelKey, string $unit = ''): string
{
    if (count($rows) === 0) {
        return '<div class="empty-state">' . agritwin_h(agritwin_t('not_available')) . '</div>';
    }
    $max = max(array_map(static fn (array $row): float => (float) $row[$valueKey], $rows)) ?: 1;
    $height = 42 + count($rows) * 34;
    $svg = '<svg class="analytics-chart" viewBox="0 0 520 ' . $height . '" role="img">';
    $svg .= '<rect class="chart-bg" x="0" y="0" width="520" height="' . $height . '" rx="12"></rect>';
    foreach ($rows as $idx => $row) {
        $y = 28 + $idx * 34;
        $w = max(8, ((float) $row[$valueKey] / $max) * 310);
        $svg .= '<text class="bar-label" x="22" y="' . ($y + 14) . '">' . agritwin_h((string) $row[$labelKey]) . '</text>';
        $svg .= '<rect class="chart-bar" x="170" y="' . $y . '" width="' . round($w, 1) . '" height="18" rx="5"></rect>';
        $svg .= '<text class="bar-value" x="' . (185 + round($w, 1)) . '" y="' . ($y + 14) . '">' . agritwin_h((string) $row[$valueKey] . $unit) . '</text>';
    }
    return $svg . '</svg>';
}

function agritwin_svg_gauge(float $value, string $label): string
{
    $value = max(0, min(100, $value));
    $dash = round(($value / 100) * 251, 1);
    return '<svg class="analytics-gauge" viewBox="0 0 150 92" role="img" aria-label="' . agritwin_h($label) . '"><path class="gauge-track" d="M20 75 A55 55 0 0 1 130 75"></path><path class="gauge-value" stroke-dasharray="' . $dash . ' 251" d="M20 75 A55 55 0 0 1 130 75"></path><text x="75" y="64">' . agritwin_h((string) round($value)) . '</text><text class="gauge-label" x="75" y="84">' . agritwin_h($label) . '</text></svg>';
}

function agritwin_average_timeseries(array $series, string $label): array
{
    $first = $series[0]['points'] ?? [];
    $points = [];
    foreach ($first as $idx => $point) {
        $values = [];
        foreach ($series as $line) {
            if (isset($line['points'][$idx]['value'])) {
                $values[] = (float) $line['points'][$idx]['value'];
            }
        }
        $points[] = [
            'time' => $point['time'],
            'label' => $point['label'],
            'value' => count($values) > 0 ? round(array_sum($values) / count($values), 2) : 0,
        ];
    }
    return ['label' => $label, 'points' => $points];
}

agritwin_render_page('analytics', agritwin_t('analytics'), function () use ($analytics, $stations, $range, $stationFilter, $cropFilter): void {
    $summary = $analytics['summary'];
    $soilRows = array_values(array_filter($analytics['sensor_stations']['rankings'], static fn (array $row): bool => ($cropFilter === 'all' || $row['crop'] === $cropFilter) && ($stationFilter === 'all' || $row['station_id'] === $stationFilter)));
    $weatherTs = $analytics['weather']['timeseries'];
    $weatherOverview = [
        agritwin_average_timeseries($weatherTs['air_temperature'], agritwin_t('temperature')),
        agritwin_average_timeseries($weatherTs['relative_humidity'], agritwin_t('humidity')),
        agritwin_average_timeseries($weatherTs['wind_speed'], agritwin_t('wind')),
    ];
    $soilTs = agritwin_metric_timeseries('soil_moisture', $stationFilter, $range);
    $soilTempTs = agritwin_metric_timeseries('soil_temperature', $stationFilter, $range);
    if ($cropFilter !== 'all') {
        $soilTs['series'] = array_values(array_filter($soilTs['series'], static fn (array $line): bool => $line['crop'] === $cropFilter));
        $soilTempTs['series'] = array_values(array_filter($soilTempTs['series'], static fn (array $line): bool => $line['crop'] === $cropFilter));
    }
    $rainBars = $weatherTs['rainfall'][0]['points'] ?? [];
    $tempLine = $weatherTs['air_temperature'][0]['points'] ?? [];
    ?>
    <section class="view-section analytics-hero">
        <div class="section-heading">
            <div>
                <p class="eyebrow"><?= agritwin_h($summary['data_source']) ?> · <?= agritwin_h($summary['location']) ?></p>
                <h2><?= agritwin_h(agritwin_t('analytics_overview')) ?></h2>
                <p><?= agritwin_h(agritwin_t('analytics_operational_picture')) ?></p>
            </div>
            <?= agritwin_badge('normal') ?>
        </div>
        <form class="analytics-controls" method="get">
            <label><?= agritwin_h(agritwin_t('time_range')) ?>
                <select name="range">
                    <?php foreach (['24h', '7d', '30d'] as $option): ?><option value="<?= agritwin_h($option) ?>" <?= $range === $option ? 'selected' : '' ?>><?= agritwin_h($option) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label><?= agritwin_h(agritwin_t('station_filter')) ?>
                <select name="station">
                    <option value="all"><?= agritwin_h(agritwin_t('all_stations')) ?></option>
                    <?php foreach ($stations as $station): ?><option value="<?= agritwin_h($station['id']) ?>" <?= $stationFilter === $station['id'] ? 'selected' : '' ?>><?= agritwin_h($station['id']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label><?= agritwin_h(agritwin_t('crop_filter')) ?>
                <select name="crop">
                    <option value="all"><?= agritwin_h(agritwin_t('all_crops')) ?></option>
                    <?php foreach (array_unique(array_column($stations, 'crop')) as $crop): ?><option value="<?= agritwin_h($crop) ?>" <?= $cropFilter === $crop ? 'selected' : '' ?>><?= agritwin_h($crop) ?></option><?php endforeach; ?>
                </select>
            </label>
            <button class="primary-action" type="submit"><?= agritwin_h(agritwin_t('refresh')) ?></button>
            <a class="secondary-action" href="../api/analytics/summary"><?= agritwin_h(agritwin_t('export_json')) ?></a>
        </form>
        <div class="analytics-summary-grid">
            <article><span><?= agritwin_h(agritwin_t('active_sensor_stations')) ?></span><strong><?= (int) $summary['station_count'] ?></strong></article>
            <article><span><?= agritwin_h(agritwin_t('average_soil_moisture')) ?></span><strong><?= agritwin_h((string) $summary['average_soil_moisture_pct']) ?>% VWC</strong></article>
            <article><span><?= agritwin_h(agritwin_t('latest_weather_source')) ?></span><strong><?= agritwin_h($summary['latest_weather_source']) ?></strong></article>
            <article><span><?= agritwin_h(agritwin_t('pest_risk')) ?></span><strong><?= agritwin_h($summary['pest_risk_level']) ?></strong></article>
            <article><span><?= agritwin_h(agritwin_t('data_freshness')) ?></span><strong><?= (int) $summary['freshness_score'] ?>%</strong></article>
        </div>
        <div class="insight-strip">
            <?php foreach ($summary['insights'] as $insight): ?><p><?= agritwin_h($insight) ?></p><?php endforeach; ?>
        </div>
    </section>

    <section class="view-section analytics-section">
        <div class="section-heading"><div><h2><?= agritwin_h(agritwin_t('weather_microclimate')) ?></h2><p><?= agritwin_h(agritwin_t('weather_microclimate_note')) ?></p></div></div>
        <div class="analytics-two-col">
            <div class="analytics-panel">
                <h3><?= agritwin_h(agritwin_t('temperature_humidity_wind')) ?></h3>
                <?= agritwin_svg_multi_line_chart($weatherOverview, agritwin_t('temperature_humidity_wind')) ?>
            </div>
            <div class="analytics-panel">
                <h3><?= agritwin_h(agritwin_t('rainfall_temperature')) ?></h3>
                <?= agritwin_svg_bar_line_chart($rainBars, $tempLine, agritwin_t('rainfall_temperature')) ?>
            </div>
        </div>
        <div class="analytics-card-row">
            <?php foreach ($analytics['weather']['forecast_cards'] as $card): ?>
                <article><span><?= agritwin_h($card['label']) ?></span><strong><?= agritwin_h($card['value']) ?></strong><p><?= agritwin_h($card['detail']) ?></p></article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="view-section analytics-section">
        <div class="section-heading"><div><h2><?= agritwin_h(agritwin_t('soil_sensor_stations')) ?></h2><p><?= agritwin_h(agritwin_t('soil_sensor_note')) ?></p></div></div>
        <div class="analytics-two-col">
            <div class="analytics-panel"><h3><?= agritwin_h(agritwin_t('soil_moisture_trend')) ?></h3><?= agritwin_svg_multi_line_chart($soilTs['series'], agritwin_t('soil_moisture_trend'), '% VWC') ?></div>
            <div class="analytics-panel"><h3><?= agritwin_h(agritwin_t('soil_temperature_trend')) ?></h3><?= agritwin_svg_multi_line_chart($soilTempTs['series'], agritwin_t('soil_temperature_trend'), ' degC') ?></div>
        </div>
        <div class="analytics-two-col">
            <div class="analytics-panel"><h3><?= agritwin_h(agritwin_t('station_irrigation_need')) ?></h3><?= agritwin_svg_horizontal_bars($soilRows, 'irrigation_need_score', 'station_id', '%') ?></div>
            <div class="table-wrap analytics-panel">
                <h3><?= agritwin_h(agritwin_t('latest_readings')) ?></h3>
                <table class="status-table"><thead><tr><th><?= agritwin_h(agritwin_t('station_reading')) ?></th><th><?= agritwin_h(agritwin_t('crop')) ?></th><th><?= agritwin_h(agritwin_t('soil_moisture')) ?></th><th><?= agritwin_h(agritwin_t('trend')) ?></th><th><?= agritwin_h(agritwin_t('data_freshness')) ?></th></tr></thead><tbody>
                <?php foreach ($soilRows as $row): ?><tr><td><?= agritwin_h($row['station_id']) ?></td><td><?= agritwin_h($row['crop']) ?></td><td><?= agritwin_h((string) $row['soil_moisture_pct']) ?>% VWC</td><td><?= agritwin_h($row['soil_moisture_pct'] < 24 ? '↓' : '→') ?></td><td><?= agritwin_badge('normal') ?></td></tr><?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </section>

    <section class="view-section analytics-section">
        <div class="section-heading"><div><h2><?= agritwin_h(agritwin_t('irrigation_performance')) ?></h2><p><?= agritwin_h(agritwin_t('irrigation_performance_note')) ?></p></div></div>
        <div class="analytics-three-col">
            <article class="analytics-panel"><?= agritwin_svg_gauge((float) $analytics['irrigation']['water_balance_score'], agritwin_t('water_balance_score')) ?></article>
            <article class="analytics-panel"><span><?= agritwin_h(agritwin_t('irrigation_deficit')) ?></span><strong><?= agritwin_h((string) $analytics['irrigation']['estimated_irrigation_deficit_pct_vwc']) ?>% VWC</strong><p><?= agritwin_h(agritwin_t('irrigation_deficit_note')) ?></p></article>
            <article class="analytics-panel"><span><?= agritwin_h(agritwin_t('pump_energy')) ?></span><strong><?= agritwin_h((string) $analytics['irrigation']['pump_energy']['avg']) ?> kWh</strong><p><?= agritwin_h(agritwin_t('pump_energy_note')) ?></p></article>
        </div>
        <div class="timeline">
            <?php foreach ($analytics['irrigation']['timeline']['recommendation_timeline'] as $event): ?><div><strong><?= agritwin_h($event['time']) ?></strong><span><?= agritwin_h($event['label']) ?></span><?= agritwin_badge($event['status']) ?></div><?php endforeach; ?>
        </div>
    </section>

    <section class="view-section analytics-section">
        <div class="section-heading"><div><h2><?= agritwin_h(agritwin_t('pest_disease_risk')) ?></h2><p><?= agritwin_h(agritwin_t('pest_disease_note')) ?></p></div></div>
        <div class="analytics-two-col">
            <div class="analytics-panel"><?= agritwin_svg_gauge((float) $analytics['pest_disease']['risk_index'], agritwin_t('risk_index')) ?></div>
            <div class="analytics-panel"><h3><?= agritwin_h(agritwin_t('risk_trend')) ?></h3><?= agritwin_svg_multi_line_chart([['label' => agritwin_t('risk_index'), 'points' => $analytics['pest_disease']['timeseries']['points']]], agritwin_t('risk_trend')) ?></div>
        </div>
        <p class="source-note"><?= agritwin_h(agritwin_t('gdd_accumulation')) ?>: <?= agritwin_h((string) $analytics['pest_disease']['timeseries']['gdd_accumulation']) ?> · <?= agritwin_h(agritwin_t('pest_disease_insight')) ?></p>
    </section>

    <section class="view-section analytics-section">
        <div class="section-heading"><div><h2><?= agritwin_h(agritwin_t('kpi_operational_performance')) ?></h2><p><?= agritwin_h(agritwin_t('kpi_operational_note')) ?></p></div></div>
        <div class="kpi-grid">
            <?php foreach ($analytics['kpis'] as $kpi): ?><div><strong><?= agritwin_h($kpi['metric']) ?></strong><p><?= agritwin_h($kpi['value']) ?></p><span><?= agritwin_h($kpi['status']) ?></span></div><?php endforeach; ?>
        </div>
    </section>

    <section class="view-section analytics-section">
        <div class="section-heading"><div><h2><?= agritwin_h(agritwin_t('service_freshness')) ?></h2><p><?= agritwin_h(agritwin_t('service_freshness_note')) ?></p></div></div>
        <div class="table-wrap">
            <table class="status-table"><thead><tr><th>OpenAgri</th><th><?= agritwin_h(agritwin_t('status')) ?></th><th><?= agritwin_h(agritwin_t('last_check')) ?></th><th><?= agritwin_h(agritwin_t('latency')) ?></th><th><?= agritwin_h(agritwin_t('integration_priority')) ?></th></tr></thead><tbody>
            <?php foreach ($analytics['service_freshness'] as $service): ?><tr><td><?= agritwin_h($service['service']) ?></td><td><?= agritwin_badge($service['status']) ?></td><td><?= agritwin_h($service['last_check']) ?></td><td><?= $service['latency_ms'] !== null ? agritwin_h((string) $service['latency_ms']) . ' ms' : agritwin_h(agritwin_t('not_available')) ?></td><td><?= $service['required_for_d1'] ? agritwin_h(agritwin_t('core_services')) : agritwin_h(agritwin_t('additional_operational_services')) ?></td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
    </section>
    <?php
});
