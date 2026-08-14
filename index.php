<?php
/**
 * Front controller — the only web-reachable PHP file in the project.
 *
 * Apache rewrites every request that is not a real file to this script (see
 * .htaccess), so URLs never carry a .php extension and every request passes
 * through the same bootstrap, CSRF check and middleware stack. In the legacy
 * layout all 39 PHP files were individually addressable and each had to repeat
 * its own session guard.
 */

require_once __DIR__ . '/includes/bootstrap.php';

use App\Csrf;
use App\Request;
use App\Router;

$request = new Request();

// Verify CSRF on every state-changing request, centrally, so no individual
// controller can forget to. Every mutating route in this app is POST.
if ($request->isPost()) {
    Csrf::verify();
}

$router = new Router();
require __DIR__ . '/includes/routes.php';

$router->dispatch($request);
