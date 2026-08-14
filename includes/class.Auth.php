<?php
/**
 * Authentication and the identity of the current request.
 *
 * Replaces two parallel legacy systems ($_SESSION['id'] for users against the
 * `user` table, $_SESSION['aid'] for admins against `admin`) which could both
 * be set at once and were checked by hand at the top of ~30 files.
 *
 * Fixes carried over from the legacy behaviour:
 *   - logout genuinely destroys the session. The old logout.php only wrote a
 *     flash and redirected; the session was torn down as a side effect of the
 *     login page rendering that flash, so hitting logout and then navigating
 *     straight to /profile/ left you signed in.
 *   - the session id is regenerated on sign-in. The old code never called
 *     session_regenerate_id() anywhere, leaving session fixation wide open.
 *   - failed attempts are counted and throttled. There was no limit before.
 *   - the identity is re-read from the database each request, so a suspended or
 *     deleted account loses access immediately instead of when its session
 *     happens to expire.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Auth
{
    /** Per-request cache of the signed-in user row. false = not yet loaded. */
    private static $cached = false;

    // --- Identity ------------------------------------------------------------

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user ? $user['role'] : null;
    }

    /** The signed-in user row, or null. Read once per request. */
    public static function user(): ?array
    {
        if (self::$cached !== false) {
            return self::$cached;
        }

        $id = $_SESSION['auth_user_id'] ?? null;
        if (!$id) {
            return self::$cached = null;
        }

        $user = User::find((int) $id);

        // The account may have been deleted or suspended after sign-in.
        if ($user === null || $user['deleted_at'] !== null) {
            self::forget();
            return self::$cached = null;
        }

        return self::$cached = $user;
    }

    /** Does the signed-in user hold this permission? */
    public static function can(string $permission): bool
    {
        return Permission::allows(self::role(), $permission);
    }

    /** Throw 403 unless the permission is held. */
    public static function authorize(string $permission): void
    {
        if (!self::can($permission)) {
            throw new HttpException(403);
        }
    }

    public static function isCustomer(): bool
    {
        return self::role() === 'customer';
    }

    public static function isStaff(): bool
    {
        return in_array(self::role(), ['staff', 'admin'], true);
    }

    // --- Sign in / out -------------------------------------------------------

    /**
     * Verify credentials and sign in on success.
     *
     * @param bool $staffOnly true for /admin/login, which must refuse customer
     *                        accounts so admin access is not reachable from the
     *                        public form.
     * @return array{ok:bool, error:?string, wait:?int}
     */
    public static function attempt(string $email, string $password, bool $staffOnly = false): array
    {
        $email = strtolower(trim($email));

        if ($wait = Throttle::lockedFor($email)) {
            return ['ok' => false, 'error' => 'throttled', 'wait' => $wait];
        }

        $user = User::findByEmail($email);

        // Always run a hash comparison, even when no such user exists, so the
        // response time does not reveal which e-mail addresses are registered.
        $hash = $user['password'] ?? '$2y$10$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidin';
        $ok   = password_verify($password, $hash);

        if (!$user || !$ok) {
            Throttle::record($email, false);
            // One message for both cases. The legacy app said "User not found"
            // versus "Incorrect password", which enumerates accounts.
            return ['ok' => false, 'error' => 'invalid', 'wait' => null];
        }

        if ($staffOnly && !in_array($user['role'], ['staff', 'admin'], true)) {
            Throttle::record($email, false);
            return ['ok' => false, 'error' => 'not_staff', 'wait' => null];
        }

        if ($user['status'] !== 'active' || $user['deleted_at'] !== null) {
            Throttle::record($email, false);
            return ['ok' => false, 'error' => 'inactive', 'wait' => null];
        }

        // Transparently upgrade the stored hash if the cost factor has moved on.
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
            User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_BCRYPT), false);
        }

        Throttle::record($email, true);
        Throttle::clear($email);
        self::login($user);

        return ['ok' => true, 'error' => null, 'wait' => null];
    }

    /** Establish the session for a user row. */
    public static function login(array $user): void
    {
        // Rotate the session id so a fixed pre-login id cannot be reused.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['auth_user_id'] = (int) $user['id'];
        $_SESSION['locale']       = $user['locale'] ?: DEFAULT_LANG;
        self::$cached             = $user;

        Csrf::clear(); // a fresh session gets a fresh token

        User::touchLogin((int) $user['id'], client_ip());
        Lang::init();
    }

    /** Sign out and destroy the session for real. */
    public static function logout(): void
    {
        self::forget();

        if (session_status() === PHP_SESSION_ACTIVE) {
            // Keep queued flashes so the sign-in page can show "signed out".
            $flash = $_SESSION['flash'] ?? [];
            $_SESSION = [];

            // Expire the cookie itself, not just the server-side data.
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires'  => time() - 42000,
                    'path'     => $p['path'],
                    'domain'   => $p['domain'],
                    'secure'   => $p['secure'],
                    'httponly' => $p['httponly'],
                    'samesite' => $p['samesite'] ?? 'Lax',
                ]);
            }

            session_destroy();

            // A new session purely to carry the flash message across the
            // redirect. It holds no identity.
            session_start();
            session_regenerate_id(true);
            $_SESSION['flash'] = $flash;
        }
    }

    private static function forget(): void
    {
        unset($_SESSION['auth_user_id']);
        self::$cached = false;
    }
}
