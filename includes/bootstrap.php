<?php
/**
 * Application bootstrap. Required once by index.php (the only web entry point)
 * and by the CLI scripts in database/.
 *
 * Responsibilities, in order:
 *   - load configuration and helpers
 *   - register the class autoloader
 *   - install error/exception handling
 *   - start a hardened, app-scoped session
 *   - expand the encoded ?q= token back into $_GET
 *   - initialise the active language
 */

define('APP_BOOTSTRAPPED', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// --- Class autoloader --------------------------------------------------------
// All classes live flat in includes/ as class.{Name}.php under the single App\
// namespace. Core names like Request, Session, View and Router are the highest
// collision risk in PHP, so the namespace keeps them isolated from any vendored
// code that declares globals.
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return; // not ours — let any other autoloader try
    }
    $name = substr($class, 4);

    // Reject sub-namespaces and every path metacharacter, so the file lookup is
    // provably confined to includes/ and "flat" stays an enforced invariant.
    if ($name === '' || strpbrk($name, "\\/.\0") !== false) {
        return;
    }

    $file = __DIR__ . '/class.' . $name . '.php';
    if (!is_file($file)) {
        return; // let PHP raise its own "class not found"
    }
    require $file;

    // is_file() is case-insensitive on NTFS, so `new App\session()` would
    // silently load class.Session.php here and then fail on a case-sensitive
    // host. Surface it now rather than at deploy time.
    if (APP_ENV === 'dev' && !class_exists($class, false) && !interface_exists($class, false)) {
        throw new LogicException(
            "Autoload case mismatch: {$class} was not declared by " . basename($file)
        );
    }
});

// --- Errors ------------------------------------------------------------------
// php.ini on this box has display_errors = 1. Without this, any uncaught
// exception prints the DB credentials and absolute paths straight to the
// browser.
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', APP_ENV === 'dev' ? '1' : '0');

if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0775, true);
}
ini_set('error_log', LOG_PATH . '/php-error.log');

// Convert warnings/notices into exceptions so they cannot be silently ignored
// the way the legacy code ignored every failed mysqli_query().
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false; // suppressed with @ — respect it
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $e): void {
    error_log(sprintf(
        "%s: %s in %s:%d\n%s",
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    $isHttp  = $e instanceof App\HttpException;
    $status  = $isHttp ? $e->getStatusCode() : 500;
    $viewKey = $isHttp ? $e->getViewKey() : '500';

    if (!headers_sent()) {
        http_response_code($status);
    }

    // In dev, show the real error. In prod, show nothing internal.
    if (APP_ENV === 'dev' && $status === 500) {
        echo '<pre style="padding:1rem;font:13px/1.5 monospace;white-space:pre-wrap">';
        echo htmlspecialchars(
            get_class($e) . ': ' . $e->getMessage() . "\n\n"
            . $e->getFile() . ':' . $e->getLine() . "\n\n"
            . $e->getTraceAsString(),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '</pre>';
        return;
    }

    App\View::renderError($status, $viewKey);
});

// --- Session -----------------------------------------------------------------
// Scoped to this app only. With PHP's defaults (name PHPSESSID, path "/") the
// session is shared with every other project under http://localhost, and since
// Aminship also writes $_SESSION['id'], a login there would authenticate here.
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => (BASE_URL === '' ? '/' : BASE_URL . '/'),
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    // Refuse session ids the server never issued, which is what makes session
    // fixation possible. The legacy app never regenerated an id at all.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

// --- Encoded query parameters ------------------------------------------------
// All GET data travels as one opaque ?q=<base64url> token, expanded here before
// anything reads GET data so the rest of the app just uses get('key').
//
// This is a presentation choice, not a security boundary: the token is decoded
// by anyone in one step, and because it lands in $_GET a user can craft any
// values they like. Every consumer still validates, binds and authorises.
if (isset($_GET['q'])) {
    $token = (string) $_GET['q'];
    unset($_GET['q']);
    foreach (q_decode($token) as $k => $v) {
        // Only scalar keys/values; a crafted token must not inject arrays into
        // code paths that expect strings.
        if (is_string($k) && (is_scalar($v) || $v === null)) {
            $_GET[$k] = $v;
        }
    }
}

// --- Language ----------------------------------------------------------------
App\Lang::init();

// ?setlang=bn on any request switches and persists the choice. Handled here,
// before any output, so the Set-Cookie header is still valid.
if (isset($_GET['setlang'])) {
    App\Lang::set((string) $_GET['setlang']);
}
