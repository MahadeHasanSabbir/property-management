<?php
/**
 * Route middleware — the single place access rules are enforced.
 *
 * The legacy app repeated `if (!isset($_SESSION['id'])) header("location:...")`
 * at the top of roughly thirty files, with two parallel session keys
 * ($_SESSION['id'] for users, $_SESSION['aid'] for admins) that could both be
 * set at once. Here there is one identity and one set of named guards.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Middleware
{
    public static function run(string $name, Request $request): void
    {
        switch ($name) {
            case 'auth':
                self::auth();
                return;

            case 'guest':
                self::guest();
                return;

            case 'customer':
                self::auth();
                if (Auth::role() !== 'customer') {
                    // Staff and admins have no property records of their own;
                    // send them somewhere useful rather than showing a 403.
                    redirect('admin');
                }
                return;

            case 'staff':
                self::auth();
                if (!in_array(Auth::role(), ['staff', 'admin'], true)) {
                    throw new HttpException(403);
                }
                return;

            case 'admin':
                self::auth();
                if (Auth::role() !== 'admin') {
                    throw new HttpException(403);
                }
                return;

            default:
                throw new \LogicException("Unknown middleware: {$name}");
        }
    }

    private static function auth(): void
    {
        if (!Auth::check()) {
            // Remember where they were headed so login can return them there.
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                $_SESSION['intended'] = (new Request())->path();
            }
            flash('warning', t('auth.please_sign_in'));
            redirect('login');
        }

        // A user disabled or deleted mid-session must lose access immediately;
        // the legacy code trusted the session key forever and never re-checked.
        if (Auth::user()['status'] !== 'active') {
            Auth::logout();
            flash('danger', t('auth.account_inactive'));
            redirect('login');
        }

        // A forced password change blocks everything except the change form
        // itself and signing out.
        if (!empty(Auth::user()['must_change_password'])) {
            $path = (new Request())->path();
            $allowed = ['/profile/password', '/logout'];
            if (!in_array($path, $allowed, true)) {
                flash('warning', t('auth.must_change_password'));
                redirect('profile/password');
            }
        }
    }

    private static function guest(): void
    {
        if (Auth::check()) {
            redirect(Auth::role() === 'customer' ? 'dashboard' : 'admin');
        }
    }
}
