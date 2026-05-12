<?php
namespace AgriTwin\Kpi;

final class KpiCalculator
{
    public function summarize(array $events, ?string $mode = null): array
    {
        $events = $this->filterMode($events, $mode);

        return [
            'architecture_mode' => $mode ?? 'all',
            'generated_at' => gmdate('c'),
            'event_count' => count($events),
            'irrigation_schedule_latency' => $this->irrigationScheduleLatency($events),
            'water_stress_forecast_mae' => $this->waterStressMae($events),
            'nitrogen_recommendation_error' => $this->nitrogenRecommendationError($events),
            'data_continuity_uptime' => $this->dataContinuityUptime($events),
            'network_usage' => $this->networkUsage($events),
            'compute_usage' => $this->computeUsage($events),
            'energy_usage' => $this->energyUsage($events),
            'standards_interoperability_switchability' => $this->standardsEvidence($events),
        ];
    }

    public function compare(array $events, string $leftMode, string $rightMode): array
    {
        $left = $this->summarize($events, $leftMode);
        $right = $this->summarize($events, $rightMode);

        return [
            'generated_at' => gmdate('c'),
            'baseline_mode' => $leftMode,
            'comparison_mode' => $rightMode,
            'baseline' => $left,
            'comparison' => $right,
            'deltas' => [
                'irrigation_schedule_latency_seconds_avg' => $this->delta($left, $right, ['irrigation_schedule_latency', 'average_seconds']),
                'water_stress_mae_m3_m3' => $this->delta($left, $right, ['water_stress_forecast_mae', 'mae_m3_m3']),
                'nitrogen_mae_kg_ha' => $this->delta($left, $right, ['nitrogen_recommendation_error', 'mae_kg_ha']),
                'uptime_ratio' => $this->delta($left, $right, ['data_continuity_uptime', 'ratio']),
                'network_total_mb' => $this->delta($left, $right, ['network_usage', 'total_mb']),
                'cpu_hours' => $this->delta($left, $right, ['compute_usage', 'cpu_hours']),
                'gpu_hours' => $this->delta($left, $right, ['compute_usage', 'gpu_hours']),
                'forecast_cycle_energy_kwh' => $this->delta($left, $right, ['energy_usage', 'forecast_cycle_kwh']),
                'farming_operation_energy_kwh' => $this->delta($left, $right, ['energy_usage', 'farming_operation_kwh']),
            ],
        ];
    }

    public function irrigationScheduleLatency(array $events): array
    {
        $received = [];
        foreach ($events as $event) {
            if (($event['event_type'] ?? '') === 'input_packet_received' && isset($event['cycle_id'], $event['received_at'])) {
                $received[$event['cycle_id']] = $this->timestamp($event['received_at']);
            }
        }

        $latencies = [];
        $missing = 0;
        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'irrigation_schedule_generated') {
                continue;
            }
            $cycleId = $event['cycle_id'] ?? null;
            $generatedAt = isset($event['generated_at']) ? $this->timestamp($event['generated_at']) : null;
            if (!$cycleId || $generatedAt === null || !isset($received[$cycleId])) {
                $missing++;
                continue;
            }
            $latencies[] = max(0, $generatedAt - $received[$cycleId]);
        }

        return [
            'average_seconds' => $this->average($latencies),
            'minimum_seconds' => $latencies === [] ? null : min($latencies),
            'maximum_seconds' => $latencies === [] ? null : max($latencies),
            'samples' => count($latencies),
            'missing_pairs' => $missing,
            'definition' => 'schedule_generated_at minus input_packet_received_at per forecast cycle',
        ];
    }

    public function waterStressMae(array $events): array
    {
        $errors = [];
        $missing = 0;
        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'water_stress_forecast') {
                continue;
            }
            if (!isset($event['predicted_soil_moisture_m3_m3'], $event['ground_truth_soil_moisture_m3_m3'])) {
                $missing++;
                continue;
            }
            $errors[] = abs((float) $event['predicted_soil_moisture_m3_m3'] - (float) $event['ground_truth_soil_moisture_m3_m3']);
        }

        return [
            'mae_m3_m3' => $this->average($errors),
            'samples' => count($errors),
            'missing_ground_truth' => $missing,
            'definition' => 'mean absolute error between predicted soil moisture and probe reading',
        ];
    }

    public function nitrogenRecommendationError(array $events): array
    {
        $errors = [];
        $missing = 0;
        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'nitrogen_recommendation') {
                continue;
            }
            if (!isset($event['recommended_nitrogen_kg_ha'], $event['agronomist_reference_nitrogen_kg_ha'])) {
                $missing++;
                continue;
            }
            $errors[] = abs((float) $event['recommended_nitrogen_kg_ha'] - (float) $event['agronomist_reference_nitrogen_kg_ha']);
        }

        return [
            'mae_kg_ha' => $this->average($errors),
            'samples' => count($errors),
            'missing_reference' => $missing,
            'definition' => 'mean absolute error between DSS recommendation and agronomist reference prescription',
        ];
    }

    public function dataContinuityUptime(array $events): array
    {
        $total = 0;
        $onTime = 0;
        $missingProcessedAt = 0;
        $late = 0;

        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'sensor_packet_received') {
                continue;
            }
            $total++;
            if (!isset($event['packet_created_at'], $event['processed_at'])) {
                $missingProcessedAt++;
                continue;
            }
            $ageSeconds = $this->timestamp($event['processed_at']) - $this->timestamp($event['packet_created_at']);
            if ($ageSeconds <= 1800) {
                $onTime++;
            } else {
                $late++;
            }
        }

        return [
            'ratio' => $total === 0 ? null : $onTime / $total,
            'packets_total' => $total,
            'packets_processed_within_30_minutes' => $onTime,
            'late_packets' => $late,
            'missing_processed_at' => $missingProcessedAt,
            'expected_packet_interval_minutes' => 15,
            'definition' => '15-minute sensor packets processed within 30 minutes divided by total received packets',
        ];
    }

    public function networkUsage(array $events): array
    {
        $sent = 0;
        $received = 0;
        $windowSeconds = 0;
        $peakUplink = null;
        $peakDownlink = null;
        $byService = [];

        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'network_usage') {
                continue;
            }
            $service = (string) ($event['service'] ?? 'unknown');
            $eventSent = (int) ($event['bytes_sent'] ?? 0);
            $eventReceived = (int) ($event['bytes_received'] ?? 0);
            $sent += $eventSent;
            $received += $eventReceived;
            $byService[$service]['bytes_sent'] = ($byService[$service]['bytes_sent'] ?? 0) + $eventSent;
            $byService[$service]['bytes_received'] = ($byService[$service]['bytes_received'] ?? 0) + $eventReceived;
            if (isset($event['window_start'], $event['window_end'])) {
                $windowSeconds += max(0, $this->timestamp($event['window_end']) - $this->timestamp($event['window_start']));
            }
            $peakUplink = max($peakUplink ?? 0.0, (float) ($event['peak_uplink_mbps'] ?? 0));
            $peakDownlink = max($peakDownlink ?? 0.0, (float) ($event['peak_downlink_mbps'] ?? 0));
        }

        foreach ($byService as $service => $values) {
            $byService[$service]['total_mb'] = $this->bytesToMb($values['bytes_sent'] + $values['bytes_received']);
        }

        $totalMb = $this->bytesToMb($sent + $received);
        $hours = $windowSeconds > 0 ? $windowSeconds / 3600 : null;

        return [
            'bytes_sent' => $sent,
            'bytes_received' => $received,
            'total_mb' => $totalMb,
            'mb_per_hour' => $hours ? $totalMb / $hours : null,
            'peak_uplink_mbps' => $peakUplink,
            'peak_downlink_mbps' => $peakDownlink,
            'by_service' => $byService,
            'definition' => 'bytes sent and received per service and architecture mode, normalized to MB/hour where a window exists',
        ];
    }

    public function computeUsage(array $events): array
    {
        $cpuHours = 0.0;
        $gpuHours = 0.0;
        $samples = 0;

        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'compute_usage') {
                continue;
            }
            $samples++;
            if (isset($event['cpu_hours'])) {
                $cpuHours += (float) $event['cpu_hours'];
            } elseif (isset($event['duration_seconds'], $event['cpu_utilization_ratio'])) {
                $cpuHours += ((float) $event['duration_seconds'] / 3600) * (float) $event['cpu_utilization_ratio'];
            }
            if (isset($event['gpu_hours'])) {
                $gpuHours += (float) $event['gpu_hours'];
            } elseif (isset($event['duration_seconds'], $event['gpu_utilization_ratio'])) {
                $gpuHours += ((float) $event['duration_seconds'] / 3600) * (float) $event['gpu_utilization_ratio'];
            }
        }

        return [
            'cpu_hours' => $cpuHours,
            'gpu_hours' => $gpuHours,
            'samples' => $samples,
            'definition' => 'measured CPU/GPU hours where available, otherwise duration multiplied by utilization ratio',
        ];
    }

    public function energyUsage(array $events): array
    {
        $forecast = 0.0;
        $operation = 0.0;
        $sources = [];
        $samples = 0;

        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'energy_usage') {
                continue;
            }
            $samples++;
            $kwh = (float) ($event['kwh'] ?? 0);
            if (($event['scope'] ?? '') === 'farming_operation') {
                $operation += $kwh;
            } else {
                $forecast += $kwh;
            }
            if (isset($event['source'])) {
                $sources[] = $event['source'];
            }
        }

        return [
            'forecast_cycle_kwh' => $forecast,
            'farming_operation_kwh' => $operation,
            'samples' => $samples,
            'sources' => $sources,
            'definition' => 'kWh for forecast processing/transmission and farming operations when meter or estimate data exists',
        ];
    }

    public function standardsEvidence(array $events): array
    {
        $evidence = [];
        foreach ($events as $event) {
            if (($event['event_type'] ?? '') !== 'standards_evidence') {
                continue;
            }
            $category = (string) ($event['category'] ?? 'uncategorized');
            foreach ((array) ($event['evidence'] ?? []) as $item) {
                $evidence[$category][] = (string) $item;
            }
        }

        foreach ($evidence as $category => $items) {
            $evidence[$category] = array_values(array_unique($items));
        }

        return [
            'categories' => $evidence,
            'category_count' => count($evidence),
            'definition' => 'auditable evidence for standards, interfaces, module replaceability, and hardware reuse',
        ];
    }

    private function filterMode(array $events, ?string $mode): array
    {
        if ($mode === null || $mode === 'all') {
            return $events;
        }
        return array_values(array_filter($events, fn (array $event): bool => ($event['architecture_mode'] ?? null) === $mode));
    }

    private function delta(array $left, array $right, array $path): ?float
    {
        $leftValue = $this->valueAt($left, $path);
        $rightValue = $this->valueAt($right, $path);
        if (!is_numeric($leftValue) || !is_numeric($rightValue)) {
            return null;
        }
        return (float) $rightValue - (float) $leftValue;
    }

    private function valueAt(array $data, array $path): mixed
    {
        $value = $data;
        foreach ($path as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return null;
            }
            $value = $value[$part];
        }
        return $value;
    }

    private function timestamp(string $value): int
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Invalid timestamp: $value");
        }
        return $timestamp;
    }

    private function average(array $values): ?float
    {
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function bytesToMb(int $bytes): float
    {
        return $bytes / 1024 / 1024;
    }
}
