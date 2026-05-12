#!/usr/bin/env php
<?php
require_once __DIR__ . '/../src/kpi/autoload.php';

use AgriTwin\Kpi\KpiCalculator;
use AgriTwin\Kpi\KpiEventStore;
use AgriTwin\Kpi\KpiExporter;
use AgriTwin\Kpi\KpiSampleData;

$command = $argv[1] ?? 'help';
$root = dirname(__DIR__);
$storePath = getenv('AGRITWIN_KPI_STORE') ?: $root . '/var/kpi/events.jsonl';
$store = new KpiEventStore($storePath);
$calculator = new KpiCalculator();
$exporter = new KpiExporter();

try {
    match ($command) {
        'collect-kpis' => collectKpis($store, $argv),
        'export-kpis' => exportKpis($store, $calculator, $exporter, $argv, $root),
        'compare-ads' => compareAds($store, $calculator, $exporter, $argv, $root),
        'generate-d1-evidence' => generateD1Evidence($store, $calculator, $exporter, $root),
        default => help(),
    };
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

function collectKpis(KpiEventStore $store, array $argv): void
{
    $append = in_array('--append', $argv, true);
    $events = KpiSampleData::events();
    if ($append) {
        foreach ($events as $event) {
            $store->append($event);
        }
    } else {
        $store->replaceAll($events);
    }
    echo 'Collected ' . count($events) . ' synthetic KPI sample events into ' . $store->path() . PHP_EOL;
}

function exportKpis(KpiEventStore $store, KpiCalculator $calculator, KpiExporter $exporter, array $argv, string $root): void
{
    $mode = option($argv, '--mode') ?? 'all';
    $format = option($argv, '--format') ?? 'json';
    $events = eventsOrSamples($store);
    $summary = $calculator->summarize($events, $mode === 'all' ? null : $mode);
    $output = option($argv, '--output') ?? $root . '/exports/kpi/kpi_summary.' . ($format === 'csv' ? 'csv' : 'json');

    if ($format === 'csv') {
        $exporter->writeCsv($summary, $output);
    } else {
        $exporter->writeJson($summary, $output);
    }
    echo "Exported KPI summary to $output" . PHP_EOL;
}

function compareAds(KpiEventStore $store, KpiCalculator $calculator, KpiExporter $exporter, array $argv, string $root): void
{
    $left = $argv[2] ?? 'cloud';
    $right = $argv[3] ?? 'mixed';
    $events = eventsOrSamples($store);
    $comparison = $calculator->compare($events, $left, $right);
    $output = option($argv, '--output') ?? $root . '/docs/evidence/generated/ads_comparison.md';
    $exporter->writeMarkdownReport($comparison, $output);
    echo "Wrote ADS comparison report to $output" . PHP_EOL;
}

function generateD1Evidence(KpiEventStore $store, KpiCalculator $calculator, KpiExporter $exporter, string $root): void
{
    $events = $store->all();
    if ($events === []) {
        $events = KpiSampleData::events();
        $store->replaceAll($events);
    }

    $summary = $calculator->summarize($events);
    $comparison = $calculator->compare($events, 'cloud', 'mixed');
    $exporter->writeJson($events, $root . '/fixtures/kpi/sample_events.json');
    $exporter->writeJson($summary, $root . '/docs/evidence/generated/kpi_summary.json');
    $exporter->writeCsv($summary, $root . '/docs/evidence/generated/kpi_summary.csv');
    $exporter->writeMarkdownReport($comparison, $root . '/docs/evidence/generated/ads_comparison.md');

    echo 'Generated D1 KPI evidence under docs/evidence/generated/' . PHP_EOL;
}

function eventsOrSamples(KpiEventStore $store): array
{
    $events = $store->all();
    return $events === [] ? KpiSampleData::events() : $events;
}

function option(array $argv, string $name): ?string
{
    foreach ($argv as $arg) {
        if (str_starts_with($arg, $name . '=')) {
            return substr($arg, strlen($name) + 1);
        }
    }
    return null;
}

function help(): void
{
    echo <<<TXT
AgriTwin KPI CLI

Usage:
  php scripts/agritwin-kpi.php collect-kpis [--append]
  php scripts/agritwin-kpi.php export-kpis [--mode=all|cloud|mixed] [--format=json|csv] [--output=path]
  php scripts/agritwin-kpi.php compare-ads cloud mixed [--output=path]
  php scripts/agritwin-kpi.php generate-d1-evidence

Environment:
  AGRITWIN_KPI_STORE=var/kpi/events.jsonl

TXT;
}
