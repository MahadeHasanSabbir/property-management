<?php
/**
 * Localization (English / Bangla).
 *
 * The active language comes from the signed-in user's stored preference, then
 * a cookie, then the configured default. Dictionaries live in includes/lang/.
 *
 * This is also what settles the terminology inconsistency in the legacy code,
 * where the same concept was `khatian` in the database, `khotiyan` in the form
 * field, and "Ledger No" on screen. Code and schema now use one canonical term
 * and every visible string is looked up here.
 *
 * Adapted from the sister project (Aminship).
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Lang
{
    private static string $code = 'en';
    private static array $dict = [];
    private static ?array $fallback = null;

    /** Resolve the active language and load its dictionary. */
    public static function init(): void
    {
        $code = $_SESSION['locale'] ?? $_COOKIE[LANG_COOKIE] ?? DEFAULT_LANG;
        self::$code = self::isAvailable((string) $code) ? (string) $code : DEFAULT_LANG;
        self::load();
    }

    /** Persist a new choice in a cookie (and the session) and reload. */
    public static function set(string $code): void
    {
        if (!self::isAvailable($code)) {
            return;
        }

        setcookie(LANG_COOKIE, $code, [
            'expires'  => time() + LANG_COOKIE_TTL,
            'path'     => (BASE_URL === '' ? '/' : BASE_URL . '/'),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[LANG_COOKIE] = $code;
        $_SESSION['locale']   = $code;

        self::$code = $code;
        self::load();
    }

    public static function code(): string
    {
        return self::$code;
    }

    /** Text direction — both supported languages are LTR, but views ask. */
    public static function dir(): string
    {
        return 'ltr';
    }

    public static function available(): array
    {
        return array_map('trim', explode(',', AVAILABLE_LANGS));
    }

    /** Human label for a code, used by the switcher. */
    public static function label(string $code): string
    {
        return ['en' => 'English', 'bn' => 'বাংলা'][$code] ?? $code;
    }

    /**
     * Translate a key. A missing key falls back to English, then to the key
     * itself, so the interface is never blank. Supports {placeholder}
     * substitution.
     */
    public static function t(string $key, array $replace = []): string
    {
        $value = self::$dict[$key] ?? null;

        if ($value === null && self::$code !== 'en') {
            if (self::$fallback === null) {
                $file = __DIR__ . '/lang/en.php';
                self::$fallback = is_file($file) ? (require $file) : [];
            }
            $value = self::$fallback[$key] ?? null;
        }

        $value = $value ?? $key;

        foreach ($replace as $k => $v) {
            $value = str_replace('{' . $k . '}', (string) $v, $value);
        }
        return $value;
    }

    private static function load(): void
    {
        $file = __DIR__ . '/lang/' . self::$code . '.php';
        self::$dict = is_file($file) ? (require $file) : [];
    }

    private static function isAvailable(string $code): bool
    {
        return in_array($code, self::available(), true);
    }
}
