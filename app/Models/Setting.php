<?php
/**
 * Akilli Kariyer - Ayarlar modeli (site_settings tablosu)
 */

namespace App\Models;

use App\Core\Database;

class Setting
{
    /** @var array<string,mixed>|null */
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll('SELECT * FROM `site_settings` ORDER BY `setting_group`, `id`');
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row;
            }
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        if (!isset($all[$key])) {
            return $default;
        }
        $row = $all[$key];
        $value = $row['setting_value'];

        return match ($row['setting_type']) {
            'number'  => is_numeric($value) ? (float) $value : $default,
            'boolean' => (int) $value === 1,
            default   => $value,
        };
    }

    public static function text(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);
        return is_scalar($value) ? (string) $value : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, null);
        return $value === null ? $default : (bool) $value;
    }

    public static function group(string $group): array
    {
        return array_filter(self::all(), fn($row) => $row['setting_group'] === $group);
    }

    public static function set(string $key, string $value): void
    {
        Database::query(
            'INSERT INTO `site_settings` (`setting_key`,`setting_value`) VALUES (?,?)
             ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)',
            [$key, $value]
        );
        self::$cache = null;
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set($key, (string) $value);
        }
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
