<?php
/**
 * Template rendering. Views live in views/ and contain markup only — no SQL,
 * no business logic, and no literal English (every string goes through t()).
 *
 * A view is rendered into a layout via output buffering, so there is exactly
 * one <!DOCTYPE>, one <head> and one navbar in the project. The legacy app
 * hand-wrote all three in every one of ~20 files and kept four divergent copies
 * of header.php.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class View
{
    /** Data shared with every view (active route name, page title, ...). */
    private static array $shared = [];

    public static function share(string $key, $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Render a view inside a layout and send it.
     *
     * @param string $view   dotted path under views/, e.g. 'properties.index'
     * @param array  $data   variables extracted into the view's scope
     * @param string $layout layout name under views/layouts/
     */
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        echo self::capture($view, $data, $layout);
    }

    /** Render to a string rather than sending it (used by the error handler). */
    public static function capture(string $view, array $data = [], string $layout = 'app'): string
    {
        $content = self::renderPartial($view, $data);

        if ($layout === '') {
            return $content;
        }

        return self::renderPartial('layouts.' . $layout, array_merge($data, [
            'content' => $content,
        ]));
    }

    /** Render a single template with no layout — also used for partials. */
    public static function renderPartial(string $view, array $data = []): string
    {
        $file = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("View not found: {$view} ({$file})");
        }

        extract(array_merge(self::$shared, $data), EXTR_SKIP);

        ob_start();
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }

    /**
     * Render an error page. Called by the global exception handler, so it must
     * not itself throw — if the error view is missing or the session is in a
     * bad state, fall back to plain text.
     */
    public static function renderError(int $status, string $viewKey = ''): void
    {
        $key = $viewKey !== '' ? $viewKey : (string) $status;

        try {
            echo self::capture('errors.error', [
                'status'  => $status,
                'title'   => t('error.' . $key . '.title'),
                'message' => t('error.' . $key . '.message'),
            ], 'app');
        } catch (\Throwable $e) {
            error_log('Error view failed: ' . $e->getMessage());
            header('Content-Type: text/plain; charset=UTF-8');
            echo "Error {$status}";
        }
    }
}
