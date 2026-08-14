<?php
/**
 * The incoming HTTP request, reduced to what the router needs: a normalised
 * path with the project's base directory stripped off, and the method.
 *
 * All the subtlety here is a consequence of running from a subdirectory
 * (http://localhost/property-management/) behind a front controller.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Request
{
    private string $path;
    private string $method;

    public function __construct()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // 1. Split the query string BEFORE decoding. Decoding first would turn
        //    a literal "%3F" inside a path segment into "?" and truncate the
        //    path at the wrong place.
        if (($q = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $q);
        }

        // 2. Strip the project's base path. Compared case-insensitively because
        //    NTFS is: http://localhost/Property-Management/ reaches this exact
        //    app, and a case-sensitive compare would fail to strip the base and
        //    404 every single route with no obvious cause.
        $base = rtrim(BASE_URL, '/');
        if ($base !== '' && strncasecmp($uri, $base, strlen($base)) === 0) {
            $uri = substr($uri, strlen($base));
        }

        // 3. Decode, then refuse traversal and NUL bytes.
        $uri = rawurldecode($uri);
        if (strpos($uri, "\0") !== false || preg_match('#(^|/)\.\.(/|$)#', $uri)) {
            throw new HttpException(400, 'Malformed request path');
        }

        // 4. Canonical form: exactly one leading slash, never a trailing one.
        //    ""->"/", "/x/"->"/x", "//x"->"/x". Done in PHP rather than with a
        //    301 redirect, because a redirect rule fights mod_dir's
        //    DirectorySlash and produces a genuine redirect loop on any real
        //    directory.
        $uri = preg_replace('#/+#', '/', $uri);
        $this->path = '/' . trim($uri, '/');

        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        return $this->path;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /** Path segments with empties removed: "/properties/12/edit" -> [properties, 12, edit]. */
    public function segments(): array
    {
        return array_values(array_filter(explode('/', $this->path), static fn($s) => $s !== ''));
    }
}
