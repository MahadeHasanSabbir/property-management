<?php
/**
 * A small pattern-matching router.
 *
 * Routes are declared in includes/routes.php as
 *   $r->get('/properties/{id}/edit', [PropertyController::class, 'edit'], 'auth');
 *
 * {placeholders} match a single path segment and are passed to the action in
 * declaration order. The third argument is a middleware name (or list) run
 * before the action, so access rules are declared with the route rather than
 * repeated inside each controller.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Router
{
    /** @var array<string, array<int, array{regex:string, keys:array, action:array, middleware:array}>> */
    private array $routes = [];

    public function get(string $pattern, array $action, $middleware = []): void
    {
        $this->add('GET', $pattern, $action, $middleware);
    }

    public function post(string $pattern, array $action, $middleware = []): void
    {
        $this->add('POST', $pattern, $action, $middleware);
    }

    /** Register the same action for both GET and POST (forms that self-submit). */
    public function form(string $pattern, array $action, $middleware = []): void
    {
        $this->add('GET', $pattern, $action, $middleware);
        $this->add('POST', $pattern, $action, $middleware);
    }

    private function add(string $method, string $pattern, array $action, $middleware): void
    {
        $keys = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $m) use (&$keys): string {
                $keys[] = $m[1];
                return '([^/]+)';
            },
            $pattern
        );

        $this->routes[$method][] = [
            'regex'      => '#^' . $regex . '$#',
            'keys'       => $keys,
            'action'     => $action,
            'middleware' => (array) $middleware,
        ];
    }

    /**
     * Match and run. Throws HttpException(404) when no pattern matches, and
     * 405 when the path exists under a different method — which is a real
     * signal during development, not a generic "not found".
     */
    public function dispatch(Request $request): void
    {
        $path   = $request->path();
        $method = $request->method();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['regex'], $path, $m)) {
                continue;
            }
            array_shift($m);

            foreach ($route['middleware'] as $name) {
                Middleware::run($name, $request);
            }

            [$class, $action] = $route['action'];
            $controller = new $class();
            $controller->$action(...$m);
            return;
        }

        foreach ($this->routes as $otherMethod => $routes) {
            if ($otherMethod === $method) {
                continue;
            }
            foreach ($routes as $route) {
                if (preg_match($route['regex'], $path)) {
                    throw new HttpException(405);
                }
            }
        }

        throw new HttpException(404);
    }
}
