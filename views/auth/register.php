<?php
/**
 * Registration.
 *
 * All validation is enforced server-side in AccountController::register(). The
 * legacy form validated only in client-side JavaScript, so the rules were
 * advisory — and the name pattern it used, /^[A-Za-z .]{3,35}$/, rejected every
 * Bengali name.
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<h1 class="h4 mb-3"><?= te('auth.sign_up') ?></h1>

<form method="post" action="<?= e(url('register')) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="mb-3">
        <label for="name" class="form-label"><?= te('auth.name') ?></label>
        <input type="text" class="form-control" id="name" name="name"
               value="<?= e(old('name')) ?>" maxlength="60"
               autocomplete="name" required autofocus>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label"><?= te('auth.email') ?></label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= e(old('email')) ?>" maxlength="190"
               autocomplete="username" required>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">
            <?= te('auth.phone') ?>
            <span class="text-body-secondary small">(<?= te('common.optional') ?>)</span>
        </label>
        <input type="tel" class="form-control" id="phone" name="phone"
               value="<?= e(old('phone')) ?>" maxlength="20"
               autocomplete="tel" placeholder="01712345678">
        <div class="form-text form-hint">+880 …</div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label"><?= te('auth.password') ?></label>
        <input type="password" class="form-control" id="password" name="password"
               autocomplete="new-password" minlength="8" required>
        <div class="form-text form-hint"><?= te('valid.password') ?></div>
    </div>

    <div class="mb-3">
        <label for="password_confirm" class="form-label"><?= te('auth.password_confirm') ?></label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
               autocomplete="new-password" minlength="8" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-person-plus me-1"></i><?= te('auth.sign_up') ?>
    </button>
</form>

<p class="text-center text-body-secondary small mt-3 mb-0">
    <?= te('auth.have_account') ?>
    <a href="<?= e(url('login')) ?>"><?= te('nav.sign_in') ?></a>
</p>
