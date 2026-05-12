<?php
namespace AgriTwin\OpenAgri\Support;

final class Env
{
    public static function get(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }

    public static function int(string $name, int $default): int
    {
        $value = self::get($name);
        if ($value === null || !is_numeric($value)) {
            return $default;
        }
        return (int) $value;
    }

    public static function bool(string $name, bool $default = false): bool
    {
        $value = self::get($name);
        if ($value === null) {
            return $default;
        }
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }
}

