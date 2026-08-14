<?php
/**
 * Change own password. Also the page a user is held on when an administrator
 * has forced a password change (must_change_password), which is why it works
 * without the rest of the app being reachable.
 *
 * @var array $user
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h1 class="h4 mb-3"><?= te('profile.change_password') ?></h1>

        <?php if (!empty($user['must_change_password'])): ?>
            <div class="alert alert-warning d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div><?= te('auth.must_change_password') ?></div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= e(url('profile/password')) ?>" novalidate>
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label"><?= te('auth.current_password') ?></label>
                        <input type="password" class="form-control" id="current_password"
                               name="current_password" autocomplete="current-password" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><?= te('auth.new_password') ?></label>
                        <input type="password" class="form-control" id="password" name="password"
                               autocomplete="new-password" minlength="8" required>
                        <div class="form-text form-hint"><?= te('valid.password') ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label"><?= te('auth.password_confirm') ?></label>
                        <input type="password" class="form-control" id="password_confirm"
                               name="password_confirm" autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key me-1"></i><?= te('action.save') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
