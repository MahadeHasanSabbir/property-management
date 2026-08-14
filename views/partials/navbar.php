<?php
/**
 * The one and only navigation bar. Links shown depend on the signed-in role.
 *
 * Legacy note: the four header.php copies each highlighted the active tab with
 * basename($_SERVER['PHP_SELF']), which under a front controller is always
 * "index.php" — every item would light up at once. The active item is passed in
 * explicitly instead.
 *
 * @var string $active
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Auth;

$user = Auth::user();
$role = Auth::role();

/** Render one nav item, marking it current when it matches $active. */
$item = static function (string $key, string $path, string $label, string $icon) use ($active): string {
    $isActive = ($active === $key);
    return sprintf(
        '<li class="nav-item"><a class="nav-link%s" href="%s"%s><i class="bi bi-%s me-1"></i>%s</a></li>',
        $isActive ? ' active' : '',
        e(url($path)),
        $isActive ? ' aria-current="page"' : '',
        e($icon),
        e($label)
    );
};
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('')) ?>">
            <i class="bi bi-house-door-fill"></i>
            <span><?= te('app.name') ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#pm-nav" aria-controls="pm-nav"
                aria-expanded="false" aria-label="<?= te('nav.toggle') ?>">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="pm-nav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($role === 'customer'): ?>
                    <?= $item('dashboard',  'dashboard',         t('nav.dashboard'),  'speedometer2') ?>
                    <?= $item('properties', 'properties',        t('nav.properties'), 'folder2-open') ?>
                    <?= $item('search',     'properties/search', t('nav.search'),     'search') ?>
                    <?= $item('calculator', 'calculator',        t('nav.calculator'), 'calculator') ?>
                <?php elseif ($role === 'staff' || $role === 'admin'): ?>
                    <?= $item('admin',    'admin',          t('nav.admin'),    'speedometer2') ?>
                    <?= $item('users',    'admin/users',    t('nav.users'),    'people') ?>
                    <?= $item('messages', 'admin/messages', t('nav.messages'), 'envelope') ?>
                    <?php if ($role === 'admin'): ?>
                        <?= $item('plans',    'admin/plans',     t('nav.plans'),     'tags') ?>
                        <?= $item('audit',    'admin/audit-log', t('nav.audit_log'), 'clock-history') ?>
                        <?= $item('settings', 'admin/settings',  t('nav.settings'),  'gear') ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?= $item('home',       '',           t('nav.home'),       'house') ?>
                    <?= $item('about',      'about',      t('nav.about'),      'info-circle') ?>
                    <?= $item('calculator', 'calculator', t('nav.calculator'), 'calculator') ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= e($user['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= e(url('profile')) ?>">
                                    <i class="bi bi-person me-2"></i><?= te('nav.profile') ?>
                                </a>
                            </li>
                            <?php if ($role === 'customer'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= e(url('plan')) ?>">
                                        <i class="bi bi-star me-2"></i><?= te('nav.plan') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <?php /* Sign-out is POST + CSRF: a GET logout can be triggered
                                          by any third-party <img> tag. */ ?>
                                <form method="post" action="<?= e(url('logout')) ?>" class="px-1">
                                    <?= App\Csrf::field() ?>
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i><?= te('nav.sign_out') ?>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url('login')) ?>"><?= te('nav.sign_in') ?></a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary btn-sm" href="<?= e(url('register')) ?>"><?= te('nav.sign_up') ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
