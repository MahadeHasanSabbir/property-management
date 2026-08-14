<?php
/**
 * Customer sign-in.
 *
 * Sign-in is by e-mail. The legacy form asked for a 9-digit generated id, and
 * prefilled it from ?id= via a query that interpolated $_GET straight into SQL
 * with no escaping at all — a pre-authentication injection on the login page
 * itself.
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<div class="d-flex align-items-center gap-2 mb-1">
    <i class="bi bi-person-circle fs-4 text-body-secondary"></i>
    <h1 class="h4 mb-0"><?= te('auth.customer_title') ?></h1>
</div>
<p class="text-body-secondary small mb-3"><?= te('auth.customer_intro') ?></p>

<form method="post" action="<?= e(url('login')) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="mb-3">
        <label for="email" class="form-label"><?= te('auth.email') ?></label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= e(old('email')) ?>" autocomplete="username"
               maxlength="190" required autofocus>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label"><?= te('auth.password') ?></label>
        <input type="password" class="form-control" id="password" name="password"
               autocomplete="current-password" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i><?= te('auth.customer_title') ?>
    </button>
</form>

<div class="d-flex justify-content-between mt-3 small">
    <a href="<?= e(url('forgot-password')) ?>"><?= te('auth.forgot') ?></a>
    <span class="text-body-secondary">
        <?= te('auth.no_account') ?>
        <a href="<?= e(url('register')) ?>"><?= te('nav.sign_up') ?></a>
    </span>
</div>

<?php /* The staff page previously had no link here at all, so the only way to
         reach it was to know the URL. */ ?>
<hr class="my-4">

<div class="text-center">
    <p class="text-body-secondary small mb-2"><?= te('auth.are_you_staff') ?></p>
    <a href="<?= e(url('admin/login')) ?>" class="btn btn-outline-secondary btn-sm w-100">
        <i class="bi bi-shield-lock me-1"></i><?= te('auth.go_staff_login') ?>
    </a>
</div>
