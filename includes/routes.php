<?php
/**
 * The complete route table. Receives $router from index.php.
 *
 * Conventions:
 *   - Record ids are plain path segments; filters travel in the encoded ?q=
 *     token built by url().
 *   - Every state-changing route is POST. The legacy app deleted accounts,
 *     dropped tables, wiped analytics and reset passwords over GET, guarded
 *     only by a JavaScript confirm().
 *   - Access rules are the last argument, never repeated inside controllers.
 */

defined('APP_BOOTSTRAPPED') || exit;

/** @var App\Router $router */

use App\AccountController;
use App\AdminController;
use App\AdminUserController;
use App\HomeController;
use App\PropertyController;

// --- Public ------------------------------------------------------------------
$router->get('/',           [HomeController::class, 'index']);
$router->get('/about',      [HomeController::class, 'about']);
$router->get('/calculator', [HomeController::class, 'calculator']);
$router->post('/contact',   [HomeController::class, 'storeMessage']);

// --- Authentication ----------------------------------------------------------
$router->get('/login',   [AccountController::class, 'showLogin'],    'guest');
$router->post('/login',  [AccountController::class, 'login'],        'guest');
$router->get('/register',  [AccountController::class, 'showRegister'], 'guest');
$router->post('/register', [AccountController::class, 'register'],     'guest');
$router->post('/logout',   [AccountController::class, 'logout']);

$router->get('/forgot-password',  [AccountController::class, 'showForgot'], 'guest');
$router->post('/forgot-password', [AccountController::class, 'sendReset'],  'guest');
$router->get('/reset-password/{token}',  [AccountController::class, 'showReset'],  'guest');
$router->post('/reset-password/{token}', [AccountController::class, 'resetPassword'], 'guest');

// Staff and admins sign in separately, so admin access is not discoverable
// from the public login form.
$router->get('/admin/login',  [AdminController::class, 'showLogin'], 'guest');
$router->post('/admin/login', [AdminController::class, 'login'],     'guest');

// --- Customer area -----------------------------------------------------------
$router->get('/dashboard', [AccountController::class, 'dashboard'], 'customer');

$router->get('/profile',           [AccountController::class, 'profile'],        'auth');
$router->get('/profile/edit',      [AccountController::class, 'editProfile'],    'auth');
$router->post('/profile',          [AccountController::class, 'updateProfile'],  'auth');
$router->get('/profile/password',  [AccountController::class, 'editPassword'],   'auth');
$router->post('/profile/password', [AccountController::class, 'updatePassword'], 'auth');
$router->post('/profile/delete',   [AccountController::class, 'deleteAccount'],  'customer');

$router->get('/plan', [AccountController::class, 'plan'], 'customer');

// --- Properties --------------------------------------------------------------
// Static segments are declared before {id} so /properties/create and
// /properties/search are never captured as an id.
// Filterable listings accept POST as well: the filter form posts, and the
// controller redirects to the encoded GET equivalent so the visible URL is one
// ?q= token rather than a row of readable key=value pairs.
$router->get('/properties',         [PropertyController::class, 'index'],  'customer');
$router->post('/properties/filter', [PropertyController::class, 'index'],  'customer');
$router->form('/properties/search', [PropertyController::class, 'search'], 'customer');
$router->get('/properties/create',  [PropertyController::class, 'create'], 'customer');
$router->get('/properties/export',  [PropertyController::class, 'export'], 'customer');
$router->post('/properties',        [PropertyController::class, 'store'],  'customer');

$router->get('/properties/{id}',         [PropertyController::class, 'show'],    'customer');
$router->get('/properties/{id}/edit',    [PropertyController::class, 'edit'],    'customer');
$router->post('/properties/{id}',        [PropertyController::class, 'update'],  'customer');
$router->post('/properties/{id}/delete', [PropertyController::class, 'destroy'], 'customer');

// --- Documents ---------------------------------------------------------------
$router->post('/properties/{id}/documents', [PropertyController::class, 'uploadDocument'], 'customer');
$router->get('/documents/{id}',             [PropertyController::class, 'downloadDocument'], 'auth');
$router->post('/documents/{id}/delete',     [PropertyController::class, 'deleteDocument'],   'customer');

// --- Admin area --------------------------------------------------------------
$router->get('/admin',            [AdminController::class, 'dashboard'], 'staff');
$router->form('/admin/messages',  [AdminController::class, 'messages'],  'staff');
$router->post('/admin/messages/{id}/read',   [AdminController::class, 'markMessageRead'], 'staff');
$router->post('/admin/messages/{id}/delete', [AdminController::class, 'deleteMessage'],   'admin');
$router->form('/admin/audit-log', [AdminController::class, 'auditLog'], 'admin');
$router->get('/admin/settings',  [AdminController::class, 'settings'], 'admin');
$router->post('/admin/settings', [AdminController::class, 'updateSettings'], 'admin');

// GET lists users; POST is the filter form, which redirects to the encoded GET.
// Creating a user therefore posts to /admin/users/create rather than colliding
// with the filter on POST /admin/users.
$router->form('/admin/users',            [AdminUserController::class, 'index'],  'staff');
$router->get('/admin/users/create',      [AdminUserController::class, 'create'], 'admin');
$router->post('/admin/users/create',     [AdminUserController::class, 'store'],  'admin');
$router->get('/admin/users/{id}',       [AdminUserController::class, 'show'],   'staff');
$router->get('/admin/users/{id}/edit',  [AdminUserController::class, 'edit'],   'admin');
$router->post('/admin/users/{id}',      [AdminUserController::class, 'update'], 'admin');
$router->post('/admin/users/{id}/password', [AdminUserController::class, 'resetPassword'], 'admin');
$router->post('/admin/users/{id}/delete',   [AdminUserController::class, 'destroy'],       'admin');
$router->get('/admin/users/{id}/properties', [AdminUserController::class, 'properties'],   'staff');

$router->get('/admin/plans',       [AdminUserController::class, 'plans'],      'admin');
$router->post('/admin/plans/{code}', [AdminUserController::class, 'updatePlan'], 'admin');
