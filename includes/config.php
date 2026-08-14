<?php
/**
 * Central application configuration — the single source of truth for database
 * credentials and app-wide settings.
 *
 * The legacy code inlined mysqli_connect("localhost","root","","property") in
 * 29 separate files; changing the host or password meant 29 edits. Everything
 * now reads from here, and includes/class.Database.php is the only file that
 * consumes the credentials.
 *
 * To use different credentials without touching version control, create
 * includes/config.local.php and call cfg() there — it is gitignored and loaded
 * before the defaults, so anything it sets wins.
 */

defined('APP_BOOTSTRAPPED') || exit;

/**
 * Define a constant only if it is not already set, so config.local.php can
 * override any default without triggering a redefinition notice.
 */
function cfg(string $name, $value): void
{
    if (!defined($name)) {
        define($name, $value);
    }
}

// --- Local overrides ---------------------------------------------------------
// Loaded first so its values take precedence. Not committed (see .gitignore).
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// --- Environment -------------------------------------------------------------
// 'dev' shows errors on screen; 'prod' logs them and shows a neutral page.
cfg('APP_ENV', 'dev');

// --- Database (PDO / MySQL) --------------------------------------------------
cfg('DB_HOST', '127.0.0.1');
cfg('DB_NAME', 'property_v2');
cfg('DB_USER', 'root');
cfg('DB_PASS', '');
cfg('DB_CHARSET', 'utf8mb4');

// Legacy database, read by database/migrate_legacy.php only. Never opened by
// the application itself.
cfg('LEGACY_DB_NAME', 'property');

// --- Application -------------------------------------------------------------
cfg('APP_NAME', 'Property Management');

/**
 * Base URL path of the project relative to the web root, e.g.
 * "/property-management", or "" when served at the web root.
 *
 * Derived from SCRIPT_NAME rather than DOCUMENT_ROOT: with a single front
 * controller, SCRIPT_NAME is always "<base>/index.php" even after the internal
 * rewrite, so dirname() yields the base exactly. That avoids string-matching
 * DOCUMENT_ROOT (which breaks under Alias and on drive-letter case
 * differences) and keeps working if this folder is renamed or moved to the web
 * root. On CLI there is no SCRIPT_NAME worth using, so it falls back to "".
 */
if (!defined('BASE_URL')) {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base   = $script === '' ? '' : rtrim(dirname($script), '/');
    define('BASE_URL', ($base === '/' || $base === '.') ? '' : $base);
}

// Filesystem paths.
cfg('BASE_PATH', dirname(__DIR__));
cfg('STORAGE_PATH', BASE_PATH . '/storage');
cfg('UPLOAD_PATH', STORAGE_PATH . '/uploads');
cfg('LOG_PATH', STORAGE_PATH . '/logs');

// --- Session -----------------------------------------------------------------
// A distinct session name is not cosmetic. XAMPP serves several projects from
// the same http://localhost origin, and PHP's default PHPSESSID with
// cookie_path "/" is shared across all of them. Aminship also stores
// $_SESSION['id'], so with the defaults a login there would be honoured here.
cfg('SESSION_NAME', 'PMSESSID');

// --- Security ----------------------------------------------------------------
cfg('CSRF_TOKEN_NAME', '_token');

// Login throttling. Counted per identifier first: on XAMPP every request comes
// from 127.0.0.1, so per-IP limits alone are meaningless locally.
cfg('THROTTLE_MAX_ATTEMPTS', 5);
cfg('THROTTLE_DECAY_MINUTES', 15);

// Password reset tokens.
cfg('RESET_TOKEN_TTL_MINUTES', 60);

// --- Uploads -----------------------------------------------------------------
cfg('UPLOAD_MAX_BYTES', 5 * 1024 * 1024); // 5 MB per document
cfg('UPLOAD_ALLOWED_MIME', 'application/pdf,image/jpeg,image/png,image/webp');

// --- Pagination --------------------------------------------------------------
// Whitelisted page sizes. The legacy code passed $_GET['itemsPerPage'] straight
// into "LIMIT $offset, $items_per_page" with no cast, which was injectable.
cfg('PAGE_SIZES', '10,15,20,50');
cfg('PAGE_SIZE_DEFAULT', 15);

// --- Localization ------------------------------------------------------------
cfg('DEFAULT_LANG', 'en');
cfg('AVAILABLE_LANGS', 'en,bn');
cfg('LANG_COOKIE', 'pm_lang');
cfg('LANG_COOKIE_TTL', 60 * 60 * 24 * 365);

// --- Domain ------------------------------------------------------------------
// One square foot -> cent divisor, preserved from the original calculator.
cfg('CENT_DIVISOR', 435.6);
