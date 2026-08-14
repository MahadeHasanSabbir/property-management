<?php
/**
 * Request a password reset link.
 *
 * The response is identical whether or not the address is registered, so this
 * endpoint cannot be used to enumerate accounts.
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<h1 class="h4 mb-2"><?= te('auth.reset_title') ?></h1>
<p class="text-body-secondary small"><?= te('auth.reset_intro') ?></p>

<form method="post" action="<?= e(url('forgot-password')) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="mb-3">
        <label for="email" class="form-label"><?= te('auth.email') ?></label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= e(old('email')) ?>" maxlength="190"
               autocomplete="username" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-envelope me-1"></i><?= te('auth.send_link') ?>
    </button>
</form>

<p class="text-center small mt-3 mb-0">
    <a href="<?= e(url('login')) ?>"><?= te('action.back') ?></a>
</p>
