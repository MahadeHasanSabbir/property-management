<?php
/**
 * Edit own profile.
 *
 * @var array $user
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
use App\Lang;

$v = static function (string $field) use ($user): string {
    $old = old($field);
    return $old !== '' ? $old : (string) ($user[$field] ?? '');
};
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h4 mb-3"><?= te('profile.edit') ?></h1>

        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= e(url('profile')) ?>" novalidate>
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= te('auth.name') ?></label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= e($v('name')) ?>" maxlength="60" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?= te('auth.email') ?></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($v('email')) ?>" maxlength="190" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label"><?= te('auth.phone') ?></label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?= e($v('phone')) ?>" maxlength="20" placeholder="01712345678">
                    </div>

                    <div class="mb-3">
                        <label for="locale" class="form-label"><?= te('profile.language') ?></label>
                        <select class="form-select" id="locale" name="locale">
                            <?php foreach (Lang::available() as $code): ?>
                                <option value="<?= e($code) ?>"
                                    <?= ($user['locale'] ?? DEFAULT_LANG) === $code ? 'selected' : '' ?>>
                                    <?= e(Lang::label($code)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i><?= te('action.save') ?>
                        </button>
                        <a href="<?= e(url('profile')) ?>" class="btn btn-outline-secondary">
                            <?= te('action.cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
