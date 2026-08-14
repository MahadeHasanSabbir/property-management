<?php
/**
 * Small stateless utility functions used across views and controllers.
 * Business logic lives in the classes; these are pure presentation, request and
 * validation helpers. Kept as global functions so views need no `use` lines.
 */

defined('APP_BOOTSTRAPPED') || exit;

// --- Output ------------------------------------------------------------------

/**
 * Escape a value for safe HTML output. Deliberately the shortest thing to type,
 * so there is no incentive to skip it.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Translate a key using the active language (shortcut for Lang::t). */
function t(string $key, array $replace = []): string
{
    return App\Lang::t($key, $replace);
}

/** Translate and escape in one step — the common case in views. */
function te(string $key, array $replace = []): string
{
    return e(App\Lang::t($key, $replace));
}

// --- Encoded query parameters ------------------------------------------------

/**
 * Encode query parameters into a single URL-safe base64 token.
 *
 * base64url (- and _ instead of + and /, padding stripped) rather than plain
 * base64: a literal "+" in a query string decodes to a space, "/" is awkward in
 * a URL, and a trailing "=" invites truncation by intermediaries.
 *
 * This is cosmetic. It hides nothing from anyone who can run base64_decode.
 */
function q_encode(array $params): string
{
    if (!$params) {
        return '';
    }
    return rtrim(strtr(base64_encode(http_build_query($params)), '+/', '-_'), '=');
}

/**
 * Decode a URL-safe base64 token back into a parameter array.
 * A malformed or truncated token yields an empty array — callers treat that as
 * "no filters", never as an error.
 */
function q_decode(string $token): array
{
    $b64 = strtr($token, '-_', '+/');
    if ($pad = strlen($b64) % 4) {
        $b64 .= str_repeat('=', 4 - $pad);
    }
    $decoded = base64_decode($b64, true);
    if ($decoded === false) {
        return [];
    }
    $out = [];
    parse_str($decoded, $out);
    return $out;
}

// --- URLs --------------------------------------------------------------------

/**
 * Build an absolute URL from a project-relative path.
 *
 * Record ids stay as plain path segments — url('properties/12/edit') — because
 * those are the router's own structure. Any query string is folded into one
 * encoded ?q= token:
 *   url('properties/search?dag=12&page=2')
 *     -> "/property-management/properties/search?q=ZGFnPTEyJnBhZ2U9Mg"
 *
 * Root-relative hrefs written by hand ("/properties") would resolve against the
 * web root, not the project, so every link in the app goes through here.
 */
function url(string $path = '', array $params = []): string
{
    if (($pos = strpos($path, '?')) !== false) {
        $inline = [];
        parse_str(substr($path, $pos + 1), $inline);
        $params = array_merge($inline, $params);
        $path   = substr($path, 0, $pos);
    }

    $query = '';
    if ($params) {
        $token = q_encode($params);
        if ($token !== '') {
            $query = '?q=' . $token;
        }
    }

    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/') . $query;
}

/** Build an absolute URL to a file inside resources/. */
function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/resources/' . ltrim($path, '/');
}

/** Redirect to a project-relative path and stop execution. */
function redirect(string $path, array $params = []): void
{
    header('Location: ' . url($path, $params));
    exit;
}

/** Abort the request with an HTTP status handled by the exception handler. */
function abort(int $status, string $message = ''): void
{
    throw new App\HttpException($status, $message);
}

// --- Request input -----------------------------------------------------------

/** Read a trimmed POST field. */
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) && is_scalar($_POST[$key])
        ? trim((string) $_POST[$key])
        : $default;
}

/** Read a trimmed GET field (already expanded from the ?q= token). */
function get(string $key, string $default = ''): string
{
    return isset($_GET[$key]) && is_scalar($_GET[$key])
        ? trim((string) $_GET[$key])
        : $default;
}

/**
 * Keep only the expected keys from an input array, dropping everything else.
 * Applied to decoded ?q= data so a crafted token cannot smuggle extra keys into
 * code that later reads a superglobal directly.
 */
function only(array $input, array $keys): array
{
    return array_intersect_key($input, array_flip($keys));
}

/** Clamp a value to an integer within bounds — used for page and page size. */
function clamp_int($value, int $min, int $max, int $default): int
{
    if (!is_scalar($value) || !is_numeric((string) $value)) {
        return $default;
    }
    return max($min, min($max, (int) $value));
}

/** Best-effort client IP, validated. Returns null when not a valid IP. */
function client_ip(): ?string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
}

// --- Flash messages ----------------------------------------------------------

/** Queue a one-shot notice (success|danger|info|warning). */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Pull and clear all queued flash messages. */
function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Remember submitted input so a failed form can be re-rendered filled in. */
function flash_old(array $input): void
{
    $_SESSION['old'] = $input;
}

/** Read a previously submitted field value (and clear the store on first read). */
function old(string $key, string $default = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
    }
    return isset($old[$key]) && is_scalar($old[$key]) ? (string) $old[$key] : $default;
}

// --- Validation --------------------------------------------------------------
// Server-side rules. Client-side validation is a convenience; these are the
// ones that actually hold.

/**
 * A person's name. Unicode-aware on purpose: an ASCII-only rule would reject
 * every Bengali name, which is most of them here.
 */
function valid_name(string $v): bool
{
    return (bool) preg_match('/^[\p{L}\p{M} .\'-]{3,60}$/u', $v);
}

/** Bangladeshi mobile number in canonical +880 form. */
function valid_phone(string $v): bool
{
    return (bool) preg_match('/^\+8801[3-9][0-9]{8}$/', $v);
}

/**
 * Normalise a Bangladeshi number to +880 form.
 * Accepts 01XXXXXXXXX, 8801XXXXXXXXX and +8801XXXXXXXXX. Returns null when the
 * input cannot be interpreted as a Bangladeshi mobile number.
 */
function normalize_phone(string $v): ?string
{
    $digits = preg_replace('/[^0-9]/', '', $v);
    if ($digits === '') {
        return null;
    }
    if (strlen($digits) === 11 && $digits[0] === '0') {
        $digits = '88' . $digits;
    }
    if (strlen($digits) === 13 && strncmp($digits, '880', 3) === 0) {
        $candidate = '+' . $digits;
        return valid_phone($candidate) ? $candidate : null;
    }
    return null;
}

function valid_email(string $v): bool
{
    return strlen($v) <= 190 && (bool) filter_var($v, FILTER_VALIDATE_EMAIL);
}

/**
 * Password policy. The ceiling is bcrypt's 72-byte input limit.
 */
function valid_password(string $v): bool
{
    $len = strlen($v);
    return $len >= 8 && $len <= 72;
}

/** A dag / khatian / deed token: digits, optionally with a letter suffix. */
function valid_identifier_token(string $v): bool
{
    return (bool) preg_match('/^[0-9]{1,18}[A-Za-z]?$/', $v);
}

/**
 * Split a comma-separated dag/khatian list into clean tokens. Values arrive as
 * '1232 , 25 ,12' as often as '1232,25,12', so spaces and empty segments are
 * both tolerated.
 */
function split_tokens(string $raw): array
{
    $parts = array_map('trim', explode(',', $raw));
    $parts = array_filter($parts, static fn($p) => $p !== '');
    return array_values(array_unique($parts));
}

/** Format a nullable date for display, or a dash when unknown. */
function fmt_date(?string $date): string
{
    if ($date === null || $date === '' || $date === '0000-00-00') {
        return '—';
    }
    $ts = strtotime($date);
    return $ts === false ? '—' : date('d M Y', $ts);
}

/** Format a nullable decimal area, trimming pointless trailing zeros. */
function fmt_area(?string $area): string
{
    if ($area === null || $area === '') {
        return '—';
    }
    return rtrim(rtrim(number_format((float) $area, 3, '.', ','), '0'), '.');
}
