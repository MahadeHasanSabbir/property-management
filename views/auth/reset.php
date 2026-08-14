<?php
/**
 * Choose a new password from a reset link.
 *
 * @var string $token
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<h1 class="h4 mb-3"><?= te('auth.reset_title') ?></h1>

<form method="post" action="<?= e(url('reset-password/' . $token)) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="mb-3">
        <label for="password" class="form-label"><?= te('auth.new_password') ?></label>
        <input type="password" class="form-control" id="password" name="password"
               autocomplete="new-password" minlength="8" required autofocus>
        <div class="form-text form-hint"><?= te('valid.password') ?></div>
    </div>

    <div class="mb-3">
        <label for="password_confirm" class="form-label"><?= te('auth.password_confirm') ?></label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
               autocomplete="new-password" minlength="8" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-key me-1"></i><?= te('action.save') ?>
    </button>
</form>
