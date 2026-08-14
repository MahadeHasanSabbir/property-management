<?php
/**
 * CSRF protection.
 *
 * The legacy app had none, and combined that with destructive actions on GET.
 * The worst case was profile/delete.php, which deleted the signed-in account
 * and dropped its data table on a bare GET with no parameters — meaning an
 * <img src="..."> on any third-party page destroyed an account. Every
 * state-changing route here is POST and every POST is verified.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Csrf
{
    /** Current token for this session, created on first use. */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Hidden input for forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(self::token()) . '">';
    }

    /** Verify the submitted token, or abort with 419. */
    public static function verify(): void
    {
        $submitted = $_POST[CSRF_TOKEN_NAME] ?? '';

        // 403 on the wire (Apache mangles invented codes into a 500), but the
        // page reads as "session expired", which is what actually happened.
        if (!is_string($submitted) || $submitted === '' || empty($_SESSION['csrf_token'])) {
            throw new HttpException(403, 'CSRF token missing', '419');
        }
        if (!hash_equals($_SESSION['csrf_token'], $submitted)) {
            throw new HttpException(403, 'CSRF token mismatch', '419');
        }
    }

    /** Drop the token, e.g. on logout so a new session gets a fresh one. */
    public static function clear(): void
    {
        unset($_SESSION['csrf_token']);
    }
}
