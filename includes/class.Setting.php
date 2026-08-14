<?php
/**
 * Site settings. Key/value is the right shape here precisely because the set is
 * open-ended — unlike plan entitlements, which are typed columns on `plans`.
 *
 * Values an administrator can change without a deployment.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Setting
{
    private static ?array $cache = null;

    /** All settings, loaded once per request. */
    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Database::all('SELECT key_name, value FROM settings') as $row) {
                self::$cache[$row['key_name']] = $row['value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        return $value === null ? $default : ($value === '1' || $value === 'true');
    }

    public static function set(string $key, ?string $value): void
    {
        Database::run(
            'INSERT INTO settings (key_name, value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [$key, $value]
        );
        self::$cache = null;
    }

    /** Bulk update from an admin form, restricted to known keys. */
    public static function setMany(array $values, array $allowed): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, $allowed, true)) {
                self::set($key, is_scalar($value) ? (string) $value : null);
            }
        }
    }
}
