<?php
/**
 * Staff / administrator sign-in.
 *
 * A separate URL so admin access is not advertised on the public form. The
 * actual boundary is the role check in Auth::attempt() and the route
 * middleware — signing in here with a customer account is refused, and signing
 * in on the public form with an admin account still lands in the admin area.
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<div class="d-flex align-items-center gap-2 mb-1">
    <i class="bi bi-shield-lock fs-4 text-body-secondary"></i>
    <h1 class="h4 mb-0"><?= te('auth.staff_title') ?></h1>
</div>
<p class="text-body-secondary small mb-3"><?= te('auth.staff_intro') ?></p>

<form method="post" action="<?= e(url('admin/login')) ?>" novalidate>
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

    <button type="submit" class="btn btn-dark w-100">
        <i class="bi bi-shield-lock me-1"></i><?= te('auth.staff_title') ?>
    </button>
</form>

<?php /* Named by destination, not a bare "Sign in" — on a page that is itself a
         sign-in form, that label would say nothing about where it leads. */ ?>
<hr class="my-4">

<div class="text-center">
    <p class="text-body-secondary small mb-2"><?= te('auth.are_you_customer') ?></p>
    <a href="<?= e(url('login')) ?>" class="btn btn-outline-secondary btn-sm w-100">
        <i class="bi bi-person me-1"></i><?= te('auth.go_customer_login') ?>
    </a>
</div>
