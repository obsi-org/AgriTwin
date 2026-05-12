<?php
namespace AgriTwin\OpenAgri\Adapters;

final class Normalizers
{
    public static function weatherForecast(array $payload, array $context = []): array
    {
        $records = [];
        $items = self::graphOrArray($payload);
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $type = $item['measurement_type'] ?? null;
            $value = $item['value'] ?? null;
            $timestamp = $item['timestamp'] ?? $item['resultTime'] ?? null;
            if ($type !== null && $value !== null) {
                $records[$timestamp ?? 'current'][$type] = $value;
            }
        }

        if (isset($payload['data']['main']) || isset($payload['data']['wind'])) {
            $timestamp = isset($payload['data']['dt']) ? gmdate('c', (int) $payload['data']['dt']) : gmdate('c');
            $records[$timestamp] = [
                'ambient_temperature' => $payload['data']['main']['temp'] ?? null,
                'ambient_humidity' => $payload['data']['main']['humidity'] ?? null,
                'wind_speed' => $payload['data']['wind']['speed'] ?? null,
                'rainfall' => $payload['data']['rain']['1h'] ?? $payload['data']['rain']['3h'] ?? null,
            ];
        }

        if ($records === []) {
            $records[gmdate('c')] = [
                'ambient_temperature' => self::firstValue($payload, ['temperature', 'temp']),
                'rainfall' => self::firstValue($payload, ['rainfall', 'rain', 'precipitation']),
                'wind_speed' => self::firstValue($payload, ['wind_speed', 'wind']),
                'gdd' => self::firstValue($payload, ['gdd', 'growing_degree_days']),
                'et0' => self::firstValue($payload, ['et0', 'eto']),
            ];
        }

        return [
            'record_type' => 'weather_forecast',
            'farm_id' => $context['farm_id'] ?? null,
            'parcel_id' => $context['parcel_id'] ?? null,
            'provider' => 'openagri_weather',
            'confidence' => self::confidence($context),
            'forecasts' => array_map(
                fn ($timestamp, $vars): array => [
                    'timestamp' => $timestamp,
                    'temperature_deg_c' => self::number($vars['ambient_temperature'] ?? $vars['temperature'] ?? null),
                    'rainfall_mm' => self::number($vars['rainfall'] ?? $vars['precipitation'] ?? null),
                    'wind_speed_m_s' => self::number($vars['wind_speed'] ?? null),
                    'gdd' => self::number($vars['gdd'] ?? null),
                    'et0_mm_day' => self::number($vars['et0'] ?? $vars['eto'] ?? null),
                ],
                array_keys($records),
                array_values($records)
            ),
            'source' => self::source('weather', $context),
        ];
    }

    public static function irrigation(array $payload, array $context = []): array
    {
        $items = self::graphOrArray($payload);
        $etoValues = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $value = $item['hasSimpleResult'] ?? $item['eto'] ?? $item['et0'] ?? $item['value'] ?? null;
            if ($value !== null) {
                $etoValues[] = [
                    'date' => $item['resultTime'] ?? $item['date'] ?? null,
                    'et0_mm_day' => self::number($value),
                ];
            }
        }

        return [
            'record_type' => 'irrigation_advisory',
            'farm_id' => $context['farm_id'] ?? null,
            'parcel_id' => $context['parcel_id'] ?? null,
            'et0_series' => $etoValues,
            'kc' => self::number(self::firstValue($payload, ['kc', 'crop_coefficient'])),
            'water_balance_mm' => self::number(self::firstValue($payload, ['water_balance', 'balance_mm'])),
            'suggested_schedule' => self::firstValue($payload, ['suggested_schedule', 'schedule']) ?? [],
            'confidence' => self::confidence($context),
            'source' => self::source('irrigation', $context),
        ];
    }

    public static function pestDisease(array $payload, array $context = []): array
    {
        $items = self::graphOrArray($payload);
        $risks = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['models']) && is_array($item['models'])) {
                foreach ($item['models'] as $model) {
                    $risks[] = self::riskFromArray($model);
                }
                continue;
            }
            $risks[] = self::riskFromArray($item);
        }

        if ($risks === []) {
            $risks[] = self::riskFromArray($payload);
        }

        return [
            'record_type' => 'pest_disease_risk',
            'farm_id' => $context['farm_id'] ?? null,
            'parcel_id' => $context['parcel_id'] ?? null,
            'crop' => $context['crop'] ?? self::firstValue($payload, ['crop']),
            'growth_stage' => $context['growth_stage'] ?? self::firstValue($payload, ['growth_stage']),
            'risks' => $risks,
            'confidence' => self::confidence($context),
            'source' => self::source('pest_disease', $context),
        ];
    }

    public static function farmCalendar(array $payload, array $context = []): array
    {
        $items = self::graphOrArray($payload);
        $records = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $records[] = [
                'external_id' => $item['@id'] ?? $item['id'] ?? null,
                'operation' => $item['@type'] ?? $item['activityType']['@type'] ?? $item['type'] ?? null,
                'title' => $item['title'] ?? $item['name'] ?? null,
                'observation' => $item['details'] ?? $item['description'] ?? null,
                'parcel' => $item['hasAgriParcel']['@id'] ?? $item['parcel'] ?? null,
                'asset' => $item['usesAgriculturalMachinery'][0]['@id'] ?? $item['asset'] ?? null,
                'timestamp' => $item['hasStartDatetime'] ?? $item['dateCreated'] ?? $item['timestamp'] ?? null,
            ];
        }

        return [
            'record_type' => 'farm_calendar_sync',
            'farm_id' => $context['farm_id'] ?? null,
            'records' => $records,
            'confidence' => self::confidence($context),
            'source' => self::source('farmcalendar', $context),
        ];
    }

    public static function reporting(array $payload, array $context = []): array
    {
        return [
            'record_type' => 'reporting_request',
            'farm_id' => $context['farm_id'] ?? null,
            'report_type' => $context['report_type'] ?? 'advisory',
            'report_id' => $payload['report_id'] ?? $payload['id'] ?? $payload['uuid'] ?? $payload[0] ?? null,
            'status' => $payload['status'] ?? 'requested',
            'download_url' => $payload['download_url'] ?? null,
            'confidence' => self::confidence($context),
            'source' => self::source('reporting', $context),
        ];
    }

    private static function riskFromArray(array $item): array
    {
        $score = self::firstValue($item, ['risk_score', 'riskIndex', 'risk_index', 'score']);
        if ($score === null && isset($item['gdd_values']) && is_array($item['gdd_values'])) {
            $score = end($item['gdd_values']);
        }
        return [
            'threat' => $item['name'] ?? $item['pest'] ?? $item['disease'] ?? $item['eppo_code'] ?? null,
            'risk_index' => self::number($score),
            'risk_class' => $item['risk_class'] ?? $item['classification'] ?? null,
            'confidence_score' => self::number($item['confidence'] ?? null),
            'alert_threshold' => self::number($item['alert_threshold'] ?? $item['threshold'] ?? null),
            'date' => $item['date'] ?? $item['resultTime'] ?? null,
        ];
    }

    private static function graphOrArray(array $payload): array
    {
        if (isset($payload['@graph']) && is_array($payload['@graph'])) {
            return $payload['@graph'];
        }
        if (array_is_list($payload)) {
            return $payload;
        }
        return [$payload];
    }

    private static function firstValue(array $payload, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload)) {
                return $payload[$key];
            }
        }
        foreach ($payload as $value) {
            if (is_array($value)) {
                $found = self::firstValue($value, $keys);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        return null;
    }

    private static function confidence(array $context): array
    {
        return [
            'mode' => !empty($context['stale']) ? 'reduced' : ($context['confidence_mode'] ?? 'normal'),
            'source_age_seconds' => $context['age_seconds'] ?? null,
        ];
    }

    private static function source(string $service, array $context): array
    {
        return [
            'system' => 'openagri_' . $service,
            'cache_class' => !empty($context['from_cache']) ? (!empty($context['stale']) ? 'stale_cache' : 'warm_cache') : 'live',
            'retrieved_at' => gmdate('c'),
        ];
    }

    private static function number(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }
}

